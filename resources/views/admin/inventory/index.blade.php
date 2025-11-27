@extends('admin.layouts.app')
{{-- resources/views/admin/inventory/index.blade.php --}}
@section('title', 'Inventory Management')

@push('styles')
<style>
    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #ddd;
        transition: all 0.3s;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
    }

    .filter-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
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

    .warehouse-tabs {
        border-bottom: 2px solid #5B914C;
        margin-bottom: 20px;
    }

    .warehouse-tab {
        padding: 10px 20px;
        border: none;
        background: transparent;
        color: #666;
        cursor: pointer;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
    }

    .warehouse-tab:hover {
        color: #5B914C;
    }

    .warehouse-tab.active {
        color: #5B914C;
        font-weight: 600;
        border-bottom-color: #5B914C;
    }

    /* Current stock display in modal */
    .current-stock-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border-left: 4px solid #5B914C;
        margin-bottom: 15px;
    }

    .current-stock-info .stock-label {
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 5px;
    }

    .current-stock-info .stock-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }
    .badge.bg-purple {
        background-color: #6f42c1 !important;
    }

    .badge.bg-info {
        background-color: #0dcaf0 !important;
    }

    .variant-indicator {
        padding-left: 15px;
        border-left: 3px solid #6f42c1;
        margin-left: 5px;
    }

    .warehouse-count {
        margin-left: 5px;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 10px;
        }

    .warehouse-tab .warehouse-count {
        background-color: #6c757d !important;
    }

    .warehouse-tab.active .warehouse-count {
        background-color: #5B914C !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-boxes"></i> Inventory Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Inventory</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <button type="button" class="btn btn-primary" onclick="openAdjustModal()">
                <i class="bi bi-plus-slash-minus"></i> Adjust Stock
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="openTransferModal()">
                <i class="bi bi-arrow-left-right"></i> Transfer Stock
            </button>
            @endif
            <a href="{{ route('admin.inventory.movement') }}" class="btn btn-outline-info">
                <i class="bi bi-clock-history"></i> Movement History
            </a>
            <button type="button" class="btn btn-warning" id="cleanupBtn">
                Cleanup Deleted Products
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statsContainer">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value" id="statTotalProducts">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">In Stock</div>
                <div class="stat-value text-success" id="statInStock">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" onclick="window.location.href='{{ route('admin.inventory.low-stock') }}'">
                <div class="stat-label">Low Stock</div>
                <div class="stat-value text-warning" id="statLowStock">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" onclick="window.location.href='{{ route('admin.inventory.out-of-stock') }}'">
                <div class="stat-label">Out of Stock</div>
                <div class="stat-value text-danger" id="statOutOfStock">0</div>
            </div>
        </div>
    </div>

    <!-- Warehouse Tabs -->
    <div class="warehouse-tabs">
        <button class="warehouse-tab active" data-warehouse="">
            <i class="bi bi-grid"></i> All Warehouses
            <span class="badge bg-secondary warehouse-count" id="count-all">0</span>
        </button>
        @foreach($warehouses as $warehouse)
        <button class="warehouse-tab" data-warehouse="{{ $warehouse->id }}">
            <i class="bi bi-building"></i> {{ $warehouse->name }}
            @if($warehouse->is_default)
                <span class="badge bg-primary">Default</span>
            @endif
            <span class="badge bg-secondary warehouse-count" title="Total Products/Variantes in Warehouse" id="count-{{ $warehouse->id }}">0</span>
        </button>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Stock Status</label>
                <select id="filterStockStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="filterSearch" class="form-control" placeholder="Product name or SKU...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="button" class="btn btn-filter" onclick="applyFilters()">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="button" class="btn btn-outline-success" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="inventoryTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Product / Variant</th>
                            <th>Total Stock</th>
                            <th>Available</th>
                            <th>Reserved</th>
                            <th>Warehouses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-plus-slash-minus"></i> Adjust Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustStockForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="adjustProductId" class="form-select" required>
                            <option value="">Select Product</option>
                        </select>
                        <small class="text-muted">Search by name or SKU</small>
                    </div>

                    <!-- NEW: Variant Selection (shown only if product has variants) -->
                    <div class="mb-3" id="variantSelectionDiv" style="display: none;">
                        <label class="form-label fw-bold">Variant</label>
                        <select name="variant_id" id="adjustVariantId" class="form-select">
                            <option value="">Select Variant (or leave empty for main product)</option>
                        </select>
                        <small class="text-muted">Choose a specific variant or adjust main product stock</small>
                    </div>

                    <!-- Current Stock Info Display -->
                    <div id="currentStockInfo" class="current-stock-info" style="display: none;">
                        <div class="stock-label">Current Stock</div>
                        <div class="stock-value" id="currentStockValue">0</div>
                        <small class="text-muted" id="warehouseStockInfo"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="adjustWarehouseId" class="form-select" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $warehouse->is_default ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                                @if($warehouse->is_default) (Default) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                     <!-- ✅ ADD THIS NEW FIELD -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Storage Location</label>
                        <input type="text" name="location" id="adjustLocation" class="form-control"
                            placeholder="e.g., Aisle A-5, Shelf 3">
                        <small class="text-muted">Optional: Specify where this item is stored in the warehouse</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Action Type <span class="text-danger">*</span></label>
                        <select name="action_type" id="adjustActionType" class="form-select" required>
                            <option value="set">Set Stock (Replace current quantity)</option>
                            <option value="add">Add Stock (Increase quantity)</option>
                            <option value="reduce">Reduce Stock (Decrease quantity)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="adjustQuantity" class="form-control" min="0" required>
                        <small class="text-muted" id="actionHint">Enter the quantity</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason for adjustment (e.g., 'Received new shipment', 'Stock count correction')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Adjust Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfer Stock Modal -->
<div class="modal fade" id="transferStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right"></i> Transfer Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferStockForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="transferProductId" class="form-select" required>
                            <option value="">Select Product</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">From Warehouse <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" id="fromWarehouseId" class="form-select" required>
                            <option value="">Select Source Warehouse</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">To Warehouse <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" id="toWarehouseId" class="form-select" required>
                            <option value="">Select Destination Warehouse</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="transferQuantity" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason for transfer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Transfer Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let inventoryTable;
let currentWarehouse = '';
let productsData = [];

$(document).ready(function() {
    // Initialize DataTable
    initializeDataTable();

    // Load statistics
    loadStatistics();

    // Load products for dropdowns
    loadProducts();

    loadWarehouseCounts();
    // Warehouse tab clicks
    $('.warehouse-tab').on('click', function() {
        $('.warehouse-tab').removeClass('active');
        $(this).addClass('active');
        currentWarehouse = $(this).data('warehouse');

        // Destroy and recreate table with correct columns
        if (inventoryTable) {
            inventoryTable.destroy();
        }

        // Update table headers
        if (currentWarehouse) {
            $('#inventoryTable thead tr').html(`
                <th>Product / Variant</th>
                <th>Quantity</th>
                <th>Available</th>
                <th>Reserved</th>
                <th>Location</th>
                <th>Actions</th>
            `);
        } else {
            $('#inventoryTable thead tr').html(`
                <th>Product / Variant</th>
                <th>Total Stock</th>
                <th>Available</th>
                <th>Reserved</th>
                <th>Warehouses</th>
                <th>Actions</th>
            `);
        }

        initializeDataTable();
        loadStatistics();
    });

    // Product change - show current stock
    $('#adjustProductId').on('change', function() {
        const productId = $(this).val();
        const warehouseId = $('#adjustWarehouseId').val();

        if (productId) {
            // Load product details including variants
            loadProductVariants(productId);

            if (warehouseId) {
                loadProductStock(productId, warehouseId);
            }
        } else {
            $('#variantSelectionDiv').hide();
            $('#currentStockInfo').hide();
        }
    });

    function loadProductVariants(productId) {
        $.ajax({
            url: '{{ route("admin.products.ajax-details", ":id") }}'.replace(':id', productId),
            method: 'GET',
            success: function(response) {
                if (response.success && response.product) {
                    const product = response.product;

                    // Check if product has variants
                    if (product.has_variants && product.variants && product.variants.length > 0) {
                        let variantOptions = '<option value="">Main Product (No Variant)</option>';

                        product.variants.forEach(variant => {
                            // Check status from the nested status object
                            const isActive = variant.status && variant.status.key_code === 'VARIANT_ACTIVE';

                            if (isActive) {
                                variantOptions += `<option value="${variant.id}">
                                    ${variant.variant_name}: ${variant.variant_value} (SKU: ${variant.sku})
                                </option>`;
                            }
                        });

                        $('#adjustVariantId').html(variantOptions);
                        $('#variantSelectionDiv').slideDown();
                    } else {
                        $('#variantSelectionDiv').hide();
                        $('#adjustVariantId').val('');
                    }
                }
            },
            error: function(xhr) {
                console.error('Failed to load product variants:', xhr);
            }
        });
    }

    // Variant change - update stock display
    $('#adjustVariantId').on('change', function() {
        const productId = $('#adjustProductId').val();
        const variantId = $(this).val();
        const warehouseId = $('#adjustWarehouseId').val();

        if (productId && warehouseId) {
            loadProductStock(productId, warehouseId, variantId);
        }
    });

    // Warehouse change - update stock display
    $('#adjustWarehouseId').on('change', function() {
        const productId = $('#adjustProductId').val();
        const warehouseId = $(this).val();

        if (productId && warehouseId) {
            loadProductStock(productId, warehouseId);
        } else {
            $('#currentStockInfo').hide();
        }
    });

    // Action type change - update hint
    $('#adjustActionType').on('change', function() {
        const actionType = $(this).val();
        updateActionHint(actionType);
    });
});

// Initialize DataTable
function initializeDataTable() {
    let columns;

    if (currentWarehouse) {
        columns = [
            { data: 'product_info', name: 'product_info', orderable: false },
            { data: 'quantity', name: 'quantity', orderable: false },
            { data: 'available', name: 'available', orderable: false },
            { data: 'reserved', name: 'reserved', orderable: false },
            { data: 'location', name: 'location', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ];
    } else {
        columns = [
            { data: 'product_info', name: 'product_info', orderable: false },
            { data: 'total_stock', name: 'total_stock', orderable: false },
            { data: 'available', name: 'available', orderable: false },
            { data: 'reserved', name: 'reserved', orderable: false },
            { data: 'warehouses', name: 'warehouses', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ];
    }

    inventoryTable = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.inventory.data") }}',
            data: function(d) {
                d.warehouse_id = currentWarehouse;
                d.stock_status = $('#filterStockStatus').val();
                d.search = $('#filterSearch').val();
            }
        },
        columns: columns,
        ordering: false,
        pageLength: 50,
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading...',
            emptyTable: 'No inventory data available'
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });
}
// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.inventory.statistics") }}',
        data: { warehouse_id: currentWarehouse },
        success: function(stats) {
            $('#statTotalProducts').text(stats.total_products || 0);
            $('#statInStock').text(stats.in_stock || 0);
            $('#statLowStock').text(stats.low_stock || 0);
            $('#statOutOfStock').text(stats.out_of_stock || 0);
        },
        error: function(xhr) {
            console.error('Failed to load statistics:', xhr);
        }
    });
}

