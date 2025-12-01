@extends('admin.layouts.app')

@section('title', 'Top Customers')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .page-header {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .top-customer-rank {
        min-width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .rank-1 {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        box-shadow: 0 6px 12px rgba(255, 215, 0, 0.4);
        animation: pulse-gold 2s infinite;
    }
    .rank-2 {
        background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%);
        box-shadow: 0 6px 12px rgba(192, 192, 192, 0.4);
    }
    .rank-3 {
        background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
        box-shadow: 0 6px 12px rgba(205, 127, 50, 0.4);
    }
    .rank-other {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
    }
    @keyframes pulse-gold {
        0%, 100% { box-shadow: 0 6px 12px rgba(255, 215, 0, 0.4); }
        50% { box-shadow: 0 8px 16px rgba(255, 215, 0, 0.6); }
    }
    .customer-card {
        border: 2px solid transparent;
        border-radius: 12px;
        transition: all 0.3s;
    }
    .customer-card:hover {
        border-color: #FFD700;
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    .customer-card.rank-1-card {
        border-color: #FFD700;
        background: linear-gradient(135deg, rgba(255, 215, 0, 0.05) 0%, rgba(255, 165, 0, 0.05) 100%);
    }
    .customer-card.rank-2-card {
        border-color: #C0C0C0;
        background: linear-gradient(135deg, rgba(192, 192, 192, 0.05) 0%, rgba(169, 169, 169, 0.05) 100%);
    }
    .customer-card.rank-3-card {
        border-color: #CD7F32;
        background: linear-gradient(135deg, rgba(205, 127, 50, 0.05) 0%, rgba(139, 69, 19, 0.05) 100%);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .medal-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .info-banner {
        background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.1) 100%);
        border-left: 4px solid #FFD700;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .revenue-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .revenue-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
        transition: width 0.5s ease;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(255, 215, 0, 0.05);
    }
    .trophy-column {
        position: relative;
    }
    .trophy-icon {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 1.25rem;
    }
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        flex-shrink: 0;
        margin-right: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-trophy-fill text-warning"></i> Top Customers
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
                    <li class="breadcrumb-item active">Top Customers</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" id="exportTopCustomers">
                <i class="bi bi-file-earmark-excel-fill"></i> Export
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="info-banner">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill text-warning fs-3 me-3"></i>
            <div>
                <h6 class="mb-1 fw-bold">About Top Customers</h6>
                <p class="mb-0 text-muted">
                    Top customers are ranked by <strong>total spending</strong>. These are your highest-value customers who contribute the most to your revenue. Focus on retaining and nurturing these VIP relationships.
                </p>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Top 10 Revenue</div>
                            <div class="stat-value">{{ store_currency_symbol() }}{{ number_format($stats['top_10_revenue'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Revenue Share</div>
                            <div class="stat-value">{{ number_format($stats['top_10_percentage'], 1) }}%</div>
                            <small class="text-muted">Top 10 contribution</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy-fill text-warning medal-icon"></i>
                    <div class="stat-value" style="font-size: 1.5rem;">Top 100</div>
                    <div class="stat-label">Elite Customers</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-primary medal-icon"></i>
                    <div class="stat-value" style="font-size: 1.25rem;">
                        {{ store_currency_symbol() }}{{ number_format($topCustomers->first()->total_spent ?? 0, 0) }}
                    </div>
                    <div class="stat-label">Highest Spender</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 3 Podium --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="mb-4 fw-bold text-center">
                <i class="bi bi-award-fill text-warning"></i> Top 3 Champions
            </h5>
            <div class="row align-items-end justify-content-center">
                {{-- 2nd Place --}}
                @if(isset($topCustomers[1]))
                <div class="col-md-4 mb-3">
                    <div class="customer-card rank-2-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <div class="avatar-circle" style="width: 60px; height: 60px; font-size: 1.5rem; background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%);">
                                    {{ strtoupper(substr($topCustomers[1]->first_name, 0, 1)) }}
                                </div>
                                <span class="trophy-icon">🥈</span>
                            </div>
                            <h5 class="fw-bold mb-1">
                                <a href="{{ route('admin.customers.show', $topCustomers[1]->id) }}" class="text-decoration-none">
                                    {{ $topCustomers[1]->getFullName() }}
                                </a>
                            </h5>
                            {!! $topCustomers[1]->getTypeBadge() !!}
                            <div class="mt-3">
                                <div class="stat-value text-success" style="font-size: 1.5rem;">
                                    {{ store_currency_symbol() }}{{ number_format($topCustomers[1]->total_spent, 2) }}
                                </div>
                                <small class="text-muted">Total Spent</small>
                            </div>
                            <div class="row mt-3 text-center">
                                <div class="col-6">
                                    <strong class="d-block">{{ $topCustomers[1]->total_orders }}</strong>
                                    <small class="text-muted">Orders</small>
                                </div>
                                <div class="col-6">
                                    <strong class="d-block">{{ store_currency_symbol() }}{{ number_format($topCustomers[1]->average_order_value, 0) }}</strong>
                                    <small class="text-muted">Avg Value</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 1st Place (Center & Larger) --}}
                @if(isset($topCustomers[0]))
                <div class="col-md-4 mb-3">
                    <div class="customer-card rank-1-card card border-0 shadow-lg h-100">
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <div class="avatar-circle" style="width: 80px; height: 80px; font-size: 2rem; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);">
                                    {{ strtoupper(substr($topCustomers[0]->first_name, 0, 1)) }}
                                </div>
                                <span class="trophy-icon" style="font-size: 2rem;">🏆</span>
                            </div>
                            <h4 class="fw-bold mb-1">
                                <a href="{{ route('admin.customers.show', $topCustomers[0]->id) }}" class="text-decoration-none">
                                    {{ $topCustomers[0]->getFullName() }}
                                </a>
                            </h4>
                            {!! $topCustomers[0]->getTypeBadge() !!}
                            <div class="mt-3">
                                <div class="stat-value text-warning" style="font-size: 2rem;">
                                    {{ store_currency_symbol() }}{{ number_format($topCustomers[0]->total_spent, 2) }}
                                </div>
                                <small class="text-muted">Total Spent</small>
                            </div>
                            <div class="row mt-3 text-center">
                                <div class="col-6">
                                    <strong class="d-block fs-5">{{ $topCustomers[0]->total_orders }}</strong>
                                    <small class="text-muted">Orders</small>
                                </div>
                                <div class="col-6">
                                    <strong class="d-block fs-5">{{ store_currency_symbol() }}{{ number_format($topCustomers[0]->average_order_value, 0) }}</strong>
                                    <small class="text-muted">Avg Value</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 3rd Place --}}
                @if(isset($topCustomers[2]))
                <div class="col-md-4 mb-3">
                    <div class="customer-card rank-3-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <div class="avatar-circle" style="width: 60px; height: 60px; font-size: 1.5rem; background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);">
                                    {{ strtoupper(substr($topCustomers[2]->first_name, 0, 1)) }}
                                </div>
                                <span class="trophy-icon">🥉</span>
                            </div>
                            <h5 class="fw-bold mb-1">
                                <a href="{{ route('admin.customers.show', $topCustomers[2]->id) }}" class="text-decoration-none">
                                    {{ $topCustomers[2]->getFullName() }}
                                </a>
                            </h5>
                            {!! $topCustomers[2]->getTypeBadge() !!}
                            <div class="mt-3">
                                <div class="stat-value text-success" style="font-size: 1.5rem;">
                                    {{ store_currency_symbol() }}{{ number_format($topCustomers[2]->total_spent, 2) }}
                                </div>
                                <small class="text-muted">Total Spent</small>
                            </div>
                            <div class="row mt-3 text-center">
                                <div class="col-6">
                                    <strong class="d-block">{{ $topCustomers[2]->total_orders }}</strong>
                                    <small class="text-muted">Orders</small>
                                </div>
                                <div class="col-6">
                                    <strong class="d-block">{{ store_currency_symbol() }}{{ number_format($topCustomers[2]->average_order_value, 0) }}</strong>
                                    <small class="text-muted">Avg Value</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Top 4-10 Cards --}}
    @if($topCustomers->count() > 3)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-star-fill text-warning"></i> Top 4-10 Customers
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($topCustomers->slice(3, 7) as $index => $customer)
                <div class="col-lg-6 mb-3">
                    <div class="card customer-card border shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="top-customer-rank rank-other me-3">
                                    {{ $index + 4 }}
                                </div>
                                <div class="avatar-circle me-2">
                                    {{ strtoupper(substr($customer->first_name, 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold">
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-decoration-none">
                                            {{ $customer->getFullName() }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ $customer->email }}</small>
                                </div>
                                {!! $customer->getTypeBadge() !!}
                            </div>

                            <div class="row mt-3 text-center">
                                <div class="col-4">
                                    <strong class="text-success d-block">{{ store_currency_symbol() }}{{ number_format($customer->total_spent, 0) }}</strong>
                                    <small class="text-muted">Total Spent</small>
                                </div>
                                <div class="col-4">
                                    <strong class="text-primary d-block">{{ $customer->total_orders }}</strong>
                                    <small class="text-muted">Orders</small>
                                </div>
                                <div class="col-4">
                                    <strong class="d-block">{{ store_currency_symbol() }}{{ number_format($customer->average_order_value, 0) }}</strong>
                                    <small class="text-muted">Avg Value</small>
                                </div>
                            </div>

                            {{-- Revenue Bar --}}
                            @php
                                $maxSpent = $topCustomers->first()->total_spent;
                                $percentage = $maxSpent > 0 ? ($customer->total_spent / $maxSpent * 100) : 0;
                            @endphp
                            <div class="revenue-bar">
                                <div class="revenue-bar-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format($percentage, 1) }}% of top spender</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Full Top Customers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-ol text-primary"></i> Complete Rankings (Top 100)
            </h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="topCustomersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="70" class="text-center"><i class="bi bi-hash"></i> Rank</th>
                            <th><i class="bi bi-person"></i> Customer</th>
                            <th class="text-center"><i class="bi bi-tag"></i> Type</th>
                            <th class="text-center"><i class="bi bi-circle-fill"></i> Status</th>
                            <th class="text-center"><i class="bi bi-cart"></i> Orders</th>
                            <th class="text-end"><i class="bi bi-currency-dollar"></i> Total Spent</th>
                            <th class="text-end"><i class="bi bi-graph-up"></i> Avg Order</th>
                            <th><i class="bi bi-clock-history"></i> Last Order</th>
                            <th width="120" class="text-center"><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $index => $customer)
                        <tr>
                            <td class="text-center">
                                <div class="top-customer-rank {{
                                    $index == 0 ? 'rank-1' :
                                    ($index == 1 ? 'rank-2' :
                                    ($index == 2 ? 'rank-3' : 'rank-other'))
                                }}" style="width: 35px; height: 35px; font-size: 0.9rem; margin: auto;">
                                    {{ $index + 1 }}
                                    @if($index == 0) 🏆
                                    @elseif($index == 1) 🥈
                                    @elseif($index == 2) 🥉
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2">
                                        {{ strtoupper(substr($customer->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $customer->getFullName() }}</strong>
                                        @if($customer->is_verified)
                                            <i class="bi bi-patch-check-fill text-success"></i>
                                        @endif
                                        <br>
                                        <small class="text-muted"><i class="bi bi-envelope"></i> {{ $customer->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{!! $customer->getTypeBadge() !!}</td>
                            <td class="text-center">{!! $customer->getStatusBadge() !!}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $customer->total_orders }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">{{ store_currency_symbol() }}{{ number_format($customer->total_spent, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>{{ store_currency_symbol() }}{{ number_format($customer->average_order_value, 2) }}</strong>
                            </td>
                            <td>
                                @if($customer->last_order_at)
                                    {{ $customer->last_order_at->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $customer->last_order_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    @if(auth('admin')->user()->hasPermission('customers.read'))
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif

                                    @if(auth('admin')->user()->hasPermission('customers.update'))
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#topCustomersTable').DataTable({
        order: [[5, 'desc']], // Order by total spent
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            emptyTable: "No top customers found",
            zeroRecords: "No matching customers found",
            info: "Showing _START_ to _END_ of _TOTAL_ top customers",
            infoEmpty: "Showing 0 to 0 of 0 customers"
        },
        columnDefs: [
            { orderable: false, targets: [0, 8] } // Disable sorting on rank and actions
        ]
    });

    // Export top customers
    $('#exportTopCustomers').on('click', function() {
        window.location.href = '{{ route("admin.customers.export") }}?top=true&limit=100';

        Swal.fire({
            icon: 'info',
            title: 'Exporting...',
            text: 'Exporting top 100 customers to CSV',
            timer: 2000,
            showConfirmButton: false
        });
    });
});
</script>
@endpush
