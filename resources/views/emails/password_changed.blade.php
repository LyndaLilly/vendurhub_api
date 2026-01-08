<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Changed | VENDURHUB</title>
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
        margin: 15px 0;
        line-height: 1.5;
    }

    .alert-box {
        background-color: #ffebee;
        color: #c62828;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px 0;
        font-weight: bold;
    }

    .footer {
        background-color: #fafafa;
        color: #555555;
        text-align: center;
        font-size: 14px;
        padding: 15px 20px;
    }

    a {
        color: #024da0;
        text-decoration: none;
    }

    @media screen and (max-width: 480px) {
        .email-container {
            width: 90%;
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

        <p>Your password has been <strong>successfully changed</strong>.</p>

        <div class="alert-box">
            If you did not perform this change, please <a href="mailto:support@vendurhub.com">contact support immediately</a>.
        </div>

        <p>Thank you for using VENDURHUB!</p>
    </div>

    <div class="footer">
        &copy; 2026 <strong>VENDURHUB.COM</strong>. All rights reserved.
    </div>
</div>
</body>
</html>