// Load products for dropdowns - FIXED
function loadProducts() {
    $.ajax({
        url: '{{ route("admin.products.ajax-list") }}',  // FIXED: Correct route
        method: 'GET',
        data: {
            track_inventory: 1,
            limit: 500  // Load more products
        },
        success: function(response) {
            if (response.success && response.products) {
                productsData = response.products;

                let options = '<option value="">Select Product</option>';
                response.products.forEach(product => {
                    options += `<option value="${product.id}">${product.name} (${product.sku})</option>`;
                });

                $('#adjustProductId, #transferProductId').html(options);
                console.log('Products loaded:', response.products.length);
            } else {
                console.warn('No products found in response');
            }
        },
        error: function(xhr) {
            console.error('Failed to load products:', xhr);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load products. Please refresh the page.',
                confirmButtonColor: '#5B914C'
            });
        }
    });
}

// Load product stock for selected warehouse
function loadProductStock(productId, warehouseId, variantId = null) {
    $.ajax({
        url: '{{ route("admin.products.ajax-details", ":id") }}'.replace(':id', productId),
        method: 'GET',
        success: function(response) {
            console.log('API Response:', response); // Debug log

            if (response.success && response.product) {
                const product = response.product;
                let currentStock = 0;
                let stockInfo = '';

                if (variantId) {
                    // Show variant stock
                    const variant = product.variants?.find(v => v.id == variantId);
                    if (variant) {
                        const variantStock = variant.warehouse_stock?.find(ws => ws.warehouse_id == warehouseId);

                        if (variantStock) {
                            currentStock = variantStock.quantity || 0;
                            stockInfo = `
                                <strong>Variant:</strong> ${variant.variant_name}: ${variant.variant_value}<br>
                                Available: ${variantStock.available_quantity || 0} | Reserved: ${variantStock.reserved_quantity || 0}
                            `;
                        } else {
                            currentStock = variant.stock_quantity || 0;
                            stockInfo = `
                                <strong>Variant:</strong> ${variant.variant_name}: ${variant.variant_value}<br>
                                <i class="bi bi-info-circle"></i> No stock in this warehouse (Overall Variant Qty: ${variant.stock_quantity || 0})
                            `;
                        }
                    } else {
                        currentStock = 0;
                        stockInfo = '<span class="text-danger">Variant not found</span>';
                    }
                } else {
                    // Show main product stock
                    if (product.has_variants) {
                        // For products with variants, show total variant stock
                        const totalVariantStock = product.total_variant_stock || 0;
                        currentStock = totalVariantStock;
                        stockInfo = `
                            <i class="bi bi-collection"></i> Product with variants<br>
                            Total across all variants: ${totalVariantStock}
                        `;
                    } else {
                        // For simple products, check warehouse stock
                        const warehouseStock = product.warehouse_stock?.find(ws => ws.warehouse_id == warehouseId);

                        if (warehouseStock) {
                            currentStock = warehouseStock.quantity || 0;
                            stockInfo = `
                                Available: ${warehouseStock.available_quantity || 0} | Reserved: ${warehouseStock.reserved_quantity || 0}
                            `;
                        } else {
                            currentStock = product.stock_quantity || 0;
                            stockInfo = `
                                <i class="bi bi-info-circle"></i> No stock in this warehouse (Overall Product Qty: ${product.stock_quantity || 0})
                            `;
                        }
                    }
                }

                $('#currentStockValue').text(currentStock);
                $('#warehouseStockInfo').html(stockInfo);
                $('#currentStockInfo').slideDown();

                if (warehouseStock && warehouseStock.location) {
                    $('#adjustLocation').val(warehouseStock.location);
                } else {
                    $('#adjustLocation').val('');
                }

            } else {
                console.error('API response indicates failure:', response);
                $('#currentStockInfo').hide();
            }
        },
        error: function(xhr) {
            console.error('Failed to load product details:', xhr);
            $('#currentStockInfo').hide();

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load product stock information',
                confirmButtonColor: '#5B914C'
            });
        }
    });
}

