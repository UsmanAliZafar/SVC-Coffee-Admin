@extends('admin.layouts.app')

@section('title', 'View Page - ' . $page->title)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $page->title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($page->title, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
            @if(auth('admin')->user()->hasPermission('content.update'))
            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Edit Page
            </a>
            @endif
        </div>
    </div>

    {{-- Quick Actions Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        {{-- Status Badge --}}
                        <span class="badge {{ $page->status_badge_class }} fs-6">
                            @if($page->status == 'published')
                                <i class="bi bi-check-circle me-1"></i>
                            @elseif($page->status == 'draft')
                                <i class="bi bi-pencil-square me-1"></i>
                            @else
                                <i class="bi bi-archive me-1"></i>
                            @endif
                            {{ $page->status_label }}
                        </span>

                        {{-- Visibility Badge --}}
                        @if($page->visibility == 'public')
                        <span class="badge bg-info fs-6">
                            <i class="bi bi-eye me-1"></i>Public
                        </span>
                        @else
                        <span class="badge bg-secondary fs-6">
                            <i class="bi bi-eye-slash me-1"></i>Private
                        </span>
                        @endif

                        {{-- Navigation Badges --}}
                        @if($page->show_in_header)
                        <span class="badge bg-primary" title="Shown in Header Menu">
                            <i class="bi bi-layout-text-window-reverse me-1"></i>Header
                        </span>
                        @endif
                        @if($page->show_in_footer)
                        <span class="badge bg-dark" title="Shown in Footer Menu">
                            <i class="bi bi-layout-text-window me-1"></i>Footer
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        {{-- Quick Actions --}}
                        @if(auth('admin')->user()->hasPermission('content.update'))
                            @if($page->status == 'draft')
                            <form action="{{ route('admin.pages.publish', $page) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Publish
                                </button>
                            </form>
                            @elseif($page->status == 'published')
                            <form action="{{ route('admin.pages.unpublish', $page) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square me-1"></i>Unpublish
                                </button>
                            </form>
                            @endif

                            @if($page->status != 'archived')
                            <form action="{{ route('admin.pages.archive', $page) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-archive me-1"></i>Archive
                                </button>
                            </form>
                            @endif
                        @endif

                        @if(auth('admin')->user()->hasPermission('content.create'))
                        <form action="{{ route('admin.pages.duplicate', $page) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-info">
                                <i class="bi bi-copy me-1"></i>Duplicate
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('admin.pages.preview', $page) }}"
                           class="btn btn-sm btn-outline-primary"
                           target="_blank">
                            <i class="bi bi-eye me-1"></i>Preview
                        </a>

                        @if(auth('admin')->user()->hasPermission('content.delete'))
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="confirmDelete()">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Content Area --}}
        <div class="col-lg-8">
            {{-- Featured Image --}}
            @if($page->featured_image)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <img src="{{ Storage::url($page->featured_image) }}"
                         alt="{{ $page->featured_image_alt ?? $page->title }}"
                         class="img-fluid w-100 rounded">
                    @if($page->featured_image_alt)
                    <div class="p-3 bg-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Alt Text:</strong> {{ $page->featured_image_alt }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Page Content</h5>
                </div>
                <div class="card-body">
                    @if($page->excerpt)
                    <div class="alert alert-light border-start border-primary border-4 mb-4">
                        <strong>Excerpt:</strong>
                        <p class="mb-0 mt-2">{{ $page->excerpt }}</p>
                    </div>
                    @endif

                    <div class="page-content">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>

            {{-- SEO Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-search me-2"></i>SEO Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small mb-1">Meta Title</label>
                            <div class="p-3 bg-light rounded">
                                {{ $page->meta_title ?: $page->title }}
                            </div>
                            <small class="text-muted">
                                Length: {{ strlen($page->meta_title ?: $page->title) }} characters
                            </small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small mb-1">Meta Description</label>
                            <div class="p-3 bg-light rounded">
                                {{ $page->meta_description ?: 'Not set' }}
                            </div>
                            @if($page->meta_description)
                            <small class="text-muted">
                                Length: {{ strlen($page->meta_description) }} characters
                            </small>
                            @endif
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Meta Keywords</label>
                            <div class="p-3 bg-light rounded">
                                @if($page->meta_keywords)
                                    @foreach(explode(',', $page->meta_keywords) as $keyword)
                                        <span class="badge bg-secondary me-1 mb-1">{{ trim($keyword) }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- SEO Preview --}}
                    <div class="mt-4">
                        <label class="form-label text-muted small mb-2">Search Engine Preview</label>
                        <div class="seo-preview p-3 border rounded bg-white">
                            <div class="seo-title text-primary mb-1" style="font-size: 20px; font-weight: 400;">
                                {{ $page->meta_title ?: $page->title }}
                            </div>
                            <div class="seo-url text-success mb-2" style="font-size: 14px;">
                                {{ url('/page/' . $page->slug) }}
                            </div>
                            <div class="seo-description text-muted" style="font-size: 14px;">
                                {{ $page->meta_description ?: Str::limit(strip_tags($page->content), 160) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advanced Settings --}}
            @if($page->custom_css_class || $page->custom_css || $page->custom_js)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-code-slash me-2"></i>Advanced Settings</h5>
                </div>
                <div class="card-body">
                    @if($page->custom_css_class)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Custom CSS Class</label>
                        <div class="p-3 bg-light rounded font-monospace">
                            {{ $page->custom_css_class }}
                        </div>
                    </div>
                    @endif

                    @if($page->custom_css)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Custom CSS</label>
                        <pre class="p-3 bg-dark text-light rounded font-monospace" style="max-height: 300px; overflow-y: auto;"><code>{{ $page->custom_css }}</code></pre>
                    </div>
                    @endif

                    @if($page->custom_js)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Custom JavaScript</label>
                        <pre class="p-3 bg-dark text-light rounded font-monospace" style="max-height: 300px; overflow-y: auto;"><code>{{ $page->custom_js }}</code></pre>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Child Pages --}}
            @if($page->children->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Child Pages ({{ $page->children->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($page->children as $child)
                        <a href="{{ route('admin.pages.show', $child) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-arrow-return-right me-2 text-muted"></i>
                                {{ $child->title }}
                                <span class="badge {{ $child->status_badge_class }} ms-2">
                                    {{ $child->status_label }}
                                </span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Page Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Page Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 40%;">
                                    <i class="bi bi-link-45deg me-1"></i>Slug:
                                </td>
                                <td>
                                    <code>{{ $page->slug }}</code>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-layout-text-window me-1"></i>Template:
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ ucwords(str_replace('-', ' ', $page->template)) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-sort-numeric-down me-1"></i>Display Order:
                                </td>
                                <td>{{ $page->display_order }}</td>
                            </tr>
                            @if($page->parent)
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-diagram-3 me-1"></i>Parent Page:
                                </td>
                                <td>
                                    <a href="{{ route('admin.pages.show', $page->parent) }}" class="text-decoration-none">
                                        {{ $page->parent->title }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($page->menu_label)
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-menu-button-wide me-1"></i>Menu Label:
                                </td>
                                <td>{{ $page->menu_label }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Publishing Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Publishing</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 40%;">
                                    <i class="bi bi-calendar-plus me-1"></i>Created:
                                </td>
                                <td>
                                    <div>{{ $page->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $page->created_at->format('h:i A') }}</small>
                                </td>
                            </tr>
                            @if($page->creator)
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-person me-1"></i>Created By:
                                </td>
                                <td>{{ $page->creator->name }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>Updated:
                                </td>
                                <td>
                                    <div>{{ $page->updated_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $page->updated_at->format('h:i A') }}</small>
                                </td>
                            </tr>
                            @if($page->updater)
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-person-check me-1"></i>Updated By:
                                </td>
                                <td>{{ $page->updater->name }}</td>
                            </tr>
                            @endif
                            @if($page->published_at)
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>Published:
                                </td>
                                <td>
                                    <div>{{ $page->published_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $page->published_at->format('h:i A') }}</small>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Navigation Settings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-menu-button-wide me-2"></i>Navigation</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <i class="bi bi-layout-text-window-reverse me-2"></i>Header Menu
                        </div>
                        <div>
                            @if($page->show_in_header)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Enabled
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Disabled
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="bi bi-layout-text-window me-2"></i>Footer Menu
                        </div>
                        <div>
                            @if($page->show_in_footer)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Enabled
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Disabled
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page URL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-globe me-2"></i>Page URL</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text"
                               class="form-control font-monospace"
                               id="pageUrl"
                               value="{{ url('/page/' . $page->slug) }}"
                               readonly>
                        <button class="btn btn-outline-secondary"
                                type="button"
                                onclick="copyUrl()"
                                title="Copy URL">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <a href="{{ url('/page/' . $page->slug) }}"
                           class="btn btn-outline-primary"
                           target="_blank"
                           title="Open in new tab">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Frontend URL for this page
                    </small>
                </div>
            </div>

            {{-- Breadcrumbs --}}
            @if($page->parent)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-signpost me-2"></i>Breadcrumbs</h5>
                </div>
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-light p-3 rounded">
                            @foreach($page->getBreadcrumbs() as $breadcrumb)
                                @if($loop->last)
                                    <li class="breadcrumb-item active" aria-current="page">
                                        {{ $breadcrumb['title'] }}
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.pages.show', \App\Models\Page::where('slug', $breadcrumb['slug'])->first()) }}">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
            @endif

            {{-- Statistics --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-0">{{ $page->children->count() }}</h3>
                                <small class="text-muted">Child Pages</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-0">{{ strlen(strip_tags($page->content)) }}</h3>
                                <small class="text-muted">Characters</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-0">{{ str_word_count(strip_tags($page->content)) }}</h3>
                                <small class="text-muted">Words</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-0">{{ $page->updated_at->diffForHumans() }}</h3>
                                <small class="text-muted">Last Updated</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Form (Hidden) --}}
<form id="deleteForm" action="{{ route('admin.pages.destroy', $page) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<style>
    .page-content {
        font-size: 1rem;
        line-height: 1.8;
        color: #2c3e50;
    }

    .page-content h1, .page-content h2, .page-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        color: #5B914C;
    }

    .page-content p {
        margin-bottom: 1rem;
    }

    .page-content ul, .page-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }

    .page-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .page-content blockquote {
        border-left: 4px solid #5B914C;
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
        color: #6c757d;
    }

    .badge {
        font-weight: 500;
    }

    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.9rem;
    }

    .seo-preview {
        font-family: Arial, sans-serif;
    }

    pre code {
        display: block;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .table td {
        vertical-align: middle;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    // Copy URL to clipboard
    function copyUrl() {
        const urlInput = document.getElementById('pageUrl');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(urlInput.value).then(function() {
            // Show success message
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-secondary');

            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }, function(err) {
            alert('Failed to copy URL');
        });
    }

    // Confirm delete
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this page?\n\nTitle: {{ $page->title }}\n\nThis action cannot be undone.')) {
            @if($page->hasChildren())
                alert('Cannot delete this page because it has child pages. Please delete or reassign the child pages first.');
            @else
                document.getElementById('deleteForm').submit();
            @endif
        }
    }
</script>
@endpush
