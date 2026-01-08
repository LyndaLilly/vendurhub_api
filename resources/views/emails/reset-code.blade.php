<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
</head>
<body>
    <p>Hello {{ $user->name ?? 'User' }},</p>

    <p>Your password reset code is:</p>

    <h2>{{ $code }}</h2>

    <p>This code will expire in 15 minutes.</p>

    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
