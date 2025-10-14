@extends('admin.layouts.app')

@section('title', 'Edit Role')

@section('page_title', 'Edit Role')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">Edit Role</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil me-2"></i>Edit Role: {{ $role->display_name }}
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $role) }}" method="POST" id="editRoleForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Role Information -->
                            <div class="col-md-4">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Role Information
                                </h6>

                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('display_name') is-invalid @enderror"
                                           id="display_name"
                                           name="display_name"
                                           value="{{ old('display_name', $role->display_name) }}"
                                           placeholder="e.g. Content Manager"
                                           required>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">System Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $role->name) }}"
                                           placeholder="e.g. content_manager"
                                           {{ $role->name === 'super_admin' ? 'readonly' : '' }}
                                           required>
                                    <small class="form-text text-muted">
                                        @if($role->name === 'super_admin')
                                            System role name cannot be changed
                                        @else
                                            Lowercase letters and underscores only
                                        @endif
                                    </small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              placeholder="Describe the role's purpose and responsibilities">{{ old('description', $role->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="is_active"
                                               id="is_active"
                                               value="1"
                                               {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                               {{ $role->name === 'super_admin' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Role Active
                                            <small class="text-muted d-block">
                                                @if($role->name === 'super_admin')
                                                    System role cannot be deactivated
                                                @else
                                                    Enable this role for assignment
                                                @endif
                                            </small>
                                        </label>
                                    </div>
                                </div>

                                <!-- Role Statistics -->
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-muted mb-2">Role Statistics</h6>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h5 class="text-success mb-0">{{ $role->adminUsers()->count() }}</h5>
                                                    <small class="text-muted">Assigned Users</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h5 class="text-info mb-0">{{ $role->permissions()->count() }}</h5>
                                                <small class="text-muted">Permissions</small>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div id="permissionSummary">
                                            <!-- Will be updated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions Selection -->
                            <div class="col-md-8">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-key me-2"></i>Assign Permissions <span class="text-danger">*</span>
                                </h6>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-success btn-sm" id="selectAll">
                                                <i class="bi bi-check-all me-1"></i>Select All
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                                <i class="bi bi-x-circle me-1"></i>Deselect All
                                            </button>
                                            @if($role->name !== 'super_admin')
                                            <button type="button" class="btn btn-outline-info btn-sm" id="selectRecommended">
                                                <i class="bi bi-star me-1"></i>Recommended Set
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="permissions-container" style="max-height: 500px; overflow-y: auto;">
                                    @foreach($groupedPermissions as $module => $permissions)
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-folder me-2"></i>
                                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                                </h6>
                                                <div class="form-check">
                                                    <input class="form-check-input module-toggle"
                                                           type="checkbox"
                                                           id="module_{{ $module }}"
                                                           data-module="{{ $module }}"
                                                           {{ $role->name === 'super_admin' ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="module_{{ $module }}">
                                                        <small>Select All</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($permissions as $permission)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox"
                                                               type="checkbox"
                                                               name="permissions[]"
                                                               value="{{ $permission->id }}"
                                                               id="permission_{{ $permission->id }}"
                                                               data-module="{{ $module }}"
                                                               {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                               {{ $role->name === 'super_admin' ? 'disabled' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <strong>{{ $permission->display_name }}</strong>
                                                            @if($permission->description)
                                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    @error('permissions')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror

                                    @if($role->name === 'super_admin')
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Super Administrator:</strong> This role automatically has all permissions and cannot be modified.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>Back to Roles
                                        </a>
                                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-info ms-2">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                    <div>
                                        @if($role->name !== 'super_admin')
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Changes
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>Update Role
                                        </button>
                                        @else
                                        <span class="text-muted">
                                            <i class="bi bi-lock me-1"></i>System role cannot be modified
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-check-input:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .form-check-input:focus {
        border-color: #5B914C;
        box-shadow: 0 0 0 0.25rem rgba(91, 145, 76, 0.25);
    }

    .btn-success {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .btn-success:hover {
        background-color: #4a7a3f;
        border-color: #4a7a3f;
    }

    .btn-outline-success {
        color: #5B914C;
        border-color: #5B914C;
    }

    .btn-outline-success:hover {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .permissions-container .card {
        transition: all 0.3s ease;
    }

    .permissions-container .card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .border-end {
        border-right: 1px solid #dee2e6 !important;
    }

    .form-check-input:disabled {
        opacity: 0.5;
    }

    .form-check-input:disabled + .form-check-label {
        opacity: 0.5;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const isSystemRole = {{ $role->name === 'super_admin' ? 'true' : 'false' }};

    if (!isSystemRole) {
        // Select all permissions
        $('#selectAll').click(function() {
            $('.permission-checkbox:not(:disabled)').prop('checked', true);
            $('.module-toggle:not(:disabled)').prop('checked', true);
            updatePermissionSummary();
        });

        // Deselect all permissions
        $('#deselectAll').click(function() {
            $('.permission-checkbox:not(:disabled)').prop('checked', false);
            $('.module-toggle:not(:disabled)').prop('checked', false);
            updatePermissionSummary();
        });

        // Select recommended permissions
        $('#selectRecommended').click(function() {
            // Define recommended permissions for different role types
            const recommendedActions = ['read', 'create', 'update'];

            $('.permission-checkbox:not(:disabled)').each(function() {
                const permissionName = $(this).closest('label').find('strong').text().toLowerCase();
                const shouldCheck = recommendedActions.some(action => permissionName.includes(action));
                $(this).prop('checked', shouldCheck);
            });

            // Update module toggles
            $('.module-toggle:not(:disabled)').each(function() {
                const module = $(this).data('module');
                const moduleCheckboxes = $(`.permission-checkbox[data-module="${module}"]:not(:disabled)`);
                const checkedCount = moduleCheckboxes.filter(':checked').length;
                const totalCount = moduleCheckboxes.length;

                $(this).prop('checked', checkedCount === totalCount);
                $(this).prop('indeterminate', checkedCount > 0 && checkedCount < totalCount);
            });

            updatePermissionSummary();
        });

        // Module toggle functionality
        $('.module-toggle').change(function() {
            if ($(this).is(':disabled')) return;

            const module = $(this).data('module');
            const isChecked = $(this).is(':checked');

            $(`.permission-checkbox[data-module="${module}"]:not(:disabled)`).prop('checked', isChecked);
            updatePermissionSummary();
        });

        // Individual permission checkbox change
        $('.permission-checkbox').change(function() {
            if ($(this).is(':disabled')) return;

            const module = $(this).data('module');
            const moduleCheckboxes = $(`.permission-checkbox[data-module="${module}"]:not(:disabled)`);
            const checkedCount = moduleCheckboxes.filter(':checked').length;
            const totalCount = moduleCheckboxes.length;

            // Update module toggle state
            const moduleToggle = $(`.module-toggle[data-module="${module}"]:not(:disabled)`);
            moduleToggle.prop('checked', checkedCount === totalCount);
            moduleToggle.prop('indeterminate', checkedCount > 0 && checkedCount < totalCount);

            updatePermissionSummary();
        });

        // Form validation
        $('#editRoleForm').submit(function(e) {
            let isValid = true;

            // Check if at least one permission is selected
            if ($('.permission-checkbox:checked:not(:disabled)').length === 0) {
                e.preventDefault();
                isValid = false;

                // Show error message
                showAlert('error', 'Please select at least one permission for the role.');

                // Scroll to permissions section
                $('html, body').animate({
                    scrollTop: $('.permissions-container').offset().top - 100
                }, 500);
            }

            return isValid;
        });
    }

    // Update permission summary
    function updatePermissionSummary() {
        const checkedPermissions = $('.permission-checkbox:checked:not(:disabled)');
        const totalPermissions = $('.permission-checkbox:not(:disabled)').length;

        if (isSystemRole) {
            $('#permissionSummary').html('<small class="text-info">System role has all permissions</small>');
            return;
        }

        if (checkedPermissions.length === 0) {
            $('#permissionSummary').html('<small class="text-muted">No permissions selected</small>');
            return;
        }

        // Group by modules
        const moduleCount = {};
        checkedPermissions.each(function() {
            const module = $(this).data('module');
            moduleCount[module] = (moduleCount[module] || 0) + 1;
        });

        let summaryHtml = `<small><strong>${checkedPermissions.length}</strong> of <strong>${totalPermissions}</strong> permissions selected</small><br>`;

        Object.keys(moduleCount).forEach(module => {
            const moduleName = module.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            summaryHtml += `<span class="badge bg-info text-dark me-1 mb-1">${moduleName}: ${moduleCount[module]}</span>`;
        });

        $('#permissionSummary').html(summaryHtml);
    }

    // Initialize module toggle states
    if (!isSystemRole) {
        $('.module-toggle').each(function() {
            const module = $(this).data('module');
            const moduleCheckboxes = $(`.permission-checkbox[data-module="${module}"]:not(:disabled)`);
            const checkedCount = moduleCheckboxes.filter(':checked').length;
            const totalCount = moduleCheckboxes.length;

            $(this).prop('checked', checkedCount === totalCount);
            $(this).prop('indeterminate', checkedCount > 0 && checkedCount < totalCount);
        });
    }

    // Initialize permission summary
    updatePermissionSummary();

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
