@extends('admin.layouts.app')

@section('title', 'Orders Management')
@section('styles')
<style>
    .btn-primary,
    .bg-primary,
    .badge.bg-primary {
        background-color: #5B914C !important;
        border-color: #5B914C !important;
    }

    .text-primary {
        color: #5B914C !important;
    }

    .btn-primary:hover {
        background-color: #4a7a3d !important;
        border-color: #4a7a3d !important;
    }

    .btn-outline-primary {
        color: #5B914C !important;
        border-color: #5B914C !important;
    }

    .btn-outline-primary:hover {
        background-color: #5B914C !important;
        border-color: #5B914C !important;
        color: white !important;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Orders Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Orders</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('orders.create'))
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Order
            </a>
            @endif
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Orders</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Orders</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="bi bi-gear fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Processing</h6>
                            <h3 class="mb-0">{{ number_format($stats['processing_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Today's Revenue</h6>
                            <h3 class="mb-0">{{ store_currency_symbol() }} {{ number_format($stats['today_revenue'], 2) }}</h3>
                            <small class="text-muted">{{ $stats['today_orders'] }} orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h6 class="card-title mb-3">Quick Filters</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.orders.pending') }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-clock-history"></i> Pending
                </a>
                <a href="{{ route('admin.orders.processing') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-gear"></i> Processing
                </a>
                <a href="{{ route('admin.orders.shipped') }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-truck"></i> Shipped
                </a>
                <a href="{{ route('admin.orders.delivered') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-check-circle"></i> Delivered
                </a>
                <a href="{{ route('admin.orders.cancelled') }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle"></i> Cancelled
                </a>
                <a href="{{ route('admin.orders.today') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-calendar-day"></i> Today's Orders
                </a>
                <a href="{{ route('admin.orders.refunds') }}" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Refunds
                </a>
                <a href="{{ route('admin.orders.reports') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </div>
        </div>
    </div>
    {{-- Bulk Actions Bar --}}
    <div id="bulkActionsBar" class="alert alert-info d-none mb-0 border-0 rounded-0" role="alert">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <strong><span id="selectedCount">0</span> order(s) selected</strong>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary" id="bulkUpdateStatusBtn">
                    <i class="bi bi-arrow-repeat"></i> Update Status
                </button>
                <button type="button" class="btn btn-success" id="bulkUpdatePaymentBtn">
                    <i class="bi bi-credit-card"></i> Update Payment
                </button>
                <button type="button" class="btn btn-danger" id="bulkDeleteBtn">
                    <i class="bi bi-trash"></i> Delete
                </button>
                <button type="button" class="btn btn-secondary" id="clearSelectionBtn">
                    <i class="bi bi-x"></i> Clear
                </button>
            </div>
        </div>
    </div>
    {{-- Main Orders Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">All Orders</h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filtersCollapse">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="collapse" id="filtersCollapse">
            <div class="card-body border-bottom bg-light">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" id="filterCustomer">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order Status</label>
                            <select class="form-select" name="status" id="filterStatus">
                                <option value="">All Statuses</option>
                                @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" name="payment_status" id="filterPaymentStatus">
                                <option value="">All Payment Statuses</option>
                                @foreach($paymentStatusList as $status)
                                <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Order Source</label>
                            <select class="form-select" name="order_source" id="filterSource">
                                <option value="">All Sources</option>
                                <option value="web">Web</option>
                                <option value="mobile">Mobile</option>
                                <option value="pos">POS</option>
                                <option value="phone">Phone</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" id="filterDateFrom">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" id="filterDateTo">
                        </div>

                        <div class="col-12">
                            <button type="button" class="btn btn-primary" id="applyFilters">
                                <i class="bi bi-check-circle"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            {{-- Bulk Actions --}}
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info mb-0 d-flex align-items-center justify-content-between">
                    <span>
                        <strong id="selectedCount">0</strong> order(s) selected
                    </span>
                    <div>
                        <button class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ordersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Date</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables will populate this --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. Only unpaid and unshipped orders can be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Order</button>
            </div>
        </div>
    </div>
</div>
{{-- Bulk Update Status Modal --}}
<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Update status for <strong><span id="bulkStatusCount">0</span> order(s)</strong></p>
                <div class="mb-3">
                    <label class="form-label">New Status</label>
                    <select class="form-select" id="bulkStatusSelect">
                        <option value="">-- Select Status --</option>
                        @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkStatus">Update</button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Update Payment Status Modal --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Update payment status for <strong><span id="bulkPaymentCount">0</span> order(s)</strong></p>
                <div class="mb-3">
                    <label class="form-label">New Payment Status</label>
                    <select class="form-select" id="bulkPaymentSelect">
                        <option value="">-- Select Payment Status --</option>
                        @foreach($paymentStatusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmBulkPayment">Update</button>
            </div>
        </div>
    </div>
</div>

{{-- Quick Status Update Modal (Single Order) --}}
<div class="modal fade" id="quickStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="quickUpdateOrderId">
                <div class="mb-3">
                    <label class="form-label">Order Status</label>
                    <select class="form-select" id="quickStatusSelect">
                        <option value="">-- No Change --</option>
                        @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Status</label>
                    <select class="form-select" id="quickPaymentSelect">
                        <option value="">-- No Change --</option>
                        @foreach($paymentStatusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmQuickUpdate">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    .text-purple {
        color: #6f42c1 !important;
    }
    .btn-outline-purple {
        color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-outline-purple:hover {
        color: #fff;
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#filterCustomer').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search customer...',
        allowClear: true
    });
    let ordersTable;
    let selectedOrders = [];
    let deleteOrderId = null;
     // ✅ Get customer_id from PHP variable (cleaner than request())
    const urlCustomerId = '{{ $selectedCustomerId ?? "" }}';

    // Set dropdown value from URL if exists
    if (urlCustomerId) {
        $('#filterCustomer').val(urlCustomerId);
    }

    // Initialize Select2 if using it
    if ($.fn.select2) {
        $('#filterCustomer').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search customer...',
            allowClear: true
        });
    }
    // Initialize DataTable
    function initDataTable() {
        ordersTable = $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.orders.data") }}',
                data: function(d) {
                    d.status = $('#filterStatus').val();
                    d.payment_status = $('#filterPaymentStatus').val();
                    d.order_source = $('#filterSource').val();
                    d.customer_id = $('#filterCustomer').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'order_number_link', name: 'order_number' },
                { data: 'customer_info', name: 'customer_id', orderable: false },
                { data: 'items_count', name: 'items_count', searchable: false },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'payment_info', name: 'payment_status_key_code' },
                { data: 'status_badge', name: 'status_key_code' },
                { data: 'order_source', name: 'order_source' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[8, 'desc']],
            pageLength: 25,
            language: {
                processing: `
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Loading Orders...</div>
                    </div>
                `
            },
            drawCallback: function() {
                // Reinitialize checkboxes after redraw
                updateCheckboxStates();
            }
        });
    }

    initDataTable();

    // Apply Filters
    $('#applyFilters').on('click', function() {
        ordersTable.ajax.reload();
    });

    // Clear Filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        ordersTable.ajax.reload();
    });

    // Select All Checkboxes
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.order-checkbox').prop('checked', isChecked);

        if (isChecked) {
            $('.order-checkbox').each(function() {
                const orderId = $(this).val();
                if (!selectedOrders.includes(orderId)) {
                    selectedOrders.push(orderId);
                }
            });
        } else {
            selectedOrders = [];
        }

        updateBulkActionsBar();
    });

    // Individual Checkbox
    $(document).on('change', '.order-checkbox', function() {
        const orderId = $(this).val();

        if ($(this).is(':checked')) {
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
            }
        } else {
            selectedOrders = selectedOrders.filter(id => id !== orderId);
            $('#selectAll').prop('checked', false);
        }

        updateBulkActionsBar();
    });

    // Update Checkbox States
    function updateCheckboxStates() {
        $('.order-checkbox').each(function() {
            const orderId = $(this).val();
            if (selectedOrders.includes(orderId)) {
                $(this).prop('checked', true);
            }
        });
    }

    // Update Bulk Actions Bar
    function updateBulkActionsBar() {
        if (selectedOrders.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
            $('#selectedCount').text(selectedOrders.length);
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Clear Selection
    $('#clearSelectionBtn').on('click', function() {
        selectedOrders = [];
        $('.order-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActionsBar();
    });

    // ====================================
    // BULK UPDATE STATUS
    // ====================================
    $('#bulkUpdateStatusBtn').on('click', function() {
        if (selectedOrders.length === 0) return;

        $('#bulkStatusCount').text(selectedOrders.length);
        $('#bulkStatusModal').modal('show');
    });

    $('#confirmBulkStatus').on('click', function() {
        const statusCode = $('#bulkStatusSelect').val();

        if (!statusCode) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a status'
            });
            return;
        }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        $.ajax({
            url: '{{ route("admin.orders.bulk-update-status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_ids: selectedOrders,
                status_key_code: statusCode
            },
            success: function(response) {
                $('#bulkStatusModal').modal('hide');
                $('#confirmBulkStatus').prop('disabled', false).html('Update');
                $('#bulkStatusSelect').val('');

                if (response.success) {
                    let message = response.message;
                    if (response.errors && response.errors.length > 0) {
                        message += '<br><br><small class="text-danger">' + response.errors.join('<br>') + '</small>';
                    }

                    Swal.fire({
                        icon: response.failed > 0 ? 'warning' : 'success',
                        title: response.failed > 0 ? 'Partially Completed' : 'Success!',
                        html: message,
                        timer: 3000
                    });

                    // Reload table and clear selection
                    ordersTable.ajax.reload();
                    selectedOrders = [];
                    $('#selectAll').prop('checked', false);
                    updateBulkActionsBar();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                $('#bulkStatusModal').modal('hide');
                $('#confirmBulkStatus').prop('disabled', false).html('Update');

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred'
                });
            }
        });
    });

    // ====================================
    // BULK UPDATE PAYMENT STATUS
    // ====================================
    $('#bulkUpdatePaymentBtn').on('click', function() {
        if (selectedOrders.length === 0) return;

        $('#bulkPaymentCount').text(selectedOrders.length);
        $('#bulkPaymentModal').modal('show');
    });

    $('#confirmBulkPayment').on('click', function() {
        const paymentStatusCode = $('#bulkPaymentSelect').val();

        if (!paymentStatusCode) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a payment status'
            });
            return;
        }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        $.ajax({
            url: '{{ route("admin.orders.bulk-update-payment-status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_ids: selectedOrders,
                payment_status_key_code: paymentStatusCode
            },
            success: function(response) {
                $('#bulkPaymentModal').modal('hide');
                $('#confirmBulkPayment').prop('disabled', false).html('Update');
                $('#bulkPaymentSelect').val('');

                if (response.success) {
                    let message = response.message;
                    if (response.errors && response.errors.length > 0) {
                        message += '<br><br><small class="text-danger">' + response.errors.join('<br>') + '</small>';
                    }

                    Swal.fire({
                        icon: response.failed > 0 ? 'warning' : 'success',
                        title: response.failed > 0 ? 'Partially Completed' : 'Success!',
                        html: message,
                        timer: 3000
                    });

                    // Reload table and clear selection
                    ordersTable.ajax.reload();
                    selectedOrders = [];
                    $('#selectAll').prop('checked', false);
                    updateBulkActionsBar();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                $('#bulkPaymentModal').modal('hide');
                $('#confirmBulkPayment').prop('disabled', false).html('Update');

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred'
                });
            }
        });
    });

    // ====================================
    // QUICK UPDATE SINGLE ORDER
    // ====================================
    $(document).on('click', '.quick-update-btn', function() {
        const orderId = $(this).data('id');
        const currentStatus = $(this).data('status');
        const currentPayment = $(this).data('payment');

        $('#quickUpdateOrderId').val(orderId);
        $('#quickStatusSelect').val(currentStatus);
        $('#quickPaymentSelect').val(currentPayment);
        $('#quickStatusModal').modal('show');
    });

    $('#confirmQuickUpdate').on('click', function() {
    const orderId = $('#quickUpdateOrderId').val();
    const statusCode = $('#quickStatusSelect').val();
    const paymentCode = $('#quickPaymentSelect').val();

    if (!statusCode && !paymentCode) {
        Swal.fire({
            icon: 'warning',
            title: 'No Changes',
            text: 'Please select at least one status to update'
        });
        return;
    }

    // ✅ NEW: Warn if skipping confirmation
    const currentStatus = $('.quick-update-btn[data-id="' + orderId + '"]').data('status');
        if (currentStatus === 'ORDER_PENDING' &&
            statusCode &&
            ['ORDER_PROCESSING', 'ORDER_PACKED', 'ORDER_SHIPPED', 'ORDER_DELIVERED'].includes(statusCode)) {

            Swal.fire({
                icon: 'info',
                title: 'Confirmation Required',
                html: 'This order will be automatically <strong>CONFIRMED</strong> first, then updated to <strong>' +
                    statusCode.replace('ORDER_', '') + '</strong>.<br><br>' +
                    'Inventory will be deducted from reserved stock.',
                showCancelButton: true,
                confirmButtonText: 'Continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performQuickUpdate(orderId, statusCode, paymentCode);
                }
            });
            return;
        }

        performQuickUpdate(orderId, statusCode, paymentCode);
    });

    function performQuickUpdate(orderId, statusCode, paymentCode) {
        const $btn = $('#confirmQuickUpdate');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        const data = {
            _token: '{{ csrf_token() }}'
        };

        if (statusCode) data.status_key_code = statusCode;
        if (paymentCode) data.payment_status_key_code = paymentCode;

        $.ajax({
            url: `/admin/orders/${orderId}/quick-update-status`,
            type: 'POST',
            data: data,
            success: function(response) {
                $('#quickStatusModal').modal('hide');
                $btn.prop('disabled', false).html('Update');

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    ordersTable.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                $('#quickStatusModal').modal('hide');
                $btn.prop('disabled', false).html('Update');

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred'
                });
            }
        });
    }

    // ====================================
    // DELETE ORDER (SINGLE)
    // ====================================
    $(document).on('click', '.delete-order', function() {
        deleteOrderId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (deleteOrderId) {
            $.ajax({
                url: `/admin/orders/${deleteOrderId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        ordersTable.ajax.reload();
                        selectedOrders = selectedOrders.filter(id => id !== deleteOrderId);
                        updateBulkActionsBar();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }

                    deleteOrderId = null;
                },
                error: function(xhr) {
                    $('#deleteModal').modal('hide');

                    let errorMessage = 'An error occurred while deleting the order.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });

                    deleteOrderId = null;
                }
            });
        }
    });

    // ====================================
    // BULK DELETE
    // ====================================
    $('#bulkDeleteBtn').on('click', function() {
        if (selectedOrders.length === 0) return;

        Swal.fire({
            icon: 'warning',
            title: 'Confirm Bulk Delete',
            html: `Are you sure you want to delete <strong>${selectedOrders.length}</strong> order(s)?<br><small class="text-muted">This action cannot be undone.</small>`,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show processing indicator
                Swal.fire({
                    title: 'Processing...',
                    html: 'Deleting orders, please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Process deletions
                let deleted = 0;
                let failed = 0;
                const errors = [];

                Promise.all(selectedOrders.map(orderId => {
                    return $.ajax({
                        url: `/admin/orders/${orderId}`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' }
                    })
                    .then(() => deleted++)
                    .catch(xhr => {
                        failed++;
                        errors.push(xhr.responseJSON?.message || 'Unknown error');
                    });
                })).then(() => {
                    let errorHtml = '';
                    if (errors.length > 0) {
                        errorHtml = '<br><br><small class="text-danger">' + errors.slice(0, 5).join('<br>') + '</small>';
                    }

                    Swal.fire({
                        icon: failed > 0 ? 'warning' : 'success',
                        title: 'Bulk Delete Complete',
                        html: `${deleted} order(s) deleted successfully${failed > 0 ? `, ${failed} failed` : ''}${errorHtml}`,
                        timer: 3000
                    });

                    // Reload table and clear selection
                    ordersTable.ajax.reload();
                    selectedOrders = [];
                    $('#selectAll').prop('checked', false);
                    updateBulkActionsBar();
                });
            }
        });
    });
});

</script>
@endpush
