@extends('admin.layouts.app')

@section('title', 'Edit Permission')

@section('page_title', 'Edit Permission')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
            <li class="breadcrumb-item active">Edit Permission</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Warning Alert -->
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <strong>System Security Warning:</strong>
                    Modifying permissions affects system security. Changes will impact all roles using this permission.
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil me-2"></i>Edit Permission: {{ $permission->display_name }}
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" id="editPermissionForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Permission Details -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Permission Details
                                </h6>

                                <div class="mb-3">
                                    <label for="module" class="form-label">Module Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('module') is-invalid @enderror"
                                           id="module"
                                           name="module"
                                           value="{{ old('module', $permission->module) }}"
                                           placeholder="e.g. products, users, orders"
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
                                    <label for="action" class="form-label">Action Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('action') is-invalid @enderror"
                                           id="action"
                                           name="action"
                                           value="{{ old('action', $permission->action) }}"
                                           placeholder="e.g. read, create, update, delete"
                                           list="actionList"
                                           required>
                                    <datalist id="actionList">
                                        @foreach($existingActions as $existingAction)
                                            <option value="{{ $existingAction }}">{{ ucwords(str_replace('_', ' ', $existingAction)) }}</option>
                                        @endforeach
                                        <option value="read">Read</option>
                                        <option value="create">Create</option>
                                        <option value="update">Update</option>
                                        <option value="delete">Delete</option>
                                        <option value="export">Export</option>
                                        <option value="import">Import</option>
                                    </datalist>
                                    <small class="form-text text-muted">Lowercase letters and underscores only</small>
                                    @error('action')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('display_name') is-invalid @enderror"
                                           id="display_name"
                                           name="display_name"
                                           value="{{ old('display_name', $permission->display_name) }}"
                                           placeholder="e.g. Create Products"
                                           required>
                                    <small class="form-text text-muted">Human-readable permission name</small>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              placeholder="Describe what this permission allows users to do">{{ old('description', $permission->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Current Permission Info -->
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-muted mb-2">Current Permission</h6>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h5 class="text-info mb-0">{{ $permission->roles()->count() }}</h5>
                                                    <small class="text-muted">Assigned Roles</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h5 class="text-success mb-0">{{ $permission->created_at->diffForHumans() }}</h5>
                                                <small class="text-muted">Created</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview & Impact -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-eye me-2"></i>Changes Preview & Impact
                                </h6>

                                <!-- Live Preview -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-2">Updated Permission</h6>
                                        <div id="permissionPreview">
                                            <div class="mb-2">
                                                <strong>System Name:</strong>
                                                <code id="previewName" class="text-primary">{{ $permission->name }}</code>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Display Name:</strong>
                                                <span id="previewDisplayName" class="text-info">{{ $permission->display_name }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Description:</strong>
                                                <span id="previewDescription" class="text-muted">{{ $permission->description ?: 'No description provided' }}</span>
                                            </div>
                                        </div>

                                        <!-- Availability Check -->
                                        <div id="availabilityCheck" class="mt-3" style="display: none;">
                                            <div id="availabilityResult"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Impact Analysis -->
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Impact Analysis
                                    </div>
                                    <div class="card-body">
                                        <h6>Affected Roles ({{ $permission->roles()->count() }}):</h6>
                                        @if($permission->roles()->count() > 0)
                                            <div class="mb-3">
                                                @foreach($permission->roles as $role)
                                                    <span class="badge bg-info text-dark me-1 mb-1">{{ $role->display_name }}</span>
                                                @endforeach
                                            </div>
                                            <div class="alert alert-warning">
                                                <small>
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Changes to this permission will affect all users assigned to the above roles.
                                                </small>
                                            </div>
                                        @else
                                            <p class="text-muted mb-3">No roles are currently using this permission.</p>
                                        @endif

                                        <h6>Change Impact:</h6>
                                        <ul class="small mb-0">
                                            <li>System name changes will require code updates</li>
                                            <li>Display name changes affect admin interface</li>
                                            <li>Description changes are informational only</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Permission Guidelines -->
                                <div class="card border-info mt-3">
                                    <div class="card-header bg-info text-white">
                                        <i class="bi bi-lightbulb me-2"></i>Best Practices
                                    </div>
                                    <div class="card-body">
                                        <ul class="small mb-0">
                                            <li>Test permission changes in development first</li>
                                            <li>Notify affected users about permission updates</li>
                                            <li>Keep permission names descriptive and consistent</li>
                                            <li>Document permission purpose in description field</li>
                                        </ul>
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
                                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>Back to Permissions
                                        </a>
                                        <a href="{{ route('admin.permissions.show', $permission) }}" class="btn btn-outline-info ms-2">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Changes
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>Update Permission
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

    .border-end {
        border-right: 1px solid #dee2e6 !important;
    }

    code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
    }

    #availabilityResult.available {
        color: #198754;
    }

    #availabilityResult.unavailable {
        color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const originalName = '{{ $permission->name }}';

    // Live preview updates
    function updatePreview() {
        const module = $('#module').val() || 'module';
        const action = $('#action').val() || 'action';
        const displayName = $('#display_name').val() || 'Will be generated...';
        const description = $('#description').val() || 'No description provided';

        const systemName = module + '.' + action;

        $('#previewName').text(systemName);
        $('#previewDisplayName').text(displayName);
        $('#previewDescription').text(description);

        // Check availability if both module and action are provided and name has changed
        if (module !== 'module' && action !== 'action' && module.length > 0 && action.length > 0 && systemName !== originalName) {
            checkAvailability(module, action);
        } else {
            $('#availabilityCheck').hide();
        }
    }

    // Check permission availability
    function checkAvailability(module, action) {
        $.ajax({
            url: '{{ route("admin.permissions.check-availability") }}',
            method: 'GET',
            data: {
                module: module,
                action: action,
                exclude_id: '{{ $permission->id }}'
            },
            success: function(response) {
                $('#availabilityCheck').show();
                const resultDiv = $('#availabilityResult');

                if (response.available) {
                    resultDiv.html('<i class="bi bi-check-circle-fill me-1"></i>' + response.message)
                            .removeClass('unavailable')
                            .addClass('available');
                } else {
                    resultDiv.html('<i class="bi bi-x-circle-fill me-1"></i>' + response.message)
                            .removeClass('available')
                            .addClass('unavailable');
                }
            },
            error: function() {
                $('#availabilityCheck').hide();
            }
        });
    }

    // Event listeners
    $('#module, #action, #display_name, #description').on('input', updatePreview);

    // Form validation
    $('#editPermissionForm').submit(function(e) {
        const module = $('#module').val();
        const action = $('#action').val();

        // Validate format
        const moduleRegex = /^[a-z_]+$/;
        const actionRegex = /^[a-z_]+$/;

        if (!moduleRegex.test(module)) {
            e.preventDefault();
            showAlert('error', 'Module name must contain only lowercase letters and underscores.');
            $('#module').focus();
            return false;
        }

        if (!actionRegex.test(action)) {
            e.preventDefault();
            showAlert('error', 'Action name must contain only lowercase letters and underscores.');
            $('#action').focus();
            return false;
        }

        // Confirm if name is changing and permission is in use
        const newName = module + '.' + action;
        const rolesCount = {{ $permission->roles()->count() }};

        if (newName !== originalName && rolesCount > 0) {
            const confirmMessage = `This permission is used by ${rolesCount} role(s). Changing the system name may require code updates. Are you sure you want to continue?`;
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        }
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
    }
});
</script>
@endpush
