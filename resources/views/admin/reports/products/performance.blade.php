{{-- resources/views/admin/reports/products/performance.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Product Performance - Coffee Admin')

@push('styles')
<style>
    .performance-dashboard {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .performance-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #5B914C;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
    }

    .performance-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.15);
    }

    .performance-card .icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }

    .performance-card .value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .performance-card .label {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .performance-card .change {
        font-size: 0.875rem;
        font-weight: 600;
        margin-top: 10px;
    }

    .change.positive {
        color: #28a745;
    }

    .change.negative {
        color: #dc3545;
    }

    .product-performance-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .product-performance-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .product-performance-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
        white-space: nowrap;
    }

    .product-performance-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .product-performance-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .product-performance-table tbody tr:hover {
        background-color: #f8fdf6;
        transform: scale(1.01);
    }

    .product-image-cell {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-excellent {
        background-color: #d4edda;
        color: #155724;
    }

    .status-good {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .status-average {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-poor {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-critical {
        background-color: #f5c6cb;
        color: #721c24;
        animation: pulse-red 2s ease-in-out infinite;
    }

    @keyframes pulse-red {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .stock-indicator {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stock-in {
        background-color: #d4edda;
        color: #155724;
    }

    .stock-low {
        background-color: #fff3cd;
        color: #856404;
    }

    .stock-out {
        background-color: #f8d7da;
        color: #721c24;
    }

    .performance-score {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        position: relative;
    }

    .score-excellent {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .score-good {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
    }

    .score-average {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }

    .score-poor {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
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

    .metric-comparison {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .metric-bar {
        flex-grow: 1;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .metric-fill {
        height: 100%;
        background: linear-gradient(90deg, #5B914C 0%, #6BA055 100%);
        transition: width 0.5s ease;
    }

    .quick-filter-btn {
        padding: 8px 20px;
        border: 2px solid #e9ecef;
        border-radius: 20px;
        background: white;
        color: #6c757d;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin: 5px;
    }

    .quick-filter-btn:hover,
    .quick-filter-btn.active {
        border-color: #5B914C;
        background: #5B914C;
        color: white;
    }

    .trend-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .trend-up {
        background-color: #d4edda;
        color: #155724;
    }

    .trend-down {
        background-color: #f8d7da;
        color: #721c24;
    }

    .trend-stable {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .actions-dropdown {
        position: relative;
    }

    .inventory-alert {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 12px;
        height: 12px;
        background: #dc3545;
        border-radius: 50%;
        border: 2px solid white;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    .comparison-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .comparison-item {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #5B914C;
    }

    @media print {
        .no-print { display: none !important; }
        .product-performance-table { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .performance-card { margin-bottom: 15px; }
        .product-performance-table { font-size: 0.875rem; }
    }
</style>
@endpush

@section('content')
<div class="product-performance-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-speedometer2 text-brand"></i> Product Performance Analytics
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Product Performance</li>
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

    <!-- Performance Dashboard -->
    <div class="performance-dashboard">
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="performance-card">
                    <div class="icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="value">{{ number_format($total_products) }}</div>
                    <div class="label">Total Products</div>
                    <div class="change positive">
                        <i class="bi bi-arrow-up"></i> {{ $active_products }} Active
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="performance-card">
                    <div class="icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</div>
                    <div class="label">Total Revenue</div>
                    <div class="change positive">
                        <i class="bi bi-arrow-up"></i> Period Performance
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="performance-card">
                    <div class="icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-layers"></i>
                    </div>
                    <div class="value">{{ number_format($total_units_sold) }}</div>
                    <div class="label">Units Sold</div>
                    <div class="change positive">
                        <i class="bi bi-arrow-up"></i> Sales Volume
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="performance-card">
                    <div class="icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="value">{{ $low_stock_count }}</div>
                    <div class="label">Low Stock Items</div>
                    @if($low_stock_count > 0)
                    <div class="change negative">
                        <i class="bi bi-arrow-down"></i> Needs Attention
                    </div>
                    @else
                    <div class="change positive">
                        <i class="bi bi-check-circle"></i> All Good
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.products.performance') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> Start Date
                    </label>
                    <input type="date" class="form-control" name="start_date"
                           value="{{ $start_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> End Date
                    </label>
                    <input type="date" class="form-control" name="end_date"
                           value="{{ $end_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel"></i> Performance Filter
                    </label>
                    <select class="form-select" name="performance_filter">
                        <option value="all" {{ request('performance_filter') == 'all' ? 'selected' : '' }}>All Products</option>
                        <option value="excellent" {{ request('performance_filter') == 'excellent' ? 'selected' : '' }}>Excellent Performers</option>
                        <option value="good" {{ request('performance_filter') == 'good' ? 'selected' : '' }}>Good Performers</option>
                        <option value="average" {{ request('performance_filter') == 'average' ? 'selected' : '' }}>Average Performers</option>
                        <option value="poor" {{ request('performance_filter') == 'poor' ? 'selected' : '' }}>Poor Performers</option>
                        <option value="low_stock" {{ request('performance_filter') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="mt-3">
                <label class="form-label fw-semibold small">Quick Filters:</label>
                <div class="d-flex flex-wrap">
                    <button type="button" class="quick-filter-btn {{ !request('performance_filter') || request('performance_filter') == 'all' ? 'active' : '' }}"
                            onclick="setFilter('all')">
                        All Products
                    </button>
                    <button type="button" class="quick-filter-btn {{ request('performance_filter') == 'excellent' ? 'active' : '' }}"
                            onclick="setFilter('excellent')">
                        <i class="bi bi-star-fill"></i> Excellent
                    </button>
                    <button type="button" class="quick-filter-btn {{ request('performance_filter') == 'low_stock' ? 'active' : '' }}"
                            onclick="setFilter('low_stock')">
                        <i class="bi bi-exclamation-triangle"></i> Low Stock
                    </button>
                </div>
            </div>

            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing data from <strong>{{ $start_date->format('M d, Y') }}</strong>
                to <strong>{{ $end_date->format('M d, Y') }}</strong>
                @if(request('performance_filter') && request('performance_filter') != 'all')
                | Filter: <strong class="text-capitalize">{{ str_replace('_', ' ', request('performance_filter')) }}</strong>
                @endif
            </div>
        </form>
    </div>

    <!-- Performance Charts -->
    @if(count($products) > 0)
    <div class="row mb-4">
        <!-- Top Performers Chart -->
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-bar-chart text-brand"></i> Top 10 Performers by Revenue
                </h5>
                <div class="chart-container">
                    <canvas id="topPerformersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-pie-chart text-brand"></i> Performance Distribution
                </h5>
                <div class="chart-container">
                    <canvas id="performanceDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Product Performance Table -->
    @if(count($products) > 0)
    <div class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check text-brand"></i> Detailed Product Performance
                    </h5>
                    <span class="badge bg-brand">{{ count($products) }} Products</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table product-performance-table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Units Sold</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Avg Price</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Turn Rate</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Status</th>
                                <th class="text-center no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            @php
                                // Calculate performance score (0-100)
                                $max_revenue = $products->max('revenue');
                                $revenue_score = $max_revenue > 0 ? ($product->revenue / $max_revenue) * 50 : 0;

                                $max_units = $products->max('units_sold');
                                $units_score = $max_units > 0 ? ($product->units_sold / $max_units) * 30 : 0;

                                $order_score = min(($product->order_count / 10) * 20, 20);

                                $performance_score = round($revenue_score + $units_score + $order_score);

                                // Determine performance level
                                if ($performance_score >= 80) {
                                    $performance_level = 'excellent';
                                    $performance_class = 'score-excellent';
                                } elseif ($performance_score >= 60) {
                                    $performance_level = 'good';
                                    $performance_class = 'score-good';
                                } elseif ($performance_score >= 40) {
                                    $performance_level = 'average';
                                    $performance_class = 'score-average';
                                } else {
                                    $performance_level = 'poor';
                                    $performance_class = 'score-poor';
                                }

                                // Stock status
                                $stock_status = $product->stock_quantity <= 0 ? 'out' :
                                               ($product->stock_quantity <= $product->low_stock_threshold ? 'low' : 'in');

                                // Turn rate (units sold / stock quantity)
                                $turn_rate = $product->stock_quantity > 0 ?
                                            number_format(($product->units_sold / $product->stock_quantity) * 100, 1) : 'N/A';
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}"
                                             alt="{{ $product->name }}"
                                             class="product-image-cell me-3"
                                             onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                                        @else
                                        <div class="product-image-cell bg-light d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-box text-muted"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <strong>{{ Str::limit($product->name, 30) }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>
                                    @if($product->category_name)
                                    <span class="badge bg-light text-dark">{{ $product->category_name }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="position-relative d-inline-block">
                                        <span class="stock-indicator stock-{{ $stock_status }}">
                                            {{ number_format($product->stock_quantity) }}
                                        </span>
                                        @if($stock_status == 'low' || $stock_status == 'out')
                                        <span class="inventory-alert"></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ number_format($product->units_sold) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($product->revenue, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    {{ $product->units_sold > 0 ? store_currency_symbol() . number_format($product->revenue / $product->units_sold, 2) : 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ number_format($product->order_count) }}</span>
                                </td>
                                <td class="text-center">
                                    @if(is_numeric($turn_rate))
                                    <span class="trend-indicator {{ $turn_rate > 50 ? 'trend-up' : ($turn_rate > 20 ? 'trend-stable' : 'trend-down') }}">
                                        {{ $turn_rate }}%
                                    </span>
                                    @else
                                    <span class="text-muted">{{ $turn_rate }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="performance-score {{ $performance_class }}">
                                        {{ $performance_score }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-{{ $performance_level }}">
                                        {{ ucfirst($performance_level) }}
                                    </span>
                                </td>
                                <td class="text-center no-print">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-eye"></i> View Details</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil"></i> Edit Product</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-graph-up"></i> View Analytics</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if($stock_status == 'low' || $stock_status == 'out')
                                            <li><a class="dropdown-item text-warning" href="#"><i class="bi bi-box-seam"></i> Restock</a></li>
                                            @endif
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
        <h5>No Performance Data</h5>
        <p class="mb-0">No product performance data available for the selected period and filters.</p>
    </div>
    @endif

    <!-- Performance Insights -->
    @if(count($products) > 0)
    <div class="comparison-section">
        <h5 class="mb-4">
            <i class="bi bi-lightbulb text-warning"></i> Performance Insights
        </h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="comparison-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="bi bi-trophy text-warning"></i> Best Performer</h6>
                    </div>
                    @if($products->count() > 0)
                    @php $best = $products->sortByDesc('revenue')->first(); @endphp
                    <p class="mb-1"><strong>{{ $best->name }}</strong></p>
                    <p class="text-muted small mb-0">
                        Revenue: {{ store_currency_symbol() }}{{ number_format($best->revenue, 2) }} |
                        Units: {{ number_format($best->units_sold) }}
                    </p>
                    @endif
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="comparison-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="bi bi-arrow-up-circle text-success"></i> Highest Turnover</h6>
                    </div>
                    @if($products->count() > 0)
                    @php
                        $highest_turn = $products->filter(function($p) {
                            return $p->stock_quantity > 0;
                        })->sortByDesc(function($p) {
                            return $p->units_sold / $p->stock_quantity;
                        })->first();
                    @endphp
                    @if($highest_turn)
                    <p class="mb-1"><strong>{{ $highest_turn->name }}</strong></p>
                    <p class="text-muted small mb-0">
                        Turn Rate: {{ number_format(($highest_turn->units_sold / $highest_turn->stock_quantity) * 100, 1) }}%
                    </p>
                    @endif
                    @endif
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="comparison-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="bi bi-exclamation-triangle text-danger"></i> Needs Attention</h6>
                    </div>
                    @if($low_stock_count > 0)
                    <p class="mb-1"><strong>{{ $low_stock_count }} Low Stock Items</strong></p>
                    <p class="text-muted small mb-0">Immediate restocking required</p>
                    @else
                    <p class="mb-1"><strong>All Stock Levels Good</strong></p>
                    <p class="text-muted small mb-0">No urgent action needed</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-speedometer2 text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Performance Scoring</h6>
                <p class="text-muted small mb-0">
                    Products scored based on revenue (50%), units sold (30%), and order count (20%).
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-arrows-angle-contract text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Turnover Rate</h6>
                <p class="text-muted small mb-0">
                    Shows how quickly products move relative to stock levels. Higher is better.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up-arrow text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Action Required</h6>
                <p class="text-muted small mb-0">
                    Monitor low stock items and poor performers for strategic decisions.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Filter helper
function setFilter(filter) {
    const form = document.getElementById('filterForm');
    const select = form.querySelector('[name="performance_filter"]');
    select.value = filter;
    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const products = @json($products->take(10));

    // Top Performers Chart
    const topPerformersCtx = document.getElementById('topPerformersChart');
    if (topPerformersCtx && products.length > 0) {
        new Chart(topPerformersCtx, {
            type: 'bar',
            data: {
                labels: products.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name),
                datasets: [{
                    label: 'Revenue',
                    data: products.map(p => p.revenue),
                    backgroundColor: '#5B914C',
                    borderColor: '#4a7a3d',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const product = products[context.dataIndex];
                                return [
                                    'Revenue: {{ store_currency_symbol() }}' + context.parsed.x.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    'Units: ' + product.units_sold.toLocaleString(),
                                    'Orders: ' + product.order_count.toLocaleString()
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
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

    // Performance Distribution Chart
    const distributionCtx = document.getElementById('performanceDistributionChart');
    if (distributionCtx) {
        const allProducts = @json($products);
        const distribution = {
            excellent: 0,
            good: 0,
            average: 0,
            poor: 0
        };

        allProducts.forEach(product => {
            const maxRevenue = Math.max(...allProducts.map(p => p.revenue));
            const revenueScore = maxRevenue > 0 ? (product.revenue / maxRevenue) * 50 : 0;
            const maxUnits = Math.max(...allProducts.map(p => p.units_sold));
            const unitsScore = maxUnits > 0 ? (product.units_sold / maxUnits) * 30 : 0;
            const orderScore = Math.min((product.order_count / 10) * 20, 20);
            const score = revenueScore + unitsScore + orderScore;

            if (score >= 80) distribution.excellent++;
            else if (score >= 60) distribution.good++;
            else if (score >= 40) distribution.average++;
            else distribution.poor++;
        });

        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Poor'],
                datasets: [{
                    data: [distribution.excellent, distribution.good, distribution.average, distribution.poor],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
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
                            usePointStyle: true
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
                                return label + ': ' + value + ' (' + percentage + '%)';
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
