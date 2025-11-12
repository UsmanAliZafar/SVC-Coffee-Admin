@extends('admin.layouts.app')
@section('title', 'Low Stock Products')

@push('styles')
{{-- Keep your existing styles --}}
<style>
    .alert-banner {
        background: linear-gradient(135deg, #fff3cd 0%, #ffc107 100%);
        border: 2px solid #ffc107;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 16px rgba(255, 193, 7, 0.2);
    }

    .alert-icon {
        font-size: 3.5rem;
        color: #856404;
        animation: warning-pulse 2s infinite;
    }

    @keyframes warning-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .stat-card {
        background: linear-gradient(135deg, #fff9e6 0%, #ffe8a1 100%);
        padding: 24px;
        border-radius: 12px;
        text-align: center;
        border: 2px solid #ffc107;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .stat-card:hover::before {
        left: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
    }

    .stat-label {
        font-size: 0.875rem;
        color: #856404;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #856404;
        line-height: 1;
    }

    .stat-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 2rem;
        opacity: 0.2;
    }

    .filter-section {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .btn-filter {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-filter:hover {
        background: linear-gradient(135deg, #4a7a3d 0%, #3d6433 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
    }

    .warehouse-tabs {
        border-bottom: 3px solid #ffc107;
        margin-bottom: 24px;
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 0;
    }

    .warehouse-tab {
        padding: 12px 24px;
        border: none;
        background: transparent;
        color: #666;
        cursor: pointer;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
        margin-bottom: -3px;
        white-space: nowrap;
        font-weight: 500;
    }

    .warehouse-tab:hover {
        color: #856404;
        background: rgba(255, 193, 7, 0.1);
    }

    .warehouse-tab.active {
        color: #856404;
        font-weight: 700;
        border-bottom-color: #ffc107;
        background: rgba(255, 193, 7, 0.15);
    }

    .stock-level-bar {
        height: 24px;
        background: #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    .stock-level-fill {
        height: 100%;
        background: linear-gradient(90deg, #dc3545 0%, #fd7e14 50%, #ffc107 100%);
        transition: width 0.5s ease;
        position: relative;
        overflow: hidden;
    }

    .stock-level-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .priority-badge {
        position: relative;
        padding: 6px 12px 6px 28px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .priority-badge::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        animation: priority-pulse 2s infinite;
    }

    @keyframes priority-pulse {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 0 0 0 currentColor;
        }
        50% {
            opacity: 0.7;
            box-shadow: 0 0 0 6px transparent;
        }
    }

    .priority-critical {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .priority-critical::before {
        background-color: white;
    }

    .priority-high {
        background: linear-gradient(135deg, #fd7e14, #e8590c);
        color: white;
    }

    .priority-high::before {
        background-color: white;
    }

    .priority-medium {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #000;
    }

    .priority-medium::before {
        background-color: #000;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: center;
    }

    .action-buttons .btn {
        transition: all 0.3s;
    }

    .action-buttons .btn:hover {
        transform: translateY(-2px);
    }

    .table-hover tbody tr {
        transition: all 0.3s;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.1) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 5rem;
        color: #28a745;
        opacity: 0.3;
        margin-bottom: 20px;
    }

    .bulk-action-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 15px 30px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        display: none;
        align-items: center;
        gap: 15px;
        z-index: 1000;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from { bottom: -100px; opacity: 0; }
        to { bottom: 20px; opacity: 1; }
    }

    .bulk-action-bar.show {
        display: flex;
    }

    .stock-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.85rem;
        padding: 4px 8px;
        border-radius: 12px;
        background: #f8f9fa;
    }

    .stock-trend.trending-down {
        color: #dc3545;
    }

    .stock-trend.trending-up {
        color: #28a745;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }

    .form-select:focus, .form-control:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .badge {
        padding: 6px 12px;
        font-weight: 600;
        border-radius: 6px;
    }
</style>
<style>
    .badge.bg-info {
        background-color: #0dcaf0 !important;
        color: #000 !important;
    }

    .variant-indicator {
        padding-left: 10px;
        border-left: 3px solid #0dcaf0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Keep your existing header, banner, and statistics --}}
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-2">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i> Low Stock Alert
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Low Stock</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <button type="button" class="btn btn-warning" onclick="bulkRestockModal()">
                <i class="bi bi-box-seam"></i> Bulk Restock
            </button>
            @endif
            <button type="button" class="btn btn-outline-info" onclick="exportLowStock()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner">
        <div class="row align-items-center">
            <div class="col-auto">
                <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
            </div>
            <div class="col">
                <h4 class="mb-2" style="color: #856404; font-weight: 700;">
                    ⚠️ Low Stock Alert
                </h4>
                <p class="mb-0" style="color: #856404; font-size: 1.05rem;">
                    The following products and variants have stock levels at or below their threshold.
                    <strong>Take action now</strong> to prevent stockouts and maintain service levels.
                </p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-exclamation-triangle stat-icon"></i>
                <div class="stat-label">Low Stock Items</div>
                <div class="stat-value" id="statLowStockCount">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-x-octagon stat-icon text-danger"></i>
                <div class="stat-label">Critical (≤5 units)</div>
                <div class="stat-value text-danger" id="statCriticalCount">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-currency-dollar stat-icon"></i>
                <div class="stat-label">Value at Risk</div>
                <div class="stat-value" id="statValueAtRisk">{{ store_currency_symbol() }}0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-clock-history stat-icon"></i>
                <div class="stat-label">Avg Lead Time</div>
                <div class="stat-value" id="statAvgDaysRestock">—</div>
            </div>
        </div>
    </div>

    <!-- Warehouse Tabs -->
    <div class="warehouse-tabs">
        <button class="warehouse-tab active" data-warehouse="">
            <i class="bi bi-grid"></i> All Warehouses
        </button>
        @foreach($warehouses as $warehouse)
        <button class="warehouse-tab" data-warehouse="{{ $warehouse->id }}">
            <i class="bi bi-building"></i> {{ $warehouse->name }}
            @if($warehouse->is_default)
                <span class="badge bg-primary ms-1">Default</span>
            @endif
        </button>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="bi bi-filter"></i> Priority Level
                </label>
                <select id="filterPriority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="critical">🔴 Critical (≤5)</option>
                    <option value="high">🟠 High (6-10)</option>
                    <option value="medium">🟡 Medium (>10)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="bi bi-tag"></i> Category
                </label>
                <select id="filterCategory" class="form-select">
                    <option value="">All Categories</option>
                    <option value="coffee-machines">Coffee Machines</option>
                    <option value="coffee-beans">Coffee Beans</option>
                    <option value="spare-parts">Spare Parts</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="bi bi-search"></i> Search
                </label>
                <input type="text" id="filterSearch" class="form-control"
                       placeholder="Product, variant, or SKU...">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="bi bi-arrow-down-up"></i> Sort By
                </label>
                <select id="filterSort" class="form-select">
                    <option value="priority">Priority</option>
                    <option value="stock_asc">Stock (Low-High)</option>
                    <option value="stock_desc">Stock (High-Low)</option>
                    <option value="name">Name (A-Z)</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-filter flex-grow-1" onclick="applyFilters()">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                        <i class="bi bi-x-circle"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="lowStockTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Product / Variant</th>
                            <th width="10%">Current Stock</th>
                            <th width="10%">Threshold</th>
                            <th width="15%">Stock Level</th>
                            <th width="12%">Priority</th>
                            <th width="15%">Warehouse</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    <div class="bulk-action-bar" id="bulkActionBar">
        <span class="fw-bold"><span id="selectedCount">0</span> items selected</span>
        <button class="btn btn-sm btn-warning" onclick="bulkRestockModal()">
            <i class="bi bi-box-seam"></i> Restock Selected
        </button>
        <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
            <i class="bi bi-x"></i> Clear
        </button>
    </div>
</div>

{{-- Keep your existing modals --}}
<!-- Quick Restock Modal -->
<div class="modal fade" id="quickRestockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107, #ff9800); color: #000;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-box-seam"></i> Quick Restock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickRestockForm">
                @csrf
                <input type="hidden" name="product_id" id="restockProductId">
                <input type="hidden" name="variant_id" id="restockVariantId">
                <input type="hidden" name="warehouse_id" id="restockWarehouseId">
                <input type="hidden" name="action_type" value="add">

                <div class="modal-body">
                    <div class="alert alert-warning border-0 shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong id="restockProductName" style="font-size: 1.1rem;"></strong>
                                <div id="restockVariantName" class="text-muted small" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Current Stock</small>
                                <strong class="text-danger" style="font-size: 1.2rem;" id="restockCurrentStock">0</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Threshold</small>
                                <strong style="font-size: 1.2rem;" id="restockThreshold">0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Quantity to Add <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="quantity" id="restockQuantity"
                               class="form-control form-control-lg" min="1" required>
                        <small class="text-muted">
                            💡 Suggested: <strong class="text-success" id="restockSuggested">0</strong> units
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <select name="reason" class="form-select">
                            <option value="Restock - Low Stock Alert">Low Stock Alert</option>
                            <option value="Restock - Purchase Order">Purchase Order</option>
                            <option value="Restock - Emergency">Emergency Restock</option>
                            <option value="Restock - Supplier Delivery">Supplier Delivery</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="PO number, supplier, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-check-circle"></i> Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Restock Modal -->
<div class="modal fade" id="bulkRestockModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107, #ff9800); color: #000;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-box-seam"></i> Bulk Restock Selected Items
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkRestockForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info border-0 shadow-sm">
                        <i class="bi bi-info-circle me-2"></i>
                        You have selected <strong id="selectedItemsCount">0</strong> items for restocking.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-gear"></i> Restock Strategy
                        </label>
                        <select id="bulkRestockStrategy" class="form-select form-select-lg">
                            <option value="to_threshold">🎯 Restock to Threshold Level</option>
                            <option value="double_threshold">📈 Restock to 2x Threshold (Recommended)</option>
                            <option value="triple_threshold">🚀 Restock to 3x Threshold</option>
                            <option value="custom">⚙️ Custom Quantity for All</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customQuantityField" style="display: none;">
                        <label class="form-label fw-bold">Quantity per Item</label>
                        <input type="number" id="bulkCustomQuantity" class="form-control form-control-lg" min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <select id="bulkReason" class="form-select">
                            <option value="Bulk Restock - Low Stock Alert">Bulk Low Stock Alert</option>
                            <option value="Bulk Restock - Purchase Order">Bulk Purchase Order</option>
                            <option value="Bulk Restock - Emergency">Bulk Emergency Restock</option>
                        </select>
                    </div>

                    <div id="bulkRestockPreview" class="mb-3" style="display: none;">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-eye"></i> Restock Preview
                        </h6>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Product / Variant</th>
                                        <th width="15%">Current</th>
                                        <th width="15%">Add</th>
                                        <th width="15%">New Total</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkRestockPreviewBody"></tbody>
                            </table>
                        </div>
                        <div class="alert alert-success mt-3 mb-0">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Total Items:</strong> <span id="previewTotalItems">0</span>
                                </div>
                                <div class="col-6">
                                    <strong>Total Quantity:</strong> <span id="previewTotalQuantity">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-info" onclick="previewBulkRestock()">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-warning fw-bold" id="bulkRestockSubmit" disabled>
                        <i class="bi bi-check-circle"></i> Restock All (<span id="bulkRestockCount">0</span>)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let lowStockTable;
let currentWarehouse = '';
let selectedItems = [];

$(document).ready(function() {
    initializeDataTable();
    loadStatistics();

    // Warehouse tab clicks
    $('.warehouse-tab').on('click', function() {
        $('.warehouse-tab').removeClass('active');
        $(this).addClass('active');
        currentWarehouse = $(this).data('warehouse');
        refreshData();
    });

    // Bulk restock strategy change
    $('#bulkRestockStrategy').on('change', function() {
        $('#customQuantityField').toggle($(this).val() === 'custom');
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.item-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedItems();
    });

    // Filter changes
    $('#filterPriority, #filterCategory, #filterSort').on('change', function() {
        applyFilters();
    });

    // Search with debounce
    let searchTimeout;
    $('#filterSearch').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    });
});

function initializeDataTable() {
    lowStockTable = $('#lowStockTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.inventory.low-stock.data") }}',
            data: function(d) {
                return {
                    warehouse_id: currentWarehouse,
                    priority: $('#filterPriority').val(),
                    category: $('#filterCategory').val(),
                    search: $('#filterSearch').val(),
                    sort: $('#filterSort').val(),
                    start: d.start,
                    length: d.length,
                    draw: d.draw
                };
            }
        },
        columns: [
        {
            data: 'checkbox',
            orderable: false,
            searchable: false
        },
        {
            data: 'product_info',
            name: 'name',
            orderable: true
        },
        {
            data: 'total_stock',
            name: 'stock_quantity',
            orderable: true
        },
        {
            data: 'threshold',
            name: 'low_stock_threshold',
            orderable: true
        },
        {
            data: 'stock_level',
            orderable: false,
            searchable: false
        },
        {
            data: 'priority',
            orderable: false,
            searchable: false
        },
        {
            data: 'warehouse_name',
            orderable: false,
            searchable: false
        },
        {
            data: 'actions',
            orderable: false,
            searchable: false
        }
    ],
        order: [[2, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: `
                <div class="empty-state">
                    <i class="bi bi-check-circle-fill"></i>
                    <h5>All Products Well Stocked! 🎉</h5>
                    <p>No products or variants are currently below their stock threshold.</p>
                </div>
            `,
            zeroRecords: `
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h5>No Matching Items</h5>
                    <p>Try adjusting your filters.</p>
                </div>
            `
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
            updateSelectedItems();
        },
        error: function(xhr, error, thrown) {
            console.error('DataTable Error:', error, thrown);
        }
    });
}

function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.inventory.low-stock.statistics") }}',
        data: { warehouse_id: currentWarehouse },
        success: function(stats) {
            $('#statLowStockCount').text(stats.low_stock || 0);
            $('#statCriticalCount').text(stats.critical_count || 0);
            $('#statValueAtRisk').text('{{ store_currency_symbol() }}' + (stats.value_at_risk || 0).toLocaleString());
            $('#statAvgDaysRestock').text(stats.avg_days_restock || '—');
        },
        error: function(xhr) {
            console.error('Failed to load statistics:', xhr);
        }
    });
}

