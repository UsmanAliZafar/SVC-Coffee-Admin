<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Shipped</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">🚚 Order Shipped</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">{{ config('app.name') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #0c5460;">📦 Order has been shipped!</strong>
            </div>

            <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Shipping Details</h2>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Order Number:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right; font-weight: 700;">
                            {{ $data['order_number'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @if(isset($data['tracking_number']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Tracking Number:</td>
                        <td style="padding: 10px 0; color: #17a2b8; text-align: right; font-weight: 700; font-family: monospace;">
                            {{ $data['tracking_number'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['carrier']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Carrier:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right;">
                            {{ $data['carrier'] }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Shipped Date:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right;">
                            {{ now()->format('M d, Y') }}
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 35px; background-color: #17a2b8; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Track Order
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 13px; color: #666;">
            <p style="margin: 0; font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>
</html>
