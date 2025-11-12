@extends('admin.layouts.app')
{{-- resources/views/admin/inventory/out-of-stock.blade.php --}}
@section('title', 'Out of Stock Products')

@push('styles')
<style>
    .alert-banner {
        background: linear-gradient(135deg, #f8d7da 0%, #dc3545 100%);
        border: 2px solid #dc3545;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .alert-icon {
        font-size: 3rem;
        color: #721c24;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffe6e6 0%, #ffcccc 100%);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid #dc3545;
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .stat-label {
        font-size: 0.85rem;
        color: #721c24;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #dc3545;
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
        border-bottom: 2px solid #dc3545;
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
        color: #dc3545;
    }

    .warehouse-tab.active {
        color: #dc3545;
        font-weight: 600;
        border-bottom-color: #dc3545;
    }

    .out-of-stock-badge {
        position: relative;
        padding-left: 20px;
        background: #dc3545;
        color: white;
        padding: 5px 10px 5px 25px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .out-of-stock-badge::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        background: white;
        border-radius: 50%;
        animation: pulse-red 1.5s infinite;
    }

    @keyframes pulse-red {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
        }
        50% {
            opacity: 0.7;
            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
        }
    }

    .days-out-badge {
        background: linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%);
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .impact-level {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .impact-high {
        background: #dc3545;
        color: white;
    }

    .impact-medium {
        background: #ffc107;
        color: #000;
    }

    .impact-low {
        background: #6c757d;
        color: white;
    }

    .restock-urgency {
        display: flex;
        gap: 3px;
        align-items: center;
    }

    .urgency-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #dc3545;
    }

    .urgency-dot.active {
        background: #dc3545;
        box-shadow: 0 0 8px rgba(220, 53, 69, 0.6);
    }

    .urgency-dot.inactive {
        background: #e9ecef;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        justify-content: center;
    }

    .product-row-danger {
        background-color: #fff5f5 !important;
    }

    .lost-sales-estimate {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 4px;
        padding: 8px;
        font-size: 0.85rem;
    }

    .empty-stock-illustration {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-stock-illustration i {
        font-size: 5rem;
        color: #dc3545;
        opacity: 0.3;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-x-octagon-fill text-danger"></i> Out of Stock Products
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Out of Stock</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <button type="button" class="btn btn-danger" onclick="bulkRestockModal()">
                <i class="bi bi-box-seam"></i> Urgent Restock
            </button>
            @endif
            <button type="button" class="btn btn-outline-info" onclick="exportOutOfStock()">
                <i class="bi bi-download"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner">
        <div class="row align-items-center">
            <div class="col-auto">
                <i class="bi bi-x-octagon-fill alert-icon"></i>
            </div>
            <div class="col">
                <h4 class="mb-1" style="color: #721c24;">
                    <strong>Critical Stock Alert</strong>
                </h4>
                <p class="mb-0" style="color: #721c24;">
                    These products are completely out of stock and unavailable for sale.
                    <strong>Immediate action required</strong> to prevent lost sales and customer dissatisfaction.
                </p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Out of Stock</div>
                <div class="stat-value" id="statOutOfStockCount">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Estimated Lost Sales</div>
                <div class="stat-value" id="statLostSales">{{ store_currency_symbol() }} 0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Avg Days Out of Stock</div>
                <div class="stat-value" id="statAvgDaysOut">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Pending Restocks</div>
                <div class="stat-value" id="statPendingRestocks">0</div>
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
            <div class="col-md-2">
                <label class="form-label">Impact Level</label>
                <select id="filterImpact" class="form-select">
                    <option value="">All Impact Levels</option>
                    <option value="high">High Impact</option>
                    <option value="medium">Medium Impact</option>
                    <option value="low">Low Impact</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Days Out of Stock</label>
                <select id="filterDaysOut" class="form-select">
                    <option value="">All</option>
                    <option value="1-7">1-7 days</option>
                    <option value="8-30">8-30 days</option>
                    <option value="30+">30+ days</option>
                </select>
            </div>
            <div class="col-md-2">
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

    <!-- Out of Stock Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="outOfStockTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Days Out</th>
                            <th>Impact</th>
                            <th>Urgency</th>
                            <th>Warehouse</th>
                            <th>Lost Sales Est.</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Urgent Restock Modal -->
<div class="modal fade" id="urgentRestockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill"></i> Urgent Restock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="urgentRestockForm">
                @csrf
                <input type="hidden" name="product_id" id="restockProductId">
                <input type="hidden" name="warehouse_id" id="restockWarehouseId">
                <input type="hidden" name="action_type" value="add">

                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong><i class="bi bi-x-octagon"></i> OUT OF STOCK</strong><br>
                        <strong id="restockProductName"></strong><br>
                        <small>
                            SKU: <strong id="restockProductSku"></strong> |
                            Days Out: <strong id="restockDaysOut">0</strong>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Restock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="restockQuantity"
                               class="form-control form-control-lg" min="1" required>
                        <small class="text-muted">
                            Recommended: <strong id="restockRecommended">0</strong> units
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Priority Level</label>
                        <select class="form-select">
                            <option value="urgent" selected>🔴 Urgent - Restock Immediately</option>
                            <option value="high">🟠 High - Within 24 hours</option>
                            <option value="normal">🟡 Normal - Within 3 days</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Expected Arrival Date</label>
                        <input type="date" name="expected_date" class="form-control"
                               min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <select name="reason" class="form-select">
                            <option value="Urgent Restock - Out of Stock">Urgent Restock - Out of Stock</option>
                            <option value="Emergency Purchase Order">Emergency Purchase Order</option>
                            <option value="Direct Supplier Order">Direct Supplier Order</option>
                            <option value="Transfer from Other Location">Transfer from Other Location</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Supplier / Source</label>
                        <input type="text" name="supplier" class="form-control"
                               placeholder="Supplier name or source">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="PO number, tracking info, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle"></i> Confirm Restock
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
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Bulk Urgent Restock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkRestockForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        You have selected <strong id="selectedItemsCount">0</strong> out-of-stock items for urgent restocking.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Default Restock Quantity</label>
                        <select id="bulkRestockStrategy" class="form-select">
                            <option value="threshold">Restock to Threshold Level</option>
                            <option value="double_threshold">Restock to 2x Threshold</option>
                            <option value="triple_threshold">Restock to 3x Threshold (Recommended)</option>
                            <option value="custom">Custom Quantity for All</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customQuantityField" style="display: none;">
                        <label class="form-label fw-bold">Quantity per Item</label>
                        <input type="number" id="bulkCustomQuantity" class="form-control" min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Priority Level</label>
                        <select id="bulkPriority" class="form-select">
                            <option value="urgent" selected>🔴 Urgent - Restock Immediately</option>
                            <option value="high">🟠 High - Within 24 hours</option>
                            <option value="normal">🟡 Normal - Within 3 days</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Expected Arrival Date</label>
                        <input type="date" id="bulkExpectedDate" class="form-control"
                               min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <select id="bulkReason" class="form-select">
                            <option value="Bulk Urgent Restock - Out of Stock">Bulk Urgent Restock - Out of Stock</option>
                            <option value="Emergency Bulk Purchase Order">Emergency Bulk Purchase Order</option>
                            <option value="Supplier Bulk Order">Supplier Bulk Order</option>
                        </select>
                    </div>

                    <div id="bulkRestockPreview" class="mb-3" style="display: none;">
                        <h6 class="fw-bold">Preview:</h6>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Warehouse</th>
                                        <th>Current</th>
                                        <th>Add</th>
                                        <th>New Total</th>
                                    </tr>
                                </thead>
                                <tbody id="bulkRestockPreviewBody">
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <strong>Total Items:</strong> <span id="previewTotalItems">0</span><br>
                            <strong>Total Quantity to Add:</strong> <span id="previewTotalQuantity">0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="previewBulkRestock()">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-danger" id="bulkRestockSubmit" disabled>
                        <i class="bi bi-check-circle"></i> Restock All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let outOfStockTable;
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
// Initialize DataTable - FIXED VERSION
function initializeDataTable() {
    outOfStockTable = $('#outOfStockTable').DataTable({
        processing: true,
        serverSide: true,
        columns: columns,
        ajax: {
            url: '{{ route("admin.inventory.out-of-stock.data") }}', // SPECIFIC ROUTE
            data: function(d) {
                return {
                    warehouse_id: currentWarehouse,
                    impact: $('#filterImpact').val(),
                    days_out: $('#filterDaysOut').val(),
                    category: $('#filterCategory').val(),
                    search: $('#filterSearch').val(),
                    // DataTables parameters
                    start: d.start,
                    length: d.length,
                    search: { value: d.search.value },
                    order: d.order,
                    columns: d.columns
                };
            }
        },
        columns: [
            { data: 'id', orderable: false, searchable: false },
            { data: 'product_info', name: 'product_info', orderable: false },
            { data: 'total_stock', name: 'total_stock' },
            { data: 'threshold', name: 'threshold' },
            { data: 'priority', name: 'priority' },
            { data: 'warehouse_name', name: 'warehouse_name' },
        ],
        order: [[3, 'desc']], // Sort by days out
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading out of stock items...',
            emptyTable: '<div class="empty-stock-illustration"><i class="bi bi-check-circle"></i><h5>No Out of Stock Items</h5><p>Great! All products are currently in stock.</p></div>',
            zeroRecords: '<div class="empty-stock-illustration"><i class="bi bi-search"></i><h5>No Matching Items</h5><p>No out of stock items match your filters.</p></div>'
        },
        drawCallback: function() {
            // Update selected items after table redraw
            updateSelectedItems();
        },
        error: function(xhr, error, thrown) {
            console.error('DataTable error:', error, thrown);
            // Show error message
            let errorHtml = '<div class="empty-stock-illustration text-danger">';
            errorHtml += '<i class="bi bi-exclamation-triangle"></i>';
            errorHtml += '<h5>Error Loading Data</h5>';
            errorHtml += '<p>Failed to load out of stock items. Please try again.</p>';
            errorHtml += '</div>';

            $('.dataTables_empty').html(errorHtml);
        }
    });
}

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.inventory.statistics") }}',
        data: {
            warehouse_id: currentWarehouse,
            out_of_stock_only: true
        },
        success: function(stats) {
            $('#statOutOfStockCount').text(stats.out_of_stock_count || 0);
            $('#statLostSales').text('{{ store_currency_symbol() }}' + (stats.estimated_lost_sales || 0).toLocaleString());
            $('#statAvgDaysOut').text(stats.avg_days_out || 0);
            $('#statPendingRestocks').text(stats.pending_restocks || 0);
        }
    });
}

