@extends('admin.layouts.app')

@section('title', 'Roles Management')

@section('page_title', 'Roles & Permissions')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Roles</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check me-2"></i>Roles Management
                    </h5>
                    <div>
                        @if(auth('admin')->user()->hasPermission('roles.create'))
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-light btn-sm me-2">
                            <i class="bi bi-plus-circle me-1"></i>Add New Role
                        </a>
                        @endif

                        @if(auth('admin')->user()->hasRole('super_admin'))
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-key me-1"></i>Manage Permissions
                        </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters and Search -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('admin.roles.index') }}" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Search</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control" name="search"
                                               value="{{ request('search') }}"
                                               placeholder="Role name or description">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-outline-success">
                                            <i class="bi bi-funnel me-1"></i>Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Roles Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Role Information</th>
                                    <th>Users Count</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>{{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}</td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $role->display_name }}
                                                @if($role->name === 'super_admin')
                                                    <span class="badge bg-danger ms-2">System</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-code-slash me-1"></i>{{ $role->name }}
                                            </small>
                                            @if($role->description)
                                                <br><small class="text-muted">{{ $role->description }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info fs-6">
                                            {{ $role->admin_users_count }} {{ $role->admin_users_count === 1 ? 'User' : 'Users' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary fs-6">
                                            {{ $role->permissions_count ?? 0 }} Permissions
                                        </span>
                                    </td>
                                    <td>
                                        @if(auth('admin')->user()->hasPermission('roles.update') && !in_array($role->name, ['super_admin']))
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle"
                                                       type="checkbox"
                                                       data-role-id="{{ $role->id }}"
                                                       {{ $role->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    <span class="badge bg-{{ $role->is_active ? 'success' : 'secondary' }}">
                                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </label>
                                            </div>
                                        @else
                                            <span class="badge bg-{{ $role->is_active ? 'success' : 'secondary' }}">
                                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ $role->created_at->format('M d, Y') }}<br>
                                            <span class="text-muted">{{ $role->created_at->format('H:i A') }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if(auth('admin')->user()->hasPermission('roles.read'))
                                            <a href="{{ route('admin.roles.show', $role) }}"
                                               class="btn btn-sm btn-outline-info"
                                               data-bs-toggle="tooltip" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif

                                            @if(auth('admin')->user()->hasPermission('roles.update'))
                                            <a href="{{ route('admin.roles.edit', $role) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               data-bs-toggle="tooltip" title="Edit Role">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif

                                            @if(auth('admin')->user()->hasPermission('roles.delete') && !in_array($role->name, ['super_admin', 'manager', 'staff']))
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-role"
                                                    data-role-id="{{ $role->id }}"
                                                    data-role-name="{{ $role->display_name }}"
                                                    data-bs-toggle="tooltip" title="Delete Role">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-shield-x" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No roles found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($roles->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $roles->firstItem() }} to {{ $roles->lastItem() }}
                                    of {{ $roles->total() }} results
                                </small>
                            </div>
                            <div>
                                {{ $roles->withQueryString()->links() }}
                            </div>
                        </div>
                    @endif
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
                <p>Are you sure you want to delete the role <strong id="roleName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
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
    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .status-toggle {
        cursor: pointer;
    }

    .form-switch .form-check-input {
        width: 2.5rem;
        height: 1.25rem;
    }

    .badge.fs-6 {
        font-size: 0.875rem !important;
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

    // Status toggle functionality
    $('.status-toggle').change(function() {
        const roleId = $(this).data('role-id');
        const isChecked = $(this).is(':checked');
        const toggle = $(this);
        const badge = toggle.siblings('label').find('.badge');

        $.ajax({
            url: `/admin/roles/${roleId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                toggle.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    badge.removeClass('bg-success bg-secondary')
                         .addClass(response.status ? 'bg-success' : 'bg-secondary')
                         .text(response.status ? 'Active' : 'Inactive');

                    // Show success message
                    showAlert('success', response.message);
                } else {
                    // Revert checkbox state
                    toggle.prop('checked', !isChecked);
                    showAlert('error', response.message || 'Failed to update status');
                }
            },
            error: function(xhr) {
                // Revert checkbox state
                toggle.prop('checked', !isChecked);

                let errorMessage = 'Failed to update status';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                showAlert('error', errorMessage);
            },
            complete: function() {
                toggle.prop('disabled', false);
            }
        });
    });

    // Delete role functionality
    $('.delete-role').click(function() {
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');

        $('#roleName').text(roleName);
        $('#deleteForm').attr('action', `/admin/roles/${roleId}`);
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

        // Insert alert at the top of the card body
        $('.card-body').prepend(alertHtml);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
