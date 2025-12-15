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
.variant-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }

    .warehouse-indicator {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 5px;
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
            <button type="button" class="btn btn-brand dropdown-toggle d-none" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="exportData('pdf')"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportData('excel')"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportData('csv')"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- ✅ UPDATED: Filter Toolbar with DataTables -->
    <div class="filter-toolbar no-print">
        <div class="row align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-building"></i> Warehouse
                </label>
                <select class="form-select" id="warehouse_filter">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-grid-3x3-gap"></i> Category
                </label>
                <select class="form-select" id="category_filter">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-funnel"></i> Stock Status
                </label>
                <select class="form-select" id="status_filter">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search"></i> Search
                </label>
                <input type="text" class="form-control" id="search_input" placeholder="Product name or SKU...">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing <strong id="record_count">0</strong> items
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                <i class="bi bi-x-circle"></i> Clear Filters
            </button>
        </div>
    </div>

    <!-- ✅ NEW: DataTable View -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockLevelsTable" class="table table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-center">Available</th>
                            <th class="text-center">Reserved</th>
                            <th class="text-center">Threshold</th>
                            <th class="text-end">Stock Value</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-bar-chart text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Real-Time Data</h6>
                <p class="text-muted small mb-0">
                    Stock levels reflect current inventory across all warehouses and variants.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-layers text-info fs-3 mb-2"></i>
                <h6 class="fw-bold">Variant Support</h6>
                <p class="text-muted small mb-0">
                    Track individual variant stock levels separately from parent products.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-building text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Multi-Warehouse</h6>
                <p class="text-muted small mb-0">
                    View aggregated stock or filter by specific warehouse locations.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let stockTable;

$(document).ready(function() {
    // ✅ Initialize DataTable
    stockTable = $('#stockLevelsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.reports.inventory.stock-levels') }}",
            data: function(d) {
                d.warehouse_id = $('#warehouse_filter').val();
                d.category_id = $('#category_filter').val();
                d.stock_status = $('#status_filter').val();
                d.search = $('#search_input').val();
            }
        },
        columns: [
            {
                data: 'product_info',
                name: 'product_info',
                orderable: false,
                searchable: false
            },
            {
                data: 'category',
                name: 'category',
                orderable: false
            },
            {
                data: 'current_stock',
                name: 'current_stock',
                className: 'text-center'
            },
            {
                data: 'available',
                name: 'available',
                className: 'text-center'
            },
            {
                data: 'reserved',
                name: 'reserved',
                className: 'text-center'
            },
            {
                data: 'threshold',
                name: 'threshold',
                className: 'text-center'
            },
            {
                data: 'stock_value',
                name: 'stock_value',
                className: 'text-end'
            },
            {
                data: 'status_badge',
                name: 'status_badge',
                className: 'text-center',
                orderable: false
            }
        ],
        order: [[2, 'desc']], // Sort by current stock descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            emptyTable: "No products found",
            info: "Showing _START_ to _END_ of _TOTAL_ items",
            infoEmpty: "Showing 0 to 0 of 0 items",
            infoFiltered: "(filtered from _MAX_ total items)",
            lengthMenu: "Show _MENU_ items",
            loadingRecords: "Loading...",
            processing: '<div class="spinner-border text-brand" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            zeroRecords: "No matching records found"
        },
        drawCallback: function(settings) {
            $('#record_count').text(settings._iRecordsDisplay);
        }
    });

    // ✅ Filter change handlers
    $('#warehouse_filter, #category_filter, #status_filter').on('change', function() {
        stockTable.ajax.reload();
    });

    // ✅ Search with debounce
    let searchTimeout;
    $('#search_input').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            stockTable.ajax.reload();
        }, 500);
    });
});

// ✅ Clear all filters
function clearFilters() {
    $('#warehouse_filter').val('');
    $('#category_filter').val('');
    $('#status_filter').val('');
    $('#search_input').val('');
    stockTable.ajax.reload();
}

// ✅ Export functionality
function exportData(format) {
    const warehouseId = $('#warehouse_filter').val();
    const categoryId = $('#category_filter').val();
    const stockStatus = $('#status_filter').val();
    const search = $('#search_input').val();

    let url = "{{ route('admin.reports.inventory.stock-levels') }}";
    url += `?export=${format}`;

    if (warehouseId) url += `&warehouse_id=${warehouseId}`;
    if (categoryId) url += `&category_id=${categoryId}`;
    if (stockStatus) url += `&stock_status=${stockStatus}`;
    if (search) url += `&search=${search}`;

    window.open(url, '_blank');
}
</script>
@endpush