// Update action hint based on action type
function updateActionHint(actionType) {
    let hint = '';
    switch(actionType) {
        case 'set':
            hint = 'This will replace the current stock with the entered quantity';
            break;
        case 'add':
            hint = 'This will add the entered quantity to current stock';
            break;
        case 'reduce':
            hint = 'This will subtract the entered quantity from current stock';
            break;
    }
    $('#actionHint').text(hint);
}

// Load warehouse counts
function loadWarehouseCounts() {
    $.ajax({
        url: '{{ route("admin.inventory.warehouse-counts") }}',
        method: 'GET',
        success: function(counts) {
            // Update all warehouse counts
            $.each(counts, function(key, count) {
                if (key === 'all') {
                    $('#count-all').text(count);
                } else {
                    $('#count-' + key).text(count);
                }
            });
        },
        error: function(xhr) {
            console.error('Failed to load warehouse counts:', xhr);
        }
    });
}

// Apply filters
function applyFilters() {
    inventoryTable.ajax.reload();
}

// Reset filters
function resetFilters() {
    $('#filterStockStatus').val('');
    $('#filterSearch').val('');
    inventoryTable.ajax.reload();
}

// Refresh data
function refreshData() {
    inventoryTable.ajax.reload();
    loadStatistics();
    loadWarehouseCounts();
}

