@extends('admin.layouts.app')

@section('title', 'Returning Customers')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-arrow-repeat text-primary"></i> Returning Customers
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">Returning</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i>
        <strong>Returning Customers</strong> are customers who have placed 2 or more orders. These are your repeat buyers.
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-arrow-repeat fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Returning Customers</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_returning']) }}</h3>
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
                            <h6 class="text-muted mb-1">Repeat Customer Rate</h6>
                            <h3 class="mb-0">{{ number_format($stats['repeat_rate'], 1) }}%</h3>
                            <small class="text-muted">Of total customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="bi bi-arrow-repeat text-primary"></i> Returning Customers List</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="returningCustomersTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Total Orders</th>
                            <th>Total Spent</th>
                            <th>Last Order</th>
                            <th>Registered</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data loaded via AJAX --}}
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
    const table = $('#returningCustomersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.customers.data") }}',
            data: function(d) {
                // Filter for returning customers only (2+ orders)
                d.returning = true;
            }
        },
        columns: [
            { data: 'customer_info', name: 'first_name' },
            { data: 'customer_type', name: 'customer_type' },
            { data: 'status_badge', name: 'status_key_code' },
            { data: 'total_orders', name: 'total_orders', render: function(data) {
                return '<span class="badge bg-primary">' + data + ' orders</span>';
            }},
            { data: 'total_spent', name: 'total_spent', render: function(data) {
                return '<strong>$' + parseFloat(data).toFixed(2) + '</strong>';
            }},
            { data: 'last_order', name: 'last_order_at' },
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']], // Order by total orders
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No returning customers found",
            zeroRecords: "No matching customers found"
        }
    });

    // Delete customer
    $(document).on('click', '.delete-customer', function() {
        const customerId = $(this).data('id');

        Swal.fire({
            title: 'Delete Customer?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/customers/${customerId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete customer', 'error');
                    }
                });
            }
        });
    });

    // Toggle status
    $(document).on('click', '.toggle-status-btn', function() {
        const customerId = $(this).data('id');
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
                    url: `/admin/customers/${customerId}/toggle-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update status', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
