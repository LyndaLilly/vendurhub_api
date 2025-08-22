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
    const UPLOAD_BASE_URL = 'https://api.vendurhub.com/public/uploads/';

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
            'image_choice'  => 'nullable|string',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
        ]);

        $product = Product::with(['user', 'images'])->findOrFail($productId);

        $location = DeliveryLocation::where('user_id', $product->user_id)
            ->where('id', $request->delivery_location_id)
            ->firstOrFail();

        $quantity          = (int) $request->quantity;
        $productPrice      = $product->price;
        $deliveryPrice     = $location->delivery_price;
        $totalProductPrice = $productPrice * $quantity;
        $totalPrice        = $totalProductPrice + $deliveryPrice;

        // Handle payment proof upload
        $paymentProofUrl = null;
        if ($request->hasFile('payment_proof')) {
            $file      = $request->file('payment_proof');
            $subfolder = 'payment_proofs';
            $uploadDir = public_path("uploads/{$subfolder}");

            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            $paymentProofUrl = self::UPLOAD_BASE_URL . "{$subfolder}/{$filename}";
        }

        // Resolve chosen product image
        $chosenImageUrl = null;
        if ($request->image_choice && isset($product->images[intval($request->image_choice) - 1])) {
            $imagePath      = $product->images[intval($request->image_choice) - 1]->image_path;
            $chosenImageUrl = self::UPLOAD_BASE_URL . $imagePath;
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
            'payment_proof'  => $paymentProofUrl,
            'image_choice'   => $chosenImageUrl,
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
                    'image_choice' => $chosenImageUrl,
                ],
                'delivery'    => [
                    'location' => $location,
                    'price'    => $deliveryPrice,
                ],
                'payment'     => [
                    'type'      => $order->payment_type,
                    'status'    => $order->status,
                    'proof_url' => $paymentProofUrl,
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
        $user = auth()->user();

        $orders = Order::with(['product.images'])
            ->where('vendor_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($order) {
                // Resolve chosen image URL
                $imageChoice = $order->image_choice ?? null;

                // Resolve all product images URLs
                $productImages = $order->product
                ? $order->product->images->map(fn($img) => OrderController::UPLOAD_BASE_URL . $img->image_path)
                : [];

                // Resolve payment proof URL
                $paymentProofUrl = $order->payment_proof ?? null;

                return [
                    'id'               => $order->id,
                    'product'          => $order->product ? [
                        'id'     => $order->product->id,
                        'name'   => $order->product->name,
                        'price'  => $order->product->price,
                        'images' => $productImages,
                    ] : null,
                    'buyer_name'       => $order->fullname,
                    'buyer_email'      => $order->email,
                    'buyer_phone'      => $order->mobile_number,
                    'delivery_address' => $order->address,
                    'quantity'         => $order->quantity,
                    'status'           => $order->status,
                    'payment_status'   => $order->payment_status ?? null,
                    'payment_proof'    => $paymentProofUrl,
                    'image_choice'     => $imageChoice,
                    'total_price'      => $order->total_price,
                    'created_at'       => $order->created_at,
                ];
            });

        return response()->json(['orders' => $orders]);
    }

    public function updateStatus(Request $request, $orderId)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);

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

        $imageChoice     = $order->image_choice ?? null;
        $paymentProofUrl = $order->payment_proof ?? null;
        $productImages   = $order->product->images->map(fn($img) => OrderController::UPLOAD_BASE_URL . $img->image_path);

        return response()->json([
            'message'      => 'Order status updated.',
            'email_status' => $emailMessage,
            'order'        => [
                'id'             => $order->id,
                'product'        => [
                    'id'     => $order->product->id,
                    'name'   => $order->product->name,
                    'price'  => $order->product_price,
                    'images' => $productImages,
                ],
                'buyer_name'     => $order->fullname,
                'buyer_email'    => $order->email,
                'buyer_phone'    => $order->mobile_number,
                'buyer_whatsapp' => $order->whatsapp,
                'buyer_address'  => $order->address,
                'quantity'       => $order->quantity,
                'status'         => $order->status,
                'payment_type'   => $order->payment_type,
                'payment_status' => $order->payment_status ?? null,
                'payment_proof'  => $paymentProofUrl,
                'image_choice'   => $imageChoice,
                'total_price'    => $order->total_price,
                'created_at'     => $order->created_at,
            ],
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

        $imageChoice     = $order->image_choice ?? null;
        $paymentProofUrl = $order->payment_proof ?? null;
        $productImages   = $order->product->images->map(fn($img) => OrderController::UPLOAD_BASE_URL . $img->image_path);

        return response()->json([
            'order' => [
                'id'             => $order->id,
                'product'        => [
                    'id'     => $order->product->id,
                    'name'   => $order->product->name,
                    'price'  => $order->product_price,
                    'images' => $productImages,
                ],
                'buyer_name'     => $order->fullname,
                'buyer_email'    => $order->email,
                'buyer_phone'    => $order->mobile_number,
                'buyer_whatsapp' => $order->whatsapp,
                'buyer_address'  => $order->address,
                'quantity'       => $order->quantity,
                'status'         => $order->status,
                'payment_type'   => $order->payment_type,
                'product_price'  => $order->product_price,
                'delivery_price' => $order->delivery_price,
                'total_price'    => $order->total_price,
                'payment_proof'  => $paymentProofUrl,
                'image_choice'   => $imageChoice,
                'created_at'     => $order->created_at,
            ],
        ]);
    }

}
