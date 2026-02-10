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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
            position: relative;
        }

        .close-button {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f0f0f0;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 20px;
            color: #666;
        }

        .close-button:hover {
            background: #e0e0e0;
            transform: rotate(90deg);
        }

        .countdown-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #dc3545;
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

        .error-icon svg {
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

        .error-details {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #fed7d7;
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

        .error-message {
            color: #dc3545;
            font-size: 16px;
            font-weight: 600;
            background: #fff5f5;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .help-note {
            margin-top: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
            color: #1565c0;
            font-size: 14px;
        }

        .help-note a {
            color: #1565c0;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Countdown Badge -->
        <div class="countdown-badge">
            Auto-closing in <span id="countdown">10</span>s
        </div>

        <!-- Close Button -->
        <button class="close-button" onclick="closeWindow()" title="Close window">
            ×
        </button>

        <div class="error-icon">
            <svg viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </div>

        <h1>Payment {{ ucfirst(strtolower($status ?? 'Failed')) }}</h1>
        <p class="subtitle">{{ $subtitle ?? 'Your payment could not be processed' }}</p>

        @if($message)
        <div class="error-message">
            {{ $message }}
        </div>
        @endif

        <div class="error-details">
            @if($trackId)
            <div class="detail-row">
                <span class="detail-label">Transaction Reference</span>
                <span class="detail-value">{{ $trackId }}</span>
            </div>
            @endif

            @if(isset($errorCode))
            <div class="detail-row">
                <span class="detail-label">Error Code</span>
                <span class="detail-value">{{ $errorCode }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="detail-label">Date & Time</span>
                <span class="detail-value">{{ now()->format('M d, Y h:i A') }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" style="color: #dc3545;">{{ ucfirst($status ?? 'Failed') }}</span>
            </div>
        </div>

        @if($canRetry ?? true)
        <div class="help-note">
            💡 You can retry your payment or choose a different payment method.
        </div>
        @else
        <div class="help-note">
            ⚠️ Please contact our support team for assistance with this transaction.
        </div>
        @endif

        <div class="btn-group">
            @if($canRetry ?? true)
            <button onclick="retryPayment()" class="btn btn-primary">
                Try Again
            </button>
            @endif
            <button onclick="closeWindow()" class="btn btn-secondary">
                Close Window
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-close countdown timer
            let countdown = 10;
            const countdownElement = document.getElementById('countdown');
            const container = document.querySelector('.success-container'); // Change to '.error-container' for failed page
            const countdownBadge = document.querySelector('.countdown-badge');
            let countdownInterval;
            let isPaused = false;

            function startCountdown() {
                countdownInterval = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;

                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        closeWindow();
                    }
                }, 1000);
            }

            // Close window function
            function closeWindow() {
                window.close();

                if (window.opener) {
                    window.opener.postMessage({
                        type: 'PAYMENT_SUCCESS', // Change to 'PAYMENT_FAILED' for failed page
                        data: @json(session('transaction_data', []))
                    }, '*');
                    window.close();
                } else {
                    window.location.href = '{{ url('/') }}'; // Change to '/checkout' for failed page
                }
            }

            // Retry payment function (Only for failed page)
            function retryPayment() {
                if (window.opener) {
                    window.opener.postMessage({
                        type: 'PAYMENT_RETRY',
                        data: {
                            trackId: '{{ $trackId ?? "" }}'
                        }
                    }, '*');
                    window.close();
                } else {
                    window.location.href = '{{ url('/checkout') }}';
                }
            }

            // Make functions global so onclick handlers can access them
            window.closeWindow = closeWindow;
            window.retryPayment = retryPayment; // Only for failed page

            // Start countdown
            startCountdown();

            // Pause/resume on hover
            container.addEventListener('mouseenter', () => {
                if (!isPaused) {
                    clearInterval(countdownInterval);
                    isPaused = true;
                    countdownBadge.innerHTML = 'Auto-close paused';
                }
            });

            container.addEventListener('mouseleave', () => {
                if (isPaused && countdown > 0) {
                    isPaused = false;
                    countdownBadge.innerHTML = `Auto-closing in <span id="countdown">${countdown}</span>s`;
                    startCountdown();
                }
            });

            // Listen for ESC key to close
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeWindow();
                }
            });
        });
    </script>
</body>
</html>
