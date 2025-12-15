{{-- resources/views/admin/reports/exports/sales-custom-range-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ $start_date->format('M d, Y') }} to {{ $end_date->format('M d, Y') }}</title>
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
        <h1>Custom Range Sales Report</h1>
        <p>{{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }} ({{ $days_count }} days)</p>
        <p style="font-size: 9px;">Generated: {{ now()->format('M d, Y H:i:s') }}</p>
    </div>

    {{-- Summary --}}
    <div class="summary-grid">
        <div class="summary-row">
            <div class="summary-card">
                <div class="label">Total Orders</div>
                <div class="value">{{ number_format($sales_data['total_orders']) }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Revenue</div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</div>
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

    {{-- Customer Analysis --}}
    <div class="section">
        <div class="section-title">Customer Analysis</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-right">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Customers</td>
                    <td class="text-right">{{ number_format($customer_analysis['total_customers']) }}</td>
                </tr>
                <tr>
                    <td>New Customers</td>
                    <td class="text-right">{{ number_format($customer_analysis['new_customers']) }}</td>
                </tr>
                <tr>
                    <td>Returning Customers</td>
                    <td class="text-right">{{ number_format($customer_analysis['returning_customers']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Category Breakdown --}}
    <div class="section">
        <div class="section-title">Sales by Category</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Units Sold</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category_breakdown as $category)
                <tr>
                    <td>{{ $category['category'] }}</td>
                    <td class="text-right">{{ number_format($category['units_sold']) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Page Break --}}
    <div class="page-break"></div>

    {{-- Top Products --}}
    <div class="section">
        <div class="section-title">Top Selling Products</div>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-right">Units</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_products as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td><span class="badge">{{ $product->sku }}</span></td>
                    <td class="text-right">{{ number_format($product->total_sold) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($product->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Sales Report {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }} | Generated by {{ config('app.name') }}</p>
    </div>
</body>
</html>
