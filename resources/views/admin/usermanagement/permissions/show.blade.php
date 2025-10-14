@extends('admin.layouts.app')

@section('title', 'View Permission')

@section('page_title', 'Permission Details')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
            <li class="breadcrumb-item active">{{ $permission->display_name }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Permission Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-key me-2"></i>Permission Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-4">
                                <div class="permission-icon me-4">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center"
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        <i class="bi bi-key"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ $permission->display_name }}</h4>
                                    <p class="text-muted mb-0">
                                        <code class="text-primary">{{ $permission->name }}</code>
                                    </p>
                                    @if($permission->description)
                                    <p class="text-muted mb-0 mt-1">{{ $permission->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label text-muted">Module</label>
                                    <p class="mb-0">
                                        <span class="badge bg-primary fs-6">
                                            {{ ucwords(str_replace('_', ' ', $permission->module)) }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-6">
                                    <label class="form-label text-muted">Action</label>
                                    <p class="mb-0">
                                        <span class="badge bg-secondary fs-6">
                                            {{ ucwords(str_replace('_', ' ', $permission->action)) }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted">Full Permission Name</label>
                                    <p class="mb-0">
                                        <code class="bg-light text-dark p-2 d-inline-block rounded">{{ $permission->name }}</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Created Date</label>
                                    <p class="mb-0">{{ $permission->created_at->format('F d, Y \a\t H:i A') }}</p>
                                    <small class="text-muted">{{ $permission->created_at->diffForHumans() }}</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted">Last Updated</label>
                                    <p class="mb-0">{{ $permission->updated_at->format('F d, Y \a\t H:i A') }}</p>
                                    <small class="text-muted">{{ $permission->updated_at->diffForHumans() }}</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted">Usage Status</label>
                                    <p class="mb-0">
                                        @if($permission->roles->count() > 0)
                                            <span class="badge bg-success fs-6">Active</span>
                                            <small class="text-muted d-block">Used by {{ $permission->roles->count() }} role(s)</small>
                                        @else
                                            <span class="badge bg-warning text-dark fs-6">Unused</span>
                                            <small class="text-muted d-block">Not assigned to any roles</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Roles Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check me-2"></i>Assigned to Roles ({{ $permission->roles->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($permission->roles as $role)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="role-icon me-3">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        {{ $role->display_name }}
                                        @if($role->name === 'super_admin')
                                            <span class="badge bg-danger ms-2">System</span>
                                        @elseif(in_array($role->name, ['manager', 'staff']))
                                            <span class="badge bg-warning text-dark ms-2">Core</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $role->name }}</small>
                                    @if($role->description)
                                        <br><small class="text-muted">{{ $role->description }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $role->is_active ? 'success' : 'secondary' }}">
                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <br><small class="text-muted">{{ $role->adminUsers()->count() }} users</small>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-shield-x text-muted" style="font-size: 3rem;"></i>
                            <h6 class="text-muted mt-2">No roles assigned</h6>
                            <p class="text-muted">This permission is not currently assigned to any roles.</p>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-shield-plus me-1"></i>Assign to Role
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Related Permissions Card -->
            @php
                $relatedPermissions = App\Models\Permission::where('module', $permission->module)
                                                          ->where('id', '!=', $permission->id)
                                                          ->limit(10)
                                                          ->get();
            @endphp

            @if($relatedPermissions->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-collection me-2"></i>Related Permissions in {{ ucwords(str_replace('_', ' ', $permission->module)) }} Module
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($relatedPermissions as $related)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-key-fill text-info me-2"></i>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('admin.permissions.show', $related) }}"
                                           class="text-decoration-none">
                                            <strong>{{ $related->display_name }}</strong>
                                        </a>
                                        <br><small class="text-muted">{{ $related->name }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary">{{ $related->roles()->count() }} roles</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(App\Models\Permission::where('module', $permission->module)->where('id', '!=', $permission->id)->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.permissions.index', ['module' => $permission->module]) }}"
                               class="btn btn-outline-primary btn-sm">
                                View All {{ ucwords(str_replace('_', ' ', $permission->module)) }} Permissions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif
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
                        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Permission
                        </a>

                        @if($permission->roles()->count() === 0)
                        <button type="button" class="btn btn-outline-danger delete-permission"
                                data-permission-id="{{ $permission->id }}"
                                data-permission-name="{{ $permission->display_name }}">
                            <i class="bi bi-trash me-2"></i>Delete Permission
                        </button>
                        @else
                        <button type="button" class="btn btn-outline-secondary" disabled
                                data-bs-toggle="tooltip" title="Cannot delete: Permission is in use">
                            <i class="bi bi-lock me-2"></i>Cannot Delete
                        </button>
                        @endif

                        <a href="{{ route('admin.permissions.create') }}" class="btn btn-outline-success">
                            <i class="bi bi-copy me-2"></i>Create Similar
                        </a>

                        <hr>

                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Permissions
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
                                <h4 class="text-info mb-1">{{ $permission->roles->count() }}</h4>
                                <small class="text-muted">Assigned Roles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $permission->roles->sum(function($role) { return $role->adminUsers()->count(); }) }}</h4>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Module</small>
                            <p class="mb-0 fw-bold">{{ ucwords(str_replace('_', ' ', $permission->module)) }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Action</small>
                            <p class="mb-0 fw-bold">{{ ucwords(str_replace('_', ' ', $permission->action)) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Timeline Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Usage Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Permission Created</h6>
                                <small class="text-muted">{{ $permission->created_at->format('M d, Y H:i A') }}</small>
                            </div>
                        </div>

                        @if($permission->updated_at != $permission->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Modified</h6>
                                <small class="text-muted">{{ $permission->updated_at->format('M d, Y H:i A') }}</small>
                            </div>
                        </div>
                        @endif

                        @if($permission->roles->count() > 0)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Currently Active</h6>
                                <small class="text-muted">Used by {{ $permission->roles->count() }} role(s)</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
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
                <p>Are you sure you want to delete the permission <strong>{{ $permission->display_name }}</strong>?</p>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone and may affect system functionality.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Permission</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .permission-icon, .role-icon {
        flex-shrink: 0;
    }

    .badge.fs-6 {
        font-size: 0.875rem !important;
    }

    .border-end {
        border-right: 1px solid #dee2e6 !important;
    }

    code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0.5rem;
        bottom: -1rem;
        width: 2px;
        background-color: #dee2e6;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
    }

    .timeline-content {
        background-color: #f8f9fa;
        padding: 0.75rem;
        border-radius: 0.25rem;
        border-left: 3px solid #5B914C;
    }

    .bg-success {
        background-color: #5B914C !important;
    }

    .text-success {
        color: #5B914C !important;
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
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Delete permission functionality
    $('.delete-permission').click(function() {
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
