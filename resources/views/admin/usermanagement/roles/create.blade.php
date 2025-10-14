@extends('admin.layouts.app')

@section('title', 'Create Role')

@section('page_title', 'Create New Role')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">Create Role</li>
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
                        <i class="bi bi-shield-plus me-2"></i>Create New Role
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST" id="createRoleForm">
                        @csrf

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
                                           value="{{ old('display_name') }}"
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
                                           value="{{ old('name') }}"
                                           placeholder="e.g. content_manager"
                                           required>
                                    <small class="form-text text-muted">Lowercase letters and underscores only</small>
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
                                              placeholder="Describe the role's purpose and responsibilities">{{ old('description') }}</textarea>
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
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Role Active
                                            <small class="text-muted d-block">Enable this role for assignment</small>
                                        </label>
                                    </div>
                                </div>

                                <!-- Permission Summary -->
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-muted mb-2">Permission Summary</h6>
                                        <div id="permissionSummary">
                                            <small class="text-muted">Select permissions to see summary</small>
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
                                                           data-module="{{ $module }}">
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
                                                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back to Roles
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>Create Role
                                        </button>
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

    .module-toggle:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Generate system name from display name
    $('#display_name').on('input', function() {
        const displayName = $(this).val();
        const systemName = displayName.toLowerCase()
                                     .replace(/[^a-z0-9\s]/g, '')
                                     .replace(/\s+/g, '_')
                                     .substring(0, 50);

        if ($('#name').val() === '' || $('#name').data('auto-generated')) {
            $('#name').val(systemName).data('auto-generated', true);
        }
    });

    // Mark system name as manually entered
    $('#name').on('input', function() {
        $(this).data('auto-generated', false);
    });

    // Select all permissions
    $('#selectAll').click(function() {
        $('.permission-checkbox').prop('checked', true);
        $('.module-toggle').prop('checked', true);
        updatePermissionSummary();
    });

    // Deselect all permissions
    $('#deselectAll').click(function() {
        $('.permission-checkbox').prop('checked', false);
        $('.module-toggle').prop('checked', false);
        updatePermissionSummary();
    });

    // Module toggle functionality
    $('.module-toggle').change(function() {
        const module = $(this).data('module');
        const isChecked = $(this).is(':checked');

        $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
        updatePermissionSummary();
    });

    // Individual permission checkbox change
    $('.permission-checkbox').change(function() {
        const module = $(this).data('module');
        const moduleCheckboxes = $(`.permission-checkbox[data-module="${module}"]`);
        const checkedCount = moduleCheckboxes.filter(':checked').length;
        const totalCount = moduleCheckboxes.length;

        // Update module toggle state
        const moduleToggle = $(`.module-toggle[data-module="${module}"]`);
        moduleToggle.prop('checked', checkedCount === totalCount);
        moduleToggle.prop('indeterminate', checkedCount > 0 && checkedCount < totalCount);

        updatePermissionSummary();
    });

    // Update permission summary
    function updatePermissionSummary() {
        const checkedPermissions = $('.permission-checkbox:checked');
        const totalPermissions = $('.permission-checkbox').length;

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

    // Form validation
    $('#createRoleForm').submit(function(e) {
        let isValid = true;

        // Check if at least one permission is selected
        if ($('.permission-checkbox:checked').length === 0) {
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
