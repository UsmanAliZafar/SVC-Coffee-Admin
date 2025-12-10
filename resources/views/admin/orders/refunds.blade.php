@extends('admin.layouts.app')

@section('title', 'Refunds Management')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Refunds Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Refunds</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
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
                            <h6 class="text-muted mb-1">Pending Refunds</h6>
                            <h3 class="mb-0" id="pendingRefunds">0</h3>
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
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completed Refunds</h6>
                            <h3 class="mb-0" id="completedRefunds">0</h3>
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
                                <i class="bi bi-arrow-counterclockwise fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Partial Refunds</h6>
                            <h3 class="mb-0" id="partialRefunds">0</h3>
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
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                                {{ store_currency_symbol() }}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Refunded</h6>
                            <h3 class="mb-0" id="totalRefunded">{{ store_currency_symbol() }} 0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h6 class="card-title mb-3">Quick Actions</h6>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary btn-sm" id="bulkApproveBtn">
                    <i class="bi bi-check-circle"></i> Approve Selected
                </button>
                <button class="btn btn-outline-danger btn-sm" id="bulkRejectBtn">
                    <i class="bi bi-x-circle"></i> Reject Selected
                </button>
                <button class="btn btn-outline-info btn-sm" id="exportBtn">
                    <i class="bi bi-file-earmark-excel"></i> Export Report
                </button>
            </div>
        </div>
    </div>

    {{-- Refunds Status Tabs --}}
    <ul class="nav nav-tabs mb-3" id="refundsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                <i class="bi bi-list-ul"></i> All Refunds <span class="badge bg-secondary ms-2" id="allCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                <i class="bi bi-clock-history"></i> Pending <span class="badge bg-warning ms-2" id="pendingCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                <i class="bi bi-check-circle"></i> Approved <span class="badge bg-success ms-2" id="approvedCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                <i class="bi bi-clipboard-check"></i> Completed <span class="badge bg-primary ms-2" id="completedCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                <i class="bi bi-x-circle"></i> Rejected <span class="badge bg-danger ms-2" id="rejectedCount">0</span>
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="refundsTabsContent">
        {{-- All Refunds Tab --}}
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">All Refunds & Returns</h5>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#allFilters">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="collapse" id="allFilters">
                    <div class="card-body border-bottom bg-light">
                        <form id="allFilterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Refund Type</label>
                                    <select class="form-select" name="refund_type">
                                        <option value="">All Types</option>
                                        <option value="full">Full Refund</option>
                                        <option value="partial">Partial Refund</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" name="payment_method">
                                        <option value="">All Methods</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="date" class="form-control" name="date_from">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="date" class="form-control" name="date_to">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-primary w-100 apply-filters" data-table="all">
                                        <i class="bi bi-search"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="allRefundsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="40"><input type="checkbox" class="form-check-input select-all"></th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Refund Type</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested Date</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTable will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Refunds Tab --}}
        <div class="tab-pane fade" id="pending" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Pending Refund Requests</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> These refund requests require your review and approval.
                    </div>
                    <div class="table-responsive">
                        <table id="pendingRefundsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="40"><input type="checkbox" class="form-check-input select-all-pending"></th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Refund Type</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Requested</th>
                                    <th>Days Pending</th>
                                    <th width="180">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTable will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved Refunds Tab --}}
        <div class="tab-pane fade" id="approved" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Approved Refunds</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="approvedRefundsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Approved By</th>
                                    <th>Approved Date</th>
                                    <th>Payment Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTable will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Completed Refunds Tab --}}
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Completed Refunds</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="completedRefundsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Refund Type</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Completed Date</th>
                                    <th>Processing Time</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTable will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rejected Refunds Tab --}}
        <div class="tab-pane fade" id="rejected" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Rejected Refund Requests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rejectedRefundsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Rejected By</th>
                                    <th>Rejected Date</th>
                                    <th>Rejection Reason</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTable will populate this --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" id="bulkActionsBar" style="z-index: 1050;">
        <div class="card shadow-lg border-0">
            <div class="card-body py-2 px-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold"><span id="selectedCount">0</span> selected</span>
                    <div class="vr"></div>
                    <button type="button" class="btn btn-sm btn-success" id="approveSelectedBtn">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="rejectSelectedBtn">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Approve Refund Modal --}}
