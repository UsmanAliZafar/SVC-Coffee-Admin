@extends('admin.layouts.app')

@section('title', 'Bulk Create Permissions')

@section('page_title', 'Bulk Create Permissions')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.usermanagement.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.usermanagement.permissions.index') }}">Permissions</a></li>
            <li class="breadcrumb-item active">Bulk Create</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Info Alert -->
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>
                    <strong>Bulk Permission Creation:</strong>
                    Create multiple permissions for a module at once. This is useful for setting up CRUD operations or multiple actions for a feature.
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-square me-2"></i>Bulk Create Permissions
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.usermanagement.permissions.bulk-create.store') }}" method="POST" id="bulkCreateForm">
                        @csrf

                        <div class="row">
                            <!-- Module Configuration -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-folder me-2"></i>Module Configuration
                                </h6>

                                <div class="mb-3">
                                    <label for="module" class="form-label">Module Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('module') is-invalid @enderror"
                                           id="module"
                                           name="module"
                                           value="{{ old('module') }}"
                                           placeholder="e.g. products, orders, customers"
                                           list="moduleList"
                                           required>
                                    <datalist id="moduleList">
                                        @foreach($existingModules as $existingModule)
                                            <option value="{{ $existingModule }}">{{ ucwords(str_replace('_', ' ', $existingModule)) }}</option>
                                        @endforeach
                                    </datalist>
                                    <small class="form-text text-muted">Lowercase letters and underscores only</small>
                                    @error('module')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Actions <span class="text-danger">*</span></label>

                                    <!-- Quick Selection Buttons -->
                                    <div class="mb-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectCRUD">
                                                <i class="bi bi-check-all me-1"></i>CRUD Set
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" id="selectAll">
                                                <i class="bi bi-check-square me-1"></i>Select All
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAll">
                                                <i class="bi bi-x-square me-1"></i>Clear All
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Common Actions Grid -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="small text-muted mb-2">Common Actions</h6>
                                            @foreach($commonActions as $action)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input action-checkbox"
                                                       type="checkbox"
                                                       name="actions[]"
                                                       value="{{ $action }}"
                                                       id="action_{{ $action }}"
                                                       {{ in_array($action, old('actions', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="action_{{ $action }}">
                                                    <strong>{{ ucwords($action) }}</strong>
                                                    <br><small class="text-muted">{{ $action }}.module</small>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="small text-muted mb-2">Additional Actions</h6>
                                            @php
                                                $additionalActions = ['export', 'import', 'manage', 'approve', 'publish', 'archive'];
                                            @endphp
                                            @foreach($additionalActions as $action)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input action-checkbox"
                                                       type="checkbox"
                                                       name="actions[]"
                                                       value="{{ $action }}"
                                                       id="action_{{ $action }}"
                                                       {{ in_array($action, old('actions', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="action_{{ $action }}">
                                                    <strong>{{ ucwords($action) }}</strong>
                                                    <br><small class="text-muted">{{ $action }}.module</small>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Custom Actions -->
                                    <div class="mt-3">
                                        <h6 class="small text-muted mb-2">Custom Actions</h6>
                                        <div class="input-group mb-2">
                                            <input type="text"
                                                   class="form-control"
                                                   id="customAction"
                                                   placeholder="Enter custom action name">
                                            <button class="btn btn-outline-success"
                                                    type="button"
                                                    id="addCustomAction">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <div id="customActionsContainer"></div>
                                    </div>

                                    @error('actions')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Preview & Summary -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-eye me-2"></i>Preview & Summary
                                </h6>

                                <!-- Live Preview -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">Permissions to be Created</h6>
                                        <div id="permissionPreview">
                                            <p class="text-muted">Select a module and actions to see preview</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Template Examples -->
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <i class="bi bi-lightbulb me-2"></i>Quick Templates
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm template-btn"
                                                    data-module="products" data-actions='["read","create","update","delete","export"]'>
                                                <i class="bi bi-box me-2"></i>Products Management
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm template-btn"
                                                    data-module="orders" data-actions='["read","create","update","delete","export","approve"]'>
                                                <i class="bi bi-cart me-2"></i>Orders Management
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm template-btn"
                                                    data-module="customers" data-actions='["read","create","update","delete","export"]'>
                                                <i class="bi bi-people me-2"></i>Customers Management
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm template-btn"
                                                    data-module="reports" data-actions='["read","export","generate"]'>
                                                <i class="bi bi-graph-up me-2"></i>Reports System
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm template-btn"
                                                    data-module="settings" data-actions='["read","update","manage"]'>
                                                <i class="bi bi-gear me-2"></i>Settings Management
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Click to auto-fill common module patterns</small>
                                    </div>
                                </div>

                                <!-- Existing Permissions Warning -->
                                <div id="existingPermissionsWarning" class="card border-warning mt-3" style="display: none;">
                                    <div class="card-header bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Existing Permissions
                                    </div>
                                    <div class="card-body">
                                        <div id="existingPermissionsList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('admin.usermanagement.permissions.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>Back to Permissions
                                        </a>
                                        <a href="{{ route('admin.usermanagement.permissions.create') }}" class="btn btn-outline-info ms-2">
                                            <i class="bi bi-plus me-1"></i>Single Create Instead
                                        </a>
                                    </div>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="bi bi-check-circle me-1"></i>Create <span id="submitCount">0</span> Permissions
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

    .btn-outline-success:hover {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .template-btn {
        text-align: left;
    }

    .custom-action-tag {
        display: inline-block;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.25rem 0.5rem;
        margin: 0.125rem;
        font-size: 0.875rem;
    }

    .custom-action-tag .remove-action {
        color: #dc3545;
        cursor: pointer;
        margin-left: 0.5rem;
    }

    .permission-item {
        background-color: #f8f9fa;
        border-left: 4px solid #5B914C;
        padding: 0.5rem;
        margin-bottom: 0.25rem;
        border-radius: 0.25rem;
    }

    .permission-item.existing {
        border-left-color: #ffc107;
        background-color: #fff3cd;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let customActionCounter = 0;

    // Update preview when module or actions change
    function updatePreview() {
        const module = $('#module').val();
        const checkedActions = $('.action-checkbox:checked');

        if (!module || checkedActions.length === 0) {
            $('#permissionPreview').html('<p class="text-muted">Select a module and actions to see preview</p>');
            $('#submitCount').text('0');
            return;
        }

        let previewHtml = '';
        let count = 0;

        checkedActions.each(function() {
            const action = $(this).val();
            const permissionName = module + '.' + action;
            const displayName = action.charAt(0).toUpperCase() + action.slice(1) + ' ' +
                               module.charAt(0).toUpperCase() + module.slice(1).replace(/_/g, ' ');

            previewHtml += `
                <div class="permission-item">
                    <strong>${displayName}</strong><br>
                    <code class="small">${permissionName}</code>
                </div>
            `;
            count++;
        });

        $('#permissionPreview').html(previewHtml);
        $('#submitCount').text(count);

        // Check for existing permissions
        if (module) {
            checkExistingPermissions(module, checkedActions);
        }
    }

    // Check for existing permissions
    function checkExistingPermissions(module, checkedActions) {
        const actions = [];
        checkedActions.each(function() {
            actions.push($(this).val());
        });

        if (actions.length === 0) {
            $('#existingPermissionsWarning').hide();
            return;
        }

        // Simple check - you might want to implement this as an AJAX call
        $.ajax({
            url: '{{ route("admin.usermanagement.permissions.get-by-module") }}',
            method: 'GET',
            data: { module: module },
            success: function(response) {
                const existingActions = response.map(p => p.action);
                const conflicts = actions.filter(action => existingActions.includes(action));

                if (conflicts.length > 0) {
                    let warningHtml = '<p class="mb-2">The following permissions already exist and will be skipped:</p>';
                    conflicts.forEach(action => {
                        warningHtml += `<div class="permission-item existing">
                            <code>${module}.${action}</code>
                        </div>`;
                    });
                    $('#existingPermissionsList').html(warningHtml);
                    $('#existingPermissionsWarning').show();
                } else {
                    $('#existingPermissionsWarning').hide();
                }
            },
            error: function() {
                $('#existingPermissionsWarning').hide();
            }
        });
    }

    // Event listeners
    $('#module').on('input', updatePreview);
    $('.action-checkbox').on('change', updatePreview);

    // Quick selection buttons
    $('#selectCRUD').click(function() {
        $('.action-checkbox').prop('checked', false);
        ['read', 'create', 'update', 'delete'].forEach(action => {
            $(`#action_${action}`).prop('checked', true);
        });
        updatePreview();
    });

    $('#selectAll').click(function() {
        $('.action-checkbox').prop('checked', true);
        updatePreview();
    });

    $('#clearAll').click(function() {
        $('.action-checkbox').prop('checked', false);
        updatePreview();
    });

    // Template buttons
    $('.template-btn').click(function() {
        const module = $(this).data('module');
        const actions = $(this).data('actions');

        $('#module').val(module);
        $('.action-checkbox').prop('checked', false);

        actions.forEach(action => {
            $(`#action_${action}`).prop('checked', true);
        });

        updatePreview();
    });

    // Custom action functionality
    $('#addCustomAction').click(function() {
        const customAction = $('#customAction').val().trim().toLowerCase();

        if (!customAction) {
            showAlert('error', 'Please enter a custom action name');
            return;
        }

        if (!/^[a-z_]+$/.test(customAction)) {
            showAlert('error', 'Action name must contain only lowercase letters and underscores');
            return;
        }

        // Check if action already exists in form
        if ($(`#action_${customAction}`).length > 0) {
            showAlert('error', 'This action is already added');
            return;
        }

        // Add custom action checkbox
        const customActionHtml = `
            <div class="form-check mb-2 custom-action-item" data-action="${customAction}">
                <input class="form-check-input action-checkbox"
                       type="checkbox"
                       name="actions[]"
                       value="${customAction}"
                       id="action_${customAction}"
                       checked>
                <label class="form-check-label" for="action_${customAction}">
                    <strong>${customAction.charAt(0).toUpperCase() + customAction.slice(1)}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-custom-action">
                        <i class="bi bi-x"></i>
                    </button>
                    <br><small class="text-muted">${customAction}.module</small>
                </label>
            </div>
        `;

        $('#customActionsContainer').append(customActionHtml);
        $('#customAction').val('');
        updatePreview();
    });

    // Remove custom action
    $(document).on('click', '.remove-custom-action', function() {
        $(this).closest('.custom-action-item').remove();
        updatePreview();
    });

    // Allow Enter key to add custom action
    $('#customAction').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#addCustomAction').click();
        }
    });

    // Form validation
    $('#bulkCreateForm').submit(function(e) {
        const module = $('#module').val();
        const checkedActions = $('.action-checkbox:checked');

        if (!module) {
            e.preventDefault();
            showAlert('error', 'Please enter a module name');
            $('#module').focus();
            return false;
        }

        if (!/^[a-z_]+$/.test(module)) {
            e.preventDefault();
            showAlert('error', 'Module name must contain only lowercase letters and underscores');
            $('#module').focus();
            return false;
        }

        if (checkedActions.length === 0) {
            e.preventDefault();
            showAlert('error', 'Please select at least one action');
            return false;
        }

        // Disable submit button to prevent double submission
        $('#submitBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Creating...');
    });

    // Initialize preview
    updatePreview();

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

        // Scroll to top to show alert
        $('.card-body').scrollTop(0);
    }
});
</script>
@endpush
