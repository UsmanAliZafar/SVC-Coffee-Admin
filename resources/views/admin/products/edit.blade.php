@extends('admin.layouts.app')
{{--  products/edite.blade.php --}}
@section('title', 'Edit Product')

@push('styles')
<style>
    .form-section {
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
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
    }
    .required-field::after {
        content: " *";
        color: #dc3545;
    }
    .btn-save {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        min-width: 120px;
    }
    .btn-save:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }
    .image-preview-container {
        position: relative;
        display: inline-block;
        margin: 10px;
    }
    .image-preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #ddd;
    }
    .remove-image {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        z-index: 10;
    }
    .set-primary-btn {
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
        background: #5B914C;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .image-preview-container:hover .set-primary-btn {
        opacity: 1;
    }
    .primary-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        background: #ffc107;
        color: #000;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
    }
    .dropzone-area {
        border: 2px dashed #5B914C;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
    }
    .dropzone-area:hover {
        background: #e9ecef;
        border-color: #4a7a3d;
    }
    .dropzone-area.dragover {
        background: #d4edda;
        border-color: #28a745;
    }
    .ck-editor__editable {
        min-height: 300px;
    }
    .tags-container {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px;
        min-height: 42px;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        cursor: text;
    }
    .tag-item {
        display: inline-flex;
        align-items: center;
        background: #5B914C;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.875rem;
    }
    .tag-item .remove-tag {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
        background: none;
        border: none;
        color: white;
        padding: 0;
        font-size: 1.2rem;
    }
    .tag-suggestions {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        width: 100%;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .tag-suggestion-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    .tag-suggestion-item:hover {
        background: #f8f9fa;
    }
    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
        padding: 5px;
    }
    .add-btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: #5B914C;
        color: white;
        border-radius: 50%;
        cursor: pointer;
        margin-left: 10px;
        transition: all 0.2s;
    }
    .add-btn-icon:hover {
        background: #4a7a3d;
        transform: scale(1.1);
    }
    .product-info-badge {
        background: #e9ecef;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .product-info-badge .badge {
        font-size: 0.85rem;
        padding: 6px 12px;
        margin-right: 5px;
    }

    .url-handle-container .input-group-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .url-handle-container code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.875rem;
    }

    .url-handle-container .list-group-item {
        padding: 10px 15px;
    }

    #urlEditSection .alert {
        font-size: 0.875rem;
        padding: 10px 15px;
    }

    /* Tags Styling */
    .tags-container {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px;
        min-height: 46px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        cursor: text;
        background: #fff;
        transition: border-color 0.15s ease-in-out;
    }

    .tags-container:hover {
        border-color: #5B914C;
    }

    .tags-container:focus-within {
        border-color: #5B914C;
        box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 4px 8px 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        gap: 6px;
        animation: tagSlideIn 0.2s ease-out;
        transition: all 0.2s;
    }

    @keyframes tagSlideIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .tag-item:hover {
        background: linear-gradient(135deg, #4a7a3d 0%, #3d6632 100%);
        transform: translateY(-1px);
    }

    .tag-item .tag-name {
        line-height: 1;
    }

    .tag-item .remove-tag {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1rem;
        line-height: 1;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .tag-item .remove-tag:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 150px;
        padding: 4px;
        font-size: 0.875rem;
    }

    .tag-suggestions {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1050;
        width: 100%;
        margin-top: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        animation: suggestionsSlideDown 0.2s ease-out;
    }

    @keyframes suggestionsSlideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tag-suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tag-suggestion-item:last-child {
        border-bottom: none;
    }

    .tag-suggestion-item:hover {
        background: #f8f9fa;
        padding-left: 20px;
    }

    .tag-suggestion-item i {
        color: #5B914C;
        font-size: 0.9rem;
    }

    .tag-suggestion-item.tag-create-new {
        background: #f0f7ed;
        font-weight: 500;
    }

    .tag-suggestion-item.tag-create-new:hover {
        background: #e1f0da;
    }

    .tag-suggestion-item.tag-create-new i {
        color: #4a7a3d;
    }

    /* Dimensions Input Styling */
    .row.g-2 input {
        text-align: center;
        font-weight: 600;
    }

    .row.g-2 small {
        display: block;
        text-align: center;
        margin-top: 4px;
        font-weight: 600;
        color: #5B914C;
    }

    /* Shipping Info Display */
    .alert-info p {
        margin-bottom: 0.5rem;
    }

    .alert-info p:last-child {
        margin-bottom: 0;
    }

    /* Disabled Checkbox Styling */
    input[type="checkbox"]:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Variant Section Styling */
    .card.border-primary {
        border-width: 2px;
    }

    .card-header.bg-primary {
        background-color: #5B914C !important;
        border-bottom: 2px solid #4a7a3d;
    }

    #taxPercentage:disabled {
        background-color: #f8f9fa !important;
        cursor: not-allowed;
        color: #6c757d;
    }

    /* Select2 with Icon Button Styling */
    .input-group .select2-container {
        flex: 1;
    }

    .add-btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background-color: #5B914C;
        color: white;
        border: 1px solid #5B914C;
        border-radius: 0 0.375rem 0.375rem 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .add-btn-icon:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }

    .add-btn-icon i {
        font-size: 1.2rem;
    }

    /* Adjust Select2 to work with input-group */
    .input-group .select2-container .select2-selection--single {
        border-radius: 0.375rem 0 0 0.375rem;
        border-right: none;
    }

    /* Video Preview Styling */
    .video-preview-wrapper {
        position: relative;
        width: 150px;
        height: 150px;
    }

    .video-preview-wrapper video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #ddd;
    }

    .image-preview-container .badge {
        font-size: 0.7rem;
        padding: 3px 6px;
        z-index: 5;
    }

    /* Video hover effects */
    .video-preview-wrapper:hover {
        border-color: #5B914C;
    }

    .video-preview-wrapper video::-webkit-media-controls-panel {
        background: rgba(0, 0, 0, 0.7);
    }

    /* Media type badge colors */
    .badge.bg-primary {
        background-color: #5B914C !important;
    }

    .badge.bg-dark {
        background-color: rgba(0, 0, 0, 0.8) !important;
    }

    /* Upload Widget - Google Drive Style */
    .upload-widget {
        position: fixed;
        bottom: -400px;
        right: 20px;
        width: 400px;
        max-height: 500px;
        background: white;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        transition: bottom 0.3s ease, max-height 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .upload-widget.show {
        bottom: 0;
    }

    .upload-widget.minimized {
        max-height: 60px;
    }

    .upload-widget.minimized .upload-widget-body {
        display: none;
    }

    /* Widget Header */
    .upload-widget-header {
        padding: 15px;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .upload-widget-header strong {
        font-size: 0.95rem;
    }

    .upload-actions {
        display: flex;
        gap: 8px;
    }

    .btn-widget-minimize,
    .btn-widget-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-widget-minimize:hover,
    .btn-widget-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .overall-progress small {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .overall-progress .progress {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .overall-progress .progress-bar {
        background: white !important;
        transition: width 0.3s ease;
    }

    /* Widget Body */
    .upload-widget-body {
        padding: 15px;
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .upload-files-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* File Item */
    .upload-file-item {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid #dee2e6;
        transition: all 0.3s;
    }

    .upload-file-item.success {
        border-left-color: #28a745;
        background: #d4edda;
    }

    .upload-file-item.error {
        border-left-color: #dc3545;
        background: #f8d7da;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .file-info i {
        font-size: 1.2rem;
    }

    .file-name {
        flex: 1;
        font-size: 0.85rem;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-size {
        font-size: 0.75rem;
    }

    .file-progress .progress {
        height: 4px;
        background: #dee2e6;
        border-radius: 2px;
        margin-bottom: 4px;
    }

    .file-progress .progress-bar {
        background: #5B914C;
        transition: width 0.3s ease;
    }

    .file-status {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Scrollbar Styling */
    .upload-widget-body::-webkit-scrollbar {
        width: 6px;
    }

    .upload-widget-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .upload-widget-body::-webkit-scrollbar-thumb {
        background: #5B914C;
        border-radius: 3px;
    }

    .upload-widget-body::-webkit-scrollbar-thumb:hover {
        background: #4a7a3d;
    }

    /* Validation Error Modal */
    .validation-error-modal ul {
        padding-left: 20px;
    }

    .validation-error-modal ul li {
        margin-bottom: 10px;
    }

    .validation-error-modal .alert {
        text-align: left;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .upload-widget {
            right: 10px;
            left: 10px;
            width: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil-square"></i> Edit Product</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">Edit: {{ $product->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> View Product
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Product Info Badge -->
    <div class="product-info-badge">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Product ID:</strong> <code>{{ $product->id }}</code>
                <span class="ms-3"><strong>SKU:</strong> <code>{{ $product->sku }}</code></span>
                @if($product->barcode)
                <span class="ms-3"><strong>Barcode:</strong> <code>{{ $product->barcode }}</code></span>
                @endif
            </div>
            <div>
                {!! $product->getStatusBadge() !!}
                {!! $product->getStockBadge() !!}
                @if($product->is_featured)
                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>
                @endif
            </div>
        </div>
        <div class="mt-2 text-muted small">
            <i class="bi bi-clock"></i> Created: {{ $product->created_at->format('M d, Y H:i') }}
            | Last Updated: {{ $product->updated_at->format('M d, Y H:i') }}
            @if($product->creator)
            | By: {{ $product->creator->name }}
            @endif
        </div>
    </div>

    <form id="productForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">

                <!-- Basic Information -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>

                    <div class="mb-3">
                        <label class="form-label required-field">Product Name</label>
                        <input type="text" name="name" id="productName" class="form-control" value="{{ $product->name }}" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input readonly type="text" name="slug" id="productSlug" class="form-control" value="{{ $product->slug }}">
                            <div class="form-text">To Update Url Check  SEO Settings. Can'nt be simple.</div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">SKU</label>
                            <input type="text" name="sku" id="productSku" class="form-control" value="{{ $product->sku }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" value="{{ $product->barcode }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Type</label>
                        <input type="text" name="product_type" class="form-control" placeholder="e.g., simple, digital, etc." value="{{ $product->product_type }}">
                        <div class="form-text">Simple product type by default</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="3">{{ $product->short_description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" id="productDescription" class="form-control">{{ $product->description }}</textarea>
                    </div>
                </div>

                <!-- Product Images & Videos -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-camera-video"></i> Product Media (Images & Videos)</h5>

                    <div class="mb-3">
                        <label class="form-label">Main Image</label>
                        <input type="file" name="main_image" id="mainImage" class="form-control" accept="image/*">
                        <div class="form-text">Upload new image to replace current (max 2MB)</div>
                        <div id="mainImagePreview" class="mt-2">
                            @if($product->main_image)
                            <div class="image-preview-container">
                                <img src="{{ $product->getMainImageUrl() }}" class="image-preview" alt="Main Image">
                                <button type="button" class="remove-image" onclick="removeMainImage()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gallery Media (Images & Videos)</label>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Supported Formats:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Images:</strong> PNG, JPG, GIF, WEBP up to 2MB each</li>
                                <li><strong>Videos:</strong> MP4, MOV, AVI, WMV, FLV, WEBM up to 50MB each</li>
                            </ul>
                        </div>
                        <div class="dropzone-area" id="dropzoneArea">
                            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #5B914C;"></i>
                            <p class="mb-2"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted mb-0">Images & Videos supported</p>
                        </div>
                        <input type="file" name="images[]" id="galleryImages" class="d-none"
                            accept="image/*,video/*" multiple>

                        <div id="galleryPreview" class="mt-3">
                            @foreach($product->images as $media)
                            <div class="image-preview-container" data-id="{{ $media->id }}">
                                @if($media->is_primary)
                                <span class="primary-badge"><i class="bi bi-star-fill"></i> Primary</span>
                                @endif

                                @if($media->isVideo())
                                    <!-- Video Preview -->
                                    <div class="video-preview-wrapper" style="position: relative;">
                                        <video class="image-preview" controls>
                                            <source src="{{ $media->getMediaUrl() }}" type="{{ $media->mime_type }}">
                                            Your browser does not support the video tag.
                                        </video>
                                        <span class="badge bg-primary" style="position: absolute; top: 5px; left: 5px;">
                                            <i class="bi bi-play-circle"></i> Video
                                        </span>
                                        @if($media->duration)
                                        <span class="badge bg-dark" style="position: absolute; top: 5px; right: 40px;">
                                            {{ $media->getFormattedDuration() }}
                                        </span>
                                        @endif
                                        @if($media->file_size)
                                        <span class="badge bg-secondary" style="position: absolute; bottom: 35px; left: 5px;">
                                            {{ number_format($media->file_size / 1048576, 2) }} MB
                                        </span>
                                        @endif
                                    </div>
                                @else
                                    <!-- Image Preview -->
                                    <img src="{{ $media->getImageUrl() }}" class="image-preview" alt="{{ $media->image_name }}">
                                @endif

                                <button type="button" class="remove-image" onclick="deleteProductImage('{{ $product->id }}', '{{ $media->id }}', this)">
                                    <i class="bi bi-x"></i>
                                </button>

                                @if(!$media->is_primary && $media->isImage())
                                <button type="button" class="set-primary-btn" onclick="setPrimaryImage('{{ $product->id }}', '{{ $media->id }}')">
                                    <i class="bi bi-star"></i> Set Primary
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!--  FeatureLinting. -->
                @include('admin.products.partials.features', ['product' => $product])
                <!--   -->

                <!-- Product Variants -->
                <div class="form-section" id="variants-section">
                    <h5 class="section-title"><i class="bi bi-grid-3x3-gap"></i> Product Variants</h5>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="has_variants" id="hasVariants" class="form-check-input"
                            {{ old('has_variants', $product->has_variants) ? 'checked' : '' }}
                            onchange="toggleVariantsSection()">
                        <label class="form-check-label" for="hasVariants">
                            <i class="bi bi-layers"></i> This product has variants
                        </label>
                        <div class="form-text">
                            Enable this if product has multiple options (size, color, weight, etc.)
                        </div>
                    </div>

                    <div id="variantsContent" style="display: {{ $product->has_variants ? 'block' : 'none' }};">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i> <strong>Variants Active</strong><br>
                            <small>This product uses variants for pricing and inventory.</small>
                        </div>

                        <!-- Add Variant Button -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="openAddVariantModal()">
                                <i class="bi bi-plus-circle"></i> Add New Variant
                            </button>
                        </div>

                        <!-- Variants List -->
                        <div id="variantsList">
                            @if($product->variants->count() > 0)
                                @foreach($product->variants as $variant)
                                    @include('admin.products.partials.variant-card', ['variant' => $variant])
                                @endforeach
                            @else
                                <div class="alert alert-warning" id="noVariantsAlert">
                                    <i class="bi bi-exclamation-triangle"></i> No variants added yet. Click "Add New Variant" to get started.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div id="noVariantsMessage" style="display: {{ $product->has_variants ? 'none' : 'block' }};">
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> <strong>Simple Product Mode</strong><br>
                            <small>This product uses basic pricing and inventory. Enable variants above if you need multiple options.</small>
                        </div>
                    </div>
                </div>
                <!-- Product Variants -->

                <!-- SEO Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-search"></i> SEO Settings</h5>

                    <!-- URL Handle -->
                    <div class="mb-3">
                        <label class="form-label">URL Handle</label>
                        <div class="url-handle-container">
                            <div class="input-group" id="urlHandleDisplay">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-link-45deg"></i> {{ url('/') }}/products/
                                </span>
                                <input type="text" class="form-control bg-light" value="{{ $product->slug }}" readonly>
                                <button type="button" class="btn btn-outline-secondary" id="editUrlBtn" title="Edit URL">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>

                            <!-- Edit URL Section (Hidden by default) -->
                            <div id="urlEditSection" class="mt-3" style="display: none;">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Warning:</strong> Changing the URL will affect SEO and existing links.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New URL Slug</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            {{ url('/') }}/products/
                                        </span>
                                        <input type="text" id="newSlugInput" class="form-control" value="{{ $product->slug }}" placeholder="new-product-url">
                                    </div>
                                    <div class="form-text">
                                        Use lowercase letters, numbers, and hyphens only
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="createRedirect" checked>
                                    <label class="form-check-label" for="createRedirect">
                                        <i class="bi bi-arrow-right-circle"></i> Create 301 redirect from old URL to new URL
                                    </label>
                                    <div class="form-text">
                                        Recommended: This will automatically redirect visitors from the old URL to the new one
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-filter" id="saveUrlBtn">
                                        <i class="bi bi-check-circle"></i> Save URL Change
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" id="cancelUrlBtn">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                </div>
                            </div>

                            <!-- Existing Redirects -->
                            @if($productRedirects && $productRedirects->count() > 0)
                            <div class="mt-3">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-arrow-repeat"></i> Existing Redirects
                                </h6>
                                <div class="list-group">
                                    @foreach($productRedirects as $redirect)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <small class="text-muted">From:</small>
                                                <code>{{ $redirect->old_url }}</code>
                                                <i class="bi bi-arrow-right mx-2"></i>
                                                <small class="text-muted">To:</small>
                                                <code>{{ $redirect->new_url }}</code>
                                            </div>
                                            <div>
                                                {!! $redirect->getTypeBadge() !!}
                                                {!! $redirect->getStatusBadge() !!}
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-bar-chart"></i> {{ $redirect->hit_count }} hits
                                            | Created: {{ $redirect->created_at->format('M d, Y') }}
                                        </small>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ $product->meta_title }}" maxlength="60">
                        <div class="form-text">Recommended: 50-60 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" maxlength="160">{{ $product->meta_description }}</textarea>
                        <div class="form-text">Recommended: 150-160 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ $product->meta_keywords }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control" value="{{ $product->canonical_url }}">
                        <div class="form-text">Leave empty to use default product URL</div>
                    </div>
                </div>

            </div>
            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Product Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-gear"></i> Product Settings</h5>

                    <div class="mb-3">
                        <label class="form-label required-field">Status</label>
                        <select name="status_key_code" class="form-select" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ $product->status_key_code === $status->key_code ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <div class="input-group">
                            <select name="category_id" id="categorySelect" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->indent }}{{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="add-btn-icon" onclick="openAddCategoryModal()" title="Add New Category">
                                <i class="bi bi-plus"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <div class="input-group">
                            <select name="vendor_id" id="vendorSelect" class="form-select">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ $product->vendor_id == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="add-btn-icon" onclick="openAddVendorModal()" title="Add New Vendor">
                                <i class="bi bi-plus"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <div class="position-relative">
                            <div class="tags-container" id="tagsContainer">
                                <!-- Tags will be rendered here -->
                            </div>
                            <div class="tag-suggestions" id="tagSuggestions"></div>
                        </div>
                        <div class="form-text">Press Enter, comma, or Tab to add tags. Start typing to see suggestions.</div>
                        <input type="hidden" name="tags" id="tagsHiddenInput" value="">
                    </div>

                    <hr>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" id="isFeatured" class="form-check-input" {{ $product->is_featured ? 'checked' : '' }}>
                        <label class="form-check-label" for="isFeatured">
                            <i class="bi bi-star"></i> Featured Product
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="show_on_home" id="showOnHome" class="form-check-input" {{ $product->show_on_home ? 'checked' : '' }}>
                        <label class="form-check-label" for="showOnHome">
                            <i class="bi bi-house"></i> Show on Homepage
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_available" id="isAvailable" class="form-check-input" {{ $product->is_available ? 'checked' : '' }}>
                        <label class="form-check-label" for="isAvailable">
                            <i class="bi bi-check-circle"></i> Available for Purchase
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="track_inventory" id="trackInventory" class="form-check-input" {{ $product->track_inventory ? 'checked' : '' }}>
                        <label class="form-check-label" for="trackInventory">
                            <i class="bi bi-box"></i> Track Inventory
                        </label>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-currency-dollar"></i> Pricing</h5>

                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select name="curency" class="form-select">
                            @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" {{ $product->curency === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required-field">Regular Price</label>
                        <input type="number" name="price" id="regularPrice" class="form-control" value="{{ $product->price }}" placeholder="0.00" step="0.01" min="0" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sale Price</label>
                        <input type="number" name="sale_price" id="salePrice" class="form-control" value="{{ $product->sale_price }}" placeholder="0.00" step="0.01" min="0">
                        <div class="form-text">Leave empty if not on sale</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cost Price</label>
                        <input type="number" name="cost_price" class="form-control" value="{{ $product->cost_price }}" placeholder="0.00" step="0.01" min="0">
                        <div class="form-text">Your cost (for profit calculation)</div>
                    </div>

                    @if($product->isOnSale())
                    <div class="alert alert-success">
                        <i class="bi bi-tag-fill"></i> <strong>On Sale!</strong><br>
                        <small>Discount: {{ $product->getDiscountPercentage() }}% off</small><br>
                        <small>Savings: {{ $product->curency }} {{ number_format($product->getDiscountAmount(), 2) }}</small>
                    </div>
                    @endif

                    <hr class="my-3">

                    <!-- TAX SETTINGS - ADD THIS SECTION -->
                    <h6 class="text-muted mb-3"><i class="bi bi-receipt"></i> Tax Settings</h6>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_taxable" id="isTaxable" class="form-check-input" {{ $product->is_taxable ? 'checked' : '' }}>
                        <label class="form-check-label" for="isTaxable">
                            <i class="bi bi-calculator"></i> This product is taxable
                        </label>
                    </div>

                    <div id="taxSettingsSection" style="{{ $product->is_taxable ? '' : 'display: none;' }}">
                        <div class="mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="tax_type" id="taxType" class="form-select">
                                <option value="exclusive" {{ $product->tax_type === 'exclusive' ? 'selected' : '' }}>Tax Exclusive (Price + Tax)</option>
                                <option value="inclusive" {{ $product->tax_type === 'inclusive' ? 'selected' : '' }}>Tax Inclusive (Price includes Tax)</option>
                            </select>
                            <div class="form-text" id="taxTypeHelp">
                                <small><strong>Exclusive:</strong> Tax will be added to the price</small><br>
                                <small><strong>Inclusive:</strong> Tax is already included in the price</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tax Percentage (%)</label>
                            <div class="input-group">
                                <input type="number" name="tax_percentage" id="taxPercentage" class="form-control" value="{{ $product->tax_percentage }}" placeholder="0.00" step="0.01" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Enter tax rate (e.g., 15 for 15% tax)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tax Class</label>
                            <input type="text" name="tax_class" class="form-control" value="{{ $product->tax_class }}" placeholder="e.g., Standard, Reduced, Zero">
                            <div class="form-text">Optional: Tax classification for reporting</div>
                        </div>

                        <!-- Current Tax Info -->
                        @if($product->is_taxable && $product->tax_percentage > 0)
                        <div class="alert alert-info">
                            <strong>Current Tax Info:</strong>
                            <div class="mt-2 small">
                                @php
                                    $taxInfo = $product->getTaxInfo();
                                @endphp
                                <p class="mb-1"><strong>Type:</strong> {{ $product->getTaxTypeLabel() }}</p>
                                <p class="mb-1"><strong>Rate:</strong> {{ $product->tax_percentage }}%</p>
                                <p class="mb-1"><strong>Price (excl. tax):</strong> {{ get_currency_symbol($product->curency) }} {{ number_format($taxInfo['price_excluding_tax'], 2) }}</p>
                                <p class="mb-1"><strong>Tax Amount:</strong> {{ get_currency_symbol($product->curency) }} {{ number_format($taxInfo['tax_amount'], 2) }}</p>
                                <p class="mb-0"><strong>Price (incl. tax):</strong> {{ get_currency_symbol($product->curency) }} {{ number_format($taxInfo['price_including_tax'], 2) }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Tax Calculation Preview -->
                        <div class="alert alert-info" id="taxPreview" style="display: none;">
                            <strong>Tax Preview:</strong>
                            <div id="taxPreviewContent" class="mt-2 small"></div>
                        </div>
                    </div>
                </div>

                <!-- Stock Management -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-box-seam"></i> Stock Management</h5>

                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustStock(-10)">
                                <i class="bi bi-dash-lg"></i> 10
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustStock(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="stock_quantity" id="stockQuantity" class="form-control text-center" value="{{ $product->stock_quantity }}" min="0">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustStock(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustStock(10)">
                                <i class="bi bi-plus-lg"></i> 10
                            </button>
                        </div>
                        <div class="form-text">Current stock level</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control" value="{{ $product->low_stock_threshold }}" min="0">
                        <div class="form-text">Alert when stock reaches this level</div>
                    </div>

                    @if($product->isLowStock())
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Low Stock Alert!</strong><br>
                        <small>Current stock ({{ $product->stock_quantity }}) is below threshold ({{ $product->low_stock_threshold }})</small>
                    </div>
                    @elseif(!$product->isInStock())
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle-fill"></i> <strong>Out of Stock!</strong><br>
                        <small>This product is currently unavailable</small>
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> <strong>In Stock</strong><br>
                        <small>{{ $product->stock_quantity }} units available</small>
                    </div>
                    @endif
                </div>
                <!-- Shipping Dimensions & Weight -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-box"></i> Shipping Information</h5>

                    <div class="mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <div class="input-group">
                            <input type="number" name="weight" class="form-control"
                                value="{{ old('weight', $product->weight) }}"
                                placeholder="0.00" step="0.01" min="0">
                            <span class="input-group-text">kg</span>
                        </div>
                        <div class="form-text">Product weight for shipping calculation</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dimensions (cm)</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="number" name="length" class="form-control"
                                    value="{{ old('length', $product->length) }}"
                                    placeholder="Length" step="0.01" min="0">
                                <small class="text-muted">L</small>
                            </div>
                            <div class="col-4">
                                <input type="number" name="width" class="form-control"
                                    value="{{ old('width', $product->width) }}"
                                    placeholder="Width" step="0.01" min="0">
                                <small class="text-muted">W</small>
                            </div>
                            <div class="col-4">
                                <input type="number" name="height" class="form-control"
                                    value="{{ old('height', $product->height) }}"
                                    placeholder="Height" step="0.01" min="0">
                                <small class="text-muted">H</small>
                            </div>
                        </div>
                        <div class="form-text">Product dimensions: Length × Width × Height</div>
                    </div>

                    @if($product->weight || ($product->length && $product->width && $product->height))
                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> Current Shipping Info:</strong>
                        <div class="mt-2 small">
                            @if($product->weight)
                            <p class="mb-1"><strong>Weight:</strong> {{ $product->weight }} kg</p>
                            @endif
                            @if($product->length && $product->width && $product->height)
                            <p class="mb-0"><strong>Dimensions:</strong> {{ $product->length }} × {{ $product->width }} × {{ $product->height }} cm</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-lightning-fill"></i> Quick Actions</h5>

                    <div class="d-grid gap-2">
                        @if(auth('admin')->user()->hasPermission('products.update'))
                        <button type="button" class="btn btn-outline-warning" onclick="toggleFeatured()">
                            <i class="bi bi-star{{ $product->is_featured ? '-fill' : '' }}"></i>
                            {{ $product->is_featured ? 'Remove from Featured' : 'Mark as Featured' }}
                        </button>
{{--
                        <button type="button" class="btn btn-outline-info" onclick="togglePublish()">
                            <i class="bi bi-{{ $product->published_at ? 'eye-slash' : 'eye' }}"></i>
                            {{ $product->published_at ? 'Unpublish' : 'Publish' }}
                        </button> --}}

                        <button type="button" class="btn btn-outline-secondary" onclick="duplicateProduct()">
                            <i class="bi bi-files"></i> Duplicate Product
                        </button>
                        @endif

                        @if(auth('admin')->user()->hasPermission('products.delete'))
                        <button type="button" class="btn btn-outline-danger" onclick="deleteProduct()">
                            <i class="bi bi-trash"></i> Delete Product
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-save btn-lg">
                            <i class="bi bi-check-circle"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline-info">
                            <i class="bi bi-eye"></i> View Product
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required-field">Category Name</label>
                        <input type="text" name="category_title" id="categoryTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="category_parent_id" class="form-select">
                            <option value="">None (Root Category)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->indent }}{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-check"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addVendorForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required-field">Vendor Name</label>
                        <input type="text" name="vendor_name" id="vendorName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="vendor_email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="vendor_phone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-check"></i> Add Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Variant Modal -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title" id="variantModalTitle">
                    <i class="bi bi-plus-circle"></i> Add New Variant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="variantForm">
                @csrf
                <input type="hidden" name="variant_id" id="variantId">
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="modal-body">
                    <div class="row">
                        <!-- Variant Name & Value -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Variant Name</label>
                            <input type="text" name="variant_name" id="variantName" class="form-control"
                                   placeholder="e.g., Size, Color, Weight" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Variant Value</label>
                            <input type="text" name="variant_value" id="variantValue" class="form-control"
                                   placeholder="e.g., Large, Red, 500g" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- SKU -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">SKU</label>
                            <div class="input-group">
                                <input type="text" name="sku" id="variantSku" class="form-control"
                                       placeholder="Variant SKU" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateVariantSKU()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Price -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Price</label>
                            <input type="number" name="price" id="variantPrice" class="form-control"
                                   placeholder="0.00" step="0.01" min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Sale Price -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" name="sale_price" id="variantSalePrice" class="form-control"
                                   placeholder="0.00" step="0.01" min="0">
                            <small class="text-muted">Leave empty if not on sale</small>
                        </div>

                        <!-- Weight -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" name="weight" id="variantWeight" class="form-control"
                                   placeholder="0.00" step="0.01" min="0">
                        </div>

                        <!-- Dimensions -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Dimensions (cm)</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="number" name="length" id="variantLength" class="form-control"
                                           placeholder="Length" step="0.01" min="0">
                                    <small class="text-muted d-block text-center">L</small>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="width" id="variantWidth" class="form-control"
                                           placeholder="Width" step="0.01" min="0">
                                    <small class="text-muted d-block text-center">W</small>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="height" id="variantHeight" class="form-control"
                                           placeholder="Height" step="0.01" min="0">
                                    <small class="text-muted d-block text-center">H</small>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Settings -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Initial Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="variantStockQuantity" class="form-control"
                                   placeholder="0" min="0" value="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" id="variantLowStockThreshold" class="form-control"
                                   placeholder="10" min="0" value="10">
                        </div>

                        <!-- Variant Image -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Variant Image</label>
                            <input type="file" name="variant_image" id="variantImage" class="form-control" accept="image/*">
                            <div id="variantImagePreview" class="mt-2"></div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status_key_code" id="variantStatus" class="form-select">
                                <option value="VARIANT_ACTIVE" selected>Active</option>
                                <option value="VARIANT_INACTIVE">Inactive</option>
                                <option value="VARIANT_OUT_OF_STOCK">Out of Stock</option>
                            </select>
                        </div>

                        <!-- Is Default -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_default" id="variantIsDefault" class="form-check-input">
                                <label class="form-check-label" for="variantIsDefault">
                                    <i class="bi bi-star"></i> Set as default variant
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> <span id="variantSubmitText">Save Variant</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Translation button --}}
@include('admin.components.translation-modal', [
    'module' => 'product',
    'itemId' => $product->id,
    'itemName' => $product->name
])
{{-- Translation button --}}
@endsection


@push('scripts')
<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

<script>
const PRODUCT_ID = '{{ $product->id }}';

$(document).ready(function() {
    // ✅ Initialize Category Select2
    $('#categorySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search category...',
        allowClear: true,
        width: '100%'
    });

    // ✅ Initialize Vendor Select2
    $('#vendorSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search vendor...',
        allowClear: true,
        width: '100%'
    });
    // Initialize CKEditor
    let descriptionEditor;
    ClassicEditor
        .create(document.querySelector('#productDescription'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo']
        })
        .then(editor => {
            descriptionEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    // Auto-generate slug from product name
    // $('#productName').on('keyup', function() {
    //     let name = $(this).val();
    //     let slug = name.toLowerCase()
    //         .replace(/[^\w ]+/g, '')
    //         .replace(/ +/g, '-');
    //     $('#productSlug').val(slug);
    // });

    // Main image preview
    $('#mainImage').on('change', function(e) {
        let file = e.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#mainImagePreview').html(`
                    <div class="image-preview-container">
                        <img src="${e.target.result}" class="image-preview" alt="Main Image">
                        <button type="button" class="remove-image" onclick="removeMainImage()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    // Gallery images - dropzone events
    const dropzone = $('#dropzoneArea');

    dropzone.on('click', function() {
        $('#galleryImages').click();
    });

    dropzone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropzone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    dropzone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');

        const files = e.originalEvent.dataTransfer.files;
        uploadImagesToProduct(files);
    });

    // Gallery images change
    $('#galleryImages').on('change', function(e) {
        const files = e.target.files;
        uploadImagesToProduct(files);
    });

    //Media upload  Functionality Starts Here.............
    // Constants
    const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes
    const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB for images
    const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // 50MB for videos

    // Validate files before upload
    function validateFiles(files) {
        const validFiles = [];
        const errors = [];
        const oversizedFiles = [];
        const invalidTypes = [];

        Array.from(files).forEach(file => {
            const fileSize = file.size;
            const fileName = file.name;
            const fileType = file.type;

            // Check file type
            const isImage = fileType.startsWith('image/');
            const isVideo = fileType.startsWith('video/');

            if (!isImage && !isVideo) {
                invalidTypes.push({
                    name: fileName,
                    reason: 'Invalid file type. Only images and videos are allowed.'
                });
                return;
            }

            // Check file size
            const maxSize = isVideo ? MAX_VIDEO_SIZE : MAX_IMAGE_SIZE;
            const maxSizeLabel = isVideo ? '50MB' : '2MB';

            if (fileSize > maxSize) {
                oversizedFiles.push({
                    name: fileName,
                    size: formatBytes(fileSize),
                    maxSize: maxSizeLabel,
                    type: isVideo ? 'Video' : 'Image'
                });
                return;
            }

            // File is valid
            validFiles.push(file);
        });

        // Compile errors
        if (oversizedFiles.length > 0) {
            errors.push({
                type: 'size',
                files: oversizedFiles
            });
        }

        if (invalidTypes.length > 0) {
            errors.push({
                type: 'type',
                files: invalidTypes
            });
        }

        return {
            valid: errors.length === 0,
            validFiles: validFiles,
            errors: errors
        };
    }

    // Show validation errors
    function showFileValidationError(errors) {
        let errorHtml = '<div class="text-start">';

        errors.forEach(error => {
            if (error.type === 'size') {
                errorHtml += `
                    <div class="alert alert-danger mb-3">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> Files Too Large (${error.files.length})</strong>
                        <ul class="mb-0 mt-2">
                            ${error.files.map(f => `
                                <li>
                                    <strong>${f.name}</strong><br>
                                    <small class="text-muted">
                                        ${f.type}: ${f.size} (Max: ${f.maxSize})
                                    </small>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
            }

            if (error.type === 'type') {
                errorHtml += `
                    <div class="alert alert-warning mb-3">
                        <strong><i class="bi bi-file-earmark-x"></i> Invalid File Types (${error.files.length})</strong>
                        <ul class="mb-0 mt-2">
                            ${error.files.map(f => `
                                <li>
                                    <strong>${f.name}</strong><br>
                                    <small class="text-muted">${f.reason}</small>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
            }
        });

        errorHtml += `
            <div class="alert alert-info mb-0">
                <strong><i class="bi bi-info-circle"></i> File Requirements:</strong><br>
                <small>
                    • <strong>Images:</strong> PNG, JPG, GIF, WEBP (Max: 2MB each)<br>
                    • <strong>Videos:</strong> MP4, MOV, AVI, WMV, FLV, WEBM (Max: 50MB each)
                </small>
            </div>
        </div>`;

        Swal.fire({
            icon: 'error',
            title: 'Upload Validation Failed',
            html: errorHtml,
            confirmButtonColor: '#5B914C',
            width: '600px',
            customClass: {
                popup: 'validation-error-modal'
            }
        });
    }

    // Upload media to product (MAIN FUNCTION)
    function uploadImagesToProduct(files) {
        if (files.length === 0) return;

        // Validate files before upload
        const validation = validateFiles(files);

        if (!validation.valid) {
            showFileValidationError(validation.errors);
            return;
        }

        // Upload files with progress tracking
        uploadFilesWithProgress(validation.validFiles);
    }

    // Upload files with individual progress tracking
    function uploadFilesWithProgress(files) {
        if (files.length === 0) return;

        const uploadId = 'upload-' + Date.now();
        const totalFiles = files.length;
        let completedFiles = 0;
        let failedFiles = 0;
        const uploadResults = [];

        // Create upload widget
        createUploadWidget(uploadId, totalFiles, files);

        // Upload each file individually
        files.forEach((file, index) => {
            const fileId = `file-${index}`;

            uploadSingleFileToProduct(file, fileId, uploadId, (result) => {
                if (result.success) {
                    completedFiles++;
                    uploadResults.push(result.media);

                    // Update file status
                    updateFileStatus(uploadId, fileId, 'success', result.media);
                } else {
                    failedFiles++;
                    updateFileStatus(uploadId, fileId, 'error', null, result.error);
                }

                // Update overall progress
                const overallProgress = Math.round(((completedFiles + failedFiles) / totalFiles) * 100);
                updateOverallProgress(uploadId, completedFiles, failedFiles, totalFiles, overallProgress);

                // Check if all files processed
                if (completedFiles + failedFiles === totalFiles) {
                    finalizeUpload(uploadId, uploadResults, completedFiles, failedFiles, totalFiles);
                }
            });
        });
    }

    // Upload single file with progress
    function uploadSingleFileToProduct(file, fileId, uploadId, callback) {
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('images[]', file);

        $.ajax({
            url: `/admin/products/${PRODUCT_ID}/images/upload`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = Math.round((e.loaded / e.total) * 100);
                        updateFileProgress(uploadId, fileId, percentComplete);
                    }
                }, false);

                return xhr;
            },
            success: function(response) {
                if (response.success && response.media.length > 0) {
                    callback({
                        success: true,
                        media: response.media[0]
                    });
                } else {
                    callback({
                        success: false,
                        error: 'Upload failed'
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Upload failed';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat()[0] || errorMsg;
                } else if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                callback({
                    success: false,
                    error: errorMsg
                });
            }
        });
    }

    // Add media to gallery preview
    function addMediaToGallery(media) {
        let mediaHtml = '';

        if (media.is_video) {
            mediaHtml = `
                <div class="image-preview-container" data-id="${media.id}">
                    <div class="video-preview-wrapper" style="position: relative;">
                        <video class="image-preview" controls preload="metadata">
                            <source src="${media.url}" type="${media.mime_type}">
                        </video>
                        <span class="badge bg-primary" style="position: absolute; top: 5px; left: 5px;">
                            <i class="bi bi-play-circle"></i> Video
                        </span>
                        ${media.duration ? `<span class="badge bg-dark" style="position: absolute; top: 5px; right: 40px;">${media.duration}</span>` : ''}
                        <span class="badge bg-secondary" style="position: absolute; bottom: 35px; left: 5px;">
                            ${media.file_size}
                        </span>
                    </div>
                    <button type="button" class="remove-image" onclick="deleteProductImage('${PRODUCT_ID}', '${media.id}', this)">
                        <i class="bi bi-x"></i>
                    </button>
                    <small class="d-block text-center text-muted mt-1" style="font-size: 0.75rem;">${media.name}</small>
                </div>
            `;
        } else {
            mediaHtml = `
                <div class="image-preview-container" data-id="${media.id}">
                    <img src="${media.url}" class="image-preview" alt="${media.name}">
                    <span class="badge bg-secondary" style="position: absolute; bottom: 35px; left: 5px;">
                        ${media.file_size}
                    </span>
                    <button type="button" class="remove-image" onclick="deleteProductImage('${PRODUCT_ID}', '${media.id}', this)">
                        <i class="bi bi-x"></i>
                    </button>
                    <button type="button" class="set-primary-btn" onclick="setPrimaryImage('${PRODUCT_ID}', '${media.id}')">
                        <i class="bi bi-star"></i> Set Primary
                    </button>
                    <small class="d-block text-center text-muted mt-1" style="font-size: 0.75rem;">${media.name}</small>
                </div>
            `;
        }

        $('#galleryPreview').append(mediaHtml);
    }
    //-------------------
    // Create floating upload widget (Google Drive style)
    function createUploadWidget(uploadId, totalFiles, files) {
        const widget = `
            <div id="${uploadId}" class="upload-widget">
                <div class="upload-widget-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="bi bi-cloud-upload"></i> Uploading ${totalFiles} file(s)</strong>
                        </div>
                        <div class="upload-actions">
                            <button class="btn-widget-minimize" onclick="toggleUploadWidget('${uploadId}')">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <button class="btn-widget-close" onclick="cancelUpload('${uploadId}')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Overall Progress -->
                    <div class="overall-progress mt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small id="${uploadId}-status">Preparing...</small>
                            <small id="${uploadId}-percentage">0%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success"
                                id="${uploadId}-bar"
                                role="progressbar"
                                style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div class="upload-widget-body" id="${uploadId}-body">
                    <div class="upload-files-list" id="${uploadId}-files">
                        ${files.map((file, index) => `
                            <div class="upload-file-item" id="${uploadId}-file-${index}">
                                <div class="file-info">
                                    <i class="bi bi-${file.type.startsWith('video/') ? 'play-circle' : 'image'} text-muted"></i>
                                    <span class="file-name">${file.name}</span>
                                    <span class="file-size text-muted">${formatBytes(file.size)}</span>
                                </div>
                                <div class="file-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar"
                                            id="${uploadId}-file-${index}-bar"
                                            role="progressbar"
                                            style="width: 0%"></div>
                                    </div>
                                    <span class="file-status" id="${uploadId}-file-${index}-status">
                                        <i class="bi bi-hourglass-split text-muted"></i> Waiting...
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        // Append to body
        $('body').append(widget);

        // Animate in
        setTimeout(() => {
            $(`#${uploadId}`).addClass('show');
        }, 100);
    }

    // Update individual file progress
    function updateFileProgress(uploadId, fileId, percentage) {
        $(`#${uploadId}-${fileId}-bar`).css('width', percentage + '%');
        $(`#${uploadId}-${fileId}-status`).html(`
            <i class="bi bi-upload text-primary"></i> ${percentage}%
        `);
    }

    // Update file status (success/error)
    function updateFileStatus(uploadId, fileId, status, media, error) {
        const statusElement = $(`#${uploadId}-${fileId}-status`);
        const progressBar = $(`#${uploadId}-${fileId}-bar`);
        const fileItem = $(`#${uploadId}-${fileId}`);

        if (status === 'success') {
            progressBar.removeClass('bg-primary').addClass('bg-success');
            statusElement.html('<i class="bi bi-check-circle-fill text-success"></i> Complete');
            fileItem.addClass('success');

            // Add to gallery preview
            if (media) {
                addMediaToGallery(media);
            }
        } else {
            progressBar.removeClass('bg-primary').addClass('bg-danger');
            statusElement.html(`<i class="bi bi-x-circle-fill text-danger"></i> ${error || 'Failed'}`);
            fileItem.addClass('error');
        }
    }

    // Update overall progress
    function updateOverallProgress(uploadId, completed, failed, total, percentage) {
        $(`#${uploadId}-bar`).css('width', percentage + '%');
        $(`#${uploadId}-percentage`).text(percentage + '%');

        const statusText = failed > 0
            ? `${completed} completed, ${failed} failed of ${total}`
            : `${completed} of ${total} uploaded`;

        $(`#${uploadId}-status`).text(statusText);
    }

    // Finalize upload
    function finalizeUpload(uploadId, uploadResults, completed, failed, total) {
        // Update widget header
        $(`#${uploadId} .upload-widget-header strong`).html(
            `<i class="bi bi-${failed === 0 ? 'check-circle-fill text-success' : 'exclamation-triangle-fill text-warning'}"></i>
            ${failed === 0 ? 'Upload Complete' : 'Upload Finished with Errors'}`
        );

        // Change minimize button to close
        $(`#${uploadId} .btn-widget-minimize`).remove();

        // Auto-close after delay if all successful
        if (failed === 0) {
            setTimeout(() => {
                closeUploadWidget(uploadId);
            }, 3000);
        }

        // Show toast notification
        showUploadToast(completed, failed, total);
    }

    // Show toast notification
    function showUploadToast(completed, failed, total) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        if (failed === 0) {
            Toast.fire({
                icon: 'success',
                title: `${completed} file(s) uploaded successfully!`
            });
        } else {
            Toast.fire({
                icon: 'warning',
                title: `${completed} succeeded, ${failed} failed`
            });
        }
    }

    // Toggle widget minimize/maximize
    function toggleUploadWidget(uploadId) {
        $(`#${uploadId}`).toggleClass('minimized');
        const icon = $(`#${uploadId} .btn-widget-minimize i`);

        if ($(`#${uploadId}`).hasClass('minimized')) {
            icon.removeClass('bi-dash-lg').addClass('bi-chevron-up');
        } else {
            icon.removeClass('bi-chevron-up').addClass('bi-dash-lg');
        }
    }

    // Cancel/Close upload widget
    function cancelUpload(uploadId) {
        Swal.fire({
            title: 'Close upload progress?',
            text: 'Upload progress will be hidden',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, close it',
            cancelButtonText: 'Keep open'
        }).then((result) => {
            if (result.isConfirmed) {
                closeUploadWidget(uploadId);
            }
        });
    }

    // Close upload widget
    function closeUploadWidget(uploadId) {
        $(`#${uploadId}`).removeClass('show');
        setTimeout(() => {
            $(`#${uploadId}`).remove();
        }, 300);
    }

    //Media upload Functionality Ends Here.............

// ==================== TAGS FUNCTIONALITY ====================

// Declare variables first
let selectedTags = [];
let tagSearchTimeout;
let currentTagInput = null;

// Initialize tags container
function initializeTags() {
    renderTags();
    attachTagInputEvents();
}

// Render tags
function renderTags() {
    let html = '';

    selectedTags.forEach((tag, index) => {
        html += `
            <span class="tag-item" data-index="${index}">
                <span class="tag-name">${escapeHtml(tag.name)}</span>
                <button type="button" class="remove-tag" data-index="${index}" title="Remove tag">×</button>
            </span>
        `;
    });

    html += '<input type="text" class="tag-input" id="tagInput" placeholder="Add tags..." autocomplete="off">';

    $('#tagsContainer').html(html);

    // Update hidden input
    const tagIds = selectedTags.map(t => t.id);
    $('#tagsHiddenInput').val(JSON.stringify(tagIds));

    // Reattach events after rendering
    attachTagInputEvents();

    // Focus the new input
    currentTagInput = $('#tagInput');
}

// Attach events to tag input
function attachTagInputEvents() {
    currentTagInput = $('#tagInput');

    // Remove tag button click
    $('.remove-tag').off('click').on('click', function(e) {
        e.stopPropagation();
        const index = parseInt($(this).data('index'));
        removeTagByIndex(index);
    });

    // Tag input events
    currentTagInput.off().on({
        'keydown': function(e) {
            const value = $(this).val().trim();

            // Enter, Comma, or Tab - Add tag
            if (e.key === 'Enter' || e.key === ',' || e.key === 'Tab') {
                e.preventDefault();
                if (value) {
                    addNewTag(value.replace(/,/g, ''));
                    $(this).val('');
                    $('#tagSuggestions').hide();
                }
                return false;
            }

            // Backspace on empty input - Remove last tag
            if (e.key === 'Backspace' && value === '' && selectedTags.length > 0) {
                e.preventDefault();
                removeTagByIndex(selectedTags.length - 1);
            }

            // Escape - Close suggestions
            if (e.key === 'Escape') {
                $('#tagSuggestions').hide();
            }
        },
        'keyup': function(e) {
            // Don't search on special keys
            if (['Enter', 'Tab', 'Escape', 'ArrowUp', 'ArrowDown', ','].includes(e.key)) {
                return;
            }

            const value = $(this).val().trim();

            // Search for tags
            if (value.length >= 2) {
                clearTimeout(tagSearchTimeout);
                tagSearchTimeout = setTimeout(() => searchTags(value), 300);
            } else {
                $('#tagSuggestions').hide();
            }
        },
        'blur': function() {
            // Delay to allow click on suggestions
            setTimeout(() => {
                $('#tagSuggestions').hide();
            }, 200);
        },
        'focus': function() {
            const value = $(this).val().trim();
            if (value.length >= 2) {
                searchTags(value);
            }
        }
    });
}

// Search tags from server
function searchTags(query) {
    $.ajax({
        url: '{{ route("admin.products.tags.search") }}',
        data: { q: query },
        method: 'GET',
        success: function(response) {
            if (response.success && response.tags.length > 0) {
                displayTagSuggestions(response.tags, query);
            } else {
                // Show "Create new tag" option
                displayCreateNewTagOption(query);
            }
        },
        error: function() {
            $('#tagSuggestions').hide();
        }
    });
}

// Display tag suggestions
function displayTagSuggestions(tags, query) {
    let html = '';
    let hasResults = false;

    tags.forEach(tag => {
        // Don't show already selected tags
        if (!selectedTags.find(t => t.id === tag.id)) {
            html += `
                <div class="tag-suggestion-item" data-id="${tag.id}" data-name="${escapeHtml(tag.name)}">
                    <i class="bi bi-tag"></i> ${escapeHtml(tag.name)}
                </div>
            `;
            hasResults = true;
        }
    });

    // Add "Create new" option if query doesn't match exactly
    const exactMatch = tags.find(t => t.name.toLowerCase() === query.toLowerCase());
    if (!exactMatch) {
        html += `
            <div class="tag-suggestion-item tag-create-new" data-name="${escapeHtml(query)}">
                <i class="bi bi-plus-circle"></i> Create "<strong>${escapeHtml(query)}</strong>"
            </div>
        `;
        hasResults = true;
    }

    if (hasResults) {
        $('#tagSuggestions').html(html).show();
        attachSuggestionEvents();
    } else {
        $('#tagSuggestions').hide();
    }
}

// Display create new tag option
function displayCreateNewTagOption(query) {
    const html = `
        <div class="tag-suggestion-item tag-create-new" data-name="${escapeHtml(query)}">
            <i class="bi bi-plus-circle"></i> Create "<strong>${escapeHtml(query)}</strong>"
        </div>
    `;
    $('#tagSuggestions').html(html).show();
    attachSuggestionEvents();
}

// Attach events to suggestions
function attachSuggestionEvents() {
    $('.tag-suggestion-item').off('click').on('click', function() {
        const tagId = $(this).data('id');
        const tagName = $(this).data('name');

        if (tagId) {
            // Existing tag
            addExistingTag(tagId, tagName);
        } else {
            // Create new tag
            addNewTag(tagName);
        }

        currentTagInput.val('');
        $('#tagSuggestions').hide();
        currentTagInput.focus();
    });
}

// Add existing tag
function addExistingTag(id, name) {
    // Check if tag already added
    if (selectedTags.find(t => t.id === id)) {
        showNotification('Tag already added', 'info');
        return;
    }

    selectedTags.push({ id: id, name: name });
    renderTags();
    showNotification('Tag added', 'success');
}

// Add new tag (create on server)
function addNewTag(name) {
    name = name.trim();

    if (!name) return;

    // Check if tag with same name already exists
    if (selectedTags.find(t => t.name.toLowerCase() === name.toLowerCase())) {
        showNotification('Tag already added', 'info');
        return;
    }

    // Show loading
    const loadingToast = Swal.fire({
        title: 'Creating tag...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '{{ route("admin.products.tags.create") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            name: name
        },
        success: function(response) {
            loadingToast.close();

            if (response.success) {
                selectedTags.push({
                    id: response.tag.id,
                    name: response.tag.name
                });
                renderTags();
                showNotification('Tag created and added', 'success');
            } else {
                showNotification(response.message || 'Failed to create tag', 'error');
            }
        },
        error: function(xhr) {
            loadingToast.close();
            showNotification(xhr.responseJSON?.message || 'Failed to create tag', 'error');
        }
    });
}

// Remove tag by index
function removeTagByIndex(index) {
    if (index >= 0 && index < selectedTags.length) {
        const removedTag = selectedTags[index];
        selectedTags.splice(index, 1);
        renderTags();
        showNotification(`"${removedTag.name}" removed`, 'info');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

// Escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Click on container focuses input
$(document).on('click', '#tagsContainer', function(e) {
    if (e.target.id !== 'tagInput' && !$(e.target).hasClass('remove-tag')) {
        $('#tagInput').focus();
    }
});

// Close suggestions when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#tagsContainer, #tagSuggestions').length) {
        $('#tagSuggestions').hide();
    }
});

// Load existing tags and initialize
$(document).ready(function() {
    // Load existing tags from product
    selectedTags = @json($product->tags->map(function($tag) {
        return ['id' => $tag->id, 'name' => $tag->name];
    })->values());

    // Initialize tags system
    initializeTags();
});

// ==================== END TAGS FUNCTIONALITY ====================

    // Form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        // Get CKEditor content for description
        if (descriptionEditor) {
            $('textarea[name="description"]').val(descriptionEditor.getData());
        }
        $('#taxPercentage').prop('disabled', false);
        // Update all feature editors before submit
        if (typeof beforeProductFormSubmit === 'function') {
            beforeProductFormSubmit();
        }

        // Validate features before submission
        if (typeof validateProductFeatures === 'function') {
            if (!validateProductFeatures()) {
                return false; // Stop submission if validation fails
            }
        }

        let formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: '/admin/products/{{ $product->id }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Updating Product...',
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
                        window.location.href = '/admin/products/{{ $product->id }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.close();

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, messages) {
                        const input = $(`[name="${key}"]`);
                        const feedback = input.closest('.mb-3').find('.invalid-feedback');

                        input.addClass('is-invalid');

                        if (feedback.length) {
                            feedback.text(messages[0]).show();
                        } else {
                            // Create feedback element if it doesn't exist
                            input.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                        }
                    });

                    // Scroll to first error
                    const firstError = $('.is-invalid').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: 'Please check the form:<br>' +
                            Object.values(errors).flat().map(err => `• ${err}`).join('<br>')
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to create category'
                    });
                }
            }
        });
    });

    // Add Category Form
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/admin/products/categories/quick-add',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                title: $('#categoryTitle').val(),
                parent_id: $('[name="category_parent_id"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#addCategoryModal').modal('hide');

                    // Add to select dropdown
                    const indent = response.category.full_path ? response.category.full_path.split(' > ').length > 1 ? '— '.repeat(response.category.full_path.split(' > ').length - 1) : '' : '';
                    $('#categorySelect').append(`<option value="${response.category.id}" selected>${indent}${response.category.title}</option>`);

                    // Reset form
                    $('#addCategoryForm')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to add category', 'error');
            }
        });
    });

    // Add Vendor Form
    $('#addVendorForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/admin/products/vendors/quick-add',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: $('#vendorName').val(),
                email: $('[name="vendor_email"]').val(),
                phone: $('[name="vendor_phone"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#addVendorModal').modal('hide');

                    // Add to select dropdown
                    $('#vendorSelect').append(`<option value="${response.vendor.id}" selected>${response.vendor.name}</option>`);

                    // Reset form
                    $('#addVendorForm')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to add vendor', 'error');
            }
        });
    });

});

