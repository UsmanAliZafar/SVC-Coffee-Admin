@extends('admin.layouts.app')

@section('title', 'Create Category')

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
        display: none;
    }
    .image-preview img {
        width: 100%;
        height: auto;
        border-radius: 5px;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Create New Category</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form id="categoryForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</h3>

                    <div class="mb-3">
                        <label for="title" class="form-label required-label">Category Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL)</label>
                        <input type="text" class="form-control" id="slug" name="slug" placeholder="Auto-generated from title">
                        <small class="text-muted">Leave empty to auto-generate from title</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Full Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                    </div>
                </div>

                <!-- Images -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-image"></i> Images</h3>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Main Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Recommended: 800x800px</small>
                            <div class="image-preview mt-2" id="imagePreview">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="thumbnail" class="form-label">Thumbnail</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            <small class="text-muted">Recommended: 200x200px</small>
                            <div class="image-preview mt-2" id="thumbnailPreview">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="banner_image" class="form-label">Banner Image</label>
                            <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                            <small class="text-muted">Recommended: 1920x400px</small>
                            <div class="image-preview mt-2" id="bannerPreview">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                            <small class="text-muted">Recommended: 64x64px (SVG preferred)</small>
                            <div class="image-preview mt-2" id="iconPreview">
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
                        <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="255">
                        <small class="text-muted">Recommended: 50-60 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="500"></textarea>
                        <small class="text-muted">Recommended: 150-160 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3">
                        <small class="text-muted">Separate with commas</small>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Category Settings -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-gear"></i> Settings</h3>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">None (Root Category)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->indent }}{{ $parent->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status_key_code" class="form-label required-label">Status</label>
                        <select class="form-select" id="status_key_code" name="status_key_code" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ $status->key_code === 'CATEGORY_ACTIVE' ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="order" name="order" value="0" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>

                <!-- Display Options -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-eye"></i> Display Options</h3>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star-fill text-warning"></i> Featured Category
                        </label>
                        <small class="d-block text-muted">Show in featured categories section</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show_in_menu" name="show_in_menu" value="1" checked>
                        <label class="form-check-label" for="show_in_menu">
                            <i class="bi bi-menu-button text-info"></i> Show in Menu
                        </label>
                        <small class="d-block text-muted">Display in navigation menu</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show_on_home" name="show_on_home" value="1">
                        <label class="form-check-label" for="show_on_home">
                            <i class="bi bi-house text-primary"></i> Show on Homepage
                        </label>
                        <small class="d-block text-muted">Display on homepage</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <button type="submit" class="btn btn-submit w-100 mb-2" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Create Category
                    </button>
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
    // Auto-generate slug from title
    $('#title').on('input', function() {
        const title = $(this).val();
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        $('#slug').val(slug);
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

    // Form submission
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        // Disable submit button
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating...');

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: '{{ route("admin.categories.store") }}',
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
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form and fix the errors.'
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
