<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Out of Stock Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">🚨 OUT OF STOCK</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Urgent Action Required</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #721c24;">⚠️ CRITICAL: Product is completely out of stock!</strong>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Product Name:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right; font-weight: 700;">
                            {{ $data['product_name'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @if(isset($data['product_sku']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">SKU:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right; font-family: monospace;">
                            {{ $data['product_sku'] }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Current Stock:</td>
                        <td style="padding: 10px 0; text-align: right;">
                            <span style="color: #dc3545; font-weight: 700; font-size: 22px;">0</span>
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Alert Time:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right;">
                            {{ now()->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Urgent Action Box -->
            <div style="background-color: #fff3cd; border: 2px solid #dc3545; padding: 20px; margin-top: 25px; border-radius: 6px;">
                <h3 style="margin: 0 0 15px 0; color: #dc3545;">🚨 IMMEDIATE ACTIONS REQUIRED:</h3>
                <ol style="margin: 0; padding-left: 20px; color: #856404; line-height: 1.8;">
                    <li><strong>Contact supplier immediately</strong> for urgent restock</li>
                    <li><strong>Check pending orders</strong> that may be affected</li>
                    <li><strong>Update product status</strong> on website (mark as out of stock)</li>
                    <li><strong>Set up back-in-stock notifications</strong> for customers</li>
                    <li><strong>Consider alternative products</strong> to offer customers</li>
                </ol>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 35px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Manage Product Now
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
