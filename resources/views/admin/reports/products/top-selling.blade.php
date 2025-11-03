{{-- resources/views/admin/reports/products/top-selling.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Top Selling Products - Coffee Admin')

@push('styles')
<style>
    .top-product-card {
        border-radius: 15px;
        border: 3px solid #f8f9fa;
        transition: all 0.3s ease;
        overflow: hidden;
        background: white;
        position: relative;
    }

    .top-product-card:hover {
        border-color: #5B914C;
        transform: translateY(-8px);
        box-shadow: 0 1rem 2rem rgba(91, 145, 76, 0.25)!important;
    }

    .rank-badge {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        z-index: 10;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .rank-1 {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #856404;
    }

    .rank-2 {
        background: linear-gradient(135deg, #c0c0c0 0%, #e0e0e0 100%);
        color: #495057;
    }

    .rank-3 {
        background: linear-gradient(135deg, #cd7f32 0%, #daa06d 100%);
        color: #fff;
    }

    .rank-other {
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
    }

    .product-image-section {
        position: relative;
        height: 250px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: 40px;
    }

    .product-image-section img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .top-product-card:hover .product-image-section img {
        transform: scale(1.1);
    }

    .crown-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 2rem;
        color: #ffd700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .product-details {
        padding: 25px;
    }

    .product-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 10px;
        min-height: 60px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-category-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #f8f9fa;
        color: #495057;
        margin-bottom: 15px;
    }

    .sales-amount {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin: 15px 0;
    }

    .units-sold {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        padding: 12px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 15px;
    }

    .units-sold .number {
        font-size: 1.8rem;
        font-weight: bold;
        color: #0d47a1;
        display: block;
    }

    .units-sold .label {
        font-size: 0.75rem;
        color: #1565c0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .performance-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 15px;
    }

    .performance-item {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .performance-item .value {
        font-size: 1.1rem;
        font-weight: bold;
        color: #212529;
        display: block;
    }

    .performance-item .label {
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

    .summary-cards {
        margin-bottom: 30px;
    }

    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #5B914C;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.15);
    }

    .summary-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    .summary-card .value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .summary-card .label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .chart-card {
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

    .comparison-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .comparison-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .comparison-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
    }

    .comparison-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .comparison-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .comparison-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .medal-icon {
        font-size: 1.5rem;
    }

    .trend-indicator {
        display: inline-flex;
        align-items: center;
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

    .period-selector {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .period-btn {
        padding: 8px 20px;
        border: 2px solid #e9ecef;
        border-radius: 20px;
        background: white;
        color: #6c757d;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .period-btn:hover,
    .period-btn.active {
        border-color: #5B914C;
        background: #5B914C;
        color: white;
    }

    .limit-selector {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .podium-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .podium {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 20px;
        margin-top: 20px;
    }

    .podium-place {
        text-align: center;
        position: relative;
    }

    .podium-place.second { order: 1; }
    .podium-place.first { order: 2; }
    .podium-place.third { order: 3; }

    .podium-block {
        width: 150px;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border-radius: 10px 10px 0 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        padding: 20px 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .podium-place.first .podium-block {
        height: 200px;
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #856404;
    }

    .podium-place.second .podium-block {
        height: 150px;
        background: linear-gradient(135deg, #c0c0c0 0%, #e0e0e0 100%);
        color: #495057;
    }

    .podium-place.third .podium-block {
        height: 120px;
        background: linear-gradient(135deg, #cd7f32 0%, #daa06d 100%);
    }

    @media print {
        .no-print { display: none !important; }
        .top-product-card { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .podium { flex-direction: column; align-items: center; }
        .podium-place { order: unset !important; width: 100%; }
        .podium-block { width: 100% !important; }
    }
</style>
@endpush

@section('content')
<div class="top-selling-products-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-trophy text-warning"></i> Top Selling Products
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.products.performance') }}">Products</a></li>
                    <li class="breadcrumb-item active">Top Selling</li>
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

    <!-- Filters -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.products.top-selling') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar3"></i> Period
                    </label>
                    <select class="form-select" name="period" onchange="this.form.submit()">
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
                    <input type="date" class="form-control" name="date" value="{{ request('date', $date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-hash"></i> Show Top
                    </label>
                    <select class="form-select" name="limit">
                        <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10</option>
                        <option value="20" {{ $limit == 20 ? 'selected' : '' }}>Top 20</option>
                        <option value="50" {{ $limit == 50 ? 'selected' : '' }}>Top 50</option>
                        <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Top 100</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing <strong>Top {{ $limit }}</strong> products from
                <strong>{{ $date_range['start']->format('M d, Y') }}</strong> to
                <strong>{{ $date_range['end']->format('M d, Y') }}</strong>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row summary-cards">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="value">{{ number_format($total_units_sold) }}</div>
                <div class="label">Total Units Sold</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</div>
                <div class="label">Total Revenue</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="value">{{ number_format($order_count) }}</div>
                <div class="label">Total Orders</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ $total_units_sold > 0 ? number_format($total_revenue / $total_units_sold, 2) : '0.00' }}</div>
                <div class="label">Avg Price/Unit</div>
            </div>
        </div>
    </div>

    <!-- Winner's Podium (Top 3) -->
    @if(count($top_products) >= 3)
    <div class="podium-section">
        <h4 class="text-center mb-4">
            <i class="bi bi-trophy-fill text-warning"></i> Top 3 Best Sellers
        </h4>
        <div class="podium">
            <!-- Second Place -->
            <div class="podium-place second">
                <div class="mb-3">
                    <i class="bi bi-award-fill medal-icon" style="color: #c0c0c0;"></i>
                </div>
                <div class="podium-block">
                    <div class="fw-bold mb-2">#2</div>
                    <div class="small mb-2">{{ $top_products[1]->name }}</div>
                    <div class="fw-bold">{{ number_format($top_products[1]->total_sold) }} units</div>
                </div>
            </div>

            <!-- First Place -->
            <div class="podium-place first">
                <div class="mb-3">
                    <i class="bi bi-trophy-fill medal-icon" style="color: #ffd700; font-size: 2.5rem;"></i>
                </div>
                <div class="podium-block">
                    <div class="fw-bold mb-2">#1</div>
                    <div class="small mb-2">{{ $top_products[0]->name }}</div>
                    <div class="fw-bold">{{ number_format($top_products[0]->total_sold) }} units</div>
                </div>
            </div>

            <!-- Third Place -->
            <div class="podium-place third">
                <div class="mb-3">
                    <i class="bi bi-award-fill medal-icon" style="color: #cd7f32;"></i>
                </div>
                <div class="podium-block">
                    <div class="fw-bold mb-2">#3</div>
                    <div class="small mb-2">{{ $top_products[2]->name }}</div>
                    <div class="fw-bold">{{ number_format($top_products[2]->total_sold) }} units</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Products Grid -->
    @if(count($top_products) > 0)
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="bi bi-grid-3x3 text-brand"></i> All Top Selling Products</h5>
        </div>
        @foreach($top_products as $index => $product)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="top-product-card shadow-sm">
                <!-- Rank Badge -->
                <div class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : 'rank-other' }}">
                    #{{ $index + 1 }}
                </div>

                @if($index === 0)
                <div class="crown-icon">
                    <i class="bi bi-gem-fill"></i>
                </div>
                @endif

                <!-- Product Image -->
                <div class="product-image-section">
                    @if($product->main_image)
                    <img src="{{ asset('storage/' . $product->main_image) }}"
                         alt="{{ $product->name }}"
                         onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                    @else
                    <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                    @endif
                </div>

                <!-- Product Details -->
                <div class="product-details">
                    <div class="product-title">{{ $product->name }}</div>

                    @if($product->category_name)
                    <span class="product-category-badge">
                        <i class="bi bi-tag"></i> {{ $product->category_name }}
                    </span>
                    @endif

                    <div class="units-sold">
                        <span class="number">{{ number_format($product->total_sold) }}</span>
                        <span class="label">Units Sold</span>
                    </div>

                    <div class="sales-amount">
                        {{ store_currency_symbol() }}{{ number_format($product->total_revenue, 2) }}
                    </div>

                    <div class="performance-grid">
                        <div class="performance-item">
                            <span class="value">{{ number_format($product->order_count) }}</span>
                            <span class="label">Orders</span>
                        </div>
                        <div class="performance-item">
                            <span class="value">{{ store_currency_symbol() }}{{ $product->total_sold > 0 ? number_format($product->total_revenue / $product->total_sold, 2) : '0.00' }}</span>
                            <span class="label">Per Unit</span>
                        </div>
                        <div class="performance-item">
                            <span class="value">{{ $total_revenue > 0 ? number_format(($product->total_revenue / $total_revenue) * 100, 1) : 0 }}%</span>
                            <span class="label">Of Total</span>
                        </div>
                        <div class="performance-item">
                            <span class="value">{{ $product->avg_price ? store_currency_symbol() . number_format($product->avg_price, 2) : 'N/A' }}</span>
                            <span class="label">Avg Price</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-inbox fs-1 mb-3"></i>
        <h5>No Sales Data Available</h5>
        <p class="mb-0">No products have been sold in the selected period.</p>
    </div>
    @endif

    <!-- Sales Trend Chart -->
    @if(count($top_products) > 0)
    <div class="chart-card">
        <h5 class="mb-4">
            <i class="bi bi-graph-up text-brand"></i> Sales Comparison (Top 10)
        </h5>
        <div class="chart-container">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Detailed Comparison Table -->
    @if(count($top_products) > 0)
    <div class="comparison-table">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th width="60">Rank</th>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-center">Units Sold</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-center">% of Total</th>
                    <th class="text-center">Orders</th>
                    <th class="text-end">Avg Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_products as $index => $product)
                <tr>
                    <td>
                        @if($index < 3)
                        <i class="bi bi-trophy-fill medal-icon" style="color: {{ $index == 0 ? '#ffd700' : ($index == 1 ? '#c0c0c0' : '#cd7f32') }};"></i>
                        @endif
                        <strong>#{{ $index + 1 }}</strong>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                 alt="{{ $product->name }}"
                                 class="rounded me-2"
                                 style="width: 40px; height: 40px; object-fit: cover;"
                                 onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                            @endif
                            <strong>{{ $product->name }}</strong>
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
                        <span class="badge bg-success fs-6">{{ number_format($product->total_sold) }}</span>
                    </td>
                    <td class="text-end">
                        <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($product->total_revenue, 2) }}</strong>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">
                            {{ $total_revenue > 0 ? number_format(($product->total_revenue / $total_revenue) * 100, 1) : 0 }}%
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ number_format($product->order_count) }}</span>
                    </td>
                    <td class="text-end">
                        {{ $product->avg_price ? store_currency_symbol() . number_format($product->avg_price, 2) : 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4">TOTAL</th>
                    <th class="text-center">{{ number_format($total_units_sold) }}</th>
                    <th class="text-end">
                        <strong class="text-brand">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</strong>
                    </th>
                    <th class="text-center">100%</th>
                    <th class="text-center">{{ number_format($order_count) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-lightning-charge text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Best Performers</h6>
                <p class="text-muted small mb-0">
                    Products ranked by total units sold in the selected period.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-graph-up-arrow text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Real-Time Rankings</h6>
                <p class="text-muted small mb-0">
                    Rankings update automatically based on your latest sales data.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-award text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Performance Metrics</h6>
                <p class="text-muted small mb-0">
                    Detailed analytics including revenue, orders, and average prices.
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
    // Top Products Bar Chart
    const chartCtx = document.getElementById('topProductsChart');
    if (chartCtx) {
        const products = @json($top_products->take(10));

        new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: products.map(p => p.name.length > 25 ? p.name.substring(0, 25) + '...' : p.name),
                datasets: [{
                    label: 'Units Sold',
                    data: products.map(p => p.total_sold),
                    backgroundColor: products.map((_, index) => {
                        if (index === 0) return '#ffd700';
                        if (index === 1) return '#c0c0c0';
                        if (index === 2) return '#cd7f32';
                        return '#5B914C';
                    }),
                    borderColor: products.map((_, index) => {
                        if (index === 0) return '#daa520';
                        if (index === 1) return '#a9a9a9';
                        if (index === 2) return '#a0522d';
                        return '#4a7a3d';
                    }),
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
                                const product = products[context.dataIndex];
                                return [
                                    'Units Sold: ' + context.parsed.y.toLocaleString(),
                                    'Revenue: {{ store_currency_symbol() }}' + product.total_revenue.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    'Orders: ' + product.order_count.toLocaleString()
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
});
</script>
@endpush
