{{-- resources/views/admin/reports/products/by-category.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Products by Category - Coffee Admin')

@push('styles')
<style>
    .category-performance-card {
        border-radius: 15px;
        border: 2px solid #f8f9fa;
        transition: all 0.3s ease;
        overflow: hidden;
        background: white;
        position: relative;
    }

    .category-performance-card:hover {
        border-color: #5B914C;
        transform: translateY(-5px);
        box-shadow: 0 .75rem 1.5rem rgba(91, 145, 76, 0.2)!important;
    }

    .category-header-section {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 25px;
        position: relative;
        overflow: hidden;
    }

    .category-header-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.3; }
    }

    .category-icon-container {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 15px;
    }

    .category-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .category-product-count {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .category-body {
        padding: 25px;
    }

    .metric-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .metric-box {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .metric-box:hover {
        background: #e9ecef;
        transform: scale(1.05);
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
        display: block;
    }

    .metric-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }

    .performance-bar-container {
        margin-top: 20px;
    }

    .performance-bar {
        height: 30px;
        background: #e9ecef;
        border-radius: 15px;
        overflow: hidden;
        position: relative;
        margin-bottom: 10px;
    }

    .performance-fill {
        height: 100%;
        background: linear-gradient(90deg, #5B914C 0%, #6BA055 100%);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 15px;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        transition: width 1s ease-out;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .product-list-section {
        border-top: 2px solid #e9ecef;
        padding-top: 20px;
        margin-top: 20px;
    }

    .product-item {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .product-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .product-thumbnail {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        border: 2px solid #dee2e6;
    }

    .product-info {
        flex-grow: 1;
    }

    .product-name-small {
        font-weight: 600;
        color: #212529;
        margin-bottom: 3px;
    }

    .product-units {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .summary-banner {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .summary-banner::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .summary-item {
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .summary-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .summary-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .comparison-chart-card {
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

    .detailed-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .detailed-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .detailed-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
    }

    .detailed-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .detailed-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .detailed-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .category-icon-badge {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        font-weight: bold;
        color: white;
    }

    .rank-1 { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color: #856404; }
    .rank-2 { background: linear-gradient(135deg, #c0c0c0 0%, #e0e0e0 100%); color: #495057; }
    .rank-3 { background: linear-gradient(135deg, #cd7f32 0%, #daa06d 100%); }
    .rank-other { background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%); }

    .progress-ring {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }

    .expand-btn {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .expand-btn:hover {
        color: #5B914C;
        transform: scale(1.1);
    }

    @media print {
        .no-print { display: none !important; }
        .category-performance-card { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .metric-row { grid-template-columns: 1fr; }
        .summary-banner .summary-item { margin-bottom: 20px; }
    }
</style>
@endpush

@section('content')
<div class="products-by-category-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-grid-3x3-gap text-brand"></i> Products by Category
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.products.performance') }}">Products</a></li>
                    <li class="breadcrumb-item active">By Category</li>
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
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.products.by-category') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> Start Date
                    </label>
                    <input type="date" class="form-control" name="start_date"
                           value="{{ $start_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> End Date
                    </label>
                    <input type="date" class="form-control" name="end_date"
                           value="{{ $end_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing data from <strong>{{ $start_date->format('M d, Y') }}</strong>
                to <strong>{{ $end_date->format('M d, Y') }}</strong>
                ({{ $start_date->diffInDays($end_date) + 1 }} days)
            </div>
        </form>
    </div>

    <!-- Summary Banner -->
    <div class="summary-banner">
        <div class="row">
            <div class="col-md-3 summary-item">
                <div class="summary-value">{{ count($category_performance) }}</div>
                <div class="summary-label">Active Categories</div>
            </div>
            <div class="col-md-3 summary-item">
                <div class="summary-value">{{ number_format($total_products_sold) }}</div>
                <div class="summary-label">Products Sold</div>
            </div>
            <div class="col-md-3 summary-item">
                <div class="summary-value">{{ number_format($total_units_sold) }}</div>
                <div class="summary-label">Total Units</div>
            </div>
            <div class="col-md-3 summary-item">
                <div class="summary-value">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Category Performance Cards -->
    @if(count($category_performance) > 0)
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="bi bi-award text-brand"></i> Category Performance Overview</h5>
        </div>
        @foreach($category_performance as $index => $category)
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="category-performance-card shadow-sm">
                <!-- Category Header -->
                <div class="category-header-section">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="category-icon-container">
                                @php
                                    $icons = ['cup-hot', 'box-seam', 'gear-fill', 'trophy', 'star-fill'];
                                @endphp
                                <i class="bi bi-{{ $icons[$index % count($icons)] }}"></i>
                            </div>
                            <div class="category-name">{{ $category['title'] }}</div>
                            <div class="category-product-count">
                                <i class="bi bi-box"></i> {{ $category['total_products'] }} Products
                            </div>
                        </div>
                        <div>
                            @if($index < 3)
                            <div class="rank-badge rank-{{ $index + 1 }}">
                                #{{ $index + 1 }}
                            </div>
                            @else
                            <div class="rank-badge rank-other">
                                #{{ $index + 1 }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Category Body -->
                <div class="category-body">
                    <!-- Main Metrics -->
                    <div class="metric-row">
                        <div class="metric-box">
                            <span class="metric-value">{{ number_format($category['units_sold']) }}</span>
                            <span class="metric-label">Units Sold</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">{{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}</span>
                            <span class="metric-label">Revenue</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">{{ number_format($category['order_count']) }}</span>
                            <span class="metric-label">Orders</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">{{ $category['units_sold'] > 0 ? store_currency_symbol() . number_format($category['revenue'] / $category['units_sold'], 2) : 'N/A' }}</span>
                            <span class="metric-label">Avg/Unit</span>
                        </div>
                    </div>

                    <!-- Performance Bar -->
                    <div class="performance-bar-container">
                        <small class="text-muted d-block mb-2">Revenue Share</small>
                        <div class="performance-bar">
                            <div class="performance-fill"
                                 style="width: {{ $total_revenue > 0 ? ($category['revenue'] / $total_revenue) * 100 : 0 }}%">
                                {{ $total_revenue > 0 ? number_format(($category['revenue'] / $total_revenue) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>

                    <!-- Top Products in Category -->
                    @if(isset($category['top_products']) && count($category['top_products']) > 0)
                    <div class="product-list-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <i class="bi bi-star text-warning"></i> Top Products
                            </h6>
                            <span class="badge bg-brand">{{ count($category['top_products']) }}</span>
                        </div>
                        @foreach($category['top_products']->take(3) as $product)
                        <div class="product-item">
                            @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                 alt="{{ $product->name }}"
                                 class="product-thumbnail"
                                 onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                            @else
                            <div class="product-thumbnail bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-box text-muted"></i>
                            </div>
                            @endif
                            <div class="product-info">
                                <div class="product-name-small">{{ Str::limit($product->name, 30) }}</div>
                                <div class="product-units">
                                    <i class="bi bi-graph-up"></i> {{ number_format($product->units_sold) }} sold
                                </div>
                            </div>
                            <div class="text-end">
                                <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($product->revenue, 2) }}</strong>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-inbox fs-1 mb-3"></i>
        <h5>No Category Data Available</h5>
        <p class="mb-0">No sales data found for the selected date range.</p>
    </div>
    @endif

    <!-- Comparison Charts -->
    @if(count($category_performance) > 0)
    <div class="row mb-4">
        <!-- Revenue Comparison Chart -->
        <div class="col-lg-6 mb-4">
            <div class="comparison-chart-card">
                <h5 class="mb-4">
                    <i class="bi bi-pie-chart text-brand"></i> Revenue Distribution
                </h5>
                <div class="chart-container">
                    <canvas id="revenueDonutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Units Sold Comparison Chart -->
        <div class="col-lg-6 mb-4">
            <div class="comparison-chart-card">
                <h5 class="mb-4">
                    <i class="bi bi-bar-chart text-brand"></i> Units Sold Comparison
                </h5>
                <div class="chart-container">
                    <canvas id="unitsBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Comparison Table -->
    @if(count($category_performance) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-table text-brand"></i> Detailed Category Analysis
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table detailed-table mb-0">
                            <thead>
                                <tr>
                                    <th width="60">Rank</th>
                                    <th>Category</th>
                                    <th class="text-center">Products</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-center">% of Total</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Avg/Unit</th>
                                    <th class="text-end">Avg Order Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category_performance as $index => $category)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                        <div class="rank-badge rank-{{ $index + 1 }}">
                                            #{{ $index + 1 }}
                                        </div>
                                        @else
                                        <span class="text-muted fw-bold">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="category-icon-badge" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                                @php
                                                    $icons = ['cup-hot', 'box-seam', 'gear-fill', 'trophy', 'star-fill'];
                                                @endphp
                                                <i class="bi bi-{{ $icons[$index % count($icons)] }}"></i>
                                            </div>
                                            <strong>{{ $category['title'] }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ number_format($category['total_products']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($category['units_sold']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            {{ $total_revenue > 0 ? number_format(($category['revenue'] / $total_revenue) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ number_format($category['order_count']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $category['units_sold'] > 0 ? store_currency_symbol() . number_format($category['revenue'] / $category['units_sold'], 2) : 'N/A' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $category['order_count'] > 0 ? store_currency_symbol() . number_format($category['revenue'] / $category['order_count'], 2) : 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">TOTAL</th>
                                    <th class="text-center">{{ number_format($total_units_sold) }}</th>
                                    <th class="text-end">
                                        <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</strong>
                                    </th>
                                    <th class="text-center">100%</th>
                                    <th class="text-center">{{ number_format(collect($category_performance)->sum('order_count')) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Performance Tracking</h6>
                <p class="text-muted small mb-0">
                    Monitor category performance to optimize inventory and marketing strategies.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-bullseye text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Revenue Analysis</h6>
                <p class="text-muted small mb-0">
                    Identify high-performing categories and allocate resources accordingly.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-box-seam text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Product Insights</h6>
                <p class="text-muted small mb-0">
                    See top-performing products within each category for better inventory planning.
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
    const categoryData = @json($category_performance);

    // Category colors
    const colors = [
        '#5B914C', '#0dcaf0', '#0d6efd', '#ffc107', '#dc3545',
        '#6c757d', '#20c997', '#fd7e14', '#6f42c1', '#d63384'
    ];

    // Revenue Donut Chart
    const revenueDonutCtx = document.getElementById('revenueDonutChart');
    if (revenueDonutCtx && categoryData.length > 0) {
        new Chart(revenueDonutCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(cat => cat.title),
                datasets: [{
                    data: categoryData.map(cat => cat.revenue),
                    backgroundColor: colors.slice(0, categoryData.length),
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
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 11
                            }
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
                                return [
                                    label + ': {{ store_currency_symbol() }}' + value.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    percentage + '% of total'
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    // Units Sold Bar Chart
    const unitsBarCtx = document.getElementById('unitsBarChart');
    if (unitsBarCtx && categoryData.length > 0) {
        new Chart(unitsBarCtx, {
            type: 'bar',
            data: {
                labels: categoryData.map(cat => cat.title),
                datasets: [{
                    label: 'Units Sold',
                    data: categoryData.map(cat => cat.units_sold),
                    backgroundColor: colors.slice(0, categoryData.length),
                    borderColor: colors.slice(0, categoryData.length),
                    borderWidth: 2,
                    borderRadius: 8
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
                                const category = categoryData[context.dataIndex];
                                return [
                                    'Units Sold: ' + context.parsed.y.toLocaleString(),
                                    'Revenue: {{ store_currency_symbol() }}' + category.revenue.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    'Orders: ' + category.order_count.toLocaleString()
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Animate progress bars
    setTimeout(() => {
        document.querySelectorAll('.performance-fill').forEach(bar => {
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
