@extends('admin.layouts.app')

@section('title', 'Low Stock Products')

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
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
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
    .low-stock-highlight {
        background-color: #fff3cd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-exclamation-triangle text-warning"></i> Low Stock Products
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">Low Stock</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('products.create'))
            <a href="{{ route('admin.products.create') }}" class="btn btn-filter">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
            @endif
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Products
            </a>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Low Stock Alert!</strong> These products have stock levels at or below their low stock threshold. Consider restocking soon to avoid stockouts.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Statistics Cards -->
    <div class="product-stats">
        <div class="row">
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalLowStock">0</div>
                <div class="stat-label">Total Low Stock Items</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="criticalStock">0</div>
                <div class="stat-label">Critical (≤5 units)</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="averageLowStock">0</div>
                <div class="stat-label">Average Stock Level</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="lowStockValue">{{ store_currency_symbol() }}0</div>
                <div class="stat-label">Total Stock Value</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control" placeholder="Name, SKU, Barcode...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                    @endforeach
                </select>
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
            <div class="col-md-2">
                <label class="form-label">Stock Level</label>
                <select id="stockLevelFilter" class="form-select">
                    <option value="">All Low Stock</option>
                    <option value="critical">Critical (≤5)</option>
                    <option value="very_low">Very Low (6-10)</option>
                    <option value="low">Low (11-20)</option>
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
                                <li><a class="dropdown-item bulk-action" data-action="stock" href="#"><i class="bi bi-box-seam"></i> Update Stock</a></li>
                                <li><a class="dropdown-item bulk-action" data-action="status" href="#"><i class="bi bi-toggles"></i> Change Status</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item bulk-action" data-action="feature" href="#"><i class="bi bi-star"></i> Toggle Featured</a></li>
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
                            <th width="100">Current Stock</th>
                            <th width="100">Threshold</th>
                            <th width="120">Price</th>
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
                <h5 class="modal-title"><i class="bi bi-box-seam"></i> Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Note:</strong> This will update stock levels for all selected low stock products.
                </div>
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
                    <div class="form-text">Enter the quantity to set, add, or reduce</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-filter" id="confirmBulkStock">
                    <i class="bi bi-check-circle"></i> Update Stock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stock Update Modal (Single Product) -->
<div class="modal fade" id="quickStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-lightning"></i> Quick Stock Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Product</label>
                    <input type="text" id="quickStockProductName" class="form-control" readonly>
                    <input type="hidden" id="quickStockProductId">
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Stock</label>
                    <input type="text" id="quickStockCurrent" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Stock Quantity</label>
                    <input type="number" id="quickStockNew" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-filter" id="confirmQuickStock">
                    <i class="bi bi-check-circle"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable with LOW STOCK FILTER
    let table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.products.data") }}',
            data: function(d) {
                // FORCE LOW STOCK FILTER - THIS IS THE KEY LINE
                d.stock_status = 'low_stock';

                // Include other filters
                d.status = $('#statusFilter').val();
                d.category_id = $('#categoryFilter').val();
                d.vendor_id = $('#vendorFilter').val();
                d.search = $('#searchFilter').val();
                d.stock_level = $('#stockLevelFilter').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'image_preview', name: 'image_preview', orderable: false, searchable: false },
            { data: 'name_link', name: 'name' },
            { data: 'category_name', name: 'category.title' },
            { data: 'current_stock', name: 'stock_quantity' },
            { data: 'threshold', name: 'low_stock_threshold' },
            { data: 'price_display', name: 'price' },
            { data: 'status_badge', name: 'status_key_code' },
            { data: 'badges', name: 'badges', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'asc']], // Order by current stock (lowest first)
        pageLength: 25,
        responsive: true,
        language: {
            processing: `
                <div class="text-center">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading Low Stock Products...</div>
                </div>
            `
        },
        drawCallback: function() {
            updateStatistics();

            // Highlight critical stock rows
            $('#productsTable tbody tr').each(function() {
                const stockText = $(this).find('td:eq(4)').text();
                const stock = parseInt(stockText);
                if (stock <= 5 && stock > 0) {
                    $(this).addClass('low-stock-highlight');
                }
            });
        }
    });

    // Filter change events
    $('#statusFilter, #categoryFilter, #vendorFilter, #stockLevelFilter').on('change', function() {
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
        $('#stockLevelFilter').val('');
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

    // Quick stock update (single product)
    $(document).on('click', '.quick-stock-update', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const currentStock = $(this).data('stock');

        $('#quickStockProductId').val(productId);
        $('#quickStockProductName').val(productName);
        $('#quickStockCurrent').val(currentStock);
        $('#quickStockNew').val(currentStock);

        $('#quickStockModal').modal('show');
    });

    // Confirm quick stock update
    $('#confirmQuickStock').on('click', function() {
        const productId = $('#quickStockProductId').val();
        const newStock = parseInt($('#quickStockNew').val());

        $.ajax({
            url: '{{ route("admin.products.bulk-stock-update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_ids: [productId],
                action_type: 'set',
                quantity: newStock
            },
            success: function(response) {
                if (response.success) {
                    $('#quickStockModal').modal('hide');
                    Swal.fire('Updated!', 'Stock updated successfully', 'success');
                    table.draw();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update stock', 'error');
            }
        });
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

        if (quantity <= 0 && actionType !== 'set') {
            Swal.fire('Error!', 'Please enter a valid quantity', 'error');
            return;
        }

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

    // Update statistics for LOW STOCK products only
    function updateStatistics() {
        $.ajax({
            url: '{{ route("admin.products.statistics") }}',
            data: {
                stock_status: 'low_stock'
            },
            success: function(response) {
                $('#totalLowStock').text(response.total || 0);
                $('#criticalStock').text(response.critical || 0);
                $('#averageLowStock').text(response.average_stock || 0);
                $('#lowStockValue').text('{{ store_currency_symbol() }}' + (response.total_value || 0).toLocaleString());
            }
        });
    }

    // Initial statistics load
    updateStatistics();
});
</script>
@endpush
