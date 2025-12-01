{{-- resources/views/admin/vendors/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Vendors Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Vendors Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Vendors</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            {{-- Sync All Button --}}
            <button type="button" class="btn btn-outline-primary" id="syncAllBtn" title="Sync All Product Counts">
                <i class="bi bi-arrow-repeat"></i> Sync All Counts
            </button>

            @if(auth('admin')->user()->hasPermission('vendors.create'))
                <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Vendor
                </a>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statistics-cards">
        <!-- Statistics will be loaded here -->
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search vendors...">
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="VENDOR_ACTIVE">Active</option>
                        <option value="VENDOR_INACTIVE">Inactive</option>
                        <option value="VENDOR_PENDING">Pending</option>
                        <option value="VENDOR_SUSPENDED">Suspended</option>
                    </select>
                </div>

                <!-- Country Filter -->
                <div class="col-md-5">
                    <select class="form-select" id="countryFilter">
                        <option value="">All Countries</option>
                        <option value="SA">Saudi Arabia</option>
                        <option value="AE">United Arab Emirates</option>
                        <option value="PK">Pakistan</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="IN">India</option>
                        <option value="CN">China</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendors Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Vendors List</h5>
            @if(auth('admin')->user()->hasPermission('vendors.delete'))
                <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn" style="display: none;">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="vendorsTable">
                    <thead>
                        <tr>
                            @if(auth('admin')->user()->hasPermission('vendors.delete'))
                                <th width="30">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                            @endif
                            <th>Vendor Info</th>
                            <th>Location</th>
                            <th width="80">Products</th>
                            <th width="120">Total Purchases</th>
                            <th width="100">Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTable will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .statistics-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .statistics-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .statistics-card.active {
        border: 2px solid #5B914C;
        background-color: #f8f9fa;
    }
    #syncAllBtn, .sync-vendor-btn {
        position: relative;
    }
    #syncAllBtn.syncing i, .sync-vendor-btn.syncing i {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let table;
    let searchTimeout;

    // Initialize DataTable
    function initDataTable() {
        table = $('#vendorsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.vendors.data") }}',
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    d.country = $('#countryFilter').val();
                    d.search = $('#searchInput').val();
                }
            },
            columns: [
                @if(auth('admin')->user()->hasPermission('vendors.delete'))
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                @endif
                { data: 'vendor_info', name: 'name' },
                { data: 'location', name: 'city' },
                { data: 'products_count', name: 'products_count' },
                { data: 'total_purchases', name: 'total_purchases' },
                { data: 'status_badge', name: 'status_key_code' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            drawCallback: function() {
                loadStatistics();
            }
        });
    }

    initDataTable();

    // Load Statistics
    function loadStatistics() {
        $.ajax({
            url: '{{ route("admin.vendors.statistics") }}',
            method: 'GET',
            success: function(response) {
                let html = `
                    <div class="col-md-3">
                        <div class="card statistics-card" data-filter="">
                            <div class="card-body text-center">
                                <h3 class="mb-0">${response.total}</h3>
                                <p class="text-muted mb-0"><small>Total Vendors</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card statistics-card" data-filter="VENDOR_ACTIVE">
                            <div class="card-body text-center">
                                <h3 class="mb-0 text-success">${response.active}</h3>
                                <p class="text-muted mb-0"><small>Active</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card statistics-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0 text-info">${response.total_products}</h3>
                                <p class="text-muted mb-0"><small>Total Products</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card statistics-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0 text-primary">$${response.total_purchases.toFixed(2)}</h3>
                                <p class="text-muted mb-0"><small>Total Purchases</small></p>
                            </div>
                        </div>
                    </div>
                `;
                $('#statistics-cards').html(html);
            }
        });
    }

    loadStatistics();

    // Filter handlers
    $('#statusFilter, #countryFilter').on('change', function() {
        table.ajax.reload();
    });

    // Search with delay
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.ajax.reload();
        }, 500);
    });

    // Click on statistics cards to filter
    $(document).on('click', '.statistics-card', function() {
        const filter = $(this).data('filter');

        $('.statistics-card').removeClass('active');
        $(this).addClass('active');

        $('#statusFilter').val(filter).trigger('change');
    });

    // Select all checkboxes
    $('#selectAll').on('click', function() {
        $('.vendor-checkbox').prop('checked', this.checked);
        toggleBulkDeleteBtn();
    });

    $(document).on('change', '.vendor-checkbox', function() {
        toggleBulkDeleteBtn();
    });

    function toggleBulkDeleteBtn() {
        const checkedCount = $('.vendor-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').show();
        } else {
            $('#bulkDeleteBtn').hide();
        }
    }

    // =====================================================
    // SYNC ALL BUTTON HANDLER
    // =====================================================
    $('#syncAllBtn').on('click', function() {
        const $btn = $(this);

        $btn.prop('disabled', true).addClass('syncing');

        Swal.fire({
            title: 'Syncing All...',
            text: 'Please wait while we sync all vendor counts',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("admin.vendors.sync-products-count") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });

                table.ajax.reload();
                loadStatistics();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to sync',
                });
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('syncing');
            }
        });
    });

    // =====================================================
    // SYNC INDIVIDUAL VENDOR BUTTON HANDLER
    // =====================================================
    $(document).on('click', '.sync-vendor-btn', function() {
        const $btn = $(this);
        const vendorId = $btn.data('id');

        $btn.prop('disabled', true).addClass('syncing');

        $.ajax({
            url: `/admin/vendors/${vendorId}/sync`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Synced!',
                    html: `
                        <p>Products: <strong>${response.data.products_count}</strong></p>
                        <p>Total Purchases: <strong>$${response.data.total_purchases}</strong></p>
                    `,
                    timer: 2500,
                    showConfirmButton: false
                });

                table.ajax.reload(null, false); // Don't reset pagination
                loadStatistics();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to sync',
                    timer: 2000
                });
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('syncing');
            }
        });
    });

    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.vendor-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire('Warning', 'Please select vendors to delete', 'warning');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${selectedIds.length} vendor(s)!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.vendors.bulk-delete") }}',
                    method: 'POST',
                    data: {
                        vendor_ids: selectedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        table.ajax.reload();
                        $('#selectAll').prop('checked', false);
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    });

    // Delete single vendor
    $(document).on('click', '.delete-vendor', function() {
        const vendorId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/vendors/${vendorId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
