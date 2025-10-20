@extends('admin.layouts.app')
{{-- resources/views/admin/warehouses/edit.blade.php --}}
@section('title', 'Edit Warehouse - ' . $warehouse->name)

@push('styles')
<style>
    .form-section {
        background: white;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .section-title {
        color: #5B914C;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .required-field::after {
        content: "*";
        color: #dc3545;
        margin-left: 3px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #5B914C;
        box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
    }

    .info-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .info-box.danger {
        background: #f8d7da;
        border-color: #dc3545;
        border-left-color: #dc3545;
    }

    .info-box i {
        font-size: 1.2rem;
        margin-right: 10px;
    }

    .btn-save {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        padding: 10px 30px;
        font-weight: 600;
    }

    .btn-save:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }

    .priority-slider {
        width: 100%;
    }

    .priority-value {
        display: inline-block;
        background: #5B914C;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
    }

    .char-counter {
        font-size: 0.875rem;
        color: #6c757d;
        float: right;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
    }

    .default-warehouse-notice {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 6px;
        padding: 12px 15px;
        margin-top: 10px;
    }

    .default-warehouse-notice i {
        color: #ffc107;
    }

    .warehouse-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.2);
    }

    .warehouse-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .warehouse-code-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        display: inline-block;
        margin-top: 5px;
    }

    .summary-card {
        background: linear-gradient(135deg, #f0f7ed 0%, #e8f5e0 100%);
        border: 2px solid #5B914C;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .summary-item {
        padding: 8px 0;
        border-bottom: 1px solid rgba(91, 145, 76, 0.2);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-weight: 600;
        color: #495057;
    }

    .summary-value {
        color: #6c757d;
    }

    .changes-indicator {
        background: #d1ecf1;
        border: 1px solid #0c5460;
        border-radius: 6px;
        padding: 10px 15px;
        margin-bottom: 15px;
        display: none;
    }

    .changes-indicator.show {
        display: block;
    }

    .stock-warning {
        background: #f8d7da;
        border: 2px solid #dc3545;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .stock-stat {
        text-align: center;
        padding: 10px;
        background: white;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .stock-stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .stock-stat-label {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="warehouse-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>
                    <i class="bi bi-pencil-square"></i> Edit Warehouse
                </h2>
                <div class="warehouse-code-badge">{{ $warehouse->code }}</div>
            </div>
            <div>
                <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Back to Details
                </a>
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-list"></i> All Warehouses
                </a>
            </div>
        </div>
    </div>

    <!-- Warning if warehouse has stock -->
    @if($warehouse->hasStock())
    <div class="stock-warning">
        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
        <strong>Warning:</strong> This warehouse currently has active stock.
        @if($warehouse->is_default)
            As the default warehouse, deactivating it may affect operations.
        @endif
        Changes to critical settings should be made carefully.
        <div class="mt-2">
            <strong>Current Stock:</strong> {{ number_format($warehouse->getTotalStock()) }} units across {{ $warehouse->stock()->count() }} products
        </div>
    </div>
    @endif

    <!-- Info Box -->
    @if($warehouse->is_default)
    <div class="info-box">
        <i class="bi bi-star-fill text-warning"></i>
        <strong>Default Warehouse:</strong> This is currently set as the default warehouse. Unchecking this will require setting another warehouse as default.
    </div>
    @endif

    <!-- Changes Indicator -->
    <div class="changes-indicator" id="changesIndicator">
        <i class="bi bi-info-circle"></i>
        <strong>Unsaved Changes:</strong> You have modified the form. Don't forget to save your changes.
    </div>

    <!-- Form -->
    <form id="editWarehouseForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">

                <!-- Basic Information -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-info-circle"></i> Basic Information
                    </h5>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required-field">Warehouse Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ old('name', $warehouse->name) }}"
                                   placeholder="e.g., Main Distribution Center" required maxlength="255">
                            <small class="text-muted">Enter a descriptive name for this warehouse</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Warehouse Code</label>
                            <input type="text" name="code" id="code" class="form-control"
                                   value="{{ old('code', $warehouse->code) }}"
                                   placeholder="e.g., WH-001" maxlength="50">
                            <small class="text-muted">Unique identifier</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control"
                                       value="{{ old('email', $warehouse->email) }}"
                                       placeholder="warehouse@example.com" maxlength="255">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="phone" id="phone" class="form-control"
                                       value="{{ old('phone', $warehouse->phone) }}"
                                       placeholder="+1 (555) 123-4567" maxlength="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-geo-alt"></i> Location Details
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2"
                                  maxlength="500" placeholder="Street address, building number, etc.">{{ old('address', $warehouse->address) }}</textarea>
                        <span class="char-counter">
                            <span id="addressCount">{{ strlen($warehouse->address ?? '') }}</span>/500 characters
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="city" class="form-control"
                                   value="{{ old('city', $warehouse->city) }}"
                                   placeholder="e.g., New York" maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State/Province</label>
                            <input type="text" name="state" id="state" class="form-control"
                                   value="{{ old('state', $warehouse->state) }}"
                                   placeholder="e.g., NY" maxlength="100">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="country" class="form-control"
                                   value="{{ old('country', $warehouse->country) }}"
                                   placeholder="e.g., United States" maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postal/ZIP Code</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control"
                                   value="{{ old('postal_code', $warehouse->postal_code) }}"
                                   placeholder="e.g., 10001" maxlength="20">
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-journal-text"></i> Additional Information
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4"
                                  placeholder="Any additional information about this warehouse (operating hours, special instructions, etc.)">{{ old('notes', $warehouse->notes) }}</textarea>
                        <span class="char-counter">
                            <span id="notesCount">{{ strlen($warehouse->notes ?? '') }}</span> characters
                        </span>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Current Status Info -->
                <div class="summary-card">
                    <h5 class="section-title" style="border-color: #5B914C;">
                        <i class="bi bi-info-circle"></i> Current Status
                    </h5>

                    <div class="stock-stat">
                        <div class="stock-stat-value">{{ number_format($warehouse->getTotalStock()) }}</div>
                        <div class="stock-stat-label">Total Stock Units</div>
                    </div>

                    <div class="stock-stat">
                        <div class="stock-stat-value text-success">{{ $warehouse->stock()->count() }}</div>
                        <div class="stock-stat-label">Products in Warehouse</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Created:</div>
                        <div class="summary-value">{{ $warehouse->created_at->format('M d, Y') }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Last Updated:</div>
                        <div class="summary-value">{{ $warehouse->updated_at->diffForHumans() }}</div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-gear"></i> Settings
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $warehouse->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $warehouse->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <small class="text-muted">Set warehouse operational status</small>
                        @if($warehouse->hasStock())
                        <div class="text-warning mt-2">
                            <i class="bi bi-exclamation-triangle"></i>
                            <small>Deactivating will affect {{ $warehouse->stock()->count() }} products</small>
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_default"
                                   id="is_default" value="1"
                                   {{ old('is_default', $warehouse->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                <strong>Set as Default Warehouse</strong>
                            </label>
                        </div>
                        <small class="text-muted">This will be the primary warehouse for all operations</small>

                        <div id="defaultWarehouseNotice" class="default-warehouse-notice mt-2"
                             style="display: {{ old('is_default', $warehouse->is_default) ? 'block' : 'none' }};">
                            <i class="bi bi-exclamation-triangle"></i>
                            <small><strong>Notice:</strong> This will remove the default status from any other warehouse.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Priority Level</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="priority" id="priority"
                                   class="priority-slider" min="0" max="100"
                                   value="{{ old('priority', $warehouse->priority) }}" step="1">
                            <span class="priority-value" id="priorityValue">{{ old('priority', $warehouse->priority) }}</span>
                        </div>
                        <small class="text-muted">Higher priority warehouses are used first for order fulfillment</small>
                    </div>
                </div>

                <!-- Quick Summary Preview -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-eye"></i> Preview
                    </h5>

                    <div class="summary-item">
                        <div class="summary-label">Name:</div>
                        <div class="summary-value" id="previewName">{{ $warehouse->name }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Code:</div>
                        <div class="summary-value" id="previewCode">{{ $warehouse->code }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Location:</div>
                        <div class="summary-value" id="previewLocation">
                            {{ $warehouse->getFullAddress() ?: 'Not set' }}
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Status:</div>
                        <div class="summary-value" id="previewStatus">
                            {!! $warehouse->getStatusBadge() !!}
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Default:</div>
                        <div class="summary-value" id="previewDefault">
                            @if($warehouse->is_default)
                                <span class="badge bg-primary">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Priority:</div>
                        <div class="summary-value" id="previewPriority">
                            <span class="badge bg-info">{{ $warehouse->priority }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <div>
                    <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Changes
                    </button>
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-check-circle"></i> Update Warehouse
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
// Store original values for change detection
const originalValues = {
    name: '{{ $warehouse->name }}',
    code: '{{ $warehouse->code }}',
    email: '{{ $warehouse->email }}',
    phone: '{{ $warehouse->phone }}',
    address: '{{ $warehouse->address }}',
    city: '{{ $warehouse->city }}',
    state: '{{ $warehouse->state }}',
    country: '{{ $warehouse->country }}',
    postal_code: '{{ $warehouse->postal_code }}',
    notes: '{{ $warehouse->notes }}',
    is_active: '{{ $warehouse->is_active }}',
    is_default: '{{ $warehouse->is_default ? 1 : 0 }}',
    priority: '{{ $warehouse->priority }}'
};

let hasChanges = false;

$(document).ready(function() {
    // Initialize
    updatePreview();

    // Character counters
    $('#address').on('input', function() {
        $('#addressCount').text($(this).val().length);
    });

    $('#notes').on('input', function() {
        $('#notesCount').text($(this).val().length);
    });

    // Priority slider
    $('#priority').on('input', function() {
        const value = $(this).val();
        $('#priorityValue').text(value);
        updatePreview();
    });

    // Form field changes - detect and update preview
    $('#name, #code, #email, #phone, #address, #city, #state, #country, #postal_code, #notes, #is_active, #is_default, #priority').on('input change', function() {
        detectChanges();
        updatePreview();
    });

    // Default warehouse checkbox
    $('#is_default').on('change', function() {
        if ($(this).is(':checked')) {
            $('#defaultWarehouseNotice').slideDown();
        } else {
            $('#defaultWarehouseNotice').slideUp();
        }
    });

    // Warn before leaving if changes exist
    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Form submission
    $('#editWarehouseForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });
});

// Detect changes
function detectChanges() {
    hasChanges = false;

    // Check each field
    $.each(originalValues, function(field, originalValue) {
        let currentValue = '';

        if (field === 'is_default') {
            currentValue = $('#' + field).is(':checked') ? '1' : '0';
        } else {
            currentValue = $('#' + field).val() || '';
        }

        if (String(currentValue) !== String(originalValue)) {
            hasChanges = true;
            return false; // Break loop
        }
    });

    // Show/hide changes indicator
    if (hasChanges) {
        $('#changesIndicator').addClass('show');
    } else {
        $('#changesIndicator').removeClass('show');
    }
}

// Update preview
function updatePreview() {
    // Name
    const name = $('#name').val().trim();
    $('#previewName').text(name || 'Not entered');

    // Code
    const code = $('#code').val().trim();
    $('#previewCode').text(code || 'Auto-generated');

    // Location
    const city = $('#city').val().trim();
    const state = $('#state').val().trim();
    const country = $('#country').val().trim();

    let location = '';
    if (city) location += city;
    if (state) location += (location ? ', ' : '') + state;
    if (country) location += (location ? ', ' : '') + country;

    $('#previewLocation').text(location || 'Not set');

    // Status
    const isActive = $('#is_active').val() === '1';
    $('#previewStatus').html(
        isActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'
    );

    // Default
    const isDefault = $('#is_default').is(':checked');
    $('#previewDefault').html(
        isDefault ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>'
    );

    // Priority
    const priority = $('#priority').val();
    $('#previewPriority').html(`<span class="badge bg-info">${priority}</span>`);
}

// Submit form
function submitForm() {
    const formData = $('#editWarehouseForm').serialize();

    $.ajax({
        url: '{{ route("admin.warehouses.update", $warehouse->id) }}',
        type: 'PUT',
        data: formData,
        beforeSend: function() {
            Swal.fire({
                title: 'Updating Warehouse...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                hasChanges = false; // Reset change detection

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Warehouse updated successfully',
                    confirmButtonColor: '#5B914C'
                }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.href = '{{ route("admin.warehouses.show", $warehouse->id) }}';
                    }
                });
            }
        },
        error: function(xhr) {
            Swal.close();

            if (xhr.status === 422) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                let errorHtml = '<ul class="text-start">';

                $.each(errors, function(field, messages) {
                    $.each(messages, function(index, message) {
                        errorHtml += `<li>${message}</li>`;
                    });
                });

                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorHtml
                });

                // Highlight error fields
                $.each(errors, function(field, messages) {
                    const input = $(`[name="${field}"]`);
                    input.addClass('is-invalid');

                    // Remove error class on input
                    input.on('input change', function() {
                        $(this).removeClass('is-invalid');
                    });
                });

            } else {
                // Other errors
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to update warehouse. Please try again.'
                });
            }
        }
    });
}

// Reset form
function resetForm() {
    if (!hasChanges) {
        Swal.fire({
            icon: 'info',
            title: 'No Changes',
            text: 'There are no changes to reset',
            confirmButtonColor: '#5B914C'
        });
        return;
    }

    Swal.fire({
        title: 'Reset Changes?',
        text: 'All modifications will be discarded',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reset!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset all fields to original values
            $.each(originalValues, function(field, value) {
                if (field === 'is_default') {
                    $('#' + field).prop('checked', value == '1');
                } else {
                    $('#' + field).val(value);
                }
            });

            // Reset counters and displays
            $('#addressCount').text(originalValues.address.length);
            $('#notesCount').text(originalValues.notes.length);
            $('#priorityValue').text(originalValues.priority);

            // Reset default notice
            if (originalValues.is_default == '1') {
                $('#defaultWarehouseNotice').show();
            } else {
                $('#defaultWarehouseNotice').hide();
            }

            // Clear validation errors
            $('.is-invalid').removeClass('is-invalid');

            // Reset change detection
            hasChanges = false;
            $('#changesIndicator').removeClass('show');

            updatePreview();

            Swal.fire({
                icon: 'success',
                title: 'Reset Complete!',
                text: 'Form has been reset to original values',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush
