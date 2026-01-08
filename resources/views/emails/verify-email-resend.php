<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Email Resend Verification</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f7f7;
            padding: 0;
            margin: 0;  
        }
        .container {
            max-width: 550px;
            background: #ffffff;
            margin: 30px auto;
            border-radius: 12px;
            padding: 30px 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h1 {
            color: #ff9900;
            margin-top: 0;
            font-size: 22px;
            text-align: center;
        }
        .code-box {
            background: #0044cc;
            color: #ffffff;
            padding: 15px;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
        }
        p {
            font-size: 15px;
            color: #444444;
            line-height: 1.6;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #777;
            margin-top: 25px;
        }
        .footer a {
            color: #0044cc;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Verify Your Email</h1>

        <p>Hello {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }},</p>

        <p>
            Thank you for registering with <strong>VendureHub</strong>.<br>
            Use the verification code below to complete your account setup:
        </p>

        <div class="code-box">
            {{ $code }}
        </div>

        <p>
            This verification code will expire in <strong>15 minutes</strong>.
            If you did not request this, you can safely ignore this email.
        </p>

        <p>
            For assistance, reach out to us at:<br>
            <a href="mailto:support@vendurhub.com">support@vendurhub.com</a>
        </p>

        <p class="footer">
            © 2025 VendurHub. All rights reserved.
        </p>
    </div>

</body>
</html>
