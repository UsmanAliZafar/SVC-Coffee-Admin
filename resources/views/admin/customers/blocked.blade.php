@extends('admin.layouts.app')

@section('title', 'Blocked Customers')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-lock text-danger"></i> Blocked Customers
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">Blocked</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Blocked Customers</h6>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['total_blocked']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Blocked Customers List</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover" id="blockedCustomersTable">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Blocked Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#blockedCustomersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.customers.data") }}',
            data: function(d) { d.status = 'CUSTOMER_BLOCKED'; }
        },
        columns: [
            { data: 'customer_info', name: 'first_name' },
            { data: 'customer_type' },
            { data: 'orders_info' },
            { data: 'total_spent', render: (d) => '$' + parseFloat(d).toFixed(2) },
            { data: 'created_at_formatted' },
            { data: 'actions', orderable: false }
        ]
    });
});
</script>
@endpush
