{{-- resources/views/newsletter/verified.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Email Verified' : 'Verification Failed' }} - Newsletter</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        .icon.success {
            background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
            color: white;
            animation: scaleIn 0.5s ease-out;
        }
        .icon.error {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            animation: shake 0.5s ease-out;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #333;
        }
        h1.success {
            color: #5B914C;
        }
        h1.error {
            color: #dc3545;
        }
        p {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .info-box h3 {
            color: #5B914C;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        .info-box li {
            padding: 8px 0;
            color: #555;
            display: flex;
            align-items: center;
        }
        .info-box li:before {
            content: "✓";
            color: #5B914C;
            font-weight: bold;
            margin-right: 10px;
            font-size: 20px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 10px;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91, 145, 76, 0.3);
        }
        .button.secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        .button.secondary:hover {
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #999;
            font-size: 14px;
        }
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-10px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(10px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($success)
            {{-- Success State --}}
            <div class="icon success">
                ✓
            </div>
            <h1 class="success">Email Verified Successfully!</h1>
            <p>{{ $message }}</p>

            <div class="info-box">
                <h3>🎉 What's Next?</h3>
                <ul>
                    <li>You'll receive our latest news and updates</li>
                    <li>Be the first to know about new features</li>
                    <li>Get exclusive content and offers</li>
                    <li>Unsubscribe anytime with one click</li>
                </ul>
            </div>

            @if(isset($newsletter))
            <div style="background: #e7f3e3; padding: 15px; border-radius: 10px; margin: 20px 0;">
                <p style="margin: 0; color: #5B914C;">
                    <strong>📧 Subscribed Email:</strong> {{ $newsletter->email }}
                </p>
            </div>
            @endif

            <div>
                <a href="{{ url('/') }}" class="button">
                    🏠 Back to Home
                </a>
                <a href="{{ url('/blog') }}" class="button secondary">
                    📰 Browse Articles
                </a>
            </div>

        @else
            {{-- Error State --}}
            <div class="icon error">
                ✕
            </div>
            <h1 class="error">Verification Failed</h1>
            <p>{{ $message }}</p>

            <div class="info-box" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                <h3 style="color: #856404;">⚠️ Common Issues:</h3>
                <ul>
                    <li style="color: #856404;">The verification link may have expired</li>
                    <li style="color: #856404;">The link may have already been used</li>
                    <li style="color: #856404;">The email might already be verified</li>
                </ul>
            </div>

            <div>
                {{-- <a href="{{ route('newsletter.resend') }}" class="button">
                    📧 Resend Verification Email
                </a> --}}
                <a href="{{ config('app.front_url') }}" class="button secondary">
                    🏠 Back to Home
                </a>
            </div>
        @endif

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="font-size: 12px; margin-top: 10px;">
                Need help? Contact us at {{ config('mail.from.address') }}
            </p>
        </div>
    </div>
</body>
</html>
