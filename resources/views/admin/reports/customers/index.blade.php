{{-- resources/views/admin/reports/customers/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Customer Analytics - Coffee Admin')

@push('styles')
<style>
    .customers-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .customers-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .customer-metric {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .customer-metric:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
    }

    .customer-metric .icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 10px;
    }

    .customer-metric .value {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .customer-metric .label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .customer-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #5B914C;
        margin-bottom: 20px;
    }

    .customer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.15);
    }

    .customer-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .customer-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .customer-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        flex-shrink: 0;
    }

    .customer-details {
        flex-grow: 1;
    }

    .customer-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 3px;
    }

    .customer-email {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .customer-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .badge-vip {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #856404;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-new {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-inactive {
        background: #dc3545;
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .customer-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .stat-box {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .stat-box .value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #5B914C;
        display: block;
    }

    .stat-box .label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-top: 3px;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .chart-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .chart-container {
        height: 350px;
        position: relative;
    }

    .segment-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .segment-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .segment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .segment-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
    }

    .segment-count {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .segment-bar {
        height: 25px;
        background: #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .segment-fill {
        height: 100%;
        background: linear-gradient(90deg, #5B914C 0%, #6BA055 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        transition: width 1s ease;
    }

    .customers-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .customers-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .customers-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
    }

    .customers-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .customers-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .customers-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .ltv-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .ltv-high {
        background: #d4edda;
        color: #155724;
    }

    .ltv-medium {
        background: #d1ecf1;
        color: #0c5460;
    }

    .ltv-low {
        background: #fff3cd;
        color: #856404;
    }

    @media print {
        .no-print { display: none !important; }
        .customer-card { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .customer-stats { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="customers-analytics-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-people text-brand"></i> Customer Analytics
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Customers</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group no-print">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-brand dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Customer Metrics Header -->
    <div class="customers-header">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="customer-metric">
                    <div class="icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="value">{{ number_format($total_customers) }}</div>
                    <div class="label">Total Customers</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="customer-metric">
                    <div class="icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="value">{{ number_format($new_customers) }}</div>
                    <div class="label">New This Month</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="customer-metric">
                    <div class="icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($average_ltv, 2) }}</div>
                    <div class="label">Avg Lifetime Value</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="customer-metric">
                    <div class="icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="value">{{ number_format($repeat_rate, 1) }}%</div>
                    <div class="label">Repeat Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.customers.index') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel"></i> Customer Segment
                    </label>
                    <select class="form-select" name="segment">
                        <option value="all" {{ request('segment') == 'all' ? 'selected' : '' }}>All Customers</option>
                        <option value="vip" {{ request('segment') == 'vip' ? 'selected' : '' }}>VIP Customers</option>
                        <option value="new" {{ request('segment') == 'new' ? 'selected' : '' }}>New Customers</option>
                        <option value="repeat" {{ request('segment') == 'repeat' ? 'selected' : '' }}>Repeat Customers</option>
                        <option value="inactive" {{ request('segment') == 'inactive' ? 'selected' : '' }}>Inactive (90+ days)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Name or email..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-sort-down"></i> Sort By
                    </label>
                    <select class="form-select" name="sort">
                        <option value="ltv_desc" {{ request('sort') == 'ltv_desc' ? 'selected' : '' }}>LTV: High to Low</option>
                        <option value="orders_desc" {{ request('sort') == 'orders_desc' ? 'selected' : '' }}>Orders: Most First</option>
                        <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Recently Joined</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Customer Segmentation -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="bi bi-pie-chart text-brand"></i> Customer Segmentation</h5>
        </div>
        @foreach($segments as $segment)
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="segment-card">
                <div class="segment-header">
                    <div class="segment-name">{{ $segment['name'] }}</div>
                    <div class="segment-count">{{ number_format($segment['count']) }}</div>
                </div>
                <div class="segment-bar">
                    <div class="segment-fill" style="width: {{ $total_customers > 0 ? ($segment['count'] / $total_customers) * 100 : 0 }}%">
                        {{ $total_customers > 0 ? number_format(($segment['count'] / $total_customers) * 100, 1) : 0 }}%
                    </div>
                </div>
                <small class="text-muted">{{ $segment['description'] }}</small>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-graph-up text-brand"></i> Customer Growth Trend
                </h5>
                <div class="chart-container">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-pie-chart text-brand"></i> Lifetime Value Distribution
                </h5>
                <div class="chart-container">
                    <canvas id="ltvDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers Cards -->
    @if($customers->count() > 0)
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="bi bi-star text-warning"></i> Top Customers</h5>
        </div>
        @foreach($customers->take(6) as $customer)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="customer-card">
                <div class="customer-header-row">
                    <div class="customer-info">
                        <div class="customer-avatar">
                            {{ strtoupper(substr($customer->first_name ?? 'C', 0, 1)) }}{{ strtoupper(substr($customer->last_name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="customer-details">
                            <div class="customer-name">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                            <div class="customer-email">{{ $customer->email }}</div>
                            <div class="customer-badges">
                                @if($customer->total_spent >= 1000)
                                <span class="badge-vip">
                                    <i class="bi bi-gem"></i> VIP
                                </span>
                                @endif
                                @if($customer->created_at->isAfter(now()->subDays(30)))
                                <span class="badge-new">
                                    <i class="bi bi-star"></i> New
                                </span>
                                @endif
                                @if($customer->last_order_date && $customer->last_order_date->isBefore(now()->subDays(90)))
                                <span class="badge-inactive">
                                    <i class="bi bi-clock-history"></i> Inactive
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="customer-stats">
                    <div class="stat-box">
                        <span class="value">{{ number_format($customer->total_orders) }}</span>
                        <span class="label">Orders</span>
                    </div>
                    <div class="stat-box">
                        <span class="value">{{ store_currency_symbol() }}{{ number_format($customer->total_spent, 2) }}</span>
                        <span class="label">Total Spent</span>
                    </div>
                    <div class="stat-box">
                        <span class="value">{{ store_currency_symbol() }}{{ $customer->total_orders > 0 ? number_format($customer->total_spent / $customer->total_orders, 2) : '0.00' }}</span>
                        <span class="label">Avg Order</span>
                    </div>
                    <div class="stat-box">
                        <span class="value">{{ $customer->last_order_at ? $customer->last_order_at->diffForHumans() : 'Never' }}</span>
                        <span class="label">Last Order</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Detailed Customer Table -->
    @if($customers->count() > 0)
    <div class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-brand"></i> Detailed Customer List
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table customers-table mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Total Spent</th>
                                <th class="text-end">Avg Order</th>
                                <th class="text-center">LTV Segment</th>
                                <th class="text-center">Last Order</th>
                                <th class="text-center no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                            @php
                                if ($customer->total_spent >= 1000) {
                                    $ltv_class = 'ltv-high';
                                    $ltv_label = 'High Value';
                                } elseif ($customer->total_spent >= 500) {
                                    $ltv_class = 'ltv-medium';
                                    $ltv_label = 'Medium Value';
                                } else {
                                    $ltv_class = 'ltv-low';
                                    $ltv_label = 'Low Value';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar" style="width: 40px; height: 40px; font-size: 1rem; margin-right: 10px;">
                                            {{ strtoupper(substr($customer->first_name ?? 'C', 0, 1)) }}{{ strtoupper(substr($customer->last_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ number_format($customer->total_orders) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($customer->total_spent, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    {{ $customer->total_orders > 0 ? store_currency_symbol() . number_format($customer->total_spent / $customer->total_orders, 2) : 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <span class="ltv-badge {{ $ltv_class }}">
                                        {{ $ltv_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        {{ $customer->last_order_at ? $customer->last_order_at->format('M d, Y') : 'Never' }}
                                    </small>
                                </td>
                                <td class="text-center no-print">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" target="_blank" href="{{ route('admin.customers.show', $customer->id) }}"><i class="bi bi-eye"></i> View Profile</a></li>
                                            <li><a class="dropdown-item" target="_blank" href="{{ route('admin.customers.show', $customer->id) }}"><i class="bi bi-cart"></i> View Orders</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-inbox fs-1 mb-3"></i>
        <h5>No Customers Found</h5>
        <p class="mb-0">No customers match your current filters.</p>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up-arrow text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Customer Insights</h6>
                <p class="text-muted small mb-0">
                    Analyze customer behavior, spending patterns, and lifetime value.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-people text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Segmentation</h6>
                <p class="text-muted small mb-0">
                    Segment customers by value, activity, and purchasing behavior.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-star text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Retention</h6>
                <p class="text-muted small mb-0">
                    Track customer retention and identify opportunities for engagement.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Customer Growth Chart
    const growthCtx = document.getElementById('customerGrowthChart');
    if (growthCtx) {
        const growthData = @json($customer_growth ?? []);

        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: growthData.map(d => d.month),
                datasets: [{
                    label: 'New Customers',
                    data: growthData.map(d => d.count),
                    borderColor: '#5B914C',
                    backgroundColor: 'rgba(91, 145, 76, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // LTV Distribution Chart
    const ltvCtx = document.getElementById('ltvDistributionChart');
    if (ltvCtx) {
        const ltvData = @json($ltv_distribution ?? []);

        new Chart(ltvCtx, {
            type: 'doughnut',
            data: {
                labels: ltvData.map(d => d.segment),
                datasets: [{
                    data: ltvData.map(d => d.count),
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                }
            }
        });
    }

    // Animate segment bars
    setTimeout(() => {
        document.querySelectorAll('.segment-fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    }, 200);
});
</script>
@endpush
