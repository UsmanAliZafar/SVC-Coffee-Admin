{{-- Path: resources/views/emails/customers/invoice.blade.php --}}

@php
    $storeName    = store_settings('store_name')    ?: config('app.name');
    $storeEmail   = store_settings('store_email')   ?: config('mail.from.address');
    $storePhone   = store_settings('store_phone')   ?: null;
    $storeAddress = store_settings('store_address') ?: null;
    $storeCity    = store_settings('store_city')    ?: null;
    $storeState   = store_settings('store_state')   ?: null;
    $storeZip     = store_settings('store_zip')     ?: null;
    $storeCountry = store_settings('store_country') ?: null;
    $storeTagline = store_settings('store_tagline') ?: null;

    // Currency handled by store_currency_symbol() helper inline

    // Build address line
    $addressParts = array_filter([$storeAddress, $storeCity, $storeState, $storeZip]);
    $storeFullAddress = implode(', ', $addressParts);

    // Status classes
    $statusClass = match($order->status_key_code) {
        'ORDER_PENDING'    => 'status-pending',
        'ORDER_PROCESSING' => 'status-processing',
        'ORDER_SHIPPED'    => 'status-shipped',
        'ORDER_DELIVERED'  => 'status-delivered',
        'ORDER_CANCELLED'  => 'status-cancelled',
        default            => 'status-pending'
    };
    $statusLabel = ucfirst(strtolower(str_replace('ORDER_', '', $order->status_key_code)));

    $paymentStatusClass = match($order->payment_status_key_code) {
        'PAYMENT_PAID'    => 'status-delivered',
        'PAYMENT_PENDING' => 'status-pending',
        'PAYMENT_FAILED'  => 'status-cancelled',
        default           => 'status-pending'
    };
    $paymentStatusLabel = ucfirst(strtolower(str_replace('PAYMENT_', '', $order->payment_status_key_code)));
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice — {{ $order->invoice_number ?? $order->order_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --green:       #5B914C;
            --green-dark:  #4a7a3d;
            --green-light: #eef6eb;
            --ink:         #1a1a1a;
            --muted:       #6b7280;
            --border:      #e5e7eb;
            --bg:          #f9fafb;
            --white:       #ffffff;
        }

        body {
            font-family: 'DM Sans', Arial, sans-serif;
            font-size: 12px;
            color: var(--ink);
            line-height: 1.6;
            background: var(--white);
        }

        .page {
            max-width: 820px;
            margin: 0 auto;
            padding: 40px 40px 60px;
        }

        /* ── HEADER ─────────────────────────────────────── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 36px;
            padding-bottom: 24px;
            border-bottom: 2px solid var(--green);
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-right {
            text-align: right;
        }

        .brand-name {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 26px;
            color: var(--green);
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .brand-tagline {
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .brand-details {
            font-size: 11px;
            color: var(--muted);
            line-height: 1.7;
        }

        .brand-details a {
            color: var(--muted);
            text-decoration: none;
        }

        .invoice-word {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 42px;
            color: var(--ink);
            letter-spacing: -1px;
            line-height: 1;
            margin-bottom: 14px;
        }

        .meta-grid {
            font-size: 11px;
        }

        .meta-grid tr td {
            padding: 2px 0;
        }

        .meta-grid td:first-child {
            color: var(--muted);
            padding-right: 16px;
        }

        .meta-grid td:last-child {
            font-weight: 600;
            color: var(--ink);
        }

        /* ── STATUS BADGES ──────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .status-pending    { background: #fff3cd; color: #856404; }
        .status-processing { background: #dbeafe; color: #1d4ed8; }
        .status-shipped    { background: #cffafe; color: #0e7490; }
        .status-delivered  { background: #dcfce7; color: #15803d; }
        .status-cancelled  { background: #fee2e2; color: #b91c1c; }

        /* ── ADDRESSES ───────────────────────────────────── */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 32px;
        }

        .address-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .address-cell:first-child {
            padding-right: 20px;
        }

        .address-card {
            background: var(--bg);
            border-radius: 8px;
            padding: 16px 18px;
            border-top: 3px solid var(--green);
        }

        .address-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--green);
            margin-bottom: 10px;
        }

        .address-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .address-lines {
            font-size: 11px;
            color: var(--muted);
            line-height: 1.7;
        }

        /* ── ITEMS TABLE ─────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .items-table thead tr {
            background: var(--green);
        }

        .items-table th {
            padding: 11px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--white);
        }

        .items-table th:first-child { border-radius: 6px 0 0 0; }
        .items-table th:last-child  { border-radius: 0 6px 0 0; text-align: right; }

        .items-table td {
            padding: 11px 12px;
            font-size: 11px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .items-table td:last-child { text-align: right; }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid var(--green);
        }

        .items-table tbody tr:hover td {
            background: var(--green-light);
        }

        .item-name {
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 3px;
        }

        .item-meta {
            font-size: 10px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── TOTALS ──────────────────────────────────────── */
        .totals-wrapper {
            display: table;
            width: 100%;
            margin: 24px 0 32px;
        }

        .totals-spacer {
            display: table-cell;
            width: 55%;
        }

        .totals-box {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 7px 0;
            font-size: 11px;
            border-bottom: 1px solid var(--border);
        }

        .totals-table td:first-child { color: var(--muted); }
        .totals-table td:last-child  { text-align: right; font-weight: 600; }

        .totals-table .grand-total td {
            padding: 12px 0 8px;
            font-size: 15px;
            font-weight: 700;
            color: var(--green);
            border-bottom: none;
            border-top: 2px solid var(--green);
        }

        /* ── INFO BLOCKS ─────────────────────────────────── */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            gap: 16px;
        }

        .info-block {
            background: var(--bg);
            border-radius: 8px;
            padding: 16px 18px;
            border-left: 3px solid var(--green);
            margin-bottom: 16px;
        }

        .info-block-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--green);
            margin-bottom: 10px;
        }

        .info-block-content {
            font-size: 11px;
            color: var(--muted);
            line-height: 1.8;
        }

        .info-block-content strong {
            color: var(--ink);
        }

        /* ── NOTES ───────────────────────────────────────── */
        .note-block {
            background: var(--green-light);
            border-left: 3px solid var(--green);
            border-radius: 0 6px 6px 0;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .note-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--green-dark);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .note-content {
            font-size: 11px;
            color: #374151;
            line-height: 1.7;
        }

        /* ── TERMS ───────────────────────────────────────── */
        .terms-block {
            background: var(--bg);
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 32px;
        }

        .terms-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
        }

        .terms-list {
            margin-left: 16px;
            font-size: 10px;
            color: var(--muted);
            line-height: 1.9;
        }

        /* ── FOOTER ──────────────────────────────────────── */
        .invoice-footer {
            border-top: 1px solid var(--border);
            padding-top: 20px;
            text-align: center;
        }

        .footer-thanks {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 18px;
            color: var(--green);
            margin-bottom: 8px;
        }

        .footer-note {
            font-size: 10px;
            color: var(--muted);
            margin: 4px 0;
        }

        .footer-legal {
            margin-top: 12px;
            font-size: 9px;
            color: #d1d5db;
        }

        /* ── PRINT ───────────────────────────────────────── */
        @media print {
            body { background: white; }
            .page { padding: 20px; max-width: none; width: 100%; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ── --}}
    <div class="header">
        <div class="header-left">
            <div class="brand-name">{{ $storeName }}</div>
            @if($storeTagline)
            <div class="brand-tagline">{{ $storeTagline }}</div>
            @endif
            <div class="brand-details">
                @if($storeFullAddress){{ $storeFullAddress }}@if($storeCountry), {{ $storeCountry }}@endif<br>@endif
                @if($storePhone)📞 {{ $storePhone }}<br>@endif
                @if($storeEmail)✉ <a href="mailto:{{ $storeEmail }}">{{ $storeEmail }}</a>@endif
            </div>
        </div>

        <div class="header-right">
            <div class="invoice-word">INVOICE</div>
            <table class="meta-grid">
                <tr>
                    <td>Invoice No.</td>
                    <td>{{ $order->invoice_number ?? 'INV-' . date('Ymd') . '-' . strtoupper(substr($order->id, 0, 6)) }}</td>
                </tr>
                <tr>
                    <td>Order No.</td>
                    <td>{{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td>Invoice Date</td>
                    <td>{{ $order->invoice_generated_at ? $order->invoice_generated_at->format('d M Y') : now()->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Order Date</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Order Status</td>
                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── ADDRESSES ── --}}
    <div class="addresses">
        <div class="address-cell">
            <div class="address-card">
                <div class="address-label">Bill To</div>
                <div class="address-name">{{ $order->getCustomerName() }}</div>
                <div class="address-lines">
                    @if($order->billing_company){{ $order->billing_company }}<br>@endif
                    {{ $order->billing_address_line1 }}<br>
                    @if($order->billing_address_line2){{ $order->billing_address_line2 }}<br>@endif
                    {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}<br>
                    {{ $order->billing_country }}<br>
                    @if($order->billing_phone)📞 {{ $order->billing_phone }}<br>@endif
                    ✉ {{ $order->getCustomerEmail() }}
                </div>
            </div>
        </div>

        <div class="address-cell">
            <div class="address-card">
                <div class="address-label">Ship To</div>
                <div class="address-name">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</div>
                <div class="address-lines">
                    @if($order->shipping_company){{ $order->shipping_company }}<br>@endif
                    {{ $order->shipping_address_line1 }}<br>
                    @if($order->shipping_address_line2){{ $order->shipping_address_line2 }}<br>@endif
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                    {{ $order->shipping_country }}<br>
                    @if($order->shipping_phone)📞 {{ $order->shipping_phone }}@endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── ITEMS TABLE ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:44%">Description</th>
                <th style="width:10%; text-align:center">Qty</th>
                <th style="width:14%; text-align:right">Unit Price</th>
                <th style="width:12%; text-align:right">Tax</th>
                <th style="width:16%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr>
                <td style="color:var(--muted)">{{ $index + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item->product_name }}</div>
                    @if($item->product_sku)
                        <div class="item-meta">SKU: {{ $item->product_sku }}</div>
                    @endif
                    @if($item->hasVariant())
                        <div class="item-meta">{{ $item->getVariantOptionsString() }}</div>
                    @endif
                    @if($item->product_description)
                        <div class="item-meta">{{ Str::limit($item->product_description, 100) }}</div>
                    @endif
                </td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:right">SAR {{ $item->unit_price }}</td>
                <td style="text-align:right">SAR {{ $item->tax_amount }}</td>
                <td>SAR {{ $item->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALS ── --}}
    <div class="totals-wrapper">
        <div class="totals-spacer"></div>
        <div class="totals-box">
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td>SAR {{ $order->subtotal }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td>Discount @if($order->discount_code)({{ $order->discount_code }})@endif</td>
                    <td style="color:#b91c1c">− SAR {{ $order->discount_amount }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tax ({{ number_format($order->tax_rate ?? 0, 2) }}%)</td>
                    <td>SAR {{ $order->tax_amount }}</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>SAR {{ $order->shipping_amount }}</td>
                </tr>
                <tr class="grand-total">
                    <td>Total</td>
                    <td>SAR {{ $order->total_amount }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── PAYMENT INFORMATION ── --}}
    <div class="info-block">
        <div class="info-block-title">Payment Information</div>
        <div class="info-block-content">
            <strong>Method:</strong> {{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : 'Not specified' }}<br>
            @if($order->payment_gateway)
                <strong>Gateway:</strong> {{ ucfirst($order->payment_gateway) }}<br>
            @endif
            @if($order->transaction_id)
                <strong>Transaction ID:</strong> {{ $order->transaction_id }}<br>
            @endif
            <strong>Status:</strong> <span class="badge {{ $paymentStatusClass }}">{{ $paymentStatusLabel }}</span>
            @if($order->isPaid())
                <br><strong>Paid Amount:</strong> SAR {{ $order->total_amount }}
            @elseif($order->payment_status_key_code === 'PAYMENT_PARTIALLY_PAID')
                <br><strong>Amount Due:</strong> SAR {{ $order->total_amount - $order->getTotalPaid() }}
            @endif
        </div>
    </div>

    {{-- ── SHIPPING INFORMATION ── --}}
    @if($order->shipping_tracking_number)
    <div class="info-block">
        <div class="info-block-title">Shipping Information</div>
        <div class="info-block-content">
            @if($order->shipping_carrier)<strong>Carrier:</strong> {{ $order->shipping_carrier }}<br>@endif
            <strong>Tracking No.:</strong> {{ $order->shipping_tracking_number }}<br>
            @if($order->shipping_method)<strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}<br>@endif
            @if($order->shipped_at)<strong>Shipped:</strong> {{ $order->shipped_at->format('d M Y') }}<br>@endif
            @if($order->expected_delivery_date)<strong>Est. Delivery:</strong> {{ $order->expected_delivery_date->format('d M Y') }}<br>@endif
        </div>
    </div>
    @endif

    {{-- ── CUSTOMER NOTES ── --}}
    @if($order->customer_notes)
    <div class="note-block">
        <div class="note-title">Customer Notes</div>
        <div class="note-content">{{ $order->customer_notes }}</div>
    </div>
    @endif

    {{-- ── ADMIN NOTES ── --}}
    @if($order->admin_notes)
    <div class="note-block">
        <div class="note-title">Additional Notes</div>
        <div class="note-content">{{ $order->admin_notes }}</div>
    </div>
    @endif

    {{-- ── TERMS & CONDITIONS ── --}}
    <div class="terms-block">
        <div class="terms-title">Terms &amp; Conditions</div>
        <ul class="terms-list">
            <li>Payment is due within 30 days of invoice date.</li>
            <li>Please include invoice number with payment.</li>
            <li>All sales are final unless otherwise stated.</li>
            <li>Returns must be made within 30 days of purchase with original packaging.</li>
            <li>Warranty terms apply as per manufacturer specifications.</li>
        </ul>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="invoice-footer">
        <div class="footer-thanks">Thank you for your business!</div>
        @if($storeEmail || $storePhone)
        <p class="footer-note">
            Questions about this invoice?
            @if($storeEmail)Email us at <strong>{{ $storeEmail }}</strong>@endif
            @if($storePhone && $storeEmail) or call @endif
            @if($storePhone)<strong>{{ $storePhone }}</strong>@endif
        </p>
        @endif
        <p class="footer-legal">This is a computer-generated invoice and does not require a signature. &nbsp;·&nbsp; {{ $storeName }}</p>
    </div>

</div>
</body>
</html>