// ==================== HELPER FUNCTIONS ====================

function adjustStock(amount) {
    const input = $('#stockQuantity');
    const currentValue = parseInt(input.val()) || 0;
    const newValue = Math.max(0, currentValue + amount);
    input.val(newValue);
}

function removeMainImage() {
    $('#mainImage').val('');
    $('#mainImagePreview').empty();
}

function deleteProductImage(productId, imageId, button) {
    const container = $(button).closest('.image-preview-container');
    const isVideo = container.find('video').length > 0;
    const mediaType = isVideo ? 'video' : 'image';

    Swal.fire({
        title: `Delete this ${mediaType}?`,
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/products/${productId}/images/${imageId}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        container.fadeOut(300, function() {
                            $(this).remove();
                        });

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: `${mediaType.charAt(0).toUpperCase() + mediaType.slice(1)} removed successfully`,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', `Failed to delete ${mediaType}`, 'error');
                }
            });
        }
    });
}

function setPrimaryImage(productId, imageId) {
    $.ajax({
        url: `/admin/products/${productId}/images/${imageId}/set-primary`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Primary image updated',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Failed to set primary image', 'error');
        }
    });
}

// Format bytes to human readable
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function toggleFeatured() {
    $.ajax({
        url: `/admin/products/${PRODUCT_ID}/toggle-featured`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Failed to toggle featured status', 'error');
        }
    });
}

