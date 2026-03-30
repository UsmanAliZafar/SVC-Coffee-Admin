@extends('admin.layouts.app')

@section('title', 'Create Permission')

@section('page_title', 'Create New Permission')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
            <li class="breadcrumb-item active">Create Permission</li>
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
                    Creating new permissions affects system security. Ensure the permission name follows the module.action format.
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-key-fill me-2"></i>Create New Permission
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.permissions.store') }}" method="POST" id="createPermissionForm">
                        @csrf

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
                                           value="{{ old('module') }}"
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
                                           value="{{ old('action') }}"
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
                                           value="{{ old('display_name') }}"
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
                                              placeholder="Describe what this permission allows users to do">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Preview & Guidelines -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-eye me-2"></i>Permission Preview
                                </h6>

                                <!-- Live Preview -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-2">Generated Permission</h6>
                                        <div id="permissionPreview">
                                            <div class="mb-2">
                                                <strong>System Name:</strong>
                                                <code id="previewName" class="text-primary">module.action</code>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Display Name:</strong>
                                                <span id="previewDisplayName" class="text-info">Will be generated...</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Description:</strong>
                                                <span id="previewDescription" class="text-muted">No description provided</span>
                                            </div>
                                        </div>

                                        <!-- Availability Check -->
                                        <div id="availabilityCheck" class="mt-3" style="display: none;">
                                            <div id="availabilityResult"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guidelines -->
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <i class="bi bi-lightbulb me-2"></i>Permission Guidelines
                                    </div>
                                    <div class="card-body">
                                        <h6>Naming Conventions:</h6>
                                        <ul class="small mb-3">
                                            <li><strong>Module:</strong> Represents a feature area (users, products, orders)</li>
                                            <li><strong>Action:</strong> Specific operation (read, create, update, delete)</li>
                                            <li>Use snake_case for both module and action</li>
                                            <li>Be specific and descriptive</li>
                                        </ul>

                                        <h6>Common Patterns:</h6>
                                        <div class="small">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>CRUD Operations:</strong>
                                                    <ul class="list-unstyled">
                                                        <li><code>users.read</code></li>
                                                        <li><code>users.create</code></li>
                                                        <li><code>users.update</code></li>
                                                        <li><code>users.delete</code></li>
                                                    </ul>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Special Actions:</strong>
                                                    <ul class="list-unstyled">
                                                        <li><code>orders.export</code></li>
                                                        <li><code>reports.generate</code></li>
                                                        <li><code>settings.manage</code></li>
                                                        <li><code>backup.restore</code></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <i class="bi bi-lightning me-2"></i>Quick Fill
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillCRUD('products')">
                                                Products CRUD Set
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="fillCRUD('orders')">
                                                Orders CRUD Set
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="fillCRUD('customers')">
                                                Customers CRUD Set
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Click to auto-fill common permission patterns</small>
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
                                        {{-- <a href="{{ route('admin.permissions.bulk-create') }}" class="btn btn-outline-info ms-2">
                                            <i class="bi bi-plus-square me-1"></i>Bulk Create Instead
                                        </a> --}}
                                    </div>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>Create Permission
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

    .card.border-info {
        border-color: #0dcaf0 !important;
    }

    .bg-info {
        background-color: #0dcaf0 !important;
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

        // Check availability if both module and action are provided
        if (module !== 'module' && action !== 'action' && module.length > 0 && action.length > 0) {
            checkAvailability(module, action);
        } else {
            $('#availabilityCheck').hide();
        }
    }

    // Auto-generate display name
    function generateDisplayName() {
        const module = $('#module').val();
        const action = $('#action').val();

        if (module && action && $('#display_name').data('auto-generated') !== false) {
            const moduleFormatted = module.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const actionFormatted = action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const generated = `${actionFormatted} ${moduleFormatted}`;

            $('#display_name').val(generated).data('auto-generated', true);
        }
    }

    // Auto-generate description
    function generateDescription() {
        const module = $('#module').val();
        const action = $('#action').val();

        if (module && action && $('#description').val() === '') {
            const generated = `Allow ${action} access to ${module} module`;
            $('#description').val(generated);
        }
    }

    // Check permission availability
    function checkAvailability(module, action) {
        $.ajax({
            url: '{{ route("admin.permissions.check-availability") }}',
            method: 'GET',
            data: { module: module, action: action },
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
    $('#module, #action').on('input', function() {
        updatePreview();
        generateDisplayName();
        generateDescription();
    });

    $('#display_name').on('input', function() {
        $(this).data('auto-generated', false);
        updatePreview();
    });

    $('#description').on('input', function() {
        updatePreview();
    });

    // Form validation
    $('#createPermissionForm').submit(function(e) {
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

// Quick fill functions
function fillCRUD(module) {
    $('#module').val(module);
    $('#action').val('read');
    $('#module, #action').trigger('input');
}
</script>
@endpush
