@extends('admin.layouts.app')

@section('title', 'Customer Profile - ' . $customer->getFullName())

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        background: rgba(255,255,255,0.2);
        color: white;
        border: 3px solid white;
    }
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
    }
    .info-value {
        color: #2c3e50;
        font-weight: 500;
    }
    .order-card {
        border-left: 3px solid #5B914C;
        transition: all 0.2s;
    }
    .order-card:hover {
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        transform: translateX(5px);
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-dot {
        position: absolute;
        left: -26px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #5B914C;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #5B914C;
    }
    .address-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.25rem;
        background: #f8f9fa;
        height: 100%;
    }
    .tag-badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    .action-btn {
        transition: all 0.2s;
    }
    .action-btn:hover {
        transform: translateY(-2px);
    }
    .section-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    .section-header {
        background: #f8f9fa;
        border-bottom: 2px solid #5B914C;
        padding: 1rem 1.5rem;
    }
    .product-item {
        padding: 0.75rem;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .badge-custom {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .quick-action-btn {
        width: 100%;
        padding: 0.75rem;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        background: white;
        transition: all 0.2s;
        text-align: left;
    }
    .quick-action-btn:hover {
        border-color: #5B914C;
        background: #f8f9fa;
        transform: translateX(5px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-person-circle text-primary"></i> Customer Profile
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
                    <li class="breadcrumb-item active">{{ $customer->getFullName() }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(auth('admin')->user()->hasPermission('customers.update'))
            <button type="button" class="btn btn-info text-white" id="syncCustomerBtn" data-id="{{ $customer->id }}">
                <i class="bi bi-arrow-repeat"></i> Sync Stats
            </button>
            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-warning text-white">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
            @endif
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Profile Header --}}
    <div class="profile-header shadow-sm">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="avatar-large">
                    {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                </div>
            </div>
            <div class="col">
                <h2 class="mb-1 fw-bold">
                    {{ $customer->getFullName() }}
                    @if($customer->is_verified)
                        <i class="bi bi-patch-check-fill" title="Verified Customer"></i>
                    @endif
                </h2>
                <p class="mb-2 opacity-90">
                    <i class="bi bi-envelope-fill"></i> {{ $customer->email }}
                    @if($customer->phone)
                        <span class="mx-2">|</span>
                        <i class="bi bi-telephone-fill"></i> {{ $customer->phone }}
                    @endif
                </p>
                @if($customer->company_name)
                <p class="mb-0 opacity-90">
                    <i class="bi bi-building"></i> {{ $customer->company_name }}
                </p>
                @endif
            </div>
            <div class="col-auto text-end">
                <div class="mb-2">
                    {!! $customer->getTypeBadge() !!}
                    {!! $customer->getStatusBadge() !!}
                </div>
                <div class="mb-2">
                    <span class="badge badge-custom" style="background: rgba(255,255,255,0.2);">
                        <i class="bi bi-diagram-3"></i> {{ $customer->getSegment() }}
                    </span>
                </div>
                <small class="opacity-75">
                    <i class="bi bi-calendar-plus"></i> Member since {{ $customer->created_at->format('M d, Y') }}
                </small>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-cart-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Total Orders</div>
                            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Total Spent</div>
                            <div class="stat-value">{{ store_currency_symbol() }}{{ number_format($stats['total_spent'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Avg Order Value</div>
                            <div class="stat-value">{{ store_currency_symbol() }}{{ number_format($stats['average_order_value'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Last Order</div>
                            <div class="stat-value" style="font-size: 1rem;">
                                {{ $stats['days_since_last_order'] !== null ? $stats['days_since_last_order'] . ' days ago' : 'Never' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Stats Row --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill text-success fs-2 mb-2"></i>
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['completed_orders'] }}</h4>
                    <small class="text-muted">Completed Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-2 mb-2"></i>
                    <h4 class="mb-0 fw-bold text-warning">{{ $stats['pending_orders'] }}</h4>
                    <small class="text-muted">Pending Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle-fill text-danger fs-2 mb-2"></i>
                    <h4 class="mb-0 fw-bold text-danger">{{ $stats['cancelled_orders'] }}</h4>
                    <small class="text-muted">Cancelled Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill text-info fs-2 mb-2"></i>
                    <h4 class="mb-0 fw-bold text-info">{{ $stats['referrals_count'] }}</h4>
                    <small class="text-muted">Referrals Made</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Recent Orders --}}
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-bag-check-fill text-primary"></i> Recent Orders
                    </h5>
                </div>
                <div class="card-body">
                    @if($customer->orders->count() > 0)
                        @foreach($customer->orders->take(10) as $order)
                        <div class="order-card p-3 mb-3 bg-light rounded">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong class="text-primary">#{{ $order->order_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="col-md-2">
                                    {!! $order->getStatusBadge() !!}
                                </div>
                                <div class="col-md-2 text-center">
                                    <strong>{{ $order->items->count() }}</strong>
                                    <br>
                                    <small class="text-muted">Items</small>
                                </div>
                                <div class="col-md-3 text-end">
                                    <strong class="text-success">{{ store_currency_symbol() }}{{ number_format($order->total_amount, 2) }}</strong>
                                </div>
                                <div class="col-md-2 text-end">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($customer->orders->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.orders.index', ['customer_id' => $customer->id]) }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-right-circle"></i> View All Orders ({{ $customer->orders->count() }})
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p>No orders yet</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Top Purchased Products --}}
            @if(isset($topProducts) && $topProducts->count() > 0)
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-star-fill text-warning"></i> Top Purchased Products
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($topProducts as $product)
                    <div class="product-item">
                        <div>
                            <strong>{{ $product->product_name }}</strong>
                            <br>
                            <small class="text-muted">Quantity: {{ $product->total_quantity }}</small>
                        </div>
                        <div class="text-end">
                            <strong class="text-success">{{ store_currency_symbol() }}{{ number_format($product->total_spent, 2) }}</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Addresses --}}
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Addresses
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Billing Address --}}
                        <div class="col-md-6 mb-3">
                            <div class="address-card">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-credit-card text-warning"></i> Billing Address
                                </h6>
                                @if($customer->hasBillingAddress())
                                    <p class="mb-0">{{ $customer->getBillingAddress() }}</p>
                                @else
                                    <p class="text-muted mb-0">No billing address on file</p>
                                @endif
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        <div class="col-md-6 mb-3">
                            <div class="address-card">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-truck text-info"></i> Shipping Address
                                </h6>
                                @if($customer->hasShippingAddress())
                                    <p class="mb-0">{{ $customer->getShippingAddress() }}</p>
                                @else
                                    <p class="text-muted mb-0">No shipping address on file</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">

            {{-- Quick Actions --}}
            <div class="card section-card shadow-sm mb-4 d-none">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-lightning-fill text-warning"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(auth('admin')->user()->hasPermission('customers.update'))
                        <button type="button" class="quick-action-btn" id="editCustomerBtn">
                            <i class="bi bi-pencil-square text-warning"></i>
                            <strong class="ms-2">Edit Customer</strong>
                        </button>
                        @endif

                        @if(auth('admin')->user()->hasPermission('orders.create'))
                        <button type="button" class="quick-action-btn" id="createOrderBtn">
                            <i class="bi bi-plus-circle text-success"></i>
                            <strong class="ms-2">Create Order</strong>
                        </button>
                        @endif

                        <button type="button" class="quick-action-btn" id="sendEmailBtn">
                            <i class="bi bi-envelope text-primary"></i>
                            <strong class="ms-2">Send Email</strong>
                        </button>

                        @if(auth('admin')->user()->hasPermission('customers.update'))
                        <button type="button" class="quick-action-btn" id="toggleStatusBtn">
                            <i class="bi bi-toggle-on text-info"></i>
                            <strong class="ms-2">Toggle Status</strong>
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Customer Information --}}
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle-fill text-info"></i> Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Customer ID</div>
                        <div class="info-value">{{ $customer->id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Customer Type</div>
                        <div class="info-value">{!! $customer->getTypeBadge() !!}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status</div>
                        <div class="info-value">{!! $customer->getStatusBadge() !!}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email Verified</div>
                        <div class="info-value">
                            @if($customer->is_verified)
                                <i class="bi bi-check-circle-fill text-success"></i> Yes
                            @else
                                <i class="bi bi-x-circle-fill text-danger"></i> No
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Newsletter</div>
                        <div class="info-value">
                            @if($customer->is_newsletter_subscribed)
                                <i class="bi bi-check-circle-fill text-success"></i> Subscribed
                            @else
                                <i class="bi bi-x-circle-fill text-muted"></i> Not Subscribed
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">SMS Alerts</div>
                        <div class="info-value">
                            @if($customer->is_sms_subscribed)
                                <i class="bi bi-check-circle-fill text-success"></i> Subscribed
                            @else
                                <i class="bi bi-x-circle-fill text-muted"></i> Not Subscribed
                            @endif
                        </div>
                    </div>
                    @if($customer->preferred_language)
                    <div class="info-row">
                        <div class="info-label">Language</div>
                        <div class="info-value">{{ strtoupper($customer->preferred_language) }}</div>
                    </div>
                    @endif
                    @if($customer->preferred_currency)
                    <div class="info-row">
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $customer->preferred_currency }}</div>
                    </div>
                    @endif
                    @if($customer->acquisition_source)
                    <div class="info-row">
                        <div class="info-label">Source</div>
                        <div class="info-value">{{ $customer->acquisition_source }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Business Information --}}
            @if($customer->isBusiness() || $customer->isWholesale())
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-briefcase-fill text-secondary"></i> Business Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($customer->company_name)
                    <div class="info-row">
                        <div class="info-label">Company Name</div>
                        <div class="info-value">{{ $customer->company_name }}</div>
                    </div>
                    @endif
                    @if($customer->tax_id)
                    <div class="info-row">
                        <div class="info-label">Tax ID</div>
                        <div class="info-value">{{ $customer->tax_id }}</div>
                    </div>
                    @endif
                    @if($customer->vat_number)
                    <div class="info-row">
                        <div class="info-label">VAT Number</div>
                        <div class="info-value">{{ $customer->vat_number }}</div>
                    </div>
                    @endif
                    @if($customer->business_registration)
                    <div class="info-row">
                        <div class="info-label">Registration</div>
                        <div class="info-value">{{ $customer->business_registration }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Login Activity --}}
            @if($customer->last_login_at)
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history text-success"></i> Login Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Last Login</div>
                        <div class="info-value">
                            {{ $customer->last_login_at->format('M d, Y h:i A') }}
                            <br>
                            <small class="text-muted">{{ $customer->last_login_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @if($customer->login_count)
                    <div class="info-row">
                        <div class="info-label">Total Logins</div>
                        <div class="info-value">{{ number_format($customer->login_count) }} times</div>
                    </div>
                    @endif
                    @if($customer->last_login_ip)
                    <div class="info-row">
                        <div class="info-label">Last IP Address</div>
                        <div class="info-value">
                            <code>{{ $customer->last_login_ip }}</code>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Referral Info --}}
            @if($customer->referrer || $customer->referrals->count() > 0)
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-people-fill text-info"></i> Referral Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($customer->referrer)
                    <div class="info-row">
                        <div class="info-label">Referred By</div>
                        <div class="info-value">
                            <a href="{{ route('admin.customers.show', $customer->referrer->id) }}" class="text-decoration-none">
                                {{ $customer->referrer->getFullName() }}
                            </a>
                        </div>
                    </div>
                    @endif
                    @if($customer->referral_code)
                    <div class="info-row">
                        <div class="info-label">Referral Code</div>
                        <div class="info-value">
                            <code>{{ $customer->referral_code }}</code>
                        </div>
                    </div>
                    @endif
                    @if($customer->referrals->count() > 0)
                    <div class="info-row">
                        <div class="info-label">Referrals Made</div>
                        <div class="info-value">
                            <strong>{{ $customer->referrals->count() }}</strong> customers
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($customer->notes)
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-sticky-fill text-warning"></i> Internal Notes
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-line;">{{ $customer->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="card section-card shadow-sm mb-4">
                <div class="section-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-calendar-event text-primary"></i> Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <strong>Account Created</strong>
                            <br>
                            <small class="text-muted">{{ $customer->created_at->format('M d, Y h:i A') }}</small>
                            <br>
                            <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                        </div>

                        @if($customer->first_order_at)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <strong>First Order</strong>
                            <br>
                            <small class="text-muted">{{ $customer->first_order_at->format('M d, Y h:i A') }}</small>
                            <br>
                            <small class="text-muted">{{ $customer->first_order_at->diffForHumans() }}</small>
                        </div>
                        @endif

                        @if($customer->email_verified_at)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <strong>Email Verified</strong>
                            <br>
                            <small class="text-muted">{{ $customer->email_verified_at->format('M d, Y h:i A') }}</small>
                        </div>
                        @endif

                        @if($customer->last_order_at)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <strong>Latest Order</strong>
                            <br>
                            <small class="text-muted">{{ $customer->last_order_at->format('M d, Y h:i A') }}</small>
                            <br>
                            <small class="text-muted">{{ $customer->last_order_at->diffForHumans() }}</small>
                        </div>
                        @endif

                        <div class="timeline-item" style="padding-bottom: 0;">
                            <div class="timeline-dot"></div>
                            <strong>Last Updated</strong>
                            <br>
                            <small class="text-muted">{{ $customer->updated_at->format('M d, Y h:i A') }}</small>
                        </div>
                    </div>
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
    // Sync customer stats
    $('#syncCustomerBtn').on('click', function() {
        const customerId = $(this).data('id');
        const $btn = $(this);
        const $icon = $btn.find('i');

        $icon.addClass('fa-spin');
        $btn.prop('disabled', true);

        $.ajax({
            url: `/admin/customers/${customerId}/sync-stats`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Synced!',
                        html: `
                            <div class="text-start">
                                <p class="mb-2"><strong>Total Orders:</strong> ${response.data.total_orders}</p>
                                <p class="mb-2"><strong>Total Spent:</strong> ${response.data.total_spent}</p>
                                <p class="mb-2"><strong>Avg Order Value:</strong> ${response.data.average_order_value}</p>
                                <p class="mb-2"><strong>First Order:</strong> ${response.data.first_order_at}</p>
                                <p class="mb-2"><strong>Last Order:</strong> ${response.data.last_order_at}</p>
                                <p class="mb-0"><strong>Segment:</strong> <span class="badge bg-primary">${response.data.segment}</span></p>
                            </div>
                        `,
                        confirmButtonColor: '#5B914C',
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to sync customer stats',
                    confirmButtonColor: '#5B914C'
                });
            },
            complete: function() {
                $icon.removeClass('fa-spin');
                $btn.prop('disabled', false);
            }
        });
    });

    // Edit customer button
    $('#editCustomerBtn').on('click', function() {
        window.location.href = '{{ route("admin.customers.edit", $customer->id) }}';
    });

    // Create order button
    $('#createOrderBtn').on('click', function() {
        window.location.href = '{{ route("admin.orders.create") }}?customer_id={{ $customer->id }}';
    });

    // Send email button
    $('#sendEmailBtn').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Send Email',
            html: `
                <div class="text-start">
                    <label class="form-label">To:</label>
                    <input type="email" class="form-control mb-3" value="{{ $customer->email }}" readonly>
                    <label class="form-label">Subject:</label>
                    <input type="text" class="form-control mb-3" id="emailSubject" placeholder="Enter subject">
                    <label class="form-label">Message:</label>
                    <textarea class="form-control" id="emailMessage" rows="5" placeholder="Enter your message"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Send',
            confirmButtonColor: '#5B914C',
            preConfirm: () => {
                const subject = $('#emailSubject').val();
                const message = $('#emailMessage').val();

                if (!subject || !message) {
                    Swal.showValidationMessage('Please fill in all fields');
                    return false;
                }

                return { subject, message };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Here you would implement the actual email sending logic
                Swal.fire({
                    icon: 'success',
                    title: 'Email Sent!',
                    text: 'Your email has been sent successfully',
                    confirmButtonColor: '#5B914C'
                });
            }
        });
    });

    // Toggle status button
    $('#toggleStatusBtn').on('click', function() {
        const currentStatus = '{{ $customer->status_key_code }}';
        let action = 'activate';
        let title = 'Activate Customer';

        if (currentStatus === 'CUSTOMER_ACTIVE') {
            action = 'block';
            title = 'Block Customer';
        }

        Swal.fire({
            icon: 'warning',
            title: title,
            text: `Are you sure you want to ${action} this customer?`,
            showCancelButton: true,
            confirmButtonText: `Yes, ${action}!`,
            confirmButtonColor: action === 'block' ? '#dc3545' : '#5B914C',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/customers/{{ $customer->id }}/toggle-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                confirmButtonColor: '#5B914C'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to update status',
                            confirmButtonColor: '#5B914C'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
