<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff6f00 0%, #e65100 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #ff6f00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .error-icon svg {
            width: 50px;
            height: 50px;
            fill: white;
        }

        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .error-details {
            background: #fff3e0;
            border-left: 4px solid #ff6f00;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: left;
        }

        .error-details strong {
            color: #e65100;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .error-details p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .error-code {
            background: #f5f5f5;
            padding: 10px 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-top: 10px;
            color: #333;
        }

        .troubleshooting {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .troubleshooting h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .troubleshooting-steps {
            color: #666;
            font-size: 14px;
            line-height: 1.8;
        }

        .troubleshooting-steps li {
            margin-bottom: 12px;
            padding-left: 10px;
        }

        .troubleshooting-steps li::marker {
            content: "→ ";
            font-weight: bold;
            color: #ff6f00;
        }

        .support-card {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .support-card h3 {
            color: #1976d2;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .support-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .support-card a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 600;
        }

        .support-card a:hover {
            text-decoration: underline;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #5B914C;
            color: white;
        }

        .btn-primary:hover {
            background: #4a7a3d;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .emergency-notice {
            margin-top: 20px;
            padding: 15px;
            background: #ffebee;
            border-radius: 8px;
            color: #c62828;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <svg viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13" stroke="white" stroke-width="2" stroke-linecap="round"></line>
                <line x1="12" y1="17" x2="12.01" y2="17" stroke="white" stroke-width="2" stroke-linecap="round"></line>
            </svg>
        </div>

        <h1>Something Went Wrong</h1>
        <p class="subtitle">We encountered an unexpected error while processing your payment</p>

        @if(session('error'))
        <div class="error-details">
            <strong>Error Message:</strong>
            <p>{{ session('error') }}</p>

            @if(session('error_code'))
            <div class="error-code">
                Error Code: {{ session('error_code') }}
            </div>
            @endif
        </div>
        @else
        <div class="error-details">
            <strong>Unknown Error</strong>
            <p>An unexpected error occurred during the payment process. This could be due to a temporary technical issue.</p>
        </div>
        @endif

        <div class="troubleshooting">
            <h3>🔧 Troubleshooting Steps</h3>
            <ol class="troubleshooting-steps">
                <li><strong>Check your internet connection</strong> and try again</li>
                <li><strong>Clear your browser cache</strong> and cookies</li>
                <li><strong>Try a different browser</strong> or device</li>
                <li><strong>Wait a few minutes</strong> and retry the payment</li>
                <li><strong>Contact your bank</strong> if you were charged but payment failed</li>
            </ol>
        </div>

        <div class="support-card">
            <h3>📞 Need Assistance?</h3>
            <p>
                If this problem persists, please contact our support team with the error code shown above.
                We're here to help you complete your payment.
            </p>
            <p style="margin-top: 10px;">
                <strong>Email:</strong> <a href="mailto:support@example.com">support@example.com</a><br>
                <strong>Phone:</strong> <a href="tel:+966123456789">+966 12 345 6789</a>
            </p>
        </div>

        <div class="emergency-notice">
            ⚠️ <strong>Important:</strong> If you were debited but the payment shows as failed,
            the amount will be refunded within 5-7 business days. Please check with your bank.
        </div>

        <div class="btn-group">
            <a href="{{ route('arb.payment.form') }}" class="btn btn-primary">Try Again</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
</body>
</html>
