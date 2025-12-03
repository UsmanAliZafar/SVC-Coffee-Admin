@extends('admin.layouts.app')
{{--  products/index.blade.php --}}
@section('title', 'Products Management')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-card .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #5B914C;
    }
    .btn-filter {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-filter:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }
    .product-stats {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .stat-item {
        text-align: center;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .stat-item:hover {
        transform: translateY(-3px);
    }
    .stat-item .stat-value {
        font-size: 2rem;
        font-weight: bold;
    }
    .stat-item .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .img-thumbnail {
        border-radius: 8px;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .table td {
        vertical-align: middle;
    }
    .stock-low {
        color: #ff9800;
    }
    .stock-out {
        color: #dc3545;
    }
    .stock-in {
        color: #28a745;
    }

    .stock-badge-container {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .quick-stock-btn {
        font-size: 1rem;
        line-height: 1;
        vertical-align: middle;
    }

    .quick-stock-btn:hover i {
        color: #5B914C !important;
    }
    .view-variants-btn {
        font-size: 0.60rem;
        padding: 0.25rem 0.5rem;
        margin-left: 8px;
    }

    .view-variants-btn:hover {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }

    /* Price Container */
    .price-container {
        cursor: help;
    }

    .price-range {
        white-space: nowrap;
    }

    .default-price {
        margin-top: 2px;
        color: #6c757d;
    }

    /* Stock Badge Improvements */
    .stock-badge-container .badge {
        cursor: help;
        min-width: 80px;
        text-align: center;
    }

    .stock-badge-container .badge small {
        opacity: 0.9;
        font-weight: normal;
    }

    /* Tooltip Improvements */
    .tooltip-inner {
        max-width: 300px;
        text-align: left;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-box-seam"></i> Products Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Products</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('products.create'))
            <a href="{{ route('admin.products.create') }}" class="btn btn-filter">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
            @endif
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import
            </button>
            <a href="{{ route('admin.products.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="product-stats">
        <div class="row">
            <div class="col-md-2 stat-item" onclick="filterByStatus('all')">
                <div class="stat-value" id="totalProducts">0</div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="col-md-2 stat-item" onclick="filterByStatus('active')">
                <div class="stat-value" id="activeProducts">0</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="col-md-2 stat-item" onclick="filterByStatus('draft')">
                <div class="stat-value" id="draftProducts">0</div>
                <div class="stat-label">Draft</div>
            </div>
            <div class="col-md-2 stat-item" onclick="filterByStock('featured')">
                <div class="stat-value" id="featuredProducts">0</div>
                <div class="stat-label">Featured</div>
            </div>
            <div class="col-md-2 stat-item" onclick="filterByStock('out')">
                <div class="stat-value" id="outOfStock">0</div>
                <div class="stat-label">Out of Stock</div>
            </div>
            <div class="col-md-2 stat-item" onclick="filterByStock('low')">
                <div class="stat-value" id="lowStock">0</div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control" placeholder="Name, SKU, Barcode...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($allCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->indent }}{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
             <div class="col-md-2">
                <label class="form-label">Vendor</label>
                <select id="vendorFilter" class="form-select">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Stock Status</label>
                <select id="stockFilter" class="form-select">
                    <option value="">All</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
             <div class="col-md-1">
                <label class="form-label">Featured</label>
                <select id="featuredFilter" class="form-select">
                    <option value="">All</option>
                    <option value="1">Featured Only</option>
                    <option value="0">Non-Featured</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Show on Home</label>
                <select id="homeFilter" class="form-select">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Availability</label>
                <select id="availableFilter" class="form-select">
                    <option value="">All</option>
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100" title="Reset Filters">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Bulk Actions -->
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span><strong id="selectedCount">0</strong> products selected</span>
                    <div>
                        @if(auth('admin')->user()->hasPermission('products.update'))
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-pencil"></i> Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-action" data-action="status" href="#"><i class="bi bi-toggles"></i> Change Status</a></li>
                                <li><a class="dropdown-item bulk-action" data-action="feature" href="#"><i class="bi bi-star"></i> Toggle Featured</a></li>
                                <li><a class="dropdown-item bulk-action" data-action="homepage" href="#"><i class="bi bi-house"></i> Toggle Homepage</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item bulk-action" data-action="stock" href="#"><i class="bi bi-box"></i> Update Stock</a></li>
                            </ul>
                        </div>
                        @endif
                        @if(auth('admin')->user()->hasPermission('products.delete'))
                        <button type="button" class="btn btn-sm btn-danger me-2" id="bulkDelete">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">
                            <i class="bi bi-x"></i> Deselect All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="productsTable" class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="60">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th width="120">Price</th>
                            <th width="100">Stock</th>
                            <th width="100">Status</th>
                            <th>Badges</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Import Products from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text">
                            Required columns: Name, SKU, Price. Optional: Barcode, Category, Sale Price, Stock, Description
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Need help? <i class="fa fa-info" title="For Fast imports keep numbers low."></i></strong>
                        <a href="{{ route('admin.products.import-template') }}" class="alert-link" target="_blank">
                            <i class="bi bi-download"></i> Download CSV Template
                        </a>
                        <div class="mt-2 small">
                            The template includes sample data showing the correct format for all fields.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-filter">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Status Change Modal -->
<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Select New Status</label>
                <select id="bulkStatusSelect" class="form-select">
                    @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-filter" id="confirmBulkStatus">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Stock Update Modal -->
<div class="modal fade" id="bulkStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Action Type</label>
                    <select id="stockActionType" class="form-select">
                        <option value="set">Set Stock (Replace)</option>
                        <option value="add">Add Stock</option>
                        <option value="reduce">Reduce Stock</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" id="stockQuantity" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-filter" id="confirmBulkStock">Update Stock</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stock Management Modal -->
<!-- Quick Stock Management Modal -->
<div class="modal fade" id="quickStockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Quick Stock Management
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickStockForm">
                @csrf
                <input type="hidden" id="quickStockProductId" name="product_id">
                <div class="modal-body">
                    <!-- Product Info -->
                    <div class="mb-3">
                        <h6 id="quickStockProductName" class="text-muted mb-3"></h6>

                        <!-- Total Stock Summary -->
                        <div class="alert alert-info mb-3">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="text-muted small">Total Stock (All Warehouses)</div>
                                    <h4 class="mb-0" id="quickStockTotalStock">0</h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Selected Warehouse</div>
                                    <h4 class="mb-0" id="quickStockWarehouseCurrent">0</h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Available</div>
                                    <h4 class="mb-0 text-success" id="quickStockWarehouseAvailable">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse Selection -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-building"></i> Select Warehouse <span class="text-danger">*</span>
                        </label>
                        <select id="quickStockWarehouse" name="warehouse_id" class="form-select" required>
                            <option value="">-- Select Warehouse --</option>
                        </select>
                        <div id="warehouseStockInfo" class="mt-2 d-none">
                            <div class="small text-muted">
                                <i class="bi bi-info-circle"></i>
                                Current: <strong class="text-primary" id="selectedWarehouseStock">0</strong> units |
                                Reserved: <strong class="text-warning" id="selectedWarehouseReserved">0</strong> units |
                                Available: <strong class="text-success" id="selectedWarehouseAvailable">0</strong> units
                            </div>
                        </div>
                    </div>

                    <!-- Action Type -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-gear"></i> Action Type
                        </label>
                        <select id="quickStockAction" class="form-select" name="action_type">
                            <option value="set">Set Stock (Replace)</option>
                            <option value="add">Add Stock (+)</option>
                            <option value="reduce">Reduce Stock (-)</option>
                        </select>
                    </div>

                    <!-- Quantity Input -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-123"></i> Quantity
                        </label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(-10)">
                                <i class="bi bi-dash-lg"></i> 10
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" id="quickStockQuantity" name="quantity"
                                   class="form-control text-center" min="0" value="0" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(10)">
                                <i class="bi bi-plus-lg"></i> 10
                            </button>
                        </div>
                    </div>

                    <!-- Low Stock Threshold -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-exclamation-triangle"></i> Low Stock Threshold
                        </label>
                        <input type="number" id="quickStockThreshold" name="low_stock_threshold"
                               class="form-control" min="0" value="10">
                        <div class="form-text">Alert when stock falls below this level</div>
                    </div>

                    <!-- Preview -->
                    <div id="quickStockPreview" class="alert alert-secondary">
                        <strong>Preview:</strong> <span id="previewText">Select a warehouse to see preview</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-filter">
                        <i class="bi bi-check-circle"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Variants Modal -->
<div class="modal fade" id="variantsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-grid-3x3-gap"></i> Product Variants:
                    <span id="variantsProductName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="variantsLoader" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading variants...</div>
                </div>
                <div id="variantsContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">Image</th>
                                    <th>Variant</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="variantsTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.products.data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.category_id = $('#categoryFilter').val();
                d.vendor_id = $('#vendorFilter').val();
                d.is_featured = $('#featuredFilter').val();
                d.show_on_home = $('#homeFilter').val();
                d.is_available = $('#availableFilter').val();
                d.stock_status = $('#stockFilter').val();
                d.search = $('#searchFilter').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'image_preview', name: 'image_preview', orderable: false, searchable: false },
            { data: 'name_link', name: 'name' },
            { data: 'category_name', name: 'category.title' },
            { data: 'price_display', name: 'price' },
            { data: 'stock_badge', name: 'stock_quantity' },
            { data: 'status_badge', name: 'status_key_code' },
            { data: 'badges', name: 'badges', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 50,
        responsive: true,
        language: {
            processing: `
                <div class="text-center">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading Products...</div>
                </div>
            `
        },
        drawCallback: function() {
            updateStatistics();
        }
    });

    // Filter change events
    $('#statusFilter, #categoryFilter, #vendorFilter, #featuredFilter, #homeFilter, #availableFilter, #stockFilter').on('change', function() {
        table.draw();
    });

    // Search with delay
    let searchDelay;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#searchFilter').val('');
        $('#statusFilter').val('');
        $('#categoryFilter').val('');
        $('#vendorFilter').val('');
        $('#featuredFilter').val('');
        $('#homeFilter').val('');
        $('#availableFilter').val('');
        $('#stockFilter').val('');
        table.draw();
    });
    // ✅ Initialize tooltips after table draw
    table.on('draw', function() {
        // Initialize Bootstrap tooltips
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            boundary: 'window'
        });

        updateStatistics();
    });

    // ✅ Also initialize on first load
    $('[data-bs-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        boundary: 'window'
    });
    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.product-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    $(document).on('change', '.product-checkbox', function() {
        updateBulkActions();
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        const selectedCount = $('.product-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);

        if (selectedCount > 0) {
            $('#bulkActionsBar').removeClass('d-none');
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Deselect all
    $('#deselectAll').on('click', function() {
        $('.product-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    });

    // Delete product
    $(document).on('click', '.delete-product', function() {
        const productId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.products.destroy", ":id") }}'.replace(':id', productId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.draw();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete product', 'error');
                    }
                });
            }
        });
    });

    // Bulk delete
    $('#bulkDelete').on('click', function() {
        const selectedIds = $('.product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Delete ' + selectedIds.length + ' products?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.products.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.draw();
                            $('#deselectAll').click();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete products', 'error');
                    }
                });
            }
        });
    });

    // Bulk status change
    $('.bulk-action[data-action="status"]').on('click', function(e) {
        e.preventDefault();
        $('#bulkStatusModal').modal('show');
    });

    $('#confirmBulkStatus').on('click', function() {
        const selectedIds = $('.product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const newStatus = $('#bulkStatusSelect').val();

        $.ajax({
            url: '{{ route("admin.products.bulk-status-update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_ids: selectedIds,
                status_key_code: newStatus
            },
            success: function(response) {
                if (response.success) {
                    $('#bulkStatusModal').modal('hide');
                    Swal.fire('Updated!', response.message, 'success');
                    table.draw();
                    $('#deselectAll').click();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update status', 'error');
            }
        });
    });

    // Bulk stock update
    $('.bulk-action[data-action="stock"]').on('click', function(e) {
        e.preventDefault();
        $('#bulkStockModal').modal('show');
    });

    $('#confirmBulkStock').on('click', function() {
        const selectedIds = $('.product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const actionType = $('#stockActionType').val();
        const quantity = parseInt($('#stockQuantity').val());

        $.ajax({
            url: '{{ route("admin.products.bulk-stock-update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_ids: selectedIds,
                action_type: actionType,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    $('#bulkStockModal').modal('hide');
                    Swal.fire('Updated!', response.message, 'success');
                    table.draw();
                    $('#deselectAll').click();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update stock', 'error');
            }
        });
    });

    // CSV Import
    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: '{{ route("admin.products.import") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Importing Products...',
                    html: '<div class="text-center"><div class="spinner-border text-success mb-3" role="status"></div><p>Please wait while we process your file</p><small class="text-muted">Do not close this window</small></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                $('#importModal').modal('hide');
                $('#importForm')[0].reset();

                // Determine icon based on results
                let icon = 'success';
                let title = 'Import Successful!';

                if (response.failed > 0 && response.imported === 0) {
                    icon = 'error';
                    title = 'Import Failed';
                } else if (response.failed > 0) {
                    icon = 'warning';
                    title = 'Import Completed with Issues';
                }

                // Build HTML content
                let html = `
                    <div class="import-results">
                        <!-- Summary Stats -->
                        <div class="alert alert-${icon === 'error' ? 'danger' : icon === 'warning' ? 'warning' : 'success'} mb-3">
                            <div class="d-flex justify-content-around text-center">
                                <div>
                                    <h4 class="mb-0">${response.total_processed || (response.imported + response.failed)}</h4>
                                    <small>Total Rows</small>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-success">${response.imported}</h4>
                                    <small>✓ Imported</small>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-danger">${response.failed}</h4>
                                    <small>✗ Failed</small>
                                </div>
                            </div>
                        </div>

                        <!-- Success Message -->
                        ${response.imported > 0 ? `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <strong>${response.imported}</strong> product(s) imported successfully
                            </div>
                        ` : ''}

                        <!-- Warnings Section -->
                        ${response.warnings && response.warnings.length > 0 ? `
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Warnings (${response.warnings.length})</strong>
                                </div>
                                <div class="warnings-list" style="max-height: 150px; overflow-y: auto; font-size: 0.9rem;">
                                    <ul class="mb-0 text-start">
                                        ${response.warnings.map(warn => `<li>${warn}</li>`).join('')}
                                    </ul>
                                </div>
                            </div>
                        ` : ''}

                        <!-- Errors Section -->
                        ${response.errors && response.errors.length > 0 ? `
                            <div class="alert alert-danger mb-0">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <i class="bi bi-x-circle-fill me-2"></i>
                                        <strong>Errors (${response.errors.length})</strong>
                                    </div>
                                    ${response.errors.length > 5 ? `
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="downloadErrorLog()">
                                            <i class="bi bi-download"></i> Download Full Log
                                        </button>
                                    ` : ''}
                                </div>
                                <div class="errors-list" style="max-height: 250px; overflow-y: auto; font-size: 0.9rem; background: #fff; padding: 10px; border-radius: 4px;">
                                    <ul class="mb-0 text-start" style="color: #721c24;">
                                        ${response.errors.slice(0, 20).map(err => `<li>${err}</li>`).join('')}
                                        ${response.errors.length > 20 ? `
                                            <li class="text-muted mt-2">
                                                <em>...and ${response.errors.length - 20} more errors. Click "Download Full Log" to see all.</em>
                                            </li>
                                        ` : ''}
                                    </ul>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;

                // Store errors in global variable for download
                window.importErrors = response.errors || [];

                Swal.fire({
                    icon: icon,
                    title: title,
                    html: html,
                    confirmButtonText: icon === 'error' ? 'Close' : 'Got it',
                    confirmButtonColor: icon === 'error' ? '#dc3545' : '#5B914C',
                    width: '700px',
                    customClass: {
                        htmlContainer: 'text-start',
                        popup: 'import-results-popup'
                    },
                    didOpen: () => {
                        // Add custom styles
                        const style = document.createElement('style');
                        style.textContent = `
                            .import-results-popup .swal2-html-container {
                                overflow: visible !important;
                            }
                            .import-results ul {
                                padding-left: 20px;
                            }
                            .import-results ul li {
                                margin-bottom: 5px;
                                line-height: 1.5;
                            }
                            .errors-list::-webkit-scrollbar,
                            .warnings-list::-webkit-scrollbar {
                                width: 6px;
                            }
                            .errors-list::-webkit-scrollbar-track,
                            .warnings-list::-webkit-scrollbar-track {
                                background: #f1f1f1;
                            }
                            .errors-list::-webkit-scrollbar-thumb,
                            .warnings-list::-webkit-scrollbar-thumb {
                                background: #888;
                                border-radius: 3px;
                            }
                        `;
                        document.head.appendChild(style);
                    }
                }).then(() => {
                    // Reload DataTable after user closes the modal
                    if (typeof table !== 'undefined') {
                        table.ajax.reload(null, false);
                    } else if (typeof $('#productsTable').DataTable === 'function') {
                        $('#productsTable').DataTable().ajax.reload(null, false);
                    }
                });
            },
            error: function(xhr) {
                $('#importModal').modal('hide');

                let errorTitle = 'Import Failed';
                let errorMessage = 'An unexpected error occurred while importing products.';
                let errorHtml = '';

                if (xhr.responseJSON) {
                    errorMessage = xhr.responseJSON.message || errorMessage;

                    // Handle validation errors
                    if (xhr.responseJSON.errors) {
                        errorTitle = 'Validation Error';
                        errorHtml = `
                            <div class="alert alert-danger text-start mb-0">
                                <strong>Please fix the following issues:</strong>
                                <ul class="mt-2 mb-0">
                        `;

                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            if (Array.isArray(messages)) {
                                messages.forEach(msg => {
                                    errorHtml += `<li>${msg}</li>`;
                                });
                            } else {
                                errorHtml += `<li>${messages}</li>`;
                            }
                        });

                        errorHtml += `</ul></div>`;
                    }

                    // Show technical details in debug mode
                    if (xhr.responseJSON.technical_details) {
                        errorHtml += `
                            <details class="mt-3">
                                <summary class="text-muted" style="cursor: pointer;">
                                    <small>Technical Details (for developers)</small>
                                </summary>
                                <pre class="text-start mt-2 p-2 bg-light" style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">
    ${xhr.responseJSON.technical_details}
                                </pre>
                            </details>
                        `;
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your internet connection and try again.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Import endpoint not found. Please contact support.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please try again or contact support.';
                } else if (xhr.status === 413) {
                    errorMessage = 'File is too large. Please reduce the file size and try again.';
                } else if (xhr.status === 422) {
                    errorMessage = 'Validation failed. Please check your file format.';
                }

                Swal.fire({
                    icon: 'error',
                    title: errorTitle,
                    html: errorHtml || `<p>${errorMessage}</p>`,
                    confirmButtonColor: '#dc3545',
                    width: errorHtml ? '600px' : '500px',
                    customClass: {
                        htmlContainer: 'text-start'
                    },
                    footer: xhr.status >= 500 ?
                        '<small class="text-muted">If this problem persists, please contact your administrator with error code: ' + xhr.status + '</small>' :
                        null
                });
            }
        });
    });

        // Function to download error log
        function downloadErrorLog() {
            if (!window.importErrors || window.importErrors.length === 0) {
                Swal.fire('No Errors', 'No errors to download', 'info');
                return;
            }

            // Create error log content
            let logContent = '=== PRODUCT IMPORT ERROR LOG ===\n';
            logContent += 'Generated: ' + new Date().toLocaleString() + '\n';
            logContent += 'Total Errors: ' + window.importErrors.length + '\n';
            logContent += '='.repeat(50) + '\n\n';

            window.importErrors.forEach((error, index) => {
                logContent += `${index + 1}. ${error}\n`;
            });

            // Create blob and download
            const blob = new Blob([logContent], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'import-errors-' + Date.now() + '.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            Swal.fire({
                icon: 'success',
                title: 'Downloaded!',
                text: 'Error log has been downloaded',
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Update statistics
        function updateStatistics() {
            $.ajax({
                url: '{{ route("admin.products.statistics") }}',
                success: function(response) {
                    $('#totalProducts').text(response.total);
                    $('#activeProducts').text(response.active);
                    $('#draftProducts').text(response.draft);
                    $('#featuredProducts').text(response.featured);
                    $('#outOfStock').text(response.out_of_stock);
                    $('#lowStock').text(response.low_stock);
                }
            });
        }

        // Filter by clicking stats
        window.filterByStatus = function(status) {
            $('#searchFilter').val('');
            $('#categoryFilter').val('');
            $('#vendorFilter').val('');
            $('#stockFilter').val('');

            if (status === 'all') {
                $('#statusFilter').val('');
            } else if (status === 'active') {
                $('#statusFilter').val('PRODUCT_ACTIVE');
            } else if (status === 'draft') {
                $('#statusFilter').val('PRODUCT_DRAFT');
            }

            table.draw();
        };

        window.filterByStock = function(type) {
            $('#searchFilter').val('');
            $('#statusFilter').val('');
            $('#categoryFilter').val('');
            $('#vendorFilter').val('');

            if (type === 'featured') {
                $('#featuredFilter').val('1');
                $('#stockFilter').val('');
            } else if (type === 'out') {
                $('#stockFilter').val('out_of_stock');
                $('#featuredFilter').val('');
            } else if (type === 'low') {
                $('#stockFilter').val('low_stock');
                $('#featuredFilter').val('');
            }

            table.draw();
        };

        // Initial statistics load
        updateStatistics();
    });

