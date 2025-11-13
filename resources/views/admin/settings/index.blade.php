@extends('admin.layouts.app')

@section('title', 'Store Settings')
@section('styles')
<style>
.tier-row {
    transition: all 0.3s ease;
    border-left: 3px solid #5B914C !important;
}

.tier-row:hover {
    box-shadow: 0 2px 8px rgba(91, 145, 76, 0.2);
}

.calculation-section {
    padding: 1rem;
    border-radius: 8px;
    background-color: white;
}

.form-check-input:checked {
    background-color: #5B914C;
    border-color: #5B914C;
}

.btn-outline-primary {
    color: #5B914C;
    border-color: #5B914C;
}

.btn-outline-primary:hover {
    background-color: #5B914C;
    border-color: #5B914C;
    color: white;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-sliders me-2"></i>Store Settings
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Settings Tabs -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                        <i class="bi bi-info-circle me-2"></i>Basic Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">
                        <i class="bi bi-palette me-2"></i>Branding
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="regional-tab" data-bs-toggle="tab" data-bs-target="#regional" type="button" role="tab">
                        <i class="bi bi-globe me-2"></i>Regional
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button" role="tab">
                        <i class="bi bi-cart-check me-2"></i>Orders
                    </button>
                </li>
                <li class="nav-item d-none" role="presentation">
                    <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" role="tab">
                        <i class="bi bi-receipt me-2"></i>Tax
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">
                        <i class="bi bi-truck me-2"></i>Shipping
                    </button>
                </li>
                <li class="nav-item d-none" role="presentation">
                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="bi bi-boxes me-2"></i>Inventory
                    </button>
                </li>
                <li class="nav-item d-none" role="presentation">
                    <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                        <i class="bi bi-envelope me-2"></i>Email
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="checkout-tab" data-bs-toggle="tab" data-bs-target="#checkout" type="button" role="tab">
                        <i class="bi bi-credit-card me-2"></i>Checkout
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                        <i class="bi bi-share me-2"></i>Social
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                        <i class="bi bi-search me-2"></i>SEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                        <i class="bi bi-tools me-2"></i>Maintenance
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="settingsTabContent">

                {{-- BASIC INFORMATION TAB --}}
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <form action="{{ route('admin.settings.update-basic') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-building me-2"></i>Store Information</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="store_name" class="form-label">Store Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('store_name') is-invalid @enderror"
                                       id="store_name" name="store_name" value="{{ old('store_name', $settings->store_name) }}" required>
                                @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="store_email" class="form-label">Store Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('store_email') is-invalid @enderror"
                                       id="store_email" name="store_email" value="{{ old('store_email', $settings->store_email) }}" required>
                                @error('store_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="store_phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('store_phone') is-invalid @enderror"
                                       id="store_phone" name="store_phone" value="{{ old('store_phone', $settings->store_phone) }}">
                                @error('store_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="store_tagline" class="form-label">Tagline</label>
                                <input type="text" class="form-control @error('store_tagline') is-invalid @enderror"
                                       id="store_tagline" name="store_tagline" value="{{ old('store_tagline', $settings->store_tagline) }}">
                                @error('store_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="store_description" class="form-label">Description</label>
                                <textarea class="form-control @error('store_description') is-invalid @enderror"
                                          id="store_description" name="store_description" rows="3">{{ old('store_description', $settings->store_description) }}</textarea>
                                @error('store_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="store_address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('store_address') is-invalid @enderror"
                                       id="store_address" name="store_address" value="{{ old('store_address', $settings->store_address) }}">
                                @error('store_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="store_city" class="form-label">City</label>
                                <input type="text" class="form-control @error('store_city') is-invalid @enderror"
                                       id="store_city" name="store_city" value="{{ old('store_city', $settings->store_city) }}">
                                @error('store_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="store_state" class="form-label">State/Province</label>
                                <input type="text" class="form-control @error('store_state') is-invalid @enderror"
                                       id="store_state" name="store_state" value="{{ old('store_state', $settings->store_state) }}">
                                @error('store_state')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="store_zip" class="form-label">ZIP/Postal Code</label>
                                <input type="text" class="form-control @error('store_zip') is-invalid @enderror"
                                       id="store_zip" name="store_zip" value="{{ old('store_zip', $settings->store_zip) }}">
                                @error('store_zip')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="store_country" class="form-label">Country Code</label>
                                <input type="text" class="form-control @error('store_country') is-invalid @enderror"
                                       id="store_country" name="store_country" value="{{ old('store_country', $settings->store_country) }}" maxlength="2">
                                <small class="text-muted">2-letter country code (e.g., PK, US, UK)</small>
                                @error('store_country')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- BRANDING TAB --}}
                <div class="tab-pane fade" id="branding" role="tabpanel">
                    <form action="{{ route('admin.settings.update-branding') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <h5 class="mb-3"><i class="bi bi-image me-2"></i>Brand Assets</h5>

                        <div class="row">
                            {{-- Logo --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Store Logo</label>
                                <div class="text-center mb-3">
                                    @if($settings->store_logo)
                                    <img src="{{ $settings->logo_url }}" alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                                    @else
                                    <div class="border rounded p-4 bg-light">
                                        <i class="bi bi-image" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="text-muted mb-0">No logo uploaded</p>
                                    </div>
                                    @endif
                                </div>
                                <input type="file" class="form-control @error('store_logo') is-invalid @enderror"
                                       name="store_logo" accept="image/jpeg,image/png,image/jpg,image/svg+xml">
                                <small class="text-muted">Max 2MB (JPEG, PNG, JPG, SVG)</small>
                                @error('store_logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Favicon --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Favicon</label>
                                <div class="text-center mb-3">
                                    @if($settings->store_favicon)
                                    <img src="{{ $settings->favicon_url }}" alt="Favicon" class="img-thumbnail" style="max-height: 150px;">
                                    @else
                                    <div class="border rounded p-4 bg-light">
                                        <i class="bi bi-image" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="text-muted mb-0">No favicon uploaded</p>
                                    </div>
                                    @endif
                                </div>
                                <input type="file" class="form-control @error('store_favicon') is-invalid @enderror"
                                       name="store_favicon" accept="image/png,.ico">
                                <small class="text-muted">Max 512KB (PNG, ICO)</small>
                                @error('store_favicon')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Banner --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Store Banner</label>
                                <div class="text-center mb-3">
                                    @if($settings->store_banner)
                                    <img src="{{ $settings->banner_url }}" alt="Banner" class="img-thumbnail" style="max-height: 150px;">
                                    @else
                                    <div class="border rounded p-4 bg-light">
                                        <i class="bi bi-image" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="text-muted mb-0">No banner uploaded</p>
                                    </div>
                                    @endif
                                </div>
                                <input type="file" class="form-control @error('store_banner') is-invalid @enderror"
                                       name="store_banner" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Max 5MB (JPEG, PNG, JPG)</small>
                                @error('store_banner')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-2"></i>Upload Files
                            </button>
                        </div>
                    </form>
                </div>

                {{-- REGIONAL SETTINGS TAB --}}
                <div class="tab-pane fade" id="regional" role="tabpanel">
                    <form action="{{ route('admin.settings.update-regional') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Regional Settings</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                                <select class="form-select @error('timezone') is-invalid @enderror"
                                        id="timezone" name="timezone" required>
                                    <option value="">-- Select Timezone --</option>
                                    @foreach(get_all_timezones() as $tzValue => $tzLabel)
                                        <option value="{{ $tzValue }}"
                                                {{ old('timezone', $settings->timezone) == $tzValue ? 'selected' : '' }}>
                                            {{ $tzLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Set your store's timezone</small>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date_format" class="form-label">Date Format <span class="text-danger">*</span></label>
                                <select class="form-select @error('date_format') is-invalid @enderror" id="date_format" name="date_format" required>
                                    <option value="">-- Select Date Format --</option>
                                    @foreach(get_all_date_formats() as $formatValue => $formatLabel)
                                        <option value="{{ $formatValue }}"
                                                {{ old('date_format', $settings->date_format) == $formatValue ? 'selected' : '' }}>
                                            {{ $formatLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('date_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="time_format" class="form-label">Time Format <span class="text-danger">*</span></label>
                                <select class="form-select @error('time_format') is-invalid @enderror" id="time_format" name="time_format" required>
                                    <option value="">-- Select Time Format --</option>
                                    @foreach(get_all_time_formats() as $formatValue => $formatLabel)
                                        <option value="{{ $formatValue }}"
                                                {{ old('time_format', $settings->time_format) == $formatValue ? 'selected' : '' }}>
                                            {{ $formatLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('time_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency_code" class="form-label">Currency Code <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency_code') is-invalid @enderror" id="currency_code" name="currency_code" required>
                                    <option value="">-- Select Currency --</option>
                                    @foreach(get_all_currencies() as $currencyCode => $currencyName)
                                        <option value="{{ $currencyCode }}"
                                                data-symbol="{{ get_currency_symbol($currencyCode) }}"
                                                {{ old('currency_code', $settings->currency_code) == $currencyCode ? 'selected' : '' }}>
                                            {{ $currencyName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                             <div class="col-md-6 mb-3">
                                <label for="currency_symbol" class="form-label">
                                    Currency Symbol <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control @error('currency_symbol') is-invalid @enderror"
                                    id="currency_symbol"
                                    name="currency_symbol"
                                    value="{{ old('currency_symbol', $settings->currency_symbol) }}"
                                    required readonly>
                                <small class="text-muted">Auto-filled based on currency selection</small>
                                @error('currency_symbol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency_position" class="form-label">Currency Position <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency_position') is-invalid @enderror" id="currency_position" name="currency_position" required>
                                    <option value="">-- Select Position --</option>
                                    @foreach(get_currency_positions() as $posValue => $posLabel)
                                        <option value="{{ $posValue }}"
                                                {{ old('currency_position', $settings->currency_position) == $posValue ? 'selected' : '' }}>
                                            {{ $posLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_position')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="decimal_places" class="form-label">
                                    Decimal Places <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('decimal_places') is-invalid @enderror"
                                        id="decimal_places" name="decimal_places" required>
                                    <option value="0" {{ old('decimal_places', $settings->decimal_places) == 0 ? 'selected' : '' }}>0 (100)</option>
                                    <option value="1" {{ old('decimal_places', $settings->decimal_places) == 1 ? 'selected' : '' }}>1 (100.0)</option>
                                    <option value="2" {{ old('decimal_places', $settings->decimal_places) == 2 ? 'selected' : '' }}>2 (100.00)</option>
                                    <option value="3" {{ old('decimal_places', $settings->decimal_places) == 3 ? 'selected' : '' }}>3 (100.000)</option>
                                    <option value="4" {{ old('decimal_places', $settings->decimal_places) == 4 ? 'selected' : '' }}>4 (100.0000)</option>
                                </select>
                                <small class="text-muted">Number of decimal places for prices</small>
                                @error('decimal_places')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="thousand_separator" class="form-label">
                                    Thousand Separator <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('thousand_separator') is-invalid @enderror"
                                        id="thousand_separator" name="thousand_separator" required>
                                    @foreach(get_thousand_separators() as $sepValue => $sepLabel)
                                        <option value="{{ $sepValue }}"
                                                {{ old('thousand_separator', $settings->thousand_separator) == $sepValue ? 'selected' : '' }}>
                                            {{ $sepLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Separator for thousands</small>
                                @error('thousand_separator')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="decimal_separator" class="form-label">Decimal Separator <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('decimal_separator') is-invalid @enderror"
                                       id="decimal_separator" name="decimal_separator" value="{{ old('decimal_separator', $settings->decimal_separator) }}" maxlength="1" required>
                                @error('decimal_separator')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ORDER SETTINGS TAB --}}
                <div class="tab-pane fade" id="order" role="tabpanel">
                    <form action="{{ route('admin.settings.update-order') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-hash me-2"></i>Order Configuration</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="order_prefix" class="form-label">Order Prefix <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('order_prefix') is-invalid @enderror"
                                       id="order_prefix" name="order_prefix" value="{{ old('order_prefix', $settings->order_prefix) }}" required>
                                <small class="text-muted">e.g., CS-, ORD-, INV-</small>
                                @error('order_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="order_number_start" class="form-label">Starting Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('order_number_start') is-invalid @enderror"
                                       id="order_number_start" name="order_number_start" value="{{ old('order_number_start', $settings->order_number_start) }}" min="1" required>
                                @error('order_number_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="order_number_length" class="form-label">Number Length <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('order_number_length') is-invalid @enderror"
                                       id="order_number_length" name="order_number_length" value="{{ old('order_number_length', $settings->order_number_length) }}" min="4" max="10" required>
                                <small class="text-muted">Padding with zeros</small>
                                @error('order_number_length')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <strong>Preview:</strong> {{ $settings->order_prefix }}{{ str_pad($settings->order_number_start, $settings->order_number_length, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="order_auto_confirm" name="order_auto_confirm"
                                           {{ $settings->order_auto_confirm ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_auto_confirm">
                                        Auto-confirm orders
                                    </label>
                                </div>
                                <small class="text-muted">Automatically confirm orders upon placement</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="order_notification_email" name="order_notification_email"
                                           {{ $settings->order_notification_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_notification_email">
                                        Send order notification emails
                                    </label>
                                </div>
                                <small class="text-muted">Email notifications for new orders</small>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAX SETTINGS TAB --}}
                <div class="tab-pane fade" id="tax" role="tabpanel">
                    <form action="{{ route('admin.settings.update-tax') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-percent me-2"></i>Tax Configuration</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled"
                                           {{ $settings->tax_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tax_enabled">
                                        <strong>Enable Tax</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="tax_included_in_price" name="tax_included_in_price"
                                           {{ $settings->tax_included_in_price ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tax_included_in_price">
                                        <strong>Tax Included in Price</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tax_name" class="form-label">Tax Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('tax_name') is-invalid @enderror"
                                       id="tax_name" name="tax_name" value="{{ old('tax_name', $settings->tax_name) }}" required>
                                <small class="text-muted">e.g., VAT, GST, Sales Tax</small>
                                @error('tax_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tax_rate" class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('tax_rate') is-invalid @enderror"
                                       id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate) }}"
                                       min="0" max="100" step="0.01" required>
                                @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- SHIPPING SETTINGS TAB --}}
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <form action="{{ route('admin.settings.update-shipping') }}" method="POST" id="shippingForm">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i>Shipping Configuration</h5>

                        {{-- Enable Shipping --}}
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="shipping_enabled" name="shipping_enabled"
                                        {{ old('shipping_enabled', $settings->shipping_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shipping_enabled">
                                        <strong class="fs-5">Enable Shipping</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Shipping Calculation Method --}}
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-calculator me-2"></i>Shipping Calculation Method
                                </h6>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_calculation_type" class="form-label">
                                            Calculation Type <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('shipping_calculation_type') is-invalid @enderror"
                                                id="shipping_calculation_type" name="shipping_calculation_type" required>
                                            <option value="flat_rate" {{ old('shipping_calculation_type', $settings->shipping_calculation_type) == 'flat_rate' ? 'selected' : '' }}>
                                                Flat Rate (Fixed price)
                                            </option>
                                            <option value="per_kg" {{ old('shipping_calculation_type', $settings->shipping_calculation_type) == 'per_kg' ? 'selected' : '' }}>
                                                Per Kilogram (Weight-based)
                                            </option>
                                            <option value="per_liter" {{ old('shipping_calculation_type', $settings->shipping_calculation_type) == 'per_liter' ? 'selected' : '' }}>
                                                Per Liter (Volume-based)
                                            </option>
                                            <option value="per_item" {{ old('shipping_calculation_type', $settings->shipping_calculation_type) == 'per_item' ? 'selected' : '' }}>
                                                Per Item (Quantity-based)
                                            </option>
                                            <option value="tiered" {{ old('shipping_calculation_type', $settings->shipping_calculation_type) == 'tiered' ? 'selected' : '' }}>
                                                Tiered Rates (Based on order total/weight)
                                            </option>
                                        </select>
                                        @error('shipping_calculation_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="default_shipping_cost" class="form-label">
                                            Default Shipping Cost <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                            <input type="number" class="form-control @error('default_shipping_cost') is-invalid @enderror"
                                                id="default_shipping_cost" name="default_shipping_cost"
                                                value="{{ old('default_shipping_cost', $settings->default_shipping_cost) }}"
                                                min="0" step="0.01" required>
                                            @error('default_shipping_cost')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted">Fallback rate when other methods don't apply</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Calculation Type Specific Settings --}}
                        <div class="card mb-4 border-0 bg-light" id="calculation-specific-settings">
                            <div class="card-body">

                                {{-- Flat Rate Settings --}}
                                <div class="calculation-section" id="flat_rate_section" style="display: none;">
                                    <h6 class="mb-3"><i class="bi bi-tag me-2"></i>Flat Rate Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_nationwide_flat_rate"
                                                    name="enable_nationwide_flat_rate"
                                                    {{ old('enable_nationwide_flat_rate', $settings->enable_nationwide_flat_rate) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="enable_nationwide_flat_rate">
                                                    <strong>Enable Nationwide Flat Rate</strong>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="nationwide_flat_rate" class="form-label">Nationwide Flat Rate</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                                <input type="number" class="form-control @error('nationwide_flat_rate') is-invalid @enderror"
                                                    id="nationwide_flat_rate" name="nationwide_flat_rate"
                                                    value="{{ old('nationwide_flat_rate', $settings->nationwide_flat_rate) }}"
                                                    min="0" step="0.01">
                                                @error('nationwide_flat_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Fixed shipping cost for entire country</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Per Kilogram Settings --}}
                                <div class="calculation-section" id="per_kg_section" style="display: none;">
                                    <h6 class="mb-3"><i class="bi bi-speedometer2 me-2"></i>Per Kilogram Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="shipping_rate_per_kg" class="form-label">Rate per Kilogram</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                                <input type="number" class="form-control @error('shipping_rate_per_kg') is-invalid @enderror"
                                                    id="shipping_rate_per_kg" name="shipping_rate_per_kg"
                                                    value="{{ old('shipping_rate_per_kg', $settings->shipping_rate_per_kg) }}"
                                                    min="0" step="0.01">
                                                <span class="input-group-text">/kg</span>
                                                @error('shipping_rate_per_kg')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Cost per kilogram of total order weight</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="max_weight_standard_shipping" class="form-label">Maximum Weight Limit</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control @error('max_weight_standard_shipping') is-invalid @enderror"
                                                    id="max_weight_standard_shipping" name="max_weight_standard_shipping"
                                                    value="{{ old('max_weight_standard_shipping', $settings->max_weight_standard_shipping) }}"
                                                    min="0" step="0.01">
                                                <span class="input-group-text">kg</span>
                                                @error('max_weight_standard_shipping')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Maximum weight for standard shipping (optional)</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Per Liter Settings --}}
                                <div class="calculation-section" id="per_liter_section" style="display: none;">
                                    <h6 class="mb-3"><i class="bi bi-droplet me-2"></i>Per Liter Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="shipping_rate_per_liter" class="form-label">Rate per Liter</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                                <input type="number" class="form-control @error('shipping_rate_per_liter') is-invalid @enderror"
                                                    id="shipping_rate_per_liter" name="shipping_rate_per_liter"
                                                    value="{{ old('shipping_rate_per_liter', $settings->shipping_rate_per_liter) }}"
                                                    min="0" step="0.01">
                                                <span class="input-group-text">/L</span>
                                                @error('shipping_rate_per_liter')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Cost per liter of total order volume</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="max_volume_standard_shipping" class="form-label">Maximum Volume Limit</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control @error('max_volume_standard_shipping') is-invalid @enderror"
                                                    id="max_volume_standard_shipping" name="max_volume_standard_shipping"
                                                    value="{{ old('max_volume_standard_shipping', $settings->max_volume_standard_shipping) }}"
                                                    min="0" step="0.01">
                                                <span class="input-group-text">L</span>
                                                @error('max_volume_standard_shipping')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Maximum volume for standard shipping (optional)</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Per Item Settings --}}
                                <div class="calculation-section" id="per_item_section" style="display: none;">
                                    <h6 class="mb-3"><i class="bi bi-box me-2"></i>Per Item Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="shipping_rate_per_item" class="form-label">Rate per Item</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                                <input type="number" class="form-control @error('shipping_rate_per_item') is-invalid @enderror"
                                                    id="shipping_rate_per_item" name="shipping_rate_per_item"
                                                    value="{{ old('shipping_rate_per_item', $settings->shipping_rate_per_item) }}"
                                                    min="0" step="0.01">
                                                <span class="input-group-text">/item</span>
                                                @error('shipping_rate_per_item')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Cost per item in the order</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tiered Settings --}}
                                <div class="calculation-section" id="tiered_section" style="display: none;">
                                    <h6 class="mb-3"><i class="bi bi-bar-chart-steps me-2"></i>Tiered Shipping Rates</h6>

                                    <div id="tiered-rates-container">
                                        @php
                                            $tieredRates = old('tiered_shipping_rates', $settings->tiered_shipping_rates ?? []);
                                            if (is_string($tieredRates)) {
                                                $tieredRates = json_decode($tieredRates, true) ?? [];
                                            }
                                        @endphp

                                        @forelse($tieredRates as $index => $tier)
                                        <div class="tier-row card mb-3 border" data-tier-index="{{ $index }}">
                                            <div class="card-body">
                                                <div class="row align-items-end">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Tier Type</label>
                                                        <select class="form-select tier-type" name="tiers[{{ $index }}][type]">
                                                            <option value="order_total" {{ ($tier['type'] ?? '') == 'order_total' ? 'selected' : '' }}>
                                                                Order Total
                                                            </option>
                                                            <option value="weight" {{ ($tier['type'] ?? '') == 'weight' ? 'selected' : '' }}>
                                                                Weight
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Minimum Threshold</label>
                                                        <input type="number" class="form-control tier-threshold"
                                                            name="tiers[{{ $index }}][threshold]"
                                                            value="{{ $tier['threshold'] ?? 0 }}"
                                                            min="0" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Shipping Rate</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                                            <input type="number" class="form-control tier-rate"
                                                                name="tiers[{{ $index }}][rate]"
                                                                value="{{ $tier['rate'] ?? 0 }}"
                                                                min="0" step="0.01" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-sm remove-tier w-100">
                                                            <i class="bi bi-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted" id="no-tiers-message">No tiers added yet. Click "Add Tier" to create one.</p>
                                        @endforelse
                                    </div>

                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-tier-btn">
                                        <i class="bi bi-plus-circle me-2"></i>Add Tier
                                    </button>

                                    <input type="hidden" name="tiered_shipping_rates" id="tiered_shipping_rates_input">

                                    <div class="alert alert-info mt-3">
                                        <small>
                                            <strong>How it works:</strong> Create multiple shipping rate tiers based on order total or weight.
                                            The system will apply the highest tier that the order qualifies for.
                                        </small>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Additional Shipping Settings --}}
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-gear me-2"></i>Additional Settings
                                </h6>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="free_shipping_threshold" class="form-label">
                                            <i class="bi bi-truck me-1"></i>Free Shipping Threshold
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                            <input type="number" class="form-control @error('free_shipping_threshold') is-invalid @enderror"
                                                id="free_shipping_threshold" name="free_shipping_threshold"
                                                value="{{ old('free_shipping_threshold', $settings->free_shipping_threshold) }}"
                                                min="0" step="0.01">
                                            @error('free_shipping_threshold')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted">Orders above this amount get free shipping (leave empty to disable)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="minimum_order_for_shipping" class="form-label">
                                            <i class="bi bi-cart-check me-1"></i>Minimum Order for Shipping
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                            <input type="number" class="form-control @error('minimum_order_for_shipping') is-invalid @enderror"
                                                id="minimum_order_for_shipping" name="minimum_order_for_shipping"
                                                value="{{ old('minimum_order_for_shipping', $settings->minimum_order_for_shipping) }}"
                                                min="0" step="0.01">
                                            @error('minimum_order_for_shipping')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted">Minimum order value required for shipping (optional)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="handling_fee" class="form-label">
                                            <i class="bi bi-wallet2 me-1"></i>Handling Fee
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                            <input type="number" class="form-control @error('handling_fee') is-invalid @enderror"
                                                id="handling_fee" name="handling_fee"
                                                value="{{ old('handling_fee', $settings->handling_fee ?? 0) }}"
                                                min="0" step="0.01">
                                            @error('handling_fee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted">Additional handling fee added to shipping cost</small>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="estimated_delivery_days_min" class="form-label">
                                            <i class="bi bi-calendar-check me-1"></i>Est. Delivery (Min Days)
                                        </label>
                                        <input type="number" class="form-control @error('estimated_delivery_days_min') is-invalid @enderror"
                                            id="estimated_delivery_days_min" name="estimated_delivery_days_min"
                                            value="{{ old('estimated_delivery_days_min', $settings->estimated_delivery_days_min) }}"
                                            min="1">
                                        @error('estimated_delivery_days_min')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="estimated_delivery_days_max" class="form-label">
                                            <i class="bi bi-calendar-range me-1"></i>Est. Delivery (Max Days)
                                        </label>
                                        <input type="number" class="form-control @error('estimated_delivery_days_max') is-invalid @enderror"
                                            id="estimated_delivery_days_max" name="estimated_delivery_days_max"
                                            value="{{ old('estimated_delivery_days_max', $settings->estimated_delivery_days_max) }}"
                                            min="1">
                                        @error('estimated_delivery_days_max')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_regional_rates"
                                                name="enable_regional_rates"
                                                {{ old('enable_regional_rates', $settings->enable_regional_rates) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_regional_rates">
                                                Enable Regional Rates (Coming Soon)
                                            </label>
                                        </div>
                                        <small class="text-muted">Zone-based shipping rates for different regions</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>Save Shipping Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- INVENTORY SETTINGS TAB --}}
                <div class="tab-pane fade" id="inventory" role="tabpanel">
                    <form action="{{ route('admin.settings.update-inventory') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-clipboard-data me-2"></i>Inventory Configuration</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory"
                                           {{ $settings->track_inventory ? 'checked' : '' }}>
                                    <label class="form-check-label" for="track_inventory">
                                        <strong>Track Inventory</strong>
                                    </label>
                                </div>
                                <small class="text-muted">Monitor stock levels</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allow_backorders" name="allow_backorders"
                                           {{ $settings->allow_backorders ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_backorders">
                                        <strong>Allow Backorders</strong>
                                    </label>
                                </div>
                                <small class="text-muted">Allow orders when out of stock</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="low_stock_notifications" name="low_stock_notifications"
                                           {{ $settings->low_stock_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label" for="low_stock_notifications">
                                        <strong>Low Stock Notifications</strong>
                                    </label>
                                </div>
                                <small class="text-muted">Email alerts for low stock</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="low_stock_threshold" class="form-label">Low Stock Threshold <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                       id="low_stock_threshold" name="low_stock_threshold"
                                       value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}"
                                       min="0" required>
                                <small class="text-muted">Alert when stock falls below this number</small>
                                @error('low_stock_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- EMAIL SETTINGS TAB --}}
                <div class="tab-pane fade" id="email" role="tabpanel">
                    <form action="{{ route('admin.settings.update-email') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-envelope-at me-2"></i>Email Configuration</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email_from_name" class="form-label">From Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('email_from_name') is-invalid @enderror"
                                       id="email_from_name" name="email_from_name" value="{{ old('email_from_name', $settings->email_from_name) }}" required>
                                @error('email_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email_from_address" class="form-label">From Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email_from_address') is-invalid @enderror"
                                       id="email_from_address" name="email_from_address" value="{{ old('email_from_address', $settings->email_from_address) }}" required>
                                @error('email_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <h6 class="mb-3">Email Notifications</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="customer_registration_email" name="customer_registration_email"
                                           {{ $settings->customer_registration_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="customer_registration_email">
                                        Customer Registration
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="order_confirmation_email" name="order_confirmation_email"
                                           {{ $settings->order_confirmation_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_confirmation_email">
                                        Order Confirmation
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="order_shipped_email" name="order_shipped_email"
                                           {{ $settings->order_shipped_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_shipped_email">
                                        Order Shipped
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                {{-- CHECKOUT SETTINGS TAB --}}
                <div class="tab-pane fade" id="checkout" role="tabpanel">
                    <form action="{{ route('admin.settings.update-checkout') }}" method="POST" id="checkoutForm">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-credit-card-2-front me-2"></i>Checkout & Payment Configuration</h5>

                        {{-- Payment Methods Section --}}
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-wallet2 me-2"></i>Available Payment Methods
                                </h6>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Note:</strong> At least one payment method must be enabled for customers to complete their orders.
                                </div>

                                {{-- Cash on Delivery --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-white">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-cash-coin text-success me-3" style="font-size: 24px;"></i>
                                                <div>
                                                    <h6 class="mb-0">Cash on Delivery (COD)</h6>
                                                    <small class="text-muted">Customer pays when order is delivered</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_cod"
                                                    name="enable_cod" style="width: 3rem; height: 1.5rem;"
                                                    {{ old('enable_cod', $settings->enable_cod) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="cod_settings" style="display: {{ old('enable_cod', $settings->enable_cod) ? 'block' : 'none' }};">
                                        <label for="cod_instructions" class="form-label">COD Instructions for Customers</label>
                                        <textarea class="form-control @error('cod_instructions') is-invalid @enderror"
                                                id="cod_instructions" name="cod_instructions" rows="4"
                                                placeholder="Enter instructions for COD payments (e.g., Please keep exact change ready, Payment accepted in PKR only)">{{ old('cod_instructions', $settings->cod_instructions) }}</textarea>
                                        <small class="text-muted">These instructions will be shown to customers during checkout</small>
                                        @error('cod_instructions')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Online Payment --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-white">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-credit-card text-primary me-3" style="font-size: 24px;"></i>
                                                <div>
                                                    <h6 class="mb-0">Online Payment</h6>
                                                    <small class="text-muted">Stripe, PayPal, or other payment gateways</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_online_payment"
                                                    name="enable_online_payment" style="width: 3rem; height: 1.5rem;"
                                                    {{ old('enable_online_payment', $settings->enable_online_payment) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="online_payment_settings" style="display: {{ old('enable_online_payment', $settings->enable_online_payment) ? 'block' : 'none' }};">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="payment_gateway" class="form-label">Payment Gateway</label>
                                                <select class="form-select @error('payment_gateway') is-invalid @enderror"
                                                        id="payment_gateway" name="payment_gateway">
                                                    <option value="">-- Select Gateway --</option>
                                                    <option value="stripe" {{ old('payment_gateway', $settings->payment_gateway) == 'stripe' ? 'selected' : '' }}>Stripe</option>
                                                    <option value="paypal" {{ old('payment_gateway', $settings->payment_gateway) == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                                    <option value="razorpay" {{ old('payment_gateway', $settings->payment_gateway) == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                                                    <option value="jazzcash" {{ old('payment_gateway', $settings->payment_gateway) == 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                                                    <option value="easypaisa" {{ old('payment_gateway', $settings->payment_gateway) == 'easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                                </select>
                                                @error('payment_gateway')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="payment_gateway_mode" class="form-label">Gateway Mode</label>
                                                <select class="form-select @error('payment_gateway_mode') is-invalid @enderror"
                                                        id="payment_gateway_mode" name="payment_gateway_mode">
                                                    <option value="sandbox" {{ old('payment_gateway_mode', $settings->payment_gateway_mode) == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                                    <option value="live" {{ old('payment_gateway_mode', $settings->payment_gateway_mode) == 'live' ? 'selected' : '' }}>Live (Production)</option>
                                                </select>
                                                @error('payment_gateway_mode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="payment_gateway_public_key" class="form-label">Public/Publishable Key</label>
                                                <input type="text" class="form-control @error('payment_gateway_public_key') is-invalid @enderror"
                                                    id="payment_gateway_public_key" name="payment_gateway_public_key"
                                                    value="{{ old('payment_gateway_public_key', $settings->payment_gateway_public_key) }}"
                                                    placeholder="pk_test_...">
                                                @error('payment_gateway_public_key')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="payment_gateway_secret_key" class="form-label">Secret Key</label>
                                                <input type="password" class="form-control @error('payment_gateway_secret_key') is-invalid @enderror"
                                                    id="payment_gateway_secret_key" name="payment_gateway_secret_key"
                                                    value="{{ old('payment_gateway_secret_key', $settings->payment_gateway_secret_key) }}"
                                                    placeholder="sk_test_...">
                                                <small class="text-muted">Keep this confidential</small>
                                                @error('payment_gateway_secret_key')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <label for="online_payment_instructions" class="form-label">Online Payment Instructions</label>
                                        <textarea class="form-control @error('online_payment_instructions') is-invalid @enderror"
                                                id="online_payment_instructions" name="online_payment_instructions" rows="3"
                                                placeholder="Enter instructions for online payments">{{ old('online_payment_instructions', $settings->online_payment_instructions) }}</textarea>
                                        @error('online_payment_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Bank Transfer --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-white">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-bank text-info me-3" style="font-size: 24px;"></i>
                                                <div>
                                                    <h6 class="mb-0">Bank Transfer</h6>
                                                    <small class="text-muted">Direct bank deposit or transfer</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_bank_transfer"
                                                    name="enable_bank_transfer" style="width: 3rem; height: 1.5rem;"
                                                    {{ old('enable_bank_transfer', $settings->enable_bank_transfer) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="bank_transfer_settings" style="display: {{ old('enable_bank_transfer', $settings->enable_bank_transfer) ? 'block' : 'none' }};">
                                        <h6 class="mb-3">Bank Account Details</h6>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="bank_name" class="form-label">Bank Name</label>
                                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                                                    id="bank_name" name="bank_name"
                                                    value="{{ old('bank_name', $settings->bank_name) }}"
                                                    placeholder="e.g., HBL, UBL, MCB">
                                                @error('bank_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="bank_account_name" class="form-label">Account Holder Name</label>
                                                <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror"
                                                    id="bank_account_name" name="bank_account_name"
                                                    value="{{ old('bank_account_name', $settings->bank_account_name) }}"
                                                    placeholder="Full name as per bank records">
                                                @error('bank_account_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="bank_account_number" class="form-label">Account Number</label>
                                                <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror"
                                                    id="bank_account_number" name="bank_account_number"
                                                    value="{{ old('bank_account_number', $settings->bank_account_number) }}"
                                                    placeholder="1234567890">
                                                @error('bank_account_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="bank_iban" class="form-label">IBAN (Optional)</label>
                                                <input type="text" class="form-control @error('bank_iban') is-invalid @enderror"
                                                    id="bank_iban" name="bank_iban"
                                                    value="{{ old('bank_iban', $settings->bank_iban) }}"
                                                    placeholder="PK36SCBL0000001123456702">
                                                @error('bank_iban')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="bank_swift_code" class="form-label">SWIFT Code (Optional)</label>
                                                <input type="text" class="form-control @error('bank_swift_code') is-invalid @enderror"
                                                    id="bank_swift_code" name="bank_swift_code"
                                                    value="{{ old('bank_swift_code', $settings->bank_swift_code) }}"
                                                    placeholder="HBLBPKKAXXX">
                                                @error('bank_swift_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="bank_branch" class="form-label">Branch Name (Optional)</label>
                                                <input type="text" class="form-control @error('bank_branch') is-invalid @enderror"
                                                    id="bank_branch" name="bank_branch"
                                                    value="{{ old('bank_branch', $settings->bank_branch) }}"
                                                    placeholder="Main Branch, Lahore">
                                                @error('bank_branch')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <label for="bank_transfer_instructions" class="form-label">Bank Transfer Instructions</label>
                                        <textarea class="form-control @error('bank_transfer_instructions') is-invalid @enderror"
                                                id="bank_transfer_instructions" name="bank_transfer_instructions" rows="4"
                                                placeholder="Enter instructions (e.g., Please transfer amount and send screenshot to whatsapp +92-XXX-XXXXXXX)">{{ old('bank_transfer_instructions', $settings->bank_transfer_instructions) }}</textarea>
                                        <small class="text-muted">Instructions for customers on how to complete bank transfer and submit proof</small>
                                        @error('bank_transfer_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" id="show_bank_details_on_confirmation"
                                                name="show_bank_details_on_confirmation"
                                                {{ old('show_bank_details_on_confirmation', $settings->show_bank_details_on_confirmation) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_bank_details_on_confirmation">
                                                Show bank details on order confirmation page
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Checkout Options --}}
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-gear me-2"></i>Checkout Options
                                </h6>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="require_phone_checkout"
                                                name="require_phone_checkout"
                                                {{ old('require_phone_checkout', $settings->require_phone_checkout) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="require_phone_checkout">
                                                <strong>Require Phone Number</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Customer must provide phone number at checkout</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="require_address_checkout"
                                                name="require_address_checkout"
                                                {{ old('require_address_checkout', $settings->require_address_checkout) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="require_address_checkout">
                                                <strong>Require Delivery Address</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Customer must provide full delivery address</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_guest_checkout"
                                                name="enable_guest_checkout"
                                                {{ old('enable_guest_checkout', $settings->enable_guest_checkout) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_guest_checkout">
                                                <strong>Enable Guest Checkout</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Allow customers to checkout without creating an account</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="terms_conditions_required"
                                                name="terms_conditions_required"
                                                {{ old('terms_conditions_required', $settings->terms_conditions_required) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="terms_conditions_required">
                                                <strong>Require Terms & Conditions Acceptance</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Customer must agree to terms before placing order</small>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="checkout_terms_text" class="form-label">Terms & Conditions Text</label>
                                        <textarea class="form-control @error('checkout_terms_text') is-invalid @enderror"
                                                id="checkout_terms_text" name="checkout_terms_text" rows="3"
                                                placeholder="By placing this order, you agree to our terms and conditions...">{{ old('checkout_terms_text', $settings->checkout_terms_text) }}</textarea>
                                        <small class="text-muted">Short terms text shown at checkout (link to full T&C page recommended)</small>
                                        @error('checkout_terms_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Order Confirmation --}}
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-check-circle me-2"></i>Order Confirmation
                                </h6>

                                <div class="mb-3">
                                    <label for="order_confirmation_message" class="form-label">Order Confirmation Message</label>
                                    <textarea class="form-control @error('order_confirmation_message') is-invalid @enderror"
                                            id="order_confirmation_message" name="order_confirmation_message" rows="4"
                                            placeholder="Thank you for your order! We will process it shortly...">{{ old('order_confirmation_message', $settings->order_confirmation_message) }}</textarea>
                                    <small class="text-muted">Message shown to customers after successful order placement</small>
                                    @error('order_confirmation_message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>Save Checkout Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- SOCIAL MEDIA TAB --}}
                <div class="tab-pane fade" id="social" role="tabpanel">
                    <form action="{{ route('admin.settings.update-social') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i>Social Media Links</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="facebook_url" class="form-label">
                                    <i class="bi bi-facebook text-primary me-2"></i>Facebook URL
                                </label>
                                <input type="url" class="form-control @error('facebook_url') is-invalid @enderror"
                                       id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $settings->facebook_url) }}"
                                       placeholder="https://facebook.com/yourpage">
                                @error('facebook_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="twitter_url" class="form-label">
                                    <i class="bi bi-twitter text-info me-2"></i>Twitter URL
                                </label>
                                <input type="url" class="form-control @error('twitter_url') is-invalid @enderror"
                                       id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $settings->twitter_url) }}"
                                       placeholder="https://twitter.com/yourprofile">
                                @error('twitter_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="instagram_url" class="form-label">
                                    <i class="bi bi-instagram text-danger me-2"></i>Instagram URL
                                </label>
                                <input type="url" class="form-control @error('instagram_url') is-invalid @enderror"
                                       id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $settings->instagram_url) }}"
                                       placeholder="https://instagram.com/yourprofile">
                                @error('instagram_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="linkedin_url" class="form-label">
                                    <i class="bi bi-linkedin text-primary me-2"></i>LinkedIn URL
                                </label>
                                <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                       id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $settings->linkedin_url) }}"
                                       placeholder="https://linkedin.com/company/yourcompany">
                                @error('linkedin_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="youtube_url" class="form-label">
                                    <i class="bi bi-youtube text-danger me-2"></i>YouTube URL
                                </label>
                                <input type="url" class="form-control @error('youtube_url') is-invalid @enderror"
                                       id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $settings->youtube_url) }}"
                                       placeholder="https://youtube.com/yourchannel">
                                @error('youtube_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- SEO SETTINGS TAB --}}
                <div class="tab-pane fade" id="seo" role="tabpanel">
                    <form action="{{ route('admin.settings.update-seo') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i>SEO & Analytics</h5>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                       id="meta_title" name="meta_title" value="{{ old('meta_title', $settings->meta_title) }}" maxlength="255">
                                <small class="text-muted">Recommended: 50-60 characters</small>
                                @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                          id="meta_description" name="meta_description" rows="3" maxlength="500">{{ old('meta_description', $settings->meta_description) }}</textarea>
                                <small class="text-muted">Recommended: 150-160 characters</small>
                                @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <textarea class="form-control @error('meta_keywords') is-invalid @enderror"
                                          id="meta_keywords" name="meta_keywords" rows="2">{{ old('meta_keywords', $settings->meta_keywords) }}</textarea>
                                <small class="text-muted">Comma-separated keywords</small>
                                @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <h6 class="mb-3">Analytics Integration</h6>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="google_analytics_id" class="form-label">
                                    <i class="bi bi-google text-danger me-2"></i>Google Analytics ID
                                </label>
                                <input type="text" class="form-control @error('google_analytics_id') is-invalid @enderror"
                                       id="google_analytics_id" name="google_analytics_id" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}"
                                       placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X">
                                @error('google_analytics_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="facebook_pixel_id" class="form-label">
                                    <i class="bi bi-facebook text-primary me-2"></i>Facebook Pixel ID
                                </label>
                                <input type="text" class="form-control @error('facebook_pixel_id') is-invalid @enderror"
                                       id="facebook_pixel_id" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $settings->facebook_pixel_id) }}"
                                       placeholder="XXXXXXXXXXXXXXX">
                                @error('facebook_pixel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- MAINTENANCE MODE TAB --}}
                <div class="tab-pane fade" id="maintenance" role="tabpanel">
                    <form action="{{ route('admin.settings.update-maintenance') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-cone-striped me-2"></i>Maintenance Mode</h5>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Enabling maintenance mode will make your store unavailable to customers. Admin users will still have access.
                        </div>

                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode"
                                           {{ $settings->maintenance_mode ? 'checked' : '' }}>
                                    <label class="form-check-label" for="maintenance_mode">
                                        <strong class="fs-5">Enable Maintenance Mode</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="maintenance_message" class="form-label">Maintenance Message</label>
                                <textarea class="form-control @error('maintenance_message') is-invalid @enderror"
                                          id="maintenance_message" name="maintenance_message" rows="4">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>
                                <small class="text-muted">Message displayed to visitors during maintenance</small>
                                @error('maintenance_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($settings->maintenance_mode)
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    <strong>Store is currently in maintenance mode!</strong>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 0.75rem 1.25rem;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    color: #5B914C;
    background-color: rgba(91, 145, 76, 0.1);
}

.nav-tabs .nav-link.active {
    color: #5B914C;
    background-color: rgba(91, 145, 76, 0.1);
    border-bottom: 3px solid #5B914C;
    font-weight: 600;
}

.btn-primary {
    background-color: #5B914C;
    border-color: #5B914C;
}

.btn-primary:hover {
    background-color: #4a7a3d;
    border-color: #4a7a3d;
}

.form-control:focus,
.form-select:focus {
    border-color: #5B914C;
    box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
}

.form-check-input:checked {
    background-color: #5B914C;
    border-color: #5B914C;
}

.alert {
    border-radius: 8px;
}

.img-thumbnail {
    border-radius: 8px;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencySelect = document.getElementById('currency_code');
    const symbolInput = document.getElementById('currency_symbol');
    const positionSelect = document.getElementById('currency_position');
    const decimalPlaces = document.getElementById('decimal_places');
    const thousandSep = document.getElementById('thousand_separator');
    const decimalSep = document.getElementById('decimal_separator');
    const pricePreview = document.getElementById('price-preview');

    // Update currency symbol when currency code changes
    if (currencySelect && symbolInput) {
        currencySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const symbol = selectedOption.getAttribute('data-symbol');
            if (symbol) {
                symbolInput.value = symbol;
                updatePreview();
            }
        });
    }

    // Update preview when any format setting changes
    [positionSelect, decimalPlaces, thousandSep, decimalSep].forEach(element => {
        if (element) {
            element.addEventListener('change', updatePreview);
        }
    });

    function updatePreview() {
        const symbol = symbolInput.value || '$';
        const position = positionSelect.value || 'left';
        const decimals = parseInt(decimalPlaces.value) || 2;
        const thousand = thousandSep.value || ',';
        const decimal = decimalSep.value || '.';

        // Format number 1234.56
        let amount = 1234.56;
        let formatted = amount.toFixed(decimals);

        // Split into integer and decimal parts
        let parts = formatted.split('.');

        // Add thousand separator
        if (thousand !== '') {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
        }

        // Join with decimal separator
        formatted = parts.join(decimal);

        // Apply currency position
        let result = '';
        switch(position) {
            case 'left':
                result = symbol + formatted;
                break;
            case 'right':
                result = formatted + symbol;
                break;
            case 'left_space':
                result = symbol + ' ' + formatted;
                break;
            case 'right_space':
                result = formatted + ' ' + symbol;
                break;
            default:
                result = symbol + formatted;
        }

        if (pricePreview) {
            pricePreview.textContent = result;
        }
    }
});
</script>
<script>
// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Handle tab activation based on session
@if(session('section'))
document.addEventListener('DOMContentLoaded', function() {
    const sectionTab = document.getElementById('{{ session("section") }}-tab');
    if (sectionTab) {
        const tab = new bootstrap.Tab(sectionTab);
        tab.show();
    }
});
@endif
</script>
{{-- Shipping Tab JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide calculation type specific sections
    const calculationType = document.getElementById('shipping_calculation_type');
    const sections = document.querySelectorAll('.calculation-section');

    function updateCalculationSection() {
        const selectedType = calculationType.value;

        // Hide all sections
        sections.forEach(section => {
            section.style.display = 'none';
        });

        // Show selected section
        const selectedSection = document.getElementById(selectedType + '_section');
        if (selectedSection) {
            selectedSection.style.display = 'block';
        }
    }

    calculationType.addEventListener('change', updateCalculationSection);

    // Initialize on page load
    updateCalculationSection();

    // Tiered Rates Management
    let tierIndex = {{ count($tieredRates ?? []) }};
    const tieredContainer = document.getElementById('tiered-rates-container');
    const addTierBtn = document.getElementById('add-tier-btn');
    const noTiersMessage = document.getElementById('no-tiers-message');

    // Add new tier
    addTierBtn?.addEventListener('click', function() {
        if (noTiersMessage) {
            noTiersMessage.remove();
        }

        const tierHtml = `
            <div class="tier-row card mb-3 border" data-tier-index="${tierIndex}">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Tier Type</label>
                            <select class="form-select tier-type" name="tiers[${tierIndex}][type]">
                                <option value="order_total">Order Total</option>
                                <option value="weight">Weight</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Minimum Threshold</label>
                            <input type="number" class="form-control tier-threshold"
                                   name="tiers[${tierIndex}][threshold]"
                                   value="0" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shipping Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $settings->currency_symbol }}</span>
                                <input type="number" class="form-control tier-rate"
                                       name="tiers[${tierIndex}][rate]"
                                       value="0" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-tier w-100">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        tieredContainer.insertAdjacentHTML('beforeend', tierHtml);
        tierIndex++;
    });

    // Remove tier
    tieredContainer?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-tier')) {
            const tierRow = e.target.closest('.tier-row');
            tierRow.remove();

            // Show "no tiers" message if all removed
            if (tieredContainer.children.length === 0) {
                tieredContainer.innerHTML = '<p class="text-muted" id="no-tiers-message">No tiers added yet. Click "Add Tier" to create one.</p>';
            }
        }
    });

    // Serialize tiered rates before form submission
    const shippingForm = document.getElementById('shippingForm');
    shippingForm?.addEventListener('submit', function(e) {
        const tiers = [];
        const tierRows = document.querySelectorAll('.tier-row');

        tierRows.forEach(row => {
            const type = row.querySelector('.tier-type')?.value;
            const threshold = row.querySelector('.tier-threshold')?.value;
            const rate = row.querySelector('.tier-rate')?.value;

            if (type && threshold && rate) {
                tiers.push({
                    type: type,
                    threshold: parseFloat(threshold),
                    rate: parseFloat(rate)
                });
            }
        });

        // Set JSON value
        const hiddenInput = document.getElementById('tiered_shipping_rates_input');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(tiers);
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle COD settings
    const codCheckbox = document.getElementById('enable_cod');
    const codSettings = document.getElementById('cod_settings');

    codCheckbox?.addEventListener('change', function() {
        codSettings.style.display = this.checked ? 'block' : 'none';
    });

    // Toggle Online Payment settings
    const onlineCheckbox = document.getElementById('enable_online_payment');
    const onlineSettings = document.getElementById('online_payment_settings');

    onlineCheckbox?.addEventListener('change', function() {
        onlineSettings.style.display = this.checked ? 'block' : 'none';
    });

    // Toggle Bank Transfer settings
    const bankCheckbox = document.getElementById('enable_bank_transfer');
    const bankSettings = document.getElementById('bank_transfer_settings');

    bankCheckbox?.addEventListener('change', function() {
        bankSettings.style.display = this.checked ? 'block' : 'none';
    });
});
</script>
@endsection
