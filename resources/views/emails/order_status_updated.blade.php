<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Order Status Update</title>
</head>
<body>
    <h1>Hello {{ $order->fullname }},</h1>

    <p>Your order (ID: {{ $order->id }}) status has been updated to <strong>{{ ucfirst($order->status) }}</strong>.</p>

    <h3>Order Details:</h3>
    <p><strong>Product:</strong> {{ $order->product->name ?? 'N/A' }}</p>
    <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
    <p><strong>Product Price:</strong> ₦{{ number_format($order->product_price) }}</p>
    <p><strong>Delivery Location:</strong> {{ $order->delivery_city }}, {{ $order->delivery_state }}</p>
    <p><strong>Delivery Price:</strong> ₦{{ number_format($order->delivery_price) }}</p>
    <p><strong>Total Price:</strong> ₦{{ number_format($order->total_price) }}</p>

    @if($productImageUrl)
        <p><strong>Product Image:</strong></p>
        <img src="{{ $productImageUrl }}" alt="Product Image" style="max-width: 300px;" />
    @endif

    <h3>Payment Information:</h3>
    <p><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</p>

    @if($order->payment_proof)
        <p><strong>Payment Proof:</strong></p>
        <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Payment Proof" style="max-width: 300px;" />
    @endif

    <p>Thank you for shopping with us!</p>

    @if($order->status === 'approved')
        <p>A receipt PDF is attached to this email for your records.</p>
    @endif
</body>
</html>