// Open adjust modal
function openAdjustModal() {
    $('#adjustStockForm')[0].reset();
    $('#currentStockInfo').hide();
    $('#adjustStockModal').modal('show');
}

// Open transfer modal
function openTransferModal() {
    $('#transferStockForm')[0].reset();
    $('#transferStockModal').modal('show');
}

// Handle adjust stock form submission
$('#adjustStockForm').on('submit', function(e) {
    e.preventDefault();

    const formData = $(this).serialize();

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
                didOpen: () => {
                    Swal.showLoading();
                }
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
                $('#currentStockInfo').hide();
                refreshData();
            }
        },
        error: function(xhr) {
            let errorMessage = 'Failed to adjust stock';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Error!',
                html: errorMessage
            });
        }
    });
});

// Handle transfer stock form submission
$('#transferStockForm').on('submit', function(e) {
    e.preventDefault();

    // Validation: From and To warehouses must be different
    const fromWarehouse = $('#fromWarehouseId').val();
    const toWarehouse = $('#toWarehouseId').val();

    if (fromWarehouse === toWarehouse) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Selection',
            text: 'Source and destination warehouses must be different',
            confirmButtonColor: '#5B914C'
        });
        return;
    }

    const formData = $(this).serialize();

    $.ajax({
        url: '{{ route("admin.inventory.transfer") }}',
        type: 'POST',
        data: formData,
        beforeSend: function() {
            $('#transferStockModal').modal('hide');
            Swal.fire({
                title: 'Transferring Stock...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
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
                $('#transferStockForm')[0].reset();
                refreshData();
            }
        },
        error: function(xhr) {
            let errorMessage = 'Failed to transfer stock';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Error!',
                html: errorMessage
            });
        }
    });
});

