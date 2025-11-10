<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->invoice_number ?? $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .invoice-header {
            margin-bottom: 30px;
            border-bottom: 3px solid #5B914C;
            padding-bottom: 20px;
        }

        .company-info {
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #5B914C;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }

        .invoice-title {
            text-align: right;
            margin-top: -80px;
        }

        .invoice-title h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }

        .invoice-meta {
            text-align: right;
            font-size: 11px;
        }

        .invoice-meta strong {
            color: #5B914C;
        }

        /* Addresses Section */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .address-block {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .address-block:first-child {
            padding-right: 4%;
        }

        .address-title {
            font-size: 14px;
            font-weight: bold;
            color: #5B914C;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .address-content {
            font-size: 11px;
            line-height: 1.6;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background-color: #5B914C;
            color: white;
        }

        .items-table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .items-table tbody tr:last-child {
            border-bottom: 2px solid #5B914C;
        }

        .items-table td {
            padding: 10px;
            font-size: 11px;
        }

        .item-description {
            color: #666;
            font-size: 10px;
            margin-top: 3px;
        }

        /* Totals */
        .totals-section {
            float: right;
            width: 300px;
            margin-bottom: 30px;
        }

        .totals-table {
            width: 100%;
        }

        .totals-table tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .totals-table td {
            padding: 8px 0;
            font-size: 11px;
        }

        .totals-table td:first-child {
            text-align: left;
            color: #666;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .totals-table .total-row {
            border-top: 2px solid #5B914C;
            border-bottom: 3px double #5B914C;
        }

        .totals-table .total-row td {
            padding: 12px 0;
            font-size: 14px;
            color: #5B914C;
            font-weight: bold;
        }

        /* Payment Info */
        .payment-info {
            clear: both;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .payment-info-title {
            font-size: 12px;
            font-weight: bold;
            color: #5B914C;
            margin-bottom: 10px;
        }

        .payment-info-content {
            font-size: 11px;
            line-height: 1.6;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .payment-status.paid {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .payment-status.failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Notes */
        .notes-section {
            margin-bottom: 30px;
        }

        .notes-title {
            font-size: 12px;
            font-weight: bold;
            color: #5B914C;
            margin-bottom: 10px;
        }

        .notes-content {
            font-size: 11px;
            line-height: 1.6;
            color: #666;
            border-left: 3px solid #5B914C;
            padding-left: 15px;
        }

        /* Footer */
        .invoice-footer {
            border-top: 2px solid #e0e0e0;
            padding-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        .invoice-footer p {
            margin: 5px 0;
        }

        /* Status Badge */
        .order-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
        }

        .order-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .order-status.processing {
            background-color: #cfe2ff;
            color: #084298;
        }

        .order-status.shipped {
            background-color: #cff4fc;
            color: #055160;
        }

        .order-status.delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .order-status.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Utilities */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        /* Print Specific */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Invoice Header --}}
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">Coffee Equipment Store</div>
                <div class="company-details">
                    123 Coffee Street, Bean City, CA 90210<br>
                    Phone: (555) 123-4567 | Email: info@coffeestore.com<br>
                    Website: www.coffeestore.com | Tax ID: 12-3456789
                </div>
            </div>

            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="invoice-meta">
                    <p><strong>Invoice Number:</strong> {{ $order->invoice_number ?? 'INV-' . date('Ymd') . '-' . strtoupper(substr($order->id, 0, 6)) }}</p>
                    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                    <p><strong>Invoice Date:</strong> {{ $order->invoice_generated_at ? $order->invoice_generated_at->format('F d, Y') : now()->format('F d, Y') }}</p>
                    <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
                    @php
                        $statusClass = match($order->status_key_code) {
                            'ORDER_PENDING' => 'pending',
                            'ORDER_PROCESSING' => 'processing',
                            'ORDER_SHIPPED' => 'shipped',
                            'ORDER_DELIVERED' => 'delivered',
                            'ORDER_CANCELLED' => 'cancelled',
                            default => 'pending'
                        };
                        $statusLabel = str_replace('ORDER_', '', $order->status_key_code);
                    @endphp
                    <span class="order-status {{ $statusClass }}">{{ ucfirst(strtolower($statusLabel)) }}</span>
                </div>
            </div>
        </div>

        {{-- Bill To and Ship To Addresses --}}
        <div class="addresses clearfix">
            <div class="address-block">
                <div class="address-title">Bill To</div>
                <div class="address-content">
                    <strong>{{ $order->getCustomerName() }}</strong><br>
                    @if($order->billing_company)
                        {{ $order->billing_company }}<br>
                    @endif
                    {{ $order->billing_address_line1 }}<br>
                    @if($order->billing_address_line2)
                        {{ $order->billing_address_line2 }}<br>
                    @endif
                    {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}<br>
                    {{ $order->billing_country }}<br>
                    @if($order->billing_phone)
                        Phone: {{ $order->billing_phone }}<br>
                    @endif
                    Email: {{ $order->getCustomerEmail() }}
                </div>
            </div>

            <div class="address-block">
                <div class="address-title">Ship To</div>
                <div class="address-content">
                    <strong>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</strong><br>
                    @if($order->shipping_company)
                        {{ $order->shipping_company }}<br>
                    @endif
                    {{ $order->shipping_address_line1 }}<br>
                    @if($order->shipping_address_line2)
                        {{ $order->shipping_address_line2 }}<br>
                    @endif
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                    {{ $order->shipping_country }}<br>
                    @if($order->shipping_phone)
                        Phone: {{ $order->shipping_phone }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Order Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Description</th>
                    <th style="width: 12%;">Quantity</th>
                    <th style="width: 15%;">Unit Price</th>
                    <th style="width: 10%;">Tax</th>
                    <th style="width: 13%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product_sku)
                            <div class="item-description">SKU: {{ $item->product_sku }}</div>
                        @endif
                        @if($item->hasVariant())
                            <div class="item-description">{{ $item->getVariantOptionsString() }}</div>
                        @endif
                        @if($item->product_description)
                            <div class="item-description">{{ Str::limit($item->product_description, 100) }}</div>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($item->tax_amount, 2) }}</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals Section --}}
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td>
                        Discount
                        @if($order->discount_code)
                            ({{ $order->discount_code }})
                        @endif
                        :
                    </td>
                    <td>-${{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tax ({{ number_format($order->tax_rate ?? 0, 2) }}%):</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($order->tax_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($order->shipping_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td>{{ store_currency_symbol() }} {{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        {{-- Payment Information --}}
        <div class="payment-info">
            <div class="payment-info-title">Payment Information</div>
            <div class="payment-info-content">
                <strong>Payment Method:</strong> {{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : 'Not specified' }}<br>
                @if($order->payment_gateway)
                    <strong>Payment Gateway:</strong> {{ ucfirst($order->payment_gateway) }}<br>
                @endif
                @if($order->transaction_id)
                    <strong>Transaction ID:</strong> {{ $order->transaction_id }}<br>
                @endif
                <strong>Payment Status:</strong>
                @php
                    $paymentStatusClass = match($order->payment_status_key_code) {
                        'PAYMENT_PAID' => 'paid',
                        'PAYMENT_PENDING' => 'pending',
                        'PAYMENT_FAILED' => 'failed',
                        default => 'pending'
                    };
                    $paymentStatusLabel = str_replace('PAYMENT_', '', $order->payment_status_key_code);
                @endphp
                <span class="payment-status {{ $paymentStatusClass }}">{{ ucfirst(strtolower(str_replace('_', ' ', $paymentStatusLabel))) }}</span>
                @if($order->isPaid())
                    <br><strong>Paid Amount:</strong> ${{ number_format($order->total_amount, 2) }}
                @elseif($order->payment_status_key_code === 'PAYMENT_PARTIALLY_PAID')
                    <br><strong>Amount Due:</strong> ${{ number_format($order->total_amount - $order->getTotalPaid(), 2) }}
                @endif
            </div>
        </div>

        {{-- Shipping Information --}}
        @if($order->shipping_tracking_number)
        <div class="payment-info">
            <div class="payment-info-title">Shipping Information</div>
            <div class="payment-info-content">
                @if($order->shipping_carrier)
                    <strong>Carrier:</strong> {{ $order->shipping_carrier }}<br>
                @endif
                <strong>Tracking Number:</strong> {{ $order->shipping_tracking_number }}<br>
                @if($order->shipping_method)
                    <strong>Shipping Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}<br>
                @endif
                @if($order->shipped_at)
                    <strong>Shipped Date:</strong> {{ $order->shipped_at->format('F d, Y') }}<br>
                @endif
                @if($order->expected_delivery_date)
                    <strong>Expected Delivery:</strong> {{ $order->expected_delivery_date->format('F d, Y') }}<br>
                @endif
            </div>
        </div>
        @endif

        {{-- Customer Notes --}}
        @if($order->customer_notes)
        <div class="notes-section">
            <div class="notes-title">Customer Notes</div>
            <div class="notes-content">
                {{ $order->customer_notes }}
            </div>
        </div>
        @endif

        {{-- Admin Notes --}}
        @if($order->admin_notes)
        <div class="notes-section">
            <div class="notes-title">Additional Notes</div>
            <div class="notes-content">
                {{ $order->admin_notes }}
            </div>
        </div>
        @endif

        {{-- Terms and Conditions --}}
        <div class="notes-section">
            <div class="notes-title">Terms & Conditions</div>
            <div class="notes-content">
                <ul style="margin-left: 20px; font-size: 10px;">
                    <li>Payment is due within 30 days of invoice date.</li>
                    <li>Please include invoice number with payment.</li>
                    <li>All sales are final unless otherwise stated.</li>
                    <li>Returns must be made within 30 days of purchase with original packaging.</li>
                    <li>Warranty terms apply as per manufacturer specifications.</li>
                </ul>
            </div>
        </div>

        {{-- Footer --}}
        <div class="invoice-footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>If you have any questions about this invoice, please contact us at info@coffeestore.com or (555) 123-4567</p>
            <p style="margin-top: 10px; color: #ccc;">This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
