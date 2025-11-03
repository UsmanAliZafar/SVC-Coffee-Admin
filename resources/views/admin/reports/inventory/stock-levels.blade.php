{{-- resources/views/admin/reports/inventory/stock-levels.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Stock Levels - Coffee Admin')

@push('styles')
<style>
    .stock-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .stock-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .stock-metric {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .stock-metric .value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .stock-metric .label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .stock-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .stock-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .stock-card.critical {
        border-left-color: #dc3545;
        background: #fff5f5;
    }

    .stock-card.warning {
        border-left-color: #ffc107;
        background: #fffef5;
    }

    .stock-card.good {
        border-left-color: #28a745;
        background: #f8fff9;
    }

    .product-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .product-image-box {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
        border: 3px solid #e9ecef;
        margin-right: 15px;
    }

    .product-info-section {
        flex-grow: 1;
    }

    .product-name-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 5px;
    }

    .product-sku-code {
        font-size: 0.875rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
    }

    .stock-gauge {
        position: relative;
        height: 120px;
        margin: 20px 0;
    }

    .gauge-background {
        width: 100%;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .gauge-fill {
        height: 100%;
        transition: width 1s ease-out;
        position: relative;
        overflow: hidden;
    }

    .gauge-fill.critical {
        background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
    }

    .gauge-fill.warning {
        background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%);
    }

    .gauge-fill.good {
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    }

    .gauge-markers {
        position: absolute;
        bottom: 30px;
        left: 0;
        right: 0;
        display: flex;
        justify-content: space-between;
        padding: 0 10px;
    }

    .gauge-marker {
        text-align: center;
    }

    .gauge-marker .line {
        width: 2px;
        height: 15px;
        background: #6c757d;
        margin: 0 auto 5px;
    }

    .gauge-marker .label {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .stock-numbers {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-top: 20px;
    }

    .stock-number-box {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .stock-number-box .number {
        font-size: 1.8rem;
        font-weight: bold;
        color: #5B914C;
        display: block;
    }

    .stock-number-box .label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-top: 5px;
    }

    .reorder-section {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
    }

    .reorder-point {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .reorder-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: pulse 2s ease-in-out infinite;
    }

    .reorder-indicator.active {
        background: #dc3545;
    }

    .reorder-indicator.inactive {
        background: #28a745;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .filter-toolbar {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.15);
    }

    .stat-box .icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 10px;
    }

    .stat-box.critical .icon {
        background: #dc3545;
        color: white;
    }

    .stat-box.warning .icon {
        background: #ffc107;
        color: white;
    }

    .stat-box.good .icon {
        background: #28a745;
        color: white;
    }

    .stat-box .value {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .stat-box .label {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn-restock {
        flex: 1;
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-restock:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
        color: white;
    }

    .btn-history {
        flex: 1;
        background: white;
        color: #5B914C;
        border: 2px solid #5B914C;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-history:hover {
        background: #5B914C;
        color: white;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-dot {
        position: absolute;
        left: -24px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #5B914C;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #5B914C;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 8px;
    }

    .sort-dropdown {
        padding: 8px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        font-weight: 600;
        cursor: pointer;
    }

    @media print {
        .no-print { display: none !important; }
        .stock-card { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .stock-numbers { grid-template-columns: 1fr; }
        .action-buttons { flex-direction: column; }
    }
</style>
@endpush

@section('content')
<div class="stock-levels-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-bar-chart-line text-brand"></i> Stock Levels
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Stock Levels</li>
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

    <!-- Stock Header Summary -->
    <div class="stock-header">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="stock-metric">
                    <i class="bi bi-boxes fs-1"></i>
                    <div class="value">{{ number_format($total_products) }}</div>
                    <div class="label">Total Products</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stock-metric">
                    <i class="bi bi-stack fs-1"></i>
                    <div class="value">{{ number_format($total_stock) }}</div>
                    <div class="label">Total Units</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stock-metric">
                    <i class="bi bi-cash-stack fs-1"></i>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($total_value, 2) }}</div>
                    <div class="label">Stock Value</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stock-metric">
                    <i class="bi bi-graph-up fs-1"></i>
                    <div class="value">{{ store_currency_symbol() }}{{ number_format($average_value, 2) }}</div>
                    <div class="label">Avg Value/Product</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <div class="stat-box good">
            <div class="icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="value">{{ number_format($in_stock_count) }}</div>
            <div class="label">In Stock</div>
        </div>
        <div class="stat-box warning">
            <div class="icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="value">{{ number_format($low_stock_count) }}</div>
            <div class="label">Low Stock</div>
        </div>
        <div class="stat-box critical">
            <div class="icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="value">{{ number_format($out_of_stock_count) }}</div>
            <div class="label">Out of Stock</div>
        </div>
        <div class="stat-box" style="border-left: 4px solid #17a2b8;">
            <div class="icon" style="background: #17a2b8; color: white;">
                <i class="bi bi-arrow-repeat"></i>
            </div>
            <div class="value">{{ number_format($reorder_needed) }}</div>
            <div class="label">Need Reorder</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="filter-toolbar no-print">
        <form method="GET" action="{{ route('admin.reports.inventory.stock-levels') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel"></i> Status Filter
                    </label>
                    <select class="form-select" name="status">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Products</option>
                        <option value="critical" {{ $status == 'critical' ? 'selected' : '' }}>Critical (Out of Stock)</option>
                        <option value="low" {{ $status == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="good" {{ $status == 'good' ? 'selected' : '' }}>Good Stock</option>
                        <option value="reorder" {{ $status == 'reorder' ? 'selected' : '' }}>Need Reorder</option>
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
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <i class="bi bi-info-circle"></i>
                    Showing <strong>{{ $products->count() }}</strong> products
                    @if($status != 'all')
                    | Filter: <strong class="text-capitalize">{{ str_replace('_', ' ', $status) }}</strong>
                    @endif
                </div>
                <div>
                    <label class="small me-2">Sort by:</label>
                    <select class="sort-dropdown" name="sort" onchange="this.form.submit()">
                        <option value="stock_asc" {{ request('sort') == 'stock_asc' ? 'selected' : '' }}>Stock: Low to High</option>
                        <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                        <option value="value_desc" {{ request('sort') == 'value_desc' ? 'selected' : '' }}>Value: High to Low</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Stock Level Cards -->
    @if($products->count() > 0)
    <div class="row">
        @foreach($products as $product)
        @php
            // Calculate stock percentage
            $max_stock = $product->low_stock_threshold * 3;
            $stock_percentage = $max_stock > 0 ? min(($product->stock_quantity / $max_stock) * 100, 100) : 0;

            // Determine status
            if ($product->stock_quantity <= 0) {
                $status_class = 'critical';
                $status_text = 'Out of Stock';
                $gauge_class = 'critical';
            } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
                $status_class = 'warning';
                $status_text = 'Low Stock';
                $gauge_class = 'warning';
            } else {
                $status_class = 'good';
                $status_text = 'In Stock';
                $gauge_class = 'good';
            }

            // Calculate stock value
            $stock_value = $product->stock_quantity * $product->price;

            // Check if reorder needed
            $needs_reorder = $product->stock_quantity <= $product->low_stock_threshold;
        @endphp
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="stock-card {{ $status_class }}">
                <!-- Product Header -->
                <div class="product-header">
                    @if($product->main_image)
                    <img src="{{ asset('storage/' . $product->main_image) }}"
                         alt="{{ $product->name }}"
                         class="product-image-box"
                         onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                    @else
                    <div class="product-image-box bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-box fs-1 text-muted"></i>
                    </div>
                    @endif

                    <div class="product-info-section">
                        <div class="product-name-title">{{ $product->name }}</div>
                        <div class="product-sku-code">SKU: {{ $product->sku }}</div>
                        @if($product->category)
                        <span class="badge bg-light text-dark mt-1">
                            <i class="bi bi-tag"></i> {{ $product->category->title }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Stock Gauge -->
                <div class="stock-gauge">
                    <div class="gauge-background">
                        <div class="gauge-fill {{ $gauge_class }}" style="width: {{ $stock_percentage }}%"></div>
                    </div>
                    <div class="gauge-markers">
                        <div class="gauge-marker">
                            <div class="line"></div>
                            <div class="label">0</div>
                        </div>
                        <div class="gauge-marker">
                            <div class="line"></div>
                            <div class="label">{{ $product->low_stock_threshold }}</div>
                        </div>
                        <div class="gauge-marker">
                            <div class="line"></div>
                            <div class="label">{{ $max_stock }}</div>
                        </div>
                    </div>
                </div>

                <!-- Stock Numbers -->
                <div class="stock-numbers">
                    <div class="stock-number-box">
                        <span class="number">{{ number_format($product->stock_quantity) }}</span>
                        <span class="label">Current</span>
                    </div>
                    <div class="stock-number-box">
                        <span class="number">{{ number_format($product->low_stock_threshold) }}</span>
                        <span class="label">Threshold</span>
                    </div>
                    <div class="stock-number-box">
                        <span class="number">{{ store_currency_symbol() }}{{ number_format($stock_value, 2) }}</span>
                        <span class="label">Value</span>
                    </div>
                </div>

                <!-- Reorder Section -->
                @if($needs_reorder)
                <div class="reorder-section">
                    <div class="reorder-point">
                        <div>
                            <div class="fw-bold text-danger">
                                <i class="bi bi-exclamation-circle"></i> Reorder Required
                            </div>
                            <small>Below minimum threshold</small>
                        </div>
                        <div class="reorder-indicator active"></div>
                    </div>
                </div>
                @else
                <div class="reorder-section" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                    <div class="reorder-point">
                        <div>
                            <div class="fw-bold text-success">
                                <i class="bi bi-check-circle"></i> Stock Level Good
                            </div>
                            <small>No action required</small>
                        </div>
                        <div class="reorder-indicator inactive"></div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn-restock" onclick="openRestockModal('{{ $product->id }}', '{{ $product->name }}')">
                        <i class="bi bi-plus-circle"></i> Restock
                    </button>
                    <button class="btn-history" onclick="openHistoryModal('{{ $product->id }}')">
                        <i class="bi bi-clock-history"></i> History
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-inbox fs-1 mb-3"></i>
        <h5>No Products Found</h5>
        <p class="mb-0">No products match your current filters.</p>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-bar-chart text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Visual Stock Tracking</h6>
                <p class="text-muted small mb-0">
                    Easy-to-read gauges show stock levels at a glance with color-coded indicators.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-bell text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Automated Alerts</h6>
                <p class="text-muted small mb-0">
                    Get notified when products reach reorder points or go out of stock.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up-arrow text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Smart Reordering</h6>
                <p class="text-muted small mb-0">
                    Threshold-based system helps maintain optimal inventory levels.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-brand text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Restock Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="restockForm">
                    <input type="hidden" id="restock_product_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product</label>
                        <input type="text" class="form-control" id="restock_product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity to Add</label>
                        <input type="number" class="form-control" id="restock_quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea class="form-control" id="restock_notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-brand" onclick="submitRestock()">
                    <i class="bi bi-check-circle"></i> Confirm Restock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-brand text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history"></i> Stock History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong>Restocked</strong>
                                <span class="text-muted">Nov 1, 2025</span>
                            </div>
                            <div class="text-success">+50 units</div>
                            <small class="text-muted">New stock received from supplier</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong>Sale</strong>
                                <span class="text-muted">Oct 28, 2025</span>
                            </div>
                            <div class="text-danger">-15 units</div>
                            <small class="text-muted">Order #12345</small>
                        </div>
                    </div>
                    <!-- More timeline items would be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRestockModal(productId, productName) {
    document.getElementById('restock_product_id').value = productId;
    document.getElementById('restock_product_name').value = productName;
    document.getElementById('restock_quantity').value = '';
    document.getElementById('restock_notes').value = '';

    const modal = new bootstrap.Modal(document.getElementById('restockModal'));
    modal.show();
}

function submitRestock() {
    const productId = document.getElementById('restock_product_id').value;
    const quantity = document.getElementById('restock_quantity').value;
    const notes = document.getElementById('restock_notes').value;

    if (!quantity || quantity < 1) {
        alert('Please enter a valid quantity');
        return;
    }

    // TODO: Implement AJAX call to restock endpoint
    console.log('Restock:', { productId, quantity, notes });
    alert('Restock functionality would be implemented here');

    bootstrap.Modal.getInstance(document.getElementById('restockModal')).hide();
}

function openHistoryModal(productId) {
    // TODO: Load stock history for this product
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    modal.show();
}

// Animate gauges on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.gauge-fill').forEach(fill => {
        const width = fill.style.width;
        fill.style.width = '0%';
        setTimeout(() => {
            fill.style.width = width;
        }, 100);
    });
});
</script>
@endpush
