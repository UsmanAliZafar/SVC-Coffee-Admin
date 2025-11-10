<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">✓</div>
            <h1 style="margin: 0; font-size: 24px; font-weight: 600;">Order Confirmed</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">{{ config('app.name') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <!-- Priority Badge -->
            @if(isset($notification->priority) && in_array($notification->priority, ['urgent', 'high']))
            <div style="text-align: center; margin-bottom: 20px;">
                <span style="display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase;
                    {{ $notification->priority === 'urgent' ? 'background-color: #dc3545; color: #ffffff;' : 'background-color: #ffc107; color: #333333;' }}">
                    {{ strtoupper($notification->priority) }} PRIORITY
                </span>
            </div>
            @endif

            <!-- Success Alert Box -->
            <div style="background-color: #d4edda; border-left: 4px solid #5B914C; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #155724;">
                    <i class="bi bi-check-circle-fill"></i> Order has been confirmed and is ready for processing
                </strong>
            </div>

            <!-- Main Message -->
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Hi <strong>{{ $admin->name }}</strong>,
            </p>
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                {{ $notification->message }}
            </p>

            <!-- Order Details -->
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px; border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-receipt"></i> Order Details
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 12px 0; color: #666; font-weight: 600; width: 45%;">Order Number:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 700; font-size: 16px;">
                            #{{ $data['order_number'] ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Order Status:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px; background-color: #5B914C; color: #ffffff; border-radius: 4px; font-size: 13px; font-weight: 600;">
                                CONFIRMED
                            </span>
                        </td>
                    </tr>
                    @if(isset($data['customer_name']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Customer:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ $data['customer_name'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['customer_email']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Email:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            <a href="mailto:{{ $data['customer_email'] }}" style="color: #5B914C; text-decoration: none;">
                                {{ $data['customer_email'] }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['customer_phone']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Phone:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            <a href="tel:{{ $data['customer_phone'] }}" style="color: #5B914C; text-decoration: none;">
                                {{ $data['customer_phone'] }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['total_amount']))
                    <tr style="border-top: 2px solid #5B914C;">
                        <td style="padding: 12px 0; color: #333; font-weight: 700; font-size: 15px;">Total Amount:</td>
                        <td style="padding: 12px 0; color: #5B914C; text-align: right; font-weight: 700; font-size: 18px;">
                            {{ $data['total_amount'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['payment_method']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Payment Method:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ ucfirst($data['payment_method']) }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['payment_status']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Payment Status:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px;
                                {{ $data['payment_status'] === 'paid' ? 'background-color: #28a745; color: #ffffff;' : 'background-color: #ffc107; color: #333;' }}
                                border-radius: 4px; font-size: 13px; font-weight: 600;">
                                {{ strtoupper($data['payment_status']) }}
                            </span>
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Confirmed At:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ $notification->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Order Items (if provided) -->
            @if(isset($data['items']) && is_array($data['items']) && count($data['items']) > 0)
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-box-seam"></i> Order Items
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    @foreach($data['items'] as $index => $item)
                    <tr style="{{ $index > 0 ? 'border-top: 1px solid #dee2e6;' : '' }}">
                        <td style="padding: 10px 0; color: #333;">
                            <strong>{{ $item['name'] ?? 'Product' }}</strong>
                            @if(isset($item['sku']))
                            <br><small style="color: #999;">SKU: {{ $item['sku'] }}</small>
                            @endif
                        </td>
                        <td style="padding: 10px 0; color: #666; text-align: center; white-space: nowrap;">
                            Qty: {{ $item['quantity'] ?? 1 }}
                        </td>
                        <td style="padding: 10px 0; color: #333; text-align: right; white-space: nowrap; font-weight: 600;">
                            {{ $item['price'] ?? '' }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            <!-- Shipping Information (if provided) -->
            @if(isset($data['shipping_address']) || isset($data['shipping_method']))
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-truck"></i> Shipping Information
                </h3>
                @if(isset($data['shipping_method']))
                <p style="margin: 0 0 10px 0; color: #666;">
                    <strong>Shipping Method:</strong> {{ $data['shipping_method'] }}
                </p>
                @endif
                @if(isset($data['shipping_address']))
                <p style="margin: 0; color: #666; line-height: 1.6;">
                    <strong>Shipping Address:</strong><br>
                    {{ $data['shipping_address'] }}
                </p>
                @endif
                @if(isset($data['estimated_delivery']))
                <p style="margin: 10px 0 0 0; color: #666;">
                    <strong>Estimated Delivery:</strong> {{ $data['estimated_delivery'] }}
                </p>
                @endif
            </div>
            @endif

            <!-- Next Steps -->
            <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin-top: 25px; border-radius: 4px;">
                <strong style="color: #004085;">
                    <i class="bi bi-info-circle-fill"></i> Next Steps:
                </strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #004085;">
                    <li>Order is now in processing queue</li>
                    <li>Prepare items for packing</li>
                    @if(isset($data['payment_status']) && $data['payment_status'] !== 'paid')
                    <li><strong>⚠️ Payment verification may be required</strong></li>
                    @endif
                    <li>Update shipping information once ready</li>
                    <li>Customer will be notified of dispatch</li>
                </ul>
            </div>

            <!-- Action Button -->
            @if(isset($notification->action_url) && $notification->action_url)
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 35px; background-color: #5B914C; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background-color 0.3s;">
                    View Full Order Details →
                </a>
            </div>
            @endif

            <div style="height: 1px; background-color: #e9ecef; margin: 25px 0;"></div>

            <!-- Additional Notes -->
            @if(isset($data['admin_notes']) && $data['admin_notes'])
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <strong style="color: #856404;">
                    <i class="bi bi-sticky-fill"></i> Admin Notes:
                </strong>
                <p style="margin: 10px 0 0 0; color: #856404;">
                    {{ $data['admin_notes'] }}
                </p>
            </div>
            @endif

            <!-- Personal Message -->
            <div style="text-align: center; color: #999999; font-size: 14px;">
                <p style="margin: 5px 0;">This notification was sent based on your preferences.</p>
                <p style="margin: 5px 0;">Please take necessary actions to process this order promptly.</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 25px 20px; text-align: center; font-size: 13px; color: #666; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #333;">{{ config('app.name') }}</p>
            <p style="margin: 0 0 15px 0;">
                <a href="{{ config('app.url') }}" style="color: #5B914C; text-decoration: none;">{{ config('app.url') }}</a>
            </p>
            <p style="margin: 0 0 10px 0;">
                @if(Route::has('admin.notifications.settings'))
                <a href="{{ route('admin.notifications.settings') }}" style="color: #5B914C; text-decoration: none; margin: 0 10px;">Manage Notification Settings</a> |
                @endif
                @if(Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" style="color: #5B914C; text-decoration: none; margin: 0 10px;">Admin Dashboard</a>
                @endif
                @if(Route::has('admin.orders.index'))
                | <a href="{{ route('admin.orders.index') }}" style="color: #5B914C; text-decoration: none; margin: 0 10px;">All Orders</a>
                @endif
            </p>
            <div style="height: 1px; background-color: #dee2e6; margin: 15px 0;"></div>
            <p style="margin: 10px 0 5px 0; font-size: 12px; color: #999;">
                This is an automated notification. Please do not reply to this email.
            </p>
            <p style="margin: 5px 0 0 0; font-size: 11px; color: #aaa;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
