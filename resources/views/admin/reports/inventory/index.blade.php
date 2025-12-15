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

    <!-- ✅ FIXED: Inventory Dashboard Summary -->
    <div class="inventory-dashboard">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_products']) }}</div>
                    <div class="label">Total Products</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        Simple + Variants
                    </small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-stack"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_quantity']) }}</div>
                    <div class="label">Total Stock Units</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        Across all warehouses
                    </small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="value">{{ number_format($stats['in_stock']) }}</div>
                    <div class="label">In Stock Items</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        {{ $stats['in_stock_percentage'] }}% of total
                    </small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="value">{{ number_format($stats['low_stock']) }}</div>
                    <div class="label">Low Stock Items</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        {{ $stats['low_stock_percentage'] }}% needs attention
                    </small>
                </div>
            </div>
        </div>

        <!-- ✅ NEW: Additional Statistics Row -->
        <div class="row mt-3">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="value">{{ number_format($stats['out_of_stock']) }}</div>
                    <div class="label">Out of Stock</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        {{ $stats['out_of_stock_percentage'] }}% unavailable
                    </small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($stats['total_value'], 0) }}</div>
                    <div class="label">Total Stock Value</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        At current prices
                    </small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_warehouses']) }}</div>
                    <div class="label">Active Warehouses</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        {{ number_format($stats['total_available']) }} units available
                    </small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_reserved']) }}</div>
                    <div class="label">Reserved Stock</div>
                    <small class="mt-2 d-block" style="opacity: 0.8;">
                        Pending orders
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ FIXED: Critical Alerts Section -->
    @php
        // Get critical alerts (out of stock)
        $criticalProducts = \App\Models\Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->where('stock_quantity', '<=', 0)
            ->whereDoesntHave('warehouseStock', function($q) {
                $q->where('quantity', '>', 0);
            })
            ->with('category')
            ->take(5)
            ->get();

        $criticalVariants = \App\Models\ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->where('stock_quantity', '<=', 0)
            ->whereDoesntHave('warehouseStock', function($q) {
                $q->where('quantity', '>', 0);
            })
            ->with('product.category')
            ->take(5)
            ->get();

        // Get low stock alerts
        $lowStockProducts = \App\Models\Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->take(5)
            ->get();

        $lowStockVariants = \App\Models\ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->with('product.category')
            ->take(5)
            ->get();
    @endphp

    @if($criticalProducts->count() > 0 || $criticalVariants->count() > 0 || $lowStockProducts->count() > 0 || $lowStockVariants->count() > 0)
    <div class="alert-section">
        <h5 class="mb-4">
            <i class="bi bi-bell-fill text-danger"></i> Inventory Alerts
        </h5>

        @if($criticalProducts->count() > 0 || $criticalVariants->count() > 0)
        <h6 class="text-danger mb-3">
            <i class="bi bi-exclamation-octagon-fill"></i> Critical - Out of Stock ({{ $criticalProducts->count() + $criticalVariants->count() }})
        </h6>

        {{-- Critical Simple Products --}}
        @foreach($criticalProducts as $product)
        <div class="alert-item critical">
            <div class="alert-icon critical">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <strong>{{ $product->name }}</strong>
                <div class="text-muted small">
                    SKU: {{ $product->sku }} |
                    Stock: {{ $product->stock_quantity }} |
                    Category: {{ $product->category ? $product->category->title : 'N/A' }}
                </div>
            </div>
            <a href="{{ route('admin.inventory.adjust') }}?product_id={{ $product->id }}" class="quick-action-btn restock">
                <i class="bi bi-plus-circle"></i> Restock Now
            </a>
        </div>
        @endforeach

        {{-- Critical Variants --}}
        @foreach($criticalVariants as $variant)
        <div class="alert-item critical">
            <div class="alert-icon critical">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <strong>{{ $variant->product->name }}</strong>
                <span class="badge bg-info">{{ $variant->getFullName() }}</span>
                <div class="text-muted small">
                    SKU: {{ $variant->sku }} |
                    Stock: {{ $variant->stock_quantity }} |
                    Category: {{ $variant->product->category ? $variant->product->category->title : 'N/A' }}
                </div>
            </div>
            <a href="{{ route('admin.inventory.adjust') }}?product_id={{ $variant->product_id }}&variant_id={{ $variant->id }}" class="quick-action-btn restock">
                <i class="bi bi-plus-circle"></i> Restock Now
            </a>
        </div>
        @endforeach
        @endif

        @if($lowStockProducts->count() > 0 || $lowStockVariants->count() > 0)
        <h6 class="text-warning mb-3 mt-4">
            <i class="bi bi-exclamation-triangle-fill"></i> Warning - Low Stock ({{ $lowStockProducts->count() + $lowStockVariants->count() }})
        </h6>

        {{-- Low Stock Simple Products --}}
        @foreach($lowStockProducts as $product)
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
            <a href="{{ route('admin.inventory.adjust') }}?product_id={{ $product->id }}" class="quick-action-btn restock">
                <i class="bi bi-plus-circle"></i> Restock
            </a>
        </div>
        @endforeach

        {{-- Low Stock Variants --}}
        @foreach($lowStockVariants as $variant)
        <div class="alert-item warning">
            <div class="alert-icon warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <strong>{{ $variant->product->name }}</strong>
                <span class="badge bg-info">{{ $variant->getFullName() }}</span>
                <div class="text-muted small">
                    SKU: {{ $variant->sku }} |
                    Current: {{ $variant->stock_quantity }} |
                    Threshold: {{ $variant->low_stock_threshold }}
                </div>
            </div>
            <a href="{{ route('admin.inventory.adjust') }}?product_id={{ $variant->product_id }}&variant_id={{ $variant->id }}" class="quick-action-btn restock">
                <i class="bi bi-plus-circle"></i> Restock
            </a>
        </div>
        @endforeach
        @endif

        <div class="text-center mt-3 d-none">
            <a href="{{ route('admin.inventory.low-stock') }}" class="btn btn-outline-brand">
                View All Low Stock Items
                <i class="bi bi-arrow-right"></i>
            </a>
            <a href="{{ route('admin.inventory.out-of-stock') }}" class="btn btn-outline-danger ms-2">
                View All Out of Stock Items
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    @endif

    <!-- Tab Navigation -->
    <div class="tab-navigation no-print">
        <button class="tab-btn active" data-tab="stock-levels">
            <i class="bi bi-bar-chart-line"></i> Stock Levels
        </button>
        <button class="tab-btn" data-tab="by-warehouse">
            <i class="bi bi-building"></i> By Warehouse
        </button>
        <button class="tab-btn" data-tab="movements">
            <i class="bi bi-arrow-left-right"></i> Recent Movements
        </button>
    </div>

    <!-- Tab: Stock Levels -->
    <div class="tab-content active" id="stock-levels">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Note:</strong> This overview shows aggregated stock across all warehouses.
            For detailed warehouse-specific inventory, visit
            <a href="{{ route('admin.reports.inventory.stock-levels') }}" class="alert-link">Stock Levels Report</a> or
            <a href="{{ route('admin.inventory.index') }}" class="alert-link">Inventory Management</a>.
        </div>

        <!-- ✅ NEW: Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        <h3 class="mt-2">{{ number_format($stats['in_stock']) }}</h3>
                        <p class="text-muted mb-0">In Stock Items</p>
                        <small class="text-success">{{ $stats['in_stock_percentage'] }}% of inventory</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-1"></i>
                        <h3 class="mt-2">{{ number_format($stats['low_stock']) }}</h3>
                        <p class="text-muted mb-0">Low Stock Alerts</p>
                        <small class="text-warning">{{ $stats['low_stock_percentage'] }}% needs restock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle-fill text-danger fs-1"></i>
                        <h3 class="mt-2">{{ number_format($stats['out_of_stock']) }}</h3>
                        <p class="text-muted mb-0">Out of Stock</p>
                        <small class="text-danger">{{ $stats['out_of_stock_percentage'] }}% unavailable</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <a href="{{ route('admin.reports.inventory.stock-levels') }}" class="card text-decoration-none hover-shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-bar-chart-line text-brand fs-1 mb-3"></i>
                        <h5>View Detailed Stock Levels</h5>
                        <p class="text-muted small mb-0">Complete inventory with warehouse breakdown</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3 d-none">
                <a href="{{ route('admin.inventory.low-stock') }}" class="card text-decoration-none hover-shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
                        <h5>Manage Low Stock</h5>
                        <p class="text-muted small mb-0">Items that need restocking soon</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('admin.inventory.adjust') }}" class="card text-decoration-none hover-shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-plus-circle text-success fs-1 mb-3"></i>
                        <h5>Adjust Stock</h5>
                        <p class="text-muted small mb-0">Add, reduce, or set stock quantities</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Tab: By Warehouse -->
    <div class="tab-content" id="by-warehouse">
        <div class="row">
            @foreach($warehouses as $warehouse)
            @php
                $warehouseStock = \App\Models\ProductWarehouseStock::where('warehouse_id', $warehouse->id)->sum('quantity');
                $warehouseProducts = \App\Models\ProductWarehouseStock::where('warehouse_id', $warehouse->id)
                    ->where(function($q) {
                        $q->whereNotNull('variant_id')
                          ->orWhereHas('product', function($pq) {
                              $pq->where('has_variants', false);
                          });
                    })
                    ->count();
                $warehouseLowStock = \App\Models\ProductWarehouseStock::where('warehouse_id', $warehouse->id)
                    ->where('quantity', '>', 0)
                    ->where(function($q) {
                        $q->whereHas('product', function($pq) {
                            $pq->whereColumn('product_warehouse_stock.quantity', '<=', 'products.low_stock_threshold');
                        })->orWhereHas('variant', function($vq) {
                            $vq->whereColumn('product_warehouse_stock.quantity', '<=', 'product_variants.low_stock_threshold');
                        });
                    })
                    ->count();
            @endphp
            <div class="col-md-6 mb-4">
                <div class="category-breakdown">
                    <div class="d-flex align-items-center mb-3">
                        <div class="category-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $warehouse->name }}</h5>
                            <small class="text-muted">
                                {{ $warehouse->city }}, {{ $warehouse->state }}
                            </small>
                        </div>
                        @if($warehouse->is_default)
                        <span class="badge bg-brand">Default</span>
                        @endif
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="fw-bold text-brand fs-4">{{ number_format($warehouseStock) }}</div>
                            <small class="text-muted">Total Units</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success fs-4">{{ number_format($warehouseProducts) }}</div>
                            <small class="text-muted">Products</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning fs-4">{{ $warehouseLowStock }}</div>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>

                    <a href="{{ route('admin.inventory.index') }}?warehouse_id={{ $warehouse->id }}"
                       class="btn btn-outline-brand btn-sm w-100">
                        <i class="bi bi-eye"></i> View Warehouse Details
                    </a>
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
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        const tabId = this.dataset.tab;
        document.getElementById(tabId).classList.add('active');
    });
});

// Movement Trends Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('movementTrendsChart');
    if (ctx) {
        // TODO: Replace with actual data from controller
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
                    legend: { position: 'top' },
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
