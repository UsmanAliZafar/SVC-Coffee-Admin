<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #fff; padding: 40px 20px; text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;">⚠️</div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Payment Failed</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.95;">Action Required</p>
        </div>

        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; margin-bottom: 20px;">Hi <strong>{{ $customerName }}</strong>,</p>

            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                We were unable to process your payment for order #{{ $orderNumber }}. Please review the details below and try again.
            </p>

            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #721c24; font-weight: 600;">Payment Error</p>
                <p style="margin: 10px 0 0 0; color: #721c24; font-size: 14px;">{{ $failureReason }}</p>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">Order Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Order Number:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Payment Amount:</td>
                        <td style="padding: 8px 0; color: #dc3545; text-align: right; font-weight: 700; font-size: 18px;">{{ $paymentAmount }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Payment Method:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $paymentMethod }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Failed Date:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $failureDate }}</td>
                    </tr>
                </table>
            </div>

            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #856404; font-weight: 600;">Common Reasons for Payment Failure:</p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404; font-size: 14px;">
                    <li>Insufficient funds</li>
                    <li>Incorrect card details</li>
                    <li>Card expired</li>
                    <li>Bank declined the transaction</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $retryUrl }}" style="display: inline-block; padding: 16px 40px; background-color: #5B914C; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 16px;">
                    Retry Payment Now →
                </a>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Need help with payment?</p>
                <p style="margin: 0;">
                    <a href="{{ $supportUrl }}" style="color: #dc3545; text-decoration: none; font-weight: 600;">Contact Support</a>
                </p>
            </div>
        </div>

        <div style="background-color: #f8f9fa; padding: 30px 20px; text-align: center; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #333;">{{ config('app.name') }}</p>
            <p style="margin: 0; font-size: 12px; color: #999;">© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