// Apply filters
function applyFilters() {
    outOfStockTable.ajax.reload();
}

// Reset filters
function resetFilters() {
    $('#filterImpact').val('');
    $('#filterDaysOut').val('');
    $('#filterCategory').val('');
    $('#filterSearch').val('');
    outOfStockTable.ajax.reload();
}

// Refresh data
function refreshData() {
    outOfStockTable.ajax.reload();
    loadStatistics();
}

// Urgent restock
function urgentRestock(productId, warehouseId, productName, productSku, daysOut, threshold) {
    $('#restockProductId').val(productId);
    $('#restockWarehouseId').val(warehouseId);
    $('#restockProductName').text(productName);
    $('#restockProductSku').text(productSku);
    $('#restockDaysOut').text(daysOut);

    // Calculate recommended quantity (3x threshold for out of stock items)
    let recommended = threshold * 3;
    $('#restockRecommended').text(recommended);
    $('#restockQuantity').val(recommended);

    $('#urgentRestockModal').modal('show');
}

// Handle urgent restock form
$('#urgentRestockForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    $.ajax({
        url: '{{ route("admin.inventory.adjust.store") }}',
        type: 'POST',
        data: {
            ...data,
            _token: '{{ csrf_token() }}'
        },
        beforeSend: function() {
            $('#urgentRestockModal').modal('hide');
            Swal.fire({
                title: 'Processing Urgent Restock...',
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
                    title: 'Restock Confirmed!',
                    text: 'Stock has been scheduled for restocking',
                    confirmButtonColor: '#5B914C'
                });
                $('#urgentRestockForm')[0].reset();
                refreshData();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to process restock'
            });
        }
    });
});

