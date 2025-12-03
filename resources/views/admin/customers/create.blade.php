@extends('admin.layouts.app')

@section('title', 'Add New Customer')

@push('styles')
<style>
    .form-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #5B914C;
    }
    .form-section-title {
        font-weight: 600;
        color: #5B914C;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }
    .required-field::after {
        content: " *";
        color: #dc3545;
    }
    .password-toggle {
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
    }
    .password-wrapper {
        position: relative;
    }
    .card-header-custom {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }
    .copy-address-btn {
        font-size: 0.875rem;
    }
    .section-icon {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-right: 10px;
    }
    .info-card {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-person-plus-fill text-primary"></i> Add New Customer
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.customers.index') }}">Customers</a>
                    </li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Main Form --}}
    <form id="customerForm" method="POST">
        @csrf
        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-8">

                {{-- Basic Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <span class="section-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label required-field">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="John">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label required-field">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Doe">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label required-field">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="john.doe@example.com">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 (555) 123-4567">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="company_name" class="form-label">Company Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Company Ltd.">
                            </div>
                            <small class="text-muted">Optional - Required for business/wholesale customers</small>
                        </div>
                    </div>
                </div>

                {{-- Account Security --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <span class="section-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                            Account Security
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="Minimum 8 characters">
                                    <i class="bi bi-eye password-toggle" data-target="password"></i>
                                </div>
                                <small class="text-muted">Leave blank to auto-generate</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" placeholder="Re-type password">
                                    <i class="bi bi-eye password-toggle" data-target="password_confirmation"></i>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Password Requirements:</strong> Minimum 8 characters. Leave blank to auto-generate a secure password.
                        </div>
                    </div>
                </div>

                {{-- Billing Address --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="section-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-credit-card-fill"></i>
                                </span>
                                Billing Address
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="billing_address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="billing_address_line1" name="billing_address_line1" placeholder="Street address, P.O. box">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="billing_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="billing_address_line2" name="billing_address_line2" placeholder="Apartment, suite, unit, building, floor, etc.">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="billing_city" name="billing_city" placeholder="New York">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_state" class="form-label">State / Province</label>
                                <input type="text" class="form-control" id="billing_state" name="billing_state" placeholder="NY">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="billing_postal_code" name="billing_postal_code" placeholder="10001">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label for="billing_country" class="form-label">Country</label>
                                <select class="form-select" id="billing_country" name="billing_country">
                                    <option value="">Select Country</option>
                                    <option value="United States">United States</option>
                                    <option value="Canada">Canada</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Pakistan" selected>Pakistan</option>
                                    <option value="India">India</option>
                                    <option value="China">China</option>
                                    <option value="Germany">Germany</option>
                                    <option value="France">France</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="section-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-truck"></i>
                                </span>
                                Shipping Address
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-primary copy-address-btn" id="copyBillingBtn">
                                <i class="bi bi-files"></i> Copy from Billing
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="shipping_address_line1" name="shipping_address_line1" placeholder="Street address, P.O. box">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="shipping_address_line2" name="shipping_address_line2" placeholder="Apartment, suite, unit, building, floor, etc.">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" placeholder="New York">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_state" class="form-label">State / Province</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state" placeholder="NY">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" placeholder="10001">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label for="shipping_country" class="form-label">Country</label>
                                <select class="form-select" id="shipping_country" name="shipping_country">
                                    <option value="">Select Country</option>
                                    <option value="United States">United States</option>
                                    <option value="Canada">Canada</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Pakistan" selected>Pakistan</option>
                                    <option value="India">India</option>
                                    <option value="China">China</option>
                                    <option value="Germany">Germany</option>
                                    <option value="France">France</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Information (Show for business/wholesale) --}}
                <div class="card border-0 shadow-sm mb-4" id="businessInfoCard" style="display: none;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <span class="section-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-briefcase-fill"></i>
                            </span>
                            Business Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tax_id" class="form-label">Tax ID / EIN</label>
                                <input type="text" class="form-control" id="tax_id" name="tax_id" placeholder="XX-XXXXXXX">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="vat_number" class="form-label">VAT Number</label>
                                <input type="text" class="form-control" id="vat_number" name="vat_number" placeholder="GB123456789">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="business_registration" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" id="business_registration" name="business_registration" placeholder="12345678">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Business registration details for tax and legal purposes
                        </small>
                    </div>
                </div>

                {{-- Preferences --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <span class="section-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-sliders"></i>
                            </span>
                            Preferences
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="preferred_language" class="form-label">Preferred Language</label>
                                <select class="form-select" id="preferred_language" name="preferred_language">
                                    <option value="">Select Language</option>
                                    <option value="en" selected>English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="ur">Urdu</option>
                                    <option value="ar">Arabic</option>
                                    <option value="zh">Chinese</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preferred_currency" class="form-label">Preferred Currency</label>
                                <select class="form-select" id="preferred_currency" name="preferred_currency">
                                    <option value="">Use Store Default</option>
                                    <option value="USD" selected>USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="PKR">PKR - Pakistani Rupee</option>
                                    <option value="INR">INR - Indian Rupee</option>
                                    <option value="AUD">AUD - Australian Dollar</option>
                                    <option value="CAD">CAD - Canadian Dollar</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="acquisition_source" class="form-label">Acquisition Source</label>
                            <select class="form-select" id="acquisition_source" name="acquisition_source">
                                <option value="">How did they find us?</option>
                                <option value="Website">Website</option>
                                <option value="Social Media">Social Media</option>
                                <option value="Referral">Referral</option>
                                <option value="Advertisement">Advertisement</option>
                                <option value="Search Engine">Search Engine</option>
                                <option value="Direct">Direct</option>
                                <option value="Other">Other</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                {{-- Additional Notes --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <span class="section-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-journal-text"></i>
                            </span>
                            Additional Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Internal Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any internal notes about this customer..."></textarea>
                            <small class="text-muted">
                                <i class="bi bi-lock-fill"></i> These notes are only visible to admins and will not be shown to the customer.
                            </small>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">

                {{-- Customer Settings --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-gear-fill text-secondary"></i> Customer Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customer_type" class="form-label required-field">Customer Type</label>
                            <select class="form-select" id="customer_type" name="customer_type" required>
                                <option value="">Select Type</option>
                                <option value="individual" selected>Individual</option>
                                <option value="business">Business</option>
                                <option value="wholesale">Wholesale</option>
                                <option value="vip">VIP</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="status_key_code" class="form-label required-field">Status</label>
                            <select class="form-select" id="status_key_code" name="status_key_code" required>
                                @if(isset($statusList) && $statusList->count() > 0)
                                    @foreach($statusList as $status)
                                        <option value="{{ $status->key_code }}" {{ $status->key_code == 'CUSTOMER_ACTIVE' ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="CUSTOMER_ACTIVE" selected>Active</option>
                                    <option value="CUSTOMER_INACTIVE">Inactive</option>
                                    <option value="CUSTOMER_BLOCKED">Blocked</option>
                                @endif
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1">
                                <label class="form-check-label" for="is_verified">
                                    <i class="bi bi-patch-check-fill text-success"></i> Email Verified
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Mark email as verified</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_newsletter_subscribed" name="is_newsletter_subscribed" value="1" checked>
                                <label class="form-check-label" for="is_newsletter_subscribed">
                                    <i class="bi bi-envelope-check-fill text-primary"></i> Subscribe to Newsletter
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Receive marketing emails</small>
                        </div>

                        <div class="mb-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_sms_subscribed" name="is_sms_subscribed" value="1">
                                <label class="form-check-label" for="is_sms_subscribed">
                                    <i class="bi bi-phone-vibrate-fill text-info"></i> Subscribe to SMS
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Receive SMS notifications</small>
                        </div>
                    </div>
                </div>

                {{-- Quick Info --}}
                <div class="card border-0 shadow-sm mb-4 info-card">
                    <div class="card-body text-white">
                        <h6 class="card-title mb-3 fw-bold">
                            <i class="bi bi-lightbulb-fill"></i> Quick Tips
                        </h6>
                        <ul class="mb-0 ps-3" style="font-size: 0.9rem; line-height: 1.8;">
                            <li class="mb-2">✓ Email must be unique</li>
                            <li class="mb-2">✓ Password auto-generated if blank</li>
                            <li class="mb-2">✓ Business customers need company name</li>
                            <li class="mb-2">✓ Copy billing to shipping for same address</li>
                            <li class="mb-2">✓ Active status allows immediate login</li>
                            <li class="mb-0">✓ Tags help organize customers</li>
                        </ul>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle-fill"></i> Create Customer
                            </button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let tagsArray = [];

    // Password toggle visibility
    $('.password-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const input = $(`#${targetId}`);
        const icon = $(this);

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Customer type change - show/hide business info and company name requirement
    $('#customer_type').on('change', function() {
        const type = $(this).val();
        const companyField = $('#company_name').closest('.mb-0');
        const businessCard = $('#businessInfoCard');

        if (type === 'business' || type === 'wholesale') {
            companyField.find('label').html('Company Name <span class="text-danger">*</span>');
            $('#company_name').prop('required', true);
            businessCard.slideDown();
        } else {
            companyField.find('label').html('Company Name');
            $('#company_name').prop('required', false);
            businessCard.slideUp();
        }
    });

    // Copy billing address to shipping address
    $('#copyBillingBtn').on('click', function() {
        $('#shipping_address_line1').val($('#billing_address_line1').val());
        $('#shipping_address_line2').val($('#billing_address_line2').val());
        $('#shipping_city').val($('#billing_city').val());
        $('#shipping_state').val($('#billing_state').val());
        $('#shipping_postal_code').val($('#billing_postal_code').val());
        $('#shipping_country').val($('#billing_country').val());

        // Show success toast
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Billing address copied to shipping',
            showConfirmButton: false,
            timer: 2000
        });
    });


    // Form submission
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('.d-block.invalid-feedback').remove();

        // Disable submit button
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        // Get form data
        const formData = new FormData(this);

        // AJAX request
        $.ajax({
            url: '{{ route("admin.customers.store") }}',
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
                        showConfirmButton: true,
                        confirmButtonColor: '#5B914C'
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else if (response.customer) {
                            window.location.href = "{{ route('admin.customers.show', ['id' => '__id__']) }}".replace('__id__', response.customer.id);
                        } else {
                            window.location.href = '{{ route("admin.customers.index") }}';
                        }
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    Object.keys(errors).forEach(function(key) {
                        const input = $(`[name="${key}"]`);
                        input.addClass('is-invalid');

                        // For inputs inside input-group
                        if (input.parent().hasClass('input-group')) {
                            input.parent().after('<div class="invalid-feedback d-block">' + errors[key][0] + '</div>');
                        } else {
                            input.siblings('.invalid-feedback').text(errors[key][0]);
                        }
                    });

                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid:first').offset().top - 100
                    }, 500);

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form and fix all errors.',
                        confirmButtonColor: '#5B914C'
                    });
                } else {
                    // Other errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to create customer. Please try again.',
                        confirmButtonColor: '#5B914C'
                    });
                }
            }
        });
    });

    // Real-time password confirmation check
    $('#password, #password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();

        if (password && confirmation && password !== confirmation) {
            $('#password_confirmation').addClass('is-invalid');
            $('#password_confirmation').siblings('.invalid-feedback').text('Passwords do not match');
        } else {
            $('#password_confirmation').removeClass('is-invalid');
            $('#password_confirmation').siblings('.invalid-feedback').text('');
        }
    });

    // Email format validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            $(this).parent().after('<div class="invalid-feedback d-block">Please enter a valid email address</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).parent().next('.invalid-feedback.d-block').remove();
        }
    });

    // Phone format (optional formatting)
    $('#phone').on('input', function() {
        let value = $(this).val();
        // Basic phone number formatting - customize as needed
        // This is just a simple example, adjust based on your requirements
    });
});
</script>
@endpush
