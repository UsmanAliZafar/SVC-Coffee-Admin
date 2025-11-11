@extends('admin.layouts.app')

@section('title', 'Create Coupon')

@push('styles')
<style>
    .form-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .form-section-title {
        color: #5B914C;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .btn-submit {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        padding: 10px 30px;
    }
    .btn-submit:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }
    .btn-generate-code {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    .btn-generate-code:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }
    .required-label::after {
        content: " *";
        color: #dc3545;
    }
    .discount-type-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }
    .discount-type-card:hover {
        border-color: #5B914C;
        background-color: #f8f9fa;
    }
    .discount-type-card.active {
        border-color: #5B914C;
        background-color: #e8f5e9;
    }
    .discount-type-card input[type="radio"] {
        display: none;
    }
    .discount-type-card i {
        font-size: 2rem;
        color: #5B914C;
        margin-bottom: 10px;
    }
    .discount-type-card .type-name {
        font-weight: 600;
        color: #333;
    }
    .conditional-section {
        display: none;
    }
    .info-badge {
        background-color: #e8f5e9;
        color: #5B914C;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.875rem;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Create New Coupon</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">Coupons</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form id="couponForm">
        @csrf
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label required-label">Coupon Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-uppercase" id="code" name="code" required
                                    pattern="[A-Z0-9_-]+" maxlength="50"
                                    placeholder="e.g., SUMMER2025">
                                <button type="button" class="btn btn-generate-code" id="generateCodeBtn">
                                    <i class="bi bi-magic"></i> Generate
                                </button>
                            </div>
                            <small class="text-muted">Only uppercase letters, numbers, hyphens, and underscores</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label required-label">Coupon Name</label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="255"
                                placeholder="e.g., Summer Sale 2025">
                            <small class="text-muted">Internal name for identification</small>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            maxlength="1000" placeholder="Describe this coupon..."></textarea>
                        <small class="text-muted">This will be shown to customers</small>
                    </div>
                </div>

                <!-- Discount Type Selection -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-percent"></i> Discount Type</h3>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="discount-type-card" data-type="percentage">
                                <input type="radio" name="discount_type" value="percentage" required>
                                <i class="bi bi-percent"></i>
                                <div class="type-name">Percentage</div>
                                <small class="text-muted">% off</small>
                            </label>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="discount-type-card" data-type="fixed_amount">
                                <input type="radio" name="discount_type" value="fixed_amount">
                                {{ store_currency_symbol() }}
                                <div class="type-name">Fixed Amount</div>
                                <small class="text-muted">{{ store_currency_symbol() }}  off</small>
                            </label>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="discount-type-card" data-type="free_shipping">
                                <input type="radio" name="discount_type" value="free_shipping">
                                <i class="bi bi-truck"></i>
                                <div class="type-name">Free Shipping</div>
                                <small class="text-muted">No cost</small>
                            </label>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="discount-type-card" data-type="buy_x_get_y">
                                <input type="radio" name="discount_type" value="buy_x_get_y">
                                <i class="bi bi-gift"></i>
                                <div class="type-name">Buy X Get Y</div>
                                <small class="text-muted">BOGO</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Discount Value (Percentage/Fixed) -->
                <div class="form-section conditional-section" id="discountValueSection">
                    <h3 class="form-section-title"><i class="bi bi-calculator"></i> Discount Value</h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="discount_value" class="form-label required-label">Discount Value</label>
                            <div class="input-group">
                                <span class="input-group-text" id="discountPrefix">%</span>
                                <input type="number" class="form-control" id="discount_value" name="discount_value"
                                    min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3" id="maxDiscountField">
                            <label for="max_discount_amount" class="form-label">Max Discount Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ store_currency_symbol() }} </span>
                                <input type="number" class="form-control" id="max_discount_amount"
                                    name="max_discount_amount" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <small class="text-muted">Optional cap for percentage discounts</small>
                        </div>
                    </div>

                    <div class="info-badge">
                        <i class="bi bi-info-circle"></i>
                        <span id="discountInfo">Enter the discount percentage (0-100)</span>
                    </div>
                </div>

                <!-- Buy X Get Y Configuration -->
                <div class="form-section conditional-section" id="buyXGetYSection">
                    <h3 class="form-section-title"><i class="bi bi-gift"></i> Buy X Get Y Configuration</h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="buy_quantity" class="form-label required-label">Buy Quantity</label>
                            <input type="number" class="form-control" id="buy_quantity" name="buy_quantity"
                                min="1" placeholder="2">
                            <small class="text-muted">Customer must buy this many</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="get_quantity" class="form-label required-label">Get Quantity</label>
                            <input type="number" class="form-control" id="get_quantity" name="get_quantity"
                                min="1" placeholder="1">
                            <small class="text-muted">Customer gets this many free</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="buy_product_id" class="form-label required-label">Buy Product</label>
                            <select class="form-select" id="buy_product_id" name="buy_product_id">
                                <option value="">Select product...</option>
                                <!-- Products will be loaded via AJAX -->
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="get_product_id" class="form-label required-label">Get Product (Free)</label>
                            <select class="form-select" id="get_product_id" name="get_product_id">
                                <option value="">Select product...</option>
                                <!-- Products will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>

                    <div class="info-badge">
                        <i class="bi bi-info-circle"></i>
                        Example: Buy 2 Coffee Machines, Get 1 Free
                    </div>
                </div>

                <!-- Minimum Requirements -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-cart-check"></i> Minimum Requirements</h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="min_purchase_amount" class="form-label">Minimum Purchase Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ store_currency_symbol() }} </span>
                                <input type="number" class="form-control" id="min_purchase_amount"
                                    name="min_purchase_amount" min="0" step="0.01" value="0" placeholder="0.00">
                            </div>
                            <small class="text-muted">Cart subtotal must be at least this amount</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="min_items_count" class="form-label">Minimum Items Count</label>
                            <input type="number" class="form-control" id="min_items_count" name="min_items_count"
                                min="0" value="0" placeholder="0">
                            <small class="text-muted">Cart must have at least this many items</small>
                        </div>
                    </div>
                </div>

                <!-- Restrictions (Optional - Collapsed by default) -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="bi bi-filter"></i> Product/Category Restrictions
                        <button type="button" class="btn btn-sm btn-outline-secondary float-end"
                            data-bs-toggle="collapse" data-bs-target="#restrictionsCollapse">
                            <i class="bi bi-chevron-down"></i> Toggle
                        </button>
                    </h3>

                    <div class="collapse" id="restrictionsCollapse">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Leave empty to apply to all products
                        </div>

                        <div class="mb-3">
                            <label for="applicable_product_ids" class="form-label">Applicable Products</label>
                            <select class="form-select" id="applicable_product_ids" name="applicable_product_ids[]"
                                multiple size="5">
                                <!-- Products will be loaded via AJAX -->
                            </select>
                            <small class="text-muted">Coupon applies ONLY to these products</small>
                        </div>

                        <div class="mb-3">
                            <label for="applicable_category_ids" class="form-label">Applicable Categories</label>
                            <select class="form-select" id="applicable_category_ids" name="applicable_category_ids[]"
                                multiple size="5">
                                <!-- Categories will be loaded via AJAX -->
                            </select>
                            <small class="text-muted">Coupon applies ONLY to these categories</small>
                        </div>

                        <div class="mb-3">
                            <label for="excluded_product_ids" class="form-label">Excluded Products</label>
                            <select class="form-select" id="excluded_product_ids" name="excluded_product_ids[]"
                                multiple size="5">
                                <!-- Products will be loaded via AJAX -->
                            </select>
                            <small class="text-muted">Coupon CANNOT be used with these products</small>
                        </div>

                        <div class="mb-3">
                            <label for="excluded_category_ids" class="form-label">Excluded Categories</label>
                            <select class="form-select" id="excluded_category_ids" name="excluded_category_ids[]"
                                multiple size="5">
                                <!-- Categories will be loaded via AJAX -->
                            </select>
                            <small class="text-muted">Coupon CANNOT be used with these categories</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Usage Limits -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-speedometer"></i> Usage Limits</h3>

                    <div class="mb-3">
                        <label for="usage_limit_total" class="form-label">Total Usage Limit</label>
                        <input type="number" class="form-control" id="usage_limit_total"
                            name="usage_limit_total" min="1" placeholder="Unlimited">
                        <small class="text-muted">Total times this coupon can be used (leave empty for unlimited)</small>
                    </div>

                    <div class="mb-3">
                        <label for="usage_limit_per_customer" class="form-label required-label">Per Customer Limit</label>
                        <input type="number" class="form-control" id="usage_limit_per_customer"
                            name="usage_limit_per_customer" min="1" value="1" required>
                        <small class="text-muted">Times each customer can use this coupon</small>
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-calendar-event"></i> Validity Period</h3>

                    <div class="mb-3">
                        <label for="valid_from" class="form-label">Valid From</label>
                        <input type="datetime-local" class="form-control" id="valid_from" name="valid_from">
                        <small class="text-muted">Leave empty to activate immediately</small>
                    </div>

                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Valid Until</label>
                        <input type="datetime-local" class="form-control" id="valid_until" name="valid_until">
                        <small class="text-muted">Leave empty for no expiry</small>
                    </div>
                </div>

                <!-- Applicability Rules -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-gear"></i> Applicability Rules</h3>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="applies_to_sale_items"
                            name="applies_to_sale_items" value="1" checked>
                        <label class="form-check-label" for="applies_to_sale_items">
                            <i class="bi bi-tag"></i> Apply to Sale Items
                        </label>
                        <small class="d-block text-muted">Can be used on products already on sale</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="first_order_only"
                            name="first_order_only" value="1">
                        <label class="form-check-label" for="first_order_only">
                            <i class="bi bi-star"></i> First Order Only
                        </label>
                        <small class="d-block text-muted">Only for customers' first purchase</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured"
                            name="is_featured" value="1">
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star-fill text-warning"></i> Featured Coupon
                        </label>
                        <small class="d-block text-muted">Show on homepage/banners</small>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-toggle-on"></i> Status</h3>

                    <div class="mb-3">
                        <label class="form-label">Activation Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active"
                                name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                <strong>Active</strong>
                            </label>
                        </div>
                        <small class="text-muted">Inactive coupons cannot be used</small>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-sticky"></i> Admin Notes</h3>

                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Internal Notes</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4"
                            maxlength="2000" placeholder="Internal notes (not visible to customers)..."></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <button type="submit" class="btn btn-submit w-100 mb-2" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Create Coupon
                    </button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
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
    // Auto-uppercase coupon code
    $('#code').on('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '');
    });

    // Generate random code
    $('#generateCodeBtn').on('click', function() {
        Swal.fire({
            title: 'Generate Code',
            input: 'number',
            inputLabel: 'Code Length (6-20)',
            inputValue: 8,
            inputAttributes: { min: 6, max: 20 },
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            confirmButtonText: 'Generate'
        }).then((result) => {
            if (result.isConfirmed) {
                $.get('{{ route("admin.coupons.generate-code") }}', { length: result.value })
                    .done(function(response) {
                        $('#code').val(response.code);
                    });
            }
        });
    });

    // Discount type selection
    $('.discount-type-card').on('click', function() {
        $('.discount-type-card').removeClass('active');
        $(this).addClass('active');
        $(this).find('input[type="radio"]').prop('checked', true);

        const type = $(this).data('type');
        updateDiscountFields(type);
    });

    // Update discount fields based on type
    function updateDiscountFields(type) {
        $('.conditional-section').hide();

        if (type === 'percentage') {
            $('#discountValueSection').show();
            $('#maxDiscountField').show();
            $('#discountPrefix').text('%');
            $('#discount_value').attr('max', 100);
            $('#discountInfo').text('Enter percentage (0-100). Optionally set a maximum discount cap.');
        } else if (type === 'fixed_amount') {
            $('#discountValueSection').show();
            $('#maxDiscountField').hide();
            $('#discountPrefix').text('$');
            $('#discount_value').removeAttr('max');
            $('#discountInfo').text('Enter the fixed dollar amount to discount.');
        } else if (type === 'free_shipping') {
            // No additional fields needed
        } else if (type === 'buy_x_get_y') {
            $('#buyXGetYSection').show();
        }
    }

    // Load products for dropdowns (if needed)
    // You would implement this based on your products API
    function loadProducts() {
        // Example:
        // $.get('/admin/api/products', function(products) {
        //     products.forEach(function(product) {
        //         const option = `<option value="${product.id}">${product.name}</option>`;
        //         $('#buy_product_id, #get_product_id, #applicable_product_ids, #excluded_product_ids').append(option);
        //     });
        // });
    }

    // Load categories for dropdowns
    function loadCategories() {
        // Example:
        // $.get('/admin/api/categories', function(categories) {
        //     categories.forEach(function(category) {
        //         const option = `<option value="${category.id}">${category.title}</option>`;
        //         $('#applicable_category_ids, #excluded_category_ids').append(option);
        //     });
        // });
    }

    // Initialize
    loadProducts();
    loadCategories();

    // Form submission
    $('#couponForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        // Disable submit button
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating...');

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: '{{ route("admin.coupons.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, messages) {
                        const input = $(`[name="${key}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form and fix the errors.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to create coupon'
                    });
                }
            }
        });
    });
});
</script>
@endpush
