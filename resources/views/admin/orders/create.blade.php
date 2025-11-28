@extends('admin.layouts.app')

@section('title', 'Create New Order')

@push('styles')
<style>
    .order-item-card {
        border-left: 4px solid #0d6efd;
    }
    .order-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    .sticky-top {
        position: sticky;
        z-index: 1020;
    }
    #productSelect option {
        padding: 8px;
        font-size: 14px;
    }
</style>
@endpush
@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create New Order</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Create Order</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <form id="createOrderForm" method="POST" action="{{ route('admin.orders.store') }}">
        @csrf

        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-8">

                {{-- Customer Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-person-circle text-primary"></i> Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Customer Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="customer_type" id="existingCustomer" value="existing" checked>
                                <label class="btn btn-outline-primary" for="existingCustomer">
                                    <i class="bi bi-person-check"></i> Existing Customer
                                </label>

                                <input type="radio" class="btn-check" name="customer_type" id="guestCustomer" value="guest">
                                <label class="btn btn-outline-primary" for="guestCustomer">
                                    <i class="bi bi-person"></i> Guest Customer
                                </label>
                            </div>
                        </div>

                        {{-- Existing Customer --}}
                        <div id="existingCustomerSection">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Select Customer <span class="text-danger">*</span></label>
                                <select class="form-select" id="customer_id" name="customer_id">
                                    <option value="">Choose a customer...</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-email="{{ $customer->email }}"
                                            data-phone="{{ $customer->phone }}"
                                            data-company="{{ $customer->company_name }}"
                                            data-billing="{{ json_encode([
                                                'first_name' => $customer->first_name,
                                                'last_name' => $customer->last_name,
                                                'company' => $customer->company_name,
                                                'address_line1' => $customer->billing_address_line1,
                                                'address_line2' => $customer->billing_address_line2,
                                                'city' => $customer->billing_city,
                                                'state' => $customer->billing_state,
                                                'postal_code' => $customer->billing_postal_code,
                                                'country' => $customer->billing_country,
                                                'phone' => $customer->phone
                                            ]) }}"
                                            data-shipping="{{ json_encode([
                                                'first_name' => $customer->first_name,
                                                'last_name' => $customer->last_name,
                                                'company' => $customer->company_name,
                                                'address_line1' => $customer->shipping_address_line1,
                                                'address_line2' => $customer->shipping_address_line2,
                                                'city' => $customer->shipping_city,
                                                'state' => $customer->shipping_state,
                                                'postal_code' => $customer->shipping_postal_code,
                                                'country' => $customer->shipping_country,
                                                'phone' => $customer->phone
                                            ]) }}">
                                        {{ $customer->getFullName() }} - {{ $customer->email }}
                                        @if($customer->company_name)
                                            ({{ $customer->company_name }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-success" id="addCustomerBtn" title="Add New Customer">
                                <i class="bi bi-plus-circle"></i> Add Customer
                            </button>
                            <small class="text-muted">Or click "Add Customer" to create a new customer</small>
                        </div>

                        {{-- Guest Customer --}}
                        <div id="guestCustomerSection" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="guest_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_name" name="guest_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="guest_email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="guest_email" name="guest_email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="guest_phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="guest_phone" name="guest_phone">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-cart text-primary"></i> Order Items</h5>
                            <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                                <i class="bi bi-plus-circle"></i> Add Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- ✅ NEW: Tax Summary Header --}}
                        <div class="alert alert-info mb-3 d-none" id="taxSummaryAlert">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Taxable Items</small>
                                    <strong id="taxableItemsCount">0</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Total Tax (Inclusive)</small>
                                    <strong id="inclusiveTaxTotal" class="text-success">{{ store_currency_symbol() }}0.00</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Total Tax (Exclusive)</small>
                                    <strong id="exclusiveTaxTotal" class="text-danger">{{ store_currency_symbol() }}0.00</strong>
                                </div>
                            </div>
                        </div>

                        <div id="orderItemsContainer">
                            {{-- Items will be added dynamically --}}
                        </div>

                        <div class="alert alert-info d-none" id="noItemsAlert">
                            <i class="bi bi-info-circle"></i> No items added yet. Click "Add Item" to start.
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-geo-alt text-primary"></i> Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_first_name" name="shipping_first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_last_name" name="shipping_last_name" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_address_line1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_address_line1" name="shipping_address_line1" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_address_line2" class="form-label">Address Line 2</label>
                                <input type="text" class="form-control" id="shipping_address_line2" name="shipping_address_line2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_country" name="shipping_country" value="" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="shipping_phone" name="shipping_phone">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Address --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-credit-card text-primary"></i> Billing Address</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="billing_same_as_shipping" name="billing_same_as_shipping" value="1" checked>
                                <label class="form-check-label" for="billing_same_as_shipping">
                                    Same as shipping address
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="billingAddressSection" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="billing_first_name" name="billing_first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="billing_last_name" name="billing_last_name">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="billing_address_line1" class="form-label">Address Line 1</label>
                                <input type="text" class="form-control" id="billing_address_line1" name="billing_address_line1">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="billing_address_line2" class="form-label">Address Line 2</label>
                                <input type="text" class="form-control" id="billing_address_line2" name="billing_address_line2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="billing_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="billing_city" name="billing_city">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="billing_state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="billing_state" name="billing_state">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="billing_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="billing_postal_code" name="billing_postal_code">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="billing_country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="billing_country" name="billing_country" value="United States">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-sticky text-primary"></i> Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customer_notes" class="form-label">Customer Notes</label>
                            <textarea class="form-control" id="customer_notes" name="customer_notes" rows="3" placeholder="Notes from customer..."></textarea>
                        </div>
                        <div class="mb-0">
                            <label for="admin_notes" class="form-label">Admin Notes (Internal)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Internal notes..."></textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">

                {{-- Order Summary --}}
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        {{-- Subtotal (excluding all taxes) --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="summarySubtotal">{{ store_currency_symbol() }}0.00</strong>
                        </div>

                        {{-- ✅ NEW: Product-level inclusive tax (already in price) --}}
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>
                                <i class="bi bi-info-circle" title="Tax already included in product prices"></i>
                                Tax (Inclusive):
                            </span>
                            <strong id="summaryInclusiveTax">{{ store_currency_symbol() }}0.00</strong>
                        </div>

                        {{-- ✅ NEW: Product-level exclusive tax (to be added) --}}
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>
                                <i class="bi bi-plus-circle" title="Tax to be added to total"></i>
                                Tax (Exclusive):
                            </span>
                            <strong id="summaryExclusiveTax">{{ store_currency_symbol() }}0.00</strong>
                        </div>

                        {{-- Order-level tax (admin override) --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                Additional Tax (<span id="taxRateDisplay">0</span>%):
                            </span>
                            <strong id="summaryAdditionalTax">{{ store_currency_symbol() }}0.00</strong>
                        </div>

                        {{-- Shipping --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong id="summaryShipping">{{ store_currency_symbol() }}0.00</strong>
                        </div>

                        {{-- Discount --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <strong class="text-danger" id="summaryDiscount">-{{ store_currency_symbol() }}0.00</strong>
                        </div>

                        <hr>

                        {{-- ✅ ENHANCED: Total breakdown --}}
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Base Amount:</small>
                            <small class="text-muted" id="summaryBaseAmount">{{ store_currency_symbol() }}0.00</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">All Taxes:</small>
                            <small class="text-muted" id="summaryTotalTax">{{ store_currency_symbol() }}0.00</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Grand Total:</h5>
                            <h5 class="mb-0 text-primary" id="summaryTotal">{{ store_currency_symbol() }}0.00</h5>
                        </div>
                    </div>
                </div>

                {{-- Shipping & Tax --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-truck text-primary"></i> Shipping & Tax</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_method" class="form-label">Shipping Method</label>
                            <select class="form-select" id="shipping_method" name="shipping_method">
                                <option value="standard">Standard Shipping</option>
                                <option value="express">Express Shipping</option>
                                <option value="overnight">Overnight Shipping</option>
                                <option value="pickup">Store Pickup</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="currency" name="currency" required>
                                @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_amount" class="form-label">Shipping Amount ({{ store_currency_symbol() }})</label>
                            <input type="number" class="form-control" id="shipping_amount" name="shipping_amount" value="0" min="0" step="0.01">
                        </div>
                        <div class="mb-0">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" value="0" min="0" max="100" step="0.01">
                        </div>
                    </div>
                </div>

                {{-- ✅ NEW: Discount & Coupon Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-tag text-primary"></i> Discount & Coupon</h5>
                    </div>
                    <div class="card-body">
                        {{-- Coupon Code Input --}}
                        <div class="mb-3">
                            <label for="coupon_code_input" class="form-label">
                                Coupon Code <small class="text-muted">(Optional)</small>
                            </label>
                            <div class="input-group">
                                <input type="text"
                                    class="form-control"
                                    id="coupon_code_input"
                                    placeholder="Enter coupon code"
                                    style="text-transform: uppercase;">
                                <button type="button"
                                        class="btn btn-outline-primary"
                                        id="applyCouponBtn">
                                    <i class="bi bi-check-circle"></i> Apply
                                </button>
                                <button type="button"
                                        class="btn btn-outline-danger d-none"
                                        id="removeCouponBtn">
                                    <i class="bi bi-x-circle"></i> Remove
                                </button>
                            </div>
                            <small class="text-muted">Enter a valid coupon code to get discount</small>
                        </div>

                        {{-- Coupon Success Info --}}
                        <div id="couponInfo" class="mb-3 d-none">
                            <div class="alert alert-success mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <strong id="couponName"></strong> applied
                                        <br>
                                        <small>Discount: <span id="couponDiscount"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Manual Discount Amount --}}
                        <div class="mb-3">
                            <label for="discount_amount" class="form-label">
                                Manual Discount Amount
                                <small class="text-muted">({{ store_currency_symbol() }})</small>
                            </label>
                            <input type="number"
                                class="form-control"
                                id="discount_amount"
                                name="discount_amount"
                                value="0"
                                min="0"
                                step="0.01"
                                placeholder="0.00">
                            <small class="text-muted">Or enter manual discount</small>
                        </div>

                        {{-- Hidden Fields for Coupon Data --}}
                        <input type="hidden" name="coupon_id" id="coupon_id">
                        <input type="hidden" name="discount_code" id="discount_code">

                        {{-- Info Alert --}}
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>Note:</strong> Applying a coupon will override manual discount. Remove the coupon to use manual discount.
                        </div>
                    </div>
                </div>

                {{-- Order Status --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-flag text-primary"></i> Order Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status_key_code" class="form-label">Order Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status_key_code" name="status_key_code" required>
                                @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ $status->is_default ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_status_key_code" class="form-label">Payment Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_status_key_code" name="payment_status_key_code" required>
                                @foreach($paymentStatusList as $status)
                                <option value="{{ $status->key_code }}" {{ $status->is_default ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">Select...</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="check">Cheque</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Create Order
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>

{{-- Add Item Modal --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Order Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Search Product</label>
                    <input type="text" class="form-control" id="productSearch" placeholder="Search by name or SKU...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Product <span class="text-danger">*</span></label>
                    <select class="form-select" id="productSelect" size="10">
                        @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-sku="{{ $product->sku }}"
                                data-price="{{ $product->getFinalPrice() }}"
                                data-stock="{{ $product->stock_quantity }}"
                                data-image="{{ $product->getMainImageUrl() }}"
                                data-has-variants="{{ $product->has_variants ? 'true' : 'false' }}">
                            {{ $product->name }} - {{ $product->sku }}
                            @if($product->has_variants)
                                <span class="badge bg-info">Has Variants</span>
                            @else
                                (Stock: {{ $product->stock_quantity }})
                            @endif
                            - {{ store_currency_symbol() }} {{ number_format($product->getFinalPrice(), 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- ✅ NEW: Variant Selection Section --}}
                <div id="variantSection" style="display: none;">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> This product has variants. Please select one below.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Variant <span class="text-danger">*</span></label>
                        <select class="form-select" id="variantSelect" size="5">
                            <option value="">Loading variants...</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="itemQuantity" value="1" min="1">
                        <small class="text-muted" id="stockInfo"></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Price ({{ store_currency_symbol() }}) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="itemPrice" step="0.01" min="0">
                    </div>
                </div>

                {{-- ✅ NEW: Selected Variant Preview --}}
                <div id="selectedVariantPreview" style="display: none;" class="alert alert-success">
                    <div class="d-flex align-items-center">
                        <img id="variantPreviewImage" src="" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        <div>
                            <strong id="variantPreviewName"></strong><br>
                            <small class="text-muted">SKU: <span id="variantPreviewSku"></span></small><br>
                            <small class="text-muted">Stock: <span id="variantPreviewStock"></span></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddItem" style="background-color: #5B914C; border-color: #5B914C;">
                    <i class="bi bi-check-circle"></i> Add Item
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Customer Modal --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="bi bi-person-plus-fill"></i> Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCustomerForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        {{-- Personal Information --}}
                        <input type="hidden" name="request_from" value="modal">
                        <input type="hidden" name="status_key_code" value="CUSTOMER_ACTIVE">
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-person"></i> Personal Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_first_name" name="first_name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_last_name" name="last_name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="modal_email" name="email" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="modal_phone" name="phone">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_customer_type" class="form-label">Customer Type</label>
                            <select class="form-select" id="modal_customer_type" name="customer_type">
                                <option value="individual">Individual</option>
                                <option value="business">Business</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="modal_company_name" name="company_name">
                        </div>

                        {{-- Billing Address --}}
                        <div class="col-12 mb-3 mt-3">
                            <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-geo-alt"></i> Billing Address</h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_billing_address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="modal_billing_address_line1" name="billing_address_line1">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_billing_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="modal_billing_address_line2" name="billing_address_line2">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_billing_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="modal_billing_city" name="billing_city">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_billing_state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="modal_billing_state" name="billing_state">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_billing_postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="modal_billing_postal_code" name="billing_postal_code">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_billing_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="modal_billing_country" name="billing_country" value="Pakistan">
                        </div>

                        {{-- Shipping Address --}}
                        <div class="col-12 mb-3 mt-3">
                            <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-truck"></i> Shipping Address</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="sameAsBilling">
                                <label class="form-check-label" for="sameAsBilling">
                                    Same as billing address
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_shipping_address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="modal_shipping_address_line1" name="shipping_address_line1">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_shipping_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="modal_shipping_address_line2" name="shipping_address_line2">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_shipping_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="modal_shipping_city" name="shipping_city">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_shipping_state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="modal_shipping_state" name="shipping_state">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_shipping_postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="modal_shipping_postal_code" name="shipping_postal_code">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_shipping_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="modal_shipping_country" name="shipping_country" value="">
                        </div>

                        {{-- Additional Options --}}
                        <div class="col-12 mb-3 mt-3">
                            <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-gear"></i> Additional Options</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal_is_newsletter_subscribed" name="is_newsletter_subscribed" value="1">
                                <label class="form-check-label" for="modal_is_newsletter_subscribed">
                                    Subscribe to newsletter
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal_is_verified" name="is_verified" value="1" checked>
                                <label class="form-check-label" for="modal_is_verified">
                                    Mark as verified
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="modal_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="saveCustomerBtn">
                        <i class="bi bi-check-circle"></i> Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let orderItems = [];
    let itemCounter = 0;
    let selectedVariant = null;
    const currencySymbol = '{{ store_currency_symbol() }}';
    // ============================================================
    // ENHANCED ITEM STRUCTURE WITH TAX INFO
    // ============================================================
    function createItemStructure(productData, variantData, quantity, price) {
        return {
            id: itemCounter++,
            product_id: productData.id,
            variant_id: variantData ? variantData.id : null,
            name: variantData
                ? `${productData.name} - ${variantData.name}`
                : productData.name,
            sku: variantData ? variantData.sku : productData.sku,
            image: variantData ? variantData.image : productData.image,
            quantity: quantity,
            unit_price: price,
            subtotal: quantity * price,
            has_variant: variantData !== null,
            max_stock: variantData ? variantData.stock : productData.stock,

            // ✅ TAX INFORMATION
            is_taxable: productData.is_taxable || false,
            tax_type: productData.tax_type || 'exclusive',
            tax_rate: productData.tax_rate || 0,
            tax_amount: 0, // Calculated below
            base_price_excluding_tax: 0, // Calculated below
            price_including_tax: 0 // Calculated below
        };
    }

    // ✅ CALCULATE ITEM TAX
    function calculateItemTax(item) {
        if (!item.is_taxable || item.tax_rate <= 0) {
            item.tax_amount = 0;
            item.base_price_excluding_tax = item.unit_price;
            item.price_including_tax = item.unit_price;
            return item;
        }

        const taxRate = item.tax_rate / 100;

        if (item.tax_type === 'inclusive') {
            // Tax already in price - extract it
            item.base_price_excluding_tax = item.unit_price / (1 + taxRate);
            item.tax_amount = item.unit_price - item.base_price_excluding_tax;
            item.price_including_tax = item.unit_price;
        } else {
            // Tax to be added
            item.base_price_excluding_tax = item.unit_price;
            item.tax_amount = item.unit_price * taxRate;
            item.price_including_tax = item.unit_price + item.tax_amount;
        }

        // Total tax for quantity
        item.tax_amount = item.tax_amount * item.quantity;

        return item;
    }

    // ✅ LOAD PRODUCT TAX INFO VIA AJAX
    function loadProductTaxInfo(productId, callback) {
        $.ajax({
            url: `/admin/orders/products/${productId}/tax-info`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    callback(response.tax_info);
                } else {
                    callback({
                        is_taxable: false,
                        tax_type: 'exclusive',
                        tax_rate: 0
                    });
                }
            },
            error: function() {
                callback({
                    is_taxable: false,
                    tax_type: 'exclusive',
                    tax_rate: 0
                });
            }
        });
    }
    //-------------------
    // Toggle Customer Type
    $('input[name="customer_type"]').on('change', function() {
        if ($(this).val() === 'existing') {
            $('#existingCustomerSection').show();
            $('#guestCustomerSection').hide();
            $('#guest_name, #guest_email').removeAttr('required');
            $('#customer_id').attr('required', 'required');
        } else {
            $('#existingCustomerSection').hide();
            $('#guestCustomerSection').show();
            $('#guest_name, #guest_email').attr('required', 'required');
            $('#customer_id').removeAttr('required');
        }
    });

    //Customer Selection - Auto-fill ALL addresses
    $('#customer_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');

        if (selectedOption.val()) {
            const shippingData = selectedOption.data('shipping');
            const billingData = selectedOption.data('billing');

            // ============================================================
            // AUTO-FILL SHIPPING ADDRESS
            // ============================================================
            if (shippingData) {
                $('#shipping_first_name').val(shippingData.first_name || '');
                $('#shipping_last_name').val(shippingData.last_name || '');
                $('#shipping_company').val(shippingData.company || ''); // If you have this field
                $('#shipping_address_line1').val(shippingData.address_line1 || '');
                $('#shipping_address_line2').val(shippingData.address_line2 || '');
                $('#shipping_city').val(shippingData.city || '');
                $('#shipping_state').val(shippingData.state || '');
                $('#shipping_postal_code').val(shippingData.postal_code || '');
                $('#shipping_country').val(shippingData.country || 'United States');
                $('#shipping_phone').val(shippingData.phone || '');
            }

            // ============================================================
            // AUTO-FILL BILLING ADDRESS (if not same as shipping)
            // ============================================================
            if (!$('#billing_same_as_shipping').is(':checked') && billingData) {
                $('#billing_first_name').val(billingData.first_name || '');
                $('#billing_last_name').val(billingData.last_name || '');
                $('#billing_company').val(billingData.company || ''); // If you have this field
                $('#billing_address_line1').val(billingData.address_line1 || '');
                $('#billing_address_line2').val(billingData.address_line2 || '');
                $('#billing_city').val(billingData.city || '');
                $('#billing_state').val(billingData.state || '');
                $('#billing_postal_code').val(billingData.postal_code || '');
                $('#billing_country').val(billingData.country || 'United States');
                $('#billing_phone').val(billingData.phone || '');
            }

            // ============================================================
            // VISUAL FEEDBACK
            // ============================================================
            // Show success indicator
            Swal.fire({
                icon: 'success',
                title: 'Customer Selected',
                text: 'Shipping and billing addresses loaded!',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

            // Highlight filled fields briefly
            $('.form-control[id^="shipping_"], .form-control[id^="billing_"]').each(function() {
                if ($(this).val()) {
                    $(this).addClass('bg-light border-success');
                    setTimeout(() => {
                        $(this).removeClass('bg-light border-success');
                    }, 2000);
                }
            });
        } else {
            // Customer deselected - clear fields
            clearAddressFields();
        }
    });

    // ✅ NEW: Helper function to clear address fields
    function clearAddressFields() {
        $('#shipping_first_name, #shipping_last_name, #shipping_address_line1, #shipping_address_line2, #shipping_city, #shipping_state, #shipping_postal_code, #shipping_country, #shipping_phone').val('');
        $('#billing_first_name, #billing_last_name, #billing_address_line1, #billing_address_line2, #billing_city, #billing_state, #billing_postal_code, #billing_country, #billing_phone').val('');
    }

    // Toggle Billing Address
    $('#billing_same_as_shipping').on('change', function() {
        if ($(this).is(':checked')) {
            $('#billingAddressSection').slideUp();
            $('#billingAddressSection input').removeAttr('required');
        } else {
            $('#billingAddressSection').slideDown();
        }
    });

    // Add Item Button
    $('#addItemBtn').on('click', function() {
        $('#addItemModal').modal('show');
        $('#productSearch').val('');
        $('#productSelect option').show();
        $('#productSelect').val('');
        $('#variantSection').hide();
        $('#selectedVariantPreview').hide();
        $('#itemQuantity').val(1);
        $('#itemPrice').val('');
        $('#stockInfo').text('');
        selectedVariant = null;
    });

    // Product Search
    $('#productSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        $('#productSelect option').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ============================================================
    // PRODUCT SELECTION - LOAD VARIANTS + TAX INFO
    // ============================================================
    $('#productSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price');
        const stock = selectedOption.data('stock');
        const hasVariants = selectedOption.data('has-variants') === true || selectedOption.data('has-variants') === 'true';
        const productId = selectedOption.val();

        selectedVariant = null;
        $('#itemPrice').val(price);
        $('#itemQuantity').val(1);

        if (!productId) {
            $('#taxInfoPreview').addClass('d-none');
            return;
        }

        // ✅ LOAD TAX INFO
        loadProductTaxInfo(productId, function(taxInfo) {
            displayTaxPreview(taxInfo, price);
        });

        if (hasVariants && productId) {
            $('#variantSection').show();
            $('#variantSelect').html('<option value="">Loading variants...</option>');
            $('#stockInfo').text('');
            $('#selectedVariantPreview').hide();

            $.ajax({
                url: `/admin/orders/products/${productId}/variants`,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.variants.length > 0) {
                        let variantOptions = '<option value="">Select a variant...</option>';

                        response.variants.forEach(variant => {
                            const stockBadge = variant.is_in_stock
                                ? `(Stock: ${variant.stock})`
                                : '(Out of Stock)';

                            variantOptions += `
                                <option value="${variant.id}"
                                        data-name="${variant.name}"
                                        data-sku="${variant.sku}"
                                        data-price="${variant.price}"
                                        data-stock="${variant.stock}"
                                        data-image="${variant.image}"
                                        ${!variant.is_in_stock ? 'disabled' : ''}>
                                    ${variant.name} - ${variant.sku} ${stockBadge} - ${variant.formatted_price}
                                </option>
                            `;
                        });

                        $('#variantSelect').html(variantOptions);
                    } else {
                        $('#variantSelect').html('<option value="">No variants available</option>');
                    }
                },
                error: function() {
                    $('#variantSelect').html('<option value="">Failed to load variants</option>');
                }
            });
        } else {
            $('#variantSection').hide();
            $('#selectedVariantPreview').hide();
            const stockColor = stock > 10 ? 'success' : (stock > 0 ? 'warning' : 'danger');
            $('#stockInfo').html(`<i class="bi bi-box-seam text-${stockColor}"></i> Available stock: <strong class="text-${stockColor}">${stock}</strong>`);
        }
    });

    // ✅ DISPLAY TAX PREVIEW IN MODAL
    function displayTaxPreview(taxInfo, price) {
        const $preview = $('#taxInfoPreview');

        if (!taxInfo.is_taxable) {
            $preview.html(`
                <div class="alert alert-secondary mb-0">
                    <i class="bi bi-x-circle"></i> <strong>Non-Taxable</strong> - No tax applies to this product
                </div>
            `).removeClass('d-none');
            return;
        }

        const taxRate = taxInfo.tax_rate / 100;
        let taxAmount, basePrice, totalPrice;

        if (taxInfo.tax_type === 'inclusive') {
            basePrice = price / (1 + taxRate);
            taxAmount = price - basePrice;
            totalPrice = price;
        } else {
            basePrice = price;
            taxAmount = price * taxRate;
            totalPrice = price + taxAmount;
        }

        const badgeClass = taxInfo.tax_type === 'inclusive' ? 'bg-success' : 'bg-danger';
        const icon = taxInfo.tax_type === 'inclusive' ? 'check-circle' : 'plus-circle';

        $preview.html(`
            <div class="alert alert-light border mb-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="bi bi-receipt"></i> <strong>Tax Info</strong></span>
                    <span class="badge ${badgeClass}">
                        <i class="bi bi-${icon}"></i> ${taxInfo.tax_rate}% ${taxInfo.tax_type.toUpperCase()}
                    </span>
                </div>
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Base Price:</td>
                        <td class="text-end"><strong>${currencySymbol}${basePrice.toFixed(2)}</strong></td>
                    </tr>
                    <tr class="${taxInfo.tax_type === 'inclusive' ? 'text-success' : 'text-danger'}">
                        <td>Tax Amount:</td>
                        <td class="text-end"><strong>${currencySymbol}${taxAmount.toFixed(2)}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Total Price:</strong></td>
                        <td class="text-end"><strong>${currencySymbol}${totalPrice.toFixed(2)}</strong></td>
                    </tr>
                </table>
                ${taxInfo.tax_type === 'inclusive'
                    ? '<small class="text-muted"><i class="bi bi-info-circle"></i> Tax is already included in the price</small>'
                    : '<small class="text-muted"><i class="bi bi-exclamation-triangle"></i> Tax will be added at checkout</small>'
                }
            </div>
        `).removeClass('d-none');
    }

    // Variant Selection - Update Price and Show Preview
    $('#variantSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');

        if (selectedOption.val()) {
            selectedVariant = {
                id: selectedOption.val(),
                name: selectedOption.data('name'),
                sku: selectedOption.data('sku'),
                price: selectedOption.data('price'),
                stock: selectedOption.data('stock'),
                image: selectedOption.data('image')
            };

            // Update price
            $('#itemPrice').val(selectedVariant.price);

            // Update stock info with color coding
            const stockColor = selectedVariant.stock > 10 ? 'success' : (selectedVariant.stock > 0 ? 'warning' : 'danger');
            $('#stockInfo').html(`<i class="bi bi-box-seam text-${stockColor}"></i> Available stock: <strong class="text-${stockColor}">${selectedVariant.stock}</strong>`);

            // Show preview
            $('#variantPreviewImage').attr('src', selectedVariant.image);
            $('#variantPreviewName').text(selectedVariant.name);
            $('#variantPreviewSku').text(selectedVariant.sku);
            $('#variantPreviewStock').text(selectedVariant.stock);
            $('#selectedVariantPreview').slideDown();
        } else {
            selectedVariant = null;
            $('#selectedVariantPreview').hide();
            $('#stockInfo').text('');
        }
    });

    // ✅ REAL-TIME QUANTITY VALIDATION
    $('#itemQuantity').on('input', function() {
        const quantity = parseInt($(this).val()) || 0;
        const selectedProduct = $('#productSelect option:selected');
        const hasVariants = selectedProduct.data('has-variants') === true || selectedProduct.data('has-variants') === 'true';

        let availableStock = 0;

        if (hasVariants && selectedVariant) {
            availableStock = selectedVariant.stock;
        } else if (!hasVariants) {
            availableStock = selectedProduct.data('stock');
        }

        if (quantity > availableStock) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).after(`<div class="invalid-feedback d-block">Requested quantity (${quantity}) exceeds available stock (${availableStock})</div>`);
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // ============================================================
    // ADD ITEM WITH TAX INFO
    // ============================================================
    $('#confirmAddItem').on('click', function() {
        const selectedProduct = $('#productSelect option:selected');

        if (!selectedProduct.val()) {
            Swal.fire({
                icon: 'warning',
                title: 'No Product Selected',
                text: 'Please select a product first.'
            });
            return;
        }

        const hasVariants = selectedProduct.data('has-variants') === true || selectedProduct.data('has-variants') === 'true';

        if (hasVariants && !selectedVariant) {
            Swal.fire({
                icon: 'warning',
                title: 'No Variant Selected',
                text: 'This product has variants. Please select a variant.'
            });
            return;
        }

        const quantity = parseInt($('#itemQuantity').val());
        const price = parseFloat($('#itemPrice').val());

        if (quantity < 1 || price < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Values',
                text: 'Please enter valid quantity and price.'
            });
            return;
        }

        // Stock validation (existing code)
        let availableStock = 0;
        let stockSource = '';

        if (hasVariants && selectedVariant) {
            availableStock = selectedVariant.stock;
            stockSource = `${selectedProduct.data('name')} (${selectedVariant.name})`;
        } else {
            availableStock = selectedProduct.data('stock');
            stockSource = selectedProduct.data('name');
        }

        const productId = selectedProduct.val();
        const variantId = selectedVariant ? selectedVariant.id : null;

        let existingQuantity = 0;
        orderItems.forEach(item => {
            if (item.product_id === productId && item.variant_id === variantId) {
                existingQuantity += item.quantity;
            }
        });

        const totalRequestedQuantity = existingQuantity + quantity;

        if (totalRequestedQuantity > availableStock) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Stock',
                html: `
                    <p><strong>${stockSource}</strong></p>
                    <hr>
                    <table class="table table-sm">
                        <tr>
                            <td class="text-start">Available Stock:</td>
                            <td class="text-end"><strong>${availableStock}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-start">Already in Order:</td>
                            <td class="text-end"><strong>${existingQuantity}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-start">Trying to Add:</td>
                            <td class="text-end"><strong>${quantity}</strong></td>
                        </tr>
                        <tr class="table-danger">
                            <td class="text-start"><strong>Total Needed:</strong></td>
                            <td class="text-end"><strong>${totalRequestedQuantity}</strong></td>
                        </tr>
                    </table>
                `,
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        // Duplicate check (existing code)
        let duplicateIndex = -1;
        orderItems.forEach((item, index) => {
            if (item.product_id === productId && item.variant_id === variantId) {
                duplicateIndex = index;
            }
        });

        if (duplicateIndex !== -1) {
            const existingItem = orderItems[duplicateIndex];
            const newTotalQty = existingItem.quantity + quantity;

            Swal.fire({
                icon: 'question',
                title: 'Product Already Added',
                html: `
                    <p><strong>${existingItem.name}</strong> is already in the order.</p>
                    <hr>
                    <table class="table table-sm">
                        <tr>
                            <td class="text-start">Current Quantity:</td>
                            <td class="text-end"><strong>${existingItem.quantity}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-start">Adding:</td>
                            <td class="text-end"><strong>+${quantity}</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td class="text-start"><strong>New Quantity:</strong></td>
                            <td class="text-end"><strong>${newTotalQty}</strong></td>
                        </tr>
                    </table>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yes, Increase Quantity',
                cancelButtonText: 'No, Cancel',
                confirmButtonColor: '#5B914C'
            }).then((result) => {
                if (result.isConfirmed) {
                    orderItems[duplicateIndex].quantity = newTotalQty;
                    orderItems[duplicateIndex].subtotal = newTotalQty * orderItems[duplicateIndex].unit_price;
                    orderItems[duplicateIndex] = calculateItemTax(orderItems[duplicateIndex]);

                    renderOrderItems();
                    calculateTotals();

                    $('#addItemModal').modal('hide');
                    resetAddItemModal();

                    Swal.fire({
                        icon: 'success',
                        title: 'Quantity Updated!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
            return;
        }

        // ✅ CREATE NEW ITEM WITH TAX
        loadProductTaxInfo(productId, function(taxInfo) {
            const productData = {
                id: productId,
                name: selectedProduct.data('name'),
                sku: selectedProduct.data('sku'),
                image: selectedProduct.data('image'),
                stock: availableStock,
                is_taxable: taxInfo.is_taxable,
                tax_type: taxInfo.tax_type,
                tax_rate: taxInfo.tax_rate
            };

            const variantData = selectedVariant ? {
                id: selectedVariant.id,
                name: selectedVariant.name,
                sku: selectedVariant.sku,
                image: selectedVariant.image,
                stock: selectedVariant.stock
            } : null;

            let item = createItemStructure(productData, variantData, quantity, price);
            item = calculateItemTax(item);

            orderItems.push(item);
            renderOrderItems();
            calculateTotals();

            $('#addItemModal').modal('hide');
            resetAddItemModal();

            Swal.fire({
                icon: 'success',
                title: 'Item Added!',
                text: `${item.name} added to order`,
                timer: 2000,
                showConfirmButton: false
            });
        });
    });

    // Helper function to reset modal
    function resetAddItemModal() {
        $('#itemQuantity').val(1);
        $('#itemPrice').val('');
        $('#productSelect').val('');
        $('#variantSelect').val('');
        $('#variantSection').hide();
        $('#selectedVariantPreview').hide();
        $('#taxInfoPreview').addClass('d-none');
        $('#itemQuantity').removeClass('is-invalid');
        $('#itemQuantity').next('.invalid-feedback').remove();
        selectedVariant = null;
    }

    // ============================================================
    // RENDER ORDER ITEMS WITH TAX INFO
    // ============================================================
    function renderOrderItems() {
        if (orderItems.length === 0) {
            $('#orderItemsContainer').html('');
            $('#noItemsAlert').removeClass('d-none');
            $('#taxSummaryAlert').addClass('d-none');
            return;
        }

        $('#noItemsAlert').addClass('d-none');
        $('#taxSummaryAlert').removeClass('d-none');

        let html = '';

        orderItems.forEach((item, index) => {
            const taxBadge = item.is_taxable
                ? `<span class="badge ${item.tax_type === 'inclusive' ? 'bg-success' : 'bg-danger'} badge-sm">
                    ${item.tax_rate}% ${item.tax_type === 'inclusive' ? 'INC' : 'EXC'}
                   </span>`
                : '<span class="badge bg-secondary badge-sm">No Tax</span>';

            html += `
                <div class="card order-item-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="${item.image}" class="order-item-image" alt="${item.name}">
                            </div>
                            <div class="col">
                                <h6 class="mb-1">
                                    ${item.name}
                                    ${item.has_variant ? '<span class="badge bg-info badge-sm ms-1">Variant</span>' : ''}
                                    ${taxBadge}
                                </h6>
                                <small class="text-muted">SKU: ${item.sku}</small>
                                <br>
                                <small class="text-muted">Max Stock: <strong>${item.max_stock}</strong></small>
                                ${item.is_taxable ? `
                                    <br>
                                    <small class="text-muted">
                                        Tax: ${currencySymbol}${item.tax_amount.toFixed(2)}
                                        ${item.tax_type === 'inclusive' ? '(included)' : '(extra)'}
                                    </small>
                                ` : ''}
                            </div>
                            <div class="col-auto text-center">
                                <small class="text-muted d-block">Quantity</small>
                                <input type="number" class="form-control form-control-sm item-quantity"
                                       data-index="${index}"
                                       data-max="${item.max_stock}"
                                       value="${item.quantity}"
                                       min="1"
                                       max="${item.max_stock}"
                                       style="width: 80px;">
                            </div>
                            <div class="col-auto text-center">
                                <small class="text-muted d-block">Unit Price</small>
                                <input type="number" class="form-control form-control-sm item-price"
                                       data-index="${index}" value="${item.unit_price}" min="0" step="0.01" style="width: 100px;">
                            </div>
                            <div class="col-auto text-end">
                                <small class="text-muted d-block">Subtotal</small>
                                <strong class="item-subtotal">${currencySymbol}${item.subtotal.toFixed(2)}</strong>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        ${item.variant_id ? `<input type="hidden" name="items[${index}][variant_id]" value="${item.variant_id}">` : ''}
                        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}" class="hidden-quantity-${index}">
                        <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}" class="hidden-price-${index}">
                    </div>
                </div>
            `;
        });

        $('#orderItemsContainer').html(html);
    }

    // ============================================================
    // UPDATE ITEM QUANTITY WITH TAX RECALCULATION
    // ============================================================
    $(document).on('change', '.item-quantity', function() {
        const index = $(this).data('index');
        const maxStock = $(this).data('max');
        let newQuantity = parseInt($(this).val());

        if (newQuantity < 1) {
            $(this).val(1);
            newQuantity = 1;
        }

        if (newQuantity > maxStock) {
            Swal.fire({
                icon: 'error',
                title: 'Exceeds Available Stock',
                html: `Available stock: <strong>${maxStock}</strong>`,
                confirmButtonColor: '#dc3545'
            });

            $(this).val(maxStock);
            newQuantity = maxStock;
        }

        orderItems[index].quantity = newQuantity;
        orderItems[index].subtotal = orderItems[index].quantity * orderItems[index].unit_price;
        orderItems[index] = calculateItemTax(orderItems[index]);

        $(`.hidden-quantity-${index}`).val(newQuantity);
        $(this).closest('.card-body').find('.item-subtotal').text(currencySymbol + orderItems[index].subtotal.toFixed(2));

        calculateTotals();
    });

    // Update Item Price
    $(document).on('change', '.item-price', function() {
        const index = $(this).data('index');
        const newPrice = parseFloat($(this).val());

        if (newPrice < 0) {
            $(this).val(0);
            return;
        }

        orderItems[index].unit_price = newPrice;
        orderItems[index].subtotal = orderItems[index].quantity * orderItems[index].unit_price;
        orderItems[index] = calculateItemTax(orderItems[index]);

        $(`.hidden-price-${index}`).val(newPrice);
        $(this).closest('.card-body').find('.item-subtotal').text(currencySymbol + orderItems[index].subtotal.toFixed(2));

        calculateTotals();
    });

    // Remove Item
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');

        Swal.fire({
            icon: 'warning',
            title: 'Remove Item?',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                orderItems.splice(index, 1);
                renderOrderItems();
                calculateTotals();
            }
        });
    });

    // Update Item Price
    $(document).on('change', '.item-price', function() {
        const index = $(this).data('index');
        const newPrice = parseFloat($(this).val());

        if (newPrice < 0) {
            $(this).val(0);
            return;
        }

        orderItems[index].unit_price = newPrice;
        orderItems[index].subtotal = orderItems[index].quantity * orderItems[index].unit_price;

        $(`.hidden-price-${index}`).val(newPrice);
        $(this).closest('.card-body').find('.item-subtotal').text(currencySymbol + orderItems[index].subtotal.toFixed(2));

        calculateTotals();
    });

    // Remove Item
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');

        Swal.fire({
            icon: 'warning',
            title: 'Remove Item?',
            text: 'Are you sure you want to remove this item?',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                orderItems.splice(index, 1);
                renderOrderItems();
                calculateTotals();
            }
        });
    });

    // Calculate Totals
    let currentCurrency = currencySymbol;
    $('#currency').on('change', function() {
        const selected = $(this).find('option:selected').text();
        const match = selected.match(/\(([^)]+)\)/);
        if (match) {
            currentCurrency = match[1];
        }
        calculateTotals();
    });

    // ============================================================
    // CALCULATE TOTALS WITH PRODUCT-LEVEL TAX
    // ============================================================
    function calculateTotals() {
        let subtotal = 0;
        let inclusiveTaxTotal = 0;
        let exclusiveTaxTotal = 0;
        let taxableItemsCount = 0;

        orderItems.forEach(item => {
            subtotal += item.subtotal;

            if (item.is_taxable) {
                taxableItemsCount++;
                if (item.tax_type === 'inclusive') {
                    inclusiveTaxTotal += item.tax_amount;
                } else {
                    exclusiveTaxTotal += item.tax_amount;
                }
            }
        });

        // Update tax summary
        $('#taxableItemsCount').text(taxableItemsCount);
        $('#inclusiveTaxTotal').text(currencySymbol + inclusiveTaxTotal.toFixed(2));
        $('#exclusiveTaxTotal').text(currencySymbol + exclusiveTaxTotal.toFixed(2));

        // Order-level additional tax (admin override)
        const additionalTaxRate = parseFloat($('#tax_rate').val()) || 0;
        const shippingAmount = parseFloat($('#shipping_amount').val()) || 0;
        const discountAmount = parseFloat($('#discount_amount').val()) || 0;

        const additionalTaxAmount = subtotal * (additionalTaxRate / 100);

        // Total tax = inclusive (already in price) + exclusive (to add) + additional (admin)
        const totalTax = inclusiveTaxTotal + exclusiveTaxTotal + additionalTaxAmount;

        // Final total
        const total = Math.max(0, subtotal + exclusiveTaxTotal + additionalTaxAmount + shippingAmount - discountAmount);

        // Update summary
        $('#summarySubtotal').text(currencySymbol + subtotal.toFixed(2));
        $('#summaryInclusiveTax').text(currencySymbol + inclusiveTaxTotal.toFixed(2));
        $('#summaryExclusiveTax').text(currencySymbol + exclusiveTaxTotal.toFixed(2));
        $('#summaryAdditionalTax').text(currencySymbol + additionalTaxAmount.toFixed(2));
        $('#summaryShipping').text(currencySymbol + shippingAmount.toFixed(2));
        $('#summaryDiscount').text('-' + currencySymbol + discountAmount.toFixed(2));

        $('#summaryBaseAmount').text(currencySymbol + (subtotal - inclusiveTaxTotal).toFixed(2));
        $('#summaryTotalTax').text(currencySymbol + totalTax.toFixed(2));
        $('#summaryTotal').text(currencySymbol + total.toFixed(2));
        $('#taxRateDisplay').text(additionalTaxRate);
    }

    // Recalculate on changes
    $('#tax_rate, #shipping_amount, #discount_amount').on('input', function() {
        calculateTotals();
    });

    // Form Submission
    $('#createOrderForm').on('submit', function(e) {
        e.preventDefault();

        if (orderItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items',
                text: 'Please add at least one item to the order.'
            });
            return;
        }

        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                    $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Create Order');
                }
            },
            error: function(xhr) {
                Swal.close();
                $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Create Order');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, messages) {
                        const input = $(`[name="${key}"]`);
                        const feedback = input.closest('.mb-3').find('.invalid-feedback');

                        input.addClass('is-invalid');

                        if (feedback.length) {
                            feedback.text(messages[0]).show();
                        } else {
                            input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                        }
                    });

                    const firstError = $('.is-invalid').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: 'Please check the form:<br>' +
                            Object.values(errors).flat().map(err => `• ${err}`).join('<br>')
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to create order'
                    });
                }
            }
        });
    });

    // Add Customer Modal
    $('#addCustomerBtn').on('click', function() {
        $('#addCustomerModal').modal('show');
    });

    $('#sameAsBilling').on('change', function() {
        if ($(this).is(':checked')) {
            $('#modal_shipping_address_line1').val($('#modal_billing_address_line1').val());
            $('#modal_shipping_address_line2').val($('#modal_billing_address_line2').val());
            $('#modal_shipping_city').val($('#modal_billing_city').val());
            $('#modal_shipping_state').val($('#modal_billing_state').val());
            $('#modal_shipping_postal_code').val($('#modal_billing_postal_code').val());
            $('#modal_shipping_country').val($('#modal_billing_country').val());
        }
    });

    // Submit Add Customer Form
    $('#addCustomerForm').on('submit', function(e) {
        e.preventDefault();

        const saveBtn = $('#saveCustomerBtn');
        const originalText = saveBtn.html();

        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '{{ route("admin.customers.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Customer Created!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const newCustomer = response.customer;
                    const newOption = new Option(
                        `${newCustomer.first_name} ${newCustomer.last_name} - ${newCustomer.email}`,
                        newCustomer.id,
                        true,
                        true
                    );

                    $(newOption).attr({
                        'data-email': newCustomer.email,
                        'data-phone': newCustomer.phone || '',
                        'data-billing': JSON.stringify({
                            first_name: newCustomer.first_name,
                            last_name: newCustomer.last_name,
                            address_line1: newCustomer.billing_address_line1 || '',
                            address_line2: newCustomer.billing_address_line2 || '',
                            city: newCustomer.billing_city || '',
                            state: newCustomer.billing_state || '',
                            postal_code: newCustomer.billing_postal_code || '',
                            country: newCustomer.billing_country || ''
                        }),
                        'data-shipping': JSON.stringify({
                            first_name: newCustomer.first_name,
                            last_name: newCustomer.last_name,
                            address_line1: newCustomer.shipping_address_line1 || '',
                            address_line2: newCustomer.shipping_address_line2 || '',
                            city: newCustomer.shipping_city || '',
                            state: newCustomer.shipping_state || '',
                            postal_code: newCustomer.shipping_postal_code || '',
                            country: newCustomer.shipping_country || ''
                        })
                    });

                    $('#customer_id').append(newOption).trigger('change');

                    $('#addCustomerModal').modal('hide');
                    $('#addCustomerForm')[0].reset();

                    if (newCustomer.shipping_address_line1) {
                        $('#shipping_first_name').val(newCustomer.first_name);
                        $('#shipping_last_name').val(newCustomer.last_name);
                        $('#shipping_address_line1').val(newCustomer.shipping_address_line1);
                        $('#shipping_address_line2').val(newCustomer.shipping_address_line2);
                        $('#shipping_city').val(newCustomer.shipping_city);
                        $('#shipping_state').val(newCustomer.shipping_state);
                        $('#shipping_postal_code').val(newCustomer.shipping_postal_code);
                        $('#shipping_country').val(newCustomer.shipping_country);
                        $('#shipping_phone').val(newCustomer.phone);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to create customer'
                    });
                }

                saveBtn.prop('disabled', false).html(originalText);
            },
            error: function(xhr) {
                let errorMessage = 'Failed to create customer.';

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

                saveBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#addCustomerModal').on('hidden.bs.modal', function() {
        $('#addCustomerForm')[0].reset();
        $('#sameAsBilling').prop('checked', false);
    });

    // ============================================================
    // COUPON & DISCOUNT MANAGEMENT (Keep existing code)
    // ============================================================
    let appliedCoupon = null;

    $('#coupon_code_input').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    $('#applyCouponBtn').on('click', function() {
        const couponCode = $('#coupon_code_input').val().trim().toUpperCase();

        if (!couponCode) {
            showCouponMessage({
                icon: 'warning',
                title: 'No Code Entered',
                message: 'Please enter a coupon code',
                type: 'warning'
            });
            return;
        }

        if (orderItems.length === 0) {
            showCouponMessage({
                icon: 'warning',
                title: 'Empty Order',
                message: 'Please add items to the order first',
                type: 'warning'
            });
            return;
        }

        const cartItems = [];
        let subtotal = 0;

        orderItems.forEach(item => {
            cartItems.push({
                product_id: item.product_id,
                variant_id: item.variant_id || null,
                quantity: item.quantity,
                unit_price: item.unit_price
            });
            subtotal += item.subtotal;
        });

        const customerType = $('input[name="customer_type"]:checked').val();
        const customerId = customerType === 'existing' ? $('#customer_id').val() : null;
        const guestEmail = customerType === 'guest' ? $('#guest_email').val() : null;

        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Validating...');

        $.ajax({
            url: '{{ route("admin.orders.validate-coupon") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                coupon_code: couponCode,
                customer_id: customerId,
                guest_email: guestEmail,
                cart_items: cartItems,
                subtotal: subtotal
            },
            success: function(response) {
                if (response.success) {
                    appliedCoupon = response.data;

                    $('#couponInfo').removeClass('d-none');
                    $('#couponName').text(response.data.coupon_name);
                    $('#couponDiscount').text(response.data.formatted_discount);

                    if (response.data.description) {
                        $('#couponDescription').text(response.data.description).removeClass('d-none');
                    }

                    $('#coupon_id').val(response.data.coupon_id);
                    $('#discount_code').val(response.data.coupon_code);
                    $('#discount_amount').val(response.data.discount_amount).prop('readonly', true);

                    $('#applyCouponBtn').addClass('d-none');
                    $('#removeCouponBtn').removeClass('d-none');

                    calculateTotals();

                    showCouponMessage({
                        icon: response.icon || 'success',
                        title: 'Coupon Applied!',
                        message: response.message,
                        type: response.message_type || 'success',
                        timer: 3000
                    });
                } else {
                    handleCouponError(response);
                }

                btn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;

                if (response) {
                    // Check if it's a validation error (status 422)
                    if (xhr.status === 422 && response.errors) {
                        handleValidationErrors(response.errors, response.message);
                    } else {
                        handleCouponError(response);
                    }
                } else {
                    showCouponMessage({
                        icon: 'error',
                        title: 'Connection Error',
                        message: 'Failed to connect to the server. Please check your internet connection.',
                        type: 'error'
                    });
                }

                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Helper function to display coupon messages
    function showCouponMessage(options) {
        const config = {
            icon: options.icon || 'info',
            title: options.title || 'Notice',
            showConfirmButton: options.timer ? false : true,
            timer: options.timer || null,
            customClass: {
                popup: 'coupon-message-popup',
                title: `coupon-message-${options.type || 'info'}`
            }
        };

        // Use html or text based on what's provided
        if (options.html) {
            config.html = options.html;
        } else {
            config.text = options.message || '';
        }

        Swal.fire(config);
    }

    // NEW: Helper function to handle validation errors
    function handleValidationErrors(errors, mainMessage = 'Validation Error') {
        let errorHtml = '<div class="text-left">';
        errorHtml += '<p class="mb-3"><strong>' + mainMessage + '</strong></p>';
        errorHtml += '<ul class="list-unstyled mb-0">';

        // Loop through each field's errors
        Object.keys(errors).forEach(function(field) {
            const fieldErrors = errors[field];

            // Format field name (e.g., guest_email -> Guest Email)
            const fieldName = field.split('_').map(word =>
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');

            fieldErrors.forEach(function(error) {
                errorHtml += `<li class="mb-2">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <strong>${fieldName}:</strong> ${error}
                </li>`;
            });
        });

        errorHtml += '</ul></div>';

        Swal.fire({
            icon: 'error',
            title: 'Validation Failed',
            html: errorHtml,
            confirmButtonText: 'OK',
            customClass: {
                popup: 'coupon-message-popup',
                title: 'coupon-message-error',
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    }

    // Helper function to handle coupon errors
    function handleCouponError(response) {
        let title = 'Coupon Error';
        let message = response.message || 'Failed to validate coupon';
        let icon = response.icon || 'error';
        let htmlContent = null;

        // Handle specific error types
        switch (response.message_type) {
            case 'not_found':
                title = 'Invalid Coupon';
                break;

            case 'expired':
                title = 'Coupon Expired';
                break;

            case 'customer_ineligible':
                title = 'Not Eligible';
                if (response.details) {
                    htmlContent = `<p>${message}</p><div class="mt-2 text-muted small">${response.details}</div>`;
                }
                break;

            case 'cart_requirements_not_met':
                title = 'Requirements Not Met';
                htmlContent = '<div class="text-left">';
                htmlContent += `<p>${message}</p>`;

                // Add requirement details if available
                if (response.requirements) {
                    const req = response.requirements;
                    htmlContent += '<div class="alert alert-info mt-3 mb-0 small">';

                    if (req.minimum_amount && req.current_amount < req.minimum_amount) {
                        const currency = '{{ store_currency_symbol() }}';
                        htmlContent += `<div class="mb-2">
                            <strong>Minimum Amount Required:</strong> ${currency} ${parseFloat(req.minimum_amount).toFixed(2)}<br>
                            <strong>Current Cart Amount:</strong> ${currency} ${parseFloat(req.current_amount).toFixed(2)}<br>
                            <strong>Need to add:</strong> ${currency} ${(parseFloat(req.minimum_amount) - parseFloat(req.current_amount)).toFixed(2)}
                        </div>`;
                    }

                    if (req.minimum_items && req.current_items < req.minimum_items) {
                        htmlContent += `<div>
                            <strong>Minimum Items Required:</strong> ${req.minimum_items}<br>
                            <strong>Current Items in Cart:</strong> ${req.current_items}<br>
                            <strong>Need to add:</strong> ${req.minimum_items - req.current_items} more item(s)
                        </div>`;
                    }

                    htmlContent += '</div>';
                }
                htmlContent += '</div>';
                break;

            case 'validation_error':
                // This case is now handled by handleValidationErrors function
                if (response.errors) {
                    handleValidationErrors(response.errors, message);
                    return; // Exit early since we're using a different handler
                }
                title = 'Validation Error';
                break;

            case 'system_error':
                title = 'System Error';
                message = 'An unexpected error occurred. Please try again or contact support.';
                break;
        }

        showCouponMessage({
            icon: icon,
            title: title,
            html: htmlContent || null,
            message: htmlContent ? null : message,
            type: response.message_type || 'error'
        });
    }

    // Optional: Add custom CSS for better styling
    const couponMessageStyles = `
        <style>
        .coupon-message-popup {
            border-radius: 8px;
        }
        .coupon-message-success {
            color: #5B914C !important;
        }
        .coupon-message-error {
            color: #dc3545 !important;
        }
        .coupon-message-warning {
            color: #ffc107 !important;
        }
        .coupon-message-info {
            color: #17a2b8 !important;
        }
        .swal2-html-container {
            text-align: left !important;
        }
        .swal2-html-container ul {
            padding-left: 0;
        }
        .swal2-html-container .list-unstyled li {
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-left: 3px solid #dc3545;
            border-radius: 4px;
        }
        </style>
        `;

    // Inject styles into the page
    if (!document.getElementById('coupon-message-styles')) {
        const styleElement = document.createElement('div');
        styleElement.id = 'coupon-message-styles';
        styleElement.innerHTML = couponMessageStyles;
        document.head.appendChild(styleElement);
    }

    $('#removeCouponBtn').on('click', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Remove Coupon?',
            text: 'Are you sure you want to remove this coupon?',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                appliedCoupon = null;
                $('#coupon_code_input').val('');
                $('#couponInfo').addClass('d-none');
                $('#coupon_id').val('');
                $('#discount_code').val('');
                $('#discount_amount').val(0).prop('readonly', false);

                $('#applyCouponBtn').removeClass('d-none');
                $('#removeCouponBtn').addClass('d-none');

                calculateTotals();

                Swal.fire({
                    icon: 'success',
                    title: 'Coupon Removed',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    $('#discount_amount').on('input', function() {
        if (!$(this).prop('readonly')) {
            calculateTotals();
        }
    });

    // Initialize
    $('#noItemsAlert').removeClass('d-none');
    calculateTotals();
});
</script>
@endpush
