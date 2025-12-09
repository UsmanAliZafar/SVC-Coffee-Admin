@extends('admin.layouts.app')

@section('title', 'Edit Page - ' . $page->title)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Page</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pages.show', $page) }}">{{ Str::limit($page->title, 20) }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pages.show', $page) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-2"></i>View Page
            </a>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    {{-- Status Info Bar --}}
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <strong>Editing:</strong> {{ $page->title }}
                <span class="badge {{ $page->status_badge_class }} ms-2">{{ $page->status_label }}</span>
                @if($page->parent)
                    <span class="badge bg-light text-dark ms-2">
                        <i class="bi bi-arrow-return-right me-1"></i>Child of: {{ $page->parent->title }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data" id="pageForm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Main Content Area --}}
            <div class="col-lg-8">
                {{-- Basic Information Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Page Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $page->title) }}"
                                   placeholder="Enter page title"
                                   required
                                   autofocus>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This will be the main heading of your page.</small>
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label for="slug" class="form-label">
                                URL Slug
                                <span class="text-muted small">(Optional - Auto-generated from title)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">{{ url('/') }}/page/</span>
                                <input type="text"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       id="slug"
                                       name="slug"
                                       value="{{ old('slug', $page->slug) }}"
                                       placeholder="about-us">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Only lowercase letters, numbers, and hyphens allowed.</small>
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                      id="excerpt"
                                      name="excerpt"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Brief description of the page (max 500 characters)">{{ old('excerpt', $page->excerpt) }}</textarea>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Short summary for search results and previews.</small>
                                <small class="text-muted"><span id="excerptCount">{{ strlen($page->excerpt ?? '') }}</span>/500</small>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                Page Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content"
                                      name="content"
                                      rows="15"
                                      required>{{ old('content', $page->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Main content of your page. You can use the rich text editor above.</small>
                        </div>
                    </div>
                </div>

                {{-- SEO Settings Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-search me-2"></i>SEO Settings</h5>
                            <button type="button" class="btn btn-sm btn-link" data-bs-toggle="collapse" data-bs-target="#seoSettings">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="seoSettings">
                        <div class="card-body">
                            {{-- Meta Title --}}
                            <div class="mb-3">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text"
                                       class="form-control @error('meta_title') is-invalid @enderror"
                                       id="meta_title"
                                       name="meta_title"
                                       value="{{ old('meta_title', $page->meta_title) }}"
                                       maxlength="255"
                                       placeholder="Leave empty to use page title">
                                @error('meta_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Recommended: 50-60 characters. <span id="metaTitleCount">{{ strlen($page->meta_title ?? '') }}</span>/255</small>
                            </div>

                            {{-- Meta Description --}}
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                          id="meta_description"
                                          name="meta_description"
                                          rows="3"
                                          maxlength="500"
                                          placeholder="Brief description for search engines">{{ old('meta_description', $page->meta_description) }}</textarea>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Recommended: 150-160 characters. <span id="metaDescCount">{{ strlen($page->meta_description ?? '') }}</span>/500</small>
                            </div>

                            {{-- Meta Keywords --}}
                            <div class="mb-3">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <input type="text"
                                       class="form-control @error('meta_keywords') is-invalid @enderror"
                                       id="meta_keywords"
                                       name="meta_keywords"
                                       value="{{ old('meta_keywords', $page->meta_keywords) }}"
                                       maxlength="500"
                                       placeholder="coffee, machines, brewing, beans">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Comma-separated keywords (optional).</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Advanced Settings Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-code-slash me-2"></i>Advanced Settings</h5>
                            <button type="button" class="btn btn-sm btn-link" data-bs-toggle="collapse" data-bs-target="#advancedSettings">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse {{ $page->custom_css_class || $page->custom_css || $page->custom_js ? 'show' : '' }}" id="advancedSettings">
                        <div class="card-body">
                            {{-- Custom CSS Class --}}
                            <div class="mb-3">
                                <label for="custom_css_class" class="form-label">Custom CSS Class</label>
                                <input type="text"
                                       class="form-control @error('custom_css_class') is-invalid @enderror"
                                       id="custom_css_class"
                                       name="custom_css_class"
                                       value="{{ old('custom_css_class', $page->custom_css_class) }}"
                                       placeholder="custom-class-name">
                                @error('custom_css_class')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Add custom CSS class to page wrapper.</small>
                            </div>

                            {{-- Custom CSS --}}
                            <div class="mb-3">
                                <label for="custom_css" class="form-label">Custom CSS</label>
                                <textarea class="form-control font-monospace @error('custom_css') is-invalid @enderror"
                                          id="custom_css"
                                          name="custom_css"
                                          rows="5"
                                          placeholder=".custom-class { color: #5B914C; }">{{ old('custom_css', $page->custom_css) }}</textarea>
                                @error('custom_css')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Custom CSS for this page only.</small>
                            </div>

                            {{-- Custom JS --}}
                            <div class="mb-3">
                                <label for="custom_js" class="form-label">Custom JavaScript</label>
                                <textarea class="form-control font-monospace @error('custom_js') is-invalid @enderror"
                                          id="custom_js"
                                          name="custom_js"
                                          rows="5"
                                          placeholder="console.log('Custom JS');">{{ old('custom_js', $page->custom_js) }}</textarea>
                                @error('custom_js')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Custom JavaScript for this page only.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Publish Settings Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Publish Settings</h5>
                    </div>
                    <div class="card-body">
                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>
                                    Published
                                </option>
                                <option value="archived" {{ old('status', $page->status) == 'archived' ? 'selected' : '' }}>
                                    Archived
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Visibility --}}
                        <div class="mb-3">
                            <label for="visibility" class="form-label">
                                Visibility <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('visibility') is-invalid @enderror"
                                    id="visibility"
                                    name="visibility"
                                    required>
                                <option value="public" {{ old('visibility', $page->visibility) == 'public' ? 'selected' : '' }}>
                                    Public
                                </option>
                                <option value="private" {{ old('visibility', $page->visibility) == 'private' ? 'selected' : '' }}>
                                    Private
                                </option>
                            </select>
                            @error('visibility')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Published Date --}}
                        <div class="mb-3" id="publishedDateWrapper" style="display: {{ old('status', $page->status) == 'published' ? 'block' : 'none' }};">
                            <label for="published_at" class="form-label">Publish Date</label>
                            <input type="datetime-local"
                                   class="form-control @error('published_at') is-invalid @enderror"
                                   id="published_at"
                                   name="published_at"
                                   value="{{ old('published_at', $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Schedule page publishing.</small>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success" name="action" value="save">
                                <i class="bi bi-check-circle me-2"></i>Update Page
                            </button>
                            <button type="submit" class="btn btn-primary" name="action" value="save_continue">
                                <i class="bi bi-arrow-repeat me-2"></i>Update & Continue Editing
                            </button>
                            <a href="{{ route('admin.pages.show', $page) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>

                        {{-- Last Updated Info --}}
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="bi bi-clock-history me-1"></i>
                                Last updated: {{ $page->updated_at->diffForHumans() }}
                                @if($page->updater)
                                    by {{ $page->updater->name }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Featured Image Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Featured Image</h5>
                    </div>
                    <div class="card-body">
                        {{-- Current Image --}}
                        @if($page->featured_image)
                        <div class="mb-3" id="currentImageWrapper">
                            <label class="form-label small text-muted">Current Image</label>
                            <div class="position-relative">
                                <img src="{{ Storage::url($page->featured_image) }}"
                                     alt="{{ $page->featured_image_alt ?? $page->title }}"
                                     class="img-fluid rounded w-100"
                                     id="currentImage"
                                     style="max-height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <div class="form-check">
                                        <input class="form-check-input bg-danger border-danger"
                                               type="checkbox"
                                               id="remove_image"
                                               name="remove_image"
                                               value="1"
                                               onchange="toggleImageRemove(this)">
                                        <label class="form-check-label text-white bg-danger px-2 rounded" for="remove_image">
                                            <small>Remove</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Image Preview (for new upload) --}}
                        <div class="mb-3 text-center" id="imagePreviewWrapper" style="display: none;">
                            <label class="form-label small text-muted">New Image Preview</label>
                            <img id="imagePreview" src="" class="img-fluid rounded" style="max-height: 200px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeNewImage()">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>

                        {{-- File Upload --}}
                        <div class="mb-3" id="imageUploadWrapper">
                            <label for="featured_image" class="form-label">
                                {{ $page->featured_image ? 'Change Image' : 'Upload Image' }}
                            </label>
                            <input type="file"
                                   class="form-control @error('featured_image') is-invalid @enderror"
                                   id="featured_image"
                                   name="featured_image"
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   onchange="previewImage(this)">
                            @error('featured_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                Accepted: JPEG, PNG, WEBP<br>
                                Max size: 2MB
                            </small>
                        </div>

                        {{-- Image Alt Text --}}
                        <div class="mb-3">
                            <label for="featured_image_alt" class="form-label">Image Alt Text</label>
                            <input type="text"
                                   class="form-control @error('featured_image_alt') is-invalid @enderror"
                                   id="featured_image_alt"
                                   name="featured_image_alt"
                                   value="{{ old('featured_image_alt', $page->featured_image_alt) }}"
                                   placeholder="Describe the image">
                            @error('featured_image_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Important for SEO and accessibility.</small>
                        </div>
                    </div>
                </div>

                {{-- Template Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-layout-text-window me-2"></i>Template</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="template" class="form-label">
                                Page Template <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('template') is-invalid @enderror"
                                    id="template"
                                    name="template"
                                    required>
                                @foreach($templates as $key => $label)
                                    <option value="{{ $key }}" {{ old('template', $page->template) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Hierarchy Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Page Hierarchy</h5>
                    </div>
                    <div class="card-body">
                        {{-- Parent Page --}}
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Page</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror"
                                    id="parent_id"
                                    name="parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach($parentPages as $parent)
                                    <option value="{{ $parent->id }}"
                                            {{ old('parent_id', $page->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Create a sub-page under another page.</small>
                            @if($page->hasChildren())
                            <div class="alert alert-warning mt-2 p-2 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                This page has {{ $page->children->count() }} child page(s).
                            </div>
                            @endif
                        </div>

                        {{-- Display Order --}}
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number"
                                   class="form-control @error('display_order') is-invalid @enderror"
                                   id="display_order"
                                   name="display_order"
                                   value="{{ old('display_order', $page->display_order) }}"
                                   min="0">
                            @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Lower numbers appear first.</small>
                        </div>
                    </div>
                </div>

                {{-- Navigation Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-menu-button-wide me-2"></i>Navigation</h5>
                    </div>
                    <div class="card-body">
                        {{-- Show in Header --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="show_in_header"
                                   name="show_in_header"
                                   value="1"
                                   {{ old('show_in_header', $page->show_in_header) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_header">
                                Show in Header Menu
                            </label>
                        </div>

                        {{-- Show in Footer --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="show_in_footer"
                                   name="show_in_footer"
                                   value="1"
                                   {{ old('show_in_footer', $page->show_in_footer) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_footer">
                                Show in Footer Menu
                            </label>
                        </div>

                        {{-- Menu Label --}}
                        <div class="mb-3">
                            <label for="menu_label" class="form-label">Custom Menu Label</label>
                            <select class="form-control @error('menu_label') is-invalid @enderror"
                                    id="menu_label"
                                    name="menu_label">
                                <option value="">Leave empty to use page title</option>
                                <option value="Services" {{ old('menu_label', $page->menu_label) == 'Services' ? 'selected' : '' }}>Services</option>
                                <option value="Support" {{ old('menu_label', $page->menu_label) == 'Support' ? 'selected' : '' }}>Support</option>
                                <option value="Company" {{ old('menu_label', $page->menu_label) == 'Company' ? 'selected' : '' }}>Company</option>
                                <option value="Legal" {{ old('menu_label', $page->menu_label) == 'Legal' ? 'selected' : '' }}>Legal</option>
                            </select>
                            @error('menu_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Custom text for menu links.</small>
                        </div>
                    </div>
                </div>

                {{-- Page Info Card --}}
                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Page Information</h6>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <strong>Created:</strong> {{ $page->created_at->format('M d, Y') }}
                                @if($page->creator)
                                    by {{ $page->creator->name }}
                                @endif
                            </li>
                            <li class="mb-2">
                                <strong>Page ID:</strong> <code>{{ $page->id }}</code>
                            </li>
                            <li class="mb-2">
                                <strong>Words:</strong> {{ str_word_count(strip_tags($page->content)) }}
                            </li>
                            @if($page->children->count() > 0)
                            <li>
                                <strong>Child Pages:</strong> {{ $page->children->count() }}
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{{-- Translation button --}}
@include('admin.components.translation-modal', [
    'module' => 'page',
    'itemId' => $page->id,
    'itemName' => $page->title
])
{{-- Translation button --}}
@endsection

@push('styles')
<style>
    .form-label {
        font-weight: 500;
        color: #2c3e50;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    .form-check-input:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .btn-success {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .btn-success:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }

    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.9rem;
    }

    #currentImage {
        border: 2px solid #dee2e6;
    }

    .position-relative .position-absolute {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.tiny.cloud/1/gi6hxeywu62rdkmcuajuwxtw2607xe85vdlppnr2859m4heb/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    // Initialize TinyMCE
    tinymce.init({
        selector: '#content',
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | code | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
        branding: false,
        promotion: false
    });

    // Don't auto-generate slug on edit (user might have customized it)
    let slugManuallyEdited = {{ $page->slug ? 'true' : 'false' }};

    document.getElementById('slug').addEventListener('input', function() {
        slugManuallyEdited = true;
    });

    document.getElementById('title').addEventListener('input', function() {
        if (!slugManuallyEdited) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('slug').value = slug;
        }
    });

    // Show/hide published date based on status
    document.getElementById('status').addEventListener('change', function() {
        const publishedDateWrapper = document.getElementById('publishedDateWrapper');
        if (this.value === 'published') {
            publishedDateWrapper.style.display = 'block';
        } else {
            publishedDateWrapper.style.display = 'none';
        }
    });

    // Character counters
    const excerptField = document.getElementById('excerpt');
    const excerptCount = document.getElementById('excerptCount');
    if (excerptField) {
        excerptField.addEventListener('input', function() {
            excerptCount.textContent = this.value.length;
        });
    }

    const metaTitleField = document.getElementById('meta_title');
    const metaTitleCount = document.getElementById('metaTitleCount');
    if (metaTitleField) {
        metaTitleField.addEventListener('input', function() {
            metaTitleCount.textContent = this.value.length;
        });
    }

    const metaDescField = document.getElementById('meta_description');
    const metaDescCount = document.getElementById('metaDescCount');
    if (metaDescField) {
        metaDescField.addEventListener('input', function() {
            metaDescCount.textContent = this.value.length;
        });
    }

    // Image preview for new upload
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewWrapper').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Remove new image preview
    function removeNewImage() {
        document.getElementById('featured_image').value = '';
        document.getElementById('imagePreview').src = '';
        document.getElementById('imagePreviewWrapper').style.display = 'none';
    }

    // Toggle image removal
    function toggleImageRemove(checkbox) {
        const currentImage = document.getElementById('currentImage');
        if (checkbox.checked) {
            currentImage.style.opacity = '0.3';
            currentImage.style.filter = 'grayscale(100%)';
        } else {
            currentImage.style.opacity = '1';
            currentImage.style.filter = 'none';
        }
    }

    // Form validation before submit
    document.getElementById('pageForm').addEventListener('submit', function(e) {
        // Sync TinyMCE content
        tinymce.triggerSave();

        // Check if content is empty
        const content = document.getElementById('content').value.trim();
        if (!content) {
            e.preventDefault();
            alert('Please add some content to the page.');
            return false;
        }
    });

    // Warn user about unsaved changes
    let formChanged = false;
    const form = document.getElementById('pageForm');
    const formElements = form.querySelectorAll('input, textarea, select');

    formElements.forEach(element => {
        element.addEventListener('change', function() {
            formChanged = true;
        });
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    form.addEventListener('submit', function() {
        formChanged = false;
    });
</script>
@endpush
