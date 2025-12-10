@extends('admin.layouts.app')

@section('title', 'Inactive Products')

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
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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
        opacity: 0.7;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .table td {
        vertical-align: middle;
    }
    .inactive-row {
        opacity: 0.8;
        background-color: #f8f9fa;
    }
    .inactive-badge {
        background-color: #6c757d !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-eye-slash text-secondary"></i> Inactive Products
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">Inactive</li>
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

    <!-- Info Alert -->
    <div class="alert alert-secondary alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle"></i>
        <strong>Inactive Products:</strong> These products are marked as inactive or discontinued and are not visible to customers on your website. You can activate them anytime.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Statistics Cards -->
    <div class="product-stats">
        <div class="row">
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalInactive">0</div>
                <div class="stat-label">Total Inactive Products</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="inactiveInStock">0</div>
                <div class="stat-label">With Stock Available</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="inactiveOutStock">0</div>
                <div class="stat-label">Out of Stock</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="inactiveValue">{{ store_currency_symbol() }}0</div>
                <div class="stat-label">Total Inventory Value</div>
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
                <label class="form-label">Stock Status</label>
                <select id="stockFilter" class="form-select">
                    <option value="">All</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Product Type</label>
                <select id="typeFilter" class="form-select">
                    <option value="">All Types</option>
                    <option value="simple">Simple</option>
                    <option value="variable">Variable</option>
                    <option value="grouped">Grouped</option>
                    <option value="external">External</option>
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
                            <button type="button" class="btn btn-sm btn-success" id="bulkActivate">
                                <i class="bi bi-check-circle"></i> Activate Selected
                            </button>
                        </div>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-pencil"></i> More Actions
                            </button>
                            <ul class="dropdown-menu">
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
                            <th width="100">Type</th>
                            <th>Badges</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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

<!-- Quick Activate Modal -->
<div class="modal fade" id="quickActivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Activate Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Note:</strong> Activating this product will make it visible to customers on your website.
                </div>
                <div class="mb-3">
                    <label class="form-label">Product</label>
                    <input type="text" id="activateProductName" class="form-control" readonly>
                    <input type="hidden" id="activateProductId">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="publishOnActivate" checked>
                    <label class="form-check-label" for="publishOnActivate">
                        Publish product immediately
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmActivate">
                    <i class="bi bi-check-circle"></i> Activate Product
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable with INACTIVE FILTER
    let table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.products.data") }}',
            data: function(d) {
                // FORCE INACTIVE STATUS FILTER - THIS IS THE KEY LINE
                d.status = 'PRODUCT_DISCONTINUED';

                // Include other filters
                d.category_id = $('#categoryFilter').val();
                d.vendor_id = $('#vendorFilter').val();
                d.stock_status = $('#stockFilter').val();
                d.product_type = $('#typeFilter').val();
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
            { data: 'product_type_badge', name: 'product_type' },
            { data: 'badges', name: 'badges', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: `
                <div class="text-center">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading Inactive Products...</div>
                </div>
            `
        },
        drawCallback: function() {
            updateStatistics();

            // Apply inactive styling to all rows
            $('#productsTable tbody tr').addClass('inactive-row');
        }
    });

    // Filter change events
    $('#categoryFilter, #vendorFilter, #stockFilter, #typeFilter').on('change', function() {
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
        $('#categoryFilter').val('');
        $('#vendorFilter').val('');
        $('#stockFilter').val('');
        $('#typeFilter').val('');
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

    // Quick activate product
    $(document).on('click', '.quick-activate', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');

        $('#activateProductId').val(productId);
        $('#activateProductName').val(productName);
        $('#publishOnActivate').prop('checked', true);

        $('#quickActivateModal').modal('show');
    });

    // Confirm quick activate
    $('#confirmActivate').on('click', function() {
        const productId = $('#activateProductId').val();
        const publish = $('#publishOnActivate').is(':checked');

        $.ajax({
            url: '{{ route("admin.products.bulk-status-update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_ids: [productId],
                status_key_code: 'PRODUCT_ACTIVE'
            },
            success: function(response) {
                if (response.success) {
                    // If publish is checked, also publish the product
                    if (publish) {
                        $.ajax({
                            url: '{{ route("admin.products.toggle-publish", ":id") }}'.replace(':id', productId),
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            }
                        });
                    }

                    $('#quickActivateModal').modal('hide');
                    Swal.fire('Activated!', 'Product activated successfully', 'success');
                    table.draw();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to activate product', 'error');
            }
        });
    });

    // Bulk activate
    $('#bulkActivate').on('click', function() {
        const selectedIds = $('.product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Activate ' + selectedIds.length + ' products?',
            text: "These products will become visible to customers.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, activate them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.products.bulk-activate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Activated!', response.message, 'success');
                            table.draw();
                            $('#deselectAll').click();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to activate products', 'error');
                    }
                });
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

    // Bulk toggle featured
    $('.bulk-action[data-action="feature"]').on('click', function(e) {
        e.preventDefault();

        const selectedIds = $('.product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Toggle Featured Status?',
            text: "Toggle featured status for " + selectedIds.length + " products?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, toggle it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.products.bulk-toggle-featured") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_ids: selectedIds,
                        is_featured: true // Toggle
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Updated!', response.message, 'success');
                            table.draw();
                            $('#deselectAll').click();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update featured status', 'error');
                    }
                });
            }
        });
    });

    // Update statistics for INACTIVE products only
    function updateStatistics() {
        $.ajax({
            url: '{{ route("admin.products.statistics") }}',
            data: {
                status: 'PRODUCT_DISCONTINUED'
            },
            success: function(response) {
                $('#totalInactive').text(response.total || 0);
                $('#inactiveInStock').text(response.in_stock || 0);
                $('#inactiveOutStock').text(response.out_of_stock || 0);
                $('#inactiveValue').text('{{ store_currency_symbol() }}' + (response.total_value || 0).toLocaleString());
            }
        });
    }

    // Initial statistics load
    updateStatistics();
});
</script>
@endpush
