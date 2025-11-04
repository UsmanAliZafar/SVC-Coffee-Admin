@extends('admin.layouts.app')
{{-- resources/views/admin/warehouses/index.blade.php --}}
@section('title', 'Warehouse Management')

@push('styles')
<style>
    #tableView {
        min-height: 200px;
    }

    #warehousesTable {
        width: 100% !important;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .dataTables_wrapper {
        width: 100%;
    }
    .warehouse-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }

    .warehouse-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(91, 145, 76, 0.15);
        border-color: #5B914C;
    }

    .warehouse-card.default-warehouse {
        border-color: #5B914C;
        background: linear-gradient(135deg, #f0f7ed 0%, #e8f5e0 100%);
    }

    .warehouse-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .warehouse-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .warehouse-code {
        font-size: 0.875rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .warehouse-location {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .warehouse-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 15px;
    }

    .stat-item {
        background: white;
        padding: 10px;
        border-radius: 6px;
        text-align: center;
        border: 1px solid #e9ecef;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #5B914C;
        display: block;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .warehouse-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .warehouse-actions .btn {
        flex: 1;
        min-width: 80px;
    }

    .view-mode-toggle {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 5px;
        display: inline-flex;
        gap: 5px;
    }

    .view-mode-btn {
        padding: 8px 16px;
        border: none;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s;
    }

    .view-mode-btn.active {
        background: #5B914C;
        color: white;
    }

    .view-mode-btn:hover:not(.active) {
        background: #f8f9fa;
    }

    .table-view {
        display: none;
    }

    .card-view {
        display: block;
    }

    .alert-badges {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .priority-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #5B914C;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .quick-stat-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
    }

    .quick-stat-card:hover {
        border-color: #5B914C;
        transform: translateY(-2px);
    }

    .quick-stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin-bottom: 5px;
    }

    .quick-stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-building"></i> Warehouse Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Warehouses</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('admin.warehouses.create') }}'">
                <i class="bi bi-plus-circle"></i> Add Warehouse
            </button>
            @endif
            <div class="view-mode-toggle ms-2 d-inline-flex">
                <button class="view-mode-btn active" data-view="card">
                    <i class="bi bi-grid-3x3-gap"></i> Cards
                </button>
                <button class="view-mode-btn" data-view="table">
                    <i class="bi bi-table"></i> Table
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Statistics -->
    <div class="quick-stats">
        <div class="quick-stat-card">
            <div class="quick-stat-value" id="totalWarehouses">0</div>
            <div class="quick-stat-label">Total Warehouses</div>
        </div>
        <div class="quick-stat-card">
            <div class="quick-stat-value text-success" id="activeWarehouses">0</div>
            <div class="quick-stat-label">Active</div>
        </div>
        <div class="quick-stat-card">
            <div class="quick-stat-value text-primary" id="totalStockUnits">0</div>
            <div class="quick-stat-label">Total Stock Units</div>
        </div>
        <div class="quick-stat-card">
            <div class="quick-stat-value text-info" id="totalStockValue">{{ store_currency_symbol() }}0</div>
            <div class="quick-stat-label">Total Stock Value</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select id="filterStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" id="filterSearch" class="form-control" placeholder="Search by name, code, or location...">
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2">
                <button type="button" class="btn btn-primary" onclick="applyFilters()" style="background-color: #5B914C; border-color: #5B914C;">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
                <button type="button" class="btn btn-outline-success" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Card View -->
    <div id="cardView" class="card-view">
        <div class="row" id="warehousesGrid">
            <!-- Warehouses will be loaded here via AJAX -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading warehouses...</p>
            </div>
        </div>
    </div>

    <!-- Table View -->
    <div id="tableView" class="table-view">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="warehousesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Location</th>
                                <th>Contact</th>
                                <th>Stock Info</th>
                                <th>Alerts</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let warehousesTable;
let currentView = 'card';
let tableInitialized = false; // Add this flag

$(document).ready(function() {
    // Load quick statistics
    loadQuickStats();

    // Load card view by default
    loadCardView();

    // DO NOT initialize table here anymore
    // initializeTable(); // REMOVE THIS LINE

    // View mode toggle
    $('.view-mode-btn').on('click', function() {
        const view = $(this).data('view');
        switchView(view);
    });
});

// Load quick statistics
function loadQuickStats() {
    $.ajax({
        url: '{{ route("admin.warehouses.data") }}',
        type: 'GET',
        data: { get_stats: true },
        success: function(response) {
            console.log('Stats response:', response);
            if (response.stats) {
                $('#totalWarehouses').text(response.stats.total || 0);
                $('#activeWarehouses').text(response.stats.active || 0);
                $('#totalStockUnits').text(formatNumber(response.stats.total_stock || 0));
                $('#totalStockValue').text(store_currency_symbol() + formatNumber(response.stats.total_value || 0, 2));
            }
        },
        error: function(xhr) {
            console.error('Failed to load statistics:', xhr);
            console.error('Response:', xhr.responseText);
        }
    });
}

// Load card view
function loadCardView() {
    const filters = getFilters();

    $.ajax({
        url: '{{ route("admin.warehouses.data") }}',
        data: { ...filters, view: 'card' },
        beforeSend: function() {
            $('#warehousesGrid').html(`
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading warehouses...</p>
                </div>
            `);
        },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                let html = '';
                response.data.forEach(warehouse => {
                    html += generateWarehouseCard(warehouse);
                });
                $('#warehousesGrid').html(html);
            } else {
                $('#warehousesGrid').html(`
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-3 text-muted">No warehouses found</p>
                    </div>
                `);
            }
        },
        error: function(xhr) {
            $('#warehousesGrid').html(`
                <div class="col-12 text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-danger">Failed to load warehouses</p>
                </div>
            `);
        }
    });
}

