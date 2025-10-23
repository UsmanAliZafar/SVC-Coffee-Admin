@extends('admin.layouts.app')

@section('title', 'Customers Management')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
    }
    .filter-chip {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        margin-right: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .quick-filter {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .quick-filter.active {
        background: #5B914C;
        color: white;
        border-color: #5B914C;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Customers Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Customers</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('customers.create'))
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Add Customer
            </a>
            @endif
            <button class="btn btn-outline-success" id="exportCustomersBtn">
                <i class="bi bi-file-earmark-excel"></i> Export
            </button>
            <button class="btn btn-outline-secondary" id="refreshBtn">
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
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Customers</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_customers']) }}</h3>
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
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-person-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Active Customers</h6>
                            <h3 class="mb-0">{{ number_format($stats['active_customers']) }}</h3>
                            <small class="text-success">
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
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="bi bi-person-plus fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">New This Month</h6>
                            <h3 class="mb-0">{{ number_format($stats['new_customers_this_month']) }}</h3>
                            <small class="text-muted">Since {{ now()->startOfMonth()->format('M 1') }}</small>
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
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Lifetime Value</h6>
                            <h3 class="mb-0">${{ number_format($stats['total_lifetime_value'], 2) }}</h3>
                            <small class="text-muted">Total revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted me-2"><strong>Quick Filters:</strong></span>
                <span class="filter-chip quick-filter" data-filter="all">
                    <i class="bi bi-people"></i> All
                </span>
                <span class="filter-chip quick-filter" data-filter="active">
                    <i class="bi bi-check-circle text-success"></i> Active
                </span>
                <span class="filter-chip quick-filter" data-filter="inactive">
                    <i class="bi bi-pause-circle text-secondary"></i> Inactive
                </span>
                <span class="filter-chip quick-filter" data-filter="blocked">
                    <i class="bi bi-lock text-danger"></i> Blocked
                </span>
                <span class="filter-chip quick-filter" data-filter="verified">
                    <i class="bi bi-patch-check text-primary"></i> Verified
                </span>
                <span class="filter-chip quick-filter" data-filter="newsletter">
                    <i class="bi bi-envelope text-info"></i> Newsletter Subscribers
                </span>

                <div class="ms-auto">
                    <button class="btn btn-sm btn-outline-secondary" id="clearFiltersBtn">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="bi bi-people text-primary"></i> Customers List</h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="bulkActionsBtn" disabled>
                            <i class="bi bi-gear"></i> Bulk Actions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Advanced Filters --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label small">Customer Type</label>
                    <select class="form-select form-select-sm" id="filterCustomerType">
                        <option value="">All Types</option>
                        <option value="individual">Individual</option>
                        <option value="business">Business</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="">All Statuses</option>
                        <option value="CUSTOMER_ACTIVE">Active</option>
                        <option value="CUSTOMER_INACTIVE">Inactive</option>
                        <option value="CUSTOMER_BLOCKED">Blocked</option>
                        <option value="CUSTOMER_PENDING">Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Verified</label>
                    <select class="form-select form-select-sm" id="filterVerified">
                        <option value="">All</option>
                        <option value="true">Verified Only</option>
                        <option value="false">Unverified Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Newsletter</label>
                    <select class="form-select form-select-sm" id="filterNewsletter">
                        <option value="">All</option>
                        <option value="true">Subscribed</option>
                        <option value="false">Not Subscribed</option>
                    </select>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="table-responsive">
                <table class="table table-hover" id="customersTable" style="width:100%">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Segment</th>
                            <th>Last Order</th>
                            <th>Registered</th>
                            <th width="120">Actions</th>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Selected <strong id="selectedCount">0</strong> customer(s)</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" id="bulkActivateBtn">
                        <i class="bi bi-check-circle"></i> Activate Selected
                    </button>
                    <button class="btn btn-danger" id="bulkBlockBtn">
                        <i class="bi bi-lock"></i> Block Selected
                    </button>
                    <button class="btn btn-secondary" id="bulkDeactivateBtn">
                        <i class="bi bi-pause-circle"></i> Deactivate Selected
                    </button>
                    @if(auth('admin')->user()->hasPermission('customers.delete'))
                    <hr>
                    <button class="btn btn-outline-danger" id="bulkDeleteBtn">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

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
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'customer_info', name: 'first_name' },
                { data: 'customer_type', name: 'customer_type' },
                { data: 'status_badge', name: 'status_key_code' },
                { data: 'orders_info', name: 'total_orders' },
                { data: 'segment', orderable: false, searchable: false },
                { data: 'last_order', name: 'last_order_at' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            order: [[7, 'desc']],
            pageLength: 25,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No customers found",
                zeroRecords: "No matching customers found"
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
            }
        });
    }

    initDataTable();

    // Filter change handlers
    $('#filterCustomerType, #filterStatus, #filterVerified, #filterNewsletter').on('change', function() {
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
        $('#filterCustomerType, #filterStatus, #filterVerified, #filterNewsletter').val('');
        table.ajax.reload();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.customer-checkbox:visible').prop('checked', isChecked).trigger('change');
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
        }

        updateBulkActionsButton();
    });

    function updateBulkActionsButton() {
        if (selectedCustomers.length > 0) {
            $('#bulkActionsBtn').prop('disabled', false).text(`Bulk Actions (${selectedCustomers.length})`);
        } else {
            $('#bulkActionsBtn').prop('disabled', true).text('Bulk Actions');
        }
        $('#selectedCount').text(selectedCustomers.length);
    }

    // Bulk actions modal
    $('#bulkActionsBtn').on('click', function() {
        if (selectedCustomers.length > 0) {
            $('#bulkActionsModal').modal('show');
        }
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
        window.location.href = '{{ route("admin.customers.export") }}?' + $.param({
            customer_type: $('#filterCustomerType').val(),
            status: $('#filterStatus').val(),
            verified: $('#filterVerified').val(),
            newsletter: $('#filterNewsletter').val()
        });
    });

    // Refresh table
    $('#refreshBtn').on('click', function() {
        table.ajax.reload();
        $(this).find('i').addClass('fa-spin');
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });
});
</script>
@endpush
