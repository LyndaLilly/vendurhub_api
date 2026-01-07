<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Order Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f5ff; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5ff; padding:20px;">
    <tr>
        <td align="center">

            <!-- Main Container -->
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e0e0e0;">

                <!-- Header -->
                <tr>
                    <td style="background-color:#024da0; padding:20px; text-align:center;">
                        <h1 style="margin:0; color:#ffffff; font-size:22px;">
                            🛒 New Order Received
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:25px; color:#0d0d0d;">

                        <p style="font-size:16px;">
                            Hello <strong>{{ $order->product->user->fullname ?? 'Vendor' }}</strong>,
                        </p>

                        <p style="color:#555555;">
                            You have received a new order for your product. Below are the order details:
                        </p>

                        <!-- Product Details -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5ff; border-radius:6px; padding:15px; margin:20px 0;">
                            <tr>
                                <td>
                                    <h3 style="margin-top:0; color:#024da0;">Product Details</h3>
                                    <p><strong>Product Name:</strong> {{ $order->product->name }}</p>
                                    <p><strong>Product Price:</strong> ₦{{ number_format($order->product_price, 2) }}</p>
                                    <p><strong>Delivery Price:</strong> ₦{{ number_format($order->delivery_price, 2) }}</p>
                                    <p style="font-size:16px;">
                                        <strong>Total:</strong>
                                        <span style="color:#FF9900; font-weight:bold;">
                                            ₦{{ number_format($order->total_price, 2) }}
                                        </span>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Selected Image -->
                        @if($selectedImageUrl)
                        <p>
                            <strong>Selected Product Image:</strong><br><br>
                            <img src="{{ $selectedImageUrl }}" alt="Selected Product Image"
                                 style="width:200px; border-radius:6px; border:1px solid #e0e0e0;">
                        </p>
                        @endif

                        <!-- Payment Proof -->
                        @if($paymentProofUrl)
                        <p style="margin-top:15px;">
                            <strong>Payment Proof:</strong><br>
                            <a href="{{ $paymentProofUrl }}" target="_blank"
                               style="color:#024da0; text-decoration:none; font-weight:bold;">
                                View Payment Proof
                            </a>
                        </p>
                        @endif

                        <!-- Buyer Info -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafafa; border-radius:6px; padding:15px; margin:25px 0; border:1px solid #e0e0e0;">
                            <tr>
                                <td>
                                    <h3 style="margin-top:0; color:#024da0;">Buyer Information</h3>
                                    <p><strong>Full Name:</strong> {{ $order->fullname }}</p>
                                    <p><strong>Email:</strong> {{ $order->email }}</p>
                                    <p><strong>WhatsApp:</strong> {{ $order->whatsapp }}</p>
                                    <p><strong>Address:</strong> {{ $order->address }}</p>
                                    <p><strong>Delivery Location:</strong> {{ $order->delivery_city }}, {{ $order->delivery_state }}</p>
                                    <p><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</p>
                                    <p>
                                        <strong>Status:</strong>
                                        <span style="color:#00e676; font-weight:bold;">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- CTA -->
                        <p style="text-align:center; margin:30px 0;">
                            <a href="{{ url('/vendor/dashboard') }}"
                               style="background-color:#024da0; color:#ffffff; padding:12px 20px;
                                      text-decoration:none; border-radius:6px; font-weight:bold;">
                                Login to Process Order
                            </a>
                        </p>

                        <p style="color:#555555; font-size:14px;">
                            Thank you for using our platform.<br>
                            <strong>— VendurHub Team</strong>
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color:#cde4fe; text-align:center; padding:12px; font-size:12px; color:#555555;">
                        © {{ date('Y') }} VendurHub. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
