{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Dashboard - Coffee Admin')

@push('styles')
<style>
    .btn-outline-brand {
        color: var(--brand-primary);
        border-color: var(--brand-primary);
    }

    .btn-outline-brand:hover {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
        color: white;
    }

    .icon {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-speedometer2 text-brand"></i> Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar3"></i> This week
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Products</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['total_products']) }}</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-brand text-white rounded-circle shadow">
                            <i class="bi bi-box fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Orders</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['total_orders']) }}</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Customers</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['total_customers']) }}</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Active Admins</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($stats['active_admins']) }}</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="bi bi-person-gear fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-brand text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-circle"></i> Welcome back, {{ $user->name }}!
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="card-text">
                            You have <strong>{{ $user->roles->count() }}</strong> role(s) assigned:
                            <span class="text-brand fw-semibold">{{ $user->roles->pluck('display_name')->join(', ') }}</span>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                Last login: {{ $user->last_login_at ? $user->last_login_at->format('F j, Y g:i A') : 'Never' }}
                                @if($user->last_login_ip)
                                    from {{ $user->last_login_ip }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-person"></i> View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(auth('admin')->user()->hasPermission('products.create'))
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-plus-circle fs-1 mb-2"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('orders.read'))
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-receipt fs-1 mb-2"></i>
                            <span>View Orders</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('inventory.read'))
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-boxes fs-1 mb-2"></i>
                            <span>Check Inventory</span>
                        </a>
                    </div>
                    @endif

                    @if(auth('admin')->user()->hasPermission('reports.read'))
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-brand w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-graph-up fs-1 mb-2"></i>
                            <span>View Reports</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
