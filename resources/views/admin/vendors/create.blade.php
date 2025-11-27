@extends('admin.layouts.app')

@section('title', 'Create Vendor')

@push('styles')
<style>
    .form-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }

    .btn-submit {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }

    .btn-submit:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }

    .image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
        border-radius: 8px;
        display: none;
    }

    .required-field::after {
        content: " *";
        color: red;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Create New Vendor</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form id="vendorForm" enctype="multipart/form-data">
        @csrf

        <!-- Basic Information -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Contact Person Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control">
                    <small class="text-muted">Leave blank to auto-generate from name</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://example.com">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-geo-alt"></i> Address Information</h5>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">State/Province</label>
                    <input type="text" name="state" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Country</label>
                    <select name="country" class="form-select">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" class="form-control">
                </div>
            </div>
        </div>

        <!-- Business Information -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-briefcase"></i> Business Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tax Number</label>
                    <input type="text" name="tax_number" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Payment Terms</label>
                    <input type="text" name="payment_terms" class="form-control" placeholder="e.g., Net 30 days">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Credit Limit</label>
                    <input type="number" name="credit_limit" class="form-control" min="0" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                    <select class="form-select" id="currency" name="currency" required>
                        <option value="">Select Currency</option>
                        @foreach($currencies as $code => $currency)
                            <option value="{{ $code }}" {{ old('currency') == $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $currency }}
                            </option>
                        @endforeach
                    </select>
                    @error('currency')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-bank"></i> Bank Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="bank_account_name" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_number" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Routing Number</label>
                    <input type="text" name="bank_routing_number" class="form-control">
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-images"></i> Images</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*" id="logoInput">
                    <small class="text-muted">Recommended: 200x200px</small>
                    <img id="logoPreview" class="image-preview">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Banner Image</label>
                    <input type="file" name="banner_image" class="form-control" accept="image/*" id="bannerInput">
                    <small class="text-muted">Recommended: 1200x400px</small>
                    <img id="bannerPreview" class="image-preview">
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-share"></i> Social Media Links</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-facebook"></i> Facebook</label>
                    <input type="url" name="facebook" class="form-control" placeholder="https://facebook.com/...">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-twitter"></i> Twitter</label>
                    <input type="url" name="twitter" class="form-control" placeholder="https://twitter.com/...">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-instagram"></i> Instagram</label>
                    <input type="url" name="instagram" class="form-control" placeholder="https://instagram.com/...">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-linkedin"></i> LinkedIn</label>
                    <input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/...">
                </div>
            </div>
        </div>

        <!-- Status & Settings -->
        <div class="form-section">
            <h5 class="section-title"><i class="bi bi-gear"></i> Status & Settings</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Status</label>
                    <select name="status_key_code" class="form-select" required>
                        @foreach($statusList as $status)
                            <option value="{{ $status->key_code }}" {{ $status->key_code === 'VENDOR_ACTIVE' ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_verified" id="isVerified">
                        <label class="form-check-label" for="isVerified">
                            <i class="bi bi-check-circle"></i> Mark as Verified
                        </label>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured">
                        <label class="form-check-label" for="isFeatured">
                            <i class="bi bi-star"></i> Mark as Featured
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-submit">
                    <i class="bi bi-check-circle"></i> Create Vendor
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Image preview
    $('#logoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#bannerInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#bannerPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    $('#vendorForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: '{{ route("admin.vendors.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Creating Vendor...',
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
                        text: response.message,
                        confirmButtonColor: '#5B914C'
                    }).then(() => {
                        window.location.href = '{{ route("admin.vendors.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to create vendor';

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage
                });
            }
        });
    });
});
</script>
@endpush
