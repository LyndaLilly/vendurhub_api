<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Receipt - Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
        }
        h1, h2, h3 {
            color: #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f7f7f7;
            text-align: left;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 8px;
            margin-top: 30px;
            border-left: 5px solid #4CAF50;
        }
        .total {
            font-weight: bold;
            font-size: 16px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .vendor-logo {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .signature {
            max-height: 60px;
            margin-top: 30px;
        }
        .vendor-info {
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($order->vendor->profile->business_logo)
            <img 
                src="{{ asset('storage/' . $order->vendor->profile->business_logo) }}" 
                alt="Vendor Logo" 
                class="vendor-logo" />
        @endif

        <h1>Receipt for Order #{{ $order->id }}</h1>
        <p><strong>Date:</strong> {{ $order->created_at->format('F j, Y, g:i A') }}</p>
    </div>

    <div class="section-title"><h2>Buyer Information</h2></div>
    <table>
        <tr><th>Full Name</th><td>{{ $order->fullname }}</td></tr>
        <tr><th>Email</th><td>{{ $order->email }}</td></tr>
        <tr><th>Mobile Number</th><td>{{ $order->mobile_number }}</td></tr>
        <tr><th>WhatsApp</th><td>{{ $order->whatsapp }}</td></tr>
        <tr><th>Address</th><td>{{ $order->address }}, {{ $order->city }}, {{ $order->state }}, {{ $order->country }}</td></tr>
    </table>

    <div class="section-title"><h2>Order Details</h2></div>
    <table>
        <tr><th>Product Name</th><td>{{ $order->product->name ?? 'N/A' }}</td></tr>
        <tr><th>Quantity</th><td>{{ $order->quantity }}</td></tr>
        <tr><th>Product Price</th><td>₦{{ number_format($order->product_price, 2) }}</td></tr>
        <tr><th>Delivery Price</th><td>₦{{ number_format($order->delivery_price, 2) }}</td></tr>
        <tr class="total"><th>Total Price</th><td>₦{{ number_format($order->total_price, 2) }}</td></tr>
    </table>

    <div class="section-title"><h2>Vendor Payment Details</h2></div>
    <table>
        <tr><th>Business Name</th><td>{{ $order->vendor->profile->business_name ?? 'N/A' }}</td></tr>
        <tr><th>Account Name</th><td>{{ $order->vendor->profile->business_account_name ?? 'N/A' }}</td></tr>
        <tr><th>Account Number</th><td>{{ $order->vendor->profile->business_account_number ?? 'N/A' }}</td></tr>
        <tr><th>Bank Name</th><td>{{ $order->vendor->profile->business_bank_name ?? 'N/A' }}</td></tr>
    </table>

    @if($order->payment_proof)
        <p><strong>Payment Proof:</strong> Attached or viewable separately.</p>
    @endif

    <div class="footer">
        @if($order->vendor->profile->signature)
            <p>Authorized Signature:</p>
            <img 
                src="{{ asset('storage/' . $order->vendor->profile->signature) }}" 
                alt="Signature" 
                class="signature" />
        @endif
    </div>

    <p style="margin-top: 50px;">Thank you for shopping with us!</p>
</body>
</html>