// Quick Stock Management
$(document).on('click', '.quick-stock-btn', function() {
    const productId = $(this).data('id');
    const productName = $(this).data('name');
    const threshold = parseInt($(this).data('threshold')) || 10;

    $('#quickStockProductId').val(productId);
    $('#quickStockProductName').text(productName);
    $('#quickStockQuantity').val(0);
    $('#quickStockThreshold').val(threshold);
    $('#quickStockAction').val('set');
    $('#quickStockWarehouse').html('<option value="">Loading warehouses...</option>');
    $('#warehouseStockInfo').addClass('d-none');

    // Load warehouse stock data
    $.ajax({
        url: `/admin/products/${productId}/warehouse-stock`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                warehousesData = response.warehouses;

                // Update total stock
                $('#quickStockTotalStock').text(response.total_stock);

                // Populate warehouse dropdown
                let options = '<option value="">-- Select Warehouse --</option>';
                response.warehouses.forEach(function(warehouse) {
                    const defaultBadge = warehouse.is_default ? ' (Default)' : '';
                    const stockInfo = ` - Stock: ${warehouse.current_stock}`;
                    options += `<option value="${warehouse.id}"
                                       data-stock="${warehouse.current_stock}"
                                       data-reserved="${warehouse.reserved}"
                                       data-available="${warehouse.available}">
                                    ${warehouse.name} [${warehouse.code}]${defaultBadge}${stockInfo}
                                </option>`;
                });

                $('#quickStockWarehouse').html(options);

                // Auto-select default warehouse if exists
                const defaultWarehouse = response.warehouses.find(w => w.is_default);
                if (defaultWarehouse) {
                    $('#quickStockWarehouse').val(defaultWarehouse.id).trigger('change');
                }
            }
        },
        error: function(xhr) {
            $('#quickStockWarehouse').html('<option value="">Failed to load warehouses</option>');
            Swal.fire('Error', 'Failed to load warehouse data', 'error');
        }
    });

    $('#quickStockModal').modal('show');
});
// Warehouse selection change
$('#quickStockWarehouse').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const warehouseId = $(this).val();

    if (warehouseId) {
        const stock = parseInt(selectedOption.data('stock')) || 0;
        const reserved = parseInt(selectedOption.data('reserved')) || 0;
        const available = parseInt(selectedOption.data('available')) || 0;

        $('#quickStockWarehouseCurrent').text(stock);
        $('#quickStockWarehouseAvailable').text(available);

        $('#selectedWarehouseStock').text(stock);
        $('#selectedWarehouseReserved').text(reserved);
        $('#selectedWarehouseAvailable').text(available);

        $('#warehouseStockInfo').removeClass('d-none');

        // Update preview
        updateQuickStockPreview();
    } else {
        $('#warehouseStockInfo').addClass('d-none');
        $('#quickStockWarehouseCurrent').text('0');
        $('#quickStockWarehouseAvailable').text('0');
        $('#previewText').html('Select a warehouse to see preview');
    }
});
// Adjust quick stock quantity
window.quickAdjustStock = function(amount) {
    const input = $('#quickStockQuantity');
    const currentValue = parseInt(input.val()) || 0;
    const newValue = Math.max(0, currentValue + amount);
    input.val(newValue);
    updateQuickStockPreview();
};

