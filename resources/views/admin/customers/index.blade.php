@extends('admin.layouts.app')

@section('title', 'Customers Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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
    .sync-btn-rotating {
        animation: rotate 1s linear infinite;
    }
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .table-hover tbody tr:hover {
        background-color: rgba(91, 145, 76, 0.05);
    }
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .dataTables_wrapper .dataTables_length select {
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
    }
    .filters-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-people-fill text-primary"></i> Customers Management
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Customers</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(auth('admin')->user()->hasPermission('customers.create'))
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i> Add Customer
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('customers.update'))
            <button class="btn btn-info text-white" id="bulkSyncBtn" title="Sync All Customers">
                <i class="bi bi-arrow-repeat"></i> Bulk Sync
            </button>
            @endif
            <button class="btn btn-success" id="exportCustomersBtn" title="Export to CSV">
                <i class="bi bi-file-earmark-excel-fill"></i> Export
            </button>
            <button class="btn btn-outline-secondary" id="refreshBtn" title="Refresh Table">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 text-uppercase small">Total Customers</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_customers']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-person-check-fill fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 text-uppercase small">Active Customers</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['active_customers']) }}</h3>
                            <small class="text-success fw-semibold">
                                <i class="bi bi-graph-up-arrow"></i>
                                {{ $stats['total_customers'] > 0 ? number_format(($stats['active_customers'] / $stats['total_customers']) * 100, 1) : 0 }}% of total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-person-plus-fill fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 text-uppercase small">New This Month</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['new_customers_this_month']) }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-calendar-event"></i> Since {{ now()->startOfMonth()->format('M 1') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                {{ store_currency_symbol() }}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 text-uppercase small">Lifetime Value</h6>
                            <h3 class="mb-0 fw-bold">{{ store_currency_symbol() }}{{ number_format($stats['total_lifetime_value'], 2) }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-cash-stack"></i> Total revenue
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="text-muted me-3 fw-semibold">
                    <i class="bi bi-funnel"></i> Quick Filters:
                </span>
                <span class="filter-chip quick-filter active" data-filter="all">
                    <i class="bi bi-people"></i> All Customers
                </span>
                <span class="filter-chip quick-filter" data-filter="active">
                    <i class="bi bi-check-circle-fill"></i> Active
                </span>
                <span class="filter-chip quick-filter" data-filter="inactive">
                    <i class="bi bi-pause-circle-fill"></i> Inactive
                </span>
                <span class="filter-chip quick-filter" data-filter="blocked">
                    <i class="bi bi-lock-fill"></i> Blocked
                </span>
                <span class="filter-chip quick-filter" data-filter="verified">
                    <i class="bi bi-patch-check-fill"></i> Verified
                </span>
                <span class="filter-chip quick-filter" data-filter="newsletter">
                    <i class="bi bi-envelope-fill"></i> Newsletter
                </span>

                <div class="ms-auto">
                    <button class="btn btn-sm btn-outline-danger" id="clearFiltersBtn">
                        <i class="bi bi-x-circle-fill"></i> Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-table text-primary"></i> Customers List
                    </h5>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="bulkActionsBtn" disabled>
                        <i class="bi bi-gear-fill"></i> <span id="bulkActionText">Bulk Actions</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Advanced Filters --}}
            <div class="filters-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-person-badge"></i> Customer Type
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
                            <i class="bi bi-toggle-on"></i> Status
                        </label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="CUSTOMER_ACTIVE">Active</option>
                            <option value="CUSTOMER_INACTIVE">Inactive</option>
                            <option value="CUSTOMER_BLOCKED">Blocked</option>
                            {{-- <option value="CUSTOMER_PENDING">Pending</option> --}}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-shield-check"></i> Verified
                        </label>
                        <select class="form-select form-select-sm" id="filterVerified">
                            <option value="">All</option>
                            <option value="true">Verified Only</option>
                            <option value="false">Unverified Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-envelope"></i> Newsletter
                        </label>
                        <select class="form-select form-select-sm" id="filterNewsletter">
                            <option value="">All</option>
                            <option value="true">Subscribed</option>
                            <option value="false">Not Subscribed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold invisible">Actions</label>
                        <button class="btn btn-sm btn-outline-secondary w-100" id="applyFiltersBtn">
                            <i class="bi bi-search"></i> Apply
                        </button>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="customersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="30" class="text-center">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th><i class="bi bi-person"></i> Customer</th>
                            <th><i class="bi bi-tag"></i> Type</th>
                            <th><i class="bi bi-circle-fill"></i> Status</th>
                            <th><i class="bi bi-cart"></i> Orders</th>
                            <th><i class="bi bi-diagram-3"></i> Segment</th>
                            <th><i class="bi bi-clock-history"></i> Last Order</th>
                            <th><i class="bi bi-calendar-plus"></i> Registered</th>
                            <th width="150" class="text-center"><i class="bi bi-gear"></i> Actions</th>
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

{{-- Bulk Actions Modal --}}
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-gear-fill"></i> Bulk Actions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    Selected <strong id="selectedCount">0</strong> customer(s)
                </div>
                <div class="d-grid gap-2">
                    @if(auth('admin')->user()->hasPermission('customers.update'))
                    <button class="btn btn-success" id="bulkActivateBtn">
                        <i class="bi bi-check-circle-fill"></i> Activate Selected
                    </button>
                    <button class="btn btn-warning" id="bulkDeactivateBtn">
                        <i class="bi bi-pause-circle-fill"></i> Deactivate Selected
                    </button>
                    <button class="btn btn-danger" id="bulkBlockBtn">
                        <i class="bi bi-lock-fill"></i> Block Selected
                    </button>
                    <hr>
                    <button class="btn btn-info text-white" id="bulkVerifyBtn">
                        <i class="bi bi-patch-check-fill"></i> Verify Selected
                    </button>
                    <button class="btn btn-primary" id="bulkSubscribeBtn">
                        <i class="bi bi-envelope-fill"></i> Subscribe to Newsletter
                    </button>
                    <button class="btn btn-outline-secondary" id="bulkUnsubscribeBtn">
                        <i class="bi bi-envelope-slash"></i> Unsubscribe from Newsletter
                    </button>
                    <hr>
                    <button class="btn btn-outline-info" id="bulkSyncSelectedBtn">
                        <i class="bi bi-arrow-repeat"></i> Sync Selected Stats
                    </button>
                    @endif
                    @if(auth('admin')->user()->hasPermission('customers.delete'))
                    <hr>
                    <button class="btn btn-outline-danger" id="bulkDeleteBtn">
                        <i class="bi bi-trash-fill"></i> Delete Selected
                    </button>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Sync Progress Modal --}}
