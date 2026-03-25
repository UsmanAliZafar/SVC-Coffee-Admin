<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); color: #fff; padding: 40px 20px; text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;">✓</div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Payment Confirmed!</h1>
        </div>

        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; margin-bottom: 20px;">Hi <strong>{{ $customerName }}</strong>,</p>

            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                We've received your payment for order #{{ $orderNumber }}. Thank you for your purchase!
            </p>

            <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 8px; padding: 25px; margin-bottom: 30px; color: #fff; text-align: center;">
                <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Payment Amount</p>
                <h2 style="margin: 0; font-size: 36px; font-weight: 700;">{{ $paymentAmount }}</h2>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">Payment Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Order Number:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Payment Method:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $paymentMethod }}</td>
                    </tr>
                    @if($transactionId)
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Transaction ID:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-family: monospace; font-size: 12px;">{{ $transactionId }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Payment Date:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $paidDate }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Order Status:</td>
                        <td style="padding: 8px 0; text-align: right;">
                            <span style="padding: 4px 12px; background-color: #5B914C; color: #fff; border-radius: 4px; font-size: 12px; font-weight: 600;">{{ strtoupper($orderStatus) }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #155724; font-weight: 600;">✓ Payment Successfully Processed</p>
                <p style="margin: 10px 0 0 0; color: #155724; font-size: 14px;">
                    Your order is now being prepared for shipment. We'll send you tracking information once it ships.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
