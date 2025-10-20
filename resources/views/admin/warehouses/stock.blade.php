@extends('admin.layouts.app')
{{-- resources/views/admin/warehouses/stock.blade.php --}}
@section('title', 'Warehouse Stock - ' . $warehouse->name)

@push('styles')
<style>
    .warehouse-info-bar {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .warehouse-info-bar h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .warehouse-info-bar .warehouse-code {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s;
        text-align: center;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.15);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .filter-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .stock-status-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .stock-status-tab {
        padding: 10px 20px;
        border: 2px solid #e0e0e0;
        background: white;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
        color: #6c757d;
    }

    .stock-status-tab:hover {
        border-color: #5B914C;
        color: #5B914C;
    }

    .stock-status-tab.active {
        background: #5B914C;
        border-color: #5B914C;
        color: white;
    }

    .stock-status-tab .badge {
        margin-left: 8px;
    }

    .table-card {
        background: white;
        border-radius: 8px;
        padding: 25px;
        border: 1px solid #e0e0e0;
    }

    .stock-badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .stock-badge.in-stock {
        background: #d1e7dd;
        color: #0f5132;
    }

    .stock-badge.low-stock {
        background: #fff3cd;
        color: #856404;
    }

    .stock-badge.out-of-stock {
        background: #f8d7da;
        color: #842029;
    }

    .btn-adjust {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }

    .btn-adjust:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }

    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #e0e0e0;
    }

    .quantity-info {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .quantity-main {
        font-size: 1.2rem;
        font-weight: bold;
        color: #2c3e50;
    }

    .quantity-details {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .location-badge {
        background: #e9ecef;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #495057;
        display: inline-block;
    }

    .adjust-modal-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .current-stock-display {
        background: #f0f7ed;
        border: 2px solid #5B914C;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
    }

    .current-stock-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .current-stock-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stock-breakdown {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 10px;
    }

    .breakdown-item {
        background: white;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
        border: 1px solid #dee2e6;
    }

    .breakdown-value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #5B914C;
    }

    .breakdown-label {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .action-type-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }

    .action-type-card {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }

    .action-type-card:hover {
        border-color: #5B914C;
        background: #f8f9fa;
    }

    .action-type-card.active {
        border-color: #5B914C;
        background: #f0f7ed;
    }

    .action-type-card i {
        font-size: 1.5rem;
        color: #5B914C;
        margin-bottom: 5px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Warehouse Info Bar -->
    <div class="warehouse-info-bar">
        <div>
            <h3>
                <i class="bi bi-building"></i> {{ $warehouse->name }}
                <span class="warehouse-code">{{ $warehouse->code }}</span>
            </h3>
            <div class="mt-2">
                @if($warehouse->is_active)
                    <span class="badge bg-light text-dark">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
                @if($warehouse->is_default)
                    <span class="badge bg-warning text-dark">Default Warehouse</span>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Details
            </a>
            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-light">
                <i class="bi bi-list"></i> All Warehouses
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-value" id="statTotalStock">0</div>
                <div class="stat-label">Total Stock</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-value text-success" id="statInStock">0</div>
                <div class="stat-label">In Stock</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-value text-warning" id="statLowStock">0</div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-value text-danger" id="statOutOfStock">0</div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>
    </div>

    <!-- Stock Status Tabs -->
    <div class="stock-status-tabs">
        <button class="stock-status-tab active" data-status="">
            <i class="bi bi-grid"></i> All Products
            <span class="badge bg-secondary" id="countAll">0</span>
        </button>
        <button class="stock-status-tab" data-status="in_stock">
            <i class="bi bi-check-circle"></i> In Stock
            <span class="badge bg-success" id="countInStock">0</span>
        </button>
        <button class="stock-status-tab" data-status="low_stock">
            <i class="bi bi-exclamation-triangle"></i> Low Stock
            <span class="badge bg-warning" id="countLowStock">0</span>
        </button>
        <button class="stock-status-tab" data-status="out_of_stock">
            <i class="bi bi-x-circle"></i> Out of Stock
            <span class="badge bg-danger" id="countOutOfStock">0</span>
        </button>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search Products</label>
                <input type="text" id="searchInput" class="form-control"
                       placeholder="Search by product name or SKU...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\ProductsCategories::orderBy('name')->get() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="button" class="btn btn-primary flex-fill" onclick="applyFilters()"
                        style="background-color: #5B914C; border-color: #5B914C;">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <button type="button" class="btn btn-outline-secondary flex-fill" onclick="resetFilters()">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="button" class="btn btn-outline-success flex-fill" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <a href="{{ route('admin.warehouses.export-stock', $warehouse->id) }}"
                   class="btn btn-outline-info flex-fill">
                    <i class="bi bi-download"></i> Export
                </a>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="bi bi-box-seam"></i> Stock Inventory
            </h5>
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <button type="button" class="btn btn-adjust" onclick="openBulkAdjustModal()">
                <i class="bi bi-plus-slash-minus"></i> Bulk Adjust Stock
            </button>
            @endif
        </div>

        <div class="table-responsive">
            <table id="stockTable" class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Reserved</th>
                        <th>Location</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header adjust-modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-slash-minus"></i> Adjust Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustStockForm">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                <input type="hidden" name="product_id" id="adjustProductId">
                <input type="hidden" name="stock_id" id="adjustStockId">

                <div class="modal-body">
                    <!-- Product Info -->
                    <div class="mb-3">
                        <strong>Product:</strong>
                        <div id="adjustProductName" class="text-muted"></div>
                    </div>

                    <!-- Current Stock Display -->
                    <div class="current-stock-display">
                        <div class="current-stock-label">Current Stock</div>
                        <div class="current-stock-value" id="currentQuantity">0</div>

                        <div class="stock-breakdown">
                            <div class="breakdown-item">
                                <div class="breakdown-value text-success" id="currentAvailable">0</div>
                                <div class="breakdown-label">Available</div>
                            </div>
                            <div class="breakdown-item">
                                <div class="breakdown-value text-warning" id="currentReserved">0</div>
                                <div class="breakdown-label">Reserved</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Type Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Action Type</label>
                        <div class="action-type-cards">
                            <div class="action-type-card active" data-action="set">
                                <i class="bi bi-pencil-square"></i>
                                <div class="fw-bold">Set Stock</div>
                                <small class="text-muted">Replace current</small>
                            </div>
                            <div class="action-type-card" data-action="add">
                                <i class="bi bi-plus-circle"></i>
                                <div class="fw-bold">Add Stock</div>
                                <small class="text-muted">Increase quantity</small>
                            </div>
                            <div class="action-type-card" data-action="reduce">
                                <i class="bi bi-dash-circle"></i>
                                <div class="fw-bold">Reduce Stock</div>
                                <small class="text-muted">Decrease quantity</small>
                            </div>
                        </div>
                        <input type="hidden" name="action_type" id="actionType" value="set">
                    </div>

                    <!-- Quantity Input -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="adjustQuantity"
                               class="form-control form-control-lg" min="0" required>
                        <small class="text-muted" id="actionHint">This will replace the current stock</small>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Storage Location</label>
                        <input type="text" name="location" id="adjustLocation"
                               class="form-control" placeholder="e.g., Aisle 3, Shelf B">
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="Optional reason for this adjustment"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-adjust">
                        <i class="bi bi-check-circle"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stockTable;
let currentStatus = '';

$(document).ready(function() {
    // Initialize DataTable
    initializeStockTable();

    // Load statistics
    loadStatistics();

    // Stock status tabs
    $('.stock-status-tab').on('click', function() {
        $('.stock-status-tab').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status');
        stockTable.ajax.reload();
    });

    // Action type cards
    $('.action-type-card').on('click', function() {
        $('.action-type-card').removeClass('active');
        $(this).addClass('active');
        const action = $(this).data('action');
        $('#actionType').val(action);
        updateActionHint(action);
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.stock-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Form submission
    $('#adjustStockForm').on('submit', function(e) {
        e.preventDefault();
        submitAdjustment();
    });
});

// Initialize DataTable
function initializeStockTable() {
    stockTable = $('#stockTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.warehouses.stock.data", $warehouse->id) }}',
            data: function(d) {
                d.stock_status = currentStatus;
                d.search = $('#searchInput').val();
                d.category = $('#categoryFilter').val();
            }
        },
        columns: [
            {
                data: 'checkbox',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input stock-checkbox" value="${row.id}">`;
                }
            },
            {
                data: 'product_info',
                name: 'product.name',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-2">
                            <img src="${row.product_image || '/images/placeholders/not_availble.jpg'}"
                                 class="product-image" alt="Product">
                            <div>
                                <strong>${row.product_name}</strong><br>
                                <small class="text-muted">SKU: ${row.product_sku}</small>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'quantity',
                render: function(data, type, row) {
                    const colorClass = data <= 0 ? 'text-danger' :
                                     (row.is_low_stock ? 'text-warning' : 'text-success');
                    return `<div class="quantity-main ${colorClass}">${data}</div>`;
                }
            },
            {
                data: 'available_quantity',
                render: function(data) {
                    return `<span class="badge bg-success">${data}</span>`;
                }
            },
            {
                data: 'reserved_quantity',
                render: function(data) {
                    return data > 0 ?
                        `<span class="badge bg-warning text-dark">${data}</span>` :
                        '<span class="text-muted">0</span>';
                }
            },
            {
                data: 'location',
                render: function(data) {
                    return data ? `<span class="location-badge">${data}</span>` :
                                  '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'value',
                render: function(data, type, row) {
                    const value = row.quantity * row.product_price;
                    return `<span class="text-success fw-bold">$${value.toFixed(2)}</span>`;
                }
            },
            {
                data: 'status',
                render: function(data, type, row) {
                    if (row.quantity <= 0) {
                        return '<span class="stock-badge out-of-stock">Out of Stock</span>';
                    } else if (row.is_low_stock) {
                        return '<span class="stock-badge low-stock">Low Stock</span>';
                    } else {
                        return '<span class="stock-badge in-stock">In Stock</span>';
                    }
                }
            },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = `
                        <div class="btn-group" role="group">
                            <a href="/admin/products/${row.product_id}"
                               class="btn btn-sm btn-info" title="View Product">
                                <i class="bi bi-eye"></i>
                            </a>
                    `;

                    @if(auth('admin')->user()->hasPermission('inventory.update'))
                    actions += `
                        <button type="button" class="btn btn-sm btn-primary"
                                onclick="openAdjustModal('${row.id}', '${row.product_id}', '${row.product_name}', ${row.quantity}, ${row.available_quantity}, ${row.reserved_quantity}, '${row.location || ''}')"
                                title="Adjust Stock">
                            <i class="bi bi-pencil"></i>
                        </button>
                    `;
                    @endif

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        order: [[2, 'desc']],
        pageLength: 25,
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading...',
            emptyTable: 'No stock found'
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });
}

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.warehouses.statistics", $warehouse->id) }}',
        success: function(stats) {
            $('#statTotalStock').text(stats.total_products || 0);
            $('#statInStock').text(stats.in_stock || 0);
            $('#statLowStock').text(stats.low_stock || 0);
            $('#statOutOfStock').text(stats.out_of_stock || 0);

            $('#countAll').text(stats.total_products || 0);
            $('#countInStock').text(stats.in_stock || 0);
            $('#countLowStock').text(stats.low_stock || 0);
            $('#countOutOfStock').text(stats.out_of_stock || 0);
        }
    });
}

