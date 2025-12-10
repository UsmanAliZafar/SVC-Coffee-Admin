{{-- resources/views/emails/newsletter-verification.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Newsletter Subscription</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 48px;
            font-weight: bold;
        }
        h1 {
            color: #5B914C;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin: 30px 0;
        }
        .verify-button {
            display: inline-block;
            background-color: #5B914C;
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .verify-button:hover {
            background-color: #4a7a3d;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .alternative-link {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
            color: #6c757d;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .info-box {
            background-color: #e7f3e3;
            border-left: 4px solid #5B914C;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                📧
            </div>
            <h1>Verify Your Email Address</h1>
        </div>

        <div class="content">
            <p>Hello{{ $newsletter->name ? ' ' . $newsletter->name : '' }},</p>

            <p>Thank you for subscribing to our newsletter! We're excited to have you join our community.</p>

            <p>To complete your subscription and start receiving our latest updates, please verify your email address by clicking the button below:</p>

            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="verify-button">
                    ✓ Verify Email Address
                </a>
            </div>

            <div class="info-box">
                <strong>📌 What happens after verification?</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Your subscription will be activated</li>
                    <li>You'll receive our latest news and updates</li>
                    <li>You can unsubscribe at any time</li>
                </ul>
            </div>

            <p><strong>If the button doesn't work</strong>, copy and paste this link into your browser:</p>

            <div class="alternative-link">
                {{ $verificationUrl }}
            </div>

            <div class="warning-box">
                ⚠️ <strong>Security Notice:</strong> This verification link will expire in 24 hours. If you didn't request this subscription, you can safely ignore this email.
            </div>
        </div>

        <div class="footer">
            <p><strong>Need help?</strong> Contact our support team at {{ config('mail.from.address') }}</p>
            <p style="margin: 10px 0; font-size: 12px;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <p style="font-size: 12px; color: #999;">
                This email was sent to {{ $newsletter->email }} because you requested to subscribe to our newsletter.
            </p>
        </div>
    </div>
</body>
</html>
