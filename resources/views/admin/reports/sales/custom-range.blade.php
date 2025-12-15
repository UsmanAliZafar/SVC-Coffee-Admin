{{-- resources/views/admin/reports/sales/custom-range.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Custom Range Sales Report - Coffee Admin')

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

    .custom-chart-container {
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

    .date-range-selector {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .quick-range-btn {
        border-radius: 20px;
        padding: 8px 16px;
        font-size: 0.875rem;
    }

    .customer-badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 0.875rem;
    }

    .insights-card {
        border-left: 4px solid #5B914C;
        transition: all 0.3s ease;
    }

    .insights-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-2">
                <i class="bi bi-calendar2-range text-brand"></i> Custom Range Sales Report
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Custom Range</li>
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
            <a href="{{ route('admin.reports.sales.monthly') }}" class="btn btn-brand">
                <i class="bi bi-calendar-month"></i> Monthly Report
            </a>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="date-range-selector mb-4">
        <form method="GET" action="{{ route('admin.reports.sales.custom-range') }}" id="dateRangeForm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-calendar-event"></i> Start Date
                    </label>
                    <input type="date"
                           name="start_date"
                           id="startDate"
                           class="form-control"
                           value="{{ $start_date->format('Y-m-d') }}"
                           max="{{ date('Y-m-d') }}"
                           required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-calendar-check"></i> End Date
                    </label>
                    <input type="date"
                           name="end_date"
                           id="endDate"
                           class="form-control"
                           value="{{ $end_date->format('Y-m-d') }}"
                           max="{{ date('Y-m-d') }}"
                           required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-search"></i> Generate Report
                    </button>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Quick Select:</label>
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-range-btn" onclick="setQuickRange('week')">Last 7 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-range-btn" onclick="setQuickRange('month')">Last 30 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-range-btn" onclick="setQuickRange('quarter')">Last 90 Days</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-3 text-center">
            <div class="alert alert-light mb-0 d-inline-block">
                <i class="bi bi-calendar3"></i>
                <strong>Selected Period:</strong>
                {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
                <span class="badge bg-brand ms-2">{{ $days_count }} days</span>
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
                <div class="mt-2">
                    <small class="text-muted">
                        {{ number_format($sales_data['total_orders'] / $days_count, 1) }} orders/day
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</div>
                <div class="label">Total Revenue</div>
                <div class="mt-2">
                    <small class="text-muted">
                        {{ store_currency_symbol() }}{{ number_format($sales_data['avg_daily_revenue'], 2) }}/day
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</div>
                <div class="label">Average Order Value</div>
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
                        {{ number_format($sales_data['items_sold'] / max($sales_data['total_orders'], 1), 1) }} items/order
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Sales Trend -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-brand"></i> Sales Trend ({{ $days_count }} Days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="custom-chart-container">
                        <canvas id="customRangeChart"></canvas>
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

    <!-- Top Products & Customer Analysis -->
    <div class="row mb-4">
        <!-- Top Products -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-star text-brand"></i> Top 20 Products
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    @forelse($top_products as $index => $product)
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
                        <p class="mt-2">No products sold in this period</p>
                    </div>
                    @endforelse
                </div>
                @if($top_products->count() > 0)
                <div class="card-footer bg-white border-0 text-center">
                    <a href="{{ route('admin.reports.products.top-selling') }}" class="btn btn-sm btn-outline-brand">
                        View All Products <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Analysis -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-people text-brand"></i> Customer Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="insights-card card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-people-fill text-primary fs-1 mb-2"></i>
                                    <h3 class="mb-1">{{ number_format($customer_analysis['total_customers']) }}</h3>
                                    <small class="text-muted">Total Customers</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="insights-card card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-plus-fill text-success fs-1 mb-2"></i>
                                    <h3 class="mb-1">{{ number_format($customer_analysis['new_customers']) }}</h3>
                                    <small class="text-muted">New Customers</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="insights-card card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-arrow-repeat text-info fs-1 mb-2"></i>
                                    <h3 class="mb-1">{{ number_format($customer_analysis['returning_customers']) }}</h3>
                                    <small class="text-muted">Returning Customers</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="insights-card card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-percent text-warning fs-1 mb-2"></i>
                                    <h3 class="mb-1">
                                        {{ number_format(($customer_analysis['returning_customers'] / max($customer_analysis['total_customers'], 1)) * 100, 1) }}%
                                    </h3>
                                    <small class="text-muted">Retention Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.reports.customers.new-vs-returning', [
                            'start_date' => $start_date->format('Y-m-d'),
                            'end_date' => $end_date->format('Y-m-d')
                        ]) }}" class="btn btn-outline-brand w-100">
                            <i class="bi bi-graph-up"></i> View Detailed Customer Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Performance Details -->
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

    <!-- Period Insights -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy text-warning fs-1 mb-3"></i>
                    <h6>Best Day</h6>
                    @php
                        $bestDay = collect($daily_breakdown)->sortByDesc('revenue')->first();
                    @endphp
                    @if($bestDay)
                        <p class="mb-1"><strong>{{ $bestDay['date_label'] }}</strong></p>
                        <p class="text-muted mb-0">{{ store_currency_symbol() }}{{ number_format($bestDay['revenue'], 2) }}</p>
                    @else
                        <p class="text-muted">No data</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-down text-danger fs-1 mb-3"></i>
                    <h6>Slowest Day</h6>
                    @php
                        $slowestDay = collect($daily_breakdown)->where('revenue', '>', 0)->sortBy('revenue')->first();
                    @endphp
                    @if($slowestDay)
                        <p class="mb-1"><strong>{{ $slowestDay['date_label'] }}</strong></p>
                        <p class="text-muted mb-0">{{ store_currency_symbol() }}{{ number_format($slowestDay['revenue'], 2) }}</p>
                    @else
                        <p class="text-muted">No data</p>
                    @endif
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
                    <i class="bi bi-calendar-check text-success fs-1 mb-3"></i>
                    <h6>Active Days</h6>
                    @php
                        $activeDays = collect($daily_breakdown)->filter(fn($day) => $day['order_count'] > 0)->count();
                    @endphp
                    <p class="mb-1"><strong>{{ $activeDays }} out of {{ $days_count }}</strong></p>
                    <p class="text-muted mb-0">{{ number_format(($activeDays / $days_count) * 100, 0) }}% activity rate</p>
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
                        <strong>Note:</strong> Custom range reports include all orders with Processing, Shipped, and Delivered status.
                        Daily averages are calculated based on the selected {{ $days_count }}-day period.
                        You can select any date range up to today.
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
    // Quick Range Selection
    function setQuickRange(range) {
        const today = new Date();
        const endDate = new Date();
        let startDate = new Date();

        switch(range) {
            case 'week':
                startDate.setDate(today.getDate() - 7);
                break;
            case 'month':
                startDate.setDate(today.getDate() - 30);
                break;
            case 'quarter':
                startDate.setDate(today.getDate() - 90);
                break;
        }

        document.getElementById('startDate').value = formatDate(startDate);
        document.getElementById('endDate').value = formatDate(endDate);
        document.getElementById('dateRangeForm').submit();
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Daily Sales Trend Chart
    const ctx1 = document.getElementById('customRangeChart').getContext('2d');
    const dailySalesData = @json($daily_breakdown);

    const labels = dailySalesData.map(item => item.date_label.split(',')[0]);
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

    // Date validation
    document.getElementById('startDate').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDate = new Date(document.getElementById('endDate').value);

        if (startDate > endDate) {
            document.getElementById('endDate').value = this.value;
        }
    });

    document.getElementById('endDate').addEventListener('change', function() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(this.value);

        if (endDate < startDate) {
            document.getElementById('startDate').value = this.value;
        }
    });

    // Print functionality
    window.onbeforeprint = function() {
        document.querySelectorAll('.btn-group, .date-range-selector form').forEach(el => {
            el.style.display = 'none';
        });
    };

    window.onafterprint = function() {
        document.querySelectorAll('.btn-group, .date-range-selector form').forEach(el => {
            el.style.display = '';
        });
    };
</script>
@endpush