function applyFilters() {
    lowStockTable.ajax.reload();
    loadStatistics();
}

function resetFilters() {
    $('#filterPriority, #filterCategory, #filterSearch, #filterSort').val('');
    applyFilters();
}

function refreshData() {
    lowStockTable.ajax.reload();
    loadStatistics();
}

$(document).on('change', '.item-checkbox', function() {
    updateSelectedItems();
});

function updateSelectedItems() {
    selectedItems = [];
    $('.item-checkbox:checked').each(function() {
        selectedItems.push({
            id: $(this).data('id'),
            product_id: $(this).data('product-id'),
            variant_id: $(this).data('variant-id') || null,
            warehouse_id: $(this).data('warehouse-id'),
            current: parseInt($(this).data('current')),
            threshold: parseInt($(this).data('threshold')),
            name: $(this).data('name'),
            variant_name: $(this).data('variant-name') || null
        });
    });

    $('#selectedItemsCount, #selectedCount, #bulkRestockCount').text(selectedItems.length);

    if (selectedItems.length > 0) {
        $('#bulkActionBar').addClass('show');
    } else {
        $('#bulkActionBar').removeClass('show');
    }
}

function quickRestock(productId, variantId, warehouseId, productName, variantName, currentStock, threshold) {
    $('#restockProductId').val(productId);
    $('#restockVariantId').val(variantId || '');
    $('#restockWarehouseId').val(warehouseId);
    $('#restockProductName').text(productName);

    if (variantName) {
        $('#restockVariantName').text('Variant: ' + variantName).show();
    } else {
        $('#restockVariantName').hide();
    }

    $('#restockCurrentStock').text(currentStock);
    $('#restockThreshold').text(threshold);

    let suggested = Math.max(threshold * 2 - currentStock, threshold);
    $('#restockSuggested').text(suggested);
    $('#restockQuantity').val(suggested);

    $('#quickRestockModal').modal('show');
}

