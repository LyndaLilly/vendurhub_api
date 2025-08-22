<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <p>Hello {{ $user->fullname }},</p>

    <p>Thank you for registering. Your email verification code is:</p>

    <h2>{{ $code }}</h2>

    <p>This code expires in 15 minutes.</p>

    <p>— Whatsapp Mini Ecommerce Team</p>
</body>
</html>