<div class="modal fade" id="approveRefundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Refund</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Approving this refund will process the payment reversal.
                </div>
                <form id="approveRefundForm">
                    <div class="mb-3">
                        <label class="form-label">Refund Amount</label>
                        <input type="number" class="form-control" id="approveAmount" name="amount" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" id="approvePaymentMethod" name="payment_method">
                            <option value="original">Original Payment Method</option>
                            <option value="store_credit">Store Credit</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="approveNotes" class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="approveNotes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notifyCustomerApprove" name="notify_customer" checked>
                        <label class="form-check-label" for="notifyCustomerApprove">
                            Send approval notification to customer
                        </label>
                    </div>
                    <input type="hidden" id="approveOrderId" name="order_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveRefund">
                    <i class="bi bi-check-circle"></i> Approve Refund
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Reject Refund Modal --}}
<div class="modal fade" id="rejectRefundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Refund Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectRefundForm">
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <select class="form-select" id="rejectReason" name="rejection_reason" required>
                            <option value="">Select a reason...</option>
                            <option value="outside_return_window">Outside Return Window</option>
                            <option value="item_used_damaged">Item Used/Damaged by Customer</option>
                            <option value="incomplete_return">Incomplete Return</option>
                            <option value="missing_items">Missing Items</option>
                            <option value="policy_violation">Policy Violation</option>
                            <option value="other">Other (Specify Below)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rejectDetails" class="form-label">Additional Details <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectDetails" name="rejection_details" rows="4" required
                                  placeholder="Provide detailed explanation for the customer..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notifyCustomerReject" name="notify_customer" checked>
                        <label class="form-check-label" for="notifyCustomerReject">
                            Send rejection notification to customer
                        </label>
                    </div>
                    <input type="hidden" id="rejectOrderId" name="order_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectRefund">
                    <i class="bi bi-x-circle"></i> Reject Request
                </button>
            </div>
        </div>
    </div>
</div>

