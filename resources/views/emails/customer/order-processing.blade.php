<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #fff; padding: 40px 20px; text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;">⚙️</div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">We're Preparing Your Order!</h1>
        </div>

        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; margin-bottom: 20px;">Hi <strong>{{ $customerName }}</strong>,</p>

            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Good news! Your order #{{ $orderNumber }} is now being processed. We're carefully preparing your items for shipment.
            </p>

            <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); border-radius: 8px; padding: 20px; margin-bottom: 30px; color: #fff; text-align: center;">
                <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Order Number</p>
                <h2 style="margin: 0; font-size: 28px; font-weight: 700;">#{{ $orderNumber }}</h2>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Order Status</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Current Status:</td>
                        <td style="padding: 8px 0; text-align: right;">
                            <span style="padding: 4px 12px; background-color: #007bff; color: #fff; border-radius: 4px; font-size: 12px; font-weight: 600;">PROCESSING</span>
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Processing Started:</td>
                        <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $processingDate }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 0; color: #666;">Est. Ship Date:</td>
                        <td style="padding: 8px 0; color: #5B914C; text-align: right; font-weight: 700;">{{ $estimatedShipDate }}</td>
                    </tr>
                </table>
            </div>

            <div style="background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #0c5460; font-weight: 600;">What's Happening Now?</p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #0c5460;">
                    <li>Quality checking your items</li>
                    <li>Preparing packaging materials</li>
                    <li>Scheduling pickup with carrier</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $trackingUrl }}" style="display: inline-block; padding: 14px 30px; background-color: #5B914C; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Track Order Status
                </a>
            </div>
        </div>

        <div style="background-color: #f8f9fa; padding: 30px 20px; text-align: center; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #333;">{{ config('app.name') }}</p>
            <p style="margin: 0; font-size: 12px; color: #999;">© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
