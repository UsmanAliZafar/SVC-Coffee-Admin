{{-- resources/views/admin/reports/inventory/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Inventory Overview - Coffee Admin')

@push('styles')
<style>
    .inventory-dashboard {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .inventory-dashboard::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .dashboard-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .dashboard-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-5px);
    }

    .dashboard-card .icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }

    .dashboard-card .value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .dashboard-card .label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .alert-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .alert-item {
        display: flex;
        align-items: center;
        padding: 15px;
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .alert-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .alert-item.critical {
        background: #f8d7da;
        border-left-color: #dc3545;
    }

    .alert-item.warning {
        background: #fff3cd;
        border-left-color: #ffc107;
    }

    .alert-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }

    .alert-icon.critical {
        background: #dc3545;
        color: white;
        animation: pulse-alert 2s ease-in-out infinite;
    }

    .alert-icon.warning {
        background: #ffc107;
        color: white;
    }

    @keyframes pulse-alert {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .inventory-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .inventory-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .inventory-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
    }

    .inventory-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .inventory-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .inventory-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .stock-level-bar {
        width: 100%;
        height: 25px;
        background: #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }

    .stock-level-fill {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.75rem;
        transition: width 0.5s ease;
    }

    .stock-level-fill.high {
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    }

    .stock-level-fill.medium {
        background: linear-gradient(90deg, #17a2b8 0%, #138496 100%);
    }

    .stock-level-fill.low {
        background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%);
    }

    .stock-level-fill.critical {
        background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stock-badge.in-stock {
        background-color: #d4edda;
        color: #155724;
    }

    .stock-badge.low-stock {
        background-color: #fff3cd;
        color: #856404;
    }

    .stock-badge.out-of-stock {
        background-color: #f8d7da;
        color: #721c24;
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

    .category-breakdown {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .category-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .category-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .category-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }

    .product-thumbnail {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .quick-action-btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .quick-action-btn.restock {
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
    }

    .quick-action-btn.restock:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
    }

    .movement-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .movement-in {
        background-color: #d4edda;
        color: #155724;
    }

    .movement-out {
        background-color: #f8d7da;
        color: #721c24;
    }

    .tab-navigation {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }

    .tab-btn {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        color: #5B914C;
    }

    .tab-btn.active {
        color: #5B914C;
        border-bottom-color: #5B914C;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    @media print {
        .no-print { display: none !important; }
        .inventory-table { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .dashboard-card { margin-bottom: 15px; }
        .inventory-table { font-size: 0.875rem; }
    }
</style>
@endpush

@section('content')
<div class="inventory-overview-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-box-seam text-brand"></i> Inventory Overview
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Inventory</li>
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

    <!-- Inventory Dashboard Summary -->
    <div class="inventory-dashboard">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div class="value">{{ number_format($total_products) }}</div>
                    <div class="label">Total Products</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-stack"></i>
                    </div>
                    <div class="value">{{ number_format($total_stock_value) }}</div>
                    <div class="label">Total Stock Units</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="value">{{ number_format($low_stock_count) }}</div>
                    <div class="label">Low Stock Items</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="value">{{ number_format($out_of_stock_count) }}</div>
                    <div class="label">Out of Stock</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts -->
    @if($critical_alerts->count() > 0 || $low_stock_alerts->count() > 0)
    <div class="alert-section">
        <h5 class="mb-4">
            <i class="bi bi-bell-fill text-danger"></i> Inventory Alerts
        </h5>

        @if($critical_alerts->count() > 0)
        <h6 class="text-danger mb-3">
            <i class="bi bi-exclamation-octagon-fill"></i> Critical - Out of Stock ({{ $critical_alerts->count() }})
        </h6>
        @foreach($critical_alerts->take(5) as $product)
        <div class="alert-item critical">
            <div class="alert-icon critical">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <strong>{{ $product->name }}</strong>
                <div class="text-muted small">SKU: {{ $product->sku }} | Stock: {{ $product->stock_quantity }}</div>
            </div>
            <button class="quick-action-btn restock">
                <i class="bi bi-plus-circle"></i> Restock Now
            </button>
        </div>
        @endforeach
        @endif

        @if($low_stock_alerts->count() > 0)
        <h6 class="text-warning mb-3 mt-4">
            <i class="bi bi-exclamation-triangle-fill"></i> Warning - Low Stock ({{ $low_stock_alerts->count() }})
        </h6>
        @foreach($low_stock_alerts->take(5) as $product)
        <div class="alert-item warning">
            <div class="alert-icon warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <strong>{{ $product->name }}</strong>
                <div class="text-muted small">
                    SKU: {{ $product->sku }} |
                    Current: {{ $product->stock_quantity }} |
                    Threshold: {{ $product->low_stock_threshold }}
                </div>
            </div>
            <button class="quick-action-btn restock">
                <i class="bi bi-plus-circle"></i> Restock
            </button>
        </div>
        @endforeach
        @endif

        @if($critical_alerts->count() > 3 || $low_stock_alerts->count() > 5)
        <div class="text-center mt-3">
            <a href="{{ route('admin.reports.inventory.alerts') }}" class="btn btn-outline-brand">
                View All {{ $critical_alerts->count() + $low_stock_alerts->count() }} Alerts
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Tab Navigation -->
    <div class="tab-navigation no-print">
        <button class="tab-btn active" data-tab="stock-levels">
            <i class="bi bi-bar-chart-line"></i> Stock Levels
        </button>
        <button class="tab-btn" data-tab="by-category">
            <i class="bi bi-grid-3x3-gap"></i> By Category
        </button>
        <button class="tab-btn" data-tab="movements">
            <i class="bi bi-arrow-left-right"></i> Recent Movements
        </button>
    </div>

    <!-- Tab: Stock Levels -->
    <div class="tab-content active" id="stock-levels">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.reports.inventory.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-funnel"></i> Stock Status
                        </label>
                        <select class="form-select" name="status">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Products</option>
                            <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-grid-3x3-gap"></i> Category
                        </label>
                        <select class="form-select" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-search"></i> Search
                        </label>
                        <input type="text" class="form-control" name="search"
                               placeholder="Product name or SKU..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-brand w-100">
                            <i class="bi bi-funnel"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stock Levels Table -->
        @if($products->count() > 0)
        <div class="inventory-table">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Threshold</th>
                        <th>Stock Level</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        $stock_percentage = 100;
                        if ($product->low_stock_threshold > 0) {
                            $stock_percentage = ($product->stock_quantity / ($product->low_stock_threshold * 3)) * 100;
                            $stock_percentage = min($stock_percentage, 100);
                        }

                        if ($product->stock_quantity <= 0) {
                            $status = 'out_of_stock';
                            $status_class = 'critical';
                            $status_label = 'Out of Stock';
                        } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
                            $status = 'low_stock';
                            $status_class = 'low';
                            $status_label = 'Low Stock';
                        } elseif ($stock_percentage < 50) {
                            $status = 'medium_stock';
                            $status_class = 'medium';
                            $status_label = 'In Stock';
                        } else {
                            $status = 'in_stock';
                            $status_class = 'high';
                            $status_label = 'In Stock';
                        }
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($product->main_image)
                                <img src="{{ asset('storage/' . $product->main_image) }}"
                                     alt="{{ $product->name }}"
                                     class="product-thumbnail me-3"
                                     onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                                @else
                                <div class="product-thumbnail bg-light d-flex align-items-center justify-content-center me-3">
                                    <i class="bi bi-box text-muted"></i>
                                </div>
                                @endif
                                <strong>{{ Str::limit($product->name, 30) }}</strong>
                            </div>
                        </td>
                        <td><code>{{ $product->sku }}</code></td>
                        <td>
                            @if($product->category)
                            <span class="badge bg-light text-dark">{{ $product->category->title }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <strong class="fs-5">{{ number_format($product->stock_quantity) }}</strong>
                        </td>
                        <td class="text-center">
                            <span class="text-muted">{{ number_format($product->low_stock_threshold) }}</span>
                        </td>
                        <td>
                            <div class="stock-level-bar">
                                <div class="stock-level-fill {{ $status_class }}"
                                     style="width: {{ $stock_percentage }}%">
                                    {{ round($stock_percentage) }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="stock-badge {{ str_replace('_', '-', $status) }}">
                                {{ $status_label }}
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
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-clock-history"></i> View History</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    @if($status == 'low_stock' || $status == 'out_of_stock')
                                    <li><a class="dropdown-item text-brand" href="#"><i class="bi bi-plus-circle"></i> Restock</a></li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-inbox fs-1 mb-3"></i>
            <h5>No Products Found</h5>
            <p class="mb-0">No products match your current filters.</p>
        </div>
        @endif
    </div>

    <!-- Tab: By Category -->
    <div class="tab-content" id="by-category">
        <div class="row">
            @foreach($categories as $index => $category)
            @php
                $categoryProducts = $products->where('category_id', $category->id);
                $categoryStock = $categoryProducts->sum('stock_quantity');
                $categoryLowStock = $categoryProducts->filter(function($p) {
                    return $p->stock_quantity <= $p->low_stock_threshold;
                })->count();
            @endphp
            <div class="col-md-6 mb-4">
                <div class="category-breakdown">
                    <div class="d-flex align-items-center mb-3">
                        <div class="category-icon">
                            @php
                                $icons = ['cup-hot', 'box-seam', 'gear-fill', 'trophy', 'star-fill'];
                            @endphp
                            <i class="bi bi-{{ $icons[$index % count($icons)] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $category->title }}</h5>
                            <small class="text-muted">{{ $categoryProducts->count() }} Products</small>
                        </div>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="fw-bold text-brand fs-4">{{ number_format($categoryStock) }}</div>
                            <small class="text-muted">Total Units</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success fs-4">{{ $categoryProducts->count() - $categoryLowStock }}</div>
                            <small class="text-muted">In Stock</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning fs-4">{{ $categoryLowStock }}</div>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>

                    @if($categoryLowStock > 0)
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>{{ $categoryLowStock }}</strong> item(s) need restocking
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tab: Recent Movements -->
    <div class="tab-content" id="movements">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Stock movements tracking shows inventory changes from sales, restocking, and adjustments.
            <a href="{{ route('admin.reports.inventory.movement') }}" class="alert-link">View detailed movement history →</a>
        </div>

        <div class="chart-section">
            <h5 class="mb-4">
                <i class="bi bi-graph-up text-brand"></i> Stock Movement Trends (Last 30 Days)
            </h5>
            <div class="chart-container">
                <canvas id="movementTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-box-seam text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Real-Time Tracking</h6>
                <p class="text-muted small mb-0">
                    Inventory levels update automatically with every sale and restock.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-bell text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Smart Alerts</h6>
                <p class="text-muted small mb-0">
                    Receive notifications when stock falls below threshold levels.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up-arrow text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Optimization</h6>
                <p class="text-muted small mb-0">
                    Use insights to optimize stock levels and reduce holding costs.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        // Add active class to clicked tab
        this.classList.add('active');
        const tabId = this.dataset.tab;
        document.getElementById(tabId).classList.add('active');
    });
});

// Movement Trends Chart (placeholder data)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('movementTrendsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Day 1', 'Day 5', 'Day 10', 'Day 15', 'Day 20', 'Day 25', 'Day 30'],
                datasets: [
                    {
                        label: 'Stock In',
                        data: [120, 150, 180, 140, 200, 170, 190],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Stock Out',
                        data: [80, 95, 110, 100, 130, 120, 140],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' units';
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
