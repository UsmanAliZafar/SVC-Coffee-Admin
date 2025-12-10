@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-brand text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle"></i> Welcome, {{ $user->name }}!
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-speedometer2 display-1 text-muted mb-4"></i>
                        <h4>Welcome to the Admin Panel</h4>
                        <p class="text-muted">You have limited dashboard access. Use the navigation menu to access your permitted sections.</p>

                        <div class="mt-4">
                            <p><strong>Your Roles:</strong>
                                <span class="text-brand">{{ $user->roles->pluck('display_name')->join(', ') }}</span>
                            </p>
                        </div>

                        {{-- Quick Actions based on permissions --}}
                        <div class="row mt-5">
                            @if(auth('admin')->user()->hasPermission('orders.read'))
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-brand w-100 p-3">
                                    <i class="bi bi-receipt fs-1 mb-2"></i>
                                    <div>View Orders</div>
                                </a>
                            </div>
                            @endif

                            @if(auth('admin')->user()->hasPermission('products.read'))
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-brand w-100 p-3">
                                    <i class="bi bi-box fs-1 mb-2"></i>
                                    <div>View Products</div>
                                </a>
                            </div>
                            @endif

                            @if(auth('admin')->user()->hasPermission('customers.read'))
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-brand w-100 p-3">
                                    <i class="bi bi-people fs-1 mb-2"></i>
                                    <div>View Customers</div>
                                </a>
                            </div>
                            @endif

                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline-brand w-100 p-3">
                                    <i class="bi bi-person-gear fs-1 mb-2"></i>
                                    <div>My Profile</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
