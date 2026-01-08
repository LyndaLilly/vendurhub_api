<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Reset</title>
<style>
    :root {
        --primary: #024da0;
        --primary-hover: #4ea0fd;
        --hover: #cde4fe;
        --primary-active: #0044cc;
        --primary-light: rgba(0,68,204,0.2);
        --secondary: #ff1744;
        --secondary-hover: #e60039;
        --secondary-active: #b3002d;
        --accent: #FF9900;
        --accent-hover: #ffd54f;
        --accent-active: #ffb300;
        --background: #ffffff;
        --section-bg: #f0f5ff;
        --section-alt: #fafafa;
        --border: #e0e0e0;
        --text: #0d0d0d;
        --text-muted: #555555;
        --success: #00e676;
        --error: #ff3d00;
        --white: #ffffff;
        --gray: #999999;
        --bg2: #d5d9d9;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: var(--section-bg);
        color: var(--text);
        margin: 0;
        padding: 0;
    }

    .email-container {
        max-width: 600px;
        margin: 40px auto;
        background-color: var(--background);
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .email-header {
        background-color: var(--primary);
        color: var(--white);
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
        color: var(--secondary);
        background-color: var(--hover);
        padding: 15px 25px;
        margin: 20px 0;
        border-radius: 8px;
        display: inline-block;
        letter-spacing: 3px;
    }

    .footer {
        background-color: var(--section-alt);
        color: var(--text-muted);
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
