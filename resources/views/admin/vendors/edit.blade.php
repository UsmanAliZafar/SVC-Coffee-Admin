{{-- resources/views/admin/vendors/edit.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Vendor</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                    <li class="breadcrumb-item active">Edit: {{ $vendor->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form id="editVendorForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Information Card -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #5B914C; color: white;">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $vendor->name) }}" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Company Name -->
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name', $vendor->company_name) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $vendor->email) }}" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Mobile -->
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $vendor->mobile) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Website -->
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" value="{{ old('website', $vendor->website) }}" placeholder="https://example.com">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $vendor->description) }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information Card -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #5B914C; color: white;">
                        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Address -->
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Street Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $vendor->address) }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- City -->
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $vendor->city) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- State -->
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $vendor->state) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Country -->
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ old('country', $vendor->country) == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Postal Code -->
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $vendor->postal_code) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Information Card -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #5B914C; color: white;">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Business Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Tax Number -->
                            <div class="col-md-6 mb-3">
                                <label for="tax_number" class="form-label">Tax Number</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" value="{{ old('tax_number', $vendor->tax_number) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Registration Number -->
                            <div class="col-md-6 mb-3">
                                <label for="registration_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" id="registration_number" name="registration_number" value="{{ old('registration_number', $vendor->registration_number) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banking Information Card -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #5B914C; color: white;">
                        <h5 class="mb-0"><i class="bi bi-bank"></i> Banking Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Bank Name -->
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Bank Account Name -->
                            <div class="col-md-6 mb-3">
                                <label for="bank_account_name" class="form-label">Account Name</label>
                                <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $vendor->bank_account_name) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Bank Account Number -->
                            <div class="col-md-6 mb-3">
                                <label for="bank_account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $vendor->bank_account_number) }}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Bank Routing Number -->
                            <div class="col-md-6 mb-3">
                                <label for="bank_routing_number" class="form-label">Routing Number</label>
                                <input type="text" class="form-control" id="bank_routing_number" name="bank_routing_number" value="{{ old('bank_routing_number', $vendor->bank_routing_number) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #5B914C; color: white;">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Settings</h5>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status_key_code" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status_key_code" name="status_key_code" required>
                                @foreach($statusList as $status)
                                    <option value="{{ $status->key_code }}"
                                        {{ old('status_key_code', $vendor->status_key_code) == $status->key_code ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Currency -->
                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                @foreach (get_all_currencies() as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency', $vendor->currency) == $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistics</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="syncStatsBtn" title="Sync Statistics">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Products Count:</span>
                            <span class="badge bg-primary" id="productsCount">{{ $vendor->products_count ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Total Purchases:</span>
                            <span class="badge bg-success" id="totalPurchases">${{ number_format($vendor->total_purchases ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Orders Count:</span>
                            <span class="badge bg-info" id="ordersCount">{{ $vendor->orders_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Audit Information Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Audit Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block">Created At:</small>
                            <strong>{{ $vendor->created_at->format('d M Y, h:i A') }}</strong>
                        </div>
                        @if($vendor->creator)
                        <div class="mb-2">
                            <small class="text-muted d-block">Created By:</small>
                            <strong>{{ $vendor->creator->name }}</strong>
                        </div>
                        @endif
                        <div class="mb-2">
                            <small class="text-muted d-block">Last Updated:</small>
                            <strong>{{ $vendor->updated_at->format('d M Y, h:i A') }}</strong>
                        </div>
                        @if($vendor->updater)
                        <div>
                            <small class="text-muted d-block">Updated By:</small>
                            <strong>{{ $vendor->updater->name }}</strong>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn w-100 mb-2" style="background-color: #5B914C; color: white;">
                            <i class="bi bi-check-circle"></i> Update Vendor
                        </button>
                        <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-info w-100 mb-2">
                            <i class="bi bi-eye"></i> View Vendor
                        </a>
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-light w-100">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .card-header {
        font-weight: 600;
    }
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    .invalid-feedback {
        display: block;
    }
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    #syncStatsBtn.syncing i {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle form submission
    $('#editVendorForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Get form data
        const formData = new FormData(this);

        // Disable submit button
        const $submitBtn = $('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

        // Submit via AJAX
        $.ajax({
            url: '{{ route("admin.vendors.update", $vendor->id) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = '{{ route("admin.vendors.show", $vendor->id) }}';
                    });
                }
            },
            error: function(xhr) {
                // Re-enable submit button
                $submitBtn.prop('disabled', false).html(originalText);

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    $.each(errors, function(field, messages) {
                        const $input = $(`[name="${field}"]`);
                        $input.addClass('is-invalid');
                        $input.next('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form and correct the errors.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to update vendor. Please try again.',
                    });
                }
            }
        });
    });

    // Clear error on input change
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').text('');
    });

    // Sync Statistics Button
    $('#syncStatsBtn').on('click', function() {
        const $btn = $(this);

        $btn.prop('disabled', true).addClass('syncing');

        $.ajax({
            url: '{{ route("admin.vendors.sync-individual", $vendor->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update statistics display
                    $('#productsCount').text(response.data.products_count);
                    $('#totalPurchases').text('$' + response.data.total_purchases);

                    Swal.fire({
                        icon: 'success',
                        title: 'Statistics Synced!',
                        html: `
                            <p>Products: <strong>${response.data.products_count}</strong></p>
                            <p>Total Purchases: <strong>$${response.data.total_purchases}</strong></p>
                        `,
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to sync statistics.',
                    timer: 2000
                });
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('syncing');
            }
        });
    });
});
</script>
@endpush
@endsection
