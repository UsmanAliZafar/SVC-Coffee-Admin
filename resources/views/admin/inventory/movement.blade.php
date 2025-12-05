@extends('admin.layouts.app')
{{-- resources/views/admin/inventory/movement.blade.php --}}
@section('title', 'Inventory Movement History')

@push('styles')
<style>
    .filter-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
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

    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .movement-type-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-clock-history"></i> Inventory Movement History</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Movement History</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-outline-success" onclick="exportMovements()">
                <i class="bi bi-download"></i> Export
            </button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <!-- Movement Type Legend -->
    <div class="movement-type-legend">
        <div class="legend-item">
            <span class="badge bg-primary">Adjustment</span>
            <span>Manual adjustments</span>
        </div>
        <div class="legend-item">
            <span class="badge bg-success">Purchase</span>
            <span>Purchase orders</span>
        </div>
        <div class="legend-item">
            <span class="badge bg-info">Sale</span>
            <span>Orders fulfilled</span>
        </div>
        <div class="legend-item">
            <span class="badge bg-warning">Return</span>
            <span>Customer returns</span>
        </div>
        <div class="legend-item">
            <span class="badge bg-secondary">Transfer</span>
            <span>Warehouse transfers</span>
        </div>
        <div class="legend-item">
            <span class="badge bg-danger">Damaged/Lost</span>
            <span>Damaged or lost items</span>
        </div>
        <div class="legend-item">
            <span class="badge bg-dark">Sync</span>
            <span>Warehouse sync</span>
        </div>
    </div>

    <div class="row">
        <!-- Filters -->
        <div class="col-lg-12">
            <div class="filter-section">
                <h5 class="section-title">
                    <i class="bi bi-funnel"></i> Filters
                </h5>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Warehouse</label>
                        <select id="filterWarehouse" class="form-select">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Movement Type</label>
                        <select id="filterType" class="form-select">
                            <option value="">All Types</option>
                            <option value="adjustment">Adjustment</option>
                            {{-- <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                            <option value="return">Return</option>
                            <option value="transfer">Transfer</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="sync">Sync</option> --}}
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" id="filterDateFrom" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" id="filterDateTo" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-filter" onclick="applyFilters()">
                                <i class="bi bi-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-10">
                        <input type="text" id="filterSearch" class="form-control" placeholder="Search by product name, SKU, or reason...">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Statistics -->
        <div class="col-lg-3">
            <div class="stat-card">
                <div class="stat-label">Total Movements</div>
                <div class="stat-value" id="statTotalMovements">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Stock Added</div>
                <div class="stat-value text-success" id="statStockAdded">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Stock Removed</div>
                <div class="stat-value text-danger" id="statStockRemoved">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Net Change</div>
                <div class="stat-value" id="statNetChange">0</div>
            </div>
        </div>

        <!-- Movements Table -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Movement Records
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="movementsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Warehouse</th>
                                    <th>Change</th>
                                    <th>Stock Levels</th>
                                    <th>By</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movement Details Modal -->
<div class="modal fade" id="movementDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i> Movement Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="movementDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let movementsTable;

$(document).ready(function() {
    // Initialize DataTable
    initializeDataTable();

    // Load statistics
    loadStatistics();

    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));

    $('#filterDateTo').val(today.toISOString().split('T')[0]);
    $('#filterDateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);
});

// Initialize DataTable
function initializeDataTable() {
    movementsTable = $('#movementsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.inventory.movement.data") }}',
            data: function(d) {
                d.warehouse_id = $('#filterWarehouse').val();
                d.type = $('#filterType').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.search = $('#filterSearch').val();
            }
        },
        columns: [
            { data: 'created_info', name: 'created_at' },
            { data: 'product_info', name: 'product_info', orderable: false },
            { data: 'type_badge', name: 'type' },
            { data: 'warehouse_info', name: 'warehouse_info', orderable: false },
            { data: 'quantity_change', name: 'quantity' },
            { data: 'stock_levels', name: 'stock_levels', orderable: false },
            {
                data: 'created_info',
                name: 'created_by',
                render: function(data, type, row) {
                    return data.split('<br>')[1] || '—';
                }
            },
            {
                data: 'reason',
                name: 'reason',
                render: function(data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    return data.length > 50 ? data.substring(0, 50) + '...' : data;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 50,
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Loading...',
            emptyTable: 'No movement records found'
        }
    });
}

// Load statistics
function loadStatistics() {
    const params = {
        warehouse_id: $('#filterWarehouse').val(),
        type: $('#filterType').val(),
        date_from: $('#filterDateFrom').val(),
        date_to: $('#filterDateTo').val()
    };

    $.ajax({
        url: '{{ route("admin.inventory.movement.statistics") }}',
        data: params,
        success: function(stats) {
            $('#statTotalMovements').text(stats.total || 0);
            $('#statStockAdded').text(stats.added || 0);
            $('#statStockRemoved').text(Math.abs(stats.removed || 0));

            const netChange = (stats.added || 0) + (stats.removed || 0);
            $('#statNetChange').text(netChange);

            // Color code net change
            if (netChange > 0) {
                $('#statNetChange').removeClass('text-danger').addClass('text-success');
            } else if (netChange < 0) {
                $('#statNetChange').removeClass('text-success').addClass('text-danger');
            } else {
                $('#statNetChange').removeClass('text-success text-danger');
            }
        }
    });
}

// Apply filters
function applyFilters() {
    movementsTable.ajax.reload();
    loadStatistics();
}

// Reset filters
function resetFilters() {
    $('#filterWarehouse').val('');
    $('#filterType').val('');
    $('#filterSearch').val('');

    // Reset to last 30 days
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));

    $('#filterDateTo').val(today.toISOString().split('T')[0]);
    $('#filterDateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);

    movementsTable.ajax.reload();
    loadStatistics();
}

// Export movements
function exportMovements() {
    const params = new URLSearchParams({
        warehouse_id: $('#filterWarehouse').val(),
        type: $('#filterType').val(),
        date_from: $('#filterDateFrom').val(),
        date_to: $('#filterDateTo').val(),
        search: $('#filterSearch').val()
    });

    window.location.href = '{{ route("admin.inventory.movement.export") }}?' + params.toString();
}

// View movement details (if you want to implement)
function viewMovementDetails(id) {
    $.ajax({
        url: '{{ route("admin.inventory.movement.show", ":id") }}'.replace(':id', id),
        success: function(data) {
            $('#movementDetailsContent').html(data);
            $('#movementDetailsModal').modal('show');
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load movement details'
            });
        }
    });
}

// Search on enter key
$('#filterSearch').on('keypress', function(e) {
    if (e.which === 13) {
        applyFilters();
    }
});

// Auto-apply filters when date changes
$('#filterDateFrom, #filterDateTo').on('change', function() {
    applyFilters();
});
</script>
@endpush
