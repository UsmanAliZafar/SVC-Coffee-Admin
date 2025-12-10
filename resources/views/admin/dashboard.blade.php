{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Dashboard - Coffee Admin')

@push('styles')
<style>
    .btn-outline-brand {
        color: var(--brand-primary);
        border-color: var(--brand-primary);
    }

    .btn-outline-brand:hover {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
        color: white;
    }

    .icon {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card {
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .activity-item {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s;
    }

    .activity-item:hover {
        background-color: #f8f9fa;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .badge-status {
        padding: 5px 10px;
        font-size: 0.75rem;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .percentage-badge {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .percentage-badge.positive {
        background-color: #d4edda;
        color: #155724;
    }

    .percentage-badge.negative {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-speedometer2 text-brand"></i> Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary {{ $period === 'week' ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i> This Week
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary {{ $period === 'month' ? 'active' : '' }}">
                <i class="bi bi-calendar-month"></i> This Month
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary {{ $period === 'year' ? 'active' : '' }}">
                <i class="bi bi-calendar-range"></i> This Year
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<!-- Key Stats Cards -->
<div class="row mb-4">
    <!-- Total Products -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Products</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['total_products']) }}</span>
                        <p class="mt-2 mb-0 text-muted text-sm">
                            <span class="text-success me-2">
                                <i class="bi bi-check-circle"></i> {{ number_format($stats['active_products']) }}
                            </span>
                            <span class="text-nowrap">Active</span>
                        </p>
                        @if($stats['period_new_products'] > 0)
                        <small class="text-muted">
                            <i class="bi bi-plus-circle text-brand"></i>
                            {{ number_format($stats['period_new_products']) }} new this {{ $period }}
                        </small>
                        @endif
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-brand text-white rounded-circle shadow">
                            <i class="bi bi-box fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">
                            {{ ucfirst($period) }} Orders
                        </h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['period_orders']) }}</span>
                        <p class="mt-2 mb-0 text-muted text-sm">
                            @if($orderStats['orders_require_action'] > 0)
                            <span class="text-warning me-2">
                                <i class="bi bi-exclamation-triangle"></i> {{ number_format($orderStats['orders_require_action']) }}
                            </span>
                            <span class="text-nowrap">Need Action</span>
                            @else
                            <span class="text-success">
                                <i class="bi bi-check-circle"></i> All Clear
                            </span>
                            @endif
                        </p>
                        <small class="text-muted">Total: {{ number_format($stats['total_orders']) }}</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Customers</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['total_customers']) }}</span>
                        <p class="mt-2 mb-0 text-muted text-sm">
                            <span class="text-info">
                                <i class="bi bi-person-plus"></i> Registered
                            </span>
                        </p>
                        @if($stats['period_new_customers'] > 0)
                        <small class="text-muted">
                            <i class="bi bi-plus-circle text-brand"></i>
                            {{ number_format($stats['period_new_customers']) }} new this {{ $period }}
                        </small>
                        @endif
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Revenue -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">
                            {{ $revenueStats['period_label'] }} Revenue
                        </h5>
                        <span class="h2 font-weight-bold mb-0">
                            {{ store_currency_symbol() }} {{ number_format($revenueStats['period_revenue'], 2) }}
                        </span>
                        <p class="mt-2 mb-0 text-muted text-sm">
                            @if($revenueStats['revenue_change_percentage'] > 0)
                            <span class="percentage-badge positive">
                                <i class="bi bi-arrow-up"></i> {{ number_format($revenueStats['revenue_change_percentage'], 1) }}%
                            </span>
                            <small class="text-muted ms-1">vs last {{ $period }}</small>
                            @elseif($revenueStats['revenue_change_percentage'] < 0)
                            <span class="percentage-badge negative">
                                <i class="bi bi-arrow-down"></i> {{ number_format(abs($revenueStats['revenue_change_percentage']), 1) }}%
                            </span>
                            <small class="text-muted ms-1">vs last {{ $period }}</small>
                            @else
                            <span class="text-muted">No change from last {{ $period }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row mb-4">
    <!-- Inventory Overview -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-muted mb-3">
                    <i class="bi bi-boxes text-brand"></i> Inventory Overview
                </h6>
                <div class="mb-2">
                    <small class="text-muted">Total Stock Units</small>
                    <div class="h4 mb-0">{{ number_format($inventoryStats['total_stock_units']) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Available</small>
                    <div class="text-success fw-bold">{{ number_format($inventoryStats['available_stock']) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Reserved</small>
                    <div class="text-warning fw-bold">{{ number_format($inventoryStats['reserved_stock']) }}</div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <small class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i> Low Stock
                    </small>
                    <span class="badge bg-danger">{{ $inventoryStats['low_stock_products'] }}</span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-danger">
                        <i class="bi bi-x-circle"></i> Out of Stock
                    </small>
                    <span class="badge bg-danger">{{ $inventoryStats['out_of_stock_products'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Breakdown -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-muted mb-3">
                    <i class="bi bi-list-check text-brand"></i> Order Status
                </h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-hourglass-split text-warning"></i> Pending</span>
                    <span class="badge bg-warning">{{ $orderStats['pending_orders'] }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-gear text-primary"></i> Processing</span>
                    <span class="badge bg-primary">{{ $orderStats['processing_orders'] }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-truck text-info"></i> Shipped</span>
                    <span class="badge bg-info">{{ $orderStats['shipped_orders'] }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-check-circle text-success"></i> Delivered</span>
                    <span class="badge bg-success">{{ $orderStats['delivered_orders'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted"><i class="bi bi-x-circle text-danger"></i> Cancelled</span>
                    <span class="badge bg-danger">{{ $orderStats['cancelled_orders'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-muted mb-3">
                    <i class="bi bi-graph-up text-brand"></i> Revenue Breakdown
                </h6>
                <div class="mb-2">
                    <small class="text-muted">Today</small>
                    <div class="h5 mb-0 text-success">{{ store_currency_symbol() }} {{ number_format($revenueStats['today_revenue'], 2) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">This Week</small>
                    <div class="h5 mb-0">{{ store_currency_symbol() }} {{ number_format($revenueStats['week_revenue'], 2) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">This Month</small>
                    <div class="h5 mb-0">{{ store_currency_symbol() }} {{ number_format($revenueStats['month_revenue'], 2) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">This Year</small>
                    <div class="h5 mb-0">{{ store_currency_symbol() }} {{ number_format($revenueStats['year_revenue'], 2) }}</div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Avg Order Value</small>
                    <span class="fw-bold">{{ store_currency_symbol() }} {{ number_format($revenueStats['average_order_value'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">Period</small>
                    <span class="badge bg-brand">{{ $revenueStats['period_label'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Warehouses & Alerts -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase text-muted mb-3">
                    <i class="bi bi-building text-brand"></i> System Status
                </h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-building"></i> Active Warehouses</span>
                    <span class="badge bg-brand">{{ $stats['total_warehouses'] }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-person-gear"></i> Active Admins</span>
                    <span class="badge bg-brand">{{ $stats['active_admins'] }}</span>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted">Stock Value</small>
                    <div class="h5 mb-0 text-brand">{{ store_currency_symbol() }} {{ number_format($inventoryStats['total_stock_value'], 2) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Pending Payments</small>
                    <div class="h6 mb-0 text-warning">{{ store_currency_symbol() }} {{ number_format($revenueStats['pending_payments'], 2) }}</div>
                </div>
                <div>
                    <small class="text-muted">Total Refunded</small>
                    <div class="h6 mb-0 text-danger">{{ store_currency_symbol() }} {{ number_format($revenueStats['total_refunded'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-brand text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-circle"></i> Welcome back, {{ $user->name }}!
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="card-text">
                            You have <strong>{{ $user->roles->count() }}</strong> role(s) assigned:
                            <span class="text-brand fw-semibold">{{ $user->roles->pluck('display_name')->join(', ') }}</span>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                Last login: {{ $user->last_login_at ? $user->last_login_at->format('F j, Y g:i A') : 'Never' }}
                                @if($user->last_login_ip)
                                    from {{ $user->last_login_ip }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="#" class="btn btn-outline-brand">
                            <i class="bi bi-person"></i> View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Sales Chart -->
    <div class="col-xl-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up text-brand"></i> Sales Overview (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="col-xl-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart text-brand"></i> Category Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities Row -->
<div class="row mb-4">
    <!-- Recent Orders -->
    <div class="col-xl-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-receipt text-brand"></i> Recent Orders
                </h5>
                @if(auth('admin')->user()->hasPermission('orders.read'))
                <a href="#" class="btn btn-sm btn-outline-brand">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($recentOrders->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentOrders as $order)
                    <div class="list-group-item activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none">Order #{{ $order->order_number }}</a>
                                </h6>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> {{ $order->customer ? $order->customer->name : $order->guest_name }}
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock"></i> {{ $order->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold mb-1">{{ store_currency_symbol() }} {{ number_format($order->total_amount, 2) }}</div>
                                <span class="badge {{ $order->getStatusBadgeClass() }}">
                                    {{ $order->getStatusLabel() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-2">No recent orders</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-xl-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle text-danger"></i> Low Stock Products
                </h5>
                @if(auth('admin')->user()->hasPermission('inventory.read'))
                <a href="#" class="btn btn-sm btn-outline-danger">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($lowStockProducts->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($lowStockProducts as $product)
                    <div class="list-group-item activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <small class="text-muted">
                                    SKU: {{ $product->sku }}
                                    <span class="mx-2">•</span>
                                    {{ $product->category ? $product->category->name : 'Uncategorized' }}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-danger mb-1">
                                    {{ $product->stock_quantity }} units
                                </div>
                                <div>
                                    <small class="text-muted">Threshold: {{ $product->low_stock_threshold }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-4 text-success"></i>
                    <p class="text-muted mt-2">All products have sufficient stock</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stock Alerts & Inventory Movements -->
<div class="row mb-4">
    <!-- Stock Alerts -->
    <div class="col-xl-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bell text-warning"></i> Stock Alerts
                </h5>
                @if(auth('admin')->user()->hasPermission('inventory.read'))
                <a href="#" class="btn btn-sm btn-outline-warning">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($stockAlerts->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($stockAlerts as $alert)
                    <div class="list-group-item activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ $alert->product->name }}
                                </h6>
                                <small class="text-muted">
                                    <i class="bi bi-building"></i> {{ $alert->warehouse->name }}
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock"></i> {{ $alert->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $alert->alert_type === 'out_of_stock' ? 'bg-danger' : 'bg-warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">Qty: {{ $alert->current_quantity }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-4 text-success"></i>
                    <p class="text-muted mt-2">No active stock alerts</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Inventory Movements -->
    <div class="col-xl-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-arrow-left-right text-info"></i> Recent Inventory Movements
                </h5>
                @if(auth('admin')->user()->hasPermission('inventory.read'))
                <a href="#" class="btn btn-sm btn-outline-info">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($recentInventoryMovements->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentInventoryMovements as $movement)
                    <div class="list-group-item activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $movement->product->name ?? 'Deleted Product' }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-building"></i> {{ $movement->warehouse->name }}
                                    <span class="mx-2">•</span>
                                    {{ $movement->getTypeLabel() }}
                                </small>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $movement->created_at->format('M d, Y g:i A') }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $movement->quantity >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">{{ $movement->new_quantity }} total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-2">No recent inventory movements</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-star text-warning"></i> Top Selling Products (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                @if($topProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Units Sold</th>
                                <th>Price</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $index => $product)
                            <tr>
                                <td>
                                    <span class="badge bg-brand">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-success">{{ number_format($product->total_sold) }}</span>
                                </td>
                                <td>{{ store_currency_symbol() }} {{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->stock_quantity <= $product->low_stock_threshold)
                                    <span class="badge bg-danger">{{ $product->stock_quantity }}</span>
                                    @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-2">No sales data available</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning text-brand"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(auth('admin')->user()->hasPermission('products.create'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-plus-circle fs-1 mb-2"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('orders.read'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-receipt fs-1 mb-2"></i>
                            <span>View Orders</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('inventory.read'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-boxes fs-1 mb-2"></i>
                            <span>Check Inventory</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('reports.read'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.orders.reports') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-graph-up fs-1 mb-2"></i>
                            <span>View Reports</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('customers.read'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-people fs-1 mb-2"></i>
                            <span>View Customers</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('warehouses.read'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.warehouses') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-building fs-1 mb-2"></i>
                            <span>Manage Warehouses</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('inventory.update'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-arrow-left-right fs-1 mb-2"></i>
                            <span>Stock Adjustment</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('settings.read'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-gear fs-1 mb-2"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ============================================================
    // DATE FILTER FUNCTIONALITY
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.btn-group .btn-outline-secondary');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                // Determine the filter period
                let period = '';
                if (this.textContent.includes('Week')) {
                    period = 'week';
                } else if (this.textContent.includes('Month')) {
                    period = 'month';
                } else if (this.textContent.includes('Year')) {
                    period = 'year';
                }

                // Reload dashboard with filter
                if (period) {
                    window.location.href = '{{ route("admin.dashboard") }}?period=' + period;
                }
            });
        });
    });
    // Sales Chart
    const currencySymbol = '{{ store_currency_symbol() }}';
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($salesChartData['labels']),
            datasets: [{
                label: 'Sales Amount (' + currencySymbol + ')',
                data: @json($salesChartData['sales_amounts']),
                borderColor: '#5B914C',
                backgroundColor: 'rgba(91, 145, 76, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Order Count',
                data: @json($salesChartData['order_counts']),
                borderColor: '#FFC107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true,
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
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales Amount ({{ store_currency_symbol())'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Order Count'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($categoryBreakdown['labels']),
            datasets: [{
                data: @json($categoryBreakdown['counts']),
                backgroundColor: [
                    '#5B914C',
                    '#FFC107',
                    '#17A2B8',
                    '#DC3545',
                    '#6C757D',
                    '#28A745'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
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
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Auto-refresh dashboard stats every 5 minutes
    setInterval(function() {
        console.log('Auto-refreshing dashboard stats...');
        // You can implement AJAX refresh here if needed
    }, 300000);
</script>
@endpush
