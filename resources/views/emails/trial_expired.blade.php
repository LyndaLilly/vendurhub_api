<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Expired</title>
</head>
<body>
     <p>Hello {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }},</p>

    <p>We hope you enjoyed your free trial of Vendurhub! Your trial period has now ended, and your access to premium features has been limited.</p>

    <p>To continue enjoying all the benefits and tools Vendurhub has to offer, we invite you to upgrade to a subscription today.</p>

    <p><a href="{{ $subscribeUrl }}" style="display:inline-block;padding:10px 20px;background-color:#007BFF;color:#fff;text-decoration:none;border-radius:5px;">Upgrade Now</a></p>

    <p>If you have any questions, feel free to contact our support team. We're always here to help you grow your business!</p>

    <p>Best regards,<br>
    The Vendurhub Team</p>
</body>
</html>
