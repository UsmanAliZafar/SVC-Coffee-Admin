@extends('admin.layouts.app')

@section('title', 'Edit Customer')

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
    .customer-badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    .stat-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    .stat-box .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }
    .stat-box .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #5B914C;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-pencil-square text-warning"></i> Edit Customer
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
                    <li class="breadcrumb-item active">Edit: {{ $customer->getFullName() }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> View Profile
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Customer Info Banner --}}
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="avatar-circle" style="width: 64px; height: 64px; font-size: 1.5rem; background: rgba(255,255,255,0.2);">
                        {{ strtoupper(substr($customer->first_name, 0, 1)) }}
                    </div>
                </div>
                <div class="col">
                    <h4 class="mb-1 fw-bold">
                        {{ $customer->getFullName() }}
                        @if($customer->is_verified)
                            <i class="bi bi-patch-check-fill" title="Verified"></i>
                        @endif
                    </h4>
                    <p class="mb-0">
                        <i class="bi bi-envelope"></i> {{ $customer->email }}
                        @if($customer->phone)
                            <span class="mx-2">|</span>
                            <i class="bi bi-telephone"></i> {{ $customer->phone }}
                        @endif
                    </p>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2 flex-wrap">
                        {!! $customer->getTypeBadge() !!}
                        {!! $customer->getStatusBadge() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ $customer->total_orders }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-label">Total Spent</div>
                <div class="stat-value">{{ store_currency_symbol() }}{{ number_format($customer->total_spent, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-label">Avg Order Value</div>
                <div class="stat-value">{{ store_currency_symbol() }}{{ number_format($customer->average_order_value, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-label">Customer Since</div>
                <div class="stat-value" style="font-size: 1rem;">{{ $customer->created_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Main Form --}}
    <form id="customerForm" method="POST">
        @csrf
        @method('PUT')
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
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required placeholder="John">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label required-field">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required placeholder="Doe">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label required-field">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $customer->email) }}" required placeholder="john.doe@example.com">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="+1 (555) 123-4567">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="company_name" class="form-label">Company Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}" placeholder="Company Ltd.">
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
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Password Update:</strong> Leave password fields blank to keep the current password. Fill them to set a new password.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="Leave blank to keep current">
                                    <i class="bi bi-eye password-toggle" data-target="password"></i>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" placeholder="Re-type new password">
                                    <i class="bi bi-eye password-toggle" data-target="password_confirmation"></i>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
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
                            <input type="text" class="form-control" id="billing_address_line1" name="billing_address_line1" value="{{ old('billing_address_line1', $customer->billing_address_line1) }}" placeholder="Street address, P.O. box">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="billing_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="billing_address_line2" name="billing_address_line2" value="{{ old('billing_address_line2', $customer->billing_address_line2) }}" placeholder="Apartment, suite, unit, building, floor, etc.">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="billing_city" name="billing_city" value="{{ old('billing_city', $customer->billing_city) }}" placeholder="New York">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_state" class="form-label">State / Province</label>
                                <input type="text" class="form-control" id="billing_state" name="billing_state" value="{{ old('billing_state', $customer->billing_state) }}" placeholder="NY">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="billing_postal_code" name="billing_postal_code" value="{{ old('billing_postal_code', $customer->billing_postal_code) }}" placeholder="10001">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label for="billing_country" class="form-label">Country</label>
                                <select class="form-select" id="billing_country" name="billing_country">
                                    <option value="">Select Country</option>
                                    <option value="United States" {{ old('billing_country', $customer->billing_country) == 'United States' ? 'selected' : '' }}>United States</option>
                                    <option value="Canada" {{ old('billing_country', $customer->billing_country) == 'Canada' ? 'selected' : '' }}>Canada</option>
                                    <option value="United Kingdom" {{ old('billing_country', $customer->billing_country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Australia" {{ old('billing_country', $customer->billing_country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                    <option value="Pakistan" {{ old('billing_country', $customer->billing_country) == 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                                    <option value="India" {{ old('billing_country', $customer->billing_country) == 'India' ? 'selected' : '' }}>India</option>
                                    <option value="China" {{ old('billing_country', $customer->billing_country) == 'China' ? 'selected' : '' }}>China</option>
                                    <option value="Germany" {{ old('billing_country', $customer->billing_country) == 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="France" {{ old('billing_country', $customer->billing_country) == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="Japan" {{ old('billing_country', $customer->billing_country) == 'Japan' ? 'selected' : '' }}>Japan</option>
                                    <option value="Other" {{ old('billing_country', $customer->billing_country) == 'Other' ? 'selected' : '' }}>Other</option>
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
                            <input type="text" class="form-control" id="shipping_address_line1" name="shipping_address_line1" value="{{ old('shipping_address_line1', $customer->shipping_address_line1) }}" placeholder="Street address, P.O. box">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="shipping_address_line2" name="shipping_address_line2" value="{{ old('shipping_address_line2', $customer->shipping_address_line2) }}" placeholder="Apartment, suite, unit, building, floor, etc.">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="{{ old('shipping_city', $customer->shipping_city) }}" placeholder="New York">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_state" class="form-label">State / Province</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state" value="{{ old('shipping_state', $customer->shipping_state) }}" placeholder="NY">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" value="{{ old('shipping_postal_code', $customer->shipping_postal_code) }}" placeholder="10001">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label for="shipping_country" class="form-label">Country</label>
                                <select class="form-select" id="shipping_country" name="shipping_country">
                                    <option value="">Select Country</option>
                                    <option value="United States" {{ old('shipping_country', $customer->shipping_country) == 'United States' ? 'selected' : '' }}>United States</option>
                                    <option value="Canada" {{ old('shipping_country', $customer->shipping_country) == 'Canada' ? 'selected' : '' }}>Canada</option>
                                    <option value="United Kingdom" {{ old('shipping_country', $customer->shipping_country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Australia" {{ old('shipping_country', $customer->shipping_country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                    <option value="Pakistan" {{ old('shipping_country', $customer->shipping_country) == 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                                    <option value="India" {{ old('shipping_country', $customer->shipping_country) == 'India' ? 'selected' : '' }}>India</option>
                                    <option value="China" {{ old('shipping_country', $customer->shipping_country) == 'China' ? 'selected' : '' }}>China</option>
                                    <option value="Germany" {{ old('shipping_country', $customer->shipping_country) == 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="France" {{ old('shipping_country', $customer->shipping_country) == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="Japan" {{ old('shipping_country', $customer->shipping_country) == 'Japan' ? 'selected' : '' }}>Japan</option>
                                    <option value="Other" {{ old('shipping_country', $customer->shipping_country) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Information --}}
                <div class="card border-0 shadow-sm mb-4" id="businessInfoCard" style="{{ in_array($customer->customer_type, ['business', 'wholesale']) ? '' : 'display: none;' }}">
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
                                <input type="text" class="form-control" id="tax_id" name="tax_id" value="{{ old('tax_id', $customer->tax_id) }}" placeholder="XX-XXXXXXX">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="vat_number" class="form-label">VAT Number</label>
                                <input type="text" class="form-control" id="vat_number" name="vat_number" value="{{ old('vat_number', $customer->vat_number) }}" placeholder="GB123456789">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="business_registration" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" id="business_registration" name="business_registration" value="{{ old('business_registration', $customer->business_registration) }}" placeholder="12345678">
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
                                    <option value="en" {{ old('preferred_language', $customer->preferred_language) == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ old('preferred_language', $customer->preferred_language) == 'es' ? 'selected' : '' }}>Spanish</option>
                                    <option value="fr" {{ old('preferred_language', $customer->preferred_language) == 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="de" {{ old('preferred_language', $customer->preferred_language) == 'de' ? 'selected' : '' }}>German</option>
                                    <option value="ur" {{ old('preferred_language', $customer->preferred_language) == 'ur' ? 'selected' : '' }}>Urdu</option>
                                    <option value="ar" {{ old('preferred_language', $customer->preferred_language) == 'ar' ? 'selected' : '' }}>Arabic</option>
                                    <option value="zh" {{ old('preferred_language', $customer->preferred_language) == 'zh' ? 'selected' : '' }}>Chinese</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preferred_currency" class="form-label">Preferred Currency</label>
                                <select class="form-select" id="preferred_currency" name="preferred_currency">
                                    <option value="">Use Store Default</option>
                                    <option value="USD" {{ old('preferred_currency', $customer->preferred_currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('preferred_currency', $customer->preferred_currency) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ old('preferred_currency', $customer->preferred_currency) == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="PKR" {{ old('preferred_currency', $customer->preferred_currency) == 'PKR' ? 'selected' : '' }}>PKR - Pakistani Rupee</option>
                                    <option value="INR" {{ old('preferred_currency', $customer->preferred_currency) == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                    <option value="AUD" {{ old('preferred_currency', $customer->preferred_currency) == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    <option value="CAD" {{ old('preferred_currency', $customer->preferred_currency) == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="acquisition_source" class="form-label">Acquisition Source</label>
                            <select class="form-select" id="acquisition_source" name="acquisition_source">
                                <option value="">How did they find us?</option>
                                <option value="Website" {{ old('acquisition_source', $customer->acquisition_source) == 'Website' ? 'selected' : '' }}>Website</option>
                                <option value="Social Media" {{ old('acquisition_source', $customer->acquisition_source) == 'Social Media' ? 'selected' : '' }}>Social Media</option>
                                <option value="Referral" {{ old('acquisition_source', $customer->acquisition_source) == 'Referral' ? 'selected' : '' }}>Referral</option>
                                <option value="Advertisement" {{ old('acquisition_source', $customer->acquisition_source) == 'Advertisement' ? 'selected' : '' }}>Advertisement</option>
                                <option value="Search Engine" {{ old('acquisition_source', $customer->acquisition_source) == 'Search Engine' ? 'selected' : '' }}>Search Engine</option>
                                <option value="Direct" {{ old('acquisition_source', $customer->acquisition_source) == 'Direct' ? 'selected' : '' }}>Direct</option>
                                <option value="Other" {{ old('acquisition_source', $customer->acquisition_source) == 'Other' ? 'selected' : '' }}>Other</option>
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
                            <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any internal notes about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                            <small class="text-muted">
                                <i class="bi bi-lock-fill"></i> These notes are only visible to admins and will not be shown to the customer.
                            </small>
                        </div>

                        <div class="mb-0">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags_input" placeholder="Add tags separated by commas (e.g., VIP, Wholesale, Premium)">
                            <small class="text-muted">Press Enter or comma to add tags</small>
                            <div id="tagsContainer" class="mt-2"></div>
                            <input type="hidden" name="tags" id="tags_hidden">
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
                                        <option value="{{ $status->key_code }}" {{ old('status_key_code', $customer->status_key_code) == $status->key_code ? 'selected' : '' }}>
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
                                <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1" {{ old('is_verified', $customer->is_verified) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_verified">
                                    <i class="bi bi-patch-check-fill text-success"></i> Email Verified
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Mark email as verified</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_newsletter_subscribed" name="is_newsletter_subscribed" value="1" {{ old('is_newsletter_subscribed', $customer->is_newsletter_subscribed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_newsletter_subscribed">
                                    <i class="bi bi-envelope-check-fill text-primary"></i> Subscribe to Newsletter
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Receive marketing emails</small>
                        </div>

                        <div class="mb-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_sms_subscribed" name="is_sms_subscribed" value="1" {{ old('is_sms_subscribed', $customer->is_sms_subscribed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_sms_subscribed">
                                    <i class="bi bi-phone-vibrate-fill text-info"></i> Subscribe to SMS
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Receive SMS notifications</small>
                        </div>
                    </div>
                </div>

                {{-- Last Login Info --}}
                @if($customer->last_login_at)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-clock-history text-muted"></i> Login Information
                        </h6>
                        <div class="mb-2">
                            <small class="text-muted">Last Login:</small>
                            <div class="fw-semibold">{{ $customer->last_login_at->format('M d, Y h:i A') }}</div>
                            <small class="text-muted">{{ $customer->last_login_at->diffForHumans() }}</small>
                        </div>
                        @if($customer->login_count)
                        <div class="mb-2">
                            <small class="text-muted">Total Logins:</small>
                            <div class="fw-semibold">{{ number_format($customer->login_count) }} times</div>
                        </div>
                        @endif
                        @if($customer->last_login_ip)
                        <div>
                            <small class="text-muted">Last IP:</small>
                            <div class="fw-semibold">{{ $customer->last_login_ip }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Quick Info --}}
                <div class="card border-0 shadow-sm mb-4 info-card">
                    <div class="card-body text-white">
                        <h6 class="card-title mb-3 fw-bold">
                            <i class="bi bi-lightbulb-fill"></i> Update Tips
                        </h6>
                        <ul class="mb-0 ps-3" style="font-size: 0.9rem; line-height: 1.8;">
                            <li class="mb-2">✓ Leave password blank to keep current</li>
                            <li class="mb-2">✓ Email changes may require re-verification</li>
                            <li class="mb-2">✓ Business info shows for business types</li>
                            <li class="mb-2">✓ Blocking prevents login access</li>
                            <li class="mb-0">✓ Changes are logged for audit</li>
                        </ul>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle-fill"></i> Update Customer
                            </button>
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-info">
                                <i class="bi bi-eye"></i> View Profile
                            </a>
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
    // Initialize tags display
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

    // Tags functionality

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
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        // Get form data
        const formData = new FormData(this);

        // Add tags
        if (tagsArray.length > 0) {
            formData.delete('tags');
            tagsArray.forEach((tag, index) => {
                formData.append(`tags[${index}]`, tag);
            });
        }

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
                        showConfirmButton: true,
                        confirmButtonColor: '#5B914C'
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
                        text: xhr.responseJSON?.message || 'Failed to update customer. Please try again.',
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

        // Only validate if both fields have values
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
});
</script>
@endpush
