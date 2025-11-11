<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: #fff; padding: 40px 20px; text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;">💰</div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Refund Processed</h1>
        </div>

        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; margin-bottom: 20px;">Hi <strong>{{ $customerName }}</strong>,</p>

            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Your refund for order #{{ $orderNumber }} has been successfully processed.
            </p>

            <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 8px; padding: 25px; margin-bottom: 30px; color: #fff; text-align: center;">
                <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Refund Amount</p>
                <h2 style="margin: 0; font-size: 36px; font-weight: 700;">{{ $refundAmount }}</h2>
                <p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.9; text-transform: uppercase;">{{ $refundType }} REFUND</p>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">Refund Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Order Number:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Original Amount:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $originalAmount }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Refund Method:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $refundMethod }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Processed Date:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $refundedDate }}</td>
                    </tr>
                </table>
            </div>

            <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #004085; font-weight: 600;">⏰ Processing Time</p>
                <p style="margin: 10px 0 0 0; color: #004085; font-size: 14px;">
                    The refund will appear in your account within {{ $processingTime }}.
                </p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Questions about your refund?</p>
                <p style="margin: 0;">
                    <a href="mailto:{{ config('mail.from.address') }}" style="color: #17a2b8; text-decoration: none; font-weight: 600;">Contact Support</a>
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
