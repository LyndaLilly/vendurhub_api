<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            border-bottom: 2px solid #0a1e5c;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .flex {
            width: 100%;
        }

        .left {
            width: 60%;
            float: left;
        }

        .right {
            width: 40%;
            float: right;
            text-align: right;
        }

        .receipt-title {
            background: #0a1e5c;
            color: #fff;
            padding: 6px 10px;
            display: inline-block;
            border-radius: 4px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #0a1e5c;
            color: #fff;
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .totals {
            width: 50%;
            float: right;
            margin-top: 10px;
        }

        .totals td {
            border: none;
            padding: 4px;
        }

        .footer {
            border-top: 1px solid #ccc;
            margin-top: 40px;
            padding-top: 10px;
            text-align: center;
            font-size: 11px;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }

        .signature img {
            height: 50px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="header flex">
    <div class="left">
        @if($order->vendor && $order->vendor->business_logo)
            <img src="{{ public_path('uploads/' . $order->vendor->business_logo) }}" height="60">
        @endif

        <h3 style="color:#0a1e5c; margin-bottom: 2px;">
            {{ $order->vendor->business_name ?? 'Vendor Business' }}
        </h3>
        <p style="font-size:11px;">
            {{ $order->vendor->business_location ?? '' }}
        </p>
    </div>

    <div class="right">
        <div class="receipt-title">RECEIPT</div>
        <p style="margin-top:8px;">
            <strong>Receipt No:</strong> {{ $order->receipt_number }} <br>
            <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}
        </p>
    </div>
</div>

{{-- BILLING & STATUS --}}
<div class="flex">
    <div class="left">
        <h4 style="color:#0a1e5c;">Billed To</h4>
        <p>
            <strong>Name:</strong> {{ $order->fullname }}
        </p>
    </div>

    <div class="right">
        <h4 style="color:#0a1e5c;">Status</h4>
        <p>
            <strong>Payment:</strong> {{ ucfirst($order->payment_status) }} <br>
            <strong>Delivery:</strong> {{ ucfirst($order->delivery_status) }}
        </p>
    </div>
</div>

<div class="clear"></div>

{{-- ITEMS TABLE --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Item Description</th>
            <th>Qty</th>
            <th>Price (₦)</th>
            <th>Total (₦)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price) }}</td>
                <td>{{ number_format($item->price * $item->quantity) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- TOTALS --}}
<table class="totals">
    <tr>
        <td><strong>Subtotal</strong></td>
        <td>₦{{ number_format($order->subtotal) }}</td>
    </tr>
    <tr>
        <td><strong>Amount Paid</strong></td>
        <td>₦{{ number_format($order->amount_paid) }}</td>
    </tr>
    <tr>
        <td><strong>Balance</strong></td>
        <td>₦{{ number_format($order->balance) }}</td>
    </tr>
</table>

<div class="clear"></div>

{{-- SIGNATURE --}}
<div class="signature">
    @if($order->vendor && $order->vendor->signature)
        <img src="{{ public_path('uploads/' . $order->vendor->signature) }}"><br>
    @endif
    <strong>{{ $order->vendor->business_name ?? '' }}</strong><br>
    <span style="font-size:11px;">Authorized Signature</span>
</div>

{{-- FOOTER --}}
<div class="footer">
    <p style="color:#0a1e5c;">
        Thank you for doing business with us.
    </p>
    <small style="color:#8b0000;">
        This receipt was generated electronically and is valid without signature.
    </small>
</div>

</body>
</html>
