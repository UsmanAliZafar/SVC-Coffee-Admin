<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .failed-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .failed-icon {
            width: 80px;
            height: 80px;
            background: #e53935;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .failed-icon svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
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

        .error-message {
            background: #ffebee;
            border-left: 4px solid #e53935;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: left;
        }

        .error-message strong {
            color: #c62828;
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .error-message p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .transaction-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }

        .help-section {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .help-section h3 {
            color: #1976d2;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .help-list {
            text-align: left;
            color: #666;
            font-size: 14px;
            line-height: 1.8;
        }

        .help-list li {
            margin-bottom: 8px;
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

        .support-info {
            margin-top: 20px;
            padding: 15px;
            background: #fff3e0;
            border-radius: 8px;
            color: #e65100;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-icon">
            <svg viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </div>

        <h1>Payment Failed</h1>
        <p class="subtitle">Unfortunately, your payment could not be processed</p>

        @if(session('error'))
        <div class="error-message">
            <strong>Error Details:</strong>
            <p>{{ session('error') }}</p>
        </div>
        @endif

        @if(session('transaction_data'))
        <div class="transaction-info">
            @php
                $data = session('transaction_data');
            @endphp

            @if(isset($data['payment_id']) || isset($data['paymentId']))
            <div class="info-row">
                <span class="info-label">Payment ID</span>
                <span class="info-value">{{ $data['payment_id'] ?? $data['paymentId'] ?? 'N/A' }}</span>
            </div>
            @endif

            @if(isset($data['transaction_id']) || isset($data['tranid']))
            <div class="info-row">
                <span class="info-label">Transaction ID</span>
                <span class="info-value">{{ $data['transaction_id'] ?? $data['tranid'] ?? 'N/A' }}</span>
            </div>
            @endif

            @if(isset($data['error_code']))
            <div class="info-row">
                <span class="info-label">Error Code</span>
                <span class="info-value">{{ $data['error_code'] }}</span>
            </div>
            @endif

            <div class="info-row">
                <span class="info-label">Date & Time</span>
                <span class="info-value">{{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>
        @endif

        <div class="help-section">
            <h3>Common reasons for payment failure:</h3>
            <ul class="help-list">
                <li>❌ Insufficient funds in your account</li>
                <li>❌ Incorrect card details or expired card</li>
                <li>❌ Card not enabled for online transactions</li>
                <li>❌ Daily transaction limit exceeded</li>
                <li>❌ 3D Secure authentication failed</li>
                <li>❌ Card blocked or restricted by bank</li>
            </ul>
        </div>

        <div class="support-info">
            💡 <strong>Need Help?</strong> Please contact your bank or try using a different payment method.
        </div>

        <div class="btn-group">
            <a href="{{ route('arb.payment.form') }}" class="btn btn-primary">Try Again</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
</body>
</html>
