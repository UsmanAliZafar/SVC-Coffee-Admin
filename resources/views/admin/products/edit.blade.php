@extends('admin.layouts.app')

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
    .set-primary {
        position: absolute;
        bottom: 5px;
        left: 5px;
        background: #5B914C;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 0.75rem;
        cursor: pointer;
    }
    .primary-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        background: #ffc107;
        color: #000;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
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
    .variant-row {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
    }
    .ck-editor__editable {
        min-height: 300px;
    }
    .info-badge {
        background: #e7f3ff;
        border: 1px solid #b3d7ff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .info-badge .info-label {
        font-weight: 600;
        color: #666;
        font-size: 0.85rem;
    }
    .info-badge .info-value {
        font-size: 0.95rem;
        color: #333;
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
    <div class="info-badge">
        <div class="row">
            <div class="col-md-3">
                <div class="info-label">Product ID</div>
                <div class="info-value">{{ $product->id }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Created By</div>
                <div class="info-value">{{ $product->creator ? $product->creator->name : 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Created At</div>
                <div class="info-value">{{ $product->created_at->format('M d, Y H:i') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Last Updated</div>
                <div class="info-value">{{ $product->updated_at->format('M d, Y H:i') }}</div>
            </div>
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
                            <input type="text" name="slug" id="productSlug" class="form-control" value="{{ $product->slug }}">
                            <div class="form-text">Leave empty to auto-generate from product name</div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ $product->sku }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" value="{{ $product->barcode }}">
                        <div class="invalid-feedback"></div>
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

                <!-- Product Images -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-image"></i> Product Images</h5>

                    <div class="mb-3">
                        <label class="form-label">Main Image</label>
                        <input type="file" name="main_image" id="mainImage" class="form-control" accept="image/*">
                        <div class="form-text">Upload new image to replace current (max 2MB)</div>

                        @if($product->main_image)
                        <div id="currentMainImage" class="mt-2">
                            <div class="image-preview-container">
                                <img src="{{ $product->getMainImageUrl() }}" class="image-preview" alt="Main Image">
                                <span class="primary-badge">Current Main Image</span>
                            </div>
                        </div>
                        @endif

                        <div id="mainImagePreview" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gallery Images</label>

                        @if($product->images->count() > 0)
                        <div class="mb-3">
                            <label class="form-text d-block mb-2">Current Gallery Images</label>
                            <div id="existingGallery">
                                @foreach($product->images as $image)
                                <div class="image-preview-container" data-image-id="{{ $image->id }}">
                                    <img src="{{ $image->getImageUrl() }}" class="image-preview" alt="Gallery Image">
                                    @if($image->is_primary)
                                        <span class="primary-badge"><i class="bi bi-star-fill"></i> Primary</span>
                                    @else
                                        <button type="button" class="set-primary" onclick="setPrimaryImage({{ $image->id }})">
                                            <i class="bi bi-star"></i> Set Primary
                                        </button>
                                    @endif
                                    <button type="button" class="remove-image" onclick="deleteGalleryImage({{ $image->id }})">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="dropzone-area" id="dropzoneArea">
                            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #5B914C;"></i>
                            <p class="mb-2"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted mb-0">PNG, JPG, GIF up to 2MB each</p>
                        </div>
                        <input type="file" name="images[]" id="galleryImages" class="d-none" accept="image/*" multiple>
                        <div id="galleryPreview" class="mt-3"></div>
                    </div>
                </div>

                <!-- Variants (for Variable Products) -->
                @if($product->product_type === 'variable')
                <div class="form-section" id="variantsSection">
                    <h5 class="section-title"><i class="bi bi-layers"></i> Product Variants</h5>

                    @if($product->variants->count() > 0)
                    <div class="mb-3">
                        <label class="form-text d-block mb-2">Existing Variants</label>
                        @foreach($product->variants as $variant)
                        <div class="variant-row">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>{{ $variant->getFullName() }}</strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted">SKU: {{ $variant->sku }}</small>
                                </div>
                                <div class="col-md-2">
                                    <strong>{{ $variant->getFormattedPrice() }}</strong>
                                </div>
                                <div class="col-md-2">
                                    {!! $variant->getStockBadge() !!}
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="{{ route('admin.products.variants.edit', [$product->id, $variant->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariant({{ $variant->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div id="variantsContainer"></div>

                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addVariant">
                        <i class="bi bi-plus"></i> Add New Variant
                    </button>
                </div>
                @endif

                <!-- SEO Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-search"></i> SEO Settings</h5>

                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ $product->meta_title }}">
                        <div class="form-text">Recommended: 50-60 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3">{{ $product->meta_description }}</textarea>
                        <div class="form-text">Recommended: 150-160 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ $product->meta_keywords }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input type="text" name="canonical_url" class="form-control" value="{{ $product->canonical_url }}">
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Product Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-gear"></i> Product Settings</h5>

                    <div class="mb-3">
                        <label class="form-label required-field">Product Type</label>
                        <select name="product_type" id="productType" class="form-select" required>
                            @foreach($productTypes as $key => $label)
                                <option value="{{ $key }}" {{ $product->product_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required-field">Status</label>
                        <select name="status_key_code" class="form-select" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ $product->status_key_code === $status->key_code ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id === $category->id ? 'selected' : '' }}>
                                    {{ $category->indent }}{{ $category->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $product->vendor_id === $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <select name="tags[]" id="productTags" class="form-select" multiple>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ $product->tags->contains($tag->id) ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
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
                                <option value="{{ $code }}" {{ $product->curency === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required-field">Regular Price</label>
                        <input type="number" name="price" class="form-control" value="{{ $product->price }}" step="0.01" min="0" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sale Price</label>
                        <input type="number" name="sale_price" class="form-control" value="{{ $product->sale_price }}" step="0.01" min="0">
                        <div class="form-text">Leave empty if not on sale</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cost Price</label>
                        <input type="number" name="cost_price" class="form-control" value="{{ $product->cost_price }}" step="0.01" min="0">
                        <div class="form-text">Your cost (for profit calculation)</div>
                    </div>

                    @if($product->isOnSale())
                    <div class="alert alert-success">
                        <i class="bi bi-tag"></i> <strong>On Sale!</strong><br>
                        Discount: {{ $product->getFormattedSalePrice() }} (-{{ $product->getDiscountPercentage() }}%)
                    </div>
                    @endif
                </div>

                <!-- Stock Info -->
                @if($product->track_inventory)
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-box-seam"></i> Stock Information</h5>

                    <div class="alert alert-info mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Total Stock:</strong><br>
                                <h4 class="mb-0">{{ $product->getTotalStock() }} units</h4>
                            </div>
                            <div>
                                {!! $product->isInStock() ?
                                    '<span class="badge bg-success">In Stock</span>' :
                                    '<span class="badge bg-danger">Out of Stock</span>' !!}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-save btn-lg">
                            <i class="bi bi-check-circle"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.duplicate', $product->id) }}" class="btn btn-outline-secondary" onclick="return confirm('Duplicate this product?')">
                            <i class="bi bi-files"></i> Duplicate Product
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
@endsection

@push('scripts')
<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

<script>
$(document).ready(function() {

    const productId = '{{ $product->id }}';

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
    $('#productName').on('keyup', function() {
        let name = $(this).val();
        let slug = name.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        $('#productSlug').val(slug);
    });

    // Main image preview
    $('#mainImage').on('change', function(e) {
        let file = e.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#currentMainImage').hide();
                $('#mainImagePreview').html(`
                    <div class="image-preview-container">
                        <img src="${e.target.result}" class="image-preview" alt="New Main Image">
                        <span class="primary-badge">New Main Image</span>
                        <button type="button" class="remove-image" onclick="removeMainImage()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    // Gallery images - dropzone click
    $('#dropzoneArea').on('click', function() {
        $('#galleryImages').click();
    });

    // Gallery images preview
    let galleryFiles = [];
    $('#galleryImages').on('change', function(e) {
        let files = Array.from(e.target.files);

        files.forEach(file => {
            galleryFiles.push(file);

            let reader = new FileReader();
            reader.onload = function(e) {
                $('#galleryPreview').append(`
                    <div class="image-preview-container" data-index="${galleryFiles.length - 1}">
                        <img src="${e.target.result}" class="image-preview" alt="New Gallery Image">
                        <span class="primary-badge">New</span>
                        <button type="button" class="remove-image" onclick="removeGalleryImage(${galleryFiles.length - 1})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        });
    });

    // Product type change - show/hide variants
    $('#productType').on('change', function() {
        if ($(this).val() === 'variable') {
            $('#variantsSection').removeClass('d-none');
        } else {
            $('#variantsSection').addClass('d-none');
        }
    });

    // Add variant
    let variantIndex = {{ $product->variants->count() }};
    $('#addVariant').on('click', function() {
        variantIndex++;
        let variantHtml = `
            <div class="variant-row" id="variant-${variantIndex}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Variant Name</label>
                        <input type="text" name="variants[${variantIndex}][name]" class="form-control" placeholder="e.g., Size">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Variant Value</label>
                        <input type="text" name="variants[${variantIndex}][value]" class="form-control" placeholder="e.g., Large">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">SKU</label>
                        <input type="text" name="variants[${variantIndex}][sku]" class="form-control" placeholder="VAR-SKU">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Price</label>
                        <input type="number" name="variants[${variantIndex}][price]" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger w-100" onclick="removeVariant(${variantIndex})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#variantsContainer').append(variantHtml);
    });

    // Form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        // Get CKEditor content
        if (descriptionEditor) {
            $('textarea[name="description"]').val(descriptionEditor.getData());
        }

        let formData = new FormData(this);

        // Add gallery images
        galleryFiles.forEach((file, index) => {
            formData.append('images[]', file);
        });

        $.ajax({
            url: '{{ route("admin.products.update", $product->id) }}',
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
                        location.reload();
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
                    let errors = xhr.responseJSON.errors;

                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').empty();

                    // Show errors
                    $.each(errors, function(field, messages) {
                        let input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form for errors'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to update product'
                    });
                }
            }
        });
    });

});

// Remove main image
function removeMainImage() {
    $('#mainImage').val('');
    $('#mainImagePreview').empty();
    $('#currentMainImage').show();
}

// Remove gallery image (new upload)
function removeGalleryImage(index) {
    $(`.image-preview-container[data-index="${index}"]`).remove();
    galleryFiles.splice(index, 1);
}

// Delete existing gallery image
function deleteGalleryImage(imageId) {
    Swal.fire({
        title: 'Delete this image?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.products.images.delete", [$product->id, ":imageId"]) }}'.replace(':imageId', imageId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $(`.image-preview-container[data-image-id="${imageId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete image'
                    });
                }
            });
        }
    });
}

// Set primary image
function setPrimaryImage(imageId) {
    $.ajax({
        url: '{{ route("admin.products.images.set-primary", [$product->id, ":imageId"]) }}'.replace(':imageId', imageId),
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
                text: xhr.responseJSON?.message || 'Failed to set primary image'
            });
        }
    });
}

// Delete variant
function deleteVariant(variantId) {
    Swal.fire({
        title: 'Delete this variant?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.products.variants.destroy", [$product->id, ":variantId"]) }}'.replace(':variantId', variantId),
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
                        text: xhr.responseJSON?.message || 'Failed to delete variant'
                    });
                }
            });
        }
    });
}

// Remove new variant (not yet saved)
function removeVariant(index) {
    $(`#variant-${index}`).remove();
}

// Upload new gallery images via AJAX
function uploadGalleryImages() {
    if (galleryFiles.length === 0) return;

    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');

    galleryFiles.forEach((file, index) => {
        formData.append('images[]', file);
    });

    $.ajax({
        url: '{{ route("admin.products.images.upload", $product->id) }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Images Uploaded!',
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
                text: xhr.responseJSON?.message || 'Failed to upload images'
            });
        }
    });
}
</script>
@endpush
