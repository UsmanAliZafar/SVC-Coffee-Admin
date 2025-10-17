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
                        <a href="{{ asset('templates/products_import_template.csv') }}" target="_blank">Download CSV Template</a>
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
<div class="modal fade" id="quickStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Quick Stock Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickStockForm">
                @csrf
                <input type="hidden" id="quickStockProductId" name="product_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 id="quickStockProductName" class="text-muted"></h6>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <div class="alert alert-info mb-2">
                            <strong id="quickStockCurrent">0</strong> units
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action Type</label>
                        <select id="quickStockAction" class="form-select" name="action_type">
                            <option value="set">Set Stock (Replace)</option>
                            <option value="add">Add Stock (+)</option>
                            <option value="reduce">Reduce Stock (-)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(-10)">
                                <i class="bi bi-dash-lg"></i> 10
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" id="quickStockQuantity" name="quantity" class="form-control text-center" min="0" value="0" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="quickAdjustStock(10)">
                                <i class="bi bi-plus-lg"></i> 10
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" id="quickStockThreshold" name="low_stock_threshold" class="form-control" min="0" value="10">
                        <div class="form-text">Optional: Update threshold for low stock alerts</div>
                    </div>

                    <div id="quickStockPreview" class="alert alert-secondary">
                        <strong>Preview:</strong> <span id="previewText">New stock will be: 0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-filter">
                        <i class="bi bi-check-circle"></i> Update Stock
                    </button>
                </div>
            </form>
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
        pageLength: 25,
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
                    title: 'Importing...',
                    text: 'Please wait while we import your products',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                $('#importModal').modal('hide');
                $('#importForm')[0].reset();

                let html = response.message;
                if (response.errors && response.errors.length > 0) {
                    html += '<br><br><strong>Errors:</strong><br>' + response.errors.slice(0, 10).join('<br>');
                    if (response.errors.length > 10) {
                        html += '<br>...and ' + (response.errors.length - 10) + ' more';
                    }
                }

                Swal.fire({
                    title: 'Import Complete',
                    html: html,
                    icon: response.failed > 0 ? 'warning' : 'success'
                });

                table.draw();
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to import products', 'error');
            }
        });
    });

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
// Quick Stock Management
$(document).on('click', '.quick-stock-btn', function() {
    const productId = $(this).data('id');
    const productName = $(this).data('name');
    const currentStock = parseInt($(this).data('stock'));
    const threshold = parseInt($(this).data('threshold'));

    $('#quickStockProductId').val(productId);
    $('#quickStockProductName').text(productName);
    $('#quickStockCurrent').text(currentStock);
    $('#quickStockQuantity').val(0);
    $('#quickStockThreshold').val(threshold);
    $('#quickStockAction').val('set');

    updateQuickStockPreview();
    $('#quickStockModal').modal('show');
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
    const action = $('#quickStockAction').val();
    const quantity = parseInt($('#quickStockQuantity').val()) || 0;
    const currentStock = parseInt($('#quickStockCurrent').text());
    let newStock = 0;
    let actionText = '';

    switch(action) {
        case 'set':
            newStock = quantity;
            actionText = `Stock will be set to: <strong>${newStock}</strong> units`;
            break;
        case 'add':
            newStock = currentStock + quantity;
            actionText = `Stock will be increased to: <strong>${newStock}</strong> units (${currentStock} + ${quantity})`;
            break;
        case 'reduce':
            newStock = Math.max(0, currentStock - quantity);
            actionText = `Stock will be reduced to: <strong>${newStock}</strong> units (${currentStock} - ${quantity})`;
            break;
    }

    $('#previewText').html(actionText);

    // Change preview color based on result
    const previewDiv = $('#quickStockPreview');
    previewDiv.removeClass('alert-secondary alert-success alert-warning alert-danger');

    if (newStock <= 0) {
        previewDiv.addClass('alert-danger');
    } else if (newStock <= parseInt($('#quickStockThreshold').val())) {
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

    const productId = $('#quickStockProductId').val();
    const formData = {
        _token: '{{ csrf_token() }}',
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
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                table.draw(false); // Reload table without resetting pagination
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
</script>
@endpush
