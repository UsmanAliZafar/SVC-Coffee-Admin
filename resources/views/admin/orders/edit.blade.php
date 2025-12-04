@extends('admin.layouts.app')

@section('title', 'Edit Order #' . $order->order_number)

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Order #{{ $order->order_number }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Order
            </a>
        </div>
    </div>

    <form id="editOrderForm" method="POST" action="{{ route('admin.orders.update', $order->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-8">

                {{-- Customer Information --}}
                {{-- Customer Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle text-primary"></i> Customer Information</h5>
                            @if(!$order->customer)
                            <button type="button" class="btn btn-sm btn-success" id="addCustomerBtn" title="Add New Customer">
                                <i class="bi bi-plus-circle"></i> Add Customer
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($order->customer)
                            {{-- Existing Customer (Display Only) --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Customer Name</label>
                                    <p class="mb-0">{{ $order->getCustomerName() }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <p class="mb-0">{{ $order->getCustomerEmail() }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone</label>
                                    <p class="mb-0">{{ $order->getCustomerPhone() ?: '—' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Customer Type</label>
                                    <p class="mb-0">
                                        <span class="badge bg-primary">Registered Customer</span>
                                        <a href="{{ route('admin.customers.show', $order->customer_id) }}" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="bi bi-eye"></i> View Profile
                                        </a>
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Guest Customer - Can be upgraded to registered customer --}}
                            <div id="guestCustomerInfo">
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle"></i> This order was placed by a guest customer.
                                    You can convert them to a registered customer by clicking "Add Customer" above.
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Guest Name</label>
                                        <p class="mb-0">{{ $order->guest_name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Guest Email</label>
                                        <p class="mb-0">{{ $order->guest_email }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Guest Phone</label>
                                        <p class="mb-0">{{ $order->guest_phone ?: '—' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Customer Type</label>
                                        <p class="mb-0">
                                            <span class="badge bg-secondary">Guest</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden field for linking customer after creation --}}
                            <input type="hidden" name="customer_id" id="linked_customer_id" value="">
                        @endif
                    </div>
                </div>

                {{-- Order Items (Editable) --}}
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
                            {{-- Items will be rendered here --}}
                        </div>

                        <div class="alert alert-info d-none" id="noItemsAlert">
                            <i class="bi bi-info-circle"></i> No items in order. Click "Add Item" to add products.
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
                                <input type="text" class="form-control" id="shipping_first_name" name="shipping_first_name"
                                       value="{{ old('shipping_first_name', $order->shipping_first_name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_last_name" name="shipping_last_name"
                                       value="{{ old('shipping_last_name', $order->shipping_last_name) }}" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_address_line1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_address_line1" name="shipping_address_line1"
                                       value="{{ old('shipping_address_line1', $order->shipping_address_line1) }}" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_address_line2" class="form-label">Address Line 2</label>
                                <input type="text" class="form-control" id="shipping_address_line2" name="shipping_address_line2"
                                       value="{{ old('shipping_address_line2', $order->shipping_address_line2) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city"
                                       value="{{ old('shipping_city', $order->shipping_city) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state"
                                       value="{{ old('shipping_state', $order->shipping_state) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code"
                                       value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_country" name="shipping_country"
                                       value="{{ old('shipping_country', $order->shipping_country) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="shipping_phone" name="shipping_phone"
                                       value="{{ old('shipping_phone', $order->shipping_phone) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shipping Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-truck text-primary"></i> Shipping Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_method" class="form-label">Shipping Method</label>
                                <select class="form-select" id="shipping_method" name="shipping_method">
                                    <option value="standard" {{ $order->shipping_method === 'standard' ? 'selected' : '' }}>Standard Shipping</option>
                                    <option value="express" {{ $order->shipping_method === 'express' ? 'selected' : '' }}>Express Shipping</option>
                                    <option value="overnight" {{ $order->shipping_method === 'overnight' ? 'selected' : '' }}>Overnight Shipping</option>
                                    <option value="pickup" {{ $order->shipping_method === 'pickup' ? 'selected' : '' }}>Store Pickup</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_carrier" class="form-label">Carrier</label>
                                <input type="text" class="form-control" id="shipping_carrier" name="shipping_carrier"
                                       value="{{ old('shipping_carrier', $order->shipping_carrier) }}" placeholder="e.g., FedEx, UPS">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_tracking_number" class="form-label">Tracking Number</label>
                                <input type="text" class="form-control" id="shipping_tracking_number" name="shipping_tracking_number"
                                       value="{{ old('shipping_tracking_number', $order->shipping_tracking_number) }}"
                                       placeholder="Enter tracking number">
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
                            <textarea class="form-control" id="customer_notes" name="customer_notes" rows="3"
                                      placeholder="Notes from customer...">{{ old('customer_notes', $order->customer_notes) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"
                                      placeholder="Internal admin notes...">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label for="internal_notes" class="form-label">Internal Notes</label>
                            <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3"
                                      placeholder="Private internal notes...">{{ old('internal_notes', $order->internal_notes) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">

                {{-- Order Summary --}}
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header text-white py-3" style="background-color: #5B914C;">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="summarySubtotal">{{ $order->currency }} 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (<span id="taxRateDisplay">0</span>%):</span>
                            <strong id="summaryTax">{{ $order->currency }} 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong id="summaryShipping">{{ $order->currency }} 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Discount:</span>
                            <strong id="summaryDiscount">-{{ $order->currency }} 0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Total:</h5>
                            <h5 class="mb-0" style="color: #5B914C;" id="summaryTotal">{{ $order->currency }} 0.00</h5>
                        </div>
                    </div>
                </div>

                {{-- Order Settings --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-gear text-primary"></i> Order Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status_key_code" class="form-label">Order Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status_key_code" name="status_key_code" required>
                                @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ $order->status_key_code === $status->key_code ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="payment_status_key_code" class="form-label">Payment Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_status_key_code" name="payment_status_key_code" required>
                                @foreach($paymentStatusList as $status)
                                <option value="{{ $status->key_code }}" {{ $order->payment_status_key_code === $status->key_code ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">Select...</option>
                                <option value="credit_card" {{ $order->payment_method === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="debit_card" {{ $order->payment_method === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                <option value="paypal" {{ $order->payment_method === 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="bank_transfer" {{ $order->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cash" {{ $order->payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="check" {{ $order->payment_method === 'check' ? 'selected' : '' }}>Check</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="currency" name="currency" required>
                                @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" {{ $order->currency === $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_amount" class="form-label">Shipping Amount ({{ store_currency_symbol() }})</label>
                            <input type="number" class="form-control" id="shipping_amount" name="shipping_amount"
                                value="{{ $order->shipping_amount }}" min="0" step="0.01">
                        </div>

                        <div class="mb-0 d-none">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate"
                                value="{{ $order->tax_rate ?? 0 }}" min="0" max="100" step="0.01">
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
                                    value="{{ old('discount_code', $order->discount_code) }}"
                                    placeholder="Enter coupon code"
                                    style="text-transform: uppercase;">
                                <button type="button"
                                        class="btn btn-outline-primary {{ $order->discount_code ? 'd-none' : '' }}"
                                        id="applyCouponBtn">
                                    <i class="bi bi-check-circle"></i> Apply
                                </button>
                                <button type="button"
                                        class="btn btn-outline-danger {{ $order->discount_code ? '' : 'd-none' }}"
                                        id="removeCouponBtn">
                                    <i class="bi bi-x-circle"></i> Remove
                                </button>
                            </div>
                            <small class="text-muted">Enter a valid coupon code to get discount</small>
                        </div>

                        {{-- Coupon Success Info --}}
                        <div id="couponInfo" class="mb-3 {{ $order->discount_code ? '' : 'd-none' }}">
                            <div class="alert alert-success mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <strong id="couponName">{{ $order->discount_code }}</strong> applied
                                        <br>
                                        <small>Discount: <span id="couponDiscount">{{ store_currency_symbol() }} {{ number_format($order->discount_amount, 2) }}</span></small>
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
                                value="{{ old('discount_amount', $order->discount_amount) }}"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                {{ $order->discount_code ? 'readonly' : '' }}>
                            <small class="text-muted">Or enter manual discount</small>
                        </div>

                        {{-- Hidden Fields for Coupon Data --}}
                        <input type="hidden" name="coupon_id" id="coupon_id" value="">
                        <input type="hidden" name="discount_code" id="discount_code" value="{{ $order->discount_code }}">

                        {{-- Info Alert --}}
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>Note:</strong> Applying a coupon will override manual discount. Remove the coupon to use manual discount.
                        </div>
                    </div>
                </div>
                {{-- Action Buttons --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Update Order
                        </button>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary w-100">
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
                        <input type="number" class="form-control" id="itemPrice" step="0.01" min="0" readonly>
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

{{-- Add Customer Modal (Same as Create) --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="bi bi-person-plus-fill"></i> Convert Guest to Registered Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCustomerForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle"></i> Creating this customer will link them to this order.
                    </div>
                    <div class="row">
                        {{-- Personal Information --}}
                        <input type="hidden" name="request_from" value="modal">
                        <input type="hidden" name="status_key_code" value="CUSTOMER_ACTIVE">
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-person"></i> Personal Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_first_name" name="first_name"
                                   value="{{ $order->guest_name ? explode(' ', $order->guest_name)[0] : '' }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_last_name" name="last_name"
                                   value="{{ $order->guest_name ? (count(explode(' ', $order->guest_name)) > 1 ? implode(' ', array_slice(explode(' ', $order->guest_name), 1)) : '') : '' }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="modal_email" name="email"
                                   value="{{ $order->guest_email ?? '' }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="modal_phone" name="phone"
                                   value="{{ $order->guest_phone ?? '' }}">
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
                            <input type="text" class="form-control" id="modal_billing_address_line1" name="billing_address_line1"
                                   value="{{ $order->billing_address_line1 ?? '' }}">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_billing_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="modal_billing_address_line2" name="billing_address_line2"
                                   value="{{ $order->billing_address_line2 ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_billing_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="modal_billing_city" name="billing_city"
                                   value="{{ $order->billing_city ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_billing_state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="modal_billing_state" name="billing_state"
                                   value="{{ $order->billing_state ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_billing_postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="modal_billing_postal_code" name="billing_postal_code"
                                   value="{{ $order->billing_postal_code ?? '' }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_billing_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="modal_billing_country" name="billing_country"
                                   value="{{ $order->billing_country ?? 'Pakistan' }}">
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
                            <input type="text" class="form-control" id="modal_shipping_address_line1" name="shipping_address_line1"
                                   value="{{ $order->shipping_address_line1 ?? '' }}">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="modal_shipping_address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="modal_shipping_address_line2" name="shipping_address_line2"
                                   value="{{ $order->shipping_address_line2 ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_shipping_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="modal_shipping_city" name="shipping_city"
                                   value="{{ $order->shipping_city ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_shipping_state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="modal_shipping_state" name="shipping_state"
                                   value="{{ $order->shipping_state ?? '' }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="modal_shipping_postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="modal_shipping_postal_code" name="shipping_postal_code"
                                   value="{{ $order->shipping_postal_code ?? '' }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_shipping_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="modal_shipping_country" name="shipping_country"
                                   value="{{ $order->shipping_country ?? 'Pakistan' }}">
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
                        <i class="bi bi-check-circle"></i> Create & Link Customer
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
    let currentCurrency = '{{ $order->currency }}';

    // Initialize with existing items
    @foreach($order->items as $index => $item)
    orderItems.push({
        id: {{ $index }},
        existing_id: '{{ $item->id }}',
        product_id: '{{ $item->product_id }}',
        name: '{{ $item->product_name }}',
        sku: '{{ $item->product_sku }}',
        image: '{{ $item->getProductImageUrl() }}',
        quantity: {{ $item->quantity }},
        unit_price: parseFloat({{ $item->unit_price }}),
        subtotal: {{ $item->quantity }} * parseFloat({{ $item->unit_price }}),
        action: 'keep'
    });
    itemCounter++;
    @endforeach

    renderOrderItems();
    calculateTotals();

    // Currency Change
    $('#currency').on('change', function() {
        currentCurrency = $(this).val();
        calculateTotals();
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
            $(this).toggle(text.includes(searchTerm));
        });
    });

    // Product Selection
    $('#productSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        $('#itemPrice').val(selectedOption.data('price'));
    });

    // Confirm Add Item
    $('#confirmAddItem').on('click', function() {
        const selectedProduct = $('#productSelect option:selected');

        if (!selectedProduct.val()) {
            Swal.fire('No Product Selected', 'Please select a product first.', 'warning');
            return;
        }

        const quantity = parseInt($('#itemQuantity').val());
        const price = parseFloat($('#itemPrice').val());

        if (quantity < 1 || price < 0) {
            Swal.fire('Invalid Values', 'Please enter valid quantity and price.', 'warning');
            return;
        }

        orderItems.push({
            id: itemCounter++,
            existing_id: null,
            product_id: selectedProduct.val(),
            name: selectedProduct.data('name'),
            sku: selectedProduct.data('sku'),
            image: selectedProduct.data('image'),
            quantity: quantity,
            unit_price: price,
            subtotal: quantity * price,
            action: 'add'
        });

        renderOrderItems();
        calculateTotals();

        $('#addItemModal').modal('hide');
        $('#itemQuantity').val(1);
        $('#itemPrice').val('');
        $('#productSelect').val('');
    });

    // Render Order Items
    function renderOrderItems() {
        if (orderItems.filter(item => item.action !== 'delete').length === 0) {
            $('#orderItemsContainer').html('');
            $('#noItemsAlert').removeClass('d-none');
            return;
        }

        $('#noItemsAlert').addClass('d-none');
        let html = '';

        orderItems.forEach((item, index) => {
            if (item.action === 'delete') return;

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
                                       data-index="${index}" value="${item.unit_price}" min="0" step="0.01" style="width: 100px;" readonly>
                            </div>
                            <div class="col-auto text-end">
                                <small class="text-muted d-block">Subtotal</small>
                                <strong class="item-subtotal">${currentCurrency} ${item.subtotal.toFixed(2)}</strong>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        ${item.existing_id ? `<input type="hidden" name="items[${index}][id]" value="${item.existing_id}">` : ''}
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}" class="hidden-quantity-${index}">
                        <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}" class="hidden-price-${index}">
                        <input type="hidden" name="items[${index}][action]" value="${item.action}" class="hidden-action-${index}">
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
        orderItems[index].subtotal = newQuantity * orderItems[index].unit_price;  // ✅ Use newQuantity, not orderItems[index].quantity
        if (orderItems[index].action === 'keep') orderItems[index].action = 'update';

        $(`.hidden-quantity-${index}`).val(newQuantity);
        $(`.hidden-action-${index}`).val(orderItems[index].action);
        $(this).closest('.card-body').find('.item-subtotal').text(currentCurrency + ' ' + orderItems[index].subtotal.toFixed(2));

        calculateTotals();
    });

    // Remove Item
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        const item = orderItems[index];

        Swal.fire({
            icon: 'warning',
            title: 'Remove Item?',
            text: 'Are you sure you want to remove this item? Stock will be restored.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                if (item.existing_id) {
                    // Existing item - call API to delete and restore stock
                    $.ajax({
                        url: `/admin/orders/{{ $order->id }}/items/${item.existing_id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                // Remove from array
                                orderItems.splice(index, 1);
                                renderOrderItems();
                                calculateTotals();

                                // Update order totals from server response
                                if (response.order) {
                                    $('#summarySubtotal').text(currentCurrency + ' ' + parseFloat(response.order.subtotal).toFixed(2));
                                    $('#summaryTax').text(currentCurrency + ' ' + parseFloat(response.order.tax_amount).toFixed(2));
                                    $('#summaryTotal').text(currentCurrency + ' ' + parseFloat(response.order.total_amount).toFixed(2));
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to remove item.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage
                            });
                        }
                    });
                } else {
                    // New item not yet saved - just remove from array
                    orderItems.splice(index, 1);
                    renderOrderItems();
                    calculateTotals();

                    Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: 'Item removed from order.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }
        });
    });

    // Calculate Totals (UPDATED FOR COUPON SUPPORT)
    function calculateTotals() {
        let subtotal = 0;

        orderItems.forEach(item => {
            if (item.action !== 'delete') {
                subtotal += item.subtotal;
            }
        });

        const taxRate = parseFloat($('#tax_rate').val()) || 0;
        const shippingAmount = parseFloat($('#shipping_amount').val()) || 0;
        const discountAmount = parseFloat($('#discount_amount').val()) || 0;

        const taxAmount = subtotal * (taxRate / 100);
        const total = Math.max(0, subtotal + taxAmount + shippingAmount - discountAmount);

        $('#summarySubtotal').text(currentCurrency + ' ' + subtotal.toFixed(2));
        $('#summaryTax').text(currentCurrency + ' ' + taxAmount.toFixed(2));
        $('#summaryShipping').text(currentCurrency + ' ' + shippingAmount.toFixed(2));
        $('#summaryDiscount').text('-' + currentCurrency + ' ' + discountAmount.toFixed(2));
        $('#summaryTotal').text(currentCurrency + ' ' + total.toFixed(2));
        $('#taxRateDisplay').text(taxRate);
    }
    // Recalculate on changes
    $('#tax_rate, #shipping_amount, #discount_amount').on('input', function() {
        calculateTotals();
    });

    // Form Submission
    $('#editOrderForm').on('submit', function(e) {
        e.preventDefault();

        if (orderItems.filter(item => item.action !== 'delete').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items',
                text: 'Order must have at least one item.'
            });
            return;
        }

        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

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
                    $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Update Order');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating the order.';

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

                $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Update Order');
            }
        });
    });

    // Open Add Customer Modal (For Edit Page - Convert Guest)
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

    // Submit Add Customer Form (Edit Page - Convert Guest to Customer)
    $('#addCustomerForm').on('submit', function(e) {
        e.preventDefault();

        const saveBtn = $('#saveCustomerBtn');
        const originalText = saveBtn.html();

        // Disable button and show loading
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        $.ajax({
            url: '{{ route("admin.customers.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Set the customer ID to link with order
                    $('#linked_customer_id').val(response.customer.id);

                    // Update the UI to show customer is now linked
                    $('#guestCustomerInfo').html(`
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle"></i> Customer created successfully and linked to this order!
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Customer Name</label>
                                <p class="mb-0">${response.customer.first_name} ${response.customer.last_name}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="mb-0">${response.customer.email}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <p class="mb-0">${response.customer.phone || '—'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Customer Type</label>
                                <p class="mb-0">
                                    <span class="badge bg-success">Newly Registered Customer</span>
                                </p>
                            </div>
                        </div>
                    `);

                    // Hide the Add Customer button
                    $('#addCustomerBtn').hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Customer Created!',
                        html: `${response.message}<br><small class="text-muted">Don't forget to save the order to apply changes.</small>`,
                        timer: 3000,
                        showConfirmButton: false
                    });

                    // Close modal
                    $('#addCustomerModal').modal('hide');
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
        $('#sameAsBilling').prop('checked', false);
    });

    // ============================================================
    // COUPON & DISCOUNT MANAGEMENT (EDIT PAGE)
    // ============================================================

    let appliedCoupon = null;

    // Initialize existing coupon if present
    @if($order->discount_code)
    appliedCoupon = {
        coupon_code: '{{ $order->discount_code }}',
        discount_amount: {{ $order->discount_amount }}
    };
    @endif

    // Auto-uppercase coupon code input
    $('#coupon_code_input').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Apply Coupon Button
    $('#applyCouponBtn').on('click', function() {
        const couponCode = $('#coupon_code_input').val().trim().toUpperCase();

        if (!couponCode) {
            Swal.fire({
                icon: 'warning',
                title: 'No Code Entered',
                text: 'Please enter a coupon code'
            });
            return;
        }

        // Validate that items exist
        if (orderItems.filter(item => item.action !== 'delete').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty Order',
                text: 'Order must have at least one item'
            });
            return;
        }

        // Prepare cart items for API
        const cartItems = [];
        let subtotal = 0;

        orderItems.forEach(item => {
            if (item.action !== 'delete') {
                cartItems.push({
                    product_id: item.product_id,
                    variant_id: item.variant_id || null,
                    quantity: item.quantity,
                    unit_price: item.unit_price
                });
                subtotal += item.subtotal;
            }
        });

        // Get customer info
        const customerId = '{{ $order->customer_id }}' || null;
        const guestEmail = '{{ $order->guest_email }}' || null;

        // Show loading
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Validating...');

        // Validate coupon via AJAX
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

                    // Update UI
                    $('#couponInfo').removeClass('d-none');
                    $('#couponName').text(response.data.coupon_name);
                    $('#couponDiscount').text(response.data.formatted_discount);

                    // Update hidden fields
                    $('#coupon_id').val(response.data.coupon_id);
                    $('#discount_code').val(response.data.coupon_code);
                    $('#discount_amount').val(response.data.discount_amount).prop('readonly', true);

                    // Toggle buttons
                    $('#applyCouponBtn').addClass('d-none');
                    $('#removeCouponBtn').removeClass('d-none');

                    // Recalculate totals
                    calculateTotals();

                    Swal.fire({
                        icon: 'success',
                        title: 'Coupon Applied!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Coupon',
                        text: response.message
                    });
                }

                btn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                let errorMessage = 'Failed to validate coupon';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });

                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Remove Coupon Button
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
                // Clear coupon data
                appliedCoupon = null;
                $('#coupon_code_input').val('');
                $('#couponInfo').addClass('d-none');
                $('#coupon_id').val('');
                $('#discount_code').val('');
                $('#discount_amount').val(0).prop('readonly', false);

                // Toggle buttons
                $('#applyCouponBtn').removeClass('d-none');
                $('#removeCouponBtn').addClass('d-none');

                // Recalculate totals
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

    // Manual Discount Input Change
    $('#discount_amount').on('input', function() {
        if (!$(this).prop('readonly')) {
            calculateTotals();
        }
    });

    // Update Item Price (add to existing item-price change handler)
    $(document).on('change', '.item-price', function() {
        const index = $(this).data('index');
        const newPrice = parseFloat($(this).val());

        if (newPrice < 0) {
            $(this).val(0);
            return;
        }

        orderItems[index].unit_price = newPrice;
        orderItems[index].subtotal = orderItems[index].quantity * orderItems[index].unit_price;
        if (orderItems[index].action === 'keep') orderItems[index].action = 'update';

        $(`.hidden-price-${index}`).val(newPrice);
        $(`.hidden-action-${index}`).val(orderItems[index].action);
        $(this).closest('.card-body').find('.item-subtotal').text(currentCurrency + ' ' + orderItems[index].subtotal.toFixed(2));

        calculateTotals();
    });
});
</script>
@endpush
