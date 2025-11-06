<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #333; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">⚠️ Low Stock Alert</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.8;">{{ config('app.name') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #856404;">📦 Product stock is running low!</strong>
            </div>

            <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Stock Alert Details</h2>

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
                            <span style="color: #dc3545; font-weight: 700; font-size: 18px;">{{ $data['current_stock'] ?? '0' }}</span>
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Low Stock Threshold:</td>
                        <td style="padding: 10px 0; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['threshold'] ?? 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Stock Level Indicator -->
            @php
                $currentStock = $data['current_stock'] ?? 0;
                $threshold = $data['threshold'] ?? 10;
                $percentage = $threshold > 0 ? ($currentStock / $threshold) * 100 : 0;
                $percentage = min($percentage, 100);

                $barColor = '#dc3545'; // Red
                if ($percentage > 50) $barColor = '#ffc107'; // Yellow
                if ($percentage > 75) $barColor = '#28a745'; // Green
            @endphp

            <div style="margin: 25px 0;">
                <p style="margin: 0 0 10px 0; color: #666; font-weight: 600;">Stock Level:</p>
                <div style="width: 100%; height: 30px; background-color: #e9ecef; border-radius: 15px; overflow: hidden;">
                    <div style="width: {{ $percentage }}%; height: 100%; background-color: {{ $barColor }}; transition: width 0.3s ease;"></div>
                </div>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">{{ round($percentage) }}% of threshold</p>
            </div>

            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin-top: 25px; border-radius: 4px;">
                <strong style="color: #155724;">💡 Recommended Actions:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #155724;">
                    <li>Review sales trends for this product</li>
                    <li>Place a restock order with suppliers</li>
                    <li>Consider adjusting inventory thresholds</li>
                    <li>Check for pending orders</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 35px; background-color: #ffc107; color: #333; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Manage Product Stock
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 13px; color: #666;">
            <p style="margin: 0 0 10px 0;">
                <a href="{{ route('admin.products.index') }}" style="color: #ffc107; text-decoration: none;">View All Products</a> |
                <a href="{{ route('admin.notifications.settings') }}" style="color: #ffc107; text-decoration: none;">Notification Settings</a>
            </p>
            <p style="margin: 0; font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>
</html>
