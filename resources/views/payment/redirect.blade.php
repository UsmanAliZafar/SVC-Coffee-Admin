<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Payment Gateway...</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
            color: white;
        }
        .loader {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            max-width: 500px;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #5B914C;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            margin-bottom: 10px;
            font-size: 24px;
        }
        p {
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .payment-info {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            text-align: left;
        }
        .payment-info div {
            margin-bottom: 8px;
        }
        .payment-info strong {
            display: inline-block;
            width: 100px;
        }
        .countdown {
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <h2>Redirecting to Payment Gateway</h2>
        <p>Please wait, do not close this window...</p>

        <div class="payment-info">
            <div><strong>Payment ID:</strong> {{ $payment_id }}</div>
            <div><strong>Track ID:</strong> {{ $track_id }}</div>
            <div><strong>Status:</strong> Pending</div>
        </div>

        <div class="countdown">
            Redirecting in <span id="countdown">2</span> seconds...
        </div>
    </div>

    <script>
        // Countdown timer
        let seconds = 2;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds--;
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }
            if (seconds <= 0) {
                clearInterval(timer);
            }
        }, 1000);

        // Auto-redirect to payment page
        setTimeout(function() {
            console.log('Redirecting to:', "{{ $payment_url }}");
            window.location.href = "{{ $payment_url }}";
        }, 2000);
    </script>
</body>
</html>
