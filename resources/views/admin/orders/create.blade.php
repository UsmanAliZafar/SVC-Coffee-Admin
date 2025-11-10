@extends('admin.layouts.app')

@section('title', 'Create New Order')

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
                                            data-billing="{{ json_encode([
                                                'first_name' => $customer->first_name,
                                                'last_name' => $customer->last_name,
                                                'address_line1' => $customer->billing_address_line1,
                                                'address_line2' => $customer->billing_address_line2,
                                                'city' => $customer->billing_city,
                                                'state' => $customer->billing_state,
                                                'postal_code' => $customer->billing_postal_code,
                                                'country' => $customer->billing_country
                                            ]) }}"
                                            data-shipping="{{ json_encode([
                                                'first_name' => $customer->first_name,
                                                'last_name' => $customer->last_name,
                                                'address_line1' => $customer->shipping_address_line1,
                                                'address_line2' => $customer->shipping_address_line2,
                                                'city' => $customer->shipping_city,
                                                'state' => $customer->shipping_state,
                                                'postal_code' => $customer->shipping_postal_code,
                                                'country' => $customer->shipping_country
                                            ]) }}">
                                        {{ $customer->getFullName() }} - {{ $customer->email }}
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
                                <input type="text" class="form-control" id="shipping_country" name="shipping_country" value="United States" required>
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
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="summarySubtotal">{{ store_currency_symbol() }}0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (<span id="taxRateDisplay">0</span>%):</span>
                            <strong id="summaryTax">{{ store_currency_symbol() }}0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong id="summaryShipping">{{ store_currency_symbol() }}0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <strong class="text-danger" id="summaryDiscount">-{{ store_currency_symbol() }}0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Total:</h5>
                            <h5 class="mb-0 text-primary" id="summaryTotal">{{ store_currency_symbol() }}0.00</h5>
                        </div>
                    </div>
                </div>

                {{-- Shipping & Discount --}}
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
                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" value="0" min="0" max="100" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="discount_code" class="form-label">Discount Code</label>
                            <input type="text" class="form-control" id="discount_code" name="discount_code">
                        </div>
                        <div class="mb-0">
                            <label for="discount_amount" class="form-label">Discount Amount ({{ store_currency_symbol() }})</label>
                            <input type="number" class="form-control" id="discount_amount" name="discount_amount" value="0" min="0" step="0.01">
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
            <div class="modal-header">
                <h5 class="modal-title">Add Order Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                data-image="{{ $product->getMainImageUrl() }}">
                            {{ $product->name }} - {{ $product->sku }} (Stock: {{ $product->stock_quantity }}) - {{ store_currency_symbol() }} {{ number_format($product->getFinalPrice(), 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="itemQuantity" value="1" min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Price ({{ store_currency_symbol() }}) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="itemPrice" step="0.01" min="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddItem">Add Item</button>
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
                            <input type="text" class="form-control" id="modal_shipping_country" name="shipping_country" value="Pakistan">
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

@push('scripts')
<script>
$(document).ready(function() {
    let orderItems = [];
    let itemCounter = 0;

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

    // Customer Selection - Auto-fill addresses
    $('#customer_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');

        if (selectedOption.val()) {
            const shippingData = selectedOption.data('shipping');
            const billingData = selectedOption.data('billing');

            // Fill shipping address
            if (shippingData) {
                $('#shipping_first_name').val(shippingData.first_name || '');
                $('#shipping_last_name').val(shippingData.last_name || '');
                $('#shipping_address_line1').val(shippingData.address_line1 || '');
                $('#shipping_address_line2').val(shippingData.address_line2 || '');
                $('#shipping_city').val(shippingData.city || '');
                $('#shipping_state').val(shippingData.state || '');
                $('#shipping_postal_code').val(shippingData.postal_code || '');
                $('#shipping_country').val(shippingData.country || 'United States');
            }

            // Fill billing address if checkbox is not checked
            if (!$('#billing_same_as_shipping').is(':checked') && billingData) {
                $('#billing_first_name').val(billingData.first_name || '');
                $('#billing_last_name').val(billingData.last_name || '');
                $('#billing_address_line1').val(billingData.address_line1 || '');
                $('#billing_address_line2').val(billingData.address_line2 || '');
                $('#billing_city').val(billingData.city || '');
                $('#billing_state').val(billingData.state || '');
                $('#billing_postal_code').val(billingData.postal_code || '');
                $('#billing_country').val(billingData.country || 'United States');
            }
        }
    });

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

    // Product Selection - Auto-fill price
    $('#productSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price');
        $('#itemPrice').val(price);
    });

    // Confirm Add Item
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

        const item = {
            id: itemCounter++,
            product_id: selectedProduct.val(),
            name: selectedProduct.data('name'),
            sku: selectedProduct.data('sku'),
            image: selectedProduct.data('image'),
            quantity: quantity,
            unit_price: price,
            subtotal: quantity * price
        };

        orderItems.push(item);
        renderOrderItems();
        calculateTotals();

        $('#addItemModal').modal('hide');
        $('#itemQuantity').val(1);
        $('#itemPrice').val('');
        $('#productSelect').val('');
    });

    // Render Order Items
    function renderOrderItems() {
        if (orderItems.length === 0) {
            $('#orderItemsContainer').html('');
            $('#noItemsAlert').removeClass('d-none');
            return;
        }

        $('#noItemsAlert').addClass('d-none');
        let html = '';

        orderItems.forEach((item, index) => {
            html += `
                <div class="card order-item-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="${item.image}" class="order-item-image" alt="${item.name}">
                            </div>
                            <div class="col">
                                <h6 class="mb-1">${item.name}</h6>
                                <small class="text-muted">SKU: ${item.sku}</small>
                            </div>
                            <div class="col-auto text-center">
                                <small class="text-muted d-block">Quantity</small>
                                <input type="number" class="form-control form-control-sm item-quantity"
                                       data-index="${index}" value="${item.quantity}" min="1" style="width: 80px;">
                            </div>
                            <div class="col-auto text-center">
                                <small class="text-muted d-block">Unit Price</small>
                                <input type="number" class="form-control form-control-sm item-price"
                                       data-index="${index}" value="${item.unit_price}" min="0" step="0.01" style="width: 100px;">
                            </div>
                            <div class="col-auto text-end">
                                <small class="text-muted d-block">Subtotal</small>
                                <strong class="item-subtotal">{{ store_currency_symbol() }}${item.subtotal.toFixed(2)}</strong>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}" class="hidden-quantity-${index}">
                        <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}" class="hidden-price-${index}">
                    </div>
                </div>
            `;
        });

        $('#orderItemsContainer').html(html);
    }

    // Update Item Quantity
    $(document).on('change', '.item-quantity', function() {
        const index = $(this).data('index');
        const newQuantity = parseInt($(this).val());

        if (newQuantity < 1) {
            $(this).val(1);
            return;
        }

        orderItems[index].quantity = newQuantity;
        orderItems[index].subtotal = orderItems[index].quantity * orderItems[index].unit_price;

        $(`.hidden-quantity-${index}`).val(newQuantity);
        $(this).closest('.card-body').find('.item-subtotal').text('$' + orderItems[index].subtotal.toFixed(2));

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

        $(`.hidden-price-${index}`).val(newPrice);
        $(this).closest('.card-body').find('.item-subtotal').text('$' + orderItems[index].subtotal.toFixed(2));

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
    let currentCurrency = '{{ store_currency_symbol() }}';
    $('#currency').on('change', function() {
        const selected = $(this).find('option:selected').text();
        currentCurrency = selected.match(/\(([^)]+)\)/)[1]; // Extract symbol from parentheses
        calculateTotals();
    });
    // Calculate Totals
    function calculateTotals() {
        let subtotal = 0;

        orderItems.forEach(item => {
            subtotal += item.subtotal;
        });

        const taxRate = parseFloat($('#tax_rate').val()) || 0;
        const shippingAmount = parseFloat($('#shipping_amount').val()) || 0;
        const discountAmount = parseFloat($('#discount_amount').val()) || 0;

        const taxAmount = subtotal * (taxRate / 100);
        const total = subtotal + taxAmount + shippingAmount - discountAmount;

        $('#summarySubtotal').text(currentCurrency + subtotal.toFixed(2));
        $('#summaryTax').text(currentCurrency + taxAmount.toFixed(2));
        $('#summaryShipping').text(currentCurrency + shippingAmount.toFixed(2));
        $('#summaryDiscount').text('-' + currentCurrency + discountAmount.toFixed(2));
        $('#summaryTotal').text(currentCurrency + total.toFixed(2));
        $('#taxRateDisplay').text(taxRate);
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

        // Disable submit button
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        // Submit form via AJAX
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

                // ✅ FIX: Re-enable button immediately
                $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Create Order');

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, messages) {
                        const input = $(`[name="${key}"]`);
                        const feedback = input.closest('.mb-3').find('.invalid-feedback');

                        input.addClass('is-invalid');

                        if (feedback.length) {
                            feedback.text(messages[0]).show();
                        } else {
                            // Create feedback element if it doesn't exist
                            input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                        }
                    });

                    // Scroll to first error
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

    // Initialize
    $('#noItemsAlert').removeClass('d-none');
    calculateTotals();

    $('#addCustomerBtn').on('click', function() {
        $('#addCustomerModal').modal('show');
    });

    // Copy Billing to Shipping Address
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

        // Disable button and show loading
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

                    // Add new customer to dropdown
                    const newCustomer = response.customer;
                    const newOption = new Option(
                        `${newCustomer.first_name} ${newCustomer.last_name} - ${newCustomer.email}`,
                        newCustomer.id,
                        true,
                        true
                    );

                    // Set data attributes for auto-fill
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

                    // Close modal and reset form
                    $('#addCustomerModal').modal('hide');
                    $('#addCustomerForm')[0].reset();

                    // Auto-fill shipping address if data exists
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

                // Re-enable button
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

                // Re-enable button
                saveBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Reset modal form when closed
    $('#addCustomerModal').on('hidden.bs.modal', function() {
        $('#addCustomerForm')[0].reset();
        $('#sameAsBilling').prop('checked', false);
    });
});
</script>
@endpush