// Update stock preview
function updateQuickStockPreview() {
    const warehouseId = $('#quickStockWarehouse').val();

    if (!warehouseId) {
        $('#previewText').html('Select a warehouse to see preview');
        $('#quickStockPreview').removeClass('alert-success alert-warning alert-danger').addClass('alert-secondary');
        return;
    }

    const action = $('#quickStockAction').val();
    const quantity = parseInt($('#quickStockQuantity').val()) || 0;
    const selectedOption = $('#quickStockWarehouse').find('option:selected');
    const currentStock = parseInt(selectedOption.data('stock')) || 0;
    const reserved = parseInt(selectedOption.data('reserved')) || 0;

    let newStock = 0;
    let actionText = '';

    switch(action) {
        case 'set':
            newStock = quantity;
            actionText = `Stock in selected warehouse will be set to: <strong>${newStock}</strong> units`;
            break;
        case 'add':
            newStock = currentStock + quantity;
            actionText = `Stock will increase to: <strong>${newStock}</strong> units (${currentStock} + ${quantity})`;
            break;
        case 'reduce':
            newStock = Math.max(0, currentStock - quantity);
            actionText = `Stock will reduce to: <strong>${newStock}</strong> units (${currentStock} - ${quantity})`;
            break;
    }

    const newAvailable = Math.max(0, newStock - reserved);
    actionText += `<br><small class="text-muted">Available after update: ${newAvailable} units (${reserved} reserved)</small>`;

    $('#previewText').html(actionText);

    // Change preview color
    const previewDiv = $('#quickStockPreview');
    previewDiv.removeClass('alert-secondary alert-success alert-warning alert-danger');

    const threshold = parseInt($('#quickStockThreshold').val()) || 10;

    if (newStock <= 0) {
        previewDiv.addClass('alert-danger');
    } else if (newStock <= threshold) {
        previewDiv.addClass('alert-warning');
    } else {
        previewDiv.addClass('alert-success');
    }
}

