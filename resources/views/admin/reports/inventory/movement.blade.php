{{-- resources/views/admin/reports/inventory/movement.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Inventory Movements - Coffee Admin')

@push('styles')
<style>
    .movement-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .movement-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .summary-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
    }

    .summary-card .icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 15px;
    }

    .summary-card .value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .summary-card .label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .timeline-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .movement-timeline {
        position: relative;
        padding-left: 40px;
        margin-top: 30px;
    }

    .movement-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #5B914C 0%, #e9ecef 100%);
    }

    .movement-item {
        position: relative;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .movement-item:hover {
        transform: translateX(5px);
    }

    .movement-dot {
        position: absolute;
        left: -30px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 0 0 3px;
        z-index: 2;
    }

    .movement-dot.in {
        background: #28a745;
        box-shadow: 0 0 0 3px #28a745;
    }

    .movement-dot.out {
        background: #dc3545;
        box-shadow: 0 0 0 3px #dc3545;
    }

    .movement-dot.adjustment {
        background: #ffc107;
        box-shadow: 0 0 0 3px #ffc107;
    }

    .movement-card {
        background: white;
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .movement-card:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.15);
    }

    .movement-card.in {
        border-left: 4px solid #28a745;
        background: linear-gradient(90deg, #f0fff4 0%, white 10%);
    }

    .movement-card.out {
        border-left: 4px solid #dc3545;
        background: linear-gradient(90deg, #fff5f5 0%, white 10%);
    }

    .movement-card.adjustment {
        border-left: 4px solid #ffc107;
        background: linear-gradient(90deg, #fffef5 0%, white 10%);
    }

    .movement-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .movement-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .movement-type-badge.in {
        background: #d4edda;
        color: #155724;
    }

    .movement-type-badge.out {
        background: #f8d7da;
        color: #721c24;
    }

    .movement-type-badge.adjustment {
        background: #fff3cd;
        color: #856404;
    }

    .movement-quantity {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .movement-quantity.positive {
        color: #28a745;
    }

    .movement-quantity.negative {
        color: #dc3545;
    }

    .movement-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .detail-item {
        text-align: center;
    }

    .detail-value {
        font-weight: bold;
        color: #212529;
        display: block;
        margin-bottom: 3px;
    }

    .detail-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
    }

    .product-info-inline {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .product-thumb {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .product-details {
        flex-grow: 1;
    }

    .product-name-inline {
        font-weight: 700;
        color: #212529;
        margin-bottom: 3px;
    }

    .product-sku-inline {
        font-size: 0.875rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
    }

    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .chart-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .chart-container {
        height: 350px;
        position: relative;
    }

    .movement-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #5B914C;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.15);
    }

    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-card.in .stat-icon {
        background: #d4edda;
        color: #155724;
    }

    .stat-card.out .stat-icon {
        background: #f8d7da;
        color: #721c24;
    }

    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .stat-card .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .date-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #f8f9fa;
        border-radius: 15px;
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
    }

    .reference-link {
        color: #5B914C;
        text-decoration: none;
        font-weight: 600;
    }

    .reference-link:hover {
        text-decoration: underline;
    }

    .notes-box {
        background: #f8f9fa;
        border-left: 3px solid #5B914C;
        padding: 12px 15px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 0.875rem;
        color: #495057;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    @media print {
        .no-print { display: none !important; }
        .movement-item { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .movement-timeline { padding-left: 30px; }
        .movement-details { grid-template-columns: 1fr; }
    }
.variant-badge {
        display: inline-block;
        padding: 3px 8px;
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 5px;
    }
</style>
@endpush

@section('content')
<div class="inventory-movements-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-arrow-left-right text-brand"></i> Inventory Movements
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Movements</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group no-print">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-brand dropdown-toggle d-none" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="exportMovements('pdf')"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportMovements('excel')"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportMovements('csv')"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- ✅ UPDATED: Movement Header Summary with Live Stats -->
    <div class="movement-header">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="value" id="total_movements">0</div>
                    <div class="label">Total Movements</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="value" id="stock_in_count">0</div>
                    <div class="label">Stock In</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                    <div class="value" id="stock_out_count">0</div>
                    <div class="label">Stock Out</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="value" id="net_change">0</div>
                    <div class="label">Net Change</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ UPDATED: Filter Panel -->
    <div class="filter-panel no-print">
        <div class="row align-items-end mb-3">
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-range"></i> Start Date
                </label>
                <input type="date" class="form-control" id="date_from"
                       value="{{ now()->subDays(30)->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-range"></i> End Date
                </label>
                <input type="date" class="form-control" id="date_to"
                       value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-tag"></i> Type
                </label>
                <select class="form-select" id="type_filter">
                    <option value="">All Types</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="sale">Sale</option>
                    <option value="purchase">Purchase</option>
                    <option value="return">Return</option>
                    <option value="transfer">Transfer</option>
                    <option value="damage">Damage</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-building"></i> Warehouse
                </label>
                <select class="form-select" id="warehouse_filter">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search"></i> Search
                </label>
                <input type="text" class="form-control" id="search_input"
                       placeholder="Product, SKU, user...">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-brand w-100" onclick="applyFilters()">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing <strong id="record_count">0</strong> movements
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                <i class="bi bi-x-circle"></i> Clear Filters
            </button>
        </div>
    </div>

    <!-- ✅ NEW: DataTable View -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="movementsTable" class="table table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Warehouse</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Before → After</th>
                            <th>By User</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-clock-history text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Complete History</h6>
                <p class="text-muted small mb-0">
                    Every stock movement is tracked with timestamp, user, and reason including variants.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-shield-check text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Audit Trail</h6>
                <p class="text-muted small mb-0">
                    Maintain compliance with complete inventory audit trails for products and variants.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-layers text-info fs-3 mb-2"></i>
                <h6 class="fw-bold">Variant Support</h6>
                <p class="text-muted small mb-0">
                    Track movements for individual product variants separately.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let movementsTable;

$(document).ready(function() {
    // ✅ Load initial statistics
    loadStatistics();

    // ✅ Initialize DataTable
    movementsTable = $('#movementsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.reports.inventory.movement') }}",
            data: function(d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.type = $('#type_filter').val();
                d.warehouse_id = $('#warehouse_filter').val();
                d.search = $('#search_input').val();
            }
        },
        columns: [
            {
                data: 'created_info',
                name: 'created_at',
                width: '12%'
            },
            {
                data: 'product_info',
                name: 'product_info',
                orderable: false,
                searchable: false,
                width: '25%'
            },
            {
                data: 'type_badge',
                name: 'type',
                orderable: false,
                width: '10%'
            },
            {
                data: 'warehouse_info',
                name: 'warehouse_info',
                orderable: false,
                width: '15%'
            },
            {
                data: 'quantity_change',
                name: 'quantity',
                className: 'text-center',
                width: '8%'
            },
            {
                data: 'stock_levels',
                name: 'stock_levels',
                className: 'text-center',
                orderable: false,
                width: '12%'
            },
            {
                data: 'created_info',
                name: 'creator',
                orderable: false,
                render: function(data, type, row) {
                    // Extract user name from created_info HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    const userElement = tempDiv.querySelector('.text-muted');
                    return userElement ? userElement.textContent : 'System';
                },
                width: '10%'
            },
            {
                data: 'reason',
                name: 'reason',
                orderable: false,
                render: function(data, type, row) {
                    if (!data || data === 'null') return '<span class="text-muted">—</span>';
                    return '<small>' + data + '</small>';
                },
                width: '15%'
            }
        ],
        order: [[0, 'desc']], // Sort by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            emptyTable: "No movements found",
            info: "Showing _START_ to _END_ of _TOTAL_ movements",
            infoEmpty: "Showing 0 to 0 of 0 movements",
            infoFiltered: "(filtered from _MAX_ total movements)",
            lengthMenu: "Show _MENU_ movements",
            loadingRecords: "Loading...",
            processing: '<div class="spinner-border text-brand" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            zeroRecords: "No matching movements found"
        },
        drawCallback: function(settings) {
            $('#record_count').text(settings._iRecordsDisplay);
        }
    });
});

