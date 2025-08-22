<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subscription Expiry Reminder</title>
</head>
<body>
    <h1>Hi {{ $user->name ?? 'User' }},</h1>

    <p>Your subscription will expire on <strong>{{ \Carbon\Carbon::parse($expiresAt)->toFormattedDateString() }}</strong>.</p>

    <p>Please renew your subscription to continue enjoying our services without interruption.</p>

    <p>Thank you,<br>
    {{ config('app.name') }} Team</p>
</body>
</html>
