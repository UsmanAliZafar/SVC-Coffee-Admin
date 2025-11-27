@extends('admin.layouts.app')

@section('title', 'Vendor Details - ' . $vendor->name)

@push('styles')
<style>
    .info-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-label {
        font-weight: 600;
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 1rem;
        color: #333;
        margin-bottom: 15px;
    }

    .vendor-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
    }

    .vendor-logo {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 10px;
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .vendor-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #ddd;
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .social-icon {
        font-size: 1.5rem;
        margin-right: 10px;
        transition: color 0.2s;
    }

    .social-icon:hover {
        color: #5B914C;
    }

    .banner-image {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .timeline-item {
        padding: 10px 0;
        border-left: 2px solid #5B914C;
        padding-left: 20px;
        margin-left: 10px;
        position: relative;
    }

    .timeline-item::before {
        content: '';
        width: 12px;
        height: 12px;
        background: #5B914C;
        border-radius: 50%;
        position: absolute;
        left: -7px;
        top: 15px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Vendor Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                    <li class="breadcrumb-item active">{{ $vendor->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('vendors.update'))
            <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Vendor
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('vendors.update'))
            <button type="button" class="btn btn-outline-success" onclick="toggleVerified()">
                <i class="bi bi-check-circle"></i> {{ $vendor->is_verified ? 'Unverify' : 'Verify' }}
            </button>
            <button type="button" class="btn btn-outline-warning" onclick="toggleFeatured()">
                <i class="bi bi-star"></i> {{ $vendor->is_featured ? 'Unfeature' : 'Feature' }}
            </button>
            @endif
            @if(auth('admin')->user()->hasPermission('vendors.delete'))
            <button type="button" class="btn btn-outline-danger" onclick="deleteVendor()">
                <i class="bi bi-trash"></i> Delete
            </button>
            @endif
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Vendor Header -->
    <div class="vendor-header">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <img src="{{ $vendor->getLogoUrl() }}" alt="{{ $vendor->name }}" class="vendor-logo">
            </div>
            <div class="col-md-7">
                <h1 class="vendor-title">{{ $vendor->name }}</h1>
                @if($vendor->company_name)
                    <p class="mb-2"><strong>Company:</strong> {{ $vendor->company_name }}</p>
                @endif
                <div class="mt-3">
                    {!! $vendor->getStatusBadge() !!}

                    @if($vendor->is_verified)
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                    @endif

                    @if($vendor->is_featured)
                        <span class="badge bg-warning"><i class="bi bi-star"></i> Featured</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3 text-end">
                <div class="stat-card bg-white">
                    <div class="stat-label">Rating</div>
                    <div class="stat-value">
                        {!! $vendor->getRatingStars() !!}
                    </div>
                    <small class="text-muted">{{ $vendor->reviews_count }} reviews</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner Image -->
    @if($vendor->banner_image)
    <div class="info-section">
        <img src="{{ $vendor->getBannerUrl() }}" alt="Banner" class="banner-image">
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">

            <!-- Contact Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-telephone"></i> Contact Information
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a>
                        </div>
                    </div>

                    @if($vendor->phone)
                    <div class="col-md-6">
                        <div class="info-label">Phone</div>
                        <div class="info-value">
                            <a href="tel:{{ $vendor->phone }}">{{ $vendor->phone }}</a>
                        </div>
                    </div>
                    @endif

                    @if($vendor->mobile)
                    <div class="col-md-6">
                        <div class="info-label">Mobile</div>
                        <div class="info-value">
                            <a href="tel:{{ $vendor->mobile }}">{{ $vendor->mobile }}</a>
                        </div>
                    </div>
                    @endif

                    @if($vendor->website)
                    <div class="col-md-6">
                        <div class="info-label">Website</div>
                        <div class="info-value">
                            <a href="{{ $vendor->website }}" target="_blank" rel="noopener">
                                {{ $vendor->website }} <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Address Information -->
            @if($vendor->hasCompleteAddress())
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-geo-alt"></i> Address Information
                </h5>

                <div class="info-value">
                    {{ $vendor->getFullAddress() }}
                </div>
            </div>
            @endif

            <!-- Description -->
            @if($vendor->description)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-file-text"></i> About
                </h5>

                <div class="info-value">
                    {{ $vendor->description }}
                </div>
            </div>
            @endif

            <!-- Business Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-briefcase"></i> Business Information
                </h5>

                <div class="row">
                    @if($vendor->tax_number)
                    <div class="col-md-6">
                        <div class="info-label">Tax Number</div>
                        <div class="info-value">{{ $vendor->tax_number }}</div>
                    </div>
                    @endif

                    @if($vendor->registration_number)
                    <div class="col-md-6">
                        <div class="info-label">Registration Number</div>
                        <div class="info-value">{{ $vendor->registration_number }}</div>
                    </div>
                    @endif

                    @if($vendor->payment_terms)
                    <div class="col-md-6">
                        <div class="info-label">Payment Terms</div>
                        <div class="info-value">{{ $vendor->payment_terms }}</div>
                    </div>
                    @endif

                    @if($vendor->credit_limit)
                    <div class="col-md-6">
                        <div class="info-label">Credit Limit</div>
                        <div class="info-value">{{ $vendor->currency }} {{ number_format($vendor->credit_limit, 2) }}</div>
                    </div>
                    @endif

                    @if($vendor->currency)
                    <div class="col-md-6">
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $vendor->currency }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Bank Information -->
            @if($vendor->hasBankDetails())
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-bank"></i> Bank Information
                </h5>

                <div class="row">
                    @if($vendor->bank_name)
                    <div class="col-md-6">
                        <div class="info-label">Bank Name</div>
                        <div class="info-value">{{ $vendor->bank_name }}</div>
                    </div>
                    @endif

                    @if($vendor->bank_account_name)
                    <div class="col-md-6">
                        <div class="info-label">Account Name</div>
                        <div class="info-value">{{ $vendor->bank_account_name }}</div>
                    </div>
                    @endif

                    @if($vendor->bank_account_number)
                    <div class="col-md-6">
                        <div class="info-label">Account Number</div>
                        <div class="info-value">{{ $vendor->bank_account_number }}</div>
                    </div>
                    @endif

                    @if($vendor->bank_routing_number)
                    <div class="col-md-6">
                        <div class="info-label">Routing Number</div>
                        <div class="info-value">{{ $vendor->bank_routing_number }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Social Media -->
            @if(!empty($vendor->getSocialMedia()))
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-share"></i> Social Media
                </h5>

                <div>
                    @if($vendor->getSocialMediaLink('facebook'))
                        <a href="{{ $vendor->getSocialMediaLink('facebook') }}" target="_blank" class="social-icon text-primary" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                    @endif

                    @if($vendor->getSocialMediaLink('twitter'))
                        <a href="{{ $vendor->getSocialMediaLink('twitter') }}" target="_blank" class="social-icon text-info" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                    @endif

                    @if($vendor->getSocialMediaLink('instagram'))
                        <a href="{{ $vendor->getSocialMediaLink('instagram') }}" target="_blank" class="social-icon text-danger" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif

                    @if($vendor->getSocialMediaLink('linkedin'))
                        <a href="{{ $vendor->getSocialMediaLink('linkedin') }}" target="_blank" class="social-icon text-primary" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Activity Timeline -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-clock-history"></i> Activity Timeline
                </h5>

                <div class="timeline-item">
                    <strong>Vendor Created</strong><br>
                    <small class="text-muted">
                        {{ $vendor->created_at->format('M d, Y H:i') }}
                        @if($vendor->creator)
                            by {{ $vendor->creator->name }}
                        @endif
                    </small>
                </div>

                @if($vendor->updated_at != $vendor->created_at)
                <div class="timeline-item">
                    <strong>Last Updated</strong><br>
                    <small class="text-muted">
                        {{ $vendor->updated_at->format('M d, Y H:i') }}
                        @if($vendor->updater)
                            by {{ $vendor->updater->name }}
                        @endif
                    </small>
                </div>
                @endif
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">

            <!-- Quick Stats -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-graph-up"></i> Quick Stats
                </h5>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Products</div>
                            <div class="stat-value">{{ $vendor->products_count }}</div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Orders</div>
                            <div class="stat-value">{{ $vendor->orders_count }}</div>
                        </div>
                    </div>

                    @if($vendor->total_purchases)
                    <div class="col-12">
                        <div class="stat-card">
                            <div class="stat-label">Total Purchases</div>
                            <div class="stat-value text-success">{{ $vendor->getFormattedTotalPurchases() }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-info-circle"></i> Vendor Details
                </h5>

                <div class="mb-3">
                    <div class="info-label">Vendor ID</div>
                    <div class="info-value"><code>{{ $vendor->id }}</code></div>
                </div>

                @if($vendor->slug)
                <div class="mb-3">
                    <div class="info-label">Slug</div>
                    <div class="info-value"><code>{{ $vendor->slug }}</code></div>
                </div>
                @endif

                <div class="mb-0">
                    <div class="info-label">Sort Order</div>
                    <div class="info-value">{{ $vendor->sort_order ?? 0 }}</div>
                </div>
            </div>

            <!-- Quick Actions -->
            @if(auth('admin')->user()->hasPermission('vendors.update'))
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-lightning"></i> Quick Actions
                </h5>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="toggleVerified()">
                        <i class="bi bi-check-circle"></i> Toggle Verified
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="toggleFeatured()">
                        <i class="bi bi-star"></i> Toggle Featured
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const vendorId = '{{ $vendor->id }}';

// Delete vendor
function deleteVendor() {
    Swal.fire({
        title: 'Delete this vendor?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.vendors.destroy", $vendor->id) }}',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '{{ route("admin.vendors.index") }}';
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete vendor'
                    });
                }
            });
        }
    });
}

// Toggle verified
function toggleVerified() {
    $.ajax({
        url: '{{ route("admin.vendors.toggle-verified", $vendor->id) }}',
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
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to update verification'
            });
        }
    });
}

// Toggle featured
function toggleFeatured() {
    $.ajax({
        url: '{{ route("admin.vendors.toggle-featured", $vendor->id) }}',
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
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to update featured status'
            });
        }
    });
}
</script>
@endpush