// ✅ Load movement statistics
function loadStatistics() {
    const params = new URLSearchParams({
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        type: $('#type_filter').val() || '',
        warehouse_id: $('#warehouse_filter').val() || ''
    });

    $.ajax({
        url: "{{ route('admin.inventory.movement-statistics') }}?" + params.toString(),
        method: 'GET',
        success: function(response) {
            $('#total_movements').text(response.total.toLocaleString());
            $('#stock_in_count').text(response.added.toLocaleString());
            $('#stock_out_count').text(response.removed.toLocaleString());

            const netChange = response.net;
            const netSign = netChange >= 0 ? '+' : '';
            $('#net_change').text(netSign + netChange.toLocaleString());
        },
        error: function(xhr) {
            console.error('Failed to load statistics:', xhr);
        }
    });
}

// ✅ Apply filters
function applyFilters() {
    movementsTable.ajax.reload();
    loadStatistics();
}

// ✅ Clear all filters
function clearFilters() {
    $('#date_from').val("{{ now()->subDays(30)->format('Y-m-d') }}");
    $('#date_to').val("{{ now()->format('Y-m-d') }}");
    $('#type_filter').val('');
    $('#warehouse_filter').val('');
    $('#search_input').val('');

    movementsTable.ajax.reload();
    loadStatistics();
}

// ✅ Export movements
function exportMovements(format) {
    const params = new URLSearchParams({
        export: format,
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        type: $('#type_filter').val() || '',
        warehouse_id: $('#warehouse_filter').val() || '',
        search: $('#search_input').val() || ''
    });

    window.open("{{ route('admin.inventory.export-movements') }}?" + params.toString(), '_blank');
}

// ✅ Search with debounce
let searchTimeout;
$('#search_input').on('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        movementsTable.ajax.reload();
    }, 500);
});

// ✅ Filter change handlers
$('#date_from, #date_to, #type_filter, #warehouse_filter').on('change', function() {
    applyFilters();
});
</script>
@endpush
