@extends('admin.layouts.app')

@section('title', 'View Contact Message')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-envelope-open me-2"></i>
                        Contact Message Details
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.contact-us.index') }}">Contact Us</a></li>
                            <li class="breadcrumb-item active">View Message</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.contact-us.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Contact Details -->
        <div class="col-lg-8 mb-4">
            <!-- Contact Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background-color: #5B914C; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-person-circle me-2"></i>Contact Information
                    </h6>
                    <div>
                        {!! $contact->getStatusBadge() !!}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-person-fill"></i> Name
                            </h6>
                            <p class="mb-0"><strong>{{ $contact->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-envelope-fill"></i> Email
                            </h6>
                            <p class="mb-0">
                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-telephone-fill"></i> Phone
                            </h6>
                            <p class="mb-0">
                                @if($contact->phone)
                                    <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-flag-fill"></i> Priority
                            </h6>
                            <p class="mb-0">
                                <span class="badge bg-{{ $contact->getPriorityColor() }}">
                                    {{ $contact->getPriorityLabel() }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-geo-alt-fill"></i> IP Address
                            </h6>
                            <p class="mb-0">
                                <code>{{ $contact->ip_address ?? 'N/A' }}</code>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-person-badge"></i> Assigned To
                            </h6>
                            <p class="mb-0">
                                @if($contact->assignedAdmin)
                                    <span class="badge bg-primary">{{ $contact->assignedAdmin->name }}</span>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($contact->user_agent)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-browser-chrome"></i> User Agent
                            </h6>
                            <p class="mb-0">
                                <small class="text-muted">{{ $contact->user_agent }}</small>
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Message Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background-color: #5B914C; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-chat-text me-2"></i>Message
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-bookmark-fill text-primary"></i> {{ $contact->subject }}
                    </h5>
                    <div class="message-content p-3 bg-light rounded">
                        <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">{{ $contact->message }}</p>
                    </div>
                </div>
            </div>

            <!-- Admin Notes Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background-color: #5B914C; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-sticky me-2"></i>Admin Notes
                    </h6>
                    @if(auth('admin')->user()->hasPermission('contact_us.update'))
                        <button type="button" class="btn btn-sm btn-light" id="edit-notes-btn">
                            <i class="bi bi-pencil"></i> Edit Notes
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div id="notes-display">
                        @if($contact->admin_notes)
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $contact->admin_notes }}</p>
                        @else
                            <p class="text-muted mb-0">No admin notes yet.</p>
                        @endif
                    </div>
                    <div id="notes-edit" style="display: none;">
                        <textarea class="form-control mb-3" id="admin-notes-textarea" rows="4">{{ $contact->admin_notes }}</textarea>
                        <button type="button" class="btn btn-primary btn-sm" id="save-notes-btn">
                            <i class="bi bi-check"></i> Save Notes
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" id="cancel-notes-btn">
                            <i class="bi bi-x"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            @if(auth('admin')->user()->hasPermission('contact_us.update'))
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background-color: #5B914C; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-lightning-fill me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                            <i class="bi bi-arrow-repeat"></i> Update Status
                        </button>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#assignModal">
                            <i class="bi bi-person-plus"></i> Assign to Admin
                        </button>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#priorityModal">
                            <i class="bi bi-flag"></i> Update Priority
                        </button>
                        {{-- <a href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject }}" class="btn btn-success">
                            <i class="bi bi-reply-fill"></i> Reply via Email
                        </a> --}}
                        @if(auth('admin')->user()->hasPermission('contact_us.delete'))
                            <button type="button" class="btn btn-danger" id="delete-contact-btn">
                                <i class="bi bi-trash"></i> Delete Message
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background-color: #5B914C; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-clock-history me-2"></i>Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Created -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-plus-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Message Received</h6>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="bi bi-calendar3"></i>
                                            {{ $contact->created_at->format('M d, Y') }}
                                        </small>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="bi bi-clock"></i>
                                            {{ $contact->created_at->format('h:i A') }}
                                        </small>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <small>{{ $contact->created_at->diffForHumans() }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Read -->
                        @if($contact->read_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-eye-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Message Read</h6>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="bi bi-calendar3"></i>
                                            {{ $contact->read_at->format('M d, Y') }}
                                        </small>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="bi bi-clock"></i>
                                            {{ $contact->read_at->format('h:i A') }}
                                        </small>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <small>{{ $contact->read_at->diffForHumans() }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Resolved -->
                        @if($contact->resolved_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Message Resolved</h6>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="bi bi-calendar3"></i>
                                            {{ $contact->resolved_at->format('M d, Y') }}
                                        </small>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="bi bi-clock"></i>
                                            {{ $contact->resolved_at->format('h:i A') }}
                                        </small>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <small>{{ $contact->resolved_at->diffForHumans() }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Current Status -->
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="timeline-icon text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 40px; height: 40px; background-color: {{ $contact->status->bg_color ?? '#6c757d' }};">
                                    <i class="{{ $contact->status->icon ?? 'bi-circle' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Current Status</h6>
                                    <p class="mb-0">
                                        {!! $contact->getStatusBadge() !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background-color: #5B914C; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-info-circle me-2"></i>Additional Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Message ID</small>
                        <p class="mb-0"><strong>#{{ $contact->id }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Created</small>
                        <p class="mb-0">{{ $contact->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Last Updated</small>
                        <p class="mb-0">{{ $contact->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @if($contact->read_at)
                    <div class="mb-3">
                        <small class="text-muted">Read Status</small>
                        <p class="mb-0">
                            <span class="badge bg-success">Read</span>
                        </p>
                    </div>
                    @else
                    <div class="mb-3">
                        <small class="text-muted">Read Status</small>
                        <p class="mb-0">
                            <span class="badge bg-primary">Unread</span>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="bi bi-arrow-repeat"></i> Update Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="update-status-form">
                    <div class="mb-3">
                        <label class="form-label">Select Status</label>
                        <select class="form-select" id="status-select" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}"
                                    {{ $contact->status_key_code == $status->key_code ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="status-admin-notes" rows="3"
                                  placeholder="Add notes about this status change..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-status-btn">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">
                    <i class="bi bi-person-plus"></i> Assign to Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assign-form">
                    <div class="mb-3">
                        <label class="form-label">Select Admin</label>
                        <select class="form-select" id="assign-select" required>
                            @foreach($adminUsers as $admin)
                                <option value="{{ $admin->id }}"
                                    {{ $contact->assigned_to == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-assign-btn">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- Priority Modal -->
<div class="modal fade" id="priorityModal" tabindex="-1" aria-labelledby="priorityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="priorityModalLabel">
                    <i class="bi bi-flag"></i> Update Priority
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="priority-form">
                    <div class="mb-3">
                        <label class="form-label">Select Priority</label>
                        <select class="form-select" id="priority-select" required>
                            <option value="low" {{ $contact->priority == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="normal" {{ $contact->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ $contact->priority == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $contact->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-priority-btn">Update Priority</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .message-content {
        border-left: 4px solid #5B914C;
    }

    .timeline {
        position: relative;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-icon {
        position: relative;
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const contactId = {{ $contact->id }};

    // Edit Notes
    $('#edit-notes-btn').on('click', function() {
        $('#notes-display').hide();
        $('#notes-edit').show();
    });

    $('#cancel-notes-btn').on('click', function() {
        $('#notes-edit').hide();
        $('#notes-display').show();
    });

    // Save Notes
    $('#save-notes-btn').on('click', function() {
        const notes = $('#admin-notes-textarea').val();

        $.ajax({
            url: `/admin/contact-us/${contactId}/status`,
            method: 'PATCH',
            data: {
                status_key_code: '{{ $contact->status_key_code }}',
                admin_notes: notes,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification('Notes updated successfully', 'success');
                location.reload();
            },
            error: function(xhr) {
                showNotification('An error occurred', 'error');
            }
        });
    });

    // Update Status
    $('#save-status-btn').on('click', function() {
        const status = $('#status-select').val();
        const notes = $('#status-admin-notes').val();

        $.ajax({
            url: `/admin/contact-us/${contactId}/status`,
            method: 'PATCH',
            data: {
                status_key_code: status,
                admin_notes: notes,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification(response.message, 'success');
                location.reload();
            },
            error: function(xhr) {
                showNotification('An error occurred', 'error');
            }
        });
    });

    // Assign to Admin
    $('#save-assign-btn').on('click', function() {
        const adminId = $('#assign-select').val();

        $.ajax({
            url: `/admin/contact-us/${contactId}/assign`,
            method: 'PATCH',
            data: {
                assigned_to: adminId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification(response.message, 'success');
                location.reload();
            },
            error: function(xhr) {
                showNotification('An error occurred', 'error');
            }
        });
    });

    // Update Priority
    $('#save-priority-btn').on('click', function() {
        const priority = $('#priority-select').val();

        $.ajax({
            url: `/admin/contact-us/${contactId}/priority`,
            method: 'PATCH',
            data: {
                priority: priority,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification(response.message, 'success');
                location.reload();
            },
            error: function(xhr) {
                showNotification('An error occurred', 'error');
            }
        });
    });

    // Delete Contact
    $('#delete-contact-btn').on('click', function() {
        if (confirm('Are you sure you want to delete this contact message?')) {
            $.ajax({
                url: `/admin/contact-us/${contactId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showNotification(response.message, 'success');
                    setTimeout(function() {
                        window.location.href = '{{ route("admin.contact-us.index") }}';
                    }, 1500);
                },
                error: function(xhr) {
                    showNotification('An error occurred', 'error');
                }
            });
        }
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
