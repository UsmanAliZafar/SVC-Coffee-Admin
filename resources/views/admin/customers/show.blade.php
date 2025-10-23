@extends('admin.layouts.app')

@section('title', 'Customer Details - ' . $customer->getFullName())

@push('styles')
<style>
    .customer-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 2rem;
        color: white;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        box-shadow: 0 4px 8px rgba(91, 145, 76, 0.3);
    }
    .stat-card {
        transition: transform 0.2s;
        border-left: 4px solid #5B914C;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .timeline-item {
        border-left: 2px solid #dee2e6;
        padding-left: 20px;
        padding-bottom: 20px;
        position: relative;
    }
    .timeline-item:last-child {
        border-left: 0;
        padding-bottom: 0;
    }
    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: absolute;
        left: -7px;
        top: 5px;
        border: 2px solid #fff;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
    }
    .info-value {
        color: #212529;
        font-size: 1rem;
    }
    .segment-badge-large {
        font-size: 0.9rem;
        padding: 8px 16px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Customer Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">{{ $customer->getFullName() }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('customers.update'))
            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Customer
            </a>
            @endif
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-4">

            {{-- Customer Profile Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="customer-avatar-large mx-auto mb-3">
                        {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                    </div>

                    <h4 class="mb-1">{{ $customer->getFullName() }}</h4>

                    <div class="mb-2">
                        {!! $customer->getTypeBadge() !!}
                        {!! $customer->getStatusBadge() !!}
                    </div>

                    <div class="mb-3">
                        <span class="segment-badge-large badge {{ match($stats['segment']) {
                            'New' => 'bg-info',
                            'One-time Buyer' => 'bg-secondary',
                            'At Risk' => 'bg-danger',
                            'High Value' => 'bg-success',
                            'Loyal' => 'bg-primary',
                            default => 'bg-light text-dark',
                        } }}">
                            <i class="bi bi-star"></i> {{ $stats['segment'] }} Customer
                        </span>
                    </div>

                    @if($customer->company_name)
                    <p class="text-muted mb-2">
                        <i class="bi bi-building"></i> {{ $customer->company_name }}
                    </p>
                    @endif

                    <p class="text-muted mb-1">
                        <i class="bi bi-envelope"></i> {{ $customer->email }}
                    </p>

                    @if($customer->phone)
                    <p class="text-muted mb-1">
                        <i class="bi bi-telephone"></i> {{ $customer->phone }}
                    </p>
                    @endif

                    <p class="text-muted mb-3">
                        <i class="bi bi-calendar"></i> Member since {{ $customer->created_at->format('M d, Y') }}
                    </p>

                    <hr>

                    {{-- Quick Actions --}}
                    <div class="d-grid gap-2">
                        @if(auth('admin')->user()->hasPermission('customers.update'))
                        @if($customer->isActive())
                        <button class="btn btn-danger btn-sm toggle-status-btn" data-action="block">
                            <i class="bi bi-lock"></i> Block Customer
                        </button>
                        @elseif($customer->isBlocked())
                        <button class="btn btn-success btn-sm toggle-status-btn" data-action="activate">
                            <i class="bi bi-unlock"></i> Activate Customer
                        </button>
                        @endif
                        @endif

                        @if(auth('admin')->user()->hasPermission('customers.delete'))
                        <button class="btn btn-outline-danger btn-sm" id="deleteCustomerBtn">
                            <i class="bi bi-trash"></i> Delete Customer
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-person-lines-fill text-primary"></i> Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $customer->email }}</div>
                        @if($customer->email_verified_at)
                        <small class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</small>
                        @else
                        <small class="text-warning"><i class="bi bi-exclamation-circle-fill"></i> Not Verified</small>
                        @endif
                    </div>

                    @if($customer->phone)
                    <div class="mb-3">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">{{ $customer->phone }}</div>
                    </div>
                    @endif

                    @if($customer->company_name)
                    <div class="mb-3">
                        <div class="info-label">Company</div>
                        <div class="info-value">{{ $customer->company_name }}</div>
                    </div>
                    @endif

                    <div class="mb-0">
                        <div class="info-label">Subscriptions</div>
                        <div class="d-flex gap-2 mt-1">
                            @if($customer->is_newsletter_subscribed)
                            <span class="badge bg-success"><i class="bi bi-envelope-check"></i> Newsletter</span>
                            @endif
                            @if($customer->is_sms_subscribed)
                            <span class="badge bg-info"><i class="bi bi-phone-vibrate"></i> SMS</span>
                            @endif
                            @if(!$customer->is_newsletter_subscribed && !$customer->is_sms_subscribed)
                            <span class="text-muted">No subscriptions</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Billing Address --}}
            @if($customer->billing_address_line1)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-geo-alt text-warning"></i> Billing Address</h6>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        {{ $customer->billing_address_line1 }}<br>
                        @if($customer->billing_address_line2)
                        {{ $customer->billing_address_line2 }}<br>
                        @endif
                        @if($customer->billing_city)
                        {{ $customer->billing_city }},
                        @endif
                        @if($customer->billing_state)
                        {{ $customer->billing_state }}
                        @endif
                        @if($customer->billing_postal_code)
                        {{ $customer->billing_postal_code }}
                        @endif
                        <br>
                        @if($customer->billing_country)
                        {{ $customer->billing_country }}
                        @endif
                    </address>
                </div>
            </div>
            @endif

            {{-- Referral Information --}}
            @if($customer->referrer || $customer->referrals->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-people text-info"></i> Referral Information</h6>
                </div>
                <div class="card-body">
                    @if($customer->referrer)
                    <div class="mb-3">
                        <div class="info-label">Referred By</div>
                        <div class="info-value">
                            <a href="{{ route('admin.customers.show', $customer->referrer->id) }}">
                                {{ $customer->referrer->getFullName() }}
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($customer->referral_code)
                    <div class="mb-3">
                        <div class="info-label">Referral Code</div>
                        <div class="info-value">
                            <code>{{ $customer->referral_code }}</code>
                        </div>
                    </div>
                    @endif

                    @if($customer->referrals->count() > 0)
                    <div class="mb-0">
                        <div class="info-label">Referred Customers</div>
                        <div class="info-value">{{ $customer->referrals->count() }} customer(s)</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- Right Column --}}
        <div class="col-lg-8">

            {{-- Statistics Cards --}}
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2"><i class="bi bi-cart"></i> Total Orders</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_orders']) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2"><i class="bi bi-currency-dollar"></i> Total Spent</h6>
                            <h3 class="mb-0">${{ number_format($stats['total_spent'], 2) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2"><i class="bi bi-graph-up"></i> Avg Order</h6>
                            <h3 class="mb-0">${{ number_format($stats['average_order_value'], 2) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2"><i class="bi bi-clock-history"></i> Pending</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Stats --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="info-label">Lifetime Value</div>
                            <div class="info-value text-success h5 mb-0">
                                ${{ number_format($stats['lifetime_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Days Since Last Order</div>
                            <div class="info-value h5 mb-0">
                                @if($stats['days_since_last_order'])
                                    {{ $stats['days_since_last_order'] }} days
                                @else
                                    <span class="text-muted">Never ordered</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Customer Segment</div>
                            <div class="info-value h5 mb-0">{{ $stats['segment'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Purchased Products --}}
            @if($topProducts->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-bag-check text-success"></i> Top Purchased Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $product)
                                <tr>
                                    <td>{{ $product->product_name }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $product->total_quantity }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong>${{ number_format($product->total_spent, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Recent Orders --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-cart text-primary"></i> Recent Orders</h6>
                    @if($customer->orders->count() > 10)
                    <a href="{{ route('admin.orders.index') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-outline-primary">
                        View All Orders
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($customer->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $order->created_at->format('M d, Y') }}<br>
                                        <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $order->items->count() }} items</span>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($order->total_amount, 2) }}</strong>
                                    </td>
                                    <td>{!! $order->getStatusBadge() !!}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">No orders yet</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Activity Timeline --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-clock-history text-info"></i> Activity Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-item">
                        <div class="timeline-dot bg-success"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Customer Registered</strong>
                                <p class="text-muted mb-0">Account created</p>
                            </div>
                            <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if($customer->email_verified_at)
                    <div class="timeline-item">
                        <div class="timeline-dot bg-primary"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Email Verified</strong>
                                <p class="text-muted mb-0">Email address confirmed</p>
                            </div>
                            <small class="text-muted">{{ $customer->email_verified_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endif

                    @if($customer->first_order_at)
                    <div class="timeline-item">
                        <div class="timeline-dot bg-warning"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>First Order</strong>
                                <p class="text-muted mb-0">Placed first order</p>
                            </div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($customer->first_order_at)->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endif

                    @if($customer->last_order_at)
                    <div class="timeline-item">
                        <div class="timeline-dot bg-info"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Last Order</strong>
                                <p class="text-muted mb-0">Most recent purchase</p>
                            </div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($customer->last_order_at)->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endif

                    @if($customer->last_login_at)
                    <div class="timeline-item">
                        <div class="timeline-dot bg-secondary"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Last Login</strong>
                                <p class="text-muted mb-0">Last accessed account</p>
                            </div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($customer->last_login_at)->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Internal Notes --}}
            @if($customer->notes)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-journal-text text-secondary"></i> Internal Notes</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light mb-0">
                        {{ $customer->notes }}
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status-btn').on('click', function() {
        const action = $(this).data('action');
        const actionText = action.charAt(0).toUpperCase() + action.slice(1);

        Swal.fire({
            title: `${actionText} Customer?`,
            text: `Are you sure you want to ${action} this customer?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action}!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.customers.toggle-status", $customer->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update status', 'error');
                    }
                });
            }
        });
    });

    // Delete customer
    $('#deleteCustomerBtn').on('click', function() {
        Swal.fire({
            title: 'Delete Customer?',
            text: "This action cannot be undone! The customer must have no orders to be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.customers.destroy", $customer->id) }}',
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                window.location.href = '{{ route("admin.customers.index") }}';
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete customer', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