// Adjust stock from table (delegated event)
$(document).on('click', '.adjust-product-stock', function() {
    const productId = $(this).data('id');
    const productName = $(this).data('name');
    const variantId = $(this).data('variant-id'); // Get variant ID if exists
    const warehouseId = $(this).data('warehouse-id'); // Get warehouse ID if exists

    // Set product
    $('#adjustProductId').val(productId).trigger('change');

    // Wait for variants to load, then set variant if exists
    if (variantId) {
        setTimeout(function() {
            $('#adjustVariantId').val(variantId).trigger('change');
        }, 500);
    }

    // Set warehouse if exists
    if (warehouseId) {
        $('#adjustWarehouseId').val(warehouseId);
    }

    $('#adjustStockModal').modal('show');
});
// Cleanup deleted products button
$('#cleanupBtn').on('click', function() {
    Swal.fire({
        title: 'Cleanup Orphaned Stock Records?',
        html: `
            <div class="text-start">
                <p class="mb-2"><strong>This action will:</strong></p>
                <ul class="text-muted">
                    <li>Find all stock records linked to <strong>deleted products</strong></li>
                    <li>Permanently <strong>remove these orphaned records</strong> from the database</li>
                    <li>Clean up inventory data to prevent errors</li>
                </ul>
                <p class="mt-3 mb-2"><strong class="text-danger">⚠️ Warning:</strong></p>
                <ul class="text-danger">
                    <li>This action <strong>cannot be undone</strong></li>
                    <li>Stock history for deleted products will be lost</li>
                    <li>No products will be deleted, only their stock records</li>
                </ul>
                <p class="mt-3 text-info">
                    <i class="bi bi-info-circle"></i>
                    <em>Active products and their stock will NOT be affected</em>
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f0ad4e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Clean Up Now',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'swal-wide',
            confirmButton: 'btn btn-warning',
            cancelButton: 'btn btn-secondary'
        },
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: '{{ route("admin.inventory.cleanup-orphaned") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            }).then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Cleanup failed');
                }
                return response;
            }).catch(error => {
                Swal.showValidationMessage(
                    `Request failed: ${error.message || error.responseJSON?.message || 'Unknown error'}`
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const response = result.value;

            Swal.fire({
                title: 'Cleanup Complete!',
                html: `
                    <div class="text-center">
                        <p class="mb-3">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        </p>
                        <p class="mb-2"><strong>${response.message}</strong></p>
                        <p class="text-muted">
                            Removed <span class="badge bg-success">${response.count}</span> orphaned record(s)
                        </p>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'OK'
            }).then(() => {
                // Reload the DataTable to show updated results
                $('#inventoryTable').DataTable().ajax.reload();
            });
        }
    });
});
</script>
@endpush
