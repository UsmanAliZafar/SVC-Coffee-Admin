@extends('admin.layouts.app')

@section('title', 'Customer Reports & Analytics')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-bar-chart text-primary"></i> Customer Reports & Analytics
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Customers
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Customers</h6>
                    <h3 class="mb-0">{{ number_format($stats['total_customers']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Active Customers</h6>
                    <h3 class="mb-0 text-success">{{ number_format($stats['active_customers']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">New This Month</h6>
                    <h3 class="mb-0 text-info">{{ number_format($stats['new_this_month']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Lifetime Value</h6>
                    <h3 class="mb-0 text-warning">${{ number_format($stats['total_lifetime_value'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Reports Section --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Customer Analytics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Average Customer Value</h6>
                    <h4 class="text-success">${{ number_format($stats['avg_customer_value'], 2) }}</h4>
                </div>
                <div class="col-md-6">
                    <h6>Customer Distribution</h6>
                    <ul class="list-unstyled">
                        <li><strong>Active:</strong> {{ $stats['active_customers'] }}</li>
                        <li><strong>New:</strong> {{ $stats['new_this_month'] }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
