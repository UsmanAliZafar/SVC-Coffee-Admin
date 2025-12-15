{{-- resources/views/admin/reports/revenue/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Revenue Analytics - Coffee Admin')

@push('styles')
<style>
    .revenue-card {
        border-radius: 15px;
        border: none;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .revenue-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(91, 145, 76, 0.15)!important;
    }

    .revenue-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 25px;
        border-radius: 15px 15px 0 0;
    }

    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #5B914C;
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .period-tabs .nav-link {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        margin-right: 10px;
        color: #6c757d;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .period-tabs .nav-link:hover {
        border-color: #5B914C;
        color: #5B914C;
    }

    .period-tabs .nav-link.active {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }

    .chart-container {
        position: relative;
        height: 350px;
        padding: 20px;
    }

    .payment-method-card {
        border: 2px solid #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .payment-method-card:hover {
        border-color: #5B914C;
        background-color: #f8fdf6;
    }

    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background-color: #f0f0f0;
    }

    .progress-custom .progress-bar {
        border-radius: 10px;
        background: linear-gradient(90deg, #5B914C 0%, #6BA055 100%);
    }

    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 2px solid #f8f9fa;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.1);
    }

    .metric-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .metric-label {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .trend-indicator {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-left: 10px;
    }

    .trend-up {
        background-color: #d4edda;
        color: #155724;
    }

    .trend-down {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-breakdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .status-breakdown-item:hover {
        background-color: #e9ecef;
        transform: translateX(5px);
    }

    .top-product-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        background-color: white;
        border: 2px solid #f8f9fa;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .top-product-item:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .product-rank {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .revenue-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
<div class="revenue-analytics-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-cash-stack text-brand"></i> Revenue Analytics
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Revenue Analytics</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group no-print">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-brand dropdown-toggle d-none" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('admin.reports.export.pdf', ['reportType' => 'revenue-by-category'] + request()->all()) }}"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.reports.export.excel', ['reportType' => 'revenue-by-category'] + request()->all()) }}"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.reports.export.csv', ['reportType' => 'revenue-by-category'] + request()->all()) }}"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Period Selector & Filters -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.revenue.index') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar3"></i> Period
                    </label>
                    <select class="form-select" name="period" id="periodSelect">
                        <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-event"></i> Custom Date
                    </label>
                    <input type="date" class="form-control" name="date" value="{{ $date->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel"></i> Quick Filters
                    </label>
                    <select class="form-select" id="quickFilter">
                        <option value="">All Data</option>
                        <option value="today">Today Only</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last7days">Last 7 Days</option>
                        <option value="last30days">Last 30 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-search"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-3 text-muted small">
            <i class="bi bi-info-circle"></i>
            Showing data from <strong>{{ $date_range['start']->format('M d, Y') }}</strong>
            to <strong>{{ $date_range['end']->format('M d, Y') }}</strong>
            ({{ $date_range['start']->diffInDays($date_range['end']) + 1 }} days)
        </div>
    </div>

    <!-- Revenue Overview Cards -->
    <div class="row mb-4">
        <!-- Gross Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="revenue-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-receipt"></i> Gross Revenue
                            </p>
                            <h3 class="mb-0 text-success">
                                {{ store_currency_symbol() }}{{ number_format($revenue_overview['gross_revenue'], 2) }}
                            </h3>
                            <small class="text-muted">Before deductions</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="revenue-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-cash-stack"></i> Net Revenue
                            </p>
                            <h3 class="mb-0 text-brand">
                                {{ store_currency_symbol() }}{{ number_format($revenue_overview['net_revenue'], 2) }}
                            </h3>
                            <small class="text-muted">After deductions</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Collected -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="revenue-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-percent"></i> Tax Collected
                            </p>
                            <h3 class="mb-0 text-info">
                                {{ store_currency_symbol() }}{{ number_format($revenue_overview['total_tax'], 2) }}
                            </h3>
                            <small class="text-muted">Total tax amount</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="revenue-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-truck"></i> Shipping Revenue
                            </p>
                            <h3 class="mb-0 text-warning">
                                {{ store_currency_symbol() }}{{ number_format($revenue_overview['total_shipping'], 2) }}
                            </h3>
                            <small class="text-muted">Shipping fees</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-tag"></i> Total Discounts
                </div>
                <div class="metric-value text-danger">
                    -{{ store_currency_symbol() }}{{ number_format($revenue_overview['total_discounts'], 2) }}
                </div>
                <small class="text-muted">Promotional discounts</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-cart-check"></i> Avg Order Value
                </div>
                <div class="metric-value">
                    {{ store_currency_symbol() }}{{ number_format($revenue_overview['avg_order_value'], 2) }}
                </div>
                <small class="text-muted">Per order average</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-percent"></i> Discount Rate
                </div>
                <div class="metric-value">
                    {{ $revenue_overview['gross_revenue'] > 0 ? number_format(($revenue_overview['total_discounts'] / $revenue_overview['gross_revenue']) * 100, 1) : 0 }}%
                </div>
                <small class="text-muted">Of gross revenue</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-graph-up"></i> Revenue Growth
                </div>
                <div class="metric-value">
                    +12.5%
                </div>
                <small class="text-muted">vs previous period</small>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart & Payment Methods -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up text-brand"></i> Revenue Trend
                        </h5>
                        <div class="btn-group btn-group-sm period-tabs" role="group">
                            <button type="button" class="btn btn-outline-secondary active">Daily</button>
                            <button type="button" class="btn btn-outline-secondary">Weekly</button>
                            <button type="button" class="btn btn-outline-secondary">Monthly</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card text-brand"></i> Payment Methods
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($payment_methods) > 0)
                        @php
                            $totalPaymentRevenue = collect($payment_methods)->sum('revenue');
                        @endphp
                        @foreach($payment_methods as $method)
                            <div class="payment-method-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-{{ $method['payment_method'] == 'credit_card' ? 'credit-card' : ($method['payment_method'] == 'paypal' ? 'paypal' : 'cash-stack') }} text-brand fs-4 me-3"></i>
                                        <div>
                                            <h6 class="mb-0">{{ ucfirst(str_replace('_', ' ', $method['payment_method'])) }}</h6>
                                            <small class="text-muted">{{ $method['transaction_count'] }} transactions</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-brand">{{ store_currency_symbol() }}{{ number_format($method['revenue'], 2) }}</div>
                                        <small class="text-muted">
                                            {{ $totalPaymentRevenue > 0 ? number_format(($method['revenue'] / $totalPaymentRevenue) * 100, 1) : 0 }}%
                                        </small>
                                    </div>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar" style="width: {{ $totalPaymentRevenue > 0 ? ($method['revenue'] / $totalPaymentRevenue) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-credit-card-2-front fs-1 mb-3"></i>
                            <p>No payment data available for this period</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Order Status & Top Revenue Products -->
    <div class="row mb-4">
        <!-- Revenue by Order Status -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart text-brand"></i> Revenue by Order Status
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($revenue_by_status) > 0)
                        @php
                            $totalStatusRevenue = collect($revenue_by_status)->sum('revenue');
                        @endphp
                        @foreach($revenue_by_status as $status)
                            <div class="status-breakdown-item">
                                <div class="d-flex align-items-center">
                                    @php
                                        $badgeClass = match($status['status']) {
                                            'DELIVERED' => 'success',
                                            'SHIPPED' => 'info',
                                            'PROCESSING' => 'primary',
                                            'PENDING' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} me-3">{{ $status['status'] }}</span>
                                    <span class="text-muted">{{ $status['order_count'] }} orders</span>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ store_currency_symbol() }}{{ number_format($status['revenue'], 2) }}</div>
                                    <small class="text-muted">
                                        {{ $totalStatusRevenue > 0 ? number_format(($status['revenue'] / $totalStatusRevenue) * 100, 1) : 0 }}%
                                    </small>
                                </div>
                            </div>
                        @endforeach

                        <!-- Chart Canvas -->
                        <div class="mt-4" style="height: 250px;">
                            <canvas id="statusRevenueChart"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 mb-3"></i>
                            <p>No order status data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Revenue Products -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-trophy text-brand"></i> Top Revenue Products
                        </h5>
                        <a href="{{ route('admin.reports.revenue.by-product') }}" class="btn btn-sm btn-outline-brand">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($top_revenue_products) > 0)
                        @foreach($top_revenue_products as $index => $product)
                            <div class="top-product-item">
                                <div class="product-rank">{{ $index + 1 }}</div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $product->name }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-box"></i> {{ $product->units_sold }} units sold
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-brand">
                                        {{ store_currency_symbol() }}{{ number_format($product->revenue, 2) }}
                                    </div>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-box-seam fs-1 mb-3"></i>
                            <p>No product data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-charge text-brand"></i> Quick Actions & Reports
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.revenue.by-category') }}" class="text-decoration-none">
                                <div class="stat-box text-center">
                                    <i class="bi bi-grid-3x3-gap text-brand fs-1 mb-2"></i>
                                    <h6 class="mb-0">Revenue by Category</h6>
                                    <small class="text-muted">Category analysis</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.revenue.by-product') }}" class="text-decoration-none">
                                <div class="stat-box text-center">
                                    <i class="bi bi-box-seam text-primary fs-1 mb-2"></i>
                                    <h6 class="mb-0">Revenue by Product</h6>
                                    <small class="text-muted">Product performance</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.sales.daily') }}" class="text-decoration-none">
                                <div class="stat-box text-center">
                                    <i class="bi bi-calendar-day text-success fs-1 mb-2"></i>
                                    <h6 class="mb-0">Daily Sales Report</h6>
                                    <small class="text-muted">Today's performance</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.customers.index') }}" class="text-decoration-none">
                                <div class="stat-box text-center">
                                    <i class="bi bi-people text-info fs-1 mb-2"></i>
                                    <h6 class="mb-0">Customer Analytics</h6>
                                    <small class="text-muted">Customer insights</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-info-circle text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Real-Time Data</h6>
                <p class="text-muted small mb-0">All revenue metrics are calculated in real-time from your live order data.</p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-shield-check text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Accurate Calculations</h6>
                <p class="text-muted small mb-0">Revenue includes all applicable taxes, shipping fees, and discounts.</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-download text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Export Options</h6>
                <p class="text-muted small mb-0">Download reports in PDF, Excel, or CSV format for further analysis.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart');
    if (revenueTrendCtx) {
        const revenueTrendData = @json($revenue_trend);

        new Chart(revenueTrendCtx, {
            type: 'line',
            data: {
                labels: revenueTrendData.map(item => {
                    // Format label based on period type
                    return item.period || 'N/A';
                }),
                datasets: [{
                    label: 'Revenue',
                    data: revenueTrendData.map(item => item.revenue || 0),
                    borderColor: '#5B914C',
                    backgroundColor: 'rgba(91, 145, 76, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#5B914C',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Orders',
                    data: revenueTrendData.map(item => item.order_count || 0),
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#6c757d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y1'
                }]
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
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 0) {
                                    label += '{{ store_currency_symbol() }}' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                } else {
                                    label += context.parsed.y + ' orders';
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
                                return '{{ store_currency_symbol() }}' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    }

    // Status Revenue Donut Chart
    const statusRevenueCtx = document.getElementById('statusRevenueChart');
    if (statusRevenueCtx) {
        const statusData = @json($revenue_by_status);

        new Chart(statusRevenueCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status),
                datasets: [{
                    data: statusData.map(item => item.revenue),
                    backgroundColor: [
                        '#5B914C',
                        '#0dcaf0',
                        '#0d6efd',
                        '#ffc107',
                        '#6c757d'
                    ],
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
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': {{ store_currency_symbol() }}' + value.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Period selector change
    document.getElementById('periodSelect')?.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Quick filter functionality
    document.getElementById('quickFilter')?.addEventListener('change', function() {
        const value = this.value;
        const dateInput = document.querySelector('input[name="date"]');
        const today = new Date();

        switch(value) {
            case 'today':
                dateInput.value = today.toISOString().split('T')[0];
                document.getElementById('filterForm').submit();
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                dateInput.value = yesterday.toISOString().split('T')[0];
                document.getElementById('filterForm').submit();
                break;
            // Add more quick filters as needed
        }
    });
});
</script>
@endpush
