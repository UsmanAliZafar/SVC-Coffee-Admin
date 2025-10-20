@extends('admin.layouts.app')
{{-- resources/views/admin/inventory/low-stock.blade.php --}}
@section('title', 'Low Stock Products')

@push('styles')
<style>
    .alert-banner {
        background: linear-gradient(135deg, #fff3cd 0%, #ffc107 100%);
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
    }

    .alert-icon {
        font-size: 3rem;
        color: #856404;
    }

    .stat-card {
        background: linear-gradient(135deg, #fff9e6 0%, #ffe8a1 100%);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid #ffc107;
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }

    .stat-label {
        font-size: 0.85rem;
        color: #856404;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #856404;
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
        border-bottom: 2px solid #ffc107;
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
        color: #856404;
    }

    .warehouse-tab.active {
        color: #856404;
        font-weight: 600;
        border-bottom-color: #ffc107;
    }

    .stock-level-bar {
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .stock-level-fill {
        height: 100%;
        background: linear-gradient(90deg, #dc3545 0%, #ffc107 100%);
        transition: width 0.3s;
    }

    .priority-badge {
        position: relative;
        padding-left: 20px;
    }

    .priority-badge::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .priority-critical::before {
        background-color: #dc3545;
    }

    .priority-high::before {
        background-color: #fd7e14;
    }

    .priority-medium::before {
        background-color: #ffc107;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        }
        50% {
            opacity: 0.7;
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
    }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .quick-restock-badge {
        background: #5B914C;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-left: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i> Low Stock Products
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Low Stock</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <button type="button" class="btn btn-warning" onclick="bulkRestockModal()">
                <i class="bi bi-box-seam"></i> Bulk Restock
            </button>
            @endif
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner">
        <div class="row align-items-center">
            <div class="col-auto">
                <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
            </div>
            <div class="col">
                <h4 class="mb-1" style="color: #856404;">
                    <strong>Low Stock Alert</strong>
                </h4>
                <p class="mb-0" style="color: #856404;">
                    The following products have stock levels at or below their low stock threshold.
                    Consider restocking these items soon to avoid stockouts.
                </p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Low Stock Items</div>
                <div class="stat-value" id="statLowStockCount">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Critical (≤5 units)</div>
                <div class="stat-value text-danger" id="statCriticalCount">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total Value at Risk</div>
                <div class="stat-value" id="statValueAtRisk">$0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Avg Days to Restock</div>
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
                <span class="badge bg-primary">Default</span>
            @endif
        </button>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Priority Level</label>
                <select id="filterPriority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="critical">Critical (≤5 units)</option>
                    <option value="high">High (6-10 units)</option>
                    <option value="medium">Medium (>10 units)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select id="filterCategory" class="form-select">
                    <option value="">All Categories</option>
                    <option value="coffee_machines">Coffee Machines</option>
                    <option value="coffee_beans">Coffee Beans</option>
                    <option value="spare_parts">Spare Parts</option>
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
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="lowStockTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Stock Level</th>
                            <th>Priority</th>
                            <th>Warehouse</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Restock Modal -->
<div class="modal fade" id="quickRestockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ffc107; color: #856404;">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Quick Restock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickRestockForm">
                @csrf
                <input type="hidden" name="product_id" id="restockProductId">
                <input type="hidden" name="warehouse_id" id="restockWarehouseId">
                <input type="hidden" name="action_type" value="add">

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong id="restockProductName"></strong><br>
                        <small>
                            Current Stock: <strong id="restockCurrentStock">0</strong> |
                            Threshold: <strong id="restockThreshold">0</strong>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Add Quantity</label>
                        <input type="number" name="quantity" id="restockQuantity"
                               class="form-control form-control-lg" min="1" required>
                        <small class="text-muted">
                            Suggested: <strong id="restockSuggested">0</strong> units
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <select name="reason" class="form-select">
                            <option value="Restock - Low Stock Alert">Restock - Low Stock Alert</option>
                            <option value="Restock - Purchase Order">Restock - Purchase Order</option>
                            <option value="Restock - Emergency">Restock - Emergency</option>
                            <option value="Restock - Other">Restock - Other</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check"></i> Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Restock Modal -->
<div class="modal fade" id="bulkRestockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ffc107; color: #856404;">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Bulk Restock Selected Items
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkRestockForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        You have selected <strong id="selectedItemsCount">0</strong> items for restocking.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Restock Strategy</label>
                        <select id="bulkRestockStrategy" class="form-select">
                            <option value="to_threshold">Restock to Threshold Level</option>
                            <option value="double_threshold">Restock to 2x Threshold</option>
                            <option value="custom">Custom Quantity for All</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customQuantityField" style="display: none;">
                        <label class="form-label fw-bold">Quantity per Item</label>
                        <input type="number" id="bulkCustomQuantity" class="form-control" min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <select id="bulkReason" class="form-select">
                            <option value="Bulk Restock - Low Stock Alert">Bulk Restock - Low Stock Alert</option>
                            <option value="Bulk Restock - Purchase Order">Bulk Restock - Purchase Order</option>
                            <option value="Bulk Restock - Emergency">Bulk Restock - Emergency</option>
                        </select>
                    </div>

                    <div id="bulkRestockPreview" class="mb-3" style="display: none;">
                        <h6 class="fw-bold">Preview:</h6>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Current</th>
                                        <th>Add</th>
                                        <th>New Total</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkRestockPreviewBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="previewBulkRestock()">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-warning" id="bulkRestockSubmit" disabled>
                        <i class="bi bi-check"></i> Restock All
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
    // Initialize DataTable
    initializeDataTable();

    // Load statistics
    loadStatistics();

    // Warehouse tab clicks
    $('.warehouse-tab').on('click', function() {
        $('.warehouse-tab').removeClass('active');
        $(this).addClass('active');
        currentWarehouse = $(this).data('warehouse');
        refreshData();
        loadStatistics();
    });

    // Bulk restock strategy change
    $('#bulkRestockStrategy').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customQuantityField').show();
        } else {
            $('#customQuantityField').hide();
        }
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.item-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedItems();
    });

    // Individual checkbox
    $(document).on('change', '.item-checkbox', function() {
        updateSelectedItems();
    });
});

// Initialize DataTable
function initializeDataTable() {
    lowStockTable = $('#lowStockTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.inventory.data") }}',
            data: function(d) {
                d.warehouse_id = currentWarehouse;
                d.stock_status = 'low_stock';
                d.priority = $('#filterPriority').val();
                d.category = $('#filterCategory').val();
                d.search = $('#filterSearch').val();
            }
        },
        columns: [
        {
            data: 'id',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                let productId = row.product_id || row.id;
                let warehouseId = row.warehouse_id || '';
                let currentStock = row.quantity || row.stock_quantity || parseInt(row.total_stock) || 0;

                // Extract raw threshold value - it's in low_stock_threshold, not threshold
                let threshold = row.low_stock_threshold || 0;

                // Extract raw product name
                let productName = (row.product_name || row.name || 'Product').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                return `<input type="checkbox" class="form-check-input item-checkbox"
                            data-id="${data}"
                            data-product-id="${productId}"
                            data-warehouse-id="${warehouseId}"
                            data-current="${currentStock}"
                            data-threshold="${threshold}"
                            data-name="${productName}">`;
            }
        },
            { data: 'product_info', name: 'product_info', orderable: false },
            {
                data: 'total_stock',           // ✅ FIXED
                name: 'stock_quantity',
                render: function(data) {
                    let className = data <= 5 ? 'text-danger' : (data <= 10 ? 'text-warning' : 'text-secondary');
                    return `<strong class="${className}">${data}</strong>`;
                }
            },
            { data: 'threshold', name: 'threshold' },
            {
                data: 'stock_level',
                orderable: false,
                render: function(data, type, row) {
                    let currentStock = row.stock_quantity || parseInt(row.total_stock) || 0;
                    let threshold = row.low_stock_threshold || 1; // Avoid division by zero
                    let percentage = (currentStock / threshold) * 100;
                    percentage = Math.min(percentage, 100);

                    return `
                        <div class="stock-level-bar">
                            <div class="stock-level-fill" style="width: ${percentage}%"></div>
                        </div>
                        <small class="text-muted">${Math.round(percentage)}% of threshold</small>
                    `;
                }
            },
            {
                data: 'priority',
                render: function(data, type, row) {
                    let priority = 'medium';
                    let label = 'Medium';
                    let badgeClass = 'bg-warning';

                    if (row.quantity <= 5) {
                        priority = 'critical';
                        label = 'Critical';
                        badgeClass = 'bg-danger';
                    } else if (row.quantity <= 10) {
                        priority = 'high';
                        label = 'High';
                        badgeClass = 'bg-warning';
                    }

                    return `<span class="badge ${badgeClass} priority-badge priority-${priority}">${label}</span>`;
                }
            },
            { data: 'warehouse_name', name: 'warehouse_name' },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Extract values from the row object
                    let productId = row.id;  // This is the product ID
                    let productName = row.name || 'Product';

                    // Get threshold from the threshold column we already have
                    let threshold = row.low_stock_threshold || 0;

                    // Get quantity from stock_quantity
                    let quantity = row.stock_quantity || 0;

                    // Get warehouse ID (if available)
                    let warehouseId = '';

                    return `
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-warning"
                                    onclick="quickRestock('${productId}', '${productId}', '${warehouseId}', '${productName.replace(/'/g, "\\'")}', ${quantity}, ${threshold})"
                                    title="Quick Restock">
                                <i class="bi bi-box-seam"></i>
                            </button>
                            <a href="/admin/products/${productId}"
                            class="btn btn-sm btn-info"
                            title="View Product">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading...',
            emptyTable: 'No low stock items found - Great job!'
        }
    });
}

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.inventory.statistics") }}',
        data: {
            warehouse_id: currentWarehouse,
            low_stock_only: true
        },
        success: function(stats) {
            $('#statLowStockCount').text(stats.low_stock_count || 0);
            $('#statCriticalCount').text(stats.critical_count || 0);
            $('#statValueAtRisk').text('$' + (stats.value_at_risk || 0).toLocaleString());
            $('#statAvgDaysRestock').text(stats.avg_days_restock || '—');
        }
    });
}

// Apply filters
function applyFilters() {
    lowStockTable.ajax.reload();
}

// Reset filters
function resetFilters() {
    $('#filterPriority').val('');
    $('#filterCategory').val('');
    $('#filterSearch').val('');
    lowStockTable.ajax.reload();
}

// Refresh data
function refreshData() {
    lowStockTable.ajax.reload();
    loadStatistics();
}

// Quick restock
function quickRestock(id, productId, warehouseId, productName, currentStock, threshold) {
    $('#restockProductId').val(productId);
    $('#restockWarehouseId').val(warehouseId);
    $('#restockProductName').text(productName);
    $('#restockCurrentStock').text(currentStock);
    $('#restockThreshold').text(threshold);

    // Calculate suggested quantity
    let suggested = Math.max(threshold - currentStock, threshold);
    $('#restockSuggested').text(suggested);
    $('#restockQuantity').val(suggested);

    $('#quickRestockModal').modal('show');
}

// Handle quick restock form
$('#quickRestockForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route("admin.inventory.adjust.store") }}',
        type: 'POST',
        data: $(this).serialize(),
        beforeSend: function() {
            $('#quickRestockModal').modal('hide');
            Swal.fire({
                title: 'Restocking...',
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
                    text: 'Stock has been restocked successfully',
                    confirmButtonColor: '#5B914C'
                });
                $('#quickRestockForm')[0].reset();
                refreshData();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to restock'
            });
        }
    });
});

// Update selected items
function updateSelectedItems() {
    selectedItems = [];
    $('.item-checkbox:checked').each(function() {
        selectedItems.push({
            id: $(this).data('id'),
            product_id: $(this).data('product-id'),
            warehouse_id: $(this).data('warehouse-id'),
            current: parseInt($(this).data('current')),
            threshold: parseInt($(this).data('threshold')),
            name: $(this).data('name')
        });
    });

    $('#selectedItemsCount').text(selectedItems.length);
}

// Open bulk restock modal
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

// Preview bulk restock
function previewBulkRestock() {
    const strategy = $('#bulkRestockStrategy').val();
    const customQty = parseInt($('#bulkCustomQuantity').val()) || 0;

    let html = '';
    selectedItems.forEach(item => {
        let addQty = 0;

        switch(strategy) {
            case 'to_threshold':
                addQty = Math.max(item.threshold - item.current, 0);
                break;
            case 'double_threshold':
                addQty = Math.max((item.threshold * 2) - item.current, 0);
                break;
            case 'custom':
                addQty = customQty;
                break;
        }

        const newTotal = item.current + addQty;

        html += `
            <tr>
                <td><small>${item.name}</small></td>
                <td><strong>${item.current}</strong></td>
                <td class="text-success"><strong>+${addQty}</strong></td>
                <td class="text-primary"><strong>${newTotal}</strong></td>
            </tr>
        `;
    });

    $('#bulkRestockPreviewBody').html(html);
    $('#bulkRestockPreview').show();
    $('#bulkRestockSubmit').prop('disabled', false);
}

// Handle bulk restock form
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
            case 'custom':
                addQty = customQty;
                break;
        }

        if (addQty > 0) {
            requests.push({
                product_id: item.product_id,
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
        text: `Processing ${requests.length} items`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Process all requests
    Promise.all(requests.map(req =>
        $.ajax({
            url: '{{ route("admin.inventory.adjust.store") }}',
            type: 'POST',
            data: {
                ...req,
                _token: '{{ csrf_token() }}'
            }
        })
    )).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: `Successfully restocked ${requests.length} items`,
            confirmButtonColor: '#5B914C'
        });
        $('#bulkRestockForm')[0].reset();
        $('#bulkRestockPreview').hide();
        $('#bulkRestockSubmit').prop('disabled', true);
        selectedItems = [];
        $('.item-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        refreshData();
    }).catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Some items failed to restock'
        });
    });
});
</script>
@endpush
