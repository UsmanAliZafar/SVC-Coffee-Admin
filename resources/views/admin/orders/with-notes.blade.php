@extends('admin.layouts.app')

@section('title', 'Orders with Notes')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Orders with Notes</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">With Notes</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('orders.create'))
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Order
            </a>
            @endif
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Orders
            </a>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i> This page displays all orders that have customer notes, admin notes, or internal notes attached.
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-chat-left-text fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Orders with Customer Notes</h6>
                            <h3 class="mb-0" id="customerNotesCount">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Orders with Admin Notes</h6>
                            <h3 class="mb-0" id="adminNotesCount">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="bi bi-file-text fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Orders with Internal Notes</h6>
                            <h3 class="mb-0" id="internalNotesCount">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notes Type Tabs --}}
    <ul class="nav nav-tabs mb-3" id="notesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-notes-tab" data-bs-toggle="tab" data-bs-target="#all-notes" type="button" role="tab">
                <i class="bi bi-list-ul"></i> All Orders with Notes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="customer-notes-tab" data-bs-toggle="tab" data-bs-target="#customer-notes" type="button" role="tab">
                <i class="bi bi-person"></i> Customer Notes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="admin-notes-tab" data-bs-toggle="tab" data-bs-target="#admin-notes" type="button" role="tab">
                <i class="bi bi-shield-check"></i> Admin Notes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="internal-notes-tab" data-bs-toggle="tab" data-bs-target="#internal-notes" type="button" role="tab">
                <i class="bi bi-file-lock"></i> Internal Notes
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="notesTabsContent">
        {{-- All Orders with Notes Tab --}}
        <div class="tab-pane fade show active" id="all-notes" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">All Orders with Notes</h5>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#allNotesFilters">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="collapse" id="allNotesFilters">
                    <div class="card-body border-bottom bg-light">
                        <form id="allNotesFilterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Order Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="ORDER_PENDING">Pending</option>
                                        <option value="ORDER_CONFIRMED">Confirmed</option>
                                        <option value="ORDER_PROCESSING">Processing</option>
                                        <option value="ORDER_SHIPPED">Shipped</option>
                                        <option value="ORDER_DELIVERED">Delivered</option>
                                        <option value="ORDER_CANCELLED">Cancelled</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Note Type</label>
                                    <select class="form-select" name="note_type">
                                        <option value="">All Types</option>
                                        <option value="customer">Customer Notes</option>
                                        <option value="admin">Admin Notes</option>
                                        <option value="internal">Internal Notes</option>
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
                                    <button type="button" class="btn btn-primary w-100" id="applyAllNotesFilters">
                                        <i class="bi bi-search"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="allNotesTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Notes Preview</th>
                                    <th>Note Types</th>
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

        {{-- Customer Notes Tab --}}
        <div class="tab-pane fade" id="customer-notes" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Orders with Customer Notes</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        <i class="bi bi-info-circle"></i> These are notes provided by customers during checkout or afterward.
                    </div>
                    <div class="table-responsive">
                        <table id="customerNotesTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Customer Notes</th>
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

        {{-- Admin Notes Tab --}}
        <div class="tab-pane fade" id="admin-notes" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Orders with Admin Notes</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Admin notes are visible to both admins and customers.
                    </div>
                    <div class="table-responsive">
                        <table id="adminNotesTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Admin Notes</th>
                                    <th>Last Updated</th>
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

        {{-- Internal Notes Tab --}}
        <div class="tab-pane fade" id="internal-notes" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-file-lock"></i> Orders with Internal Notes</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-lock"></i> Internal notes are private and only visible to admin staff members.
                    </div>
                    <div class="table-responsive">
                        <table id="internalNotesTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Internal Notes</th>
                                    <th>Last Updated</th>
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

</div>

{{-- View/Edit Notes Modal --}}
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderInfoSection" class="mb-4">
                    {{-- Order info will be loaded here --}}
                </div>

                {{-- Customer Notes --}}
                <div class="mb-4">
                    <h6 class="border-bottom pb-2"><i class="bi bi-person text-primary"></i> Customer Notes</h6>
                    <div id="customerNotesContent" class="p-3 bg-light rounded">
                        <p class="text-muted mb-0" id="noCustomerNotes">No customer notes available</p>
                        <div id="customerNotesText" style="display:none;"></div>
                    </div>
                </div>

                {{-- Admin Notes --}}
                <div class="mb-4">
                    <h6 class="border-bottom pb-2"><i class="bi bi-shield-check text-warning"></i> Admin Notes</h6>
                    <div id="adminNotesContent">
                        <textarea class="form-control" id="adminNotesTextarea" rows="4" placeholder="Add admin notes (visible to customer)..."></textarea>
                        <small class="text-muted">These notes will be visible to the customer.</small>
                    </div>
                </div>

                {{-- Internal Notes --}}
                <div class="mb-3">
                    <h6 class="border-bottom pb-2"><i class="bi bi-file-lock text-info"></i> Internal Notes</h6>
                    <div id="internalNotesContent">
                        <textarea class="form-control" id="internalNotesTextarea" rows="4" placeholder="Add internal notes (private, staff only)..."></textarea>
                        <small class="text-muted">These notes are private and only visible to admin staff.</small>
                    </div>
                </div>

                <input type="hidden" id="modalOrderId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveNotesBtn">
                    <i class="bi bi-save"></i> Save Notes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Quick Note Modal --}}
