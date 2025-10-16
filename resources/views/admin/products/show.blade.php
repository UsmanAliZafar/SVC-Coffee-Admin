@extends('admin.layouts.app')

@section('title', 'View Product')

@push('styles')
<style>
    .info-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    .section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
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
    .product-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .product-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .product-sku {
        font-size: 1rem;
        opacity: 0.9;
    }
    .price-display {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid #5B914C;
    }
    .price-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 5px;
    }
    .price-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
    }
    .sale-price {
        color: #dc3545;
    }
    .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 1.2rem;
    }
    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    .gallery-image {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #ddd;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .gallery-image:hover {
        transform: scale(1.05);
    }
    .gallery-image img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    .primary-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #ffc107;
        color: #000;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    .main-product-image {
        width: 100%;
        max-width: 500px;
        border-radius: 10px;
        border: 3px solid #5B914C;
        margin-bottom: 20px;
    }
    .badge-large {
        font-size: 1rem;
        padding: 8px 15px;
    }
    .stat-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #ddd;
    }
    .stat-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 5px;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }
    .variant-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
    }
    .tag-badge {
        display: inline-block;
        background: #5B914C;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        margin: 3px;
        font-size: 0.875rem;
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
    .description-content {
        line-height: 1.8;
        color: #555;
    }
    .description-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 10px 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Product Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('products.update'))
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Product
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('products.create'))
            <a href="{{ route('admin.products.duplicate', $product->id) }}" class="btn btn-outline-secondary" onclick="return confirm('Duplicate this product?')">
                <i class="bi bi-files"></i> Duplicate
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('products.delete'))
            <button type="button" class="btn btn-outline-danger" onclick="deleteProduct()">
                <i class="bi bi-trash"></i> Delete
            </button>
            @endif
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Product Header -->
    <div class="product-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="product-title">{{ $product->name }}</h1>
                <p class="product-sku mb-2">
                    <strong>SKU:</strong> {{ $product->sku }}
                    @if($product->barcode)
                        | <strong>Barcode:</strong> {{ $product->barcode }}
                    @endif
                </p>
                <div class="mt-3">
                    {!! $product->getStatusBadge() !!}

                    @if($product->is_featured)
                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>
                    @endif

                    @if($product->show_on_home)
                        <span class="badge bg-info"><i class="bi bi-house-fill"></i> Homepage</span>
                    @endif

                    @if($product->product_type !== 'simple')
                        <span class="badge bg-secondary">{{ ucfirst($product->product_type) }}</span>
                    @endif

                    @if($product->isPublished())
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Published</span>
                    @else
                        <span class="badge bg-warning"><i class="bi bi-clock"></i> Unpublished</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="price-display">
                    @if($product->isOnSale())
                        <div class="price-label">Regular Price</div>
                        <div class="original-price">{{ $product->getFormattedPrice() }}</div>
                        <div class="price-label mt-2">Sale Price</div>
                        <div class="price-value sale-price">{{ $product->getFormattedSalePrice() }}</div>
                        <span class="badge bg-danger badge-large mt-2">-{{ $product->getDiscountPercentage() }}% OFF</span>
                    @else
                        <div class="price-label">Price</div>
                        <div class="price-value">{{ $product->getFormattedPrice() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">

            <!-- Product Image & Gallery -->
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-images"></i> Product Images</h5>

                @if($product->getMainImageUrl())
                <div class="text-center mb-4">
                    <img src="{{ $product->getMainImageUrl() }}" alt="{{ $product->name }}" class="main-product-image">
                </div>
                @endif

                @if($product->images->count() > 0)
                <div class="image-gallery">
                    @foreach($product->images as $image)
                    <div class="gallery-image" onclick="viewImage('{{ $image->getImageUrl() }}')">
                        <img src="{{ $image->getImageUrl() }}" alt="{{ $image->alt_text }}">
                        @if($image->is_primary)
                            <span class="primary-badge"><i class="bi bi-star-fill"></i> Primary</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> No gallery images available
                </div>
                @endif
            </div>

            <!-- Product Description -->
            @if($product->short_description || $product->description)
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-file-text"></i> Description</h5>

                @if($product->short_description)
                <div class="mb-3">
                    <div class="info-label">Short Description</div>
                    <div class="info-value">{{ $product->short_description }}</div>
                </div>
                @endif

                @if($product->description)
                <div>
                    <div class="info-label">Full Description</div>
                    <div class="description-content">
                        {!! $product->description !!}
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Product Variants -->
            @if($product->product_type === 'variable' && $product->variants->count() > 0)
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-layers"></i> Product Variants ({{ $product->variants->count() }})</h5>

                @foreach($product->variants as $variant)
                <div class="variant-card">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <strong>{{ $variant->getFullName() }}</strong><br>
                            <small class="text-muted">SKU: {{ $variant->sku }}</small>
                        </div>
                        <div class="col-md-3">
                            @if($variant->isOnSale())
                                <span class="text-decoration-line-through text-muted">{{ $variant->getFormattedPrice() }}</span><br>
                                <strong class="text-success">{{ $variant->getFormattedSalePrice() }}</strong>
                                <span class="badge bg-danger">-{{ $variant->getDiscountPercentage() }}%</span>
                            @else
                                <strong>{{ $variant->getFormattedPrice() }}</strong>
                            @endif
                        </div>
                        <div class="col-md-3">
                            {!! $variant->getStockBadge() !!}
                        </div>
                        <div class="col-md-2 text-end">
                            {!! $variant->getStatusBadge() !!}
                            @if($variant->is_default)
                                <br><span class="badge bg-info mt-1">Default</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- SEO Information -->
            @if($product->meta_title || $product->meta_description || $product->meta_keywords)
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-search"></i> SEO Information</h5>

                @if($product->meta_title)
                <div class="mb-3">
                    <div class="info-label">Meta Title</div>
                    <div class="info-value">{{ $product->meta_title }}</div>
                </div>
                @endif

                @if($product->meta_description)
                <div class="mb-3">
                    <div class="info-label">Meta Description</div>
                    <div class="info-value">{{ $product->meta_description }}</div>
                </div>
                @endif

                @if($product->meta_keywords)
                <div class="mb-3">
                    <div class="info-label">Meta Keywords</div>
                    <div class="info-value">{{ $product->meta_keywords }}</div>
                </div>
                @endif

                @if($product->canonical_url)
                <div class="mb-3">
                    <div class="info-label">Canonical URL</div>
                    <div class="info-value">
                        <a href="{{ $product->canonical_url }}" target="_blank">{{ $product->canonical_url }}</a>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Activity Timeline -->
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-clock-history"></i> Activity Timeline</h5>

                <div class="timeline-item">
                    <strong>Product Created</strong><br>
                    <small class="text-muted">
                        {{ $product->created_at->format('M d, Y H:i') }}
                        @if($product->creator)
                            by {{ $product->creator->name }}
                        @endif
                    </small>
                </div>

                @if($product->updated_at != $product->created_at)
                <div class="timeline-item">
                    <strong>Last Updated</strong><br>
                    <small class="text-muted">
                        {{ $product->updated_at->format('M d, Y H:i') }}
                        @if($product->updater)
                            by {{ $product->updater->name }}
                        @endif
                    </small>
                </div>
                @endif

                @if($product->published_at)
                <div class="timeline-item">
                    <strong>Published</strong><br>
                    <small class="text-muted">{{ $product->published_at->format('M d, Y H:i') }}</small>
                </div>
                @endif
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">

            <!-- Quick Stats -->
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-graph-up"></i> Quick Stats</h5>

                <div class="row g-3">
                    @if($product->track_inventory)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Total Stock</div>
                            <div class="stat-value">{{ $product->getTotalStock() }}</div>
                        </div>
                    </div>
                    @endif

                    @if($product->isOnSale())
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Discount</div>
                            <div class="stat-value text-danger">{{ $product->getDiscountPercentage() }}%</div>
                        </div>
                    </div>
                    @endif

                    @if($product->variants->count() > 0)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Variants</div>
                            <div class="stat-value">{{ $product->variants->count() }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Images</div>
                            <div class="stat-value">{{ $product->images->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-info-circle"></i> Product Information</h5>

                <div class="mb-3">
                    <div class="info-label">Product Type</div>
                    <div class="info-value">{{ $product->getProductTypeLabel() }}</div>
                </div>

                @if($product->category)
                <div class="mb-3">
                    <div class="info-label">Category</div>
                    <div class="info-value">
                        <a href="{{ route('admin.categories.show', $product->category->id) }}">
                            {{ $product->category->title }}
                        </a>
                    </div>
                </div>
                @endif

                @if($product->vendor)
                <div class="mb-3">
                    <div class="info-label">Vendor</div>
                    <div class="info-value">
                        <a href="{{ route('admin.vendors.show', $product->vendor->id) }}">
                            {{ $product->vendor->name }}
                        </a>
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <div class="info-label">Currency</div>
                    <div class="info-value">{{ $product->curency }}</div>
                </div>

                @if($product->cost_price)
                <div class="mb-3">
                    <div class="info-label">Cost Price</div>
                    <div class="info-value">{{ $product->curency }} {{ number_format($product->cost_price, 2) }}</div>
                </div>
                @endif
            </div>

            <!-- Product Tags -->
            @if($product->tags->count() > 0)
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-tags"></i> Tags</h5>

                @foreach($product->tags as $tag)
                    <span class="tag-badge">{{ $tag->name }}</span>
                @endforeach
            </div>
            @endif

            <!-- Availability Settings -->
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-toggles"></i> Settings</h5>

                <div class="mb-2">
                    <i class="bi bi-{{ $product->is_available ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                    <strong>Available for Purchase:</strong>
                    {{ $product->is_available ? 'Yes' : 'No' }}
                </div>

                <div class="mb-2">
                    <i class="bi bi-{{ $product->track_inventory ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                    <strong>Track Inventory:</strong>
                    {{ $product->track_inventory ? 'Yes' : 'No' }}
                </div>

                @if($product->available_from)
                <div class="mb-2">
                    <i class="bi bi-calendar-check"></i>
                    <strong>Available From:</strong>
                    {{ $product->available_from->format('M d, Y') }}
                </div>
                @endif

                @if($product->available_until)
                <div class="mb-2">
                    <i class="bi bi-calendar-x"></i>
                    <strong>Available Until:</strong>
                    {{ $product->available_until->format('M d, Y') }}
                </div>
                @endif
            </div>

            <!-- Stock Status -->
            @if($product->track_inventory)
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-box-seam"></i> Stock Status</h5>

                <div class="alert alert-{{ $product->isInStock() ? 'success' : 'danger' }} mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $product->isInStock() ? 'In Stock' : 'Out of Stock' }}</strong><br>
                            <small>Total Units: {{ $product->getTotalStock() }}</small>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 2rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="info-section">
                <h5 class="section-title"><i class="bi bi-lightning"></i> Quick Actions</h5>

                <div class="d-grid gap-2">
                    @if(auth('admin')->user()->hasPermission('products.update'))
                    <button type="button" class="btn btn-outline-primary" onclick="toggleFeatured()">
                        <i class="bi bi-star"></i> Toggle Featured
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="toggleHomepage()">
                        <i class="bi bi-house"></i> Toggle Homepage
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="togglePublish()">
                        <i class="bi bi-upload"></i> Toggle Publish
                    </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 10; background-color: white; opacity: 1;"></button>
                <img src="" id="modalImage" class="w-100" alt="Product Image">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const productId = '{{ $product->id }}';

// View image in modal
function viewImage(imageUrl) {
    $('#modalImage').attr('src', imageUrl);
    $('#imageModal').modal('show');
}

// Delete product
function deleteProduct() {
    Swal.fire({
        title: 'Delete this product?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.products.destroy", $product->id) }}',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message
                        }).then(() => {
                            window.location.href = '{{ route("admin.products.index") }}';
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete product', 'error');
                }
            });
        }
    });
}

// Toggle featured
function toggleFeatured() {
    $.ajax({
        url: '{{ route("admin.products.toggle-featured", $product->id) }}',
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
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to toggle featured status', 'error');
        }
    });
}

// Toggle homepage
function toggleHomepage() {
    $.ajax({
        url: '{{ route("admin.products.toggle-homepage", $product->id) }}',
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
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to toggle homepage visibility', 'error');
        }
    });
}

// Toggle publish
function togglePublish() {
    $.ajax({
        url: '{{ route("admin.products.toggle-publish", $product->id) }}',
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
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to toggle publish status', 'error');
        }
    });
}
</script>
@endpush
