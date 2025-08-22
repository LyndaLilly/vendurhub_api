<?php
namespace App\Http\Controllers;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\DeliveryLocation;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'fullname'      => 'required|string|max:255',
            'whatsapp'      => 'required|string|max:20',
            'email'         => 'required|email',
            'address'       => 'required|string',
            'mobile_number' => 'required|string',
            'country'       => 'required|string',
            'state'         => 'required|string',
            'city'          => 'required|string',
            'quantity'      => 'required|integer|min:1',
            'payment_type'  => 'required|in:pay_now,pay_on_delivery',
            'image_choice'  => 'nullable|string', // accept string choice
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
        ]);

        \Log::info('store() called', [
            'image_choice'     => $request->input('image_choice'),
            'all_request_data' => $request->all(),
        ]);

        \Log::info('Order image_choice from request:', ['image_choice' => $request->image_choice]);

        $product = Product::with(['user', 'images'])->findOrFail($productId);

        $locationId = $request->delivery_location_id;

        $location = DeliveryLocation::where('user_id', $product->user_id)
            ->where('id', $locationId)
            ->firstOrFail();

        $quantity      = (int) $request->quantity;
        $productPrice  = $product->price;
        $deliveryPrice = $location->delivery_price;

        $totalProductPrice = $productPrice * $quantity;
        $totalPrice        = $totalProductPrice + $deliveryPrice;

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $file             = $request->file('payment_proof');
            $filename         = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $paymentProofPath = $file->storeAs('payment_proofs', $filename, 'public');
        }

        $order = Order::create([
            'fullname'       => $request->fullname,
            'whatsapp'       => $request->whatsapp,
            'email'          => $request->email,
            'address'        => $request->address,
            'mobile_number'  => $request->mobile_number,
            'country'        => $request->country,
            'state'          => $request->state,
            'city'           => $request->city,
            'quantity'       => $quantity,
            'delivery_price' => $deliveryPrice,
            'delivery_state' => $location->state,
            'delivery_city'  => $location->city,
            'product_price'  => $productPrice,
            'total_price'    => $totalPrice,
            'payment_type'   => $request->payment_type,
            'payment_proof'  => $paymentProofPath,
            'image_choice'   => $request->image_choice,
            'status'         => 'pending',
            'product_id'     => $product->id,
            'vendor_id'      => $product->user_id,
        ]);

        // Send email to vendor
        $emailMessage = 'Email sent to vendor successfully.';
        try {
            Mail::to($product->user->email)->send(new OrderPlacedMail($order));
        } catch (\Exception $e) {
            $emailMessage = 'Order placed, but failed to send email: ' . $e->getMessage();
        }

        $vendorProfile = $product->user->profile;

        // Resolve image choice path safely
        $chosenImage = null;
        if ($request->image_choice && in_array($request->image_choice, ['1', '2', '3'])) {
            $index = intval($request->image_choice) - 1;
            if (isset($product->images[$index])) {
                $chosenImage = asset('storage/' . $product->images[$index]->image_path);
            }
        }

        return response()->json([
            'message'      => 'Order placed successfully!',
            'email_status' => $emailMessage,
            'receipt'      => [
                'buyer'       => [
                    'fullname'      => $order->fullname,
                    'email'         => $order->email,
                    'mobile_number' => $order->mobile_number,
                    'whatsapp'      => $order->whatsapp,
                    'address'       => $order->address,
                ],
                'product'     => [
                    'name'         => $product->name,
                    'price'        => $product->price,
                    'quantity'     => $order->quantity,
                    'image_choice' => $chosenImage,
                ],
                'delivery'    => [
                    'location' => $location,
                    'price'    => $deliveryPrice,
                ],
                'payment'     => [
                    'type'      => $order->payment_type,
                    'status'    => $order->status,
                    'proof_url' => $paymentProofPath ? asset('storage/' . $paymentProofPath) : null,
                ],
                'total_price' => $totalPrice,
                'vendor'      => [
                    'business_name'  => $vendorProfile->business_name ?? '',
                    'account_name'   => $vendorProfile->business_account_name ?? '',
                    'account_number' => $vendorProfile->business_account_number ?? '',
                    'bank_name'      => $vendorProfile->business_bank_name ?? '',
                ],
            ],
        ]);
    }

    public function myOrders(Request $request)
    {
        try {
            $user = auth()->user();

            $orders = Order::with(['product.images'])
                ->where('vendor_id', $user->id)
                ->latest()
                ->get()
                ->map(function ($order) {
                    $imageChoice = null;

                    if (
                        $order->product &&
                        $order->product->images->count() > 0 &&
                        $order->image_choice &&
                        in_array($order->image_choice, ['1', '2', '3'])
                    ) {
                        $index = intval($order->image_choice) - 1;
                        if ($order->product->images->has($index)) {
                            $imageChoice = asset('storage/' . $order->product->images[$index]->image_path);
                        }
                    }

                    return [
                        'id'               => $order->id,
                        'product'          => $order->product ? [
                            'id'     => $order->product->id,
                            'name'   => $order->product->name,
                            'price'  => $order->product->price,
                            'images' => $order->product->images->map(fn($img) => asset('storage/' . $img->image_path)),
                        ] : null,
                        'buyer_name'       => $order->fullname,
                        'buyer_email'      => $order->email,
                        'buyer_phone'      => $order->mobile_number,
                        'delivery_address' => $order->address,
                        'quantity'         => $order->quantity,
                        'status'           => $order->status,
                        'payment_status'   => $order->payment_status ?? null,
                        'payment_proof'    => $order->payment_proof ? asset('storage/' . $order->payment_proof) : null,
                        'image_choice'     => $imageChoice,
                        'total_price'      => $order->total_price, // <-- Add this
                        'created_at'       => $order->created_at,
                    ];

                });

            return response()->json([
                'orders' => $orders,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in myOrders(): ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error while fetching orders.',
            ], 500);
        }
    }

    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $order         = Order::with(['product.images', 'vendor.profile'])->findOrFail($orderId);
        $order->status = $request->status;
        $order->save();

        $emailMessage = 'Email sent to buyer successfully.';
        try {
            $attachReceipt = ($order->status === 'approved');
            Mail::to($order->email)->send(new OrderStatusUpdatedMail($order, $attachReceipt));
        } catch (\Exception $e) {
            $emailMessage = 'Status updated, but failed to send email: ' . $e->getMessage();
        }

        return response()->json([
            'message'      => 'Order status updated.',
            'email_status' => $emailMessage,
            'order'        => $order,
        ]);
    }

    public function showSingleOrder($id)
    {
        $user = auth()->user();

        $order = Order::with(['product.images'])
            ->where('id', $id)
            ->where('vendor_id', $user->id)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $imageChoice = $order->image_choice ? asset('storage/' . $order->image_choice) : null;

        return response()->json([
            'order' => [
                'id'             => $order->id,
                'product'        => [
                    'id'    => $order->product->id,
                    'name'  => $order->product->name,
                    'price' => $order->product_price,
                ],
                'buyer_name'     => $order->fullname,
                'buyer_email'    => $order->email,
                'buyer_phone'    => $order->mobile_number,
                'buyer_whatsapp' => $order->whatsapp,
                'buyer_address'  => $order->address,
                'country'        => $order->country,
                'state'          => $order->state,
                'city'           => $order->city,
                'quantity'       => $order->quantity,
                'status'         => $order->status,
                'payment_type'   => $order->payment_type,
                'product_price'  => $order->product_price,
                'delivery_price' => $order->delivery_price,
                'total_price'    => $order->total_price,
                'payment_proof'  => $order->payment_proof ? asset('storage/' . $order->payment_proof) : null,
                'image_choice'   => $imageChoice,
                'created_at'     => $order->created_at,
            ],
        ]);
    }

}
