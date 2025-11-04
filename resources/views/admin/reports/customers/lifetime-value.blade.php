{{-- resources/views/admin/reports/customers/lifetime-value.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Customer Lifetime Value - Coffee Admin')

@push('styles')
<style>
    .ltv-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .ltv-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .ltv-metric {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .ltv-metric:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-5px);
    }

    .ltv-metric .icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 15px;
    }

    .ltv-metric .value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 15px 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .ltv-metric .label {
        font-size: 0.875rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .ltv-segment-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        transition: all 0.3s ease;
        border-left: 4px solid;
    }

    .ltv-segment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .ltv-segment-card.platinum {
        border-left-color: #e5e5e5;
        background: linear-gradient(90deg, #f8f9fa 0%, white 10%);
    }

    .ltv-segment-card.gold {
        border-left-color: #ffd700;
        background: linear-gradient(90deg, #fffef5 0%, white 10%);
    }

    .ltv-segment-card.silver {
        border-left-color: #c0c0c0;
        background: linear-gradient(90deg, #f8f8f8 0%, white 10%);
    }

    .ltv-segment-card.bronze {
        border-left-color: #cd7f32;
        background: linear-gradient(90deg, #fff8f0 0%, white 10%);
    }

    .segment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .segment-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .segment-badge.platinum {
        background: linear-gradient(135deg, #e5e5e5 0%, #b5b5b5 100%);
        color: #333;
    }

    .segment-badge.gold {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #856404;
    }

    .segment-badge.silver {
        background: linear-gradient(135deg, #c0c0c0 0%, #a8a8a8 100%);
        color: #333;
    }

    .segment-badge.bronze {
        background: linear-gradient(135deg, #cd7f32 0%, #b8692c 100%);
        color: white;
    }

    .segment-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
    }

    .segment-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .stat-item {
        text-align: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .stat-item .value {
        font-size: 1.3rem;
        font-weight: bold;
        color: #212529;
        display: block;
    }

    .stat-item .label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-top: 3px;
    }

    .customer-ltv-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 15px;
        transition: all 0.3s ease;
        border-left: 4px solid #5B914C;
    }

    .customer-ltv-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.15);
    }

    .customer-row {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .customer-rank {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: bold;
        flex-shrink: 0;
    }

    .customer-rank.top1 {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #856404;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
    }

    .customer-rank.top2 {
        background: linear-gradient(135deg, #c0c0c0 0%, #e0e0e0 100%);
        color: #333;
        box-shadow: 0 4px 12px rgba(192, 192, 192, 0.4);
    }

    .customer-rank.top3 {
        background: linear-gradient(135deg, #cd7f32 0%, #e8a55a 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(205, 127, 50, 0.4);
    }

    .customer-rank.other {
        background: #f8f9fa;
        color: #6c757d;
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

    .customer-info {
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

    .customer-ltv-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        text-align: right;
    }

    .customer-ltv-details {
        text-align: right;
        font-size: 0.875rem;
        color: #6c757d;
    }

    .chart-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .chart-container {
        height: 400px;
        position: relative;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .ltv-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .ltv-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .ltv-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
    }

    .ltv-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .ltv-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .ltv-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .ltv-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .ltv-platinum {
        background: #e5e5e5;
        color: #333;
    }

    .ltv-gold {
        background: #ffd700;
        color: #856404;
    }

    .ltv-silver {
        background: #c0c0c0;
        color: #333;
    }

    .ltv-bronze {
        background: #cd7f32;
        color: white;
    }

    .prediction-box {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border-left: 4px solid #4caf50;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .prediction-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2e7d32;
    }

    @media print {
        .no-print { display: none !important; }
    }

    @media (max-width: 768px) {
        .segment-stats { grid-template-columns: repeat(2, 1fr); }
        .customer-row { flex-wrap: wrap; }
    }
</style>
@endpush

@section('content')
<div class="customer-ltv-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-gem text-brand"></i> Customer Lifetime Value
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">Lifetime Value</li>
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

    <!-- LTV Header -->
    <div class="ltv-header">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="ltv-metric">
                    <div class="icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($average_ltv, 2) }}</div>
                    <div class="label">Average LTV</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="ltv-metric">
                    <div class="icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($highest_ltv, 2) }}</div>
                    <div class="label">Highest LTV</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="ltv-metric">
                    <div class="icon">
                        <i class="bi bi-piggy-bank"></i>
                    </div>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($total_ltv, 2) }}</div>
                    <div class="label">Total Customer Value</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="ltv-metric">
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="value">{{ number_format($total_customers) }}</div>
                    <div class="label">Total Customers</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.customers.lifetime-value') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel"></i> LTV Segment
                    </label>
                    <select class="form-select" name="segment">
                        <option value="all" {{ request('segment') == 'all' ? 'selected' : '' }}>All Segments</option>
                        <option value="platinum" {{ request('segment') == 'platinum' ? 'selected' : '' }}>Platinum ($5000+)</option>
                        <option value="gold" {{ request('segment') == 'gold' ? 'selected' : '' }}>Gold ($2000-$4999)</option>
                        <option value="silver" {{ request('segment') == 'silver' ? 'selected' : '' }}>Silver ($500-$1999)</option>
                        <option value="bronze" {{ request('segment') == 'bronze' ? 'selected' : '' }}>Bronze (<$500)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Customer name..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-sort-down"></i> Sort By
                    </label>
                    <select class="form-select" name="sort">
                        <option value="ltv_desc" {{ request('sort') == 'ltv_desc' ? 'selected' : '' }}>LTV: High to Low</option>
                        <option value="ltv_asc" {{ request('sort') == 'ltv_asc' ? 'selected' : '' }}>LTV: Low to High</option>
                        <option value="orders_desc" {{ request('sort') == 'orders_desc' ? 'selected' : '' }}>Orders: Most First</option>
                        <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Recently Joined</option>
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

    <!-- LTV Prediction -->
    <div class="prediction-box">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-2">
                    <i class="bi bi-graph-up-arrow"></i> Projected Customer Value
                </h5>
                <p class="mb-0">
                    Based on current trends, the projected average customer lifetime value
                    for next year is estimated at:
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="prediction-value">
                    {{ store_currency_symbol() }}{{ number_format($predicted_ltv, 2) }}
                </div>
                <small class="text-muted">
                    <i class="bi bi-arrow-up"></i> {{ number_format($ltv_growth_rate, 1) }}% increase
                </small>
            </div>
        </div>
    </div>

    <!-- LTV Segments -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="bi bi-award text-brand"></i> Customer Value Segments</h5>
        </div>

        @foreach($segments as $segment)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="ltv-segment-card {{ $segment['class'] }}">
                <div class="segment-header">
                    <div class="segment-badge {{ $segment['class'] }}">
                        <i class="bi {{ $segment['icon'] }}"></i>
                        {{ $segment['name'] }}
                    </div>
                </div>
                <div class="segment-value">{{ number_format($segment['count']) }}</div>
                <p class="text-muted small mb-3">{{ $segment['description'] }}</p>

                <div class="segment-stats">
                    <div class="stat-item">
                        <span class="value">{{ store_currency_symbol() }}{{ number_format($segment['total_value'], 0) }}</span>
                        <span class="label">Total Value</span>
                    </div>
                    <div class="stat-item">
                        <span class="value">{{ store_currency_symbol() }}{{ number_format($segment['avg_ltv'], 2) }}</span>
                        <span class="label">Avg LTV</span>
                    </div>
                    <div class="stat-item">
                        <span class="value">{{ number_format($segment['avg_orders'], 1) }}</span>
                        <span class="label">Avg Orders</span>
                    </div>
                    <div class="stat-item">
                        <span class="value">{{ $total_customers > 0 ? number_format(($segment['count'] / $total_customers) * 100, 1) : 0 }}%</span>
                        <span class="label">Of Total</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-pie-chart text-brand"></i> LTV Distribution
                </h5>
                <div class="chart-container">
                    <canvas id="ltvDistributionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-bar-chart text-brand"></i> Average LTV by Segment
                </h5>
                <div class="chart-container">
                    <canvas id="avgLtvChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Customers by LTV -->
    @if($top_customers->count() > 0)
    <div class="mb-4">
        <h5 class="mb-3">
            <i class="bi bi-trophy-fill text-warning"></i> Top 10 Customers by Lifetime Value
        </h5>
        @foreach($top_customers->take(10) as $index => $customer)
        <div class="customer-ltv-card">
            <div class="customer-row">
                <div class="customer-rank {{ $index < 3 ? 'top' . ($index + 1) : 'other' }}">
                    {{ $index + 1 }}
                </div>
                <div class="customer-avatar">
                    {{ strtoupper(substr($customer->first_name ?? 'C', 0, 1)) }}{{ strtoupper(substr($customer->last_name ?? 'U', 0, 1)) }}
                </div>
                <div class="customer-info">
                    <div class="customer-name">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                    <div class="customer-email">{{ $customer->email }}</div>
                    <div class="mt-2">
                        @php
                            if ($customer->total_spent >= 5000) {
                                $badge = 'ltv-platinum';
                                $label = 'Platinum';
                            } elseif ($customer->total_spent >= 2000) {
                                $badge = 'ltv-gold';
                                $label = 'Gold';
                            } elseif ($customer->total_spent >= 500) {
                                $badge = 'ltv-silver';
                                $label = 'Silver';
                            } else {
                                $badge = 'ltv-bronze';
                                $label = 'Bronze';
                            }
                        @endphp
                        <span class="ltv-badge {{ $badge }}">
                            <i class="bi bi-award"></i> {{ $label }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="customer-ltv-value">
                        {{ store_currency_symbol() }}{{ number_format($customer->total_spent, 2) }}
                    </div>
                    <div class="customer-ltv-details">
                        {{ number_format($customer->total_orders) }} orders |
                        Avg: {{ store_currency_symbol() }}{{ $customer->total_orders > 0 ? number_format($customer->total_spent / $customer->total_orders, 2) : '0.00' }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Detailed LTV Table -->
    @if($customers->count() > 0)
    <div class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-brand"></i> Detailed Customer LTV Analysis
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table ltv-table mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th class="text-center">Segment</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Lifetime Value</th>
                                <th class="text-end">Avg Order</th>
                                <th class="text-center">First Order</th>
                                <th class="text-center">Last Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                            @php
                                if ($customer->total_spent >= 5000) {
                                    $badge = 'ltv-platinum';
                                    $label = 'Platinum';
                                } elseif ($customer->total_spent >= 2000) {
                                    $badge = 'ltv-gold';
                                    $label = 'Gold';
                                } elseif ($customer->total_spent >= 500) {
                                    $badge = 'ltv-silver';
                                    $label = 'Silver';
                                } else {
                                    $badge = 'ltv-bronze';
                                    $label = 'Bronze';
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
                                    <span class="ltv-badge {{ $badge }}">
                                        {{ $label }}
                                    </span>
                                </td>
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
                                    <small class="text-muted">
                                        {{ $customer->first_order_at ? $customer->first_order_at->format('M d, Y') : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        {{ $customer->last_order_at ? $customer->last_order_at->format('M d, Y') : 'Never' }}
                                    </small>
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
                <i class="bi bi-gem text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">LTV Segmentation</h6>
                <p class="text-muted small mb-0">
                    Categorize customers into value tiers to tailor marketing and retention strategies.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-graph-up-arrow text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Predictive Analytics</h6>
                <p class="text-muted small mb-0">
                    Forecast future customer value to optimize acquisition and retention budgets.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-bullseye text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Targeted Actions</h6>
                <p class="text-muted small mb-0">
                    Focus resources on high-value customers to maximize ROI and business growth.
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
    // LTV Distribution Pie Chart
    const distributionCtx = document.getElementById('ltvDistributionChart');
    if (distributionCtx) {
        const segments = @json($segments);

        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: segments.map(s => s.name),
                datasets: [{
                    data: segments.map(s => s.count),
                    backgroundColor: ['#e5e5e5', '#ffd700', '#c0c0c0', '#cd7f32'],
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
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Average LTV Bar Chart
    const avgLtvCtx = document.getElementById('avgLtvChart');
    if (avgLtvCtx) {
        const segments = @json($segments);

        new Chart(avgLtvCtx, {
            type: 'bar',
            data: {
                labels: segments.map(s => s.name),
                datasets: [{
                    label: 'Average LTV',
                    data: segments.map(s => s.avg_ltv),
                    backgroundColor: ['#e5e5e5', '#ffd700', '#c0c0c0', '#cd7f32'],
                    borderRadius: 8,
                    borderWidth: 0
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
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Avg LTV: {{ store_currency_symbol() }}' + context.parsed.y.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ store_currency_symbol() }}' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
