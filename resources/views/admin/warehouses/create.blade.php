@extends('admin.layouts.app')
{{-- resources/views/admin/warehouses/create.blade.php --}}
@section('title', 'Create Warehouse')

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
        background: #f8f9fa;
        border-left: 4px solid #5B914C;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .info-box i {
        color: #5B914C;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-building-add"></i> Create New Warehouse</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="bi bi-info-circle"></i>
        <strong>Note:</strong> Create a new warehouse location to manage inventory across multiple facilities. All fields marked with <span class="text-danger">*</span> are required.
    </div>

    <!-- Form -->
    <form id="createWarehouseForm">
        @csrf

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
                                   placeholder="e.g., Main Distribution Center" required maxlength="255">
                            <small class="text-muted">Enter a descriptive name for this warehouse</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Warehouse Code</label>
                            <input type="text" name="code" id="code" class="form-control"
                                   placeholder="e.g., WH-001" maxlength="50">
                            <small class="text-muted">Auto-generated if empty</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control"
                                       placeholder="warehouse@example.com" maxlength="255">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="phone" id="phone" class="form-control"
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
                                  placeholder="Street address, building number, etc." maxlength="500"></textarea>
                        <span class="char-counter">
                            <span id="addressCount">0</span>/500 characters
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="city" class="form-control"
                                   placeholder="e.g., New York" maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State/Province</label>
                            <input type="text" name="state" id="state" class="form-control"
                                   placeholder="e.g., NY" maxlength="100">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="country" class="form-control"
                                   placeholder="e.g., United States" maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postal/ZIP Code</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control"
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
                                  placeholder="Any additional information about this warehouse (operating hours, special instructions, etc.)"></textarea>
                        <span class="char-counter">
                            <span id="notesCount">0</span> characters
                        </span>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Settings -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-gear"></i> Settings
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <small class="text-muted">Set warehouse operational status</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_default"
                                   id="is_default" value="1">
                            <label class="form-check-label" for="is_default">
                                <strong>Set as Default Warehouse</strong>
                            </label>
                        </div>
                        <small class="text-muted">This will be the primary warehouse for all operations</small>

                        <div id="defaultWarehouseNotice" class="default-warehouse-notice mt-2" style="display: none;">
                            <i class="bi bi-exclamation-triangle"></i>
                            <small><strong>Notice:</strong> Setting this as default will remove the default status from any existing default warehouse.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Priority Level</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="priority" id="priority"
                                   class="priority-slider" min="0" max="100" value="50" step="1">
                            <span class="priority-value" id="priorityValue">50</span>
                        </div>
                        <small class="text-muted">Higher priority warehouses are used first for order fulfillment</small>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="form-section" style="background: linear-gradient(135deg, #f0f7ed 0%, #e8f5e0 100%); border-color: #5B914C;">
                    <h5 class="section-title" style="border-color: #5B914C;">
                        <i class="bi bi-check-circle"></i> Quick Summary
                    </h5>

                    <div class="mb-2">
                        <strong>Warehouse Name:</strong>
                        <div id="summaryName" class="text-muted">Not entered</div>
                    </div>

                    <div class="mb-2">
                        <strong>Code:</strong>
                        <div id="summaryCode" class="text-muted">Auto-generated</div>
                    </div>

                    <div class="mb-2">
                        <strong>Location:</strong>
                        <div id="summaryLocation" class="text-muted">Not entered</div>
                    </div>

                    <div class="mb-2">
                        <strong>Status:</strong>
                        <div id="summaryStatus">
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <strong>Default:</strong>
                        <div id="summaryDefault">
                            <span class="badge bg-secondary">No</span>
                        </div>
                    </div>

                    <div>
                        <strong>Priority:</strong>
                        <div id="summaryPriority">
                            <span class="badge bg-info">50</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="resetForm()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-check-circle"></i> Create Warehouse
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize
    updateSummary();

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
        updateSummary();
    });

    // Form field changes - update summary
    $('#name, #code, #city, #state, #country, #is_active, #is_default').on('input change', function() {
        updateSummary();
    });

    // Default warehouse checkbox
    $('#is_default').on('change', function() {
        if ($(this).is(':checked')) {
            $('#defaultWarehouseNotice').slideDown();
        } else {
            $('#defaultWarehouseNotice').slideUp();
        }
    });

    // Form submission
    $('#createWarehouseForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });
});

// Update summary card
function updateSummary() {
    // Name
    const name = $('#name').val().trim();
    $('#summaryName').html(name || '<span class="text-muted">Not entered</span>');

    // Code
    const code = $('#code').val().trim();
    $('#summaryCode').html(code || '<span class="text-muted">Auto-generated</span>');

    // Location
    const city = $('#city').val().trim();
    const state = $('#state').val().trim();
    const country = $('#country').val().trim();

    let location = '';
    if (city) location += city;
    if (state) location += (location ? ', ' : '') + state;
    if (country) location += (location ? ', ' : '') + country;

    $('#summaryLocation').html(location || '<span class="text-muted">Not entered</span>');

    // Status
    const isActive = $('#is_active').val() === '1';
    $('#summaryStatus').html(
        isActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'
    );

    // Default
    const isDefault = $('#is_default').is(':checked');
    $('#summaryDefault').html(
        isDefault ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>'
    );

    // Priority
    const priority = $('#priority').val();
    $('#summaryPriority').html(`<span class="badge bg-info">${priority}</span>`);
}

// Submit form
function submitForm() {
    const formData = $('#createWarehouseForm').serialize();

    $.ajax({
        url: '{{ route("admin.warehouses.store") }}',
        type: 'POST',
        data: formData,
        beforeSend: function() {
            Swal.fire({
                title: 'Creating Warehouse...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Warehouse created successfully',
                    confirmButtonColor: '#5B914C'
                }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.href = '{{ route("admin.warehouses.index") }}';
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
                    text: xhr.responseJSON?.message || 'Failed to create warehouse. Please try again.'
                });
            }
        }
    });
}

// Reset form
function resetForm() {
    Swal.fire({
        title: 'Reset Form?',
        text: 'All entered data will be cleared',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reset it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#createWarehouseForm')[0].reset();
            $('#priority').val(50);
            $('#priorityValue').text(50);
            $('#addressCount').text(0);
            $('#notesCount').text(0);
            $('#defaultWarehouseNotice').hide();
            $('.is-invalid').removeClass('is-invalid');
            updateSummary();

            Swal.fire({
                icon: 'success',
                title: 'Reset!',
                text: 'Form has been reset',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush
