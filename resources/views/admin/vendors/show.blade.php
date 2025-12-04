{{-- resources/views/admin/vendors/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Vendor Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                    <li class="breadcrumb-item active">{{ $vendor->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(auth('admin')->user()->hasPermission('vendors.update'))
                <button type="button" class="btn btn-outline-primary" id="syncVendorBtn" title="Sync Statistics">
                    <i class="bi bi-arrow-repeat"></i> Sync Stats
                </button>
                <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            @endif
            @if(auth('admin')->user()->hasPermission('vendors.delete'))
                <button type="button" class="btn btn-danger" id="deleteVendorBtn">
                    <i class="bi bi-trash"></i> Delete
                </button>
            @endif
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Status & Quick Info Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">{{ $vendor->name }}</h2>
                            @if($vendor->company_name)
                                <h5 class="text-muted mb-3">{{ $vendor->company_name }}</h5>
                            @endif
                            <div class="d-flex gap-2 flex-wrap">
                                {!! $vendor->getStatusBadge() !!}
                                @if($vendor->currency)
                                    <span class="badge bg-info">{{ $vendor->currency }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column gap-2">
                                @if($vendor->email)
                                    <a href="mailto:{{ $vendor->email }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-envelope"></i> {{ $vendor->email }}
                                    </a>
                                @endif
                                @if($vendor->phone)
                                    <a href="tel:{{ $vendor->phone }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-telephone"></i> {{ $vendor->phone }}
                                    </a>
                                @endif
                                @if($vendor->website)
                                    <a href="{{ $vendor->website }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-globe"></i> Visit Website
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center" style="border-left: 4px solid #5B914C;">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Products</h6>
                            <h2 class="mb-0" style="color: #5B914C;">{{ $vendor->products_count ?? 0 }}</h2>
                            @if(auth('admin')->user()->hasPermission('products.read'))
                                <a href="{{ route('admin.products.index') }}?vendor={{ $vendor->id }}" class="btn btn-sm btn-link">
                                    View Products
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center" style="border-left: 4px solid #28a745;">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Purchases</h6>
                            <h2 class="mb-0 text-success">{{ $vendor->getFormattedTotalPurchases() }}</h2>
                            <small class="text-muted">Lifetime Value</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center" style="border-left: 4px solid #17a2b8;">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Orders</h6>
                            <h2 class="mb-0 text-info">{{ $vendor->orders_count ?? 0 }}</h2>
                            <small class="text-muted">Total Orders</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Information Card -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Vendor Name:</strong>
                            <span>{{ $vendor->name }}</span>
                        </div>
                        @if($vendor->company_name)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Company Name:</strong>
                            <span>{{ $vendor->company_name }}</span>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Email:</strong>
                            <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a>
                        </div>
                        @if($vendor->phone)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Phone:</strong>
                            <a href="tel:{{ $vendor->phone }}">{{ $vendor->phone }}</a>
                        </div>
                        @endif
                        @if($vendor->mobile)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Mobile:</strong>
                            <a href="tel:{{ $vendor->mobile }}">{{ $vendor->mobile }}</a>
                        </div>
                        @endif
                        @if($vendor->website)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Website:</strong>
                            <a href="{{ $vendor->website }}" target="_blank">{{ $vendor->website }}</a>
                        </div>
                        @endif
                        @if($vendor->description)
                        <div class="col-12">
                            <strong class="text-muted d-block mb-1">Description:</strong>
                            <p class="mb-0">{{ $vendor->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Address Information Card -->
            @if($vendor->address || $vendor->city || $vendor->country)
            <div class="card mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Address Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($vendor->address)
                        <div class="col-12 mb-3">
                            <strong class="text-muted d-block mb-1">Street Address:</strong>
                            <span>{{ $vendor->address }}</span>
                        </div>
                        @endif
                        @if($vendor->city)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">City:</strong>
                            <span>{{ $vendor->city }}</span>
                        </div>
                        @endif
                        @if($vendor->state)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">State/Province:</strong>
                            <span>{{ $vendor->state }}</span>
                        </div>
                        @endif
                        @if($vendor->country)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Country:</strong>
                            <span>{{ $vendor->country }}</span>
                        </div>
                        @endif
                        @if($vendor->postal_code)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Postal Code:</strong>
                            <span>{{ $vendor->postal_code }}</span>
                        </div>
                        @endif
                        {{-- @if($vendor->hasCompleteAddress()) --}}
                        <div class="col-12">
                            <hr>
                            <strong class="text-muted d-block mb-1">Full Address:</strong>
                            <span>{{ $vendor->getFullAddress() }}</span>
                        </div>
                        {{-- @endif --}}
                    </div>
                </div>
            </div>
            @endif

            <!-- Business Information Card -->
            @if($vendor->tax_number || $vendor->registration_number)
            <div class="card mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($vendor->tax_number)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Tax Number:</strong>
                            <span>{{ $vendor->tax_number }}</span>
                        </div>
                        @endif
                        @if($vendor->registration_number)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Registration Number:</strong>
                            <span>{{ $vendor->registration_number }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Banking Information Card -->
            {{-- @if($vendor->hasBankDetails()) --}}
            <div class="card mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="mb-0"><i class="bi bi-bank"></i> Banking Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($vendor->bank_name)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Bank Name:</strong>
                            <span>{{ $vendor->bank_name }}</span>
                        </div>
                        @endif
                        @if($vendor->bank_account_name)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Account Name:</strong>
                            <span>{{ $vendor->bank_account_name }}</span>
                        </div>
                        @endif
                        @if($vendor->bank_account_number)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Account Number:</strong>
                            <span>{{ $vendor->bank_account_number }}</span>
                        </div>
                        @endif
                        @if($vendor->bank_routing_number)
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Routing Number:</strong>
                            <span>{{ $vendor->bank_routing_number }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- @endif --}}

            <!-- Recent Products -->
            @if($vendor->products->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Recent Products</h5>
                        @if(auth('admin')->user()->hasPermission('products.read'))
                            <a href="{{ route('admin.products.index') }}?vendor={{ $vendor->id }}" class="btn btn-sm btn-primary">
                                View All Products
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendor->products->take(5) as $product)
                                <tr>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                    </td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->currency }} {{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if($product->track_inventory)
                                            <span class="badge {{ $product->stock_quantity > $product->low_stock_threshold ? 'bg-success' : 'bg-warning' }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->status_key_code == 'PRODUCT_ACTIVE')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(auth('admin')->user()->hasPermission('products.read'))
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Status & Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong class="text-muted d-block mb-2">Status:</strong>
                        {!! $vendor->getStatusBadge() !!}
                    </div>
                    @if($vendor->currency)
                    <div class="mb-3">
                        <strong class="text-muted d-block mb-2">Currency:</strong>
                        <span class="badge bg-info">{{ $vendor->currency }}</span>
                    </div>
                    @endif
                    <div>
                        <strong class="text-muted d-block mb-2">Active:</strong>
                        @if($vendor->isActive())
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            @if(auth('admin')->user()->hasPermission('vendors.update'))
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" id="syncStatsBtn">
                        <i class="bi bi-arrow-repeat"></i> Sync Statistics
                    </button>
                    @if($vendor->isActive())
                        <button type="button" class="btn btn-outline-warning w-100 mb-2" onclick="changeStatus('VENDOR_INACTIVE')">
                            <i class="bi bi-pause-circle"></i> Deactivate Vendor
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-success w-100 mb-2" onclick="changeStatus('VENDOR_ACTIVE')">
                            <i class="bi bi-play-circle"></i> Activate Vendor
                        </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Audit Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Audit Trail</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong class="text-muted d-block mb-1">Created At:</strong>
                        <span>{{ $vendor->created_at->format('d M Y, h:i A') }}</span>
                        <br>
                        <small class="text-muted">{{ $vendor->created_at->diffForHumans() }}</small>
                    </div>
                    @if($vendor->creator)
                    <div class="mb-3">
                        <strong class="text-muted d-block mb-1">Created By:</strong>
                        <span>{{ $vendor->creator->name }}</span>
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong class="text-muted d-block mb-1">Last Updated:</strong>
                        <span>{{ $vendor->updated_at->format('d M Y, h:i A') }}</span>
                        <br>
                        <small class="text-muted">{{ $vendor->updated_at->diffForHumans() }}</small>
                    </div>
                    @if($vendor->updater)
                    <div>
                        <strong class="text-muted d-block mb-1">Updated By:</strong>
                        <span>{{ $vendor->updater->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Vendor ID Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-fingerprint"></i> Vendor ID</h5>
                </div>
                <div class="card-body">
                    <code class="d-block p-2 bg-light rounded">{{ $vendor->id }}</code>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    .card-header {
        font-weight: 600;
    }
    #syncVendorBtn.syncing i, #syncStatsBtn.syncing i {
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
    // Sync Statistics (Header Button)
    $('#syncVendorBtn').on('click', function() {
        syncVendorStats($(this));
    });

    // Sync Statistics (Sidebar Button)
    $('#syncStatsBtn').on('click', function() {
        syncVendorStats($(this));
    });

    function syncVendorStats($btn) {
        $btn.prop('disabled', true).addClass('syncing');

        $.ajax({
            url: '{{ route("admin.vendors.sync-individual", $vendor->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Statistics Synced!',
                        html: `
                            <p>Products: <strong>${response.data.products_count}</strong></p>
                            <p>Total Purchases: <strong>${response.data.total_purchases}</strong></p>
                        `,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to sync statistics.',
                });
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('syncing');
            }
        });
    }

    // Delete Vendor
    $('#deleteVendorBtn').on('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.vendors.destroy", $vendor->id) }}',
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.location.href = '{{ route("admin.vendors.index") }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete vendor.',
                        });
                    }
                });
            }
        });
    });
});

// Change Status Function
function changeStatus(newStatus) {
    const statusNames = {
        'VENDOR_ACTIVE': 'Activate',
        'VENDOR_INACTIVE': 'Deactivate'
    };

    Swal.fire({
        title: `${statusNames[newStatus]} Vendor?`,
        text: "Are you sure you want to change the status?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.vendors.update", $vendor->id) }}',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status_key_code: newStatus,
                    name: '{{ $vendor->name }}',
                    email: '{{ $vendor->email }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Status updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to update status.',
                    });
                }
            });
        }
    });
}
</script>
@endpush
@endsection
