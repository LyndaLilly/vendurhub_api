<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subscription Activated</title>
</head>
<body>
    <h2>Hello {{ $user->name }},</h2>
    <p>Thank you for subscribing to our {{ ucfirst($plan) }} plan.</p>

    <p><strong>Business Name:</strong> {{ $user->business_name }}</p>
    <p><strong>Plan:</strong> {{ ucfirst($plan) }}</p>
    <p><strong>Amount Paid:</strong> ₦{{ $plan === 'monthly' ? '3,000' : '30,000' }}</p>
    <p><strong>Subscription Start Date:</strong> {{ now()->format('d M, Y') }}</p>
    <p><strong>Expiry Date:</strong> {{ $expiresAt->format('d M, Y') }}</p>

    <p>We appreciate your trust in our platform. Enjoy your subscription!</p>
</body>
</html>
