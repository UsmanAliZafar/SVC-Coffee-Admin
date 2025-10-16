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
                            <input type="text" name="slug" id="productSlug" class="form-control" value="{{ $product->slug }}">
                            <div class="form-text">URL-friendly version of the name</div>
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
                        <select name="product_type" class="form-select">
                            @foreach($productTypes as $key => $label)
                                <option value="{{ $key }}" {{ $product->product_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
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
                        <label class="form-label">Gallery Images</label>
                        <div class="dropzone-area" id="dropzoneArea">
                            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #5B914C;"></i>
                            <p class="mb-2"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted mb-0">PNG, JPG, GIF, WEBP up to 2MB each</p>
                        </div>
                        <input type="file" name="images[]" id="galleryImages" class="d-none" accept="image/*" multiple>

                        <div id="galleryPreview" class="mt-3">
                            @foreach($product->images as $image)
                            <div class="image-preview-container" data-id="{{ $image->id }}">
                                @if($image->is_primary)
                                <span class="primary-badge"><i class="bi bi-star-fill"></i> Primary</span>
                                @endif
                                <img src="{{ $image->getImageUrl() }}" class="image-preview" alt="{{ $image->image_name }}">
                                <button type="button" class="remove-image" onclick="deleteProductImage('{{ $product->id }}', '{{ $image->id }}', this)">
                                    <i class="bi bi-x"></i>
                                </button>
                                @if(!$image->is_primary)
                                <button type="button" class="set-primary-btn" onclick="setPrimaryImage('{{ $product->id }}', '{{ $image->id }}')">
                                    <i class="bi bi-star"></i> Set Primary
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-search"></i> SEO Settings</h5>

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

            <!-- Right Column - NEXT PART -->
        </div>
    </form>
</div>
@endsection
@push('scripts')
<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

<script>
const PRODUCT_ID = '{{ $product->id }}';

$(document).ready(function() {

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

    // Upload images to product
    function uploadImagesToProduct(files) {
        if (files.length === 0) return;

        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        Array.from(files).forEach(file => {
            formData.append('images[]', file);
        });

        $.ajax({
            url: `/admin/products/${PRODUCT_ID}/images/upload`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Uploading Images...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    response.images.forEach(image => {
                        $('#galleryPreview').append(`
                            <div class="image-preview-container" data-id="${image.id}">
                                <img src="${image.url}" class="image-preview" alt="${image.name}">
                                <button type="button" class="remove-image" onclick="deleteProductImage('${PRODUCT_ID}', '${image.id}', this)">
                                    <i class="bi bi-x"></i>
                                </button>
                                <button type="button" class="set-primary-btn" onclick="setPrimaryImage('${PRODUCT_ID}', '${image.id}')">
                                    <i class="bi bi-star"></i> Set Primary
                                </button>
                            </div>
                        `);
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Uploaded!',
                        text: `${response.images.length} image(s) uploaded successfully`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to upload images', 'error');
            }
        });
    }

    // Initialize tags with existing product tags
    let selectedTags = @json($product->tags->map(function($tag) {
        return ['id' => $tag->id, 'name' => $tag->name];
    }));
    renderTags();

    let tagSearchTimeout;

    $(document).on('keyup', '#tagInput', function(e) {
        const value = $(this).val().trim();

        // Add tag on Enter or Comma
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            if (value) {
                addTag(value.replace(',', ''));
                $(this).val('');
                $('#tagSuggestions').hide();
            }
            return;
        }

        // Backspace on empty input - remove last tag
        if (e.key === 'Backspace' && value === '' && selectedTags.length > 0) {
            selectedTags.pop();
            renderTags();
            return;
        }

        // Search tags
        if (value.length >= 2) {
            clearTimeout(tagSearchTimeout);
            tagSearchTimeout = setTimeout(function() {
                searchTags(value);
            }, 300);
        } else {
            $('#tagSuggestions').hide();
        }
    });

    function searchTags(query) {
        $.ajax({
            url: '/admin/products/tags/search',
            data: { q: query },
            success: function(response) {
                if (response.success && response.tags.length > 0) {
                    let html = '';
                    response.tags.forEach(tag => {
                        if (!selectedTags.find(t => t.id === tag.id)) {
                            html += `<div class="tag-suggestion-item" data-id="${tag.id}" data-name="${tag.name}">${tag.name}</div>`;
                        }
                    });
                    if (html) {
                        $('#tagSuggestions').html(html).show();
                    } else {
                        $('#tagSuggestions').hide();
                    }
                } else {
                    $('#tagSuggestions').hide();
                }
            }
        });
    }

    $(document).on('click', '.tag-suggestion-item', function() {
        const tagId = $(this).data('id');
        const tagName = $(this).data('name');
        addTag(tagName, tagId);
        $('#tagInput').val('');
        $('#tagSuggestions').hide();
    });

    function addTag(name, id = null) {
        // Check if tag already exists
        if (selectedTags.find(t => t.name.toLowerCase() === name.toLowerCase())) {
            return;
        }

        if (id) {
            // Existing tag
            selectedTags.push({ id: id, name: name });
            renderTags();
        } else {
            // Create new tag
            $.ajax({
                url: '/admin/products/tags/create',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: name
                },
                success: function(response) {
                    if (response.success) {
                        selectedTags.push({ id: response.tag.id, name: response.tag.name });
                        renderTags();
                    }
                }
            });
        }
    }

    function renderTags() {
        const tagIds = selectedTags.map(t => t.id);
        $('#tagsHiddenInput').val(JSON.stringify(tagIds));

        let html = '';
        selectedTags.forEach((tag, index) => {
            html += `
                <div class="tag-item">
                    ${tag.name}
                    <button type="button" class="remove-tag" onclick="removeTag(${index})">×</button>
                </div>
            `;
        });
        html += '<input type="text" id="tagInput" class="tag-input" placeholder="Type to search or add tags...">';

        $('#tagsContainer').html(html);
    }

    window.removeTag = function(index) {
        selectedTags.splice(index, 1);
        renderTags();
    };

    // Form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        // Get CKEditor content
        if (descriptionEditor) {
            $('textarea[name="description"]').val(descriptionEditor.getData());
        }

        let formData = new FormData(this);

        $.ajax({
            url: `/admin/products/${PRODUCT_ID}`,
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
                url: `/admin/products/${productId}/images/${imageId}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $(button).closest('.image-preview-container').fadeOut(300, function() {
                            $(this).remove();
                        });

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Image removed successfully',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to delete image', 'error');
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
</script>
@endpush
