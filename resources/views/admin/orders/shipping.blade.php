@extends('admin.layouts.app')

@section('title', 'Shipping Management')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Shipping Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Shipping</li>
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
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Ready to Ship</h6>
                            <h3 class="mb-0" id="readyToShip">0</h3>
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
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-truck fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">In Transit</h6>
                            <h3 class="mb-0" id="inTransit">0</h3>
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
                            <h6 class="text-muted mb-1">Delivered</h6>
                            <h3 class="mb-0" id="delivered">0</h3>
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
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Delayed</h6>
                            <h3 class="mb-0" id="delayed">0</h3>
                            <small class="text-muted">&gt;7 days in transit</small>
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
                <button class="btn btn-outline-primary btn-sm" id="bulkMarkShippedBtn">
                    <i class="bi bi-truck"></i> Mark as Shipped
                </button>
                <button class="btn btn-outline-success btn-sm" id="bulkPrintLabelsBtn">
                    <i class="bi bi-printer"></i> Print Labels
                </button>
                <button class="btn btn-outline-info btn-sm" id="bulkUpdateTrackingBtn">
                    <i class="bi bi-geo-alt"></i> Update Tracking
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="exportBtn">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </button>
            </div>
        </div>
    </div>

    {{-- Shipping Status Tabs --}}
    <ul class="nav nav-tabs mb-3" id="shippingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ready-tab" data-bs-toggle="tab" data-bs-target="#ready" type="button" role="tab">
                <i class="bi bi-box-seam"></i> Ready to Ship <span class="badge bg-warning ms-2" id="readyCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped" type="button" role="tab">
                <i class="bi bi-truck"></i> Shipped <span class="badge bg-primary ms-2" id="shippedCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="delivered-tab" data-bs-toggle="tab" data-bs-target="#delivered" type="button" role="tab">
                <i class="bi bi-check-circle"></i> Delivered <span class="badge bg-success ms-2" id="deliveredCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="delayed-tab" data-bs-toggle="tab" data-bs-target="#delayed" type="button" role="tab">
                <i class="bi bi-exclamation-triangle"></i> Delayed <span class="badge bg-danger ms-2" id="delayedCount">0</span>
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="shippingTabsContent">
        {{-- Ready to Ship Tab --}}
        <div class="tab-pane fade show active" id="ready" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Orders Ready to Ship</h5>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#readyFilters">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="collapse" id="readyFilters">
                    <div class="card-body border-bottom bg-light">
                        <form id="readyFilterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Shipping Method</label>
                                    <select class="form-select" name="shipping_method">
                                        <option value="">All Methods</option>
                                        <option value="standard">Standard Shipping</option>
                                        <option value="express">Express Shipping</option>
                                        <option value="overnight">Overnight</option>
                                        <option value="international">International</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Warehouse</label>
                                    <select class="form-select" name="warehouse">
                                        <option value="">All Warehouses</option>
                                        <option value="main">Main Warehouse</option>
                                        <option value="secondary">Secondary Warehouse</option>
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
                                    <button type="button" class="btn btn-primary w-100 apply-filters" data-table="ready">
                                        <i class="bi bi-search"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="readyTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="40"><input type="checkbox" class="form-check-input select-all-ready"></th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Shipping Address</th>
                                    <th>Method</th>
                                    <th>Items</th>
                                    <th>Weight</th>
                                    <th>Order Date</th>
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

        {{-- Shipped Tab --}}
        <div class="tab-pane fade" id="shipped" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Shipped Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shippedTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="40"><input type="checkbox" class="form-check-input select-all-shipped"></th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Tracking #</th>
                                    <th>Carrier</th>
                                    <th>Shipped Date</th>
                                    <th>Expected Delivery</th>
                                    <th>Status</th>
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

        {{-- Delivered Tab --}}
        <div class="tab-pane fade" id="delivered" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Delivered Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="deliveredTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Tracking #</th>
                                    <th>Shipped Date</th>
                                    <th>Delivered Date</th>
                                    <th>Delivery Time</th>
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

        {{-- Delayed Tab --}}
        <div class="tab-pane fade" id="delayed" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Delayed Shipments</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> These shipments have been in transit for more than 7 days. Consider contacting the carrier.
                    </div>
                    <div class="table-responsive">
                        <table id="delayedTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Tracking #</th>
                                    <th>Carrier</th>
                                    <th>Shipped Date</th>
                                    <th>Days in Transit</th>
                                    <th>Last Status</th>
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
    </div>

    {{-- Bulk Actions Bar --}}
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" id="bulkActionsBar" style="z-index: 1050;">
        <div class="card shadow-lg border-0">
            <div class="card-body py-2 px-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold"><span id="selectedCount">0</span> selected</span>
                    <div class="vr"></div>
                    <button type="button" class="btn btn-sm btn-primary" id="markShippedBtn">
                        <i class="bi bi-truck"></i> Mark Shipped
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="printLabelsBtn">
                        <i class="bi bi-printer"></i> Print Labels
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Mark as Shipped Modal --}}
<div class="modal fade" id="markShippedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Order as Shipped</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="markShippedForm">
                    <div class="mb-3">
                        <label for="shippingCarrier" class="form-label">Shipping Carrier <span class="text-danger">*</span></label>
                        <select class="form-select" id="shippingCarrier" name="shipping_carrier" required>
                            <option value="">Select Carrier</option>
                            <option value="USPS">USPS</option>
                            <option value="UPS">UPS</option>
                            <option value="FedEx">FedEx</option>
                            <option value="DHL">DHL</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="trackingNumber" class="form-label">Tracking Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="trackingNumber" name="tracking_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="shippedAt" class="form-label">Shipped Date</label>
                        <input type="datetime-local" class="form-control" id="shippedAt" name="shipped_at">
                    </div>
                    <div class="mb-3">
                        <label for="expectedDelivery" class="form-label">Expected Delivery Date</label>
                        <input type="date" class="form-control" id="expectedDelivery" name="expected_delivery_date">
                    </div>
                    <div class="mb-3">
                        <label for="shippingNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="shippingNotes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notifyCustomer" name="notify_customer" checked>
                        <label class="form-check-label" for="notifyCustomer">
                            Send shipping notification to customer
                        </label>
                    </div>
                    <input type="hidden" id="shippingOrderId" name="order_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMarkShipped">
                    <i class="bi bi-truck"></i> Mark as Shipped
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Update Tracking Modal --}}
<div class="modal fade" id="updateTrackingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Tracking Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateTrackingForm">
                    <div class="mb-3">
                        <label for="updateTrackingNumber" class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" id="updateTrackingNumber" name="tracking_number">
                    </div>
                    <div class="mb-3">
                        <label for="updateCarrier" class="form-label">Carrier</label>
                        <select class="form-select" id="updateCarrier" name="shipping_carrier">
                            <option value="USPS">USPS</option>
                            <option value="UPS">UPS</option>
                            <option value="FedEx">FedEx</option>
                            <option value="DHL">DHL</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="updateExpectedDelivery" class="form-label">Expected Delivery</label>
                        <input type="date" class="form-control" id="updateExpectedDelivery" name="expected_delivery_date">
                    </div>
                    <input type="hidden" id="trackingOrderId" name="order_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUpdateTracking">
                    Update Tracking
                </button>
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
    let readyTable, shippedTable, deliveredTable, delayedTable;
    let selectedOrders = [];

    // Initialize DataTables
    function initReadyTable() {
        readyTable = $('#readyTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.status = 'ORDER_PACKED,ORDER_PROCESSING';
                    d.payment_status = 'PAYMENT_PAID';
                    // Add filter data here
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="form-check-input order-checkbox" value="' + data + '">';
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
                    data: 'shipping_address_line1',
                    render: function(data, type, row) {
                        return data + ', ' + row.shipping_city + ', ' + row.shipping_state + ' ' + row.shipping_postal_code;
                    }
                },
                { data: 'shipping_method' },
                { data: 'items_count' },
                {
                    data: 'id',
                    render: function() {
                        return 'N/A'; // Calculate based on items
                    }
                },
                { data: 'created_at_formatted' },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-primary mark-shipped" data-id="' + data + '"><i class="bi bi-truck"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '/shipping-label" class="btn btn-outline-success" target="_blank"><i class="bi bi-printer"></i></a>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[7, 'desc']]
        });
    }

    function initShippedTable() {
        shippedTable = $('#shippedTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.status = 'ORDER_SHIPPED';
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="form-check-input order-checkbox" value="' + data + '">';
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
                    data: 'shipping_tracking_number',
                    render: function(data) {
                        return data ? '<code>' + data + '</code>' : '<span class="text-muted">N/A</span>';
                    }
                },
                { data: 'shipping_carrier' },
                {
                    data: 'shipped_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'expected_delivery_date',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'status_badge',
                    orderable: false
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-info update-tracking" data-id="' + data + '"><i class="bi bi-geo-alt"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[5, 'desc']]
        });
    }

    function initDeliveredTable() {
        deliveredTable = $('#deliveredTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.status = 'ORDER_DELIVERED';
                }
            },
            columns: [
                {
                    data: 'order_number',
                    render: function(data, type, row) {
                        return '<a href="/admin/orders/' + row.id + '">' + data + '</a>';
                    }
                },
                { data: 'customer_info', orderable: false },
                {
                    data: 'shipping_tracking_number',
                    render: function(data) {
                        return data ? '<code>' + data + '</code>' : 'N/A';
                    }
                },
                {
                    data: 'shipped_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'delivered_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (row.shipped_at && row.delivered_at) {
                            const shipped = new Date(row.shipped_at);
                            const delivered = new Date(row.delivered_at);
                            const days = Math.ceil((delivered - shipped) / (1000 * 60 * 60 * 24));
                            return days + ' days';
                        }
                        return 'N/A';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        return '<a href="/admin/orders/' + data + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>';
                    }
                }
            ],
            order: [[4, 'desc']]
        });
    }

    function initDelayedTable() {
        delayedTable = $('#delayedTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.status = 'ORDER_SHIPPED';
                    d.delayed = true; // Custom parameter
                }
            },
            columns: [
                {
                    data: 'order_number',
                    render: function(data, type, row) {
                        return '<a href="/admin/orders/' + row.id + '">' + data + '</a>';
                    }
                },
                { data: 'customer_info', orderable: false },
                {
                    data: 'shipping_tracking_number',
                    render: function(data) {
                        return data ? '<code>' + data + '</code>' : 'N/A';
                    }
                },
                { data: 'shipping_carrier' },
                {
                    data: 'shipped_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    }
                },
                {
                    data: 'shipped_at',
                    render: function(data) {
                        if (data) {
                            const shipped = new Date(data);
                            const now = new Date();
                            const days = Math.ceil((now - shipped) / (1000 * 60 * 60 * 24));
                            return '<span class="badge bg-danger">' + days + ' days</span>';
                        }
                        return 'N/A';
                    }
                },
                {
                    data: 'status_badge',
                    orderable: false
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-warning contact-carrier" data-id="' + data + '"><i class="bi bi-telephone"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[5, 'desc']]
        });
    }

    // Initialize all tables
    initReadyTable();

    // Initialize other tables when their tabs are shown
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');

        if (target === '#shipped' && !shippedTable) {
            initShippedTable();
        } else if (target === '#delivered' && !deliveredTable) {
            initDeliveredTable();
        } else if (target === '#delayed' && !delayedTable) {
            initDelayedTable();
        }
    });

    // Mark as Shipped
    $(document).on('click', '.mark-shipped', function() {
        const orderId = $(this).data('id');
        $('#shippingOrderId').val(orderId);
        $('#markShippedModal').modal('show');
    });

    $('#confirmMarkShipped').on('click', function() {
        const formData = $('#markShippedForm').serialize();

        $.ajax({
            url: '/admin/orders/' + $('#shippingOrderId').val() + '/mark-shipped',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#markShippedModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Order marked as shipped successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });

                readyTable.ajax.reload();
                $('#markShippedForm')[0].reset();
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

    // Update Tracking
    $(document).on('click', '.update-tracking', function() {
        const orderId = $(this).data('id');
        $('#trackingOrderId').val(orderId);
        $('#updateTrackingModal').modal('show');
    });

    $('#confirmUpdateTracking').on('click', function() {
        const formData = $('#updateTrackingForm').serialize();

        $.ajax({
            url: '/admin/orders/' + $('#trackingOrderId').val() + '/update-tracking',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#updateTrackingModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Tracking information updated!',
                    timer: 2000,
                    showConfirmButton: false
                });

                if (shippedTable) shippedTable.ajax.reload();
                $('#updateTrackingForm')[0].reset();
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

    // Checkbox selection
    $(document).on('change', '.order-checkbox', function() {
        const orderId = $(this).val();

        if ($(this).is(':checked')) {
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
            }
        } else {
            selectedOrders = selectedOrders.filter(id => id !== orderId);
        }

        updateBulkActionsBar();
    });

    $('#clearSelection').on('click', function() {
        selectedOrders = [];
        $('.order-checkbox').prop('checked', false);
        updateBulkActionsBar();
    });

    function updateBulkActionsBar() {
        if (selectedOrders.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
            $('#selectedCount').text(selectedOrders.length);
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Bulk actions
    $('#bulkMarkShippedBtn, #markShippedBtn').on('click', function() {
        if (selectedOrders.length === 0) {
            Swal.fire('No Selection', 'Please select orders first', 'warning');
            return;
        }
        Swal.fire('Coming Soon', 'Bulk mark as shipped will be implemented', 'info');
    });

    $('#bulkPrintLabelsBtn, #printLabelsBtn').on('click', function() {
        if (selectedOrders.length === 0) {
            Swal.fire('No Selection', 'Please select orders first', 'warning');
            return;
        }
        Swal.fire('Coming Soon', 'Bulk print labels will be implemented', 'info');
    });
});
</script>
@endpush
