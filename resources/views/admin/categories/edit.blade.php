@extends('admin.layouts.app')

@section('title', 'Edit Category')

@push('styles')
<style>
    .form-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .form-section-title {
        color: #5B914C;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border: 2px dashed #5B914C;
        border-radius: 8px;
        padding: 10px;
        position: relative;
    }
    .image-preview img {
        width: 100%;
        height: auto;
        border-radius: 5px;
    }
    .image-preview .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
    }
    .btn-submit {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        padding: 10px 30px;
    }
    .btn-submit:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }
    .required-label::after {
        content: " *";
        color: #dc3545;
    }
    .existing-image {
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Edit Category</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> View Details
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form id="categoryForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="category_id" value="{{ $category->id }}">

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</h3>

                    <div class="mb-3">
                        <label for="title" class="form-label required-label">Category Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $category->title) }}" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $category->slug) }}">
                        <small class="text-muted">Leave empty to auto-generate from title</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control" id="short_description" name="short_description" rows="2">{{ old('short_description', $category->short_description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Full Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $category->description) }}</textarea>
                    </div>
                </div>

                <!-- Images -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-image"></i> Images</h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Main Image</label>
                            @if($category->image)
                            <div class="existing-image image-preview">
                                <img src="{{ $category->getImageUrl('image') }}" alt="Current Image">
                                <button type="button" class="remove-image" data-field="image" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endif
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Recommended: 800x800px</small>
                            <div class="image-preview mt-2" id="imagePreview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="thumbnail" class="form-label">Thumbnail</label>
                            @if($category->thumbnail)
                            <div class="existing-image image-preview">
                                <img src="{{ $category->getImageUrl('thumbnail') }}" alt="Current Thumbnail">
                                <button type="button" class="remove-image" data-field="thumbnail" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endif
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            <small class="text-muted">Recommended: 200x200px</small>
                            <div class="image-preview mt-2" id="thumbnailPreview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="banner_image" class="form-label">Banner Image</label>
                            @if($category->banner_image)
                            <div class="existing-image image-preview">
                                <img src="{{ $category->getImageUrl('banner_image') }}" alt="Current Banner">
                                <button type="button" class="remove-image" data-field="banner_image" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endif
                            <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                            <small class="text-muted">Recommended: 1920x400px</small>
                            <div class="image-preview mt-2" id="bannerPreview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            @if($category->icon)
                            <div class="existing-image image-preview">
                                <img src="{{ $category->getImageUrl('icon') }}" alt="Current Icon">
                                <button type="button" class="remove-image" data-field="icon" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endif
                            <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                            <small class="text-muted">Recommended: 64x64px (SVG preferred)</small>
                            <div class="image-preview mt-2" id="iconPreview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-search"></i> SEO Settings</h3>

                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" maxlength="255">
                        <small class="text-muted">Recommended: 50-60 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="500">{{ old('meta_description', $category->meta_description) }}</textarea>
                        <small class="text-muted">Recommended: 150-160 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $category->meta_keywords) }}">
                        <small class="text-muted">Separate with commas</small>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Category Info -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-info-square"></i> Category Info</h3>

                    <div class="mb-2">
                        <small class="text-muted">Created</small>
                        <div><strong>{{ $category->created_at->format('M d, Y H:i') }}</strong></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Last Updated</small>
                        <div><strong>{{ $category->updated_at->format('M d, Y H:i') }}</strong></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Products Count</small>
                        <div><strong>{{ $category->products_count }}</strong></div>
                    </div>
                    @if($category->creator)
                    <div class="mb-2">
                        <small class="text-muted">Created By</small>
                        <div><strong>{{ $category->creator->name }}</strong></div>
                    </div>
                    @endif
                </div>

                <!-- Category Settings -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-gear"></i> Settings</h3>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">None (Root Category)</option>
                            @foreach($parentCategories as $parent)
                                @if($parent->id !== $category->id)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->indent }}{{ $parent->title }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status_key_code" class="form-label required-label">Status</label>
                        <select class="form-select" id="status_key_code" name="status_key_code" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ old('status_key_code', $category->status_key_code) == $status->key_code ? 'selected' : '' }}>
                                    {{ $status->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="order" name="order" value="{{ old('order', $category->order) }}" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>

                <!-- Display Options -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-eye"></i> Display Options</h3>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star-fill text-warning"></i> Featured Category
                        </label>
                        <small class="d-block text-muted">Show in featured categories section</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show_in_menu" name="show_in_menu" value="1" {{ old('show_in_menu', $category->show_in_menu) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_in_menu">
                            <i class="bi bi-menu-button text-info"></i> Show in Menu
                        </label>
                        <small class="d-block text-muted">Display in navigation menu</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show_on_home" name="show_on_home" value="1" {{ old('show_on_home', $category->show_on_home) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_on_home">
                            <i class="bi bi-house text-primary"></i> Show on Homepage
                        </label>
                        <small class="d-block text-muted">Display on homepage</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <button type="submit" class="btn btn-submit w-100 mb-2" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Update Category
                    </button>
                    <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Auto-generate slug from title (only if empty)
    let originalSlug = $('#slug').val();
    $('#title').on('input', function() {
        if (!originalSlug || $('#slug').val() === originalSlug) {
            const title = $(this).val();
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            $('#slug').val(slug);
        }
    });

    // Image preview function
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(`#${previewId}`).show().find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Image preview handlers
    $('#image').on('change', function() {
        previewImage(this, 'imagePreview');
    });

    $('#thumbnail').on('change', function() {
        previewImage(this, 'thumbnailPreview');
    });

    $('#banner_image').on('change', function() {
        previewImage(this, 'bannerPreview');
    });

    $('#icon').on('change', function() {
        previewImage(this, 'iconPreview');
    });

    // Remove existing image
    $('.remove-image').on('click', function() {
        const field = $(this).data('field');
        const imageContainer = $(this).closest('.existing-image');

        Swal.fire({
            title: 'Remove Image?',
            text: "This will remove the image when you save.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                imageContainer.remove();
                // Add hidden input to mark for removal
                $('<input>').attr({
                    type: 'hidden',
                    name: `remove_${field}`,
                    value: '1'
                }).appendTo('#categoryForm');
            }
        });
    });

    // Form submission
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const categoryId = $('input[name="category_id"]').val();
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        // Disable submit button
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: '{{ route("admin.categories.update", ":id") }}'.replace(':id', categoryId),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);

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
});
</script>
@endpush