<div class="modal fade" id="syncProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat sync-btn-rotating"></i> Syncing Customer Data
                </h5>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-info mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="syncMessage">Please wait while we sync customer statistics...</h5>
                <p class="text-muted" id="syncDetails">This may take a few moments</p>
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
        if ($.fn.DataTable.isDataTable('#customersTable')) {
            $('#customersTable').DataTable().destroy();
        }

        table = $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.customers.data") }}',
                data: function(d) {
                    d.customer_type = $('#filterCustomerType').val();
                    d.status = $('#filterStatus').val();
                    d.verified = $('#filterVerified').val();
                    d.newsletter = $('#filterNewsletter').val();
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                { data: 'customer_info', name: 'first_name' },
                { data: 'customer_type', name: 'customer_type', className: 'text-center' },
                { data: 'status_badge', name: 'status_key_code', className: 'text-center' },
                { data: 'orders_info', name: 'total_orders' },
                { data: 'segment', orderable: false, searchable: false, className: 'text-center' },
                { data: 'last_order', name: 'last_order_at' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[7, 'desc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: '<div class="text-muted"><i class="bi bi-inbox"></i> No customers found</div>',
                zeroRecords: '<div class="text-muted"><i class="bi bi-search"></i> No matching customers found</div>',
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ customers",
                infoEmpty: "Showing 0 to 0 of 0 customers",
                infoFiltered: "(filtered from _MAX_ total customers)"
            },
            drawCallback: function() {
                // Reinitialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Update selected checkboxes
                $('.customer-checkbox').each(function() {
                    if (selectedCustomers.includes($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });

                // Update select all checkbox
                updateSelectAllCheckbox();
            }
        });
    }

    initDataTable();

    // Filter change handlers
    $('#filterCustomerType, #filterStatus, #filterVerified, #filterNewsletter').on('change', function() {
        table.ajax.reload();
    });

    // Apply filters button
    $('#applyFiltersBtn').on('click', function() {
        table.ajax.reload();
    });

    // Quick filters
    $('.quick-filter').on('click', function() {
        $('.quick-filter').removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('filter');

        // Reset all filters
        $('#filterCustomerType').val('');
        $('#filterStatus').val('');
        $('#filterVerified').val('');
        $('#filterNewsletter').val('');

        // Apply specific filter
        switch(filter) {
            case 'active':
                $('#filterStatus').val('CUSTOMER_ACTIVE');
                break;
            case 'inactive':
                $('#filterStatus').val('CUSTOMER_INACTIVE');
                break;
            case 'blocked':
                $('#filterStatus').val('CUSTOMER_BLOCKED');
                break;
            case 'verified':
                $('#filterVerified').val('true');
                break;
            case 'newsletter':
                $('#filterNewsletter').val('true');
                break;
        }

        table.ajax.reload();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('.quick-filter').removeClass('active');
        $('.quick-filter[data-filter="all"]').addClass('active');
        $('#filterCustomerType, #filterStatus, #filterVerified, #filterNewsletter').val('');
        selectedCustomers = [];
        updateBulkActionsButton();
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
        updateBulkActionsButton();
    });

    // Individual checkbox selection
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

        updateBulkActionsButton();
        updateSelectAllCheckbox();
    });

    function updateSelectAllCheckbox() {
        const totalVisible = $('.customer-checkbox:visible').length;
        const totalChecked = $('.customer-checkbox:visible:checked').length;

        if (totalVisible > 0 && totalChecked === totalVisible) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    }

    function updateBulkActionsButton() {
        const count = selectedCustomers.length;
        if (count > 0) {
            $('#bulkActionsBtn').prop('disabled', false);
            $('#bulkActionText').text(`Bulk Actions (${count})`);
        } else {
            $('#bulkActionsBtn').prop('disabled', true);
            $('#bulkActionText').text('Bulk Actions');
        }
        $('#selectedCount').text(count);
    }

    // Bulk actions modal
    $('#bulkActionsBtn').on('click', function() {
        if (selectedCustomers.length > 0) {
            $('#bulkActionsModal').modal('show');
        }
    });

    // Sync individual customer
    $(document).on('click', '.sync-customer-btn', function() {
        const customerId = $(this).data('id');
        const $btn = $(this);
        const $icon = $btn.find('i');

        // Add rotating animation
        $icon.addClass('sync-btn-rotating');
        $btn.prop('disabled', true);

        $.ajax({
            url: `/admin/customers/${customerId}/sync-stats`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Synced!',
                        html: `
                            <div class="text-start">
                                <p class="mb-2"><strong>Total Orders:</strong> ${response.data.total_orders}</p>
                                <p class="mb-2"><strong>Total Spent:</strong> ${response.data.total_spent}</p>
                                <p class="mb-2"><strong>Avg Order Value:</strong> ${response.data.average_order_value}</p>
                                <p class="mb-2"><strong>First Order:</strong> ${response.data.first_order_at}</p>
                                <p class="mb-2"><strong>Last Order:</strong> ${response.data.last_order_at}</p>
                                <p class="mb-0"><strong>Segment:</strong> <span class="badge bg-primary">${response.data.segment}</span></p>
                            </div>
                        `,
                        timer: 5000,
                        showConfirmButton: true
                    });
                    table.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to sync customer', 'error');
            },
            complete: function() {
                $icon.removeClass('sync-btn-rotating');
                $btn.prop('disabled', false);
            }
        });
    });

    // Bulk sync all customers
    $('#bulkSyncBtn').on('click', function() {
        Swal.fire({
            title: 'Sync All Customers?',
            text: "This will recalculate order statistics for all customers. This may take a few moments.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Yes, sync all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkSync([]);
            }
        });
    });

    // Bulk sync selected customers
    $('#bulkSyncSelectedBtn').on('click', function() {
        if (selectedCustomers.length === 0) {
            Swal.fire('Warning', 'Please select customers to sync', 'warning');
            return;
        }

        $('#bulkActionsModal').modal('hide');

        Swal.fire({
            title: 'Sync Selected Customers?',
            text: `This will recalculate order statistics for ${selectedCustomers.length} selected customer(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-repeat"></i> Yes, sync!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkSync(selectedCustomers);
            }
        });
    });

    function performBulkSync(customerIds) {
        $('#syncProgressModal').modal('show');

        const isAll = customerIds.length === 0;
        $('#syncMessage').text(isAll ? 'Syncing all customers...' : `Syncing ${customerIds.length} customer(s)...`);

        $.ajax({
            url: '{{ route("admin.customers.bulk-sync-stats") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                customer_ids: customerIds
            },
            success: function(response) {
                $('#syncProgressModal').modal('hide');

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sync Complete!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: true
                    });

                    // Clear selections
                    selectedCustomers = [];
                    updateBulkActionsButton();
                    $('#selectAll').prop('checked', false);

                    // Reload table
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                $('#syncProgressModal').modal('hide');
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to sync customers', 'error');
            }
        });
    }

    // Bulk action handlers
    function performBulkAction(action, actionName) {
        if (selectedCustomers.length === 0) {
            Swal.fire('Warning', 'Please select customers first', 'warning');
            return;
        }

        $('#bulkActionsModal').modal('hide');

        Swal.fire({
            title: `${actionName} Customers?`,
            text: `Are you sure you want to ${actionName.toLowerCase()} ${selectedCustomers.length} customer(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionName.toLowerCase()}!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.customers.bulk-action") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action,
                        customer_ids: selectedCustomers
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            selectedCustomers = [];
                            updateBulkActionsButton();
                            $('#selectAll').prop('checked', false);
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || `Failed to ${actionName.toLowerCase()}`, 'error');
                    }
                });
            }
        });
    }

    $('#bulkActivateBtn').on('click', () => performBulkAction('activate', 'Activate'));
    $('#bulkDeactivateBtn').on('click', () => performBulkAction('deactivate', 'Deactivate'));
    $('#bulkBlockBtn').on('click', () => performBulkAction('block', 'Block'));
    $('#bulkVerifyBtn').on('click', () => performBulkAction('verify', 'Verify'));
    $('#bulkSubscribeBtn').on('click', () => performBulkAction('subscribe_newsletter', 'Subscribe'));
    $('#bulkUnsubscribeBtn').on('click', () => performBulkAction('unsubscribe_newsletter', 'Unsubscribe'));

    $('#bulkDeleteBtn').on('click', function() {
        if (selectedCustomers.length === 0) {
            Swal.fire('Warning', 'Please select customers first', 'warning');
            return;
        }

        $('#bulkActionsModal').modal('hide');

        Swal.fire({
            title: 'Delete Customers?',
            text: `This will permanently delete ${selectedCustomers.length} customer(s). This action cannot be undone!`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.customers.bulk-action") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: 'delete',
                        customer_ids: selectedCustomers
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            selectedCustomers = [];
                            updateBulkActionsButton();
                            $('#selectAll').prop('checked', false);
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete customers', 'error');
                    }
                });
            }
        });
    });

    // Toggle status
    $(document).on('click', '.toggle-status-btn', function() {
        const customerId = $(this).data('id');
        const action = $(this).data('action');
        const actionText = action.charAt(0).toUpperCase() + action.slice(1);

        Swal.fire({
            title: `${actionText} Customer?`,
            text: `Are you sure you want to ${action} this customer?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action}!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/customers/${customerId}/toggle-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update status', 'error');
                    }
                });
            }
        });
    });

    // Delete customer
    $(document).on('click', '.delete-customer', function() {
        const customerId = $(this).data('id');

        Swal.fire({
            title: 'Delete Customer?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/customers/${customerId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete customer', 'error');
                    }
                });
            }
        });
    });

    // Export customers
    $('#exportCustomersBtn').on('click', function() {
        const params = {
            customer_type: $('#filterCustomerType').val(),
            status: $('#filterStatus').val(),
            verified: $('#filterVerified').val(),
            newsletter: $('#filterNewsletter').val()
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

    // Refresh table
    $('#refreshBtn').on('click', function() {
        const $icon = $(this).find('i');
        $icon.addClass('sync-btn-rotating');

        table.ajax.reload(function() {
            setTimeout(() => {
                $icon.removeClass('sync-btn-rotating');
            }, 1000);
        });
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
