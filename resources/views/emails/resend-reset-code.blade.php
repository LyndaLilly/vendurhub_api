<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Reset</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f5ff;
        color: #0d0d0d;
        margin: 0;
        padding: 0;
    }

    .email-container {
        max-width: 600px;
        margin: 40px auto;
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .email-header {
        background-color: #024da0;
        color: #ffffff;
        padding: 20px;
        text-align: center;
    }

    .email-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: bold;
    }

    .email-body {
        padding: 30px 20px;
        text-align: center;
    }

    .email-body p {
        font-size: 16px;
        margin: 10px 0;
    }

    .code-box {
        font-size: 20px;
        font-weight: bold;
        color: #ff1744;
        background-color: #cde4fe;
        padding: 15px 25px;
        margin: 20px 0;
        border-radius: 8px;
        display: inline-block;
        letter-spacing: 3px;
    }

    .footer {
        background-color: #fafafa;
        color: #555555;
        text-align: center;
        font-size: 14px;
        padding: 15px 20px;
    }

    @media screen and (max-width: 480px) {
        .email-container {
            width: 90%;
        }
        .code-box {
            font-size: 28px;
            padding: 12px 20px;
        }
    }
</style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <h1>VENDURHUB</h1>
    </div>

    <div class="email-body">
        <p>Hello {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }},</p>

        <p>You requested a password reset. Use the code below to reset your password:</p>

        <div class="code-box">{{ $code }}</div>

        <p>This code will expire in 15 minutes.</p>
        <p>If you did not request this, please ignore this email.</p>
    </div>

    <div class="footer">
        &copy; 2026 VENDURHUB. All rights reserved.
    </div>
</div>
</body>
</html>
