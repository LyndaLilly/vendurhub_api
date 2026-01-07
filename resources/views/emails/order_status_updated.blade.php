<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { border-bottom: 2px solid #0a1e5c; margin-bottom: 15px; }
        .title { background:#0a1e5c;color:#fff;padding:6px 10px;border-radius:4px; }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        th { background:#0a1e5c;color:#fff;padding:6px; }
        td { padding:6px;border:1px solid #ddd; vertical-align: top; }
        .right { text-align:right; }
        .footer {
            margin-top:40px;
            border-top:1px solid #ccc;
            text-align:center;
            font-size:11px;
            padding-top:10px;
        }
        .product-image {
            max-width:200px;
            border:1px solid #ddd;
            padding:4px;
            margin-top:6px;
        }
    </style>
</head>
<body>

<p>Hello {{ $order->fullname }},</p>

<p>
    Your order has been
    <strong style="color:
        {{ $order->status === 'approved' ? 'green' : ($order->status === 'rejected' ? 'red' : 'black') }};
    ">
        {{ ucfirst($order->status) }}
    </strong>.
</p>

{{-- Approval or Rejection Note --}}
@if($order->status === 'approved' && !empty($order->approval_note))
    <p><strong>Note:</strong> {{ $order->approval_note }}</p>
@endif

@if($order->status === 'rejected' && !empty($order->rejection_reason))
    <p><strong>Reason for rejection:</strong> {{ $order->rejection_reason }}</p>
@endif


{{-- HEADER --}}
<table width="100%">
    <tr>
        <td>
            @if(!empty($vendorLogo) && file_exists($vendorLogo))
                <img src="{{ $vendorLogo }}" height="60"><br>
            @endif

            <strong style="color:#0a1e5c;">
                {{ $order->vendor->profile->business_name ?? 'Vendor' }}
            </strong>

            <strong style="color:#0a1e5c;">
                {{ $order->vendor->profile->business_location ?? 'Vendor Location' }}
            </strong>
        </td>
        <td class="right">
            <span class="title">RECEIPT</span><br><br>
            <strong>Order ID:</strong> {{ $order->id }}<br>
            <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}
        </td>
    </tr>
</table>

{{-- BUYER --}}
<p>
    <strong>Billed To:</strong><br>
    {{ $order->fullname }}<br>
    {{ $order->address }}
</p>

{{-- PRODUCT IMAGE --}}
@if(!empty($productImageUrl) && file_exists($productImageUrl))
    <table width="100%">
        <tr>
            <td>
                <strong>Product Image</strong><br>
                <img src="{{ $productImageUrl }}" class="product-image">
            </td>
        </tr>
    </table>
@endif

{{-- ITEMS --}}
<table>
    <thead>
        <tr>
            <th>Item</th>
            <th width="60">Qty</th>
            <th width="120">Price (₦)</th>
            <th width="120">Total (₦)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $order->product->name }}</td>
            <td>{{ $order->quantity }}</td>
            <td>{{ number_format($order->product_price) }}</td>
            <td>{{ number_format($order->product_price * $order->quantity) }}</td>
        </tr>
    </tbody>
</table>

{{-- TOTALS --}}
<table width="50%" align="right">
    <tr>
        <td><strong>Subtotal</strong></td>
        <td class="right">₦{{ number_format($order->product_price * $order->quantity) }}</td>
    </tr>
    <tr>
        <td><strong>Delivery</strong></td>
        <td class="right">₦{{ number_format($order->delivery_price) }}</td>
    </tr>
    <tr>
        <td><strong>Total</strong></td>
        <td class="right">
            <strong>₦{{ number_format($order->total_price) }}</strong>
        </td>
    </tr>
</table>

<br><br><br>

{{-- SIGNATURE --}}
<div class="right">
    @if(!empty($vendorSignature) && file_exists($vendorSignature))
        <img src="{{ $vendorSignature }}" height="50"><br>
    @endif
    <strong>{{ $order->vendor->profile->business_name ?? 'Vendor' }}</strong><br>
    <small>Authorized Signature</small>
</div>

{{-- FOOTER --}}
<div class="footer">
    Thank you for doing business with us.<br>
    This receipt was generated electronically and is valid without signature.
</div>

</body>
</html>
