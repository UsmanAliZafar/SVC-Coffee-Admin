@extends('admin.layouts.app')

@section('title', 'Contact Us Management')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-card .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #5B914C;
    }
    .btn-filter {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-filter:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }
    .contact-stats {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-item .stat-value {
        font-size: 2rem;
        font-weight: bold;
    }
    .stat-item .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #5B914C;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    .contact-info {
        line-height: 1.6;
    }
    .contact-name {
        margin-bottom: 5px;
    }
    .contact-email, .contact-phone {
        font-size: 0.9em;
        margin-bottom: 3px;
    }
    .subject-message-container {
        max-width: 400px;
    }
    .message-subject {
        font-size: 0.95em;
    }
    .message-preview {
        font-size: 0.85em;
        line-height: 1.4;
    }
    .created-at-container {
        line-height: 1.5;
    }
    .time-info {
        font-size: 0.85em;
        line-height: 1.6;
    }
    .contact-link:hover {
        color: #5B914C !important;
        text-decoration: underline !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-envelope-fill"></i> Contact Us Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Contact Us</li>
                </ol>
            </nav>
        </div>
        {{-- <div>
            @if(auth('admin')->user()->hasPermission('contact_us.read'))
            <a href="{{ route('admin.contact-us.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
            @endif
        </div> --}}
    </div>

    <!-- Statistics Cards -->
    <div class="contact-stats">
        <div class="row">
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalContacts">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Contacts</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="unreadContacts">{{ $stats['unread'] }}</div>
                <div class="stat-label">Unread</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="pendingContacts">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="inProgressContacts">{{ $stats['in_progress'] }}</div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="resolvedContacts">{{ $stats['resolved'] }}</div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="col-md-1 stat-item">
                <div class="stat-value" id="todayContacts">{{ $stats['today'] }}</div>
                <div class="stat-label">Today</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control"
                       placeholder="Search by name, email, phone, subject...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select id="priorityFilter" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Read Status</label>
                <select id="readStatusFilter" class="form-select">
                    <option value="">All</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Assigned To</label>
                <select id="assignedToFilter" class="form-select">
                    <option value="">All</option>
                    <option value="unassigned">Unassigned</option>
                    @foreach($adminUsers as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" id="dateFrom" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" id="dateTo" class="form-control">
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Bulk Actions -->
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span><strong id="selectedCount">0</strong> contacts selected</span>
                    <div>
                        @if(auth('admin')->user()->hasPermission('contact_us.update'))
                        <button type="button" class="btn btn-sm btn-primary" id="bulkUpdateStatus">
                            <i class="bi bi-arrow-repeat"></i> Update Status
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="bulkAssign">
                            <i class="bi bi-person-plus"></i> Assign
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="bulkMarkAsRead">
                            <i class="bi bi-check-circle"></i> Mark as Read
                        </button>
                        @endif
                        @if(auth('admin')->user()->hasPermission('contact_us.delete'))
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDelete">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">
                            <i class="bi bi-x"></i> Deselect All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="contactsTable" class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            @if(auth('admin')->user()->hasPermission('contact_us.delete'))
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            @endif
                            <th>Contact Info</th>
                            <th>Subject & Message</th>
                            <th width="120">Status</th>
                            <th width="100">Priority</th>
                            <th width="150">Assigned To</th>
                            <th width="150">Created At</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Status Modal -->
<div class="modal fade" id="bulkUpdateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Bulk Update Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Status</label>
                    <select class="form-select" id="bulkStatusSelect" required>
                        @foreach($statusList as $status)
                            <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <span id="bulkStatusCount">0</span> contact(s) will be updated
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="executeBulkStatus">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Bulk Assign
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Assign to Admin</label>
                    <select class="form-select" id="bulkAssignSelect" required>
                        @foreach($adminUsers as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <span id="bulkAssignCount">0</span> contact(s) will be assigned
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="executeBulkAssign">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Update Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="contactId">
                <div class="mb-3">
                    <label class="form-label">Select Status</label>
                    <select class="form-select" id="statusSelect" required>
                        @foreach($statusList as $status)
                            <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Notes (Optional)</label>
                    <textarea class="form-control" id="adminNotes" rows="3"
                              placeholder="Add internal notes about this status change..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveStatus">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Contact Modal -->
<div class="modal fade" id="assignContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Assign Contact
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assignContactId">
                <div class="mb-3">
                    <label class="form-label">Contact: <strong id="assignContactName"></strong></label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign to Admin</label>
                    <select class="form-select" id="assignAdminSelect" required>
                        @foreach($adminUsers as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAssignment">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Reply Modal -->
<div class="modal fade" id="quickReplyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-reply-fill"></i> Quick Reply
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="replyContactId">
                <div class="mb-3">
                    <label class="form-label">To: <strong id="replyEmail"></strong></label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" id="replySubject" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Your Reply</label>
                    <textarea class="form-control" id="replyMessage" rows="6"
                              placeholder="Type your reply here..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="sendReply">
                    <i class="bi bi-send"></i> Send Reply
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedContacts = [];

    // Initialize DataTable
    const table = $('#contactsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.contact-us.get-data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.priority = $('#priorityFilter').val();
                d.read_status = $('#readStatusFilter').val();
                d.assigned_to = $('#assignedToFilter').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d.search = $('#searchFilter').val();
            }
        },
        columns: [
            @if(auth('admin')->user()->hasPermission('contact_us.delete'))
            { data: 'checkbox', orderable: false, searchable: false },
            @endif
            { data: 'contact_info', orderable: false },
            { data: 'subject_message', orderable: false },
            { data: 'status_badge', orderable: false },
            { data: 'priority_badge', orderable: false },
            { data: 'assigned_to_badge', orderable: false },
            { data: 'created_at_formatted' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: `
                <div class="datatable-loading-container">
                    <div class="bars-loader">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="datatable-loading-text">Loading Contacts...</div>
                </div>
            `
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Filter change events
    $('#statusFilter, #priorityFilter, #readStatusFilter, #assignedToFilter, #dateFrom, #dateTo').on('change', function() {
        table.draw();
    });

    // Search with delay
    let searchDelay;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#searchFilter').val('');
        $('#statusFilter').val('');
        $('#priorityFilter').val('');
        $('#readStatusFilter').val('');
        $('#assignedToFilter').val('');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.draw();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.contact-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    $(document).on('change', '.contact-checkbox', function() {
        updateBulkActions();
        const totalCheckboxes = $('.contact-checkbox').length;
        const checkedCheckboxes = $('.contact-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        selectedContacts = [];
        $('.contact-checkbox:checked').each(function() {
            selectedContacts.push($(this).val());
        });

        $('#selectedCount').text(selectedContacts.length);
        $('#bulkStatusCount').text(selectedContacts.length);
        $('#bulkAssignCount').text(selectedContacts.length);

        if (selectedContacts.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Deselect all
    $('#deselectAll').on('click', function() {
        $('.contact-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    });

    // Bulk Update Status
    $('#bulkUpdateStatus').on('click', function() {
        if (selectedContacts.length === 0) {
            showNotification('Please select at least one contact', 'warning');
            return;
        }
        $('#bulkUpdateStatusModal').modal('show');
    });

    $('#executeBulkStatus').on('click', function() {
        const status = $('#bulkStatusSelect').val();

        if (!status) {
            showNotification('Please select a status', 'warning');
            return;
        }

        Swal.fire({
            title: `Update ${selectedContacts.length} contacts?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, update them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.contact-us.bulk-status") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        contact_ids: selectedContacts,
                        status_key_code: status
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        $('#bulkUpdateStatusModal').modal('hide');
                        table.draw();
                        $('#deselectAll').click();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Bulk Assign
    $('#bulkAssign').on('click', function() {
        if (selectedContacts.length === 0) {
            showNotification('Please select at least one contact', 'warning');
            return;
        }
        $('#bulkAssignModal').modal('show');
    });

    $('#executeBulkAssign').on('click', function() {
        const adminId = $('#bulkAssignSelect').val();

        if (!adminId) {
            showNotification('Please select an admin', 'warning');
            return;
        }

        Swal.fire({
            title: `Assign ${selectedContacts.length} contacts?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, assign them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.contact-us.bulk-assign") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        contact_ids: selectedContacts,
                        assigned_to: adminId
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        $('#bulkAssignModal').modal('hide');
                        table.draw();
                        $('#deselectAll').click();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Bulk Mark as Read
    $('#bulkMarkAsRead').on('click', function() {
        if (selectedContacts.length === 0) {
            showNotification('Please select at least one contact', 'warning');
            return;
        }

        Swal.fire({
            title: `Mark ${selectedContacts.length} contacts as read?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark as read!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.contact-us.bulk-mark-as-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        contact_ids: selectedContacts
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Bulk Delete
    $('#bulkDelete').on('click', function() {
        if (selectedContacts.length === 0) {
            showNotification('Please select at least one contact', 'warning');
            return;
        }

        Swal.fire({
            title: `Delete ${selectedContacts.length} contacts?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.contact-us.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        contact_ids: selectedContacts
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Update Status Button
    $(document).on('click', '.update-status-btn', function() {
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');

        $('#contactId').val(id);
        $('#statusSelect').val(currentStatus);
        $('#updateStatusModal').modal('show');
    });

    // Save Status
    $('#saveStatus').on('click', function() {
        const id = $('#contactId').val();
        const status = $('#statusSelect').val();
        const notes = $('#adminNotes').val();

        $.ajax({
            url: `/admin/contact-us/${id}/status`,
            method: 'PATCH',
            data: {
                status_key_code: status,
                admin_notes: notes,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification(response.message, 'success');
                $('#updateStatusModal').modal('hide');
                table.draw();
            },
            error: function(xhr) {
                showNotification('An error occurred', 'error');
            }
        });
    });

    // Assign Contact Button
    $(document).on('click', '.assign-contact-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        $('#assignContactId').val(id);
        $('#assignContactName').text(name);
        $('#assignContactModal').modal('show');
    });

    // Save Assignment
    $('#saveAssignment').on('click', function() {
        const id = $('#assignContactId').val();
        const adminId = $('#assignAdminSelect').val();

        $.ajax({
            url: `/admin/contact-us/${id}/assign`,
            method: 'PATCH',
            data: {
                assigned_to: adminId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification(response.message, 'success');
                $('#assignContactModal').modal('hide');
                table.draw();
            },
            error: function(xhr) {
                showNotification('An error occurred', 'error');
            }
        });
    });

    // Quick Reply Button
    $(document).on('click', '.quick-reply-btn', function() {
        const id = $(this).data('id');
        const email = $(this).data('email');
        const name = $(this).data('name');
        const subject = $(this).data('subject');

        $('#replyContactId').val(id);
        $('#replyEmail').text(`${name} <${email}>`);
        $('#replySubject').val(`Re: ${subject}`);
        $('#replyMessage').val('');
        $('#quickReplyModal').modal('show');
    });

    // Send Reply
    $('#sendReply').on('click', function() {
        const message = $('#replyMessage').val();

        if (!message.trim()) {
            showNotification('Please enter a reply message', 'warning');
            return;
        }

        // You can implement your email sending logic here
        showNotification('Reply functionality can be implemented based on your email system', 'info');
        $('#quickReplyModal').modal('hide');
    });

    // Delete Contact
    $(document).on('click', '.delete-contact', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/contact-us/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Notification Helper
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3"
                 role="alert" style="z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        $('body').append(notification);

        setTimeout(function() {
            notification.alert('close');
        }, 5000);
    }
});
</script>
@endpush
