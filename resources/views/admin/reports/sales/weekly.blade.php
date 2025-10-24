{{-- resources/views/admin/reports/sales/weekly.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Weekly Sales Report - Coffee Admin')

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

    .daily-chart-container {
        height: 350px;
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

    .week-navigator {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .daily-table td, .daily-table th {
        vertical-align: middle;
    }

    .day-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 600;
        min-width: 100px;
        text-align: center;
    }

    .weekend-row {
        background-color: #f8f9fa;
    }

    .today-row {
        background-color: #e7f3e7;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-2">
                <i class="bi bi-calendar-week text-brand"></i> Weekly Sales Report
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.sales.monthly') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Weekly</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export
            </button>
            <a href="{{ route('admin.reports.sales.monthly') }}" class="btn btn-brand">
                <i class="bi bi-calendar-month"></i> Monthly Report
            </a>
        </div>
    </div>

    <!-- Week Navigator -->
    <div class="week-navigator mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                @php
                    $prevWeek = $week_number - 1;
                    $prevYear = $year;
                    if ($prevWeek < 1) {
                        $prevWeek = 52;
                        $prevYear = $year - 1;
                    }
                @endphp
                <a href="{{ route('admin.reports.sales.weekly', ['week' => $prevWeek, 'year' => $prevYear]) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i> Previous Week
                </a>
            </div>
            <div class="col-md-4 text-center">
                <h4 class="mb-0">
                    Week {{ $week_number }}, {{ $year }}
                </h4>
                <small class="text-muted">
                    {{ $start_date->format('M d') }} - {{ $end_date->format('M d, Y') }}
                </small>
            </div>
            <div class="col-md-4 text-end">
                @php
                    $nextWeek = $week_number + 1;
                    $nextYear = $year;
                    if ($nextWeek > 52) {
                        $nextWeek = 1;
                        $nextYear = $year + 1;
                    }
                    $isNextWeekFuture = \Carbon\Carbon::now()->setISODate($nextYear, $nextWeek)->isFuture();
                @endphp
                @if(!$isNextWeekFuture)
                <a href="{{ route('admin.reports.sales.weekly', ['week' => $nextWeek, 'year' => $nextYear]) }}"
                   class="btn btn-outline-secondary">
                    Next Week <i class="bi bi-chevron-right"></i>
                </a>
                @else
                <button class="btn btn-outline-secondary" disabled>
                    Next Week <i class="bi bi-chevron-right"></i>
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
                        {{ number_format(abs($comparison['orders_change']), 1) }}% vs Last Week
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
                        {{ number_format(abs($comparison['revenue_change']), 1) }}% vs Last Week
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
        <!-- Daily Sales Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-brand"></i> Daily Sales Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="daily-chart-container">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products This Week -->
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-star text-brand"></i> Top Products This Week
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($top_products_week as $index => $product)
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
                        <p class="mt-2">No products sold this week</p>
                    </div>
                    @endforelse
                </div>
                @if($top_products_week->count() > 0)
                <div class="card-footer bg-white border-0 text-center">
                    <a href="{{ route('admin.reports.products.top-selling', ['period' => 'week']) }}" class="btn btn-sm btn-outline-brand">
                        View All Products <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Daily Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-day text-brand"></i> Daily Performance Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover daily-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Avg Order Value</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxRevenue = collect($daily_breakdown)->max('revenue');
                                    $today = \Carbon\Carbon::today();
                                @endphp
                                @foreach($daily_breakdown as $day)
                                @php
                                    $dayDate = \Carbon\Carbon::parse($day['date']);
                                    $isWeekend = in_array($day['day_name'], ['Saturday', 'Sunday']);
                                    $isToday = $dayDate->isToday();
                                    $rowClass = $isToday ? 'today-row' : ($isWeekend ? 'weekend-row' : '');
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <strong>{{ $day['date_label'] }}</strong>
                                        @if($isToday)
                                            <span class="badge bg-success ms-2">Today</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="day-badge {{ $isWeekend ? 'bg-light text-dark' : 'bg-primary bg-opacity-10 text-primary' }}">
                                            {{ $day['day_name'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($day['order_count']) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ store_currency_symbol() }}{{ number_format($day['revenue'], 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        @if($day['order_count'] > 0)
                                            {{ store_currency_symbol() }}{{ number_format($day['avg_order_value'], 2) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-brand"
                                                 role="progressbar"
                                                 style="width: {{ $maxRevenue > 0 ? ($day['revenue'] / $maxRevenue * 100) : 0 }}%"
                                                 aria-valuenow="{{ $day['revenue'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="{{ $maxRevenue }}">
                                                @if($day['revenue'] > 0 && $maxRevenue > 0)
                                                    {{ number_format(($day['revenue'] / $maxRevenue * 100), 0) }}%
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Weekly Total / Average</th>
                                    <th class="text-end">{{ number_format($sales_data['total_orders']) }}</th>
                                    <th class="text-end">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</th>
                                    <th class="text-end">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights -->
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy text-warning fs-1 mb-3"></i>
                    <h5>Best Day</h5>
                    @php
                        $bestDay = collect($daily_breakdown)->sortByDesc('revenue')->first();
                    @endphp
                    @if($bestDay)
                        <p class="mb-1"><strong>{{ $bestDay['day_name'] }}</strong></p>
                        <p class="text-muted mb-0">{{ store_currency_symbol() }}{{ number_format($bestDay['revenue'], 2) }}</p>
                    @else
                        <p class="text-muted">No data</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-success fs-1 mb-3"></i>
                    <h5>Daily Average</h5>
                    <p class="mb-1">
                        <strong>{{ store_currency_symbol() }}{{ number_format($sales_data['avg_daily_revenue'], 2) }}</strong>
                    </p>
                    <p class="text-muted mb-0">{{ number_format($sales_data['total_orders'] / 7, 1) }} orders/day</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check text-info fs-1 mb-3"></i>
                    <h5>Active Days</h5>
                    @php
                        $activeDays = collect($daily_breakdown)->filter(fn($day) => $day['order_count'] > 0)->count();
                    @endphp
                    <p class="mb-1"><strong>{{ $activeDays }} out of 7</strong></p>
                    <p class="text-muted mb-0">{{ number_format(($activeDays / 7) * 100, 0) }}% activity rate</p>
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
                        <strong>Note:</strong> Week numbers follow ISO 8601 standard (Monday as first day of week).
                        Data includes orders with Processing, Shipped, and Delivered status.
                        Weekend days are highlighted for easy identification.
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
    // Daily Sales Chart
    const ctx = document.getElementById('dailySalesChart').getContext('2d');

    const dailySalesData = @json($daily_breakdown);

    const labels = dailySalesData.map(item => item.date_label);
    const revenueData = dailySalesData.map(item => item.revenue);
    const orderData = dailySalesData.map(item => item.order_count);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenueData,
                    backgroundColor: 'rgba(91, 145, 76, 0.7)',
                    borderColor: 'rgba(91, 145, 76, 1)',
                    borderWidth: 2,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Orders',
                    data: orderData,
                    type: 'line',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                    order: 1
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
                    ticks: {
                        callback: function(value) {
                            return '{{ store_currency_symbol() }}' + value.toFixed(0);
                        }
                    }
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
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Print functionality
    window.onbeforeprint = function() {
        document.querySelectorAll('.btn-group, .week-navigator .btn').forEach(el => {
            el.style.display = 'none';
        });
    };

    window.onafterprint = function() {
        document.querySelectorAll('.btn-group, .week-navigator .btn').forEach(el => {
            el.style.display = '';
        });
    };
</script>
@endpush
