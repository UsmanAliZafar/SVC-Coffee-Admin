@extends('admin.layouts.app')

@section('title', 'Coupons Management')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-card .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #5B914C;
    }
    .btn-filter {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-filter:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }
    .coupon-stats {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-item .stat-value {
        font-size: 2rem;
        font-weight: bold;
    }
    .stat-item .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .progress {
        background-color: rgba(255,255,255,0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-ticket-perforated"></i> Coupons Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Coupons</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('coupons.create'))
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-filter">
                <i class="bi bi-plus-circle"></i> Create Coupon
            </a>
            @endif
            <button type="button" class="btn btn-outline-secondary d-none" id="generateCodeBtn">
                <i class="bi bi-magic"></i> Generate Code
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="coupon-stats">
        <div class="row">
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalCoupons">0</div>
                <div class="stat-label">Total Coupons</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="activeCoupons">0</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalUsage">0</div>
                <div class="stat-label">Total Uses</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalDiscount">{{ store_currency_symbol() }} 0</div>
                <div class="stat-label">Total Discount Given</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control" placeholder="Search by code or name...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Discount Type</label>
                <select id="discountTypeFilter" class="form-select">
                    <option value="">All Types</option>
                    @foreach($discountTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('discount_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Featured</label>
                <select id="featuredFilter" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('is_featured') == '1' ? 'selected' : '' }}>Featured Only</option>
                    <option value="0" {{ request('is_featured') == '0' ? 'selected' : '' }}>Non-Featured</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date Range</label>
                <select id="dateRangeFilter" class="form-select">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Bulk Actions -->
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span><strong id="selectedCount">0</strong> coupons selected</span>
                    <div>
                        @if(auth('admin')->user()->hasPermission('coupons.update'))
                        <button type="button" class="btn btn-sm btn-success" id="bulkActivate">
                            <i class="bi bi-check-circle"></i> Activate Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="bulkDeactivate">
                            <i class="bi bi-x-circle"></i> Deactivate Selected
                        </button>
                        @endif
                        @if(auth('admin')->user()->hasPermission('coupons.delete'))
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDelete">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">
                            <i class="bi bi-x"></i> Deselect All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="couponsTable" class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="120">Code</th>
                            <th>Coupon Name</th>
                            <th width="150">Discount</th>
                            <th width="150">Usage</th>
                            <th width="180">Validity</th>
                            <th width="120">Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#couponsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.coupons.data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.discount_type = $('#discountTypeFilter').val();
                d.is_featured = $('#featuredFilter').val();
                d.search = $('#searchFilter').val();
                d.date_range = $('#dateRangeFilter').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'code_badge', name: 'code' },
            { data: 'name_link', name: 'name' },
            { data: 'discount_badge', name: 'discount_type' },
            { data: 'usage_stats', name: 'total_used', orderable: true },
            { data: 'validity', name: 'valid_until', orderable: true },
            { data: 'status_badge', name: 'is_active' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: `
                <div class="datatable-loading-container">
                    <div class="bars-loader">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="datatable-loading-text">Loading Coupons...</div>
                </div>
            `
        },
        drawCallback: function() {
            updateStatistics();
        }
    });

    // Filter change events
    $('#statusFilter, #discountTypeFilter, #featuredFilter, #dateRangeFilter').on('change', function() {
        table.draw();
    });

    // Search with delay
    let searchDelay;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#searchFilter').val('');
        $('#statusFilter').val('');
        $('#discountTypeFilter').val('');
        $('#featuredFilter').val('');
        $('#dateRangeFilter').val('');
        table.draw();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.coupon-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    $(document).on('change', '.coupon-checkbox', function() {
        updateBulkActions();
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        const selectedCount = $('.coupon-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);

        if (selectedCount > 0) {
            $('#bulkActionsBar').removeClass('d-none');
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Deselect all
    $('#deselectAll').on('click', function() {
        $('.coupon-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    });

    // Toggle status
    $(document).on('click', '.toggle-status', function() {
        const couponId = $(this).data('id');

        $.ajax({
            url: '{{ route("admin.coupons.toggle-status", ":id") }}'.replace(':id', couponId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    table.draw(false);
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update status', 'error');
            }
        });
    });

    // Delete coupon
    $(document).on('click', '.delete-coupon', function() {
        const couponId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.coupons.destroy", ":id") }}'.replace(':id', couponId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.draw();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete coupon', 'error');
                    }
                });
            }
        });
    });

    // Bulk activate
    $('#bulkActivate').on('click', function() {
        bulkUpdateStatus(true);
    });

    // Bulk deactivate
    $('#bulkDeactivate').on('click', function() {
        bulkUpdateStatus(false);
    });

    // Bulk update status function
    function bulkUpdateStatus(isActive) {
        const selectedIds = $('.coupon-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        const action = isActive ? 'activate' : 'deactivate';

        Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} ${selectedIds.length} coupons?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isActive ? '#28a745' : '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action} them!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.coupons.bulk-update-status") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        coupon_ids: selectedIds,
                        is_active: isActive
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            table.draw();
                            $('#deselectAll').click();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update coupons', 'error');
                    }
                });
            }
        });
    }

    // Bulk delete
    $('#bulkDelete').on('click', function() {
        const selectedIds = $('.coupon-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Delete ' + selectedIds.length + ' coupons?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                let completed = 0;
                let failed = 0;

                selectedIds.forEach(function(id) {
                    $.ajax({
                        url: '{{ route("admin.coupons.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { completed++; },
                        error: function() { failed++; },
                        complete: function() {
                            if (completed + failed === selectedIds.length) {
                                table.draw();
                                $('#deselectAll').click();
                                Swal.fire({
                                    title: 'Bulk Delete Complete',
                                    html: `Deleted: ${completed}<br>Failed: ${failed}`,
                                    icon: failed > 0 ? 'warning' : 'success'
                                });
                            }
                        }
                    });
                });
            }
        });
    });

    // Generate random code
    $('#generateCodeBtn').on('click', function() {
        Swal.fire({
            title: 'Generate Coupon Code',
            input: 'number',
            inputLabel: 'Code Length (6-20)',
            inputValue: 8,
            inputAttributes: {
                min: 6,
                max: 20
            },
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            confirmButtonText: 'Generate',
            showLoaderOnConfirm: true,
            preConfirm: (length) => {
                return $.ajax({
                    url: '{{ route("admin.coupons.generate-code") }}',
                    type: 'GET',
                    data: { length: length }
                }).then(response => {
                    return response.code;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || 'Unknown error'}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Generated Code',
                    html: `<h3 style="color: #5B914C;">${result.value}</h3>
                           <button class="btn btn-sm btn-outline-secondary mt-2" onclick="navigator.clipboard.writeText('${result.value}')">
                               <i class="bi bi-clipboard"></i> Copy to Clipboard
                           </button>`,
                    icon: 'success'
                });
            }
        });
    });

    // Update statistics
    function updateStatistics() {
        $.ajax({
            url: '{{ route("admin.coupons.data") }}',
            data: {
                length: -1,
                _: new Date().getTime()
            },
            success: function(response) {
                const data = response.data;

                $('#totalCoupons').text(data.length);

                const active = data.filter(c => c.is_active).length;
                $('#activeCoupons').text(active);

                const totalUsage = data.reduce((sum, c) => sum + c.total_used, 0);
                $('#totalUsage').text(totalUsage);

                // Note: For total discount, you'd need to add this to the controller
                // For now, we'll show a placeholder
                $('#totalDiscount').text('{{ store_currency_symbol() }}  0');
            }
        });
    }

    // Initial statistics load
    updateStatistics();
});
</script>
@endpush
