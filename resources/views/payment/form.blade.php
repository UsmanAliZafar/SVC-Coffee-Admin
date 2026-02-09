<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment - Al Rajhi Bank</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-header h1 {
            color: #5B914C;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .payment-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #5B914C;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .amount-display {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
        }

        .amount-display .label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .amount-display .amount {
            color: #5B914C;
            font-size: 32px;
            font-weight: bold;
        }

        .amount-display .currency {
            color: #999;
            font-size: 18px;
        }

        .btn-pay {
            width: 100%;
            padding: 15px;
            background: #5B914C;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-pay:hover {
            background: #4a7a3d;
        }

        .btn-pay:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .payment-methods img {
            height: 30px;
            opacity: 0.7;
        }

        .security-note {
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 12px;
        }

        .security-note svg {
            width: 14px;
            height: 14px;
            margin-right: 5px;
            vertical-align: middle;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #5B914C;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>Secure Payment</h1>
            <p>Al Rajhi Bank Payment Gateway</p>
        </div>

        @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form id="paymentForm" action="{{ route('arb.payment.initiate') }}" method="POST">
            @csrf

            <div class="amount-display">
                <div class="label">Total Amount</div>
                <div class="amount">
                    <span id="displayAmount">0.00</span>
                    <span class="currency">SAR</span>
                </div>
            </div>

            <div class="form-group">
                <label for="amount">Amount *</label>
                <input type="number"
                       id="amount"
                       name="amount"
                       step="0.01"
                       min="0.01"
                       required
                       value="{{ old('amount', $amount ?? '') }}"
                       placeholder="Enter amount">
            </div>

            <div class="form-group">
                <label for="customer_name">Full Name *</label>
                <input type="text"
                       id="customer_name"
                       name="customer_name"
                       required
                       value="{{ old('customer_name', $customer_name ?? 'Usman Ali Zafar') }}"
                       placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="customer_email">Email Address</label>
                <input type="email"
                       id="customer_email"
                       name="customer_email"
                       value="{{ old('customer_email', $customer_email ?? 'usmanali6062@gmail.com') }}"
                       placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label for="customer_mobile">Mobile Number</label>
                <input type="tel"
                       id="customer_mobile"
                       name="customer_mobile"
                       value="923005216426"
                       placeholder="+966 5XX XXX XXX">
            </div>

            <div class="form-group">
                <label for="invoice_id">Invoice/Order ID (Optional)</label>
                <input type="text"
                    id="invoice_id"
                    name="invoice_id"
                    value="{{ old('invoice_id', $invoice_id ?? 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6))) }}"
                    placeholder="INV-12345"
                    readonly>
            </div>

            <button type="submit" class="btn-pay" id="payBtn">
                Proceed to Payment
            </button>
        </form>

        <div class="payment-methods">
            <span style="color: #999; font-size: 12px;">Supported Payment Methods:</span>
        </div>

        <div class="security-note">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Secured by Al Rajhi Bank - PCI DSS Compliant
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        // Update display amount in real-time
        document.getElementById('amount').addEventListener('input', function(e) {
            const amount = parseFloat(e.target.value) || 0;
            document.getElementById('displayAmount').textContent = amount.toFixed(2);
        });

        // Form submission with loading state
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            // Get browser information
            const browserInfo = {
                java_enabled: navigator.javaEnabled().toString(),
                language: navigator.language || 'en',
                color_depth: screen.colorDepth.toString(),
                screen_height: screen.height.toString(),
                screen_width: screen.width.toString(),
                timezone: new Date().getTimezoneOffset().toString(),
                js_enabled: 'true'
            };

            // Add to form data
            const browserInfoField = document.createElement('input');
            browserInfoField.type = 'hidden';
            browserInfoField.name = 'browser_info';
            browserInfoField.value = JSON.stringify(browserInfo);
            this.appendChild(browserInfoField);
        });

        // Auto-format mobile number
        document.getElementById('customer_mobile').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('966')) {
                value = '+' + value;
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
