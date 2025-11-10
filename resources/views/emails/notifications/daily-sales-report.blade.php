<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 700px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">📊 Daily Sales Report</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">{{ $data['date'] ?? now()->format('F d, Y') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <p style="font-size: 16px; color: #666; margin-bottom: 30px;">
                Good morning {{ $admin->name }}! Here's your daily sales summary for yesterday.
            </p>

            <!-- Key Metrics -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px;">
                <!-- Total Orders -->
                <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #ffffff; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">{{ $data['total_orders'] ?? 0 }}</div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Orders</div>
                </div>

                <!-- Total Revenue -->
                <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: #ffffff; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">{{ $data['total_revenue'] ?? '$0.00' }}</div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Revenue</div>
                </div>

                <!-- Average Order Value -->
                <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: #ffffff; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; margin-bottom: 5px;">{{ $data['average_order_value'] ?? '$0.00' }}</div>
                    <div style="font-size: 14px; opacity: 0.9;">Avg Order Value</div>
                </div>

                <!-- Completion Rate -->
                <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #333; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">{{ $data['completion_rate'] ?? '0%' }}</div>
                    <div style="font-size: 14px; opacity: 0.9;">Completion Rate</div>
                </div>
            </div>

            <!-- Order Status Breakdown -->
            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px;">📦 Order Status</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #666;">Completed:</td>
                        <td style="padding: 10px 0; color: #28a745; text-align: right; font-weight: 600;">
                            {{ $data['completed_orders'] ?? 0 }}
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666;">Pending:</td>
                        <td style="padding: 10px 0; color: #ffc107; text-align: right; font-weight: 600;">
                            {{ $data['pending_orders'] ?? 0 }}
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666;">Cancelled:</td>
                        <td style="padding: 10px 0; color: #dc3545; text-align: right; font-weight: 600;">
                            {{ $data['cancelled_orders'] ?? 0 }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Top Products -->
            @if(isset($data['top_products']) && !empty($data['top_products']))
            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px;">⭐ Top Selling Products</h3>
                <p style="margin: 0; color: #666; line-height: 1.8;">
                    {{ $data['top_products'] }}
                </p>
            </div>
            @endif

            <!-- Insights -->
            <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-top: 25px; border-radius: 4px;">
                <strong style="color: #0c5460;">💡 Quick Insights:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #0c5460;">
                    @if(($data['revenue_raw'] ?? 0) > 1000)
                    <li>Strong sales day with revenue exceeding $1,000! 🎉</li>
                    @endif
                    @if(($data['pending_orders'] ?? 0) > 5)
                    <li>{{ $data['pending_orders'] }} pending orders need attention</li>
                    @endif
                    @if(($data['cancelled_orders'] ?? 0) > 2)
                    <li>Higher than usual cancellations - review reasons</li>
                    @endif
                    <li>Keep up the great work! 💪</li>
                </ul>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('admin.orders.index') }}" style="display: inline-block; padding: 14px 35px; background-color: #5B914C; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    View Full Report
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 13px; color: #666;">
            <p style="margin: 0 0 10px 0;">
                <a href="{{ route('admin.reports.index') }}" style="color: #5B914C; text-decoration: none;">View All Reports</a> |
                <a href="{{ route('admin.notifications.settings') }}" style="color: #5B914C; text-decoration: none;">Notification Settings</a>
            </p>
            <p style="margin: 0; font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ config('app.name') }}. Automated daily report.
            </p>
        </div>
    </div>
</body>
</html>
