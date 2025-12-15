{{-- resources/views/admin/reports/sales/monthly.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Monthly Sales Report - Coffee Admin')

@push('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-box .icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 15px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-box .value {
        font-size: 2rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 5px;
    }

    .stat-box .label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .monthly-chart-container {
        height: 350px;
        margin: 20px 0;
    }

    .category-chart-container {
        height: 300px;
        margin: 20px 0;
    }

    .product-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }

    .product-item:hover {
        background-color: #f8f9fa;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-rank {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #5B914C;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
    }

    .comparison-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .comparison-up {
        background-color: #d4edda;
        color: #155724;
    }

    .comparison-down {
        background-color: #f8d7da;
        color: #721c24;
    }

    .month-navigator {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .week-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #5B914C;
        transition: all 0.3s ease;
    }

    .week-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .category-item {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
    }

    .category-item:last-child {
        border-bottom: none;
    }

    .month-select {
        min-width: 150px;
    }

    .year-select {
        min-width: 100px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-2">
                <i class="bi bi-calendar-month text-brand"></i> Monthly Sales Report
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Monthly</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary d-none">
                <i class="bi bi-download"></i> Export
            </button>
            <a href="{{ route('admin.reports.sales.yearly') }}" class="btn btn-brand">
                <i class="bi bi-calendar-range"></i> Yearly Report
            </a>
        </div>
    </div>

    <!-- Month Navigator -->
    <div class="month-navigator mb-4">
        <div class="row align-items-center">
            <div class="col-md-3">
                @php
                    $prevMonth = $month - 1;
                    $prevYear = $year;
                    if ($prevMonth < 1) {
                        $prevMonth = 12;
                        $prevYear = $year - 1;
                    }
                @endphp
                <a href="{{ route('admin.reports.sales.monthly', ['month' => $prevMonth, 'year' => $prevYear]) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i> Previous Month
                </a>
            </div>
            <div class="col-md-6 text-center">
                <h4 class="mb-2">
                    {{ $month_name }} {{ $year }}
                </h4>
                <small class="text-muted">
                    {{ $start_date->format('M d') }} - {{ $end_date->format('M d, Y') }}
                    ({{ $start_date->daysInMonth }} days)
                </small>
                <div class="mt-3">
                    <form method="GET" action="{{ route('admin.reports.sales.monthly') }}" class="d-inline-flex gap-2">
                        <select name="month" class="form-select form-select-sm month-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year" class="form-select form-select-sm year-select">
                            @foreach(range(date('Y') - 5, date('Y')) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-brand">Go</button>
                    </form>
                </div>
            </div>
            <div class="col-md-3 text-end">
                @php
                    $nextMonth = $month + 1;
                    $nextYear = $year;
                    if ($nextMonth > 12) {
                        $nextMonth = 1;
                        $nextYear = $year + 1;
                    }
                    $isNextMonthFuture = \Carbon\Carbon::create($nextYear, $nextMonth, 1)->isFuture();
                @endphp
                @if(!$isNextMonthFuture)
                <a href="{{ route('admin.reports.sales.monthly', ['month' => $nextMonth, 'year' => $nextYear]) }}"
                   class="btn btn-outline-secondary">
                    Next Month <i class="bi bi-chevron-right"></i>
                </a>
                @else
                <button class="btn btn-outline-secondary" disabled>
                    Next Month <i class="bi bi-chevron-right"></i>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="value">{{ number_format($sales_data['total_orders']) }}</div>
                <div class="label">Total Orders</div>
                @if(isset($comparison['orders_change']))
                <div class="mt-2">
                    <span class="comparison-badge {{ $comparison['orders_change'] >= 0 ? 'comparison-up' : 'comparison-down' }}">
                        <i class="bi bi-{{ $comparison['orders_change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($comparison['orders_change']), 1) }}% vs Last Month
                    </span>
                </div>
                @endif
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</div>
                <div class="label">Total Revenue</div>
                @if(isset($comparison['revenue_change']))
                <div class="mt-2">
                    <span class="comparison-badge {{ $comparison['revenue_change'] >= 0 ? 'comparison-up' : 'comparison-down' }}">
                        <i class="bi bi-{{ $comparison['revenue_change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($comparison['revenue_change']), 1) }}% vs Last Month
                    </span>
                </div>
                @endif
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</div>
                <div class="label">Average Order Value</div>
                <div class="mt-2">
                    <small class="text-muted">
                        Daily Avg: {{ store_currency_symbol() }}{{ number_format($sales_data['avg_daily_revenue'], 2) }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="value">{{ number_format($sales_data['items_sold']) }}</div>
                <div class="label">Items Sold</div>
                <div class="mt-2">
                    <small class="text-muted">
                        Avg per Order: {{ number_format($sales_data['items_sold'] / max($sales_data['total_orders'], 1), 1) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Sales Trend Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-brand"></i> Daily Sales Trend
                    </h5>
                </div>
                <div class="card-body">
                    <div class="monthly-chart-container">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart text-brand"></i> Category Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="category-chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Breakdown & Top Products -->
    <div class="row mb-4">
        <!-- Weekly Performance -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week text-brand"></i> Weekly Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($weekly_breakdown as $index => $week)
                    <div class="week-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $week['label'] }}</h6>
                                <small class="text-muted">{{ $week['order_count'] }} orders</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($week['revenue'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar bg-brand"
                                 style="width: {{ $sales_data['total_revenue'] > 0 ? ($week['revenue'] / $sales_data['total_revenue'] * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No weekly data available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Top Products This Month -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-star text-brand"></i> Top Products This Month
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    @forelse($top_products_month as $index => $product)
                    <div class="product-item">
                        <div class="product-rank">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                            <div class="mt-1">
                                <span class="badge bg-success">{{ number_format($product->total_sold) }} sold</span>
                                <span class="badge bg-primary ms-1">{{ store_currency_symbol() }}{{ number_format($product->total_revenue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No products sold this month</p>
                    </div>
                    @endforelse
                </div>
                @if($top_products_month->count() > 0)
                <div class="card-footer bg-white border-0 text-center">
                    <a href="{{ route('admin.reports.products.top-selling', ['period' => 'month']) }}" class="btn btn-sm btn-outline-brand">
                        View All Products <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Category Sales Details -->
    @if(count($category_breakdown) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-grid text-brand"></i> Category Performance Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Units Sold</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Avg Order Value</th>
                                    <th class="text-center">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category_breakdown as $category)
                                <tr>
                                    <td>
                                        <strong>{{ $category['category'] }}</strong>
                                    </td>
                                    <td class="text-end">{{ number_format($category['order_count']) }}</td>
                                    <td class="text-end">{{ number_format($category['units_sold']) }}</td>
                                    <td class="text-end">
                                        <strong>{{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        {{ store_currency_symbol() }}{{ number_format($category['revenue'] / max($category['order_count'], 1), 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-brand">
                                            {{ number_format(($category['revenue'] / $sales_data['total_revenue']) * 100, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">{{ number_format($sales_data['total_orders']) }}</th>
                                    <th class="text-end">{{ number_format($sales_data['items_sold']) }}</th>
                                    <th class="text-end">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</th>
                                    <th class="text-end">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</th>
                                    <th class="text-center">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Month Insights -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy text-warning fs-1 mb-3"></i>
                    <h6>Best Week</h6>
                    @php
                        $bestWeek = collect($weekly_breakdown)->sortByDesc('revenue')->first();
                    @endphp
                    @if($bestWeek)
                        <p class="mb-1"><strong>{{ $bestWeek['label'] }}</strong></p>
                        <p class="text-muted mb-0">{{ store_currency_symbol() }}{{ number_format($bestWeek['revenue'], 2) }}</p>
                    @else
                        <p class="text-muted">No data</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-success fs-1 mb-3"></i>
                    <h6>Daily Average</h6>
                    <p class="mb-1">
                        <strong>{{ store_currency_symbol() }}{{ number_format($sales_data['avg_daily_revenue'], 2) }}</strong>
                    </p>
                    <p class="text-muted mb-0">{{ number_format($sales_data['total_orders'] / $start_date->daysInMonth, 1) }} orders/day</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-star-fill text-info fs-1 mb-3"></i>
                    <h6>Top Category</h6>
                    @php
                        $topCategory = collect($category_breakdown)->sortByDesc('revenue')->first();
                    @endphp
                    @if($topCategory)
                        <p class="mb-1"><strong>{{ $topCategory['category'] }}</strong></p>
                        <p class="text-muted mb-0">{{ store_currency_symbol() }}{{ number_format($topCategory['revenue'], 2) }}</p>
                    @else
                        <p class="text-muted">No data</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check text-primary fs-1 mb-3"></i>
                    <h6>Active Days</h6>
                    @php
                        $activeDays = collect($daily_breakdown)->filter(fn($day) => $day['order_count'] > 0)->count();
                        $totalDays = $start_date->daysInMonth;
                    @endphp
                    <p class="mb-1"><strong>{{ $activeDays }} out of {{ $totalDays }}</strong></p>
                    <p class="text-muted mb-0">{{ number_format(($activeDays / $totalDays) * 100, 0) }}% activity rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info border-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Note:</strong> Monthly reports include all orders with Processing, Shipped, and Delivered status.
                        Weekly breakdown follows ISO 8601 standard. Category data shows primary product category only.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Daily Sales Trend Chart
    const ctx1 = document.getElementById('monthlySalesChart').getContext('2d');
    const dailySalesData = @json($daily_breakdown);

    const labels = dailySalesData.map(item => item.date_label.split(',')[0]); // Just day and month
    const revenueData = dailySalesData.map(item => item.revenue);
    const orderData = dailySalesData.map(item => item.order_count);

    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: 'rgba(91, 145, 76, 1)',
                    backgroundColor: 'rgba(91, 145, 76, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'Orders',
                    data: orderData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.dataset.label === 'Revenue') {
                                    label += '{{ store_currency_symbol() }}' + context.parsed.y.toFixed(2);
                                } else {
                                    label += context.parsed.y + ' orders';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue ({{ store_currency_symbol() }})'
                    },
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Category Pie Chart
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($category_breakdown);

    const categoryLabels = categoryData.map(item => item.category);
    const categoryRevenues = categoryData.map(item => item.revenue);

    const colors = [
        'rgba(91, 145, 76, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
    ];

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryRevenues,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '{{ store_currency_symbol() }}' + context.parsed.toFixed(2);

                            // Add percentage
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            label += ' (' + percentage + '%)';

                            return label;
                        }
                    }
                }
            }
        }
    });

    // Print functionality
    window.onbeforeprint = function() {
        document.querySelectorAll('.btn-group, .month-navigator .btn, .month-navigator form').forEach(el => {
            el.style.display = 'none';
        });
    };

    window.onafterprint = function() {
        document.querySelectorAll('.btn-group, .month-navigator .btn, .month-navigator form').forEach(el => {
            el.style.display = '';
        });
    };
</script>
@endpush
