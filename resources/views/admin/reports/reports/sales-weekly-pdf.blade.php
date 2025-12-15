{{-- resources/views/admin/reports/exports/sales-weekly-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Sales Report - Week {{ $week_number }}, {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            background-color: #5B914C;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            opacity: 0.9;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-row {
            display: table-row;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            border: 1px solid #e0e0e0;
            background-color: #f8f9fa;
        }
        .summary-card .label {
            color: #666;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #5B914C;
        }
        .summary-card .change {
            font-size: 8px;
            margin-top: 3px;
        }
        .summary-card .change.positive {
            color: #28a745;
        }
        .summary-card .change.negative {
            color: #dc3545;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #5B914C;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #5B914C;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table thead {
            background-color: #f8f9fa;
        }
        table th {
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 8px;
            border-radius: 3px;
            background-color: #e9ecef;
            color: #495057;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px;
            border-top: 1px solid #dee2e6;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Weekly Sales Report</h1>
        <p>Week {{ $week_number }}, {{ $year }} ({{ $start_date->format('M d') }} - {{ $end_date->format('M d, Y') }})</p>
        <p style="font-size: 9px;">Generated: {{ now()->format('M d, Y H:i:s') }}</p>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-grid">
        <div class="summary-row">
            <div class="summary-card">
                <div class="label">Total Orders</div>
                <div class="value">{{ number_format($sales_data['total_orders']) }}</div>
                @if(isset($comparison['orders_change']))
                <div class="change {{ $comparison['orders_change'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $comparison['orders_change'] >= 0 ? '↑' : '↓' }}
                    {{ number_format(abs($comparison['orders_change']), 1) }}% vs last week
                </div>
                @endif
            </div>
            <div class="summary-card">
                <div class="label">Total Revenue</div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</div>
                @if(isset($comparison['revenue_change']))
                <div class="change {{ $comparison['revenue_change'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $comparison['revenue_change'] >= 0 ? '↑' : '↓' }}
                    {{ number_format(abs($comparison['revenue_change']), 1) }}% vs last week
                </div>
                @endif
            </div>
            <div class="summary-card">
                <div class="label">Avg Order Value</div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Avg Daily Revenue</div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_daily_revenue'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Daily Breakdown --}}
    <div class="section">
        <div class="section-title">Daily Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Avg Order Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daily_breakdown as $day)
                <tr>
                    <td>{{ $day['date_label'] }}</td>
                    <td>{{ $day['day_name'] }}</td>
                    <td class="text-right">{{ number_format($day['order_count']) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($day['revenue'], 2) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($day['avg_order_value'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Top Products --}}
    <div class="section">
        <div class="section-title">Top Selling Products This Week</div>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-right">Units</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_products_week as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td><span class="badge">{{ $product->sku }}</span></td>
                    <td>{{ $product->category_name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($product->total_sold) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($product->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Weekly Sales Report - Week {{ $week_number }}, {{ $year }} | Generated by {{ config('app.name') }}</p>
    </div>
</body>
</html>
