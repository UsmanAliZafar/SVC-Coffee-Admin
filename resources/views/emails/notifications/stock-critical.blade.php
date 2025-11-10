<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRITICAL: Stock Alert</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #ffffff; padding: 30px 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px; animation: pulse 2s infinite;">⚠️</div>
            <h1 style="margin: 0; font-size: 26px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">CRITICAL STOCK ALERT</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">{{ config('app.name') }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">

            <!-- Urgent Priority Badge -->
            <div style="text-align: center; margin-bottom: 20px;">
                <span style="display: inline-block; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; background-color: #dc3545; color: #ffffff; animation: blink 1.5s infinite;">
                    🚨 URGENT PRIORITY - IMMEDIATE ACTION REQUIRED
                </span>
            </div>

            <!-- Critical Alert Box -->
            <div style="background-color: #f8d7da; border: 2px solid #dc3545; padding: 20px; margin-bottom: 25px; border-radius: 6px; box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);">
                <div style="text-align: center;">
                    <strong style="color: #721c24; font-size: 18px; display: block; margin-bottom: 10px;">
                        <i class="bi bi-exclamation-octagon-fill"></i> CRITICAL STOCK LEVEL DETECTED
                    </strong>
                    <p style="margin: 0; color: #721c24; font-size: 15px; line-height: 1.8;">
                        A product has reached critically low stock levels and requires immediate attention to prevent stockouts and lost sales.
                    </p>
                </div>
            </div>

            <!-- Main Message -->
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Hi <strong>{{ $admin->name }}</strong>,
            </p>
            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                {{ $notification->message }}
            </p>

            <!-- Product Critical Stock Card -->
            <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 8px; padding: 25px; margin-bottom: 25px; color: #ffffff; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);">
                <h2 style="margin: 0 0 15px 0; font-size: 22px; font-weight: 700; text-align: center; text-transform: uppercase;">
                    {{ $data['product_name'] ?? 'Product' }}
                </h2>

                <div style="background-color: rgba(255, 255, 255, 0.15); border-radius: 6px; padding: 20px; margin-bottom: 15px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 10px 0; width: 50%; text-align: center; border-right: 1px solid rgba(255,255,255,0.3);">
                                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Current Stock</div>
                                <div style="font-size: 36px; font-weight: 700; line-height: 1;">
                                    {{ $data['current_stock'] ?? 0 }}
                                </div>
                            </td>
                            <td style="padding: 10px 0; width: 50%; text-align: center;">
                                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Critical Threshold</div>
                                <div style="font-size: 36px; font-weight: 700; line-height: 1;">
                                    {{ $data['threshold'] ?? $data['critical_threshold'] ?? 'N/A' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                @if(isset($data['sku']))
                <p style="margin: 0; text-align: center; font-size: 14px; opacity: 0.9;">
                    SKU: <strong style="font-family: monospace; letter-spacing: 1px;">{{ $data['sku'] }}</strong>
                </p>
                @endif
            </div>

            <!-- Product Details -->
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px; border: 1px solid #e9ecef;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
                    <i class="bi bi-box-seam"></i> Product Information
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    @if(isset($data['product_name']))
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-weight: 600; width: 35%;">Product Name:</td>
                        <td style="padding: 10px 0; color: #333;">
                            <strong>{{ $data['product_name'] }}</strong>
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['sku']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">SKU:</td>
                        <td style="padding: 10px 0; color: #333; font-family: monospace;">
                            {{ $data['sku'] }}
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['category']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Category:</td>
                        <td style="padding: 10px 0; color: #333;">
                            {{ $data['category'] }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 10px 0; color: #666; font-weight: 600;">Status:</td>
                        <td style="padding: 10px 0;">
                            <span style="display: inline-block; padding: 4px 12px; background-color: #dc3545; color: #ffffff; border-radius: 4px; font-size: 13px; font-weight: 600;">
                                CRITICAL
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Stock Metrics -->
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
                    <i class="bi bi-graph-down"></i> Stock Metrics
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 12px 0; color: #666; font-weight: 600; width: 45%;">Current Stock Level:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="color: #dc3545; font-size: 20px; font-weight: 700;">
                                {{ $data['current_stock'] ?? 0 }} units
                            </span>
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Critical Threshold:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['threshold'] ?? $data['critical_threshold'] ?? 'N/A' }} units
                        </td>
                    </tr>
                    @if(isset($data['low_stock_threshold']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Low Stock Threshold:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['low_stock_threshold'] }} units
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['reorder_point']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Reorder Point:</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['reorder_point'] }} units
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['available_stock']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Available (Unfulfilled):</td>
                        <td style="padding: 12px 0; color: #333; text-align: right; font-weight: 600;">
                            {{ $data['available_stock'] }} units
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['reserved_stock']))
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td style="padding: 12px 0; color: #666; font-weight: 600;">Reserved (Orders):</td>
                        <td style="padding: 12px 0; color: #ffc107; text-align: right; font-weight: 600;">
                            {{ $data['reserved_stock'] }} units
                        </td>
                    </tr>
                    @endif
                    @if(isset($data['recommended_reorder']))
                    <tr style="border-top: 2px solid #dc3545;">
                        <td style="padding: 12px 0; color: #333; font-weight: 700;">Recommended Reorder Qty:</td>
                        <td style="padding: 12px 0; text-align: right;">
                            <span style="color: #5B914C; font-size: 18px; font-weight: 700;">
                                {{ $data['recommended_reorder'] }} units
                            </span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Warehouse/Location Info -->
            @if(isset($data['warehouse_name']) || isset($data['location']))
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
                    <i class="bi bi-geo-alt-fill"></i> Location Information
                </h3>
                @if(isset($data['warehouse_name']))
                <p style="margin: 0 0 10px 0; color: #666;">
                    <strong style="color: #333;">Warehouse:</strong> {{ $data['warehouse_name'] }}
                </p>
                @endif
                @if(isset($data['location']))
                <p style="margin: 0; color: #666;">
                    <strong style="color: #333;">Location:</strong> {{ $data['location'] }}
                </p>
                @endif
            </div>
            @endif

            <!-- Sales Impact -->
            @if(isset($data['sales_velocity']) || isset($data['days_until_stockout']) || isset($data['pending_orders']))
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #856404; font-size: 16px;">
                    <i class="bi bi-speedometer2"></i> Sales Impact Analysis
                </strong>
                <div style="margin-top: 15px;">
                    @if(isset($data['sales_velocity']))
                    <p style="margin: 0 0 10px 0; color: #856404;">
                        <strong>Average Daily Sales:</strong> {{ $data['sales_velocity'] }} units/day
                    </p>
                    @endif
                    @if(isset($data['days_until_stockout']))
                    <p style="margin: 0 0 10px 0; color: #856404;">
                        <strong>⏰ Estimated Stockout:</strong>
                        <span style="font-weight: 700; font-size: 16px;">
                            {{ $data['days_until_stockout'] }} {{ $data['days_until_stockout'] == 1 ? 'day' : 'days' }}
                        </span>
                    </p>
                    @endif
                    @if(isset($data['pending_orders']))
                    <p style="margin: 0 0 10px 0; color: #856404;">
                        <strong>Pending Orders:</strong> {{ $data['pending_orders'] }} orders waiting
                    </p>
                    @endif
                    @if(isset($data['potential_lost_sales']))
                    <p style="margin: 0; color: #dc3545; font-weight: 700;">
                        <strong>⚠️ Potential Lost Revenue:</strong> {{ $data['potential_lost_sales'] }}
                    </p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Supplier Information -->
            @if(isset($data['supplier_name']) || isset($data['supplier_lead_time']))
            <div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
                    <i class="bi bi-building"></i> Supplier Information
                </h3>
                @if(isset($data['supplier_name']))
                <p style="margin: 0 0 10px 0; color: #666;">
                    <strong style="color: #333;">Supplier:</strong> {{ $data['supplier_name'] }}
                </p>
                @endif
                @if(isset($data['supplier_contact']))
                <p style="margin: 0 0 10px 0; color: #666;">
                    <strong style="color: #333;">Contact:</strong>
                    <a href="mailto:{{ $data['supplier_contact'] }}" style="color: #5B914C; text-decoration: none;">
                        {{ $data['supplier_contact'] }}
                    </a>
                </p>
                @endif
                @if(isset($data['supplier_lead_time']))
                <p style="margin: 0 0 10px 0; color: #666;">
                    <strong style="color: #333;">Lead Time:</strong> {{ $data['supplier_lead_time'] }} days
                </p>
                @endif
                @if(isset($data['last_order_date']))
                <p style="margin: 0; color: #666;">
                    <strong style="color: #333;">Last Order Date:</strong> {{ $data['last_order_date'] }}
                </p>
                @endif
            </div>
            @endif

            <!-- Immediate Actions Required -->
            <div style="background-color: #dc3545; color: #ffffff; border-radius: 6px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);">
                <strong style="font-size: 18px; display: block; margin-bottom: 15px;">
                    <i class="bi bi-lightning-fill"></i> IMMEDIATE ACTIONS REQUIRED:
                </strong>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li><strong>Place emergency reorder with supplier IMMEDIATELY</strong></li>
                    @if(isset($data['recommended_reorder']))
                    <li>Recommended order quantity: <strong>{{ $data['recommended_reorder'] }} units</strong></li>
                    @endif
                    <li>Contact supplier to expedite delivery if possible</li>
                    <li>Check alternative suppliers for emergency stock</li>
                    @if(isset($data['pending_orders']) && $data['pending_orders'] > 0)
                    <li><strong style="text-decoration: underline;">Review {{ $data['pending_orders'] }} pending orders - may need customer communication</strong></li>
                    @endif
                    <li>Consider temporarily disabling online ordering for this product</li>
                    <li>Update product page with stock availability notice</li>
                    <li>Coordinate with warehouse team for physical stock count verification</li>
                </ul>
            </div>

            <!-- Alternative Actions -->
            <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                <strong style="color: #004085;">
                    <i class="bi bi-lightbulb-fill"></i> Alternative Actions to Consider:
                </strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #004085;">
                    <li>Check if similar products have excess stock for substitution</li>
                    <li>Contact customers with pending orders about possible delays</li>
                    <li>Review pricing strategy - consider temporary price increase</li>
                    <li>Analyze if this is a trend - adjust automatic reorder points</li>
                    @if(isset($data['category']))
                    <li>Review all products in "{{ $data['category'] }}" category for similar issues</li>
                    @endif
                </ul>
            </div>

            <!-- Quick Actions Buttons -->
            <div style="text-align: center; margin: 30px 0 25px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px; text-align: center;">
                            @if(isset($notification->action_url) && $notification->action_url)
                            <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 14px 25px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 15px;">
                                📦 View Product Details
                            </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; text-align: center;">
                            @if(Route::has('admin.purchase-orders.create'))
                            <a href="{{ route('admin.purchase-orders.create', ['product_id' => $data['product_id'] ?? '']) }}" style="display: inline-block; padding: 14px 25px; background-color: #5B914C; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 15px; margin-top: 10px;">
                                🛒 Create Purchase Order
                            </a>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div style="height: 1px; background-color: #e9ecef; margin: 25px 0;"></div>

            <!-- Similar Products Alert -->
            @if(isset($data['similar_products_affected']) && $data['similar_products_affected'] > 0)
            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <strong style="color: #721c24;">
                    ⚠️ Additional Alert: {{ $data['similar_products_affected'] }} other products in this category are also at low stock levels
                </strong>
            </div>
            @endif

            <!-- Warning Message -->
            <div style="text-align: center; color: #999999; font-size: 14px;">
                <p style="margin: 5px 0; color: #dc3545; font-weight: 600;">
                    ⏰ Time-Sensitive: This alert requires immediate attention
                </p>
                <p style="margin: 5px 0;">
                    Failure to act quickly may result in stockouts, lost sales, and disappointed customers.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 25px 20px; text-align: center; font-size: 13px; color: #666; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #333;">{{ config('app.name') }}</p>
            <p style="margin: 0 0 15px 0;">
                <a href="{{ config('app.url') }}" style="color: #dc3545; text-decoration: none;">{{ config('app.url') }}</a>
            </p>
            <p style="margin: 0 0 10px 0;">
                @if(Route::has('admin.notifications.settings'))
                <a href="{{ route('admin.notifications.settings') }}" style="color: #dc3545; text-decoration: none; margin: 0 10px;">Manage Notifications</a> |
                @endif
                @if(Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" style="color: #dc3545; text-decoration: none; margin: 0 10px;">Dashboard</a>
                @endif
                @if(Route::has('admin.inventory.index'))
                | <a href="{{ route('admin.inventory.index') }}" style="color: #dc3545; text-decoration: none; margin: 0 10px;">Inventory Management</a>
                @endif
            </p>
            <div style="height: 1px; background-color: #dee2e6; margin: 15px 0;"></div>
            <p style="margin: 10px 0 5px 0; font-size: 12px; color: #999;">
                This is an automated CRITICAL notification. Immediate action required.
            </p>
            <p style="margin: 5px 0 0 0; font-size: 11px; color: #aaa;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }
    </style>
</body>
</html>
