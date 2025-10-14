@extends('admin.layouts.app')

@section('title', 'View Role')

@section('page_title', 'Role Details')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="">Roles</a></li>
            <li class="breadcrumb-item active">{{ $role->display_name }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Role Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check me-2"></i>Role Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-4">
                                <div class="role-icon me-4">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1">
                                        {{ $role->display_name }}
                                        @if($role->name === 'super_admin')
                                            <span class="badge bg-danger ms-2">System</span>
                                        @elseif(in_array($role->name, ['manager', 'staff']))
                                            <span class="badge bg-warning text-dark ms-2">Core</span>
                                        @endif
                                    </h4>
                                    <p class="text-muted mb-0">{{ $role->name }}</p>
                                    @if($role->description)
                                    <p class="text-muted mb-0">{{ $role->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label text-muted">Status</label>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $role->is_active ? 'success' : 'secondary' }} fs-6">
                                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-6">
                                    <label class="form-label text-muted">Type</label>
                                    <p class="mb-0">
                                        @if($role->name === 'super_admin')
                                            <span class="badge bg-danger fs-6">System Role</span>
                                        @elseif(in_array($role->name, ['manager', 'staff']))
                                            <span class="badge bg-warning text-dark fs-6">Core Role</span>
                                        @else
                                            <span class="badge bg-primary fs-6">Custom Role</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Created Date</label>
                                    <p class="mb-0">{{ $role->created_at->format('F d, Y \a\t H:i A') }}</p>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted">Last Updated</label>
                                    <p class="mb-0">{{ $role->updated_at->format('F d, Y \a\t H:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Users Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people me-2"></i>Assigned Users ({{ $role->adminUsers->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($role->adminUsers->take(10) as $user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $user->name }}</h6>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <div>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-person-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No users assigned to this role</p>
                        </div>
                    @endforelse

                    @if($role->adminUsers->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="btn btn-outline-primary btn-sm">
                                View All {{ $role->adminUsers->count() }} Users
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Permissions Card -->
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-key me-2"></i>Role Permissions ({{ $role->permissions->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($groupedPermissions as $module => $permissions)
                        <div class="mb-4">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-folder me-1"></i>{{ ucwords(str_replace('_', ' ', $module)) }}
                                <span class="badge bg-light text-dark ms-2">{{ $permissions->count() }} permissions</span>
                            </h6>
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-6 col-lg-4 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <div>
                                                <small class="fw-bold">{{ $permission->display_name }}</small>
                                                @if($permission->description)
                                                    <br><small class="text-muted">{{ $permission->description }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-key text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No permissions assigned to this role</p>
                        </div>
                    @endforelse
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
                        @if(auth('admin')->user()->hasPermission('roles.update'))
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Role
                        </a>
                        @endif

                        @if(auth('admin')->user()->hasPermission('roles.update') && !in_array($role->name, ['super_admin']))
                        <button type="button" class="btn btn-outline-{{ $role->is_active ? 'secondary' : 'success' }} status-toggle"
                                data-role-id="{{ $role->id }}">
                            <i class="bi bi-{{ $role->is_active ? 'pause' : 'play' }}-circle me-2"></i>
                            {{ $role->is_active ? 'Deactivate' : 'Activate' }} Role
                        </button>
                        @endif

                        @if(auth('admin')->user()->hasPermission('roles.create'))
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-outline-success">
                            <i class="bi bi-copy me-2"></i>Duplicate Role
                        </a>
                        @endif

                        @if(auth('admin')->user()->hasPermission('roles.delete') && !in_array($role->name, ['super_admin', 'manager', 'staff']) && $role->adminUsers->count() === 0)
                        <button type="button" class="btn btn-outline-danger delete-role"
                                data-role-id="{{ $role->id }}"
                                data-role-name="{{ $role->display_name }}">
                            <i class="bi bi-trash me-2"></i>Delete Role
                        </button>
                        @endif

                        <hr>

                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Roles
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
                                <h4 class="text-success mb-1">{{ $role->adminUsers->count() }}</h4>
                                <small class="text-muted">Assigned Users</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info mb-1">{{ $role->permissions->count() }}</h4>
                            <small class="text-muted">Permissions</small>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Active Users</small>
                            <p class="mb-0 fw-bold text-success">{{ $role->adminUsers->where('is_active', true)->count() }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Created</small>
                            <p class="mb-0">{{ $role->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Breakdown Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>Permission Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($groupedPermissions as $module => $permissions)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">{{ ucwords(str_replace('_', ' ', $module)) }}</span>
                            <span class="badge bg-info">{{ $permissions->count() }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center">No permissions assigned</p>
                    @endforelse
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
                <p>Are you sure you want to <span id="statusAction"></span> the role <strong>{{ $role->display_name }}</strong>?</p>
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
                <p>Are you sure you want to delete the role <strong>{{ $role->display_name }}</strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('admin.roles.destroy', $role) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .role-icon {
        flex-shrink: 0;
    }

    .avatar-sm {
        flex-shrink: 0;
    }

    .badge.fs-6 {
        font-size: 0.875rem !important;
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
    let currentRoleId = {{ $role->id }};
    let currentRoleStatus = {{ $role->is_active ? 'true' : 'false' }};

    // Status toggle functionality
    $('.status-toggle').click(function() {
        const roleId = $(this).data('role-id');
        const newStatus = !currentRoleStatus;
        const action = newStatus ? 'activate' : 'deactivate';
        const warning = newStatus ?
            'This role will be available for assignment to users.' :
            'This role will be unavailable for new assignments, but existing users will keep their permissions.';

        $('#statusAction').text(action);
        $('#statusWarning').text(warning);
        $('#statusModal').modal('show');
    });

    // Confirm status change
    $('#confirmStatusChange').click(function() {
        const button = $('.status-toggle');

        $.ajax({
            url: `/admin/user-management/roles/${currentRoleId}/toggle-status`,
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
                    currentRoleStatus = response.status;

                    const newButtonClass = response.status ? 'btn-outline-secondary' : 'btn-outline-success';
                    const oldButtonClass = response.status ? 'btn-outline-success' : 'btn-outline-secondary';
                    const newIcon = response.status ? 'pause' : 'play';
                    const newText = response.status ? 'Deactivate' : 'Activate';

                    button.removeClass(oldButtonClass)
                          .addClass(newButtonClass)
                          .html(`<i class="bi bi-${newIcon}-circle me-2"></i>${newText} Role`);

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

    // Delete role functionality
    $('.delete-role').click(function() {
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