{{-- View Refund Details Modal --}}
<div class="modal fade" id="viewRefundModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refund Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="refundDetailsContent">
                    {{-- Loaded dynamically --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let allRefundsTable, pendingRefundsTable, approvedRefundsTable, completedRefundsTable, rejectedRefundsTable;
    let selectedRefunds = [];

    // Initialize All Refunds Table
    function initAllRefundsTable() {
        allRefundsTable = $('#allRefundsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.is_refunded = true;
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="form-check-input refund-checkbox" value="' + data + '">';
                    }
                },
                {
                    data: 'order_number',
                    render: function(data, type, row) {
                        return '<a href="/admin/orders/' + row.id + '" class="fw-bold">' + data + '</a>';
                    }
                },
                { data: 'customer_info', orderable: false },
                {
                    data: 'refunded_amount',
                    render: function(data, type, row) {
                        if (data < row.total_amount) {
                            return '<span class="badge bg-info">Partial</span>';
                        }
                        return '<span class="badge bg-dark">Full</span>';
                    }
                },
                {
                    data: 'refunded_amount',
                    render: function(data) {
                        return '<strong class="text-danger">' + parseFloat(data).toFixed(2) + '</strong>';
                    }
                },
                {
                    data: 'cancellation_reason',
                    render: function(data) {
                        return data || '<span class="text-muted">No reason provided</span>';
                    }
                },
                {
                    data: 'payment_status_key_code',
                    render: function(data) {
                        if (data === 'PAYMENT_REFUNDED') {
                            return '<span class="badge bg-success">Completed</span>';
                        } else if (data === 'PAYMENT_PARTIALLY_REFUNDED') {
                            return '<span class="badge bg-info">Partial</span>';
                        }
                        return '<span class="badge bg-warning">Pending</span>';
                    }
                },
                {
                    data: 'refunded_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-info view-refund" data-id="' + data + '"><i class="bi bi-eye"></i></button>';

                        if (row.payment_status_key_code !== 'PAYMENT_REFUNDED') {
                            actions += '<button class="btn btn-outline-success approve-refund" data-id="' + data + '" data-amount="' + row.refunded_amount + '"><i class="bi bi-check-circle"></i></button>';
                            actions += '<button class="btn btn-outline-danger reject-refund" data-id="' + data + '"><i class="bi bi-x-circle"></i></button>';
                        }

                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[7, 'desc']]
        });
    }

    // Initialize Pending Refunds Table
    function initPendingRefundsTable() {
        pendingRefundsTable = $('#pendingRefundsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.is_refunded = true;
                    d.payment_status = 'PAYMENT_PENDING';
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="form-check-input refund-checkbox" value="' + data + '">';
                    }
                },
                {
                    data: 'order_number',
                    render: function(data, type, row) {
                        return '<a href="/admin/orders/' + row.id + '">' + data + '</a>';
                    }
                },
                { data: 'customer_info', orderable: false },
                {
                    data: 'refunded_amount',
                    render: function(data, type, row) {
                        if (data < row.total_amount) {
                            return '<span class="badge bg-info">Partial</span>';
                        }
                        return '<span class="badge bg-dark">Full</span>';
                    }
                },
                {
                    data: 'refunded_amount',
                    render: function(data) {
                        return '<strong>' + parseFloat(data).toFixed(2) + '</strong>';
                    }
                },
                {
                    data: 'cancellation_reason',
                    render: function(data) {
                        return data || 'No reason';
                    }
                },
                {
                    data: 'refunded_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'refunded_at',
                    render: function(data) {
                        if (data) {
                            const refundDate = new Date(data);
                            const now = new Date();
                            const days = Math.ceil((now - refundDate) / (1000 * 60 * 60 * 24));

                            let badgeClass = 'bg-warning';
                            if (days > 7) badgeClass = 'bg-danger';

                            return '<span class="badge ' + badgeClass + '">' + days + ' days</span>';
                        }
                        return 'N/A';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-success approve-refund" data-id="' + data + '" data-amount="' + row.refunded_amount + '" title="Approve"><i class="bi bi-check-circle"></i></button>';
                        actions += '<button class="btn btn-outline-danger reject-refund" data-id="' + data + '" title="Reject"><i class="bi bi-x-circle"></i></button>';
                        actions += '<button class="btn btn-outline-info view-refund" data-id="' + data + '" title="View Details"><i class="bi bi-eye"></i></button>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[7, 'desc']]
        });
    }

    // Initialize All Tables
    initAllRefundsTable();

    // Initialize other tables when their tabs are shown
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');

        if (target === '#pending' && !pendingRefundsTable) {
            initPendingRefundsTable();
        }
    });

    // Approve Refund
    $(document).on('click', '.approve-refund', function() {
        const orderId = $(this).data('id');
        const amount = $(this).data('amount');

        $('#approveOrderId').val(orderId);
        $('#approveAmount').val(parseFloat(amount).toFixed(2));
        $('#approveRefundModal').modal('show');
    });

    $('#confirmApproveRefund').on('click', function() {
        const orderId = $('#approveOrderId').val();
        const formData = $('#approveRefundForm').serialize();

        $.ajax({
            url: '/admin/orders/' + orderId + '/approve-refund',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#approveRefundModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Refund Approved!',
                    text: 'The refund has been approved and will be processed.',
                    timer: 2000,
                    showConfirmButton: false
                });

                if (allRefundsTable) allRefundsTable.ajax.reload();
                if (pendingRefundsTable) pendingRefundsTable.ajax.reload();
                $('#approveRefundForm')[0].reset();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred'
                });
            }
        });
    });

    // Reject Refund
    $(document).on('click', '.reject-refund', function() {
        const orderId = $(this).data('id');
        $('#rejectOrderId').val(orderId);
        $('#rejectRefundModal').modal('show');
    });

    $('#confirmRejectRefund').on('click', function() {
        const orderId = $('#rejectOrderId').val();
        const formData = $('#rejectRefundForm').serialize();

        if (!$('#rejectReason').val() || !$('#rejectDetails').val()) {
            Swal.fire('Required Fields', 'Please fill in all required fields', 'warning');
            return;
        }

        $.ajax({
            url: '/admin/orders/' + orderId + '/reject-refund',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#rejectRefundModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Refund Rejected',
                    text: 'The refund request has been rejected.',
                    timer: 2000,
                    showConfirmButton: false
                });

                if (allRefundsTable) allRefundsTable.ajax.reload();
                if (pendingRefundsTable) pendingRefundsTable.ajax.reload();
                $('#rejectRefundForm')[0].reset();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred'
                });
            }
        });
    });

    // View Refund Details
    $(document).on('click', '.view-refund', function() {
        const orderId = $(this).data('id');

        $.ajax({
            url: '/admin/orders/' + orderId,
            type: 'GET',
            success: function(response) {
                // Build refund details HTML
                let html = '<div class="row">';
                html += '<div class="col-md-6"><strong>Order Number:</strong> ' + response.order_number + '</div>';
                html += '<div class="col-md-6"><strong>Customer:</strong> ' + response.customer_name + '</div>';
                html += '<div class="col-md-6 mt-2"><strong>Refund Amount:</strong> $' + parseFloat(response.refunded_amount).toFixed(2) + '</div>';
                html += '<div class="col-md-6 mt-2"><strong>Refund Date:</strong> ' + (response.refunded_at || 'N/A') + '</div>';
                html += '<div class="col-12 mt-3"><strong>Reason:</strong><br>' + (response.cancellation_reason || 'No reason provided') + '</div>';
                html += '</div>';

                $('#refundDetailsContent').html(html);
                $('#viewRefundModal').modal('show');
            }
        });
    });

    // Checkbox selection
    $(document).on('change', '.refund-checkbox', function() {
        const refundId = $(this).val();

        if ($(this).is(':checked')) {
            if (!selectedRefunds.includes(refundId)) {
                selectedRefunds.push(refundId);
            }
        } else {
            selectedRefunds = selectedRefunds.filter(id => id !== refundId);
        }

        updateBulkActionsBar();
    });

    $('#clearSelection').on('click', function() {
        selectedRefunds = [];
        $('.refund-checkbox').prop('checked', false);
        updateBulkActionsBar();
    });

    function updateBulkActionsBar() {
        if (selectedRefunds.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
            $('#selectedCount').text(selectedRefunds.length);
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Bulk Actions
    $('#bulkApproveBtn, #approveSelectedBtn').on('click', function() {
        if (selectedRefunds.length === 0) {
            Swal.fire('No Selection', 'Please select refunds first', 'warning');
            return;
        }
        Swal.fire('Coming Soon', 'Bulk approve will be implemented', 'info');
    });

    $('#bulkRejectBtn, #rejectSelectedBtn').on('click', function() {
        if (selectedRefunds.length === 0) {
            Swal.fire('No Selection', 'Please select refunds first', 'warning');
            return;
        }
        Swal.fire('Coming Soon', 'Bulk reject will be implemented', 'info');
    });

    $('#exportBtn').on('click', function() {
        Swal.fire('Coming Soon', 'Export functionality will be implemented', 'info');
    });
});
</script>
@endpush
