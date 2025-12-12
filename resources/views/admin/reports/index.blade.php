{{-- resources/views/admin/reports/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Reports & Analytics - Coffee Admin')

@push('styles')
<style>
    .report-card {
        transition: all 0.3s ease;
        border-left: 4px solid #5B914C;
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }

    .stat-card {
        border-radius: 10px;
        overflow: hidden;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.5rem;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .quick-link-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
        color: inherit;
    }

    .quick-link-card:hover {
        border-color: #5B914C;
        background-color: #f8f9fa;
        transform: translateY(-3px);
        box-shadow: 0 .25rem .5rem rgba(0,0,0,.1);
        color: inherit;
        text-decoration: none;
    }

    .quick-link-icon {
        font-size: 2.5rem;
        color: #5B914C;
        margin-bottom: 10px;
    }

    .period-selector .btn {
        border-radius: 20px;
    }

    .summary-box {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .summary-box h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .summary-box .subtitle {
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .metric-item {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .metric-label {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 5px;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #212529;
    }

    .trend-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }

    .trend-up {
        background-color: #d4edda;
        color: #155724;
    }

    .trend-down {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-graph-up-arrow text-brand"></i> Reports & Analytics Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group period-selector me-2 d-none">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar-day"></i> Today
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary active">
                <i class="bi bi-calendar-week"></i> This Week
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar-month"></i> This Month
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar-range"></i> This Year
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-brand" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</div>

<!-- Date Range Display -->
<div class="alert alert-light border mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <i class="bi bi-calendar3"></i>
            <strong>Report Period:</strong>
            {{ $date_range['start']->format('M d, Y') }} - {{ $date_range['end']->format('M d, Y') }}
            @php
                // ✅ FIX: Use integer calculation to avoid floating point errors
                $daysDifference = $date_range['start']->startOfDay()->diffInDays($date_range['end']->startOfDay()) + 1;
            @endphp
            <span class="text-muted ms-2">({{ $daysDifference }} {{ Str::plural('day', $daysDifference) }})</span>

            @if(request()->filled('start_date'))
            <span class="badge bg-info ms-2">
                <i class="bi bi-funnel"></i> Custom Range
            </span>
            @endif
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#dateRangeModal">
                <i class="bi bi-calendar-range"></i> Change Period
            </button>

            @if(request()->filled('start_date'))
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-danger ms-2">
                <i class="bi bi-x-circle"></i> Reset
            </a>
            @endif
        </div>
    </div>
</div>

<!-- Summary Banner -->
<div class="summary-box">
    <div class="row">
        <div class="col-md-3 text-center">
            <div class="subtitle">Total Revenue</div>
            <h3>{{ store_currency_symbol() }}{{ number_format($revenue_summary['net_revenue'], 2) }}</h3>
        </div>
        <div class="col-md-3 text-center border-start border-white border-opacity-25">
            <div class="subtitle">Total Orders</div>
            <h3>{{ number_format($sales_summary['total_orders']) }}</h3>
        </div>
        <div class="col-md-3 text-center border-start border-white border-opacity-25">
            <div class="subtitle">Avg Order Value</div>
            <h3>{{ store_currency_symbol() }}{{ number_format($sales_summary['avg_order_value'], 2) }}</h3>
        </div>
        <div class="col-md-3 text-center border-start border-white border-opacity-25">
            <div class="subtitle">Items Sold</div>
            <h3>{{ number_format($sales_summary['total_items_sold']) }}</h3>
        </div>
    </div>
</div>

<!-- Key Metrics Grid -->
<div class="row mb-4">
    <!-- Sales Overview -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">
                            <i class="bi bi-cart-check"></i> Total Sales
                        </p>
                        <h3 class="mb-0">{{ store_currency_symbol() }}{{ number_format($revenue_summary['gross_revenue'], 2) }}</h3>
                        <small class="text-muted">Gross Revenue</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Sold -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">
                            <i class="bi bi-box-seam"></i> Products Sold
                        </p>
                        <h3 class="mb-0">{{ number_format($product_summary['total_products_sold']) }}</h3>
                        <small class="text-muted">{{ number_format($product_summary['unique_products_sold']) }} Unique Products</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">
                            <i class="bi bi-people"></i> Active Customers
                        </p>
                        <h3 class="mb-0">{{ number_format($customer_summary['active_customers']) }}</h3>
                        <small class="text-success">
                            <i class="bi bi-plus-circle"></i> {{ number_format($customer_summary['new_customers']) }} New
                        </small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ UPDATED: Inventory Status Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">
                            <i class="bi bi-boxes"></i> Stock Value
                        </p>
                        <h3 class="mb-0">{{ store_currency_symbol() }}{{ number_format($inventory_summary['total_stock_value'], 2) }}</h3>
                        @if($inventory_summary['products_low_stock'] > 0)
                        <small class="text-warning">
                            <i class="bi bi-exclamation-triangle"></i> {{ number_format($inventory_summary['products_low_stock']) }} Low Stock Items
                        </small>
                        @elseif($inventory_summary['products_out_of_stock'] > 0)
                        <small class="text-danger">
                            <i class="bi bi-x-circle"></i> {{ number_format($inventory_summary['products_out_of_stock']) }} Out of Stock
                        </small>
                        @else
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i> All Items In Stock
                        </small>
                        @endif
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-box"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Breakdown -->
<div class="row mb-4">
    <div class="col-xl-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-line text-brand"></i> Revenue Breakdown
                    </h5>
                    <a href="{{ route('admin.reports.revenue.index') }}" class="btn btn-sm btn-outline-brand">
                        View Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="metric-item">
                            <div class="metric-label">Net Revenue</div>
                            <div class="metric-value text-success">
                                {{ store_currency_symbol() }}{{ number_format($revenue_summary['net_revenue'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-item">
                            <div class="metric-label">Tax Collected</div>
                            <div class="metric-value text-primary">
                                {{ store_currency_symbol() }}{{ number_format($revenue_summary['tax_collected'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-item">
                            <div class="metric-label">Shipping Revenue</div>
                            <div class="metric-value text-info">
                                {{ store_currency_symbol() }}{{ number_format($revenue_summary['shipping_revenue'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="metric-item">
                            <div class="metric-label">Discounts Given</div>
                            <div class="metric-value text-danger">
                                -{{ store_currency_symbol() }}{{ number_format($revenue_summary['discounts_given'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="metric-item">
                            <div class="metric-label">Average Order Value</div>
                            <div class="metric-value text-brand">
                                {{ store_currency_symbol() }}{{ number_format($sales_summary['avg_order_value'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam text-brand"></i> Inventory Status
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="text-muted small">Total Items</div>
                        <h4 class="mb-0">{{ number_format($inventory_summary['total_products']) }}</h4>
                        <small class="text-muted">Products + Variants</small>
                    </div>
                    <i class="bi bi-boxes fs-2 text-muted"></i>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">In Stock</span>
                        <span class="badge bg-success">{{ number_format($inventory_summary['products_in_stock']) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $inventory_summary['total_products'] > 0 ? round(($inventory_summary['products_in_stock'] / $inventory_summary['total_products']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Low Stock</span>
                        <span class="badge bg-warning">{{ number_format($inventory_summary['products_low_stock']) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $inventory_summary['total_products'] > 0 ? round(($inventory_summary['products_low_stock'] / $inventory_summary['total_products']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Out of Stock</span>
                        <span class="badge bg-danger">{{ number_format($inventory_summary['products_out_of_stock']) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" style="width: {{ $inventory_summary['total_products'] > 0 ? round(($inventory_summary['products_out_of_stock'] / $inventory_summary['total_products']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>

                <a href="{{ route('admin.reports.inventory.index') }}" class="btn btn-outline-brand btn-sm w-100 mt-3">
                    <i class="bi bi-bar-chart-line"></i> View Inventory Reports
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links to Reports -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">
            <i class="bi bi-lightning-charge text-brand"></i> Quick Access to Reports
        </h4>
    </div>

    <!-- Sales Reports -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.sales.daily') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-calendar-day"></i>
            </div>
            <h6>Daily Sales</h6>
            <small class="text-muted">Today's performance</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.sales.weekly') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-calendar-week"></i>
            </div>
            <h6>Weekly Sales</h6>
            <small class="text-muted">This week's data</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.sales.monthly') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-calendar-month"></i>
            </div>
            <h6>Monthly Sales</h6>
            <small class="text-muted">Monthly breakdown</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.sales.yearly') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-calendar-range"></i>
            </div>
            <h6>Yearly Sales</h6>
            <small class="text-muted">Annual report</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.sales.custom-range') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-calendar2-range"></i>
            </div>
            <h6>Custom Range</h6>
            <small class="text-muted">Select dates</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.revenue.index') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <h6>Revenue</h6>
            <small class="text-muted">Revenue analytics</small>
        </a>
    </div>

    <!-- Product Reports -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.products.top-selling') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-star"></i>
            </div>
            <h6>Top Products</h6>
            <small class="text-muted">Best sellers</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.products.by-category') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-grid"></i>
            </div>
            <h6>By Category</h6>
            <small class="text-muted">Category analysis</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.products.performance') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <h6>Performance</h6>
            <small class="text-muted">Product metrics</small>
        </a>
    </div>

    <!-- Inventory Reports -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.inventory.stock-levels') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-boxes"></i>
            </div>
            <h6>Stock Levels</h6>
            <small class="text-muted">Current stock</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.inventory.movement') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <h6>Movements</h6>
            <small class="text-muted">Stock history</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.inventory.valuation') }}" class="quick-link-card">
            <div class="quick-link-icon">
                {{ store_currency_symbol() }}
            </div>
            <h6>Valuation</h6>
            <small class="text-muted">Stock value</small>
        </a>
    </div>

    <!-- Customer Reports -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.customers.index') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-people"></i>
            </div>
            <h6>Customers</h6>
            <small class="text-muted">Customer data</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.customers.new-vs-returning') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h6>New vs Returning</h6>
            <small class="text-muted">Customer types</small>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.reports.customers.lifetime-value') }}" class="quick-link-card">
            <div class="quick-link-icon">
                <i class="bi bi-gem"></i>
            </div>
            <h6>Lifetime Value</h6>
            <small class="text-muted">Top customers</small>
        </a>
    </div>
</div>

<!-- Additional Information -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center border-end">
                        <i class="bi bi-info-circle fs-1 text-primary mb-2"></i>
                        <h6>Need Help?</h6>
                        <p class="text-muted small mb-0">
                            All reports can be exported to PDF, Excel, or CSV format.
                        </p>
                    </div>
                    <div class="col-md-4 text-center border-end">
                        <i class="bi bi-clock-history fs-1 text-success mb-2"></i>
                        <h6>Real-Time Data</h6>
                        <p class="text-muted small mb-0">
                            All metrics are calculated in real-time from your live data.
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-filter fs-1 text-warning mb-2"></i>
                        <h6>Advanced Filters</h6>
                        <p class="text-muted small mb-0">
                            Use filters to drill down into specific categories and time periods.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-brand text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-range"></i> Select Date Range
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.reports.index') }}" id="dateRangeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-event"></i> Start Date
                        </label>
                        <input type="date"
                               name="start_date"
                               id="start_date_input"
                               class="form-control"
                               value="{{ request('start_date', $date_range['start']->format('Y-m-d')) }}"
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-event"></i> End Date
                        </label>
                        <input type="date"
                               name="end_date"
                               id="end_date_input"
                               class="form-control"
                               value="{{ request('end_date', $date_range['end']->format('Y-m-d')) }}"
                               required>
                        <small class="text-muted">End date must be after start date</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lightning-charge"></i> Quick Select
                        </label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('today')">
                                <i class="bi bi-calendar-day"></i> Today
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('yesterday')">
                                <i class="bi bi-calendar-minus"></i> Yesterday
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('last7days')">
                                <i class="bi bi-calendar-week"></i> Last 7 Days
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('week')">
                                <i class="bi bi-calendar-week"></i> This Week
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('last30days')">
                                <i class="bi bi-calendar-range"></i> Last 30 Days
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('month')">
                                <i class="bi bi-calendar-month"></i> This Month
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('lastmonth')">
                                <i class="bi bi-calendar2-minus"></i> Last Month
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="setQuickRange('year')">
                                <i class="bi bi-calendar-range"></i> This Year
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-check-circle"></i> Apply Date Range
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    /**
     * ✅ IMPROVED: Set quick date ranges with better logic
     */
    function setQuickRange(range) {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time to midnight

        let startDate, endDate;

        switch(range) {
            case 'today':
                startDate = new Date(today);
                endDate = new Date(today);
                break;

            case 'yesterday':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 1);
                endDate = new Date(startDate);
                break;

            case 'last7days':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 6);
                endDate = new Date(today);
                break;

            case 'week':
                // Start of this week (Sunday)
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay());
                endDate = new Date(today);
                break;

            case 'last30days':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 29);
                endDate = new Date(today);
                break;

            case 'month':
                // Start of this month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today);
                break;

            case 'lastmonth':
                // Start of last month
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                // End of last month
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;

            case 'year':
                // Start of this year
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today);
                break;
        }

        // Update the form inputs
        document.getElementById('start_date_input').value = formatDate(startDate);
        document.getElementById('end_date_input').value = formatDate(endDate);
    }

    /**
     * ✅ Format date to YYYY-MM-DD
     */
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * ✅ Validate date range on form submit
     */
    document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date_input').value);
        const endDate = new Date(document.getElementById('end_date_input').value);

        if (endDate < startDate) {
            e.preventDefault();
            alert('End date must be after start date');
            return false;
        }

        // Check if range is too large (optional)
        const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
        if (daysDiff > 365) {
            if (!confirm('You selected a range of ' + daysDiff + ' days. This might take a while to load. Continue?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Print functionality
    window.onbeforeprint = function() {
        document.querySelectorAll('.btn, .quick-link-card').forEach(el => {
            el.style.display = 'none';
        });
    };

    window.onafterprint = function() {
        document.querySelectorAll('.btn, .quick-link-card').forEach(el => {
            el.style.display = '';
        });
    };
</script>
@endpush
