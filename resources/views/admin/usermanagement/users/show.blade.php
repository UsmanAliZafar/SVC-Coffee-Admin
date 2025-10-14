@extends('admin.layouts.app')

@section('title', 'View Admin User')

@section('page_title', 'Admin User Details')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin Users</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- User Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-4">
                                <div class="avatar-lg me-4">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ $user->name }}</h4>
                                    <p class="text-muted mb-0">{{ $user->email }}</p>
                                    <p class="text-muted mb-0">@{{ $user->username }}</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Phone Number</label>
                                    <p class="mb-0">{{ $user->phone ?: 'Not provided' }}</p>
                                </div>

                                <div class="col-6">
                                    <label class="form-label text-muted">Account Status</label>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} fs-6">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-6">
                                    <label class="form-label text-muted">Two-Factor Auth</label>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $user->two_factor_enabled ? 'success' : 'warning' }} fs-6">
                                            {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Created Date</label>
                                    <p class="mb-0">{{ $user->created_at->format('F d, Y \a\t H:i A') }}</p>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted">Last Updated</label>
                                    <p class="mb-0">{{ $user->updated_at->format('F d, Y \a\t H:i A') }}</p>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted">Last Login</label>
                                    <p class="mb-0">
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->format('F d, Y \a\t H:i A') }}
                                            <br><small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                                        @else
                                            <span class="text-muted">Never logged in</span>
                                        @endif
                                    </p>
                                </div>

                                @if($user->createdBy)
                                <div class="col-12">
                                    <label class="form-label text-muted">Created By</label>
                                    <p class="mb-0">{{ $user->createdBy->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions Card -->
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check me-2"></i>Roles & Permissions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3">Assigned Roles</h6>
                            @forelse($user->roles as $role)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="badge bg-info text-dark me-2 p-2">
                                        <i class="bi bi-person-badge me-1"></i>{{ $role->display_name }}
                                    </div>
                                    @if($role->description)
                                        <small class="text-muted">{{ $role->description }}</small>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted">No roles assigned</p>
                            @endforelse
                        </div>

                        <div class="col-md-8">
                            <h6 class="text-muted mb-3">All Permissions</h6>
                            @php
                                $allPermissions = $user->roles->flatMap->permissions->unique('id');
                                $groupedPermissions = $allPermissions->groupBy('module');
                            @endphp

                            @forelse($groupedPermissions as $module => $permissions)
                                <div class="mb-3">
                                    <h6 class="text-success mb-2">
                                        <i class="bi bi-folder me-1"></i>{{ ucwords(str_replace('_', ' ', $module)) }}
                                    </h6>
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-6 mb-1">
                                                <small class="badge bg-light text-dark">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                    {{ $permission->display_name }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">No permissions found</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tools me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(auth('admin')->user()->hasPermission('admin_users.update'))
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>Edit User
                        </a>
                        @endif

                        @if(auth('admin')->user()->hasPermission('admin_users.update'))
                        <button type="button" class="btn btn-outline-{{ $user->is_active ? 'secondary' : 'success' }} status-toggle"
                                data-user-id="{{ $user->id }}">
                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}-circle me-2"></i>
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }} Account
                        </button>
                        @endif

                        @if(auth('admin')->user()->hasPermission('admin_users.delete') && $user->id !== auth('admin')->id())
                        <button type="button" class="btn btn-outline-danger delete-user"
                                data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}">
                            <i class="bi bi-trash me-2"></i>Delete User
                        </button>
                        @endif

                        <hr>

                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ $user->roles->count() }}</h4>
                                <small class="text-muted">Assigned Roles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-1">{{ $allPermissions->count() ?? 0 }}</h4>
                            <small class="text-muted">Total Permissions</small>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-12">
                            <small class="text-muted">Account Age</small>
                            <p class="mb-0">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Log Card (Future Enhancement) -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">Activity tracking coming soon</p>
                        <small class="text-muted">Login history, actions performed, etc.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Toggle Confirmation Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="statusAction"></span> the account for <strong>{{ $user->name }}</strong>?</p>
                <p class="text-warning"><small id="statusWarning"></small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusChange">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the admin user <strong>{{ $user->name }}</strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-lg {
        flex-shrink: 0;
    }

    .badge {
        font-size: 0.75rem;
    }

    .badge.fs-6 {
        font-size: 0.875rem !important;
    }

    .card-header {
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .border-end {
        border-right: 1px solid #dee2e6 !important;
    }

    .text-success {
        color: #5B914C !important;
    }

    .bg-success {
        background-color: #5B914C !important;
    }

    .btn-outline-success:hover {
        background-color: #5B914C;
        border-color: #5B914C;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentUserId = {{ $user->id }};
    let currentUserStatus = {{ $user->is_active ? 'true' : 'false' }};

    // Status toggle functionality
    $('.status-toggle').click(function() {
        const userId = $(this).data('user-id');
        const newStatus = !currentUserStatus;
        const action = newStatus ? 'activate' : 'deactivate';
        const warning = newStatus ?
            'This user will be able to login and access the admin panel.' :
            'This user will be logged out and unable to access the admin panel.';

        $('#statusAction').text(action);
        $('#statusWarning').text(warning);
        $('#statusModal').modal('show');
    });

    // Confirm status change
    $('#confirmStatusChange').click(function() {
        const button = $('.status-toggle');

        $.ajax({
            url: `/admin/users/${currentUserId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                button.prop('disabled', true);
                $('#statusModal').modal('hide');
            },
            success: function(response) {
                if (response.success) {
                    // Update button text and style
                    currentUserStatus = response.status;

                    const newButtonClass = response.status ? 'btn-outline-secondary' : 'btn-outline-success';
                    const oldButtonClass = response.status ? 'btn-outline-success' : 'btn-outline-secondary';
                    const newIcon = response.status ? 'pause' : 'play';
                    const newText = response.status ? 'Deactivate' : 'Activate';

                    button.removeClass(oldButtonClass)
                          .addClass(newButtonClass)
                          .html(`<i class="bi bi-${newIcon}-circle me-2"></i>${newText} Account`);

                    // Update status badge
                    const statusBadge = $('.badge:contains("Active"), .badge:contains("Inactive")').first();
                    statusBadge.removeClass('bg-success bg-secondary')
                              .addClass(response.status ? 'bg-success' : 'bg-secondary')
                              .text(response.status ? 'Active' : 'Inactive');

                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message || 'Failed to update status');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to update status';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });

    // Delete user functionality
    $('.delete-user').click(function() {
        $('#deleteModal').modal('show');
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertIcon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi bi-${alertIcon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert alert at the top of the main content
        $('.container-fluid .row').first().before(alertHtml);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);

        // Scroll to top to show the alert
        $('html, body').animate({ scrollTop: 0 }, 500);
    }
});
</script>
@endpush
