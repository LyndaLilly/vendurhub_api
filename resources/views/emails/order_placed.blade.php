<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Order Notification</title>
</head>
<body>
    <h2>Hello {{ $order->product->user->name ?? 'Vendor' }},</h2>

    <p>You have received a new order for your product:</p>

    <h3>Product Details:</h3>
    <ul>
        <li><strong>Product Name:</strong> {{ $order->product->name }}</li>
        <li><strong>Price:</strong> ₦{{ number_format($order->product_price, 2) }}</li>
        <li><strong>Delivery Price:</strong> ₦{{ number_format($order->delivery_price, 2) }}</li>
        <li><strong>Total:</strong> ₦{{ number_format($order->total_price, 2) }}</li>
    </ul>

    @if($selectedImageUrl)
        <p><strong>Selected Product Image:</strong><br>
        <img src="{{ $selectedImageUrl }}" alt="Selected Product Image" width="200"></p>
    @endif

    @if($paymentProofUrl)
        <p><strong>Payment Proof:</strong><br>
        <a href="{{ $paymentProofUrl }}" target="_blank">View Payment Proof</a></p>
    @endif

    <h3>Buyer Information:</h3>
    <ul>
        <li><strong>Full Name:</strong> {{ $order->fullname }}</li>
        <li><strong>Email:</strong> {{ $order->email }}</li>
        <li><strong>WhatsApp:</strong> {{ $order->whatsapp }}</li>
        <li><strong>Phone:</strong> {{ $order->mobile_number }}</li>
        <li><strong>Address:</strong> {{ $order->address }}</li>
        <li><strong>Location:</strong> {{ $order->city }}, {{ $order->state }}, {{ $order->country }}</li>
        <li><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</li>
        <li><strong>Status:</strong> {{ ucfirst($order->status) }}</li>
    </ul>

    <p>Please login to your dashboard to process this order.</p>

    <br>
    <p>Thank you for using our platform!</p>
</body>
</html>