<div class="modal fade" id="quickNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Quick Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickNoteForm">
                    <div class="mb-3">
                        <label class="form-label">Note Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="quickNoteType" name="note_type" required>
                            <option value="">Select Type</option>
                            <option value="admin">Admin Note (Visible to Customer)</option>
                            <option value="internal">Internal Note (Private)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quickNoteText" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="quickNoteText" name="note" rows="4" required></textarea>
                    </div>
                    <input type="hidden" id="quickNoteOrderId" name="order_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveQuickNoteBtn">
                    <i class="bi bi-plus-circle"></i> Add Note
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
.notes-preview {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.note-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let allNotesTable, customerNotesTable, adminNotesTable, internalNotesTable;

    // Initialize All Notes Table
    function initAllNotesTable() {
        allNotesTable = $('#allNotesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.has_notes = true;
                    d.status = $('#allNotesFilterForm select[name="status"]').val();
                    d.note_type = $('#allNotesFilterForm select[name="note_type"]').val();
                    d.date_from = $('#allNotesFilterForm input[name="date_from"]').val();
                    d.date_to = $('#allNotesFilterForm input[name="date_to"]').val();
                }
            },
            columns: [
                {
                    data: 'order_number',
                    render: function(data, type, row) {
                        return '<a href="/admin/orders/' + row.id + '" class="fw-bold">' + data + '</a>';
                    }
                },
                { data: 'customer_info', orderable: false },
                {
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                { data: 'status_badge', orderable: false },
                {
                    data: 'customer_notes',
                    orderable: false,
                    render: function(data, type, row) {
                        let preview = '';
                        if (data) preview = data;
                        else if (row.admin_notes) preview = row.admin_notes;
                        else if (row.internal_notes) preview = row.internal_notes;

                        if (preview) {
                            return '<div class="notes-preview" title="' + preview + '">' + preview + '</div>';
                        }
                        return '<span class="text-muted">No notes</span>';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        let badges = '';
                        if (row.customer_notes) {
                            badges += '<span class="badge bg-primary note-badge me-1">Customer</span>';
                        }
                        if (row.admin_notes) {
                            badges += '<span class="badge bg-warning note-badge me-1">Admin</span>';
                        }
                        if (row.internal_notes) {
                            badges += '<span class="badge bg-info note-badge me-1">Internal</span>';
                        }
                        return badges || '<span class="text-muted">None</span>';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-primary view-notes" data-id="' + data + '" title="View/Edit Notes"><i class="bi bi-chat-left-text"></i></button>';
                        actions += '<button class="btn btn-outline-success add-quick-note" data-id="' + data + '" title="Add Quick Note"><i class="bi bi-plus-circle"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-info" title="View Order"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[2, 'desc']]
        });
    }

    // Initialize Customer Notes Table
    function initCustomerNotesTable() {
        customerNotesTable = $('#customerNotesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.has_customer_notes = true;
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
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                { data: 'status_badge', orderable: false },
                {
                    data: 'customer_notes',
                    render: function(data) {
                        return '<div class="notes-preview" title="' + data + '">' + data + '</div>';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-primary view-notes" data-id="' + data + '"><i class="bi bi-chat-left-text"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[2, 'desc']]
        });
    }

    // Initialize Admin Notes Table
    function initAdminNotesTable() {
        adminNotesTable = $('#adminNotesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.has_admin_notes = true;
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
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                { data: 'status_badge', orderable: false },
                {
                    data: 'admin_notes',
                    render: function(data) {
                        return '<div class="notes-preview" title="' + data + '">' + data + '</div>';
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-primary view-notes" data-id="' + data + '"><i class="bi bi-pencil"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[5, 'desc']]
        });
    }

    // Initialize Internal Notes Table
    function initInternalNotesTable() {
        internalNotesTable = $('#internalNotesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.has_internal_notes = true;
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
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                { data: 'status_badge', orderable: false },
                {
                    data: 'internal_notes',
                    render: function(data) {
                        return '<div class="notes-preview" title="' + data + '">' + data + '</div>';
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        let actions = '<div class="btn-group btn-group-sm">';
                        actions += '<button class="btn btn-outline-primary view-notes" data-id="' + data + '"><i class="bi bi-pencil"></i></button>';
                        actions += '<a href="/admin/orders/' + data + '" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[5, 'desc']]
        });
    }

    // Initialize all tables
    initAllNotesTable();

    // Initialize other tables when their tabs are shown
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');

        if (target === '#customer-notes' && !customerNotesTable) {
            initCustomerNotesTable();
        } else if (target === '#admin-notes' && !adminNotesTable) {
            initAdminNotesTable();
        } else if (target === '#internal-notes' && !internalNotesTable) {
            initInternalNotesTable();
        }
    });

    // Apply Filters
    $('#applyAllNotesFilters').on('click', function() {
        allNotesTable.ajax.reload();
    });

    // View/Edit Notes
    $(document).on('click', '.view-notes', function() {
        const orderId = $(this).data('id');

        // Show loading state
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching order data',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/admin/orders/' + orderId + '/data',  // Changed URL
            type: 'GET',
            success: function(response) {
                Swal.close(); // Close loading

                if (response.success) {
                    $('#modalOrderId').val(orderId);

                    // Order info
                    let orderInfo = '<div class="row">';
                    orderInfo += '<div class="col-md-6"><strong>Order:</strong> ' + response.order_number + '</div>';
                    orderInfo += '<div class="col-md-6"><strong>Customer:</strong> ' + response.customer_name + '</div>';
                    orderInfo += '<div class="col-md-6"><strong>Email:</strong> ' + response.customer_email + '</div>';
                    orderInfo += '<div class="col-md-6"><strong>Status:</strong> ' + response.status + '</div>';
                    orderInfo += '<div class="col-md-12 mt-2"><strong>Total:</strong> ' + response.total_amount + '</div>';
                    orderInfo += '</div>';
                    $('#orderInfoSection').html(orderInfo);

                    // Customer notes
                    if (response.customer_notes) {
                        $('#noCustomerNotes').hide();
                        $('#customerNotesText').html('<p class="mb-0">' + response.customer_notes + '</p>').show();
                    } else {
                        $('#noCustomerNotes').show();
                        $('#customerNotesText').hide();
                    }

                    // Admin notes
                    $('#adminNotesTextarea').val(response.admin_notes || '');

                    // Internal notes
                    $('#internalNotesTextarea').val(response.internal_notes || '');

                    $('#notesModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to load order data'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to load order data'
                });
            }
        });
    });

    // Save Notes
    $('#saveNotesBtn').on('click', function() {
        const orderId = $('#modalOrderId').val();
        const adminNotes = $('#adminNotesTextarea').val();
        const internalNotes = $('#internalNotesTextarea').val();

        $.ajax({
            url: '/admin/orders/' + orderId + '/update-notes',
            type: 'POST',
            data: {
                admin_notes: adminNotes,
                internal_notes: internalNotes,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#notesModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Notes Updated!',
                    text: 'Order notes have been saved successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Reload tables
                if (allNotesTable) allNotesTable.ajax.reload();
                if (adminNotesTable) adminNotesTable.ajax.reload();
                if (internalNotesTable) internalNotesTable.ajax.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to update notes'
                });
            }
        });
    });

    // Add Quick Note
    $(document).on('click', '.add-quick-note', function() {
        const orderId = $(this).data('id');
        $('#quickNoteOrderId').val(orderId);
        $('#quickNoteForm')[0].reset();
        $('#quickNoteModal').modal('show');
    });

    $('#saveQuickNoteBtn').on('click', function() {
        const orderId = $('#quickNoteOrderId').val();
        const noteType = $('#quickNoteType').val();
        const noteText = $('#quickNoteText').val();

        if (!noteType || !noteText) {
            Swal.fire('Required Fields', 'Please fill in all required fields', 'warning');
            return;
        }

        const field = noteType === 'admin' ? 'admin_notes' : 'internal_notes';

        $.ajax({
            url: '/admin/orders/' + orderId + '/update-notes',
            type: 'POST',
            data: {
                [field]: noteText,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#quickNoteModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Note Added!',
                    text: 'Quick note has been added successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Reload tables
                if (allNotesTable) allNotesTable.ajax.reload();
                if (noteType === 'admin' && adminNotesTable) adminNotesTable.ajax.reload();
                if (noteType === 'internal' && internalNotesTable) internalNotesTable.ajax.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to add note'
                });
            }
        });
    });
});
</script>
@endpush
