<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #5B914C;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon svg {
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

        .transaction-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }

        .detail-value {
            color: #333;
            font-size: 14px;
            font-weight: bold;
        }

        .amount-highlight {
            color: #5B914C;
            font-size: 24px;
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

        .receipt-note {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            color: #2e7d32;
            font-size: 14px;
        }

        .print-receipt {
            margin-top: 15px;
            color: #5B914C;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .print-receipt:hover {
            text-decoration: underline;
        }

        @media print {
            body {
                background: white;
            }
            .btn-group,
            .print-receipt {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1>Payment Successful!</h1>
        <p class="subtitle">Thank you for your payment</p>

        <div class="transaction-details">
            @if(session('transaction_data'))
                @php
                    $data = session('transaction_data');
                @endphp

                <div class="detail-row">
                    <span class="detail-label">Amount Paid</span>
                    <span class="detail-value amount-highlight">
                        {{ $data['amt'] ?? '0.00' }} {{ $data['currency'] ?? 'SAR' }}
                    </span>
                </div>

                @if(isset($data['tranid']))
                <div class="detail-row">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value">{{ $data['tranid'] }}</span>
                </div>
                @endif

                @if(isset($data['paymentId']))
                <div class="detail-row">
                    <span class="detail-label">Payment ID</span>
                    <span class="detail-value">{{ $data['paymentId'] }}</span>
                </div>
                @endif

                @if(isset($data['ref']))
                <div class="detail-row">
                    <span class="detail-label">Reference Number</span>
                    <span class="detail-value">{{ $data['ref'] }}</span>
                </div>
                @endif

                @if(isset($data['authCode']))
                <div class="detail-row">
                    <span class="detail-label">Authorization Code</span>
                    <span class="detail-value">{{ $data['authCode'] }}</span>
                </div>
                @endif

                @if(isset($data['paymentMethod']))
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">{{ $data['paymentMethod'] }}</span>
                </div>
                @endif

                @if(isset($data['trackid']))
                <div class="detail-row">
                    <span class="detail-label">Order Reference</span>
                    <span class="detail-value">{{ $data['trackid'] }}</span>
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Date & Time</span>
                    <span class="detail-value">{{ now()->format('M d, Y h:i A') }}</span>
                </div>
            @else
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">Payment Completed</span>
                </div>
            @endif
        </div>

        <div class="receipt-note">
            📧 A confirmation email has been sent to your registered email address.
        </div>

        <a href="#" onclick="window.print(); return false;" class="print-receipt">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print Receipt
        </a>

        <div class="btn-group">
            <a href="{{ url('/') }}" class="btn btn-primary">Back to Home</a>
            <a href="{{ route('arb.payment.form') }}" class="btn btn-secondary">New Payment</a>
        </div>
    </div>
</body>
</html>
