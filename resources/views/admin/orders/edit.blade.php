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
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-person-circle text-primary"></i> Customer Information</h5>
                    </div>
                    <div class="card-body">
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
                                    @if($order->customer)
                                        <span class="badge bg-primary">Registered Customer</span>
                                        <a href="{{ route('admin.customers.show', $order->customer_id) }}" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="bi bi-eye"></i> View Profile
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">Guest</span>
                                    @endif
                                </p>
                            </div>
                        </div>
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
                    <div class="card-header bg-primary text-white py-3">
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
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <strong class="text-danger" id="summaryDiscount">-{{ $order->currency }} 0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Total:</h5>
                            <h5 class="mb-0 text-primary" id="summaryTotal">{{ $order->currency }} 0.00</h5>
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
                            <label for="shipping_amount" class="form-label">Shipping Amount ($)</label>
                            <input type="number" class="form-control" id="shipping_amount" name="shipping_amount"
                                   value="{{ $order->shipping_amount }}" min="0" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate"
                                   value="{{ $order->tax_rate ?? 0 }}" min="0" max="100" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label for="discount_code" class="form-label">Discount Code</label>
                            <input type="text" class="form-control" id="discount_code" name="discount_code"
                                   value="{{ $order->discount_code }}">
                        </div>

                        <div class="mb-0">
                            <label for="discount_amount" class="form-label">Discount Amount ($)</label>
                            <input type="number" class="form-control" id="discount_amount" name="discount_amount"
                                   value="{{ $order->discount_amount }}" min="0" step="0.01">
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
                            {{ $product->name }} - {{ $product->sku }} (Stock: {{ $product->stock_quantity }}) - ${{ number_format($product->getFinalPrice(), 2) }}
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
                        <label class="form-label">Unit Price ($) <span class="text-danger">*</span></label>
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
                                       data-index="${index}" value="${item.unit_price}" min="0" step="0.01" style="width: 100px;">
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

    // Calculate Totals
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
        const total = subtotal + taxAmount + shippingAmount - discountAmount;

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
});
</script>
@endpush