// Open adjust modal
function openAdjustModal(stockId, productId, productName, quantity, available, reserved, location) {
    $('#adjustStockId').val(stockId);
    $('#adjustProductId').val(productId);
    $('#adjustProductName').text(productName);
    $('#currentQuantity').text(quantity);
    $('#currentAvailable').text(available);
    $('#currentReserved').text(reserved);
    $('#adjustLocation').val(location);
    $('#adjustQuantity').val('');

    // Reset to 'set' action
    $('.action-type-card').removeClass('active');
    $('.action-type-card[data-action="set"]').addClass('active');
    $('#actionType').val('set');
    updateActionHint('set');

    $('#adjustStockModal').modal('show');
}

// Update action hint
function updateActionHint(action) {
    const hints = {
        'set': 'This will replace the current stock with the entered quantity',
        'add': 'This will add the entered quantity to current stock',
        'reduce': 'This will subtract the entered quantity from current stock'
    };
    $('#actionHint').text(hints[action] || '');
}

// Submit adjustment
function submitAdjustment() {
    const formData = $('#adjustStockForm').serialize();

    $.ajax({
        url: '{{ route("admin.inventory.adjust.store") }}',
        type: 'POST',
        data: formData,
        beforeSend: function() {
            $('#adjustStockModal').modal('hide');
            Swal.fire({
                title: 'Adjusting Stock...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    confirmButtonColor: '#5B914C'
                });
                $('#adjustStockForm')[0].reset();
                refreshData();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to adjust stock'
            });
        }
    });
}

// Apply filters
function applyFilters() {
    stockTable.ajax.reload();
}

// Reset filters
function resetFilters() {
    $('#searchInput').val('');
    $('#categoryFilter').val('');
    currentStatus = '';
    $('.stock-status-tab').removeClass('active');
    $('.stock-status-tab[data-status=""]').addClass('active');
    stockTable.ajax.reload();
}

// Refresh data
function refreshData() {
    loadStatistics();
    stockTable.ajax.reload();
}
</script>
@endpush
