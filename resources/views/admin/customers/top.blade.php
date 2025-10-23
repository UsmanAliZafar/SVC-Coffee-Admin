@extends('admin.layouts.app')

@section('title', 'Top Customers')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .top-customer-rank {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%); }
    .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%); }
    .rank-other { background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-trophy text-warning"></i> Top Customers
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">Top Customers</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-outline-success" id="exportTopCustomers">
                <i class="bi bi-file-earmark-excel"></i> Export
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i>
        <strong>Top Customers</strong> are ranked by total spending. These are your highest-value customers.
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Top 10 Revenue</h6>
                            <h3 class="mb-0">${{ number_format($stats['top_10_revenue'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-percent fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Top 10 Contribution</h6>
                            <h3 class="mb-0">{{ number_format($stats['top_10_percentage'], 1) }}%</h3>
                            <small class="text-muted">Of total revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Customers Cards --}}
    <div class="row mb-4">
        @foreach($topCustomers->take(10) as $index => $customer)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="top-customer-rank me-3 {{
                            $index == 0 ? 'rank-1' :
                            ($index == 1 ? 'rank-2' :
                            ($index == 2 ? 'rank-3' : 'rank-other'))
                        }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">
                                <a href="{{ route('admin.customers.show', $customer->id) }}">
                                    {{ $customer->getFullName() }}
                                </a>
                            </h6>
                            {!! $customer->getTypeBadge() !!}
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <small class="text-muted d-block">Total Spent</small>
                            <strong class="text-success">${{ number_format($customer->total_spent, 2) }}</strong>
                        </div>
                        <div class="col-6 mb-2">
                            <small class="text-muted d-block">Orders</small>
                            <strong class="text-primary">{{ $customer->total_orders }}</strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Avg Order Value</small>
                            <strong>${{ number_format($customer->average_order_value, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Full Top Customers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="bi bi-trophy text-warning"></i> All Top Customers (Top 100)</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="topCustomersTable" style="width:100%">
                    <thead>
                        <tr>
                            <th width="50">Rank</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Total Orders</th>
                            <th>Total Spent</th>
                            <th>Avg Order</th>
                            <th>Last Order</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $index => $customer)
                        <tr>
                            <td>
                                <div class="top-customer-rank {{
                                    $index == 0 ? 'rank-1' :
                                    ($index == 1 ? 'rank-2' :
                                    ($index == 2 ? 'rank-3' : 'rank-other'))
                                }}" style="width: 30px; height: 30px; font-size: 0.875rem;">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $customer->getFullName() }}</strong><br>
                                    <small class="text-muted"><i class="bi bi-envelope"></i> {{ $customer->email }}</small>
                                </div>
                            </td>
                            <td>{!! $customer->getTypeBadge() !!}</td>
                            <td>{!! $customer->getStatusBadge() !!}</td>
                            <td>
                                <span class="badge bg-primary">{{ $customer->total_orders }} orders</span>
                            </td>
                            <td>
                                <strong class="text-success">${{ number_format($customer->total_spent, 2) }}</strong>
                            </td>
                            <td>
                                <strong>${{ number_format($customer->average_order_value, 2) }}</strong>
                            </td>
                            <td>
                                @if($customer->last_order_at)
                                {{ $customer->last_order_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $customer->last_order_at->diffForHumans() }}</small>
                                @else
                                <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
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

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#topCustomersTable').DataTable({
        order: [[5, 'desc']], // Order by total spent
        pageLength: 25,
        language: {
            emptyTable: "No top customers found",
            zeroRecords: "No matching customers found"
        }
    });

    // Export top customers
    $('#exportTopCustomers').on('click', function() {
        window.location.href = '{{ route("admin.customers.export") }}?top=true';
    });
});
</script>
@endpush
