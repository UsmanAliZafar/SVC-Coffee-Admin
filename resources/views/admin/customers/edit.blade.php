@extends('admin.layouts.app')

@section('title', 'Edit Customer - ' . $customer->getFullName())

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
    }
    .password-wrapper {
        position: relative;
    }
    .customer-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.5rem;
        color: white;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Customer</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.show', $customer->id) }}">{{ $customer->getFullName() }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    {{-- Main Form --}}
    <form id="customerForm" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-8">

                {{-- Customer Info Alert --}}
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <div class="customer-avatar me-3">
                            {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-1">Editing: {{ $customer->getFullName() }}</h5>
                            <small>Customer ID: {{ $customer->id }}</small> |
                            <small>Member since: {{ $customer->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                </div>

                {{-- Basic Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-person text-primary"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label required-field">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="{{ old('first_name', $customer->first_name) }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label required-field">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="{{ old('last_name', $customer->last_name) }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label required-field">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="{{ old('email', $customer->email) }}" required>
                                </div>
                                @if($customer->email_verified_at)
                                <small class="text-success"><i class="bi bi-check-circle-fill"></i> Email verified on {{ $customer->email_verified_at->format('M d, Y') }}</small>
                                @else
                                <small class="text-warning"><i class="bi bi-exclamation-circle-fill"></i> Email not verified</small>
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                           value="{{ old('phone', $customer->phone) }}">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                   value="{{ old('company_name', $customer->company_name) }}">
                            <small class="text-muted">Optional - for business customers</small>
                        </div>
                    </div>
                </div>

                {{-- Change Password (Optional) --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-shield-lock text-success"></i> Change Password (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> Leave password fields empty if you don't want to change the password.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" minlength="8">
                                    <i class="bi bi-eye password-toggle" data-target="password"></i>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8">
                                    <i class="bi bi-eye password-toggle" data-target="password_confirmation"></i>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Address --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-geo-alt text-warning"></i> Billing Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="billing_address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="billing_address_line1" name="billing_address_line1"
                                   value="{{ old('billing_address_line1', $customer->billing_address_line1) }}"
                                   placeholder="Street address, P.O. box">
                        </div>

                        <div class="mb-3">
                            <label for="billing_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="billing_address_line2" name="billing_address_line2"
                                   value="{{ old('billing_address_line2', $customer->billing_address_line2) }}"
                                   placeholder="Apartment, suite, unit, building, floor, etc.">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="billing_city" name="billing_city"
                                       value="{{ old('billing_city', $customer->billing_city) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_state" class="form-label">State / Province</label>
                                <input type="text" class="form-control" id="billing_state" name="billing_state"
                                       value="{{ old('billing_state', $customer->billing_state) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="billing_postal_code" name="billing_postal_code"
                                       value="{{ old('billing_postal_code', $customer->billing_postal_code) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_country" class="form-label">Country</label>
                                <select class="form-select" id="billing_country" name="billing_country">
                                    <option value="">Select Country</option>
                                    <option value="United States" {{ old('billing_country', $customer->billing_country) == 'United States' ? 'selected' : '' }}>United States</option>
                                    <option value="Canada" {{ old('billing_country', $customer->billing_country) == 'Canada' ? 'selected' : '' }}>Canada</option>
                                    <option value="United Kingdom" {{ old('billing_country', $customer->billing_country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Australia" {{ old('billing_country', $customer->billing_country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                    <option value="Pakistan" {{ old('billing_country', $customer->billing_country) == 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                                    <option value="India" {{ old('billing_country', $customer->billing_country) == 'India' ? 'selected' : '' }}>India</option>
                                    <option value="Other" {{ old('billing_country', $customer->billing_country) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Additional Notes --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-journal-text text-info"></i> Internal Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                      placeholder="Add any internal notes about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                            <small class="text-muted">These notes are only visible to admins and will not be shown to the customer.</small>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">

                {{-- Customer Settings --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-gear text-secondary"></i> Customer Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customer_type" class="form-label required-field">Customer Type</label>
                            <select class="form-select" id="customer_type" name="customer_type" required>
                                <option value="">Select Type</option>
                                <option value="individual" {{ old('customer_type', $customer->customer_type) == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ old('customer_type', $customer->customer_type) == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="wholesale" {{ old('customer_type', $customer->customer_type) == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                                <option value="vip" {{ old('customer_type', $customer->customer_type) == 'vip' ? 'selected' : '' }}>VIP</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="status_key_code" class="form-label required-field">Status</label>
                            <select class="form-select" id="status_key_code" name="status_key_code" required>
                                @if(isset($statusList) && $statusList->count() > 0)
                                    @foreach($statusList as $status)
                                        <option value="{{ $status->key_code }}"
                                                {{ old('status_key_code', $customer->status_key_code) == $status->key_code ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="CUSTOMER_ACTIVE" {{ old('status_key_code', $customer->status_key_code) == 'CUSTOMER_ACTIVE' ? 'selected' : '' }}>Active</option>
                                    <option value="CUSTOMER_INACTIVE" {{ old('status_key_code', $customer->status_key_code) == 'CUSTOMER_INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                    <option value="CUSTOMER_BLOCKED" {{ old('status_key_code', $customer->status_key_code) == 'CUSTOMER_BLOCKED' ? 'selected' : '' }}>Blocked</option>
                                @endif
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_newsletter_subscribed"
                                       name="is_newsletter_subscribed" value="1"
                                       {{ old('is_newsletter_subscribed', $customer->is_newsletter_subscribed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_newsletter_subscribed">
                                    <i class="bi bi-envelope-check"></i> Subscribe to Newsletter
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Customer will receive marketing emails</small>
                        </div>

                        <div class="mb-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_sms_subscribed"
                                       name="is_sms_subscribed" value="1"
                                       {{ old('is_sms_subscribed', $customer->is_sms_subscribed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_sms_subscribed">
                                    <i class="bi bi-phone-vibrate"></i> Subscribe to SMS
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Customer will receive SMS notifications</small>
                        </div>
                    </div>
                </div>

                {{-- Customer Statistics --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-graph-up text-success"></i> Customer Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Total Orders</small>
                            <div class="h5 mb-0">{{ number_format($customer->total_orders) }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Total Spent</small>
                            <div class="h5 mb-0">${{ number_format($customer->total_spent, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Average Order Value</small>
                            <div class="h5 mb-0">${{ number_format($customer->average_order_value, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Customer Segment</small>
                            <div>{!! $customer->getSegment() !!}</div>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted">Last Login</small>
                            <div>
                                @if($customer->last_login_at)
                                    {{ $customer->last_login_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Update Customer
                            </button>
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>

                        <hr>

                        <div class="text-muted small">
                            <i class="bi bi-info-circle"></i> <strong>Last Updated:</strong><br>
                            {{ $customer->updated_at->format('M d, Y h:i A') }}<br>
                            <small>({{ $customer->updated_at->diffForHumans() }})</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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

    // Customer type change - show/hide company name
    $('#customer_type').on('change', function() {
        const type = $(this).val();
        const companyField = $('#company_name').closest('.mb-3');

        if (type === 'business' || type === 'wholesale') {
            companyField.find('label').html('Company Name <span class="text-danger">*</span>');
            $('#company_name').prop('required', true);
        } else {
            companyField.find('label').text('Company Name');
            $('#company_name').prop('required', false);
        }
    });

    // Trigger on page load
    $('#customer_type').trigger('change');

    // Form submission
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Check if passwords match (if provided)
        const password = $('#password').val();
        const passwordConfirm = $('#password_confirmation').val();

        if (password || passwordConfirm) {
            if (password !== passwordConfirm) {
                $('#password_confirmation').addClass('is-invalid');
                $('#password_confirmation').siblings('.invalid-feedback').text('Passwords do not match');
                return false;
            }
        }

        // Disable submit button
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Updating...');

        // Get form data
        const formData = new FormData(this);

        // AJAX request
        $.ajax({
            url: '{{ route("admin.customers.update", $customer->id) }}',
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
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = '{{ route("admin.customers.show", $customer->id) }}';
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
                        input.siblings('.invalid-feedback').text(errors[key][0]);

                        // For inputs inside input-group
                        if (input.parent().hasClass('input-group')) {
                            input.addClass('is-invalid');
                            input.parent().after('<div class="invalid-feedback d-block">' + errors[key][0] + '</div>');
                        }
                    });

                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid:first').offset().top - 100
                    }, 500);

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form and fix all errors.'
                    });
                } else {
                    // Other errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to update customer. Please try again.'
                    });
                }
            }
        });
    });

    // Real-time password confirmation check
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();

        if (password && confirmation && password !== confirmation) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Passwords do not match');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        }
    });

    // Email format validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            $(this).parent().siblings('.invalid-feedback').text('Please enter a valid email address');
        } else {
            $(this).removeClass('is-invalid');
            $(this).parent().siblings('.invalid-feedback').text('');
        }
    });

    // Phone format hint
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');

        // Optional: Format phone number as (XXX) XXX-XXXX
        if (value.length >= 6) {
            value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
        } else if (value.length >= 3) {
            value = '(' + value.substring(0, 3) + ') ' + value.substring(3);
        }

        $(this).val(value);
    });

    // Warn if leaving with unsaved changes
    let formChanged = false;
    $('#customerForm input, #customerForm select, #customerForm textarea').on('change', function() {
        formChanged = true;
    });

    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    $('#customerForm').on('submit', function() {
        formChanged = false; // Don't warn on form submit
    });
});
</script>
@endpush
