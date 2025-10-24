@extends('admin.layouts.app')

@section('title', 'Store Settings')

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
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" role="tab">
                        <i class="bi bi-receipt me-2"></i>Tax
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">
                        <i class="bi bi-truck me-2"></i>Shipping
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="bi bi-boxes me-2"></i>Inventory
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                        <i class="bi bi-envelope me-2"></i>Email
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
                    <form action="{{ route('admin.settings.update-shipping') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i>Shipping Configuration</h5>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="shipping_enabled" name="shipping_enabled"
                                           {{ $settings->shipping_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shipping_enabled">
                                        <strong>Enable Shipping</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="default_shipping_cost" class="form-label">Default Shipping Cost <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('default_shipping_cost') is-invalid @enderror"
                                       id="default_shipping_cost" name="default_shipping_cost"
                                       value="{{ old('default_shipping_cost', $settings->default_shipping_cost) }}"
                                       min="0" step="0.01" required>
                                @error('default_shipping_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold</label>
                                <input type="number" class="form-control @error('free_shipping_threshold') is-invalid @enderror"
                                       id="free_shipping_threshold" name="free_shipping_threshold"
                                       value="{{ old('free_shipping_threshold', $settings->free_shipping_threshold) }}"
                                       min="0" step="0.01">
                                <small class="text-muted">Minimum order amount for free shipping (leave empty to disable)</small>
                                @error('free_shipping_threshold')
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

@endsection
