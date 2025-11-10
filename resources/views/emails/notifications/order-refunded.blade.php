<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Refunded</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">💰</div>
            <h1 style="margin: 0; font-size: 24px; font-weight: 600;">Refund Processed</h1>
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

            <!-- Refund Alert Box -->
            <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #0c5460;">
                    <i class="bi bi-cash-coin"></i> A refund has been successfully processed and issued to the customer
                </strong>
            </div>

            <!-- Main Message -->
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Hi <strong>{{ $admin->name }}</strong>,
            </p>
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                {{ $notification->message }}
            </p>

            <!-- Refund Amount Card -->
            <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 8px; padding: 25px; margin-bottom: 25px; color: #ffffff; text-align: center; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);">
                <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Refund Amount</p>
                <h2 style="margin: 0; font-size: 36px; font-weight: 700;">
                    {{ $data['refund_amount'] ?? $data['amount'] ?? 'N/A' }}
                </h2>
                @if(isset($data['refund_type']))
                <p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.9;">
                    {{ strtoupper($data['refund_type']) }} REFUND
                </p>
                @endif
            </div>

            <!-- Order & Refund Details -->
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px; border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
                    <i class="bi bi-receipt"></i> Order & Refund Information
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 12px 0; color: #666; font-weight: 600; width: 45%;">Order Number:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 700; font-size: 16px;">
                            #{{ $data['order_number'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @if(isset($data['refund_id']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Refund ID:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-family: monospace;">
                            {{ $data['refund_id'] }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Refund Status:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px; background-color: #28a745; color: #ffffff; border-radius: 4px; font-size: 13px; font-weight: 600;">
                                {{ strtoupper($data['refund_status'] ?? 'COMPLETED') }}
                            </span>
                        </td>
                    </tr>
                    @if(isset($data['refund_method']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Refund Method:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ ucfirst($data['refund_method']) }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['original_amount']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Original Order Amount:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['original_amount'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['refund_type']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Refund Type:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px;
                                {{ $data['refund_type'] === 'full' ? 'background-color: #17a2b8;' : 'background-color: #ffc107;' }}
                                color: {{ $data['refund_type'] === 'full' ? '#ffffff;' : '#333;' }}
                                border-radius: 4px; font-size: 13px; font-weight: 600;">
                                {{ strtoupper($data['refund_type']) }}
                            </span>
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Processed Date:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ $notification->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                    @if(isset($data['processed_by']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Processed By:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right;">
                            {{ $data['processed_by'] }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Customer Information -->
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
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
                            <a href="mailto:{{ $data['customer_email'] }}" style="color: #17a2b8; text-decoration: none;">
                                {{ $data['customer_email'] }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['customer_phone']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Phone:</td>
                        <td style="padding: 10px 0;">
                            <a href="tel:{{ $data['customer_phone'] }}" style="color: #17a2b8; text-decoration: none;">
                                {{ $data['customer_phone'] }}
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Refund Reason -->
            @if(isset($data['reason']) || isset($data['refund_reason']))
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #856404;">
                    <i class="bi bi-chat-left-quote-fill"></i> Refund Reason:
                </strong>
                <p style="margin: 10px 0 0 0; color: #856404; font-style: italic;">
                    "{{ $data['reason'] ?? $data['refund_reason'] }}"
                </p>
            </div>
            @endif

            <!-- Refund Items (if partial refund) -->
            @if(isset($data['refunded_items']) && is_array($data['refunded_items']) && count($data['refunded_items']) > 0)
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
                    <i class="bi bi-box-seam"></i> Refunded Items
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="padding: 10px; text-align: left; color: #333; font-weight: 600; font-size: 13px;">Product</th>
                            <th style="padding: 10px; text-align: center; color: #333; font-weight: 600; font-size: 13px;">Qty</th>
                            <th style="padding: 10px; text-align: right; color: #333; font-weight: 600; font-size: 13px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['refunded_items'] as $index => $item)
                        <tr style="{{ $index > 0 ? 'border-top: 1px solid #dee2e6;' : '' }}">
                            <td style="padding: 12px 10px;">
                                <strong style="color: #333; font-size: 14px;">{{ $item['name'] ?? 'Product' }}</strong>
                                @if(isset($item['sku']))
                                <br><small style="color: #999; font-size: 12px;">SKU: {{ $item['sku'] }}</small>
                                @endif
                            </td>
                            <td style="padding: 12px 10px; color: #666; text-align: center; font-weight: 600;">
                                {{ $item['quantity'] ?? 1 }}
                            </td>
                            <td style="padding: 12px 10px; color: #28a745; text-align: right; font-weight: 600;">
                                {{ $item['refund_amount'] ?? '' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Refund Breakdown -->
            @if(isset($data['refund_breakdown']))
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
                    <i class="bi bi-calculator"></i> Refund Breakdown
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    @if(isset($data['refund_breakdown']['items']))
                    <tr>
                        <td style="padding: 8px 10px; color: #666;">Items Refund:</td>
                        <td style="padding: 8px 10px; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['refund_breakdown']['items'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['refund_breakdown']['shipping']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 10px; color: #666;">Shipping Refund:</td>
                        <td style="padding: 8px 10px; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['refund_breakdown']['shipping'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['refund_breakdown']['tax']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 10px; color: #666;">Tax Refund:</td>
                        <td style="padding: 8px 10px; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['refund_breakdown']['tax'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['refund_breakdown']['restocking_fee']) && $data['refund_breakdown']['restocking_fee'])
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 8px 10px; color: #dc3545;">Restocking Fee:</td>
                        <td style="padding: 8px 10px; color: #dc3545; text-align: right; font-weight: 600;">
                            -{{ $data['refund_breakdown']['restocking_fee'] }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 2px solid #17a2b8;">
                        <td style="padding: 12px 10px; color: #333; font-weight: 700; font-size: 15px;">Total Refund:</td>
                        <td style="padding: 12px 10px; color: #28a745; text-align: right; font-weight: 700; font-size: 18px;">
                            {{ $data['refund_amount'] ?? $data['amount'] ?? 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
            @endif

            <!-- Processing Information -->
            <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin-top: 25px; border-radius: 4px;">
                <strong style="color: #004085;">
                    <i class="bi bi-info-circle-fill"></i> Important Information:
                </strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #004085;">
                    <li>Refund has been processed to the original payment method</li>
                    @if(isset($data['refund_method']) && str_contains(strtolower($data['refund_method']), 'card'))
                    <li>Bank processing may take 5-10 business days</li>
                    @elseif(isset($data['refund_method']) && str_contains(strtolower($data['refund_method']), 'paypal'))
                    <li>PayPal refunds typically appear within 24 hours</li>
                    @else
                    <li>Customer will receive the refund according to payment provider timelines</li>
                    @endif
                    <li>Customer has been automatically notified via email</li>
                    @if(isset($data['stock_restored']) && $data['stock_restored'])
                    <li>✅ Stock has been restored to inventory</li>
                    @endif
                    @if(isset($data['refund_type']) && $data['refund_type'] === 'partial')
                    <li>This is a partial refund - order remains in the system</li>
                    @else
                    <li>Order status has been updated to "Refunded"</li>
                    @endif
                </ul>
            </div>

            <!-- Next Steps -->
            @if(isset($data['requires_action']) && $data['requires_action'])
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px; border-radius: 4px;">
                <strong style="color: #856404;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Action Required:
                </strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404;">
                    @if(isset($data['action_items']) && is_array($data['action_items']))
                        @foreach($data['action_items'] as $action)
                        <li>{{ $action }}</li>
                        @endforeach
                    @else
                    <li>Review refund details and ensure accuracy</li>
                    <li>Update customer support ticket (if applicable)</li>
                    <li>Monitor for any customer follow-up inquiries</li>
                    @endif
                </ul>
            </div>
            @endif

            <!-- Admin Notes -->
            @if(isset($data['admin_notes']) && $data['admin_notes'])
            <div style="background-color: #f8f9fa; border-left: 4px solid #6c757d; padding: 15px; margin-top: 20px; border-radius: 4px;">
                <strong style="color: #495057;">
                    <i class="bi bi-sticky-fill"></i> Admin Notes:
                </strong>
                <p style="margin: 10px 0 0 0; color: #495057;">
                    {{ $data['admin_notes'] }}
                </p>
            </div>
            @endif

            <!-- Action Button -->
            @if(isset($notification->action_url) && $notification->action_url)
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 35px; background-color: #17a2b8; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background-color 0.3s;">
                    View Order & Refund Details →
                </a>
            </div>
            @endif

            <div style="height: 1px; background-color: #e9ecef; margin: 25px 0;"></div>

            <!-- Statistics (if available) -->
            @if(isset($data['stats']))
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                <table style="width: 100%; text-align: center; border-collapse: collapse;">
                    <tr>
                        @if(isset($data['stats']['total_refunds_today']))
                        <td style="padding: 10px; border-right: 1px solid #dee2e6;">
                            <strong style="display: block; font-size: 24px; color: #17a2b8;">{{ $data['stats']['total_refunds_today'] }}</strong>
                            <small style="color: #666; font-size: 12px;">Refunds Today</small>
                        </td>
                        @endif
                        @if(isset($data['stats']['refund_amount_today']))
                        <td style="padding: 10px; border-right: 1px solid #dee2e6;">
                            <strong style="display: block; font-size: 24px; color: #17a2b8;">{{ $data['stats']['refund_amount_today'] }}</strong>
                            <small style="color: #666; font-size: 12px;">Refunded Today</small>
                        </td>
                        @endif
                        @if(isset($data['stats']['refund_rate']))
                        <td style="padding: 10px;">
                            <strong style="display: block; font-size: 24px; color: #ffc107;">{{ $data['stats']['refund_rate'] }}%</strong>
                            <small style="color: #666; font-size: 12px;">Refund Rate</small>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>
            @endif

            <!-- Personal Message -->
            <div style="text-align: center; color: #999999; font-size: 14px;">
                <p style="margin: 5px 0;">This notification confirms the successful processing of a refund.</p>
                <p style="margin: 5px 0;">No further action is required unless noted above.</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 25px 20px; text-align: center; font-size: 13px; color: #666; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #333;">{{ config('app.name') }}</p>
            <p style="margin: 0 0 15px 0;">
                <a href="{{ config('app.url') }}" style="color: #17a2b8; text-decoration: none;">{{ config('app.url') }}</a>
            </p>
            <p style="margin: 0 0 10px 0;">
                @if(Route::has('admin.notifications.settings'))
                <a href="{{ route('admin.notifications.settings') }}" style="color: #17a2b8; text-decoration: none; margin: 0 10px;">Manage Notifications</a> |
                @endif
                @if(Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" style="color: #17a2b8; text-decoration: none; margin: 0 10px;">Dashboard</a>
                @endif
                @if(Route::has('admin.orders.index'))
                | <a href="{{ route('admin.orders.index') }}" style="color: #17a2b8; text-decoration: none; margin: 0 10px;">All Orders</a>
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
