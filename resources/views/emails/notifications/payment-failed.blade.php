<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">💳 Payment Failed</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">{{ config('app.name') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #721c24;">⚠️ A payment attempt has failed</strong>
            </div>

            <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Payment Details</h2>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Order Number:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right; font-weight: 700;">
                            {{ $data['order_number'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @if(isset($data['amount']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Amount:</td>
                        <td style="padding: 10px 0; color: #dc3545; text-align: right; font-weight: 700; font-size: 18px;">
                            {{ $data['amount'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['reason']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Failure Reason:</td>
                        <td style="padding: 10px 0; color: #dc3545; text-align: right; font-weight: 600;">
                            {{ $data['reason'] }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Failed At:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right;">
                            {{ now()->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Common Reasons Box -->
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 25px; border-radius: 4px;">
                <strong style="color: #856404;">❓ Common Reasons for Payment Failure:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404;">
                    <li>Insufficient funds in account</li>
                    <li>Card declined by bank</li>
                    <li>Incorrect card details</li>
                    <li>Card expired</li>
                    <li>Bank security restrictions</li>
                </ul>
            </div>

            <!-- Action Items -->
            <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-top: 20px; border-radius: 4px;">
                <strong style="color: #0c5460;">📋 Next Steps:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #0c5460;">
                    <li>Contact customer to resolve payment issue</li>
                    <li>Suggest alternative payment methods</li>
                    <li>Check if order should be put on hold</li>
                    <li>Monitor for additional payment attempts</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 35px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    View Order Details
                </a>
            </div>
        </div>
        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 13px; color: #666;">
            <p style="margin: 0 0 10px 0;">
                <a href="{{ route('admin.orders.index') }}" style="color: #dc3545; text-decoration: none;">All Orders</a> |
                <a href="{{ route('admin.notifications.settings') }}" style="color: #dc3545; text-decoration: none;">Settings</a>
            </p>
            <p style="margin: 0; font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>
</html>