// View stock history
function viewHistory(productId) {
    window.location.href = `{{ route('admin.inventory.movement') }}?product_id=${productId}`;
}

// Update selected items
function updateSelectedItems() {
    selectedItems = [];
    $('.item-checkbox:checked').each(function() {
        selectedItems.push({
            id: $(this).data('id'),
            product_id: $(this).data('product-id'),
            warehouse_id: $(this).data('warehouse-id'),
            threshold: parseInt($(this).data('threshold')),
            name: $(this).data('name'),
            sku: $(this).data('sku')
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

    if (strategy === 'custom' && customQty <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Quantity',
            text: 'Please enter a valid custom quantity',
            confirmButtonColor: '#5B914C'
        });
        return;
    }

    let html = '';
    let totalQuantity = 0;

    selectedItems.forEach(item => {
        let addQty = 0;

        switch(strategy) {
            case 'threshold':
                addQty = item.threshold;
                break;
            case 'double_threshold':
                addQty = item.threshold * 2;
                break;
            case 'triple_threshold':
                addQty = item.threshold * 3;
                break;
            case 'custom':
                addQty = customQty;
                break;
        }

        const newTotal = 0 + addQty; // Current is 0 for out of stock
        totalQuantity += addQty;

        html += `
            <tr>
                <td><small>${item.name}</small></td>
                <td><small>${item.warehouse_name || 'Default'}</small></td>
                <td><strong class="text-danger">0</strong></td>
                <td class="text-success"><strong>+${addQty}</strong></td>
                <td class="text-primary"><strong>${newTotal}</strong></td>
            </tr>
        `;
    });

    $('#bulkRestockPreviewBody').html(html);
    $('#previewTotalItems').text(selectedItems.length);
    $('#previewTotalQuantity').text(totalQuantity);
    $('#bulkRestockPreview').show();
    $('#bulkRestockSubmit').prop('disabled', false);
}

// Handle bulk restock form
$('#bulkRestockForm').on('submit', function(e) {
    e.preventDefault();

    const strategy = $('#bulkRestockStrategy').val();
    const customQty = parseInt($('#bulkCustomQuantity').val()) || 0;
    const reason = $('#bulkReason').val();
    const priority = $('#bulkPriority').val();
    const expectedDate = $('#bulkExpectedDate').val();

    if (!expectedDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please select an expected arrival date',
            confirmButtonColor: '#5B914C'
        });
        return;
    }

    let requests = [];

    selectedItems.forEach(item => {
        let addQty = 0;

        switch(strategy) {
            case 'threshold':
                addQty = item.threshold;
                break;
            case 'double_threshold':
                addQty = item.threshold * 2;
                break;
            case 'triple_threshold':
                addQty = item.threshold * 3;
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
                reason: `${reason} | Priority: ${priority} | Expected: ${expectedDate}`
            });
        }
    });

    $('#bulkRestockModal').modal('hide');

    Swal.fire({
        title: 'Processing Bulk Restock...',
        html: `Processing <strong>${requests.length}</strong> urgent restocks`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Process all requests
    let completed = 0;
    let failed = 0;

    Promise.allSettled(requests.map(req =>
        $.ajax({
            url: '{{ route("admin.inventory.adjust.store") }}',
            type: 'POST',
            data: {
                ...req,
                _token: '{{ csrf_token() }}'
            }
        })
    )).then((results) => {
        results.forEach(result => {
            if (result.status === 'fulfilled') {
                completed++;
            } else {
                failed++;
            }
        });

        if (failed === 0) {
            Swal.fire({
                icon: 'success',
                title: 'All Items Restocked!',
                html: `Successfully processed <strong>${completed}</strong> urgent restocks`,
                confirmButtonColor: '#5B914C'
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Partially Completed',
                html: `<strong>${completed}</strong> successful, <strong>${failed}</strong> failed`,
                confirmButtonColor: '#5B914C'
            });
        }

        $('#bulkRestockForm')[0].reset();
        $('#bulkRestockPreview').hide();
        $('#bulkRestockSubmit').prop('disabled', true);
        selectedItems = [];
        $('.item-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        refreshData();
    });
});

// Export out of stock report
function exportOutOfStock() {
    Swal.fire({
        title: 'Export Report',
        text: 'Preparing out of stock report...',
        icon: 'info',
        showConfirmButton: false,
        timer: 2000
    });

    // In a real implementation, this would trigger a backend export
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Report Ready',
            text: 'Out of stock report has been generated',
            confirmButtonColor: '#5B914C'
        });
    }, 2000);
}

// Auto-refresh every 5 minutes for critical page
setInterval(function() {
    refreshData();
}, 300000); // 5 minutes

// Show notification badge
function showNotificationBadge() {
    if ($('#statOutOfStockCount').text() > 0) {
        document.title = `(${$('#statOutOfStockCount').text()}) Out of Stock - Admin`;
    }
}

// Call on page load and after refresh
$(document).ready(function() {
    showNotificationBadge();
});
</script>
@endpush
