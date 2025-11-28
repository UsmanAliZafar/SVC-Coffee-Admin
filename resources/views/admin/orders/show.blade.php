@extends('admin.layouts.app')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Order Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">{{ $order->order_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('orders.update'))
            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Order
            </a>
            @endif
            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-success" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Invoice
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Order Header Info --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">Order #{{ $order->order_number }}</h4>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge {{ $order->getStatusBadgeClass() }} fs-6">
                                    {{ $order->getStatusLabel() }}
                                </span>
                                <span class="badge {{ $order->getPaymentStatusBadgeClass() }} fs-6">
                                    {{ $order->getPaymentStatusLabel() }}
                                </span>
                                @if($order->is_refunded)
                                <span class="badge bg-dark fs-6">
                                    <i class="bi bi-arrow-counterclockwise"></i> Refunded
                                </span>
                                @endif
                                <span class="text-muted">
                                    <i class="bi bi-calendar"></i> {{ $order->created_at->format('M d, Y h:i A') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <h3 class="text-success mb-1">{{ $order->currency ?? '$' }} {{ number_format($order->total_amount, 2) }}</h3>
                            <p class="text-muted mb-0">Total Amount</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">Quick Actions</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @if($order->canUpdateStatus())
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-arrow-repeat"></i> Update Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item update-status" href="#" data-status="ORDER_CONFIRMED">Confirm Order</a></li>
                                <li><a class="dropdown-item update-status" href="#" data-status="ORDER_PROCESSING">Mark as Processing</a></li>
                                <li><a class="dropdown-item update-status" href="#" data-status="ORDER_PACKED">Mark as Packed</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item update-status" href="#" data-status="ORDER_SHIPPED">Mark as Shipped</a></li>
                                <li><a class="dropdown-item update-status" href="#" data-status="ORDER_DELIVERED">Mark as Delivered</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger update-status" href="#" data-status="ORDER_CANCELLED">Cancel Order</a></li>
                            </ul>
                        </div>
                        @endif

                        @if($order->canShip())
                        <button class="btn btn-outline-success btn-sm" id="markShippedBtn">
                            <i class="bi bi-truck"></i> Mark as Shipped
                        </button>
                        @endif

                        @if($order->canRefund())
                        <button class="btn btn-outline-warning btn-sm" id="processRefundBtn">
                            <i class="bi bi-arrow-counterclockwise"></i> Process Refund
                        </button>
                        @endif

                        <button class="btn btn-outline-info btn-sm" id="sendEmailBtn">
                            <i class="bi bi-envelope"></i> Send Email
                        </button>

                        <button class="btn btn-outline-secondary btn-sm" id="printOrderBtn">
                            <i class="bi bi-printer"></i> Print
                        </button>

                        @if(auth('admin')->user()->hasPermission('orders.delete'))
                        <button class="btn btn-outline-danger btn-sm" id="deleteOrderBtn">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Order Items --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-cart text-primary"></i> Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">Image</th>
                                    <th>Product</th>
                                    <th style="width: 100px;">Quantity</th>
                                    <th style="width: 120px;">Unit Price</th>
                                    <th style="width: 100px;">Tax</th>
                                    <th style="width: 120px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        @if($item->product && $item->product->getMainImageUrl())
                                        <img src="{{ $item->product->getMainImageUrl() }}" alt="{{ $item->product_name }}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 5px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->product_sku)
                                        <br><small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                        @endif
                                        @if($item->variant_id)
                                        <br><span class="badge bg-info badge-sm">Variant</span>
                                        @endif
                                        @if($item->hasVariant())
                                        <br><small class="text-muted">{{ $item->getVariantOptionsString() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $item->quantity }}</span>
                                    </td>
                                    <td>
                                        {{ $order->currency ?? '$' }} {{ number_format($item->unit_price, 2) }}
                                        {{-- ✅ NEW: Show tax info for unit price --}}
                                        @if($item->is_taxable && $item->tax_rate > 0)
                                        <br>
                                        <small class="badge {{ $item->product && $item->product->tax_type === 'inclusive' ? 'bg-success' : 'bg-danger' }} badge-sm">
                                            {{ $item->tax_rate }}%
                                            {{ $item->product && $item->product->tax_type === 'inclusive' ? 'INC' : 'EXC' }}
                                        </small>
                                        @endif
                                    </td>
                                    {{-- ✅ NEW: Tax Column --}}
                                    <td>
                                        @if($item->is_taxable && $item->tax_amount > 0)
                                            <span class="{{ $item->product && $item->product->tax_type === 'inclusive' ? 'text-success' : 'text-danger' }}">
                                                {{ $order->currency ?? '$' }} {{ number_format($item->tax_amount, 2) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $item->product && $item->product->tax_type === 'inclusive' ? 'Included' : 'Added' }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $order->currency ?? '$' }} {{ number_format($item->total, 2) }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            {{-- Order Timeline --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-info"></i> Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Order Placed</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
                                <p class="mb-0 text-muted">Order {{ $order->order_number }} was created</p>
                            </div>
                        </div>

                        @if($order->confirmed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Order Confirmed</h6>
                                <small class="text-muted">{{ $order->confirmed_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif

                        @if($order->paid_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Payment Received</h6>
                                <small class="text-muted">{{ $order->paid_at->format('M d, Y h:i A') }}</small>
                                <p class="mb-0 text-muted">Amount: {{ $order->currency ?? '$' }} {{ number_format($order->total_amount, 2) }}</p>
                            </div>
                        </div>
                        @endif

                        @if($order->shipped_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Order Shipped</h6>
                                <small class="text-muted">{{ $order->shipped_at->format('M d, Y h:i A') }}</small>
                                @if($order->shipping_tracking_number)
                                <p class="mb-0 text-muted">Tracking: {{ $order->shipping_tracking_number }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($order->delivered_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Order Delivered</h6>
                                <small class="text-muted">{{ $order->delivered_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                        @endif

                        @if($order->cancelled_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Order Cancelled</h6>
                                <small class="text-muted">{{ $order->cancelled_at->format('M d, Y h:i A') }}</small>
                                @if($order->cancellation_reason)
                                <p class="mb-0 text-muted">Reason: {{ $order->cancellation_reason }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($order->refunded_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-dark"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Order Refunded</h6>
                                <small class="text-muted">{{ $order->refunded_at->format('M d, Y h:i A') }}</small>
                                <p class="mb-0 text-muted">Amount: {{ $order->currency ?? '$' }} {{ number_format($order->refunded_amount, 2) }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-chat-left-text text-warning"></i> Notes</h5>
                </div>
                <div class="card-body">
                    @if($order->customer_notes)
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="bi bi-person"></i> Customer Notes</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $order->customer_notes }}
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <h6 class="text-warning"><i class="bi bi-shield-check"></i> Admin Notes</h6>
                        <textarea class="form-control mb-2" id="adminNotes" rows="3" placeholder="Add admin notes (visible to customer)...">{{ $order->admin_notes }}</textarea>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-info"><i class="bi bi-file-lock"></i> Internal Notes</h6>
                        <textarea class="form-control mb-2" id="internalNotes" rows="3" placeholder="Add internal notes (private, staff only)...">{{ $order->internal_notes }}</textarea>
                    </div>

                    <button type="button" class="btn btn-primary btn-sm" id="saveNotesBtn">
                        <i class="bi bi-save"></i> Save Notes
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Order Summary --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-receipt text-success"></i> Order Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            {{-- Subtotal --}}
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-end"><strong>{{ $order->currency }} {{ number_format($order->subtotal, 2) }}</strong></td>
                            </tr>

                            {{-- ✅ CALCULATE: All tax components --}}
                            @php
                                // Get item-level taxes (these are already saved in order_items)
                                $inclusiveTax = $order->items->filter(function($item) {
                                    return $item->is_taxable &&
                                        $item->product &&
                                        $item->product->tax_type === 'inclusive';
                                })->sum('tax_amount');

                                $exclusiveTax = $order->items->filter(function($item) {
                                    return $item->is_taxable &&
                                        $item->product &&
                                        $item->product->tax_type === 'exclusive';
                                })->sum('tax_amount');

                                // ✅ Calculate additional tax from order tax_rate
                                $additionalTax = 0;
                                if (!empty($order->tax_rate) && $order->tax_rate > 0) {
                                    $additionalTax = ($order->subtotal * $order->tax_rate) / 100;
                                }

                                // Total tax calculation
                                $totalTaxCalculated = $inclusiveTax + $exclusiveTax + $additionalTax;
                            @endphp

                            {{-- Show Inclusive Tax (informational - already in subtotal) --}}
                            @if($inclusiveTax > 0)
                            <tr class="text-success">
                                <td>
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="Tax included in product prices"></i>
                                    Tax (Inclusive):
                                </td>
                                <td class="text-end">
                                    <strong>{{ $order->currency }} {{ number_format($inclusiveTax, 2) }}</strong>
                                    <br><small class="text-muted">Already included in subtotal</small>
                                </td>
                            </tr>
                            @endif

                            {{-- Show Exclusive Tax (added to subtotal) --}}
                            @if($exclusiveTax > 0)
                            <tr class="text-danger">
                                <td>
                                    <i class="bi bi-plus-circle" data-bs-toggle="tooltip" title="Tax added to product prices"></i>
                                    Tax (Exclusive):
                                </td>
                                <td class="text-end">
                                    <strong>{{ $order->currency }} {{ number_format($exclusiveTax, 2) }}</strong>
                                    <br><small class="text-muted">Added to total</small>
                                </td>
                            </tr>
                            @endif

                            {{-- Show Additional Tax (added to subtotal) --}}
                            @if($additionalTax > 0)
                            <tr>
                                <td>
                                    <i class="bi bi-percent"></i>
                                    Additional Tax:
                                    <br><small class="text-muted">({{ number_format($order->tax_rate, 2) }}% of subtotal)</small>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $order->currency }} {{ number_format($additionalTax, 2) }}</strong>
                                </td>
                            </tr>
                            @endif

                            {{-- Discount --}}
                            @if($order->discount_amount > 0)
                            <tr>
                                <td>
                                    <i class="bi bi-tag-fill text-danger"></i>
                                    Discount
                                    @if($order->discount_code)
                                    <br><small class="text-muted">Code: {{ $order->discount_code }}</small>
                                    @endif
                                </td>
                                <td class="text-end text-danger">
                                    <strong>-{{ $order->currency }} {{ number_format($order->discount_amount, 2) }}</strong>
                                </td>
                            </tr>
                            @endif

                            {{-- Shipping --}}
                            <tr>
                                <td>
                                    <i class="bi bi-truck"></i>
                                    Shipping:
                                    @if($order->shipping_method)
                                    <br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>{{ $order->currency }} {{ number_format($order->shipping_amount, 2) }}</strong>
                                </td>
                            </tr>

                            {{-- Total Tax Summary (Breakdown only) --}}
                            @if($totalTaxCalculated > 0)
                            <tr class="table-light">
                                <td>
                                    <strong><i class="bi bi-calculator"></i> Total Tax:</strong>
                                    <br>
                                    <small class="text-muted">
                                        @php
                                            $taxParts = [];
                                            if($inclusiveTax > 0) $taxParts[] = 'Inc: ' . $order->currency .' '. number_format($inclusiveTax, 2);
                                            if($exclusiveTax > 0) $taxParts[] = 'Exc: ' . $order->currency .' '. number_format($exclusiveTax, 2);
                                            if($additionalTax > 0) $taxParts[] = 'Add: ' . $order->currency .' '. number_format($additionalTax, 2);
                                            echo implode(' + ', $taxParts);
                                        @endphp
                                    </small>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $order->currency }} {{ number_format($totalTaxCalculated, 2) }}</strong>
                                </td>
                            </tr>
                            @endif

                            {{-- ✅ Grand Total (CALCULATED - includes additional tax) --}}
                            <tr class="border-top border-2 bg-light">
                                <td>
                                    <strong><i class="bi bi-cash-stack text-success"></i> Grand Total:</strong>
                                    <br>
                                    <small class="text-muted">
                                        Subtotal
                                        @if($exclusiveTax > 0) + Excl Tax @endif
                                        @if($additionalTax > 0) + Add Tax @endif
                                        @if($order->shipping_amount > 0) + Shipping @endif
                                        @if($order->discount_amount > 0) - Discount @endif
                                    </small>
                                </td>
                                <td class="text-end">
                                    @php
                                        // ✅ RECALCULATE total to include additional tax
                                        $calculatedTotal = $order->subtotal + $exclusiveTax + $additionalTax + $order->shipping_amount - $order->discount_amount;
                                    @endphp

                                    <h5 class="mb-0 text-success">
                                        {{ $order->currency }} {{ number_format($calculatedTotal, 2) }}
                                    </h5>

                                    {{-- ✅ Show calculation breakdown --}}
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                        ({{ $order->currency }}{{ number_format($order->subtotal, 2) }}
                                        @if($exclusiveTax > 0) + {{ $order->currency }} {{ number_format($exclusiveTax, 2) }} @endif
                                        @if($additionalTax > 0) + {{ $order->currency }} {{ number_format($additionalTax, 2) }} @endif
                                        @if($order->shipping_amount > 0) + {{ $order->currency }} {{ number_format($order->shipping_amount, 2) }} @endif
                                        @if($order->discount_amount > 0) - {{ $order->currency }} {{ number_format($order->discount_amount, 2) }} @endif)
                                    </small>

                                    {{-- ✅ Show DB value if different (for debugging) --}}
                                    @if(abs($calculatedTotal - $order->total_amount) > 0.01)
                                    <small class="text-warning d-block mt-1 d-none">
                                        <i class="bi bi-exclamation-triangle"></i> DB Total: {{ $order->currency }} {{ number_format($order->total_amount, 2) }}
                                    </small>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Customer Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-person text-primary"></i> Customer Information</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">{{ $order->getCustomerName() }}</h6>
                    <p class="mb-1"><i class="bi bi-envelope"></i> {{ $order->getCustomerEmail() }}</p>
                    @if($order->billing_phone)
                    <p class="mb-1"><i class="bi bi-telephone"></i> {{ $order->billing_phone }}</p>
                    @endif
                    @if($order->customer)
                    <a href="{{ route('admin.customers.show', $order->customer_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-arrow-right"></i> View Customer Profile
                    </a>
                    @endif
                </div>
            </div>

            {{-- Billing Address --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-credit-card text-info"></i> Billing Address</h5>
                </div>
                <div class="card-body">
                    @if($order->billing_company)
                    <p class="mb-1"><strong>{{ $order->billing_company }}</strong></p>
                    @endif
                    <p class="mb-1">{{ $order->billing_address_line1 }}</p>
                    @if($order->billing_address_line2)
                    <p class="mb-1">{{ $order->billing_address_line2 }}</p>
                    @endif
                    <p class="mb-1">{{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}</p>
                    <p class="mb-0">{{ $order->billing_country }}</p>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-truck text-warning"></i> Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</strong></p>
                    @if($order->shipping_company)
                    <p class="mb-1">{{ $order->shipping_company }}</p>
                    @endif
                    <p class="mb-1">{{ $order->shipping_address_line1 }}</p>
                    @if($order->shipping_address_line2)
                    <p class="mb-1">{{ $order->shipping_address_line2 }}</p>
                    @endif
                    <p class="mb-1">{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}</p>
                    <p class="mb-0">{{ $order->shipping_country }}</p>
                    @if($order->shipping_phone)
                    <p class="mb-0 mt-2"><i class="bi bi-telephone"></i> {{ $order->shipping_phone }}</p>
                    @endif
                </div>
            </div>

            {{-- Payment Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-credit-card-2-front text-success"></i> Payment Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Method:</strong> {{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : 'N/A' }}</p>
                    @if($order->payment_gateway)
                    <p class="mb-1"><strong>Gateway:</strong> {{ ucfirst($order->payment_gateway) }}</p>
                    @endif
                    @if($order->transaction_id)
                    <p class="mb-1"><strong>Transaction ID:</strong> <br><code>{{ $order->transaction_id }}</code></p>
                    @endif
                    <p class="mb-0">
                        <strong>Status:</strong>
                        <span class="badge {{ $order->getPaymentStatusBadgeClass() }}">
                            {{ $order->getPaymentStatusLabel() }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- Shipping Information --}}
            @if($order->shipping_tracking_number)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-box-seam text-info"></i> Shipping Information</h5>
                </div>
                <div class="card-body">
                    @if($order->shipping_carrier)
                    <p class="mb-1"><strong>Carrier:</strong> {{ $order->shipping_carrier }}</p>
                    @endif
                    <p class="mb-1"><strong>Tracking:</strong> <br><code>{{ $order->shipping_tracking_number }}</code></p>
                    @if($order->shipping_method)
                    <p class="mb-1"><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}</p>
                    @endif
                    @if($order->expected_delivery_date)
                    <p class="mb-0"><strong>Expected:</strong> {{ $order->expected_delivery_date->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- Mark as Shipped Modal --}}
<div class="modal fade" id="markShippedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Order as Shipped</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="markShippedForm">
                    <div class="mb-3">
                        <label for="shippingCarrier" class="form-label">Shipping Carrier <span class="text-danger">*</span></label>
                        <select class="form-select" id="shippingCarrier" name="shipping_carrier" required>
                            <option value="">Select Carrier</option>
                            <option value="USPS">USPS</option>
                            <option value="UPS">UPS</option>
                            <option value="FedEx">FedEx</option>
                            <option value="DHL">DHL</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="trackingNumber" class="form-label">Tracking Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="trackingNumber" name="tracking_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="shippedAt" class="form-label">Shipped Date</label>
                        <input type="datetime-local" class="form-control" id="shippedAt" name="shipped_at" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notifyCustomer" name="notify_customer" checked>
                        <label class="form-check-label" for="notifyCustomer">
                            Send shipping notification to customer
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmMarkShipped">
                    <i class="bi bi-truck"></i> Mark as Shipped
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 25px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -23px;
        top: 12px;
        bottom: -12px;
        width: 2px;
        background: #e0e0e0;
    }
    .timeline-item:last-child::before {
        display: none;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e0e0;
    }
    .timeline-content {
        padding-left: 10px;
    }
    @media print {
        .btn, nav, .card-header button {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update Status
    $('.update-status').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');

        Swal.fire({
            title: 'Update Order Status?',
            text: 'Are you sure you want to update this order status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.orders.update-status", $order->id) }}',
                    type: 'POST',
                    data: {
                        status_key_code: status,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated!',
                            text: 'Order status has been updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to update status'
                        });
                    }
                });
            }
        });
    });

    // Mark as Shipped
    $('#markShippedBtn').on('click', function() {
        $('#markShippedModal').modal('show');
    });

    $('#confirmMarkShipped').on('click', function() {
        const formData = $('#markShippedForm').serialize();

        $.ajax({
            url: '{{ route("admin.orders.mark-shipped", $order->id) }}',
            type: 'POST',
            data: formData + '&_token={{ csrf_token() }}',
            success: function(response) {
                $('#markShippedModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Order Shipped!',
                    text: 'Order has been marked as shipped.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to mark as shipped'
                });
            }
        });
    });

    // Save Notes
    $('#saveNotesBtn').on('click', function() {
        $.ajax({
            url: '{{ route("admin.orders.update-notes", $order->id) }}',
            type: 'POST',
            data: {
                admin_notes: $('#adminNotes').val(),
                internal_notes: $('#internalNotes').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Notes Saved!',
                    text: 'Order notes have been updated.',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save notes'
                });
            }
        });
    });

    // Process Refund
    $('#processRefundBtn').on('click', function() {
        Swal.fire({
            title: 'Process Refund',
            text: 'Enter refund amount:',
            input: 'number',
            inputValue: '{{ $order->total_amount }}',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            confirmButtonText: 'Process Refund'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Process refund logic
                Swal.fire('Coming Soon', 'Refund processing will be implemented', 'info');
            }
        });
    });

    // Send Email
    $('#sendEmailBtn').on('click', function() {
        Swal.fire('Coming Soon', 'Email sending functionality will be implemented', 'info');
    });

    // Print Order
    $('#printOrderBtn').on('click', function() {
        window.print();
    });

    // Delete Order
    $('#deleteOrderBtn').on('click', function() {
        Swal.fire({
            title: 'Delete Order?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.orders.destroy", $order->id) }}',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Order has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("admin.orders.index") }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete order'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
