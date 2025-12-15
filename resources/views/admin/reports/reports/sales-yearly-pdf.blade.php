{{-- resources/views/admin/reports/exports/sales-yearly-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yearly Sales Report - {{ $year }}</title>
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
        <h1>Yearly Sales Report</h1>
        <p>Year {{ $year }}</p>
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
                <div class="label">Avg Monthly Revenue</div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_monthly_revenue'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Monthly Breakdown --}}
    <div class="section">
        <div class="section-title">Monthly Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">% of Year</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $year_total = collect($monthly_breakdown)->sum('revenue');
                @endphp
                @foreach($monthly_breakdown as $month)
                <tr>
                    <td>{{ $month['month_name'] }}</td>
                    <td class="text-right">{{ number_format($month['order_count']) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($month['revenue'], 2) }}</td>
                    <td class="text-right">
                        {{ number_format($year_total > 0 ? ($month['revenue'] / $year_total) * 100 : 0, 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Quarterly Breakdown --}}
    <div class="section">
        <div class="section-title">Quarterly Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Quarter</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Growth</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quarterly_breakdown as $index => $quarter)
                <tr>
                    <td>{{ $quarter['label'] }}</td>
                    <td class="text-right">{{ number_format($quarter['order_count']) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($quarter['revenue'], 2) }}</td>
                    <td class="text-right">
                        @if($index > 0)
                            @php
                                $prev_revenue = $quarterly_breakdown[$index - 1]['revenue'];
                                $growth = $prev_revenue > 0 ? (($quarter['revenue'] - $prev_revenue) / $prev_revenue) * 100 : 0;
                            @endphp
                            {{ $growth >= 0 ? '↑' : '↓' }} {{ number_format(abs($growth), 1) }}%
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Page Break --}}
    <div class="page-break"></div>

    {{-- Category Breakdown --}}
    <div class="section">
        <div class="section-title">Sales by Category</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Units Sold</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_revenue = collect($category_breakdown)->sum('revenue');
                @endphp
                @foreach($category_breakdown as $category)
                <tr>
                    <td>{{ $category['category'] }}</td>
                    <td class="text-right">{{ number_format($category['units_sold']) }}</td>
                    <td class="text-right">{{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}</td>
                    <td class="text-right">
                        {{ number_format($total_revenue > 0 ? ($category['revenue'] / $total_revenue) * 100 : 0, 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Top Products --}}
    <div class="section">
        <div class="section-title">Top 20 Products of the Year</div>
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
                @foreach($top_products_year as $index => $product)
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
        <p>Yearly Sales Report - {{ $year }} | Generated by {{ config('app.name') }}</p>
    </div>
</body>
</html>