// Generate warehouse card HTML
function generateWarehouseCard(warehouse) {
    const defaultClass = warehouse.is_default ? 'default-warehouse' : '';
    const statusBadge = warehouse.is_active ?
        '<span class="badge bg-success">Active</span>' :
        '<span class="badge bg-secondary">Inactive</span>';

    const lowStock = warehouse.low_stock_count || 0;
    const outOfStock = warehouse.out_of_stock_count || 0;

    let alertBadges = '';
    if (outOfStock > 0) {
        alertBadges += `<span class="badge bg-danger">${outOfStock} Out</span> `;
    }
    if (lowStock > 0) {
        alertBadges += `<span class="badge bg-warning text-dark">${lowStock} Low</span>`;
    }
    if (!alertBadges) {
        alertBadges = '<span class="badge bg-success">All Good</span>';
    }

    const location = warehouse.full_address || '<span class="text-muted">No location</span>';
    const totalStock = formatNumber(warehouse.total_stock || 0);
    const totalValue = formatNumber(warehouse.total_value || 0, 2);
    const productCount = warehouse.stock_count || 0;

    let actionButtons = `
        <a href="/admin/warehouses/${warehouse.id}"
           class="btn btn-sm btn-info" title="View Details">
            <i class="bi bi-eye"></i>
        </a>
    `;

    if (warehouse.can_update) {
        actionButtons += `
            <a href="/admin/warehouses/${warehouse.id}/edit"
               class="btn btn-sm btn-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-warning"
                    onclick="toggleWarehouseStatus('${warehouse.id}')" title="Toggle Status">
                <i class="bi bi-toggle-${warehouse.is_active ? 'on' : 'off'}"></i>
            </button>
        `;
    }

    if (warehouse.can_delete && !warehouse.is_default && !warehouse.has_stock) {
        actionButtons += `
            <button type="button" class="btn btn-sm btn-danger"
                    onclick="deleteWarehouse('${warehouse.id}')" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }

    return `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="warehouse-card ${defaultClass}" onclick="viewWarehouse('${warehouse.id}')">
                ${warehouse.is_default ? '<span class="badge bg-primary position-absolute" style="top: 10px; left: 10px;">Default</span>' : ''}
                <div class="priority-badge">Priority: ${warehouse.priority}</div>

                <div class="warehouse-header">
                    <div>
                        <div class="warehouse-name">${warehouse.name}</div>
                        <span class="warehouse-code">${warehouse.code}</span>
                    </div>
                    <div>${statusBadge}</div>
                </div>

                <div class="warehouse-location">
                    <i class="bi bi-geo-alt"></i>
                    ${location}
                </div>

                <div class="warehouse-stats">
                    <div class="stat-item">
                        <span class="stat-value">${totalStock}</span>
                        <div class="stat-label">Stock Units</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">${productCount}</span>
                        <div class="stat-label">Products</div>
                    </div>
                    <div class="stat-item" style="grid-column: 1 / -1;">
                        <span class="stat-value text-success">${store_currency_symbol()}${totalValue}</span>
                        <div class="stat-label">Total Value</div>
                    </div>
                </div>

                <div class="alert-badges">
                    ${alertBadges}
                </div>

                <hr class="my-3">

                <div class="warehouse-actions" onclick="event.stopPropagation()">
                    ${actionButtons}
                </div>
            </div>
        </div>
    `;
}

// Initialize DataTable
function initializeTable() {
    if (tableInitialized) {
        warehousesTable.ajax.reload();
        warehousesTable.columns.adjust().draw();
        return;
    }

    warehousesTable = $('#warehousesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.warehouses.data") }}',
            data: function(d) {
                return { ...d, ...getFilters() };
            }
        },
        columns: [
            { data: 'info', name: 'name' },
            { data: 'location', name: 'location', orderable: false },
            { data: 'contact', name: 'contact', orderable: false },
            { data: 'stock_info', name: 'stock_info', orderable: false },
            { data: 'alerts', name: 'alerts', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'priority', name: 'priority' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        responsive: true,
        autoWidth: false, // Important!
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading...',
            emptyTable: 'No warehouses found'
        },
        drawCallback: function() {
            // Adjust columns after drawing
            warehousesTable.columns.adjust();
        }
    });

    tableInitialized = true;
}

// Switch view - UPDATED
function switchView(view) {
    currentView = view;

    $('.view-mode-btn').removeClass('active');
    $(`.view-mode-btn[data-view="${view}"]`).addClass('active');

    if (view === 'card') {
        $('#tableView').hide();
        $('#cardView').show();
        loadCardView();
    } else {
        $('#cardView').hide();
        $('#tableView').show();

        // Initialize table only when switching to table view
        setTimeout(function() {
            initializeTable();
        }, 100); // Small delay to ensure the div is visible
    }
}

// Get current filters
function getFilters() {
    return {
        status: $('#filterStatus').val(),
        search: $('#filterSearch').val()
    };
}

// Apply filters
function applyFilters() {
    if (currentView === 'card') {
        loadCardView();
    } else if (tableInitialized) {
        warehousesTable.ajax.reload();
    }
}

// Reset filters
function resetFilters() {
    $('#filterStatus').val('');
    $('#filterSearch').val('');
    applyFilters();
}

// Refresh data
function refreshData() {
    loadQuickStats();
    if (currentView === 'card') {
        loadCardView();
    } else if (tableInitialized) {
        warehousesTable.ajax.reload();
    }
}

// View warehouse details
function viewWarehouse(id) {
    window.location.href = '{{ route("admin.warehouses.show", ":id") }}'.replace(':id', id);
}

// Helper function for currency symbol
function store_currency_symbol() {
    return '{{ store_currency_symbol() }}';
}

// Toggle warehouse status
function toggleWarehouseStatus(id) {
    Swal.fire({
        title: 'Toggle Warehouse Status?',
        text: 'This will activate or deactivate the warehouse',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, toggle it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.toggle-status", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        });
                        refreshData();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to toggle status'
                    });
                }
            });
        }
    });
}

// Set default warehouse
function setDefaultWarehouse(id) {
    Swal.fire({
        title: 'Set as Default Warehouse?',
        text: 'This will be used as the default warehouse for all operations',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, set as default!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.set-default", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        });
                        refreshData();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to set default warehouse'
                    });
                }
            });
        }
    });
}

// Delete warehouse
function deleteWarehouse(id) {
    Swal.fire({
        title: 'Delete Warehouse?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        input: 'checkbox',
        inputPlaceholder: 'I understand this warehouse will be permanently deleted'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: '{{ route("admin.warehouses.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Deleting...',
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
                            title: 'Deleted!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        });
                        refreshData();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete warehouse'
                    });
                }
            });
        } else if (result.isConfirmed && !result.value) {
            Swal.fire('Cancelled', 'Please check the confirmation box', 'info');
        }
    });
}

// Format number helper
function formatNumber(num, decimals = 0) {
    return Number(num).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}
</script>
@endpush
