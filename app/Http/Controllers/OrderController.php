<?php
namespace App\Http\Controllers;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\DeliveryLocation;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    const UPLOAD_BASE_URL = 'https://api.vendurhub.com/public/uploads/';

    private function assetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return self::UPLOAD_BASE_URL . ltrim($path, '/');
    }

    public function store(Request $request, $productId)
    {
        $request->validate([
            'fullname'      => 'required|string|max:255',
            'whatsapp'      => 'required|string|max:20',
            'email'         => 'required|email',
            'address'       => 'required|string',
            'quantity'      => 'required|integer|min:1',
            'payment_type'  => 'required|in:pay_now,pay_on_delivery',
            'image_choice'  => 'nullable|string',
            'payment_proof' => $request->payment_type === 'pay_now'
                ? 'required|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048'
                : 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
        ]);

        $product = Product::with(['user', 'images'])->findOrFail($productId);

        $location = null;
        if ($request->filled('delivery_location_id')) {
            $location = DeliveryLocation::where('user_id', $product->user_id)
                ->where('id', $request->delivery_location_id)
                ->first();
        }
        $deliveryPrice = $location ? $location->delivery_price : 0;

        $quantity          = (int) $request->quantity;
        $productPrice      = $product->price;
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
            'quantity'       => $quantity,
            'delivery_price' => $deliveryPrice,
            'delivery_state' => $location->state ?? null,
            'delivery_city'  => $location->city ?? null,
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
                    'fullname' => $order->fullname,
                    'email'    => $order->email,
                    'whatsapp' => $order->whatsapp,
                    'address'  => $order->address,
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
                    'logo'           => $this->assetUrl($vendorProfile->business_logo),
                    'signature'      => $this->assetUrl($vendorProfile->business_signature),
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
                    'buyer_phone'      => $order->whatsapp,
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
        $request->validate([
            'status'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:1000',
            'approval_note'    => 'nullable|string|max:1000',
        ]);

        $order = Order::with(['product.images', 'vendor.profile'])
            ->findOrFail($orderId);

        $order->status = $request->status;

        if ($request->status === 'rejected') {
            $order->rejection_reason = $request->rejection_reason;
            $order->approval_note    = null;
        } else {
            $order->approval_note    = $request->approval_note;
            $order->rejection_reason = null;
        }

        $order->save();

        $emailMessage = 'Email sent to buyer successfully.';
        try {
            $attachReceipt = ($order->status === 'approved');
            Mail::to($order->email)->send(
                new OrderStatusUpdatedMail($order, $attachReceipt)
            );
                // Optional success log
    Log::info('Order status email sent', [
        'order_id' => $order->id,
        'email'    => $order->email,
        'status'   => $order->status,
    ]);

        } catch (\Exception $e) {
               Log::error('Order status email failed', [
        'order_id' => $order->id,
        'email'    => $order->email,
        'status'   => $order->status,
        'error'    => $e->getMessage(),
        'trace'    => $e->getTraceAsString(),
    ]);
            $emailMessage = 'Status updated, but failed to send email: ' . $e->getMessage();
        }
        

        $productImages = $order->product->images->map(
            fn($img) => self::UPLOAD_BASE_URL . $img->image_path
        );

        return response()->json([
            'message'      => 'Order status updated.',
            'email_status' => $emailMessage,
            'order'        => [
                'id'               => $order->id,
                'status'           => $order->status,
                'approval_note'    => $order->approval_note,
                'rejection_reason' => $order->rejection_reason,
                'total_price'      => $order->total_price,
                'created_at'       => $order->created_at,
                'product'          => [
                    'id'     => $order->product->id,
                    'name'   => $order->product->name,
                    'price'  => $order->product_price,
                    'images' => $productImages,
                ],
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
                'buyer_whatsapp' => $order->whatsapp,
                'buyer_address'  => $order->address,
                'delivery_state' => $order->delivery_state,
                'delivery_city'  => $order->delivery_city,
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