function togglePublish() {
    $.ajax({
        url: `/admin/products/${PRODUCT_ID}/toggle-publish`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Failed to toggle publish status', 'error');
        }
    });
}

function duplicateProduct() {
    Swal.fire({
        title: 'Duplicate this product?',
        text: "A copy will be created with '(Copy)' appended to the name",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, duplicate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/products/${PRODUCT_ID}/duplicate`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
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
                            text: 'Product duplicated successfully',
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = `/admin/products/${response.product_id}/edit`;
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to duplicate product', 'error');
                }
            });
        }
    });
}

function deleteProduct() {
    Swal.fire({
        title: 'Delete this product?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        input: 'checkbox',
        inputPlaceholder: 'I understand this action is permanent'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: `/admin/products/${PRODUCT_ID}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Deleting...',
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
                            title: 'Deleted!',
                            text: 'Product deleted successfully',
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '/admin/products';
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete product', 'error');
                }
            });
        } else if (result.isConfirmed && !result.value) {
            Swal.fire('Cancelled', 'Please check the confirmation box', 'info');
        }
    });
}

function openAddCategoryModal() {
    $('#addCategoryModal').modal('show');
}

function openAddVendorModal() {
    $('#addVendorModal').modal('show');
}

// Close tag suggestions when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#tagsContainer, #tagSuggestions').length) {
        $('#tagSuggestions').hide();
    }
});

