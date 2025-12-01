@extends('admin.layouts.app')

@section('title', 'Returning Customers')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.5rem;
    }
    .stat-change {
        font-size: 0.875rem;
        font-weight: 600;
    }
    .page-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 2px solid #e9ecef;
    }
    .filter-chip {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.875rem;
        margin-right: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
        background: #fff;
    }
    .filter-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(91, 145, 76, 0.2);
    }
    .filter-chip.active {
        background: #5B914C;
        color: white;
        border-color: #5B914C;
    }
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        flex-shrink: 0;
    }
    .loyalty-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(91, 145, 76, 0.05);
    }
    .info-banner {
        background: linear-gradient(135deg, rgba(91, 145, 76, 0.1) 0%, rgba(74, 122, 61, 0.1) 100%);
        border-left: 4px solid #5B914C;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .percentage-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 5px solid #5B914C;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: #5B914C;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-arrow-repeat text-primary"></i> Returning Customers
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.customers.index') }}">Customers</a>
                    </li>
                    <li class="breadcrumb-item active">Returning Customers</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" id="exportBtn">
                <i class="bi bi-file-earmark-excel-fill"></i> Export
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="info-banner">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill text-primary fs-3 me-3"></i>
            <div>
                <h6 class="mb-1 fw-bold">About Returning Customers</h6>
                <p class="mb-0 text-muted">
                    Returning customers are those who have made <strong>2 or more orders</strong>. These are your most valuable customers and represent the success of your customer retention strategy.
                </p>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-value">{{ number_format($stats['total_returning']) }}</div>
                            <div class="stat-label">Returning Customers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-value">{{ number_format($stats['repeat_rate'], 1) }}%</div>
                            <div class="stat-label">Repeat Rate</div>
                            <small class="text-muted">Of all customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="percentage-circle mx-auto mb-2">
                        {{ number_format($stats['repeat_rate'], 0) }}%
                    </div>
                    <div class="stat-label">Customer Retention</div>
                    <small class="text-muted">Loyalty Score</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-graph-up-arrow text-success fs-1 mb-2"></i>
                        <div class="stat-label mb-2">Impact on Business</div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="fw-bold text-success">High</div>
                                <small class="text-muted">Revenue</small>
                            </div>
                            <div class="col-6">
                                <div class="fw-bold text-primary">{{ number_format($stats['repeat_rate'], 0) }}%</div>
                                <small class="text-muted">Retention</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loyalty Segments Info --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="loyalty-badge bg-warning text-dark mb-2">
                        <i class="bi bi-star-fill"></i> 2-3 Orders
                    </div>
                    <h6 class="fw-bold">Repeat Buyers</h6>
                    <p class="text-muted small mb-0">Starting to build loyalty</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="loyalty-badge bg-info text-white mb-2">
                        <i class="bi bi-heart-fill"></i> 4-6 Orders
                    </div>
                    <h6 class="fw-bold">Regular Customers</h6>
                    <p class="text-muted small mb-0">Consistent engagement</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="loyalty-badge bg-primary text-white mb-2">
                        <i class="bi bi-trophy-fill"></i> 7-10 Orders
                    </div>
                    <h6 class="fw-bold">Loyal Customers</h6>
                    <p class="text-muted small mb-0">Strong relationship</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="loyalty-badge bg-success text-white mb-2">
                        <i class="bi bi-gem"></i> 11+ Orders
                    </div>
                    <h6 class="fw-bold">VIP Advocates</h6>
                    <p class="text-muted small mb-0">Brand ambassadors</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <span class="text-muted me-3 fw-semibold">
                    <i class="bi bi-funnel"></i> Filter by Orders:
                </span>
                <span class="filter-chip loyalty-filter active" data-min="2" data-max="">
                    <i class="bi bi-people"></i> All Returning (2+)
                </span>
                <span class="filter-chip loyalty-filter" data-min="2" data-max="3">
                    <i class="bi bi-star"></i> Repeat Buyers (2-3)
                </span>
                <span class="filter-chip loyalty-filter" data-min="4" data-max="6">
                    <i class="bi bi-heart"></i> Regular (4-6)
                </span>
                <span class="filter-chip loyalty-filter" data-min="7" data-max="10">
                    <i class="bi bi-trophy"></i> Loyal (7-10)
                </span>
                <span class="filter-chip loyalty-filter" data-min="11" data-max="">
                    <i class="bi bi-gem"></i> VIP (11+)
                </span>
                <div class="ms-auto">
                    <button class="btn btn-sm btn-outline-danger" id="clearFiltersBtn">
                        <i class="bi bi-x-circle-fill"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Filters --}}
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-tag"></i> Customer Type
                </label>
                <select class="form-select form-select-sm" id="filterCustomerType">
                    <option value="">All Types</option>
                    <option value="individual">Individual</option>
                    <option value="business">Business</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="vip">VIP</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-currency-dollar"></i> Total Spent (Min)
                </label>
                <input type="number" class="form-control form-control-sm" id="filterMinSpent" placeholder="0.00" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-currency-dollar"></i> Total Spent (Max)
                </label>
                <input type="number" class="form-control form-control-sm" id="filterMaxSpent" placeholder="10000.00" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-calendar-range"></i> Last Order
                </label>
                <select class="form-select form-select-sm" id="filterLastOrder">
                    <option value="">Any Time</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="60">Last 60 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Main Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-table text-primary"></i> Returning Customers List
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-success badge-lg" id="customerCount">
                        Loading...
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="returningCustomersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-person"></i> Customer</th>
                            <th><i class="bi bi-tag"></i> Type</th>
                            <th class="text-center"><i class="bi bi-cart"></i> Orders</th>
                            <th class="text-end"><i class="bi bi-currency-dollar"></i> Total Spent</th>
                            <th class="text-end"><i class="bi bi-graph-up"></i> Avg Value</th>
                            <th><i class="bi bi-trophy"></i> Loyalty</th>
                            <th><i class="bi bi-clock-history"></i> Last Order</th>
                            <th><i class="bi bi-calendar-plus"></i> First Order</th>
                            <th class="text-center"><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data loaded via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let table;
    let currentMinOrders = 2;
    let currentMaxOrders = null;

    // Initialize DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#returningCustomersTable')) {
            $('#returningCustomersTable').DataTable().destroy();
        }

        table = $('#returningCustomersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.customers.data") }}',
                data: function(d) {
                    d.min_orders = currentMinOrders;
                    d.max_orders = currentMaxOrders;
                    d.customer_type = $('#filterCustomerType').val();
                    d.min_spent = $('#filterMinSpent').val();
                    d.max_spent = $('#filterMaxSpent').val();
                    d.last_order_days = $('#filterLastOrder').val();
                }
            },
            columns: [
                {
                    data: 'customer_info',
                    name: 'first_name',
                    orderable: true
                },
                {
                    data: 'customer_type',
                    name: 'customer_type',
                    className: 'text-center'
                },
                {
                    data: 'total_orders',
                    name: 'total_orders',
                    className: 'text-center',
                    render: function(data) {
                        let badgeClass = 'bg-secondary';
                        if (data >= 11) badgeClass = 'bg-success';
                        else if (data >= 7) badgeClass = 'bg-primary';
                        else if (data >= 4) badgeClass = 'bg-info';
                        else if (data >= 2) badgeClass = 'bg-warning';

                        return `<span class="badge ${badgeClass}">${data} orders</span>`;
                    }
                },
                {
                    data: 'total_spent',
                    name: 'total_spent',
                    className: 'text-end',
                    render: function(data) {
                        return `<strong class="text-success">{{ store_currency_symbol() }}${parseFloat(data).toFixed(2)}</strong>`;
                    }
                },
                {
                    data: 'average_order_value',
                    name: 'average_order_value',
                    className: 'text-end',
                    render: function(data) {
                        return `{{ store_currency_symbol() }}${parseFloat(data).toFixed(2)}`;
                    }
                },
                {
                    data: 'segment',
                    name: 'segment',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'last_order',
                    name: 'last_order_at',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data;
                        }
                        return row.last_order_at;
                    }
                },
                {
                    data: 'first_order_at',
                    name: 'first_order_at',
                    render: function(data) {
                        if (data) {
                            const date = new Date(data);
                            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        }
                        return '<span class="text-muted">N/A</span>';
                    }
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [[2, 'desc']], // Order by total_orders desc
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No returning customers found",
                zeroRecords: "No matching returning customers found",
                info: "Showing _START_ to _END_ of _TOTAL_ returning customers",
                infoEmpty: "Showing 0 to 0 of 0 returning customers",
                infoFiltered: "(filtered from _MAX_ total customers)"
            },
            drawCallback: function(settings) {
                const info = table.page.info();
                $('#customerCount').text(info.recordsDisplay + ' Customers');
            }
        });
    }

    initDataTable();

    // Loyalty filter chips
    $('.loyalty-filter').on('click', function() {
        $('.loyalty-filter').removeClass('active');
        $(this).addClass('active');

        currentMinOrders = $(this).data('min');
        currentMaxOrders = $(this).data('max') || null;

        table.ajax.reload();
    });

    // Advanced filters
    $('#filterCustomerType, #filterMinSpent, #filterMaxSpent, #filterLastOrder').on('change', function() {
        table.ajax.reload();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('.loyalty-filter').removeClass('active');
        $('.loyalty-filter:first').addClass('active');

        currentMinOrders = 2;
        currentMaxOrders = null;

        $('#filterCustomerType').val('');
        $('#filterMinSpent').val('');
        $('#filterMaxSpent').val('');
        $('#filterLastOrder').val('');

        table.ajax.reload();
    });

    // Export
    $('#exportBtn').on('click', function() {
        const params = {
            min_orders: currentMinOrders,
            max_orders: currentMaxOrders,
            customer_type: $('#filterCustomerType').val(),
            min_spent: $('#filterMinSpent').val(),
            max_spent: $('#filterMaxSpent').val(),
            last_order_days: $('#filterLastOrder').val()
        };

        const queryString = $.param(params);
        window.location.href = '{{ route("admin.customers.export") }}?' + queryString;

        Swal.fire({
            icon: 'info',
            title: 'Exporting...',
            text: 'Your CSV file will download shortly',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // View customer details
    $(document).on('click', '.view-customer', function() {
        const customerId = $(this).data('id');
        window.location.href = `/admin/customers/${customerId}`;
    });
});
</script>
@endpush
