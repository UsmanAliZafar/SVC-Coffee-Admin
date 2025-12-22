@extends('admin.layouts.app')
{{-- products/show.blade.php --}}
@section('title', 'View Product - ' . $product->name)

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

    .product-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
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
        background: rgba(255, 255, 255, 0.95);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid rgba(255, 255, 255, 0.5);
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
        transition: all 0.3s;
    }

    .gallery-image:hover {
        transform: scale(1.05);
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
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
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .badge-large {
        font-size: 1rem;
        padding: 8px 15px;
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

    .variant-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        transition: all 0.2s;
    }

    .variant-card:hover {
        border-color: #5B914C;
        box-shadow: 0 2px 8px rgba(91, 145, 76, 0.2);
    }

    .tag-badge {
        display: inline-block;
        background: #5B914C;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        margin: 3px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .tag-badge:hover {
        background: #4a7a3d;
        transform: translateY(-2px);
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

    .tax-info-box {
        background: #e7f3ff;
        border-left: 4px solid #0d6efd;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .specifications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .spec-item {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        border-left: 3px solid #5B914C;
    }

    .spec-label {
        font-size: 0.8rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .spec-value {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    .action-btn {
        transition: all 0.2s;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Shipping Information Styling */
    .info-section .info-value i {
        color: #5B914C;
        margin-right: 5px;
    }

    /* Volumetric Info */
    .info-value small {
        font-size: 0.85rem;
    }

    /* Stat Card Icon Enhancement */
    .stat-card .stat-label i {
        font-size: 0.9rem;
        margin-right: 4px;
    }

    /* Variant Badge Styling */
    .badge.bg-success i,
    .badge.bg-secondary i {
        margin-right: 4px;
    }

    /* Compact Dimension Display in Stats */
    .stat-value {
        word-break: break-word;
    }

    /* Media Gallery Styling */
    .media-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }

    .gallery-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
        background: #fff;
    }

    .gallery-item:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.3);
        transform: translateY(-5px);
    }

    /* Image Styling */
    .gallery-image {
        position: relative;
        cursor: pointer;
        overflow: hidden;
        aspect-ratio: 1;
    }

    .gallery-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-image:hover img {
        transform: scale(1.1);
    }

    /* Video Styling */
    .gallery-video {
        position: relative;
        cursor: pointer;
        aspect-ratio: 1;
        background: #000;
    }

    .video-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.4);
        transition: background 0.3s ease;
    }

    .gallery-video:hover .video-overlay {
        background: rgba(0, 0, 0, 0.6);
    }

    .video-overlay i {
        font-size: 4rem;
        color: white;
        opacity: 0.9;
        transition: all 0.3s ease;
    }

    .gallery-video:hover .video-overlay i {
        font-size: 5rem;
        opacity: 1;
    }

    /* Media Badges */
    .media-badges {
        position: absolute;
        top: 8px;
        left: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        z-index: 10;
    }

    .media-badges .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        backdrop-filter: blur(4px);
    }

    /* File Size Badge */
    .file-size-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        z-index: 10;
    }

    /* Media Info */
    .media-info {
        padding: 8px;
        background: #f8f9fa;
        border-top: 1px solid #e0e0e0;
    }

    .media-info small {
        font-size: 0.75rem;
        line-height: 1.4;
    }

    /* Main Media Container */
    .main-media-container {
        display: inline-block;
        position: relative;
    }

    .main-product-image {
        max-width: 100%;
        max-height: 500px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .main-product-image:hover {
        transform: scale(1.02);
    }

    /* Statistics Box */
    .stat-box {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        background: #e9ecef;
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .stat-box h4 {
        color: #5B914C;
        font-weight: 700;
    }

    /* Primary Badge */
    .primary-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: #ffc107;
        color: #000;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .media-gallery {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .video-overlay i {
            font-size: 3rem;
        }

        .gallery-video:hover .video-overlay i {
            font-size: 3.5rem;
        }
    }

    @media (max-width: 576px) {
        .media-gallery {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }

    /* Video Modal Styling */
    .video-modal {
        background: rgba(0, 0, 0, 0.95) !important;
    }

    .video-modal-container {
        background: #000;
        padding: 10px;
        border-radius: 8px;
    }

    .video-modal .swal2-title {
        color: white !important;
    }

    .video-modal video {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
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
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary action-btn">
                <i class="bi bi-pencil"></i> Edit Product
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('products.create'))
            <button type="button" class="btn btn-outline-secondary action-btn" onclick="duplicateProduct()">
                <i class="bi bi-files"></i> Duplicate
            </button>
            @endif
            @if(auth('admin')->user()->hasPermission('products.delete'))
            <button type="button" class="btn btn-outline-danger action-btn" onclick="deleteProduct()">
                <i class="bi bi-trash"></i> Delete
            </button>
            @endif
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary action-btn">
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

                    {{-- @if($product->isPublished())
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Published</span>
                    @else
                        <span class="badge bg-warning"><i class="bi bi-clock"></i> Unpublished</span>
                    @endif --}}

                    @if($product->track_inventory)
                        {!! $product->getStockBadge() !!}
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
            <!-- Product Media (Images & Videos) -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-camera-video"></i> Product Media
                </h5>

                @if($product->getMainImageUrl())
                <div class="text-center mb-4">
                    <div class="main-media-container">
                        <img src="{{ $product->getMainImageUrl() }}"
                            alt="{{ $product->name }}"
                            class="main-product-image"
                            onclick="viewImage('{{ $product->getMainImageUrl() }}')">
                        <div class="mt-2">
                            <span class="badge bg-primary">
                                <i class="bi bi-image"></i> Main Image
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                @if($product->images->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-grid-3x3"></i> Gallery
                            <span class="badge bg-secondary">{{ $product->images->count() }} items</span>
                        </h6>
                    </div>
                </div>

                <div class="media-gallery">
                    @foreach($product->images as $media)
                    <div class="gallery-item {{ $media->isVideo() ? 'video-item' : 'image-item' }}"
                        data-id="{{ $media->id }}"
                        data-type="{{ $media->media_type }}">

                        @if($media->isVideo())
                            <!-- Video Item -->
                            <div class="gallery-video" onclick="viewVideo('{{ $media->getMediaUrl() }}', '{{ $media->image_name }}', '{{ $media->mime_type }}')">
                                <video class="video-thumbnail">
                                    <source src="{{ $media->getMediaUrl() }}" type="{{ $media->mime_type }}">
                                </video>

                                <!-- Video Overlay -->
                                <div class="video-overlay">
                                    <i class="bi bi-play-circle-fill"></i>
                                </div>

                                <!-- Media Info Badges -->
                                <div class="media-badges">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-play-circle"></i> Video
                                    </span>
                                    @if($media->duration)
                                    <span class="badge bg-dark">
                                        {{ $media->getFormattedDuration() }}
                                    </span>
                                    @endif
                                    @if($media->is_primary)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill"></i> Primary
                                    </span>
                                    @endif
                                </div>

                                <!-- File Size -->
                                @if($media->file_size)
                                <div class="file-size-badge">
                                    {{ number_format($media->file_size / 1048576, 2) }} MB
                                </div>
                                @endif
                            </div>

                            <!-- Video Info -->
                            <div class="media-info">
                                <small class="text-muted d-block text-truncate" title="{{ $media->image_name }}">
                                    <i class="bi bi-file-earmark-play"></i> {{ $media->image_name }}
                                </small>
                                @if($media->mime_type)
                                <small class="text-muted">{{ strtoupper(str_replace('video/', '', $media->mime_type)) }}</small>
                                @endif
                            </div>
                        @else
                            <!-- Image Item -->
                            <div class="gallery-image" onclick="viewImage('{{ $media->getImageUrl() }}')">
                                <img src="{{ $media->getImageUrl() }}"
                                    alt="{{ $media->alt_text }}"
                                    loading="lazy">

                                <!-- Media Info Badges -->
                                <div class="media-badges">
                                    @if($media->is_primary)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill"></i> Primary
                                    </span>
                                    @endif
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-image"></i> Image
                                    </span>
                                </div>

                                <!-- File Size -->
                                @if($media->file_size)
                                <div class="file-size-badge">
                                    {{ number_format($media->file_size / 1048576, 2) }} MB
                                </div>
                                @endif
                            </div>

                            <!-- Image Info -->
                            <div class="media-info">
                                <small class="text-muted d-block text-truncate" title="{{ $media->image_name }}">
                                    <i class="bi bi-file-earmark-image"></i> {{ $media->image_name }}
                                </small>
                                @if($media->alt_text)
                                <small class="text-muted">{{ $media->alt_text }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> No gallery media available
                </div>
                @endif
            </div>

            <!-- Media Statistics (Optional) -->
            @if($product->images->count() > 0)
            <div class="info-section">
                <h6 class="text-muted mb-3">
                    <i class="bi bi-bar-chart"></i> Media Statistics
                </h6>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-box">
                            <i class="bi bi-image text-primary" style="font-size: 2rem;"></i>
                            <h4 class="mb-0 mt-2">{{ $product->images->where('media_type', 'image')->count() }}</h4>
                            <small class="text-muted">Images</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <i class="bi bi-play-circle text-success" style="font-size: 2rem;"></i>
                            <h4 class="mb-0 mt-2">{{ $product->images->where('media_type', 'video')->count() }}</h4>
                            <small class="text-muted">Videos</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <i class="bi bi-hdd text-info" style="font-size: 2rem;"></i>
                            <h4 class="mb-0 mt-2">{{ number_format($product->images->sum('file_size') / 1048576, 2) }}</h4>
                            <small class="text-muted">MB Total</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            {{--  --}}

            <!-- Product Description -->
            @if($product->short_description || $product->description)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-file-text"></i> Description
                </h5>

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

            <!-- Product Features Display -->
            @include('admin.products.partials.features-display', ['product' => $product])

            <!-- Specifications & Attributes -->
            @if($product->specifications || $product->attributes)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-list-check"></i> Specifications & Attributes
                </h5>

                @if($product->specifications)
                <div class="mb-4">
                    <div class="info-label mb-3">Specifications</div>
                    <div class="specifications-grid">
                        @foreach($product->specifications as $key => $value)
                        <div class="spec-item">
                            <div class="spec-label">{{ $key }}</div>
                            <div class="spec-value">{{ $value }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($product->attributes)
                <div>
                    <div class="info-label mb-3">Attributes</div>
                    <div class="specifications-grid">
                        @foreach($product->attributes as $key => $value)
                        <div class="spec-item">
                            <div class="spec-label">{{ $key }}</div>
                            <div class="spec-value">{{ $value }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Tax Information -->
            @if($product->is_taxable)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-receipt"></i> Tax Information
                </h5>

                <div class="tax-info-box">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Tax Status</div>
                            <div class="info-value">
                                <span class="badge bg-primary">Taxable</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Tax Type</div>
                            <div class="info-value">{{ $product->getTaxTypeLabel() }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Tax Percentage</div>
                            <div class="info-value">{{ $product->tax_percentage }}%</div>
                        </div>
                        @if($product->tax_class)
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Tax Class</div>
                            <div class="info-value">{{ $product->tax_class }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="alert alert-light mb-0">
                        <strong>Tax Calculation Preview:</strong><br>
                        @php
                            $taxInfo = $product->getTaxInfo();
                        @endphp
                        <small>
                            Base Price: {{ $product->curency }} {{ number_format($taxInfo['price_excluding_tax'], 2) }}<br>
                            Tax Amount: {{ $product->curency }} {{ number_format($taxInfo['tax_amount'], 2) }}<br>
                            <strong>Final Price: {{ $product->curency }} {{ number_format($taxInfo['price_including_tax'], 2) }}</strong>
                        </small>
                    </div>
                </div>
            </div>
            @endif

            <!-- Product Variants -->
            @if($product->product_type === 'variable' && $product->variants->count() > 0)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-layers"></i> Product Variants ({{ $product->variants->count() }})
                </h5>

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
            @if($product->meta_title || $product->meta_description || $product->meta_keywords || $product->canonical_url)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-search"></i> SEO Information
                </h5>

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
                        <a href="{{ $product->canonical_url }}" target="_blank" rel="noopener">
                            {{ $product->canonical_url }} <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
                @endif

                <div class="mb-0">
                    <div class="info-label">Product Slug</div>
                    <div class="info-value">
                        <code>{{ $product->slug }}</code>
                    </div>
                </div>
            </div>
            @endif

            <!-- Activity Timeline -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-clock-history"></i> Activity Timeline
                </h5>

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

                @if($product->deleted_at)
                <div class="timeline-item">
                    <strong>Deleted</strong><br>
                    <small class="text-muted">{{ $product->deleted_at->format('M d, Y H:i') }}</small>
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
                    @if($product->track_inventory)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Total Stock</div>
                            <div class="stat-value">{{ $product->getTotalStock() }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Low Stock Alert</div>
                            <div class="stat-value">{{ $product->low_stock_threshold }}</div>
                        </div>
                    </div>
                    @endif

                    @if($product->weight)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label"><i class="bi bi-box"></i> Weight</div>
                            <div class="stat-value text-primary">{{ $product->weight }} kg</div>
                        </div>
                    </div>
                    @endif

                    @if($product->length && $product->width && $product->height)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label"><i class="bi bi-rulers"></i> Dimensions</div>
                            <div class="stat-value text-info" style="font-size: 0.9rem;">
                                {{ $product->length }}×{{ $product->width }}×{{ $product->height }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($product->has_variants)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label"><i class="bi bi-grid-3x3-gap"></i> Variants</div>
                            <div class="stat-value text-success">{{ $product->variants()->count() }}</div>
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
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">You Save</div>
                            <div class="stat-value text-success">{{ $product->curency }} {{ number_format($product->getDiscountAmount(), 2) }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Images</div>
                            <div class="stat-value">{{ $product->images->count() }}</div>
                        </div>
                    </div>

                    @if($product->hasFeatures())
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Features</div>
                            <div class="stat-value">{{ count($product->getFeatures()) }}</div>
                        </div>
                    </div>
                    @endif

                    @if($product->tags->count() > 0)
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-label">Tags</div>
                            <div class="stat-value">{{ $product->tags->count() }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Product Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-info-circle"></i> Product Information
                </h5>

                <div class="mb-3">
                    <div class="info-label">Product ID</div>
                    <div class="info-value"><code>{{ $product->id }}</code></div>
                </div>

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
                    <div class="info-value">{{ $product->vendor->name }}</div>
                </div>
                @endif

                <div class="mb-3">
                    <div class="info-label">Currency</div>
                    <div class="info-value">{{ $product->curency }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Regular Price</div>
                    <div class="info-value">{{ $product->curency }} {{ number_format($product->price, 2) }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Sale Price</div>
                    <div class="info-value">{{ $product->curency }} {{ number_format($product->sale_price, 2) }}</div>
                </div>

                @if($product->cost_price)
                    <div class="mb-3">
                        <div class="info-label">Cost Price</div>
                        <div class="info-value">{{ $product->curency }} {{ number_format($product->cost_price, 2) }}</div>
                    </div>

                    @php
                        $effectivePrice = $product->isOnSale() ? $product->sale_price : $product->price;
                        $difference = $effectivePrice - $product->cost_price;
                        $isProfit = $difference > 0;
                        $percentage = $effectivePrice > 0 ? number_format(($difference / $effectivePrice) * 100, 2) : 0;
                    @endphp

                    @if($isProfit)
                    <div class="mb-3">
                        <div class="info-label">Profit Margin</div>
                        <div class="info-value text-success">
                            <i class="bi bi-arrow-up-circle"></i> {{ $product->curency }} {{ number_format($difference, 2) }}
                            ({{ $percentage }}%)
                        </div>
                    </div>
                    @elseif($difference < 0)
                    <div class="mb-3">
                        <div class="info-label">Loss</div>
                        <div class="info-value text-danger">
                            <i class="bi bi-arrow-down-circle"></i> {{ $product->curency }} {{ number_format(abs($difference), 2) }}
                            ({{ abs($percentage) }}%)
                        </div>
                    </div>
                    @else
                    <div class="mb-3">
                        <div class="info-label">Margin</div>
                        <div class="info-value text-muted">
                            <i class="bi bi-dash-circle"></i> Break Even (0%)
                        </div>
                    </div>
                    @endif
                    @endif

                <div class="mb-0">
                    <div class="info-label">Sort Order</div>
                    <div class="info-value">{{ $product->sort_order ?? 0 }}</div>
                </div>
            </div>

            <!-- Shipping Information -->
            @if($product->weight || ($product->length && $product->width && $product->height))
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-truck"></i> Shipping Information
                </h5>

                @if($product->weight)
                <div class="mb-3">
                    <div class="info-label">Weight</div>
                    <div class="info-value">
                        <i class="bi bi-box"></i> {{ $product->weight }} kg
                    </div>
                </div>
                @endif

                @if($product->length && $product->width && $product->height)
                <div class="mb-3">
                    <div class="info-label">Dimensions (L × W × H)</div>
                    <div class="info-value">
                        <i class="bi bi-rulers"></i> {{ $product->length }} × {{ $product->width }} × {{ $product->height }} cm
                    </div>
                </div>

                @php
                    $volume = $product->length * $product->width * $product->height;
                @endphp
                <div class="mb-0">
                    <div class="info-label">Volumetric</div>
                    <div class="info-value text-muted">
                        <small>{{ number_format($volume, 2) }} cm³</small>
                    </div>
                </div>
                @endif

                @if(!$product->weight && !($product->length && $product->width && $product->height))
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> No shipping dimensions configured
                </div>
                @endif
            </div>
            @endif

            <!-- Product Variants Status -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-grid-3x3-gap"></i> Variants Configuration
                </h5>

                <div class="mb-3">
                    <div class="info-label">Variant Status</div>
                    <div class="info-value">
                        @if($product->has_variants)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Variants Enabled
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-x-circle"></i> No Variants
                            </span>
                        @endif
                    </div>
                </div>

                @if($product->has_variants)
                <div class="mb-0">
                    <div class="info-label">Total Variants</div>
                    <div class="info-value">
                        <strong>{{ $product->variants()->count() }}</strong> variant(s) configured
                    </div>
                </div>

                @if($product->variants()->count() > 0)
                <div class="mt-3">
                    <a href="{{ route('admin.products.edit', $product->id) }}#variants-section" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-pencil"></i> Manage Variants
                    </a>
                </div>
                @endif
                @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> This product uses simple pricing and inventory
                </div>
                @endif
            </div>

            <!-- Product Tags -->
            @if($product->tags->count() > 0)
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-tags"></i> Tags
                </h5>

                @foreach($product->tags as $tag)
                    <span class="tag-badge">{{ $tag->name }}</span>
                @endforeach
            </div>
            @endif

            <!-- Availability Settings -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-toggles"></i> Settings
                </h5>

                <div class="mb-2">
                    <i class="bi bi-{{ $product->is_taxable ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                    <strong>Taxable:</strong>
                    {{ $product->is_taxable ? 'Yes' : 'No' }}
                </div>

                {{-- <div class="mb-2">
                    <i class="bi bi-{{ $product->requires_login ? 'check-circle text-warning' : 'x-circle text-muted' }}"></i>
                    <strong>Requires Login:</strong>
                    {{ $product->requires_login ? 'Yes' : 'No' }}
                </div> --}}

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
                <h5 class="section-title">
                    <i class="bi bi-box-seam"></i> Stock Status
                </h5>

                <div class="alert alert-{{ $product->isInStock() ? 'success' : 'danger' }} mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $product->isInStock() ? 'In Stock' : 'Out of Stock' }}</strong><br>
                            <small>Current Stock: {{ $product->stock_quantity }} units</small>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 2rem; opacity: 0.3;"></i>
                    </div>
                </div>

                @if($product->isLowStock())
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Low Stock Alert!</strong><br>
                    <small>Stock is below threshold of {{ $product->low_stock_threshold }} units</small>
                </div>
                @endif
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="bi bi-lightning"></i> Quick Actions
                </h5>

                <div class="d-grid gap-2">
                    @if(auth('admin')->user()->hasPermission('products.update'))
                    <button type="button" class="btn btn-outline-primary" onclick="toggleFeatured()">
                        <i class="bi bi-star"></i> Toggle Featured
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="toggleHomepage()">
                        <i class="bi bi-house"></i> Toggle Homepage
                    </button>
                    {{-- <button type="button" class="btn btn-outline-success" onclick="togglePublish()">
                        <i class="bi bi-upload"></i> Toggle Publish
                    </button> --}}
                    @if($product->track_inventory)
                    <button type="button" class="btn btn-outline-warning" onclick="updateStock()">
                        <i class="bi bi-boxes"></i> Update Stock
                    </button>
                    @endif
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

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-boxes"></i> Update Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStockForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product</label>
                        <input type="text" class="form-control" value="{{ $product->name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $product->stock_quantity }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Action Type</label>
                        <select name="action_type" id="actionType" class="form-select" required>
                            <option value="set">Set Stock (Replace)</option>
                            <option value="add">Add Stock (Increase)</option>
                            <option value="reduce">Reduce Stock (Decrease)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="stockQuantity" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control" value="{{ $product->low_stock_threshold }}" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Update Stock
                    </button>
                </div>
            </form>
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
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '{{ route("admin.products.index") }}';
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete product'
                    });
                }
            });
        }
    });
}

// Duplicate product
function duplicateProduct() {
    Swal.fire({
        title: 'Duplicate this product?',
        text: "A copy will be created with '(Copy)' added to the name",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, duplicate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.products.duplicate", $product->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Duplicating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Duplicated!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '/admin/products/' + response.product_id + '/edit';
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to duplicate product'
                    });
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
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to toggle featured status'
            });
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
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to toggle homepage visibility'
            });
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
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to toggle publish status'
            });
        }
    });
}