// Focus tag input when clicking on tags container
$(document).on('click', '#tagsContainer', function() {
    $('#tagInput').focus();
});
//
// URL Handle Management
$('#editUrlBtn').on('click', function() {
    $('#urlEditSection').slideDown();
    $('#newSlugInput').focus();
});

$('#cancelUrlBtn').on('click', function() {
    $('#urlEditSection').slideUp();
    $('#newSlugInput').val('{{ $product->slug }}');
});

// Auto-generate slug from input
$('#newSlugInput').on('keyup', function() {
    let value = $(this).val();
    let slug = value.toLowerCase()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')      // Replace spaces with hyphens
        .replace(/-+/g, '-')       // Replace multiple hyphens with single
        .replace(/^-+|-+$/g, '');  // Remove leading/trailing hyphens
    $(this).val(slug);
});

// Save URL change
$('#saveUrlBtn').on('click', function() {
    const oldSlug = '{{ $product->slug }}';
    const newSlug = $('#newSlugInput').val().trim();
    const createRedirect = $('#createRedirect').is(':checked');

    if (!newSlug) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter a valid URL slug'
        });
        return;
    }

    if (newSlug === oldSlug) {
        Swal.fire({
            icon: 'info',
            title: 'No Changes',
            text: 'The URL slug is the same as before'
        });
        return;
    }

    // Show confirmation
    Swal.fire({
        title: 'Change Product URL?',
        html: `
            <div class="text-start">
                <p><strong>Old URL:</strong><br><code>{{ url('/') }}/products/${oldSlug}</code></p>
                <p><strong>New URL:</strong><br><code>{{ url('/') }}/products/${newSlug}</code></p>
                ${createRedirect ? '<p class="text-success"><i class="bi bi-check-circle"></i> A 301 redirect will be created</p>' : '<p class="text-warning"><i class="bi bi-exclamation-triangle"></i> No redirect will be created</p>'}
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, change URL',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            updateProductUrl(newSlug, createRedirect);
        }
    });
});

function updateProductUrl(newSlug, createRedirect) {
    $.ajax({
        url: `/admin/products/${PRODUCT_ID}/update-url`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            slug: newSlug,
            create_redirect: createRedirect ? 1 : 0
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Updating URL...',
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
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Failed to update URL'
            });
        }
    });
}
//
// Tax Settings Toggle
$('#isTaxable').on('change', function() {
    if ($(this).is(':checked')) {
        $('#taxSettingsSection').slideDown();
    } else {
        $('#taxSettingsSection').slideUp();
    }
    updateTaxPreview();
});

// Update tax preview when values change
$('#regularPrice, #salePrice, #taxPercentage, #taxType').on('keyup change', function() {
    updateTaxPreview();
});

// Update tax preview
// Update tax preview
function updateTaxPreview() {
    if (!$('#isTaxable').is(':checked')) {
        $('#taxPreview').hide();
        return;
    }

    const price = parseFloat($('#salePrice').val()) || parseFloat($('#regularPrice').val()) || 0;
    const taxPercentage = parseFloat($('#taxPercentage').val()) || 0;
    const taxType = $('#taxType').val();
    const currency = $('select[name="curency"]').val() || 'USD';
    const symbol = get_currency_symbol(currency);

    if (price <= 0) {
        $('#taxPreview').hide();
        return;
    }

    let taxAmount, priceExcludingTax, priceIncludingTax;

    if (taxType === 'inclusive') {
        // ✅ For inclusive tax: Show that tax is already included
        priceIncludingTax = price;
        taxAmount = 0; // Tax is already in the price
        priceExcludingTax = price; // Display as-is

        const html = `
            <p class="mb-1"><strong>Price (Tax Inclusive):</strong> ${symbol} ${price.toFixed(2)}</p>
            <p class="mb-0 text-muted"><small><i class="bi bi-info-circle"></i> Tax is already included in the price</small></p>
        `;

        $('#taxPreviewContent').html(html);
        $('#taxPreview').slideDown();
    } else {
        // ✅ For exclusive tax: Calculate and show breakdown
        if (taxPercentage <= 0) {
            $('#taxPreview').hide();
            return;
        }

        priceExcludingTax = price;
        taxAmount = price * (taxPercentage / 100);
        priceIncludingTax = price + taxAmount;

        const html = `
            <p class="mb-1"><strong>Base Price:</strong> ${symbol} ${priceExcludingTax.toFixed(2)}</p>
            <p class="mb-1"><strong>Tax (${taxPercentage}%):</strong> ${symbol} ${taxAmount.toFixed(2)}</p>
            <p class="mb-0"><strong>Final Price:</strong> ${symbol} ${priceIncludingTax.toFixed(2)}</p>
        `;

        $('#taxPreviewContent').html(html);
        $('#taxPreview').slideDown();
    }
}

// Helper function for currency symbol (if not already defined)
function get_currency_symbol(code) {
    const symbols = {
        'USD': '$', 'EUR': '€', 'GBP': '£', 'PKR': '₨', 'SAR': '﷼',
        'AED': 'د.إ', 'CAD': 'C$', 'AUD': 'A$', 'JPY': '¥', 'CNY': '¥', 'INR': '₹'
    };
    return symbols[code] || code;
}

// Initialize on page load
$(document).ready(function() {
    updateTaxPreview();
});
</script>
<script>
// ==================== VARIANT MANAGEMENT ====================

let variantModal;

$(document).ready(function() {
    variantModal = new bootstrap.Modal(document.getElementById('variantModal'));
});

// Toggle variants section
function toggleVariantsSection() {
    const hasVariants = $('#hasVariants').is(':checked');

    if (hasVariants) {
        $('#variantsContent').slideDown();
        $('#noVariantsMessage').slideUp();
    } else {
        // Check if variants exist
        if ($('.variant-card').length > 0) {
            Swal.fire({
                title: 'Warning!',
                text: 'This product has existing variants. Disabling variants will not delete them, but they will be hidden.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#5B914C',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#variantsContent').slideUp();
                    $('#noVariantsMessage').slideDown();
                } else {
                    $('#hasVariants').prop('checked', true);
                }
            });
        } else {
            $('#variantsContent').slideUp();
            $('#noVariantsMessage').slideDown();
        }
    }
}

// Open add variant modal
function openAddVariantModal() {
    // Reset form
    $('#variantForm')[0].reset();
    $('#variantId').val('');
    $('#variantImagePreview').empty();

    // Clear validation
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();

    // Update modal title
    $('#variantModalTitle').html('<i class="bi bi-plus-circle"></i> Add New Variant');
    $('#variantSubmitText').text('Save Variant');

    // Show modal
    variantModal.show();
}

// Generate variant SKU
function generateVariantSKU() {
    const sku = 'VAR-' + Math.random().toString(36).substring(2, 12).toUpperCase();
    $('#variantSku').val(sku);
}

// Variant image preview
$('#variantImage').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#variantImagePreview').html(`
                <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">
            `);
        };
        reader.readAsDataURL(file);
    }
});

// Submit variant form
$('#variantForm').on('submit', function(e) {
    e.preventDefault();

    const variantId = $('#variantId').val();
    const isEdit = variantId !== '';
    const url = isEdit
        ? `/admin/products/{{ $product->id }}/variants/${variantId}`
        : `/admin/products/{{ $product->id }}/variants`;

    const formData = new FormData(this);
    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            Swal.fire({
                title: isEdit ? 'Updating Variant...' : 'Creating Variant...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close();

            if (response.success) {
                variantModal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Reload variants list
                loadVariants();
            }
        },
        error: function(xhr) {
            Swal.close();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();

            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function(key, messages) {
                    const input = $(`[name="${key}"]`);
                    input.addClass('is-invalid');
                    input.siblings('.invalid-feedback').text(messages[0]).show();
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: Object.values(errors).flat().map(err => `• ${err}`).join('<br>')
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to save variant'
                });
            }
        }
    });
});

// Edit variant
function editVariant(variantId) {
    $.ajax({
        url: `/admin/products/{{ $product->id }}/variants/${variantId}`,
        type: 'GET',
        beforeSend: function() {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close();

            if (response.success) {
                const variant = response.variant;

                // Populate form
                $('#variantId').val(variant.id);
                $('#variantName').val(variant.variant_name);
                $('#variantValue').val(variant.variant_value);
                $('#variantSku').val(variant.sku);
                $('#variantPrice').val(variant.price);
                $('#variantSalePrice').val(variant.sale_price);
                $('#variantWeight').val(variant.weight);
                $('#variantLength').val(variant.length);
                $('#variantWidth').val(variant.width);
                $('#variantHeight').val(variant.height);
                $('#variantStockQuantity').val(variant.stock_quantity);
                $('#variantLowStockThreshold').val(variant.low_stock_threshold);
                $('#variantStatus').val(variant.status_key_code);
                $('#variantIsDefault').prop('checked', variant.is_default);

                // Show current image
                if (variant.image_path) {
                    $('#variantImagePreview').html(`
                        <img src="${variant.image_url}" class="img-thumbnail" style="max-width: 200px;">
                        <p class="text-muted small mt-1">Current image (upload new to replace)</p>
                    `);
                }

                // Update modal title
                $('#variantModalTitle').html('<i class="bi bi-pencil"></i> Edit Variant');
                $('#variantSubmitText').text('Update Variant');

                // Show modal
                variantModal.show();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load variant data'
            });
        }
    });
}

// Set default variant
function setDefaultVariant(variantId) {
    Swal.fire({
        title: 'Set as Default?',
        text: 'This will be the default variant shown to customers',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, set as default',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/products/{{ $product->id }}/variants/${variantId}/set-default`,
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
                            timer: 2000,
                            showConfirmButton: false
                        });

                        loadVariants();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to set default variant'
                    });
                }
            });
        }
    });
}

