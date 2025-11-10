<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">🛒</div>
            <h1 style="margin: 0; font-size: 24px; font-weight: 600;">New Order Received!</h1>
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

            <!-- New Order Alert Box -->
            <div style="background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #0c5460;">
                    <i class="bi bi-bell-fill"></i> A new order has just been placed on your store!
                </strong>
            </div>

            <!-- Main Message -->
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Hi <strong>{{ $admin->name }}</strong>,
            </p>
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                {{ $notification->message }}
            </p>

            <!-- Order Summary Card -->
            <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); border-radius: 8px; padding: 20px; margin-bottom: 25px; color: #ffffff; text-align: center;">
                <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Order Number</p>
                <h2 style="margin: 0 0 15px 0; font-size: 32px; font-weight: 700; letter-spacing: 1px;">
                    #{{ $data['order_number'] ?? 'N/A' }}
                </h2>
                @if(isset($data['total_amount']))
                <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Total Amount</p>
                <p style="margin: 0; font-size: 28px; font-weight: 700;">{{ $data['total_amount'] }}</p>
                @endif
            </div>

            <!-- Order Details -->
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px; border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-info-circle"></i> Order Information
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 12px 0; color: #666; font-weight: 600; width: 45%;">Order Status:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px; background-color: #ffc107; color: #333; border-radius: 4px; font-size: 13px; font-weight: 600;">
                                {{ strtoupper($data['status'] ?? 'PENDING') }}
                            </span>
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Order Date:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ $notification->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
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
                                {{ $data['payment_status'] === 'paid' ? 'background-color: #28a745; color: #ffffff;' : ($data['payment_status'] === 'pending' ? 'background-color: #ffc107; color: #333;' : 'background-color: #dc3545; color: #ffffff;') }}
                                border-radius: 4px; font-size: 13px; font-weight: 600;">
                                {{ strtoupper($data['payment_status']) }}
                            </span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Customer Information -->
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-person-fill"></i> Customer Details
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    @if(isset($data['customer_name']))
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-weight: 600; width: 35%;">Name:</td>
                        <td style="padding: 10px 0; color: #333;">
                            <strong>{{ $data['customer_name'] }}</strong>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['customer_email']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Email:</td>
                        <td style="padding: 10px 0;">
                            <a href="mailto:{{ $data['customer_email'] }}" style="color: #5B914C; text-decoration: none;">
                                {{ $data['customer_email'] }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['customer_phone']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Phone:</td>
                        <td style="padding: 10px 0;">
                            <a href="tel:{{ $data['customer_phone'] }}" style="color: #5B914C; text-decoration: none;">
                                {{ $data['customer_phone'] }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['customer_type']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Customer Type:</td>
                        <td style="padding: 10px 0; color: #333;">
                            <span style="display: inline-block; padding: 3px 10px;
                                {{ $data['customer_type'] === 'new' ? 'background-color: #17a2b8; color: #ffffff;' : 'background-color: #6c757d; color: #ffffff;' }}
                                border-radius: 3px; font-size: 12px; font-weight: 600;">
                                {{ strtoupper($data['customer_type']) }}
                            </span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Order Items -->
            @if(isset($data['items']) && is_array($data['items']) && count($data['items']) > 0)
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-box-seam"></i> Order Items ({{ count($data['items']) }})
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="padding: 10px; text-align: left; color: #333; font-weight: 600; font-size: 13px;">Product</th>
                            <th style="padding: 10px; text-align: center; color: #333; font-weight: 600; font-size: 13px;">Qty</th>
                            <th style="padding: 10px; text-align: right; color: #333; font-weight: 600; font-size: 13px;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['items'] as $index => $item)
                        <tr style="{{ $index > 0 ? 'border-top: 1px solid #dee2e6;' : '' }}">
                            <td style="padding: 12px 10px;">
                                <strong style="color: #333; font-size: 14px;">{{ $item['name'] ?? 'Product' }}</strong>
                                @if(isset($item['sku']))
                                <br><small style="color: #999; font-size: 12px;">SKU: {{ $item['sku'] }}</small>
                                @endif
                                @if(isset($item['variant']))
                                <br><small style="color: #666; font-size: 12px;">{{ $item['variant'] }}</small>
                                @endif
                            </td>
                            <td style="padding: 12px 10px; color: #666; text-align: center; font-weight: 600;">
                                {{ $item['quantity'] ?? 1 }}
                            </td>
                            <td style="padding: 12px 10px; color: #333; text-align: right; font-weight: 600;">
                                {{ $item['price'] ?? '' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Order Summary -->
                @if(isset($data['subtotal']) || isset($data['shipping_cost']) || isset($data['tax']) || isset($data['discount']))
                <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #dee2e6;">
                    <table style="width: 100%; border-collapse: collapse;">
                        @if(isset($data['subtotal']))
                        <tr>
                            <td style="padding: 5px 10px; color: #666; text-align: right;">Subtotal:</td>
                            <td style="padding: 5px 10px; color: #333; text-align: right; font-weight: 600; width: 120px;">
                                {{ $data['subtotal'] }}
                            </td>
                        </tr>
                        @endif
                        @if(isset($data['shipping_cost']))
                        <tr>
                            <td style="padding: 5px 10px; color: #666; text-align: right;">Shipping:</td>
                            <td style="padding: 5px 10px; color: #333; text-align: right; font-weight: 600;">
                                {{ $data['shipping_cost'] }}
                            </td>
                        </tr>
                        @endif
                        @if(isset($data['tax']))
                        <tr>
                            <td style="padding: 5px 10px; color: #666; text-align: right;">Tax:</td>
                            <td style="padding: 5px 10px; color: #333; text-align: right; font-weight: 600;">
                                {{ $data['tax'] }}
                            </td>
                        </tr>
                        @endif
                        @if(isset($data['discount']) && $data['discount'])
                        <tr>
                            <td style="padding: 5px 10px; color: #28a745; text-align: right;">Discount:</td>
                            <td style="padding: 5px 10px; color: #28a745; text-align: right; font-weight: 600;">
                                -{{ $data['discount'] }}
                            </td>
                        </tr>
                        @endif
                        @if(isset($data['total_amount']))
                        <tr style="border-top: 2px solid #5B914C;">
                            <td style="padding: 10px 10px 5px 10px; color: #333; text-align: right; font-weight: 700; font-size: 15px;">Total:</td>
                            <td style="padding: 10px 10px 5px 10px; color: #5B914C; text-align: right; font-weight: 700; font-size: 18px;">
                                {{ $data['total_amount'] }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
                @endif
            </div>
            @endif

            <!-- Shipping Information -->
            @if(isset($data['shipping_address']) || isset($data['shipping_method']))
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
                    <i class="bi bi-truck"></i> Shipping Details
                </h3>
                @if(isset($data['shipping_method']))
                <p style="margin: 0 0 10px 0; color: #666;">
                    <strong style="color: #333;">Shipping Method:</strong> {{ $data['shipping_method'] }}
                </p>
                @endif
                @if(isset($data['shipping_address']))
                <p style="margin: 0; color: #666; line-height: 1.6;">
                    <strong style="color: #333;">Shipping Address:</strong><br>
                    {{ $data['shipping_address'] }}
                </p>
                @endif
            </div>
            @endif

            <!-- Urgent Actions -->
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 25px; border-radius: 4px;">
                <strong style="color: #856404;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Action Required:
                </strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404;">
                    <li><strong>Review and confirm the order details</strong></li>
                    @if(isset($data['payment_status']) && $data['payment_status'] === 'pending')
                    <li><strong style="color: #dc3545;">⚠️ Payment verification required</strong></li>
                    @endif
                    <li>Check inventory availability for all items</li>
                    @if(isset($data['customer_type']) && $data['customer_type'] === 'new')
                    <li>🆕 This is a <strong>NEW CUSTOMER</strong> - ensure excellent service!</li>
                    @endif
                    <li>Confirm order with customer (if needed)</li>
                    <li>Prepare items for packing and shipping</li>
                </ul>
            </div>

            <!-- Special Notes -->
            @if(isset($data['customer_notes']) && $data['customer_notes'])
            <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin-top: 20px; border-radius: 4px;">
                <strong style="color: #004085;">
                    <i class="bi bi-chat-left-text-fill"></i> Customer Notes:
                </strong>
                <p style="margin: 10px 0 0 0; color: #004085; font-style: italic;">
                    "{{ $data['customer_notes'] }}"
                </p>
            </div>
            @endif

            <!-- Action Button -->
            @if(isset($notification->action_url) && $notification->action_url)
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 16px 40px; background-color: #5B914C; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 16px; transition: background-color 0.3s; box-shadow: 0 2px 4px rgba(91, 145, 76, 0.3);">
                    📋 Process This Order Now →
                </a>
            </div>
            @endif

            <div style="height: 1px; background-color: #e9ecef; margin: 25px 0;"></div>

            <!-- Statistics (if available) -->
            @if(isset($data['stats']))
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                <table style="width: 100%; text-align: center; border-collapse: collapse;">
                    <tr>
                        @if(isset($data['stats']['today_orders']))
                        <td style="padding: 10px; border-right: 1px solid #dee2e6;">
                            <strong style="display: block; font-size: 24px; color: #5B914C;">{{ $data['stats']['today_orders'] }}</strong>
                            <small style="color: #666; font-size: 12px;">Orders Today</small>
                        </td>
                        @endif
                        @if(isset($data['stats']['today_revenue']))
                        <td style="padding: 10px; border-right: 1px solid #dee2e6;">
                            <strong style="display: block; font-size: 24px; color: #5B914C;">{{ $data['stats']['today_revenue'] }}</strong>
                            <small style="color: #666; font-size: 12px;">Today's Revenue</small>
                        </td>
                        @endif
                        @if(isset($data['stats']['pending_orders']))
                        <td style="padding: 10px;">
                            <strong style="display: block; font-size: 24px; color: #ffc107;">{{ $data['stats']['pending_orders'] }}</strong>
                            <small style="color: #666; font-size: 12px;">Pending Orders</small>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>
            @endif

            <!-- Personal Message -->
            <div style="text-align: center; color: #999999; font-size: 14px;">
                <p style="margin: 5px 0;">This notification was sent based on your preferences.</p>
                <p style="margin: 5px 0;">Please process this order promptly to ensure customer satisfaction.</p>
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
                <a href="{{ route('admin.notifications.settings') }}" style="color: #5B914C; text-decoration: none; margin: 0 10px;">Manage Notifications</a> |
                @endif
                @if(Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" style="color: #5B914C; text-decoration: none; margin: 0 10px;">Dashboard</a>
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