// Update preview when inputs change
$('#quickStockAction, #quickStockQuantity, #quickStockThreshold').on('change keyup', function() {
    updateQuickStockPreview();
});

// Submit quick stock form
$('#quickStockForm').on('submit', function(e) {
    e.preventDefault();

    const warehouseId = $('#quickStockWarehouse').val();

    if (!warehouseId) {
        Swal.fire('Error', 'Please select a warehouse', 'error');
        return;
    }

    const productId = $('#quickStockProductId').val();
    const formData = {
        _token: '{{ csrf_token() }}',
        warehouse_id: warehouseId,
        action_type: $('#quickStockAction').val(),
        quantity: parseInt($('#quickStockQuantity').val()),
        low_stock_threshold: parseInt($('#quickStockThreshold').val())
    };

    $.ajax({
        url: `/admin/products/${productId}/quick-stock-update`,
        type: 'POST',
        data: formData,
        beforeSend: function() {
            Swal.fire({
                title: 'Updating Stock...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                $('#quickStockModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `<p>${response.message}</p>
                           <p class="mb-0"><strong>New Stock:</strong> ${response.new_stock} units</p>
                           <p class="mb-0 text-muted"><small>Total across all warehouses: ${response.total_stock} units</small></p>`,
                    timer: 3000,
                    showConfirmButton: true
                });
                table.draw(false);
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update stock', 'error');
        }
    });
});

// View product image in modal
function viewProductImage(imageUrl, productName) {
    Swal.fire({
        title: productName,
        imageUrl: imageUrl,
        imageAlt: productName,
        imageWidth: 600,
        imageHeight: 600,
        showCloseButton: true,
        showConfirmButton: false,
        backdrop: 'rgba(0,0,0,0.8)',
        customClass: {
            image: 'rounded',
            popup: 'border-0'
        }
    });
}
// View product variants in modal
$(document).on('click', '.view-variants-btn', function() {
    const productId = $(this).data('id');
    const productName = $(this).data('name');

    $('#variantsProductName').text(productName);
    $('#variantsLoader').show();
    $('#variantsContent').hide();
    $('#variantsTableBody').empty();
    $('#variantsModal').modal('show');

    $.ajax({
        url: `/admin/products/${productId}/modal-variants`,
        type: 'GET',
        success: function(response) {
            if (response.success && response.variants.length > 0) {
                let html = '';

                response.variants.forEach(function(variant) {
                    html += `
                        <tr>
                            <td>
                                <img src="${variant.image}"
                                     alt="${variant.name}"
                                     class="img-thumbnail"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <strong>${variant.name}</strong>
                                ${variant.is_default ? '<span class="badge bg-primary ms-2">Default</span>' : ''}
                            </td>
                            <td><code>${variant.sku}</code></td>
                            <td>${variant.price}</td>
                            <td>${variant.stock_badge}</td>
                            <td>${variant.status_badge}</td>
                        </tr>
                    `;
                });

                $('#variantsTableBody').html(html);
                $('#variantsLoader').hide();
                $('#variantsContent').show();
            } else {
                $('#variantsLoader').html('<div class="alert alert-info">No variants found</div>');
            }
        },
        error: function(xhr) {
            $('#variantsLoader').html('<div class="alert alert-danger">Failed to load variants</div>');
        }
    });
});
</script>
@endpush