$('#quickRestockForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route("admin.inventory.adjust.store") }}',
        type: 'POST',
        data: $(this).serialize(),
        beforeSend: function() {
            $('#quickRestockModal').modal('hide');
            Swal.fire({
                title: 'Processing...',
                text: 'Adding stock',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Stock added successfully',
                    confirmButtonColor: '#5B914C',
                    timer: 2000
                });
                $('#quickRestockForm')[0].reset();
                refreshData();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to add stock'
            });
        }
    });
});

function bulkRestockModal() {
    if (selectedItems.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Items Selected',
            text: 'Please select items to restock',
            confirmButtonColor: '#5B914C'
        });
        return;
    }
    $('#bulkRestockModal').modal('show');
}

function previewBulkRestock() {
    const strategy = $('#bulkRestockStrategy').val();
    const customQty = parseInt($('#bulkCustomQuantity').val()) || 0;

    if (strategy === 'custom' && customQty <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Quantity',
            text: 'Please enter a valid quantity',
            confirmButtonColor: '#5B914C'
        });
        return;
    }

    let html = '';
    let totalQty = 0;

    selectedItems.forEach(item => {
        let addQty = 0;

        switch(strategy) {
            case 'to_threshold':
                addQty = Math.max(item.threshold - item.current, 0);
                break;
            case 'double_threshold':
                addQty = Math.max((item.threshold * 2) - item.current, 0);
                break;
            case 'triple_threshold':
                addQty = Math.max((item.threshold * 3) - item.current, 0);
                break;
            case 'custom':
                addQty = customQty;
                break;
        }

        totalQty += addQty;
        const newTotal = item.current + addQty;

        let displayName = item.name;
        if (item.variant_name) {
            displayName += ' - ' + item.variant_name;
        }

        html += `
            <tr>
                <td><small>${displayName}</small></td>
                <td class="text-center"><strong class="text-danger">${item.current}</strong></td>
                <td class="text-center"><strong class="text-success">+${addQty}</strong></td>
                <td class="text-center"><strong class="text-primary">${newTotal}</strong></td>
            </tr>
        `;
    });

    $('#bulkRestockPreviewBody').html(html);
    $('#previewTotalItems').text(selectedItems.length);
    $('#previewTotalQuantity').text(totalQty);
    $('#bulkRestockPreview').show();
    $('#bulkRestockSubmit').prop('disabled', false);
}

