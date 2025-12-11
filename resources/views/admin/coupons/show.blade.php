@extends('admin.layouts.app')

@section('title', 'Coupon Details - ' . $coupon->code)

@push('styles')
<style>
    .detail-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .detail-card-title {
        color: #5B914C;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .stat-card {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card.secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }
    .stat-card.info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    .stat-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #333;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .coupon-code-display {
        background: #5B914C;
        color: white;
        padding: 15px 30px;
        border-radius: 10px;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .detail-row {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #495057;
        display: inline-block;
        min-width: 200px;
    }
    .detail-value {
        color: #212529;
    }
    .status-badge-large {
        font-size: 1rem;
        padding: 8px 16px;
    }
    .btn-action {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-action:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }
    .usage-table {
        font-size: 0.9rem;
    }
    .progress {
        height: 25px;
        font-size: 0.875rem;
    }
    .badge-custom {
        padding: 6px 12px;
        font-size: 0.875rem;
    }
    .timeline-item {
        position: relative;
        padding-left: 40px;
        padding-bottom: 20px;
        border-left: 2px solid #5B914C;
        margin-left: 10px;
    }
    .timeline-item:last-child {
        border-left: 2px solid transparent;
    }
    .timeline-icon {
        position: absolute;
        left: -11px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #5B914C;
        border: 3px solid white;
    }
    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    .alert-info-custom {
        background-color: #e8f5e9;
        border-color: #5B914C;
        color: #2d5a24;
    }
    .restriction-badge {
        display: inline-block;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 5px 10px;
        border-radius: 5px;
        margin: 3px;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-ticket-detailed"></i> Coupon Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">Coupons</a></li>
                    <li class="breadcrumb-item active">{{ $coupon->code }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @can('coupons.update')
                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-action">
                    <i class="bi bi-pencil"></i> Edit Coupon
                </a>
            @endcan
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Coupon Code Display -->
            <div class="coupon-code-display">
                <i class="bi bi-tag-fill"></i> {{ $coupon->code }}
            </div>

            <!-- Basic Information -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-info-circle"></i> Basic Information</h3>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-card-heading"></i> Coupon Name:</span>
                    <span class="detail-value">{{ $coupon->name }}</span>
                </div>

                @if($coupon->description)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-file-text"></i> Description:</span>
                    <span class="detail-value">{{ $coupon->description }}</span>
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-percent"></i> Discount Type:</span>
                    <span class="detail-value">
                        @php
                            $icons = [
                                'percentage' => 'bi-percent',
                                'fixed_amount' => store_currency_symbol(),
                                'free_shipping' => 'bi-truck',
                                'buy_x_get_y' => 'bi-gift',
                            ];
                            $colors = [
                                'percentage' => 'primary',
                                'fixed_amount' => 'success',
                                'free_shipping' => 'info',
                                'buy_x_get_y' => 'warning',
                            ];
                            $labels = [
                                'percentage' => 'Percentage Discount',
                                'fixed_amount' => 'Fixed Amount',
                                'free_shipping' => 'Free Shipping',
                                'buy_x_get_y' => 'Buy X Get Y',
                            ];
                            $icon = $icons[$coupon->discount_type] ?? 'bi-tag';
                            $color = $colors[$coupon->discount_type] ?? 'secondary';
                            $label = $labels[$coupon->discount_type] ?? ucfirst($coupon->discount_type);
                        @endphp
                        <span class="badge bg-{{ $color }} badge-custom">
                            <i class="bi {{ $icon }}"></i> {{ $label }}
                        </span>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-calculator"></i> Discount Value:</span>
                    <span class="detail-value">
                        <strong class="text-success">{{ $coupon->getFormattedDiscountValue() }}</strong>
                    </span>
                </div>

                @if($coupon->discount_type === 'percentage' && $coupon->max_discount_amount)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-shield-check"></i> Max Discount Cap:</span>
                    <span class="detail-value">{{ store_currency_symbol() }}{{ number_format($coupon->max_discount_amount, 2) }}</span>
                </div>
                @endif

                @if($coupon->discount_type === 'buy_x_get_y')
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-cart-plus"></i> Buy X Get Y:</span>
                    <span class="detail-value">
                        Buy <strong>{{ $coupon->buy_quantity }}</strong> Get <strong>{{ $coupon->get_quantity }}</strong> Free
                    </span>
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-calendar-event"></i> Created:</span>
                    <span class="detail-value">
                        {{ $coupon->created_at->format('M d, Y h:i A') }}
                        @if($coupon->createdBy)
                            <small class="text-muted">by {{ $coupon->createdBy->name }}</small>
                        @endif
                    </span>
                </div>

                @if($coupon->updated_at != $coupon->created_at)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-clock-history"></i> Last Updated:</span>
                    <span class="detail-value">
                        {{ $coupon->updated_at->format('M d, Y h:i A') }}
                        @if($coupon->updatedBy)
                            <small class="text-muted">by {{ $coupon->updatedBy->name }}</small>
                        @endif
                    </span>
                </div>
                @endif
            </div>

            <!-- Validity & Restrictions -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-shield-check"></i> Validity & Restrictions</h3>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-calendar-check"></i> Valid From:</span>
                    <span class="detail-value">
                        @if($coupon->valid_from)
                            {{ $coupon->valid_from->format('M d, Y h:i A') }}
                        @else
                            <span class="text-muted">Immediately</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-calendar-x"></i> Valid Until:</span>
                    <span class="detail-value">
                        @if($coupon->valid_until)
                            {{ $coupon->valid_until->format('M d, Y h:i A') }}
                            @if($coupon->isExpired())
                                <span class="badge bg-danger ms-2">Expired</span>
                            @endif
                        @else
                            <span class="text-muted">No Expiry</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-cash"></i> Min. Purchase Amount:</span>
                    <span class="detail-value">
                        @if($coupon->min_purchase_amount > 0)
                            {{ store_currency_symbol() }}{{ number_format($coupon->min_purchase_amount, 2) }}
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-cart"></i> Min. Items Count:</span>
                    <span class="detail-value">
                        @if($coupon->min_items_count > 0)
                            {{ $coupon->min_items_count }} items
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-tag"></i> Applies to Sale Items:</span>
                    <span class="detail-value">
                        @if($coupon->applies_to_sale_items)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-star"></i> First Order Only:</span>
                    <span class="detail-value">
                        @if($coupon->first_order_only)
                            <span class="badge bg-warning text-dark">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-star-fill"></i> Featured:</span>
                    <span class="detail-value">
                        @if($coupon->is_featured)
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Product/Category Restrictions -->
            @if($coupon->applicable_product_ids || $coupon->applicable_category_ids || $coupon->excluded_product_ids || $coupon->excluded_category_ids)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-filter"></i> Product/Category Restrictions</h3>

                @if($coupon->applicable_product_ids && count($coupon->applicable_product_ids) > 0)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-check-circle"></i> Applicable Products:</span>
                    <div class="detail-value mt-2">
                        @foreach($coupon->applicable_product_ids as $productId)
                            <span class="restriction-badge text-success">
                                <i class="bi bi-box"></i> Product ID: {{ $productId }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($coupon->applicable_category_ids && count($coupon->applicable_category_ids) > 0)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-check-circle"></i> Applicable Categories:</span>
                    <div class="detail-value mt-2">
                        @foreach($coupon->applicable_category_ids as $categoryId)
                            <span class="restriction-badge text-success">
                                <i class="bi bi-folder"></i> Category ID: {{ $categoryId }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($coupon->excluded_product_ids && count($coupon->excluded_product_ids) > 0)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-x-circle"></i> Excluded Products:</span>
                    <div class="detail-value mt-2">
                        @foreach($coupon->excluded_product_ids as $productId)
                            <span class="restriction-badge text-danger">
                                <i class="bi bi-box"></i> Product ID: {{ $productId }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($coupon->excluded_category_ids && count($coupon->excluded_category_ids) > 0)
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-x-circle"></i> Excluded Categories:</span>
                    <div class="detail-value mt-2">
                        @foreach($coupon->excluded_category_ids as $categoryId)
                            <span class="restriction-badge text-danger">
                                <i class="bi bi-folder"></i> Category ID: {{ $categoryId }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Recent Usage History -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-clock-history"></i> Recent Usage History</h3>

                @if($coupon->usages->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover usage-table">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-calendar"></i> Date</th>
                                    <th><i class="bi bi-person"></i> Customer</th>
                                    <th><i class="bi bi-receipt"></i> Order ID</th>
                                    <th>{{ store_currency_symbol() }} Discount</th>
                                    <th><i class="bi bi-cart"></i> Order Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coupon->usages->take(10) as $usage)
                                <tr>
                                    <td>
                                        <small>{{ $usage->used_at->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ $usage->used_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($usage->customer)
                                            <a href="{{ route('admin.customers.show', $usage->customer_id) }}">
                                                {{ $usage->customer->name ?? $usage->customer_email }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ $usage->customer_email ?? 'Guest' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($usage->order)
                                            <a href="{{ route('admin.orders.show', $usage->order_id) }}" class="badge bg-primary">
                                                #{{ substr($usage->order_id, 0, 8) }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">
                                            -{{ store_currency_symbol() }}{{ number_format($usage->discount_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ store_currency_symbol() }}{{ number_format($usage->order_total, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($coupon->usages->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.coupons.statistics', $coupon->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-graph-up"></i> View Full Statistics & History
                        </a>
                    </div>
                    @endif
                @else
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle"></i> This coupon hasn't been used yet.
                    </div>
                @endif
            </div>

            <!-- Admin Notes -->
            @if($coupon->admin_notes)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-sticky"></i> Admin Notes</h3>
                <div class="alert alert-secondary mb-0">
                    <i class="bi bi-file-text"></i> {{ $coupon->admin_notes }}
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Statistics & Status -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-toggle-on"></i> Status</h3>

                <div class="text-center mb-3">
                    @php
                        $statusLabel = $coupon->getStatusLabel();
                        $badges = [
                            'Active' => 'success',
                            'Inactive' => 'secondary',
                            'Expired' => 'danger',
                            'Scheduled' => 'info',
                            'Limit Reached' => 'warning',
                        ];
                        $color = $badges[$statusLabel] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} status-badge-large">
                        <i class="bi bi-circle-fill"></i> {{ $statusLabel }}
                    </span>
                </div>

                @can('coupons.update')
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="toggleStatusBtn" data-id="{{ $coupon->id }}">
                        <i class="bi bi-toggle-{{ $coupon->is_active ? 'on' : 'off' }}"></i>
                        {{ $coupon->is_active ? 'Deactivate' : 'Activate' }} Coupon
                    </button>
                </div>
                @endcan
            </div>

            <!-- Usage Statistics -->
            <div class="stat-card">
                <div class="stat-value">{{ $coupon->total_used }}</div>
                <div class="stat-label">Total Uses</div>
            </div>

            <div class="stat-card secondary">
                <div class="stat-value">
                    @if($coupon->getRemainingUses())
                        {{ $coupon->getRemainingUses() }}
                    @else
                        ∞
                    @endif
                </div>
                <div class="stat-label">Remaining Uses</div>
            </div>

            <div class="stat-card info">
                <div class="stat-value">{{ store_currency_symbol() }}{{ number_format($statistics['total_discount_given'], 2) }}</div>
                <div class="stat-label">Total Discount Given</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-value">{{ $statistics['unique_customers'] }}</div>
                <div class="stat-label">Unique Customers</div>
            </div>

            <!-- Usage Limit Progress -->
            @if($coupon->usage_limit_total)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-speedometer"></i> Usage Progress</h3>

                @php
                    $percentage = ($coupon->total_used / $coupon->usage_limit_total) * 100;
                    $progressColor = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                @endphp

                <div class="progress mb-2">
                    <div class="progress-bar bg-{{ $progressColor }}" role="progressbar"
                        style="width: {{ $percentage }}%"
                        aria-valuenow="{{ $coupon->total_used }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ $coupon->usage_limit_total }}">
                        {{ number_format($percentage, 1) }}%
                    </div>
                </div>

                <div class="text-center">
                    <small class="text-muted">
                        {{ $coupon->total_used }} of {{ $coupon->usage_limit_total }} uses
                    </small>
                </div>
            </div>
            @endif

            <!-- Per Customer Limit -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-person-badge"></i> Usage Limits</h3>

                <div class="detail-row">
                    <span class="detail-label">Total Limit:</span>
                    <span class="detail-value">
                        @if($coupon->usage_limit_total)
                            {{ $coupon->usage_limit_total }} uses
                        @else
                            <span class="text-muted">Unlimited</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Per Customer:</span>
                    <span class="detail-value">
                        {{ $coupon->usage_limit_per_customer }} {{ Str::plural('use', $coupon->usage_limit_per_customer) }}
                    </span>
                </div>
            </div>

            <!-- Average Order Value -->
            @if($statistics['average_order_value'])
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-graph-up"></i> Performance</h3>

                <div class="detail-row">
                    <span class="detail-label">Avg. Order Value:</span>
                    <span class="detail-value">
                        <strong>{{ store_currency_symbol() }}{{ number_format($statistics['average_order_value'], 2) }}</strong>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Total Orders:</span>
                    <span class="detail-value">
                        <strong>{{ $coupon->usages_count }}</strong>
                    </span>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-lightning"></i> Quick Actions</h3>

                <div class="d-grid gap-2">
                    @can('coupons.update')
                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-action">
                        <i class="bi bi-pencil"></i> Edit Coupon
                    </a>
                    @endcan

                    <a href="{{ route('admin.coupons.statistics', $coupon->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-graph-up"></i> View Full Statistics
                    </a>

                    <button type="button" class="btn btn-outline-secondary" onclick="copyCode()">
                        <i class="bi bi-clipboard"></i> Copy Code
                    </button>

                    @can('coupons.delete')
                    <button type="button" class="btn btn-outline-danger" id="deleteCouponBtn" data-id="{{ $coupon->id }}">
                        <i class="bi bi-trash"></i> Delete Coupon
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Toggle Status
    $('#toggleStatusBtn').on('click', function() {
        const couponId = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: 'Toggle Status?',
            text: 'Are you sure you want to change the coupon status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/coupons/${couponId}/toggle-status`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        }
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

    // Delete Coupon
    $('#deleteCouponBtn').on('click', function() {
        const couponId = $(this).data('id');

        Swal.fire({
            title: 'Delete Coupon?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/coupons/${couponId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = '{{ route("admin.coupons.index") }}';
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete coupon'
                        });
                    }
                });
            }
        });
    });

    // Copy Code to Clipboard
    window.copyCode = function() {
        const code = '{{ $coupon->code }}';
        navigator.clipboard.writeText(code).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Coupon code copied to clipboard',
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end'
            });
        });
    };
});
</script>
@endpush
