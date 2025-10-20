@extends('admin.layouts.app')
{{-- resources/views/admin/warehouses/index.blade.php --}}
@section('title', 'Warehouse Management')

@push('styles')
<style>
    .warehouse-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid #5B914C;
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
    }

    .warehouse-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(91, 145, 76, 0.3);
    }

    .warehouse-icon {
        font-size: 3rem;
        color: #5B914C;
        margin-bottom: 15px;
    }

    .warehouse-name {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }

    .warehouse-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }

    .warehouse-stat {
        text-align: center;
    }

    .warehouse-stat-value {
        font-size: 1.3rem;
        font-weight: bold;
        color: #5B914C;
    }

    .warehouse-stat-label {
        font-size: 0.75rem;
        color: #666;
        text-transform: uppercase;
    }

    .filter-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .btn-add-warehouse {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-add-warehouse:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.4);
        color: white;
    }

    .default-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ffc107;
        color: #000;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: bold;
    }

    .warehouse-card-container {
        position: relative;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }

    .status-active {
        background-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
    }

    .status-inactive {
        background-color: #6c757d;
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
            <button type="button" class="btn btn-add-warehouse" onclick="window.location.href='{{ route('admin.warehouses.create') }}'">
                <i class="bi bi-plus-circle"></i> Add New Warehouse
            </button>
            @endif
            <button type="button" class="btn btn-outline-secondary" onclick="toggleView()">
                <i class="bi bi-grid" id="viewIcon"></i> <span id="viewText">Table View</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4" id="quickStats">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0" id="totalWarehouses">0</h3>
                    <small class="text-muted">Total Warehouses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0" id="activeWarehouses">0</h3>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-boxes text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0" id="totalStock">0</h3>
                    <small class="text-muted">Total Stock Units</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0" id="totalValue">$0</h3>
                    <small class="text-muted">Total Stock Value</small>
                </div>
            </div>
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
                <input type="text" id="filterSearch" class="form-control" placeholder="Search by name, code, or city...">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="resetFilters()">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                    <button type="button" class="btn btn-outline-success flex-fill" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card View (Default) -->
    <div id="cardView" style="display: none;">
        <div class="row" id="warehouseCards">
            <!-- Cards will be loaded here via AJAX -->
        </div>
    </div>

    <!-- Table View -->
    <div id="tableView">
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
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
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
let currentView = 'table'; // 'table' or 'card'

$(document).ready(function() {
    // Initialize DataTable
    initializeDataTable();

    // Load quick stats
    loadQuickStats();

    // Show table view by default
    $('#tableView').show();
});

// Initialize DataTable
function initializeDataTable() {
    warehousesTable = $('#warehousesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.warehouses.data") }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.search = $('#filterSearch').val();
            }
        },
        columns: [
            { data: 'info', name: 'name', orderable: true },
            { data: 'location', name: 'city', orderable: false },
            { data: 'contact', name: 'email', orderable: false },
            { data: 'stock_info', name: 'stock_info', orderable: false },
            { data: 'alerts', name: 'alerts', orderable: false },
            { data: 'priority', name: 'priority' },
            { data: 'status', name: 'is_active' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']], // Sort by priority
        pageLength: 25,
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading...',
            emptyTable: 'No warehouses found'
        }
    });
}

// Load quick stats
function loadQuickStats() {
    $.ajax({
        url: '{{ route("admin.warehouses.data") }}',
        data: { get_stats: true },
        success: function(response) {
            if (response.stats) {
                $('#totalWarehouses').text(response.stats.total || 0);
                $('#activeWarehouses').text(response.stats.active || 0);
                $('#totalStock').text((response.stats.total_stock || 0).toLocaleString());
                $('#totalValue').text('$' + (response.stats.total_value || 0).toLocaleString());
            }
        }
    });
}

// Toggle between card and table view
function toggleView() {
    if (currentView === 'table') {
        currentView = 'card';
        $('#tableView').hide();
        $('#cardView').show();
        $('#viewIcon').removeClass('bi-grid').addClass('bi-table');
        $('#viewText').text('Table View');
        loadCardView();
    } else {
        currentView = 'table';
        $('#cardView').hide();
        $('#tableView').show();
        $('#viewIcon').removeClass('bi-table').addClass('bi-grid');
        $('#viewText').text('Card View');
    }
}

// Load card view
function loadCardView() {
    $.ajax({
        url: '{{ route("admin.warehouses.data") }}',
        data: {
            view: 'cards',
            status: $('#filterStatus').val(),
            search: $('#filterSearch').val()
        },
        success: function(response) {
            let html = '';

            if (response.data && response.data.length > 0) {
                response.data.forEach(warehouse => {
                    html += generateWarehouseCard(warehouse);
                });
            } else {
                html = '<div class="col-12"><div class="alert alert-info text-center">No warehouses found</div></div>';
            }

            $('#warehouseCards').html(html);
        }
    });
}

// Generate warehouse card HTML
function generateWarehouseCard(warehouse) {
    const statusClass = warehouse.is_active ? 'status-active' : 'status-inactive';
    const defaultBadge = warehouse.is_default ? '<span class="default-badge"><i class="bi bi-star-fill"></i> Default</span>' : '';

    return `
        <div class="col-md-4 mb-4">
            <div class="warehouse-card-container">
                ${defaultBadge}
                <div class="warehouse-card" onclick="window.location.href='{{ route('admin.warehouses.show') }}/${warehouse.id}'">
                    <div class="warehouse-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="warehouse-name">
                        <span class="status-indicator ${statusClass}"></span>
                        ${warehouse.name}
                    </div>
                    <small class="text-muted">${warehouse.code}</small>

                    <div class="warehouse-stats">
                        <div class="warehouse-stat">
                            <div class="warehouse-stat-value">${warehouse.total_stock || 0}</div>
                            <div class="warehouse-stat-label">Stock</div>
                        </div>
                        <div class="warehouse-stat">
                            <div class="warehouse-stat-value">${warehouse.product_count || 0}</div>
                            <div class="warehouse-stat-label">Products</div>
                        </div>
                        <div class="warehouse-stat">
                            <div class="warehouse-stat-value text-success">$${(warehouse.total_value || 0).toLocaleString()}</div>
                            <div class="warehouse-stat-label">Value</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Apply filters
function applyFilters() {
    if (currentView === 'table') {
        warehousesTable.ajax.reload();
    } else {
        loadCardView();
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
    if (currentView === 'table') {
        warehousesTable.ajax.reload();
    } else {
        loadCardView();
    }
    loadQuickStats();
}

// Toggle warehouse status
function toggleWarehouseStatus(warehouseId) {
    Swal.fire({
        title: 'Confirm Status Change',
        text: 'Are you sure you want to toggle this warehouse status?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, toggle it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.toggle-status", "") }}/' + warehouseId,
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
                        text: xhr.responseJSON?.message || 'Failed to update status'
                    });
                }
            });
        }
    });
}

// Set default warehouse
function setDefaultWarehouse(warehouseId) {
    Swal.fire({
        title: 'Set as Default Warehouse?',
        text: 'This will be used as the default warehouse for new stock',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, set as default!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.set-default", "") }}/' + warehouseId,
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
                        text: xhr.responseJSON?.message || 'Failed to set default'
                    });
                }
            });
        }
    });
}

// Delete warehouse
function deleteWarehouse(warehouseId) {
    Swal.fire({
        title: 'Delete Warehouse?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.destroy", "") }}/' + warehouseId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
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
        }
    });
}
</script>
@endpush
