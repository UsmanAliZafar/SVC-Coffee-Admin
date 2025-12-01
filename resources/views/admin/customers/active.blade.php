@extends('admin.layouts.app')

@section('title', 'Active Customers')

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
    .page-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
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
    .info-banner {
        background: linear-gradient(135deg, rgba(91, 145, 76, 0.1) 0%, rgba(74, 122, 61, 0.1) 100%);
        border-left: 4px solid #5B914C;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
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
    .table-hover tbody tr:hover {
        background-color: rgba(91, 145, 76, 0.05);
    }
    .engagement-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 0.25rem;
    }
    .engagement-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #5B914C 0%, #4a7a3d 100%);
        transition: width 0.5s ease;
    }
    .activity-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .percentage-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 6px solid #5B914C;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: #5B914C;
        margin: 0 auto;
    }
    .metric-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }
    .metric-box:hover {
        border-color: #5B914C;
        background: rgba(91, 145, 76, 0.05);
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #5B914C;
        display: inline-block;
        margin-right: 5px;
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(91, 145, 76, 0.7); }
        50% { box-shadow: 0 0 0 6px rgba(91, 145, 76, 0); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-person-check-fill text-success"></i> Active Customers
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
                    <li class="breadcrumb-item active">Active Customers</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" id="exportBtn">
                <i class="bi bi-file-earmark-excel-fill"></i> Export
            </button>
            <button class="btn btn-info text-white" id="bulkSyncBtn">
                <i class="bi bi-arrow-repeat"></i> Bulk Sync
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="info-banner">
        <div class="d-flex align-items-center">
            <span class="status-indicator"></span>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold">About Active Customers</h6>
                <p class="mb-0 text-muted">
                    Active customers have <strong>CUSTOMER_ACTIVE</strong> status and can access their accounts. These are your current, engaged customers who can place orders and interact with your platform.
                </p>
            </div>
        </div>
    </div>

    {{-- Statistics Dashboard --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Active Customers</div>
                            <div class="stat-value">{{ number_format($stats['total_active']) }}</div>
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
                            <div class="stat-label">Active Rate</div>
                            <div class="stat-value">{{ number_format($stats['percentage'], 1) }}%</div>
                            <small class="text-muted">Of all customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="percentage-circle">
                        {{ number_format($stats['percentage'], 0) }}%
                    </div>
                    <div class="stat-label mt-2">Engagement Rate</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-success fs-1 mb-2"></i>
                    <div class="stat-label mb-2">Health Status</div>
                    <div class="activity-badge bg-success text-white">
                        <i class="bi bi-check-circle-fill"></i> Excellent
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Engagement Metrics --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-cart-check-fill text-primary fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">With Orders</h6>
                <h4 class="mb-0 fw-bold" id="withOrdersCount">-</h4>
                <small class="text-muted">Have purchased</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-envelope-check-fill text-success fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">Verified</h6>
                <h4 class="mb-0 fw-bold" id="verifiedCount">-</h4>
                <small class="text-muted">Email verified</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-clock-history text-info fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">Recent Activity</h6>
                <h4 class="mb-0 fw-bold" id="recentCount">-</h4>
                <small class="text-muted">Last 30 days</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-star-fill text-warning fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">High Value</h6>
                <h4 class="mb-0 fw-bold" id="highValueCount">-</h4>
                <small class="text-muted">{{ store_currency_symbol() }}1000+ spent</small>
            </div>
        </div>
    </div>

    {{-- Quick Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <span class="text-muted me-3 fw-semibold">
                    <i class="bi bi-funnel"></i> Quick Filters:
                </span>
                <span class="filter-chip activity-filter active" data-filter="all">
                    <i class="bi bi-people"></i> All Active
                </span>
                <span class="filter-chip activity-filter" data-filter="verified">
                    <i class="bi bi-patch-check"></i> Verified
                </span>
                <span class="filter-chip activity-filter" data-filter="with_orders">
                    <i class="bi bi-cart-check"></i> With Orders
                </span>
                <span class="filter-chip activity-filter" data-filter="recent">
                    <i class="bi bi-clock-history"></i> Recent Activity
                </span>
                <span class="filter-chip activity-filter" data-filter="newsletter">
                    <i class="bi bi-envelope"></i> Newsletter
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
                    <i class="bi bi-cart"></i> Order Count
                </label>
                <select class="form-select form-select-sm" id="filterOrderCount">
                    <option value="">Any Orders</option>
                    <option value="0">No Orders (0)</option>
                    <option value="1">First Time (1)</option>
                    <option value="2-5">Regular (2-5)</option>
                    <option value="6+">Frequent (6+)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-calendar-range"></i> Registration Date
                </label>
                <select class="form-select form-select-sm" id="filterRegistration">
                    <option value="">All Time</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-search"></i> Search
                </label>
                <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Name, email, phone...">
            </div>
        </div>
    </div>

    {{-- Main Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-table text-primary"></i> Active Customers List
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
                <table class="table table-hover align-middle" id="activeCustomersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th><i class="bi bi-person"></i> Customer</th>
                            <th class="text-center"><i class="bi bi-tag"></i> Type</th>
                            <th class="text-center"><i class="bi bi-shield-check"></i> Verified</th>
                            <th class="text-center"><i class="bi bi-cart"></i> Orders</th>
                            <th class="text-end"><i class="bi bi-currency-dollar"></i> Total Spent</th>
                            <th><i class="bi bi-diagram-3"></i> Segment</th>
                            <th><i class="bi bi-clock-history"></i> Last Order</th>
                            <th><i class="bi bi-calendar-plus"></i> Registered</th>
                            <th class="text-center" width="120"><i class="bi bi-gear"></i> Actions</th>
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

{{-- Bulk Sync Modal --}}
<div class="modal fade" id="bulkSyncModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Syncing Active Customers
                </h5>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-info mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="syncMessage">Syncing customer statistics...</h5>
                <p class="text-muted" id="syncDetails">Please wait, this may take a moment</p>
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
    let selectedCustomers = [];

    // Initialize DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#activeCustomersTable')) {
            $('#activeCustomersTable').DataTable().destroy();
        }

        table = $('#activeCustomersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.customers.data") }}',
                data: function(d) {
                    d.status = 'CUSTOMER_ACTIVE';
                    d.customer_type = $('#filterCustomerType').val();
                    d.order_count = $('#filterOrderCount').val();
                    d.registration_days = $('#filterRegistration').val();
                    d.search_query = $('#searchInput').val();

                    // Quick filters
                    const activeFilter = $('.activity-filter.active').data('filter');
                    if (activeFilter === 'verified') {
                        d.verified = 'true';
                    } else if (activeFilter === 'with_orders') {
                        d.has_orders = 'true';
                    } else if (activeFilter === 'recent') {
                        d.recent_activity = 30;
                    } else if (activeFilter === 'newsletter') {
                        d.newsletter = 'true';
                    }
                }
            },
            columns: [
                {
                    data: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                { data: 'customer_info', name: 'first_name' },
                { data: 'customer_type', name: 'customer_type', className: 'text-center' },
                {
                    data: 'is_verified',
                    name: 'is_verified',
                    className: 'text-center',
                    render: function(data) {
                        if (data) {
                            return '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
                        }
                        return '<i class="bi bi-x-circle text-muted"></i>';
                    }
                },
                {
                    data: 'total_orders',
                    name: 'total_orders',
                    className: 'text-center',
                    render: function(data) {
                        let badgeClass = 'bg-secondary';
                        if (data >= 6) badgeClass = 'bg-success';
                        else if (data >= 2) badgeClass = 'bg-primary';
                        else if (data === 1) badgeClass = 'bg-info';
                        else if (data === 0) badgeClass = 'bg-warning';

                        return `<span class="badge ${badgeClass}">${data}</span>`;
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
                { data: 'segment', orderable: false, searchable: false, className: 'text-center' },
                { data: 'last_order', name: 'last_order_at' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[8, 'desc']], // Order by registration date (newest first)
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No active customers found",
                zeroRecords: "No matching active customers found",
                info: "Showing _START_ to _END_ of _TOTAL_ active customers",
                infoEmpty: "Showing 0 to 0 of 0 customers"
            },
            drawCallback: function(settings) {
                const info = table.page.info();
                $('#customerCount').text(info.recordsDisplay + ' Customers');

                // Update engagement metrics
                updateEngagementMetrics();

                // Update checkboxes
                $('.customer-checkbox').each(function() {
                    if (selectedCustomers.includes($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
            }
        });
    }

    initDataTable();

    // Update engagement metrics
    function updateEngagementMetrics() {
        $.ajax({
            url: '{{ route("admin.customers.data") }}',
            data: {
                status: 'CUSTOMER_ACTIVE',
                get_metrics: true
            },
            success: function(response) {
                if (response.metrics) {
                    $('#withOrdersCount').text(response.metrics.with_orders || '-');
                    $('#verifiedCount').text(response.metrics.verified || '-');
                    $('#recentCount').text(response.metrics.recent_activity || '-');
                    $('#highValueCount').text(response.metrics.high_value || '-');
                }
            }
        });
    }

    // Quick filters
    $('.activity-filter').on('click', function() {
        $('.activity-filter').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    // Advanced filters
    $('#filterCustomerType, #filterOrderCount, #filterRegistration').on('change', function() {
        table.ajax.reload();
    });

    // Search input with debounce
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.ajax.reload();
        }, 500);
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('.activity-filter').removeClass('active');
        $('.activity-filter:first').addClass('active');
        $('#filterCustomerType').val('');
        $('#filterOrderCount').val('');
        $('#filterRegistration').val('');
        $('#searchInput').val('');
        table.ajax.reload();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.customer-checkbox:visible').each(function() {
            $(this).prop('checked', isChecked);
            const customerId = $(this).val();
            if (isChecked) {
                if (!selectedCustomers.includes(customerId)) {
                    selectedCustomers.push(customerId);
                }
            } else {
                selectedCustomers = selectedCustomers.filter(id => id !== customerId);
            }
        });
    });

    // Individual checkbox
    $(document).on('change', '.customer-checkbox', function() {
        const customerId = $(this).val();
        if ($(this).prop('checked')) {
            if (!selectedCustomers.includes(customerId)) {
                selectedCustomers.push(customerId);
            }
        } else {
            selectedCustomers = selectedCustomers.filter(id => id !== customerId);
            $('#selectAll').prop('checked', false);
        }
    });

    // Bulk sync
    $('#bulkSyncBtn').on('click', function() {
        Swal.fire({
            title: 'Sync All Active Customers?',
            text: "This will update order statistics for all active customers. This may take a few moments.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Yes, sync all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkSync();
            }
        });
    });

    function performBulkSync() {
        $('#bulkSyncModal').modal('show');

        $.ajax({
            url: '{{ route("admin.customers.bulk-sync-stats") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: 'CUSTOMER_ACTIVE'
            },
            success: function(response) {
                $('#bulkSyncModal').modal('hide');

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sync Complete!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: true,
                        confirmButtonColor: '#5B914C'
                    });

                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                $('#bulkSyncModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to sync customers',
                    confirmButtonColor: '#5B914C'
                });
            }
        });
    }

    // Export
    $('#exportBtn').on('click', function() {
        const params = {
            status: 'CUSTOMER_ACTIVE',
            customer_type: $('#filterCustomerType').val(),
            order_count: $('#filterOrderCount').val(),
            registration_days: $('#filterRegistration').val()
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

    // Initial metrics load
    updateEngagementMetrics();
});
</script>
@endpush