// Update stock
function updateStock() {
    $('#updateStockModal').modal('show');
}

// Handle stock update form submission
$('#updateStockForm').on('submit', function(e) {
    e.preventDefault();

    const formData = {
        _token: '{{ csrf_token() }}',
        action_type: $('#actionType').val(),
        quantity: $('#stockQuantity').val(),
        low_stock_threshold: $('input[name="low_stock_threshold"]').val()
    };

    $.ajax({
        url: '{{ route("admin.products.quick-stock-update", $product->id) }}',
        type: 'POST',
        data: formData,
        beforeSend: function() {
            $('#updateStockModal').modal('hide');
            Swal.fire({
                title: 'Updating Stock...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    confirmButtonColor: '#5B914C'
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Failed to update stock'
            });
        }
    });
});
//
function viewImage(imageUrl) {
    Swal.fire({
        imageUrl: imageUrl,
        imageAlt: 'Product Image',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            image: 'img-fluid'
        },
        width: '80%'
    });
}

// NEW: View Video in Modal
function viewVideo(videoUrl, videoName, mimeType) {
    Swal.fire({
        title: videoName || 'Product Video',
        html: `
            <div class="video-modal-container">
                <video controls autoplay style="width: 100%; max-height: 70vh; border-radius: 8px;">
                    <source src="${videoUrl}" type="${mimeType}">
                    Your browser does not support the video tag.
                </video>
            </div>
        `,
        showCloseButton: true,
        showConfirmButton: false,
        width: '90%',
        customClass: {
            popup: 'video-modal'
        },
        didOpen: () => {
            // Pause video when modal closes
            const video = Swal.getPopup().querySelector('video');
            Swal.getCloseButton().addEventListener('click', () => {
                if (video) {
                    video.pause();
                }
            });
        }
    });
}

// Load video thumbnail on page load (optional - for better performance)
document.addEventListener('DOMContentLoaded', function() {
    const videoThumbnails = document.querySelectorAll('.video-thumbnail');

    videoThumbnails.forEach(video => {
        // Set video to first frame
        video.currentTime = 1;
        video.pause();

        // Preload metadata
        video.preload = 'metadata';
    });
});
</script>
@endpush
