@extends('admin.layouts.app')

@section('title', "Today's Orders")

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Today's Orders</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Today</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('orders.create'))
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Order
            </a>
            @endif
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Orders
            </a>
        </div>
    </div>

    {{-- Date Badge --}}
    <div class="mb-4">
        <span class="badge bg-primary fs-6 px-3 py-2">
            <i class="bi bi-calendar-day"></i> {{ now()->format('l, F j, Y') }}
        </span>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Orders</h6>
                            <h3 class="mb-0" id="totalOrdersCount">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Revenue</h6>
                            <h3 class="mb-0" id="totalRevenue">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0" id="pendingOrdersCount">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="bi bi-bar-chart fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Average Order</h6>
                            <h3 class="mb-0" id="averageOrderValue">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Orders Placed Today</h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filtersCollapse">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                    <button class="btn btn-outline-success btn-sm" id="refreshBtn">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="collapse" id="filtersCollapse">
            <div class="card-body border-bottom bg-light">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Order Status</label>
                            <select class="form-select" name="status" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="ORDER_PENDING">Pending</option>
                                <option value="ORDER_CONFIRMED">Confirmed</option>
                                <option value="ORDER_PROCESSING">Processing</option>
                                <option value="ORDER_PACKED">Packed</option>
                                <option value="ORDER_SHIPPED">Shipped</option>
                                <option value="ORDER_DELIVERED">Delivered</option>
                                <option value="ORDER_CANCELLED">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" name="payment_status" id="filterPaymentStatus">
                                <option value="">All Payment Statuses</option>
                                <option value="PAYMENT_PENDING">Pending</option>
                                <option value="PAYMENT_PAID">Paid</option>
                                <option value="PAYMENT_PARTIALLY_PAID">Partially Paid</option>
                                <option value="PAYMENT_REFUNDED">Refunded</option>
                                <option value="PAYMENT_PARTIALLY_REFUNDED">Partially Refunded</option>
                                <option value="PAYMENT_FAILED">Failed</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Order Source</label>
                            <select class="form-select" name="order_source" id="filterSource">
                                <option value="">All Sources</option>
                                <option value="web">Web</option>
                                <option value="mobile">Mobile</option>
                                <option value="pos">POS</option>
                                <option value="phone">Phone</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Time Range</label>
                            <select class="form-select" name="time_range" id="filterTimeRange">
                                <option value="">All Day</option>
                                <option value="morning">Morning (6AM-12PM)</option>
                                <option value="afternoon">Afternoon (12PM-6PM)</option>
                                <option value="evening">Evening (6PM-12AM)</option>
                                <option value="night">Night (12AM-6AM)</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                    <i class="bi bi-search"></i> Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="ordersTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th width="80">Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Time</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTable will populate this --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" id="bulkActionsBar" style="z-index: 1050;">
        <div class="card shadow-lg border-0">
            <div class="card-body py-2 px-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold"><span id="selectedCount">0</span> selected</span>
                    <div class="vr"></div>
                    @if(auth('admin')->user()->hasPermission('orders.delete'))
                    <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this order? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Order</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let ordersTable;
    let selectedOrders = [];
    let deleteOrderId = null;

    // Get today's date range
    const today = new Date();
    const todayStart = today.toISOString().split('T')[0];

    // Initialize DataTable
    function initDataTable() {
        ordersTable = $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.date_from = todayStart;
                    d.date_to = todayStart;
                    d.status = $('#filterStatus').val();
                    d.payment_status = $('#filterPaymentStatus').val();
                    d.order_source = $('#filterSource').val();
                    d.time_range = $('#filterTimeRange').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'order_number_link', name: 'order_number' },
                { data: 'customer_info', name: 'customer_id', orderable: false },
                { data: 'items_count', name: 'items_count', searchable: false },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'payment_info', name: 'payment_status_key_code' },
                { data: 'status_badge', name: 'status_key_code' },
                { data: 'order_source', name: 'order_source' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[8, 'desc']],
            pageLength: 25,
            language: {
                processing: `
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Loading Today's Orders...</div>
                    </div>
                `
            },
            drawCallback: function() {
                updateCheckboxStates();
                updateStatistics();
            }
        });
    }

    initDataTable();

    // Update Statistics
    function updateStatistics() {
        $.ajax({
            url: "{{ route('admin.orders.data') }}",
            data: {
                date_from: todayStart,
                date_to: todayStart,
                get_stats: true
            },
            success: function(response) {
                if (response.stats) {
                    $('#totalOrdersCount').text(response.stats.total_orders || 0);
                    $('#totalRevenue').text('$' + (response.stats.total_revenue || 0).toFixed(2));
                    $('#pendingOrdersCount').text(response.stats.pending_orders || 0);
                    $('#averageOrderValue').text('$' + (response.stats.average_order || 0).toFixed(2));
                }
            }
        });
    }

    // Refresh Button
    $('#refreshBtn').on('click', function() {
        const btn = $(this);
        btn.find('i').addClass('rotating');
        ordersTable.ajax.reload(function() {
            btn.find('i').removeClass('rotating');
        });
    });

    // Apply Filters
    $('#applyFilters').on('click', function() {
        ordersTable.ajax.reload();
    });

    // Clear Filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        ordersTable.ajax.reload();
    });

    // Select All Checkboxes
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.order-checkbox').prop('checked', isChecked);

        if (isChecked) {
            $('.order-checkbox').each(function() {
                const orderId = $(this).val();
                if (!selectedOrders.includes(orderId)) {
                    selectedOrders.push(orderId);
                }
            });
        } else {
            selectedOrders = [];
        }

        updateBulkActionsBar();
    });

    // Individual Checkbox
    $(document).on('change', '.order-checkbox', function() {
        const orderId = $(this).val();

        if ($(this).is(':checked')) {
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
            }
        } else {
            selectedOrders = selectedOrders.filter(id => id !== orderId);
            $('#selectAll').prop('checked', false);
        }

        updateBulkActionsBar();
    });

    // Clear Selection
    $('#clearSelection').on('click', function() {
        selectedOrders = [];
        $('.order-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActionsBar();
    });

    // Update Checkbox States
    function updateCheckboxStates() {
        $('.order-checkbox').each(function() {
            const orderId = $(this).val();
            if (selectedOrders.includes(orderId)) {
                $(this).prop('checked', true);
            }
        });
    }

    // Update Bulk Actions Bar
    function updateBulkActionsBar() {
        if (selectedOrders.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
            $('#selectedCount').text(selectedOrders.length);
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Delete Order
    $(document).on('click', '.delete-order', function() {
        deleteOrderId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (deleteOrderId) {
            $.ajax({
                url: `/admin/orders/${deleteOrderId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        ordersTable.ajax.reload();
                        selectedOrders = selectedOrders.filter(id => id !== deleteOrderId);
                        updateBulkActionsBar();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }

                    deleteOrderId = null;
                },
                error: function(xhr) {
                    $('#deleteModal').modal('hide');

                    let errorMessage = 'An error occurred while deleting the order.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });

                    deleteOrderId = null;
                }
            });
        }
    });

    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        if (selectedOrders.length === 0) return;

        Swal.fire({
            icon: 'warning',
            title: 'Confirm Bulk Delete',
            text: `Are you sure you want to delete ${selectedOrders.length} order(s)?`,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Coming Soon',
                    text: 'Bulk delete functionality will be implemented.'
                });
            }
        });
    });

    // Auto-refresh every 5 minutes
    setInterval(function() {
        ordersTable.ajax.reload(null, false);
    }, 300000); // 5 minutes
});
</script>

<style>
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.rotating {
    animation: rotate 1s linear infinite;
}
</style>
@endpush
