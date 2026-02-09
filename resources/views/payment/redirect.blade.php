<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Payment Gateway...</title>
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
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <h2>Redirecting to Payment Gateway...</h2>
        <p>Please wait...</p>
    </div>

    <script>
        // Payment data as JSON
        const paymentData = {
            "id": "{{ $tranportal_id }}",
            "trandata": "{{ $trandata }}",
            "responseURL": "{{ $response_url }}",
            "errorURL": "{{ $error_url }}"
        };

        console.log('Sending payment request:', paymentData);

        // Send JSON POST request
        fetch("{{ $payment_url }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(paymentData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(html => {
            // Replace current page with payment gateway response
            document.open();
            document.write(html);
            document.close();
        })
        .catch(error => {
            console.error('Payment request failed:', error);
            document.body.innerHTML = '<div class="loader"><h2>Error</h2><p>Failed to redirect to payment gateway. Please try again.</p></div>';
        });
    </script>
</body>
</html>