$('#bulkRestockForm').on('submit', function(e) {
    e.preventDefault();

    const strategy = $('#bulkRestockStrategy').val();
    const customQty = parseInt($('#bulkCustomQuantity').val()) || 0;
    const reason = $('#bulkReason').val();

    let requests = [];

    selectedItems.forEach(item => {
        let addQty = 0;

        switch(strategy) {
            case 'to_threshold':
                addQty = Math.max(item.threshold - item.current, 0);
                break;
            case 'double_threshold':
                addQty = Math.max((item.threshold * 2) - item.current, 0);
                break;
            case 'triple_threshold':
                addQty = Math.max((item.threshold * 3) - item.current, 0);
                break;
            case 'custom':
                addQty = customQty;
                break;
        }

        if (addQty > 0) {
            requests.push({
                product_id: item.product_id,
                variant_id: item.variant_id || '',
                warehouse_id: item.warehouse_id,
                action_type: 'add',
                quantity: addQty,
                reason: reason
            });
        }
    });

    $('#bulkRestockModal').modal('hide');

    Swal.fire({
        title: 'Bulk Restocking...',
        html: `Processing <strong>${requests.length}</strong> items`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    Promise.allSettled(requests.map(req =>
        $.ajax({
            url: '{{ route("admin.inventory.adjust.store") }}',
            type: 'POST',
            data: { ...req, _token: '{{ csrf_token() }}' }
        })
    )).then(results => {
        const success = results.filter(r => r.status === 'fulfilled').length;
        const failed = results.filter(r => r.status === 'rejected').length;

        if (failed === 0) {
            Swal.fire({
                icon: 'success',
                title: 'All Items Restocked!',
                html: `Successfully restocked <strong>${success}</strong> items`,
                confirmButtonColor: '#5B914C'
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Partially Completed',
                html: `<strong>${success}</strong> successful, <strong>${failed}</strong> failed`,
                confirmButtonColor: '#5B914C'
            });
        }

        $('#bulkRestockForm')[0].reset();
        $('#bulkRestockPreview').hide();
        $('#bulkRestockSubmit').prop('disabled', true);
        clearSelection();
        refreshData();
    });
});

function clearSelection() {
    selectedItems = [];
    $('.item-checkbox, #selectAll').prop('checked', false);
    $('#bulkActionBar').removeClass('show');
}

function viewHistory(productId, variantId) {
    let url = `{{ route('admin.inventory.movement') }}?product_id=${productId}`;
    if (variantId) {
        url += `&variant_id=${variantId}`;
    }
    window.location.href = url;
}

function exportLowStock() {
    Swal.fire({
        title: 'Exporting...',
        text: 'Preparing low stock report',
        icon: 'info',
        showConfirmButton: false,
        timer: 2000
    });

    setTimeout(() => {
        window.location.href = '{{ route("admin.inventory.movement.export") }}?stock_status=low_stock&warehouse_id=' + currentWarehouse;
    }, 2000);
}

// Auto-refresh every 5 minutes
setInterval(refreshData, 300000);
</script>
@endpush