// Delete variant
function deleteVariant(variantId) {
    Swal.fire({
        title: 'Delete Variant?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/products/{{ $product->id }}/variants/${variantId}`,
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
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $(`#variant-${variantId}`).fadeOut(300, function() {
                            $(this).remove();

                            // Check if no variants left
                            if ($('.variant-card').length === 0) {
                                $('#variantsList').html(`
                                    <div class="alert alert-warning" id="noVariantsAlert">
                                        <i class="bi bi-exclamation-triangle"></i> No variants added yet. Click "Add New Variant" to get started.
                                    </div>
                                `);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete variant'
                    });
                }
            });
        }
    });
}

// Reload variants list
function loadVariants() {
    $.ajax({
        url: `/admin/products/{{ $product->id }}/variants`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#variantsList').html(response.html);

                // Remove no variants alert if exists
                $('#noVariantsAlert').remove();
            }
        }
    });
}

// Tax Type change handler
$('#taxType').on('change', function() {
    const taxType = $(this).val();

    if (taxType === 'inclusive') {
        // Disable tax percentage when inclusive
        $('#taxPercentage').val('0').prop('disabled', true).addClass('bg-light');

        // Update help text
        $('#taxPercentage').closest('.mb-3').find('.form-text').html(
            '<small class="text-muted"><i class="bi bi-info-circle"></i> Tax percentage is not needed for inclusive pricing</small>'
        );
    } else {
        // Enable tax percentage when exclusive
        $('#taxPercentage').prop('disabled', false).removeClass('bg-light');

        // Restore original help text
        $('#taxPercentage').closest('.mb-3').find('.form-text').html(
            'Enter tax rate (e.g., 15 for 15% tax)'
        );
    }

    updateTaxPreview();
});

// Trigger on page load to set initial state based on existing product data
$(document).ready(function() {
    $('#taxType').trigger('change');
});
</script>
@endpush
