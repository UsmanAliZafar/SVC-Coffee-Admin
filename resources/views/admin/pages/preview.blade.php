<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    {{-- SEO Meta Tags --}}
    <title>{{ $page->meta_title ?: $page->title }}</title>
    <meta name="description" content="{{ $page->meta_description ?: Str::limit(strip_tags($page->content), 160) }}">
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="{{ $page->meta_title ?: $page->title }}">
    <meta property="og:description" content="{{ $page->meta_description ?: Str::limit(strip_tags($page->content), 160) }}">
    @if($page->featured_image)
        <meta property="og:image" content="{{ Storage::url($page->featured_image) }}">
    @endif
    <meta property="og:type" content="website">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    {{-- Custom Page CSS --}}
    @if($page->custom_css)
    <style>
        {{ $page->custom_css }}
    </style>
    @endif

    <style>
        :root {
            --primary-color: #5B914C;
            --secondary-color: #4a7a3d;
            --text-color: #2c3e50;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-color);
            line-height: 1.8;
        }

        /* Preview Mode Banner */
        .preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }
            to {
                transform: translateY(0);
            }
        }

        .preview-banner .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Page Content Wrapper */
        .page-wrapper {
            margin-top: 70px; /* Space for preview banner */
            min-height: calc(100vh - 70px);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .page-header .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .page-header .breadcrumb-item {
            color: rgba(255, 255, 255, 0.8);
        }

        .page-header .breadcrumb-item.active {
            color: white;
        }

        .page-header .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.6);
        }

        .page-header .breadcrumb a {
            color: white;
            text-decoration: none;
        }

        .page-header .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Featured Image */
        .featured-image-wrapper {
            margin-bottom: 40px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .featured-image-wrapper img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Page Content Styling */
        .page-content {
            font-size: 1.1rem;
            line-height: 1.9;
        }

        .page-content h1,
        .page-content h2,
        .page-content h3,
        .page-content h4,
        .page-content h5,
        .page-content h6 {
            color: var(--primary-color);
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .page-content h1 {
            font-size: 2.2rem;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .page-content h2 {
            font-size: 1.8rem;
        }

        .page-content h3 {
            font-size: 1.5rem;
        }

        .page-content p {
            margin-bottom: 1.5rem;
        }

        .page-content ul,
        .page-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2.5rem;
        }

        .page-content li {
            margin-bottom: 0.5rem;
        }

        .page-content a {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .page-content a:hover {
            color: var(--secondary-color);
        }

        .page-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 2rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .page-content blockquote {
            border-left: 4px solid var(--primary-color);
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: #6c757d;
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 4px;
        }

        .page-content table {
            width: 100%;
            margin: 2rem 0;
            border-collapse: collapse;
        }

        .page-content table th,
        .page-content table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .page-content table th {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .page-content table tr:nth-child(even) {
            background: var(--light-bg);
        }

        .page-content code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }

        .page-content pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 1.5rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 2rem 0;
        }

        .page-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        /* Excerpt Styling */
        .page-excerpt {
            background: var(--light-bg);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            font-style: italic;
            border-radius: 4px;
        }

        /* Page Footer */
        .page-footer {
            background: var(--light-bg);
            padding: 40px 0;
            margin-top: 60px;
            border-top: 3px solid var(--primary-color);
        }

        .page-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            color: #6c757d;
            font-size: 0.95rem;
        }

        .page-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Template Specific Styles */
        .template-full-width .container {
            max-width: 100%;
        }

        .template-sidebar-left .content-area {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
        }

        .template-sidebar-right .content-area {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
        }

        .sidebar {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .page-content {
                font-size: 1rem;
            }

            .template-sidebar-left .content-area,
            .template-sidebar-right .content-area {
                grid-template-columns: 1fr;
            }

            .preview-banner .container {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }

        /* Print Styles */
        @media print {
            .preview-banner,
            .page-footer {
                display: none;
            }

            .page-wrapper {
                margin-top: 0;
            }
        }
    </style>
</head>
<body class="{{ $page->custom_css_class }} template-{{ $page->template }}">

    {{-- Preview Mode Banner --}}
    <div class="preview-banner">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-eye fs-4"></i>
                <div>
                    <div class="preview-badge">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        PREVIEW MODE
                    </div>
                </div>
                <div class="text-white-50 small d-none d-md-block">
                    This is a preview of how your page will appear. Changes are not visible to visitors.
                </div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-light text-dark">
                    Status: {{ $page->status_label }}
                </span>
                @if($page->visibility == 'private')
                <span class="badge bg-warning">
                    <i class="bi bi-eye-slash me-1"></i>Private
                </span>
                @endif
                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <button onclick="window.close()" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Page Wrapper --}}
    <div class="page-wrapper">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="container">
                {{-- Breadcrumbs --}}
                @if($page->parent)
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        @foreach($page->getBreadcrumbs() as $breadcrumb)
                            @if(!$loop->last)
                                <li class="breadcrumb-item">
                                    <a href="#">{{ $breadcrumb['title'] }}</a>
                                </li>
                            @endif
                        @endforeach
                        <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                    </ol>
                </nav>
                @endif

                {{-- Page Title --}}
                <h1>{{ $page->title }}</h1>

                {{-- Page Meta Info --}}
                <div class="page-meta">
                    @if($page->published_at)
                    <div class="page-meta-item">
                        <i class="bi bi-calendar-event"></i>
                        <span>Published: {{ $page->published_at->format('M d, Y') }}</span>
                    </div>
                    @endif
                    @if($page->updated_at)
                    <div class="page-meta-item">
                        <i class="bi bi-clock-history"></i>
                        <span>Updated: {{ $page->updated_at->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <main class="container my-5">

            @if($page->template == 'sidebar-left' || $page->template == 'sidebar-right')
                <div class="content-area">
                    @if($page->template == 'sidebar-left')
                        {{-- Sidebar Left --}}
                        <aside class="sidebar">
                            <h5>Navigation</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-house-door me-2"></i>Home
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none active">
                                        <i class="bi bi-file-earmark me-2"></i>{{ $page->title }}
                                    </a>
                                </li>
                            </ul>
                        </aside>
                    @endif

                    <div>
                        {{-- Featured Image --}}
                        @if($page->featured_image)
                        <div class="featured-image-wrapper">
                            <img src="{{ Storage::url($page->featured_image) }}"
                                 alt="{{ $page->featured_image_alt ?? $page->title }}"
                                 loading="lazy">
                            @if($page->featured_image_alt)
                            <div class="p-3 bg-light text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ $page->featured_image_alt }}
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Excerpt --}}
                        @if($page->excerpt)
                        <div class="page-excerpt">
                            <i class="bi bi-quote me-2"></i>{{ $page->excerpt }}
                        </div>
                        @endif

                        {{-- Main Content --}}
                        <div class="page-content">
                            {!! $page->content !!}
                        </div>

                        {{-- Child Pages (if any) --}}
                        @if($page->children->count() > 0)
                        <div class="child-pages mt-5">
                            <h3><i class="bi bi-diagram-3 me-2"></i>Related Pages</h3>
                            <div class="row">
                                @foreach($page->children as $child)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="#" class="text-decoration-none">{{ $child->title }}</a>
                                            </h5>
                                            @if($child->excerpt)
                                            <p class="card-text text-muted">{{ $child->excerpt }}</p>
                                            @endif
                                            <span class="badge {{ $child->status_badge_class }}">
                                                {{ $child->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($page->template == 'sidebar-right')
                        {{-- Sidebar Right --}}
                        <aside class="sidebar">
                            <h5>Quick Links</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-info-circle me-2"></i>About Us
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="#" class="text-decoration-none">
                                        <i class="bi bi-envelope me-2"></i>Contact
                                    </a>
                                </li>
                            </ul>
                        </aside>
                    @endif
                </div>
            @else
                {{-- Default & Full Width & Landing Templates --}}

                {{-- Featured Image --}}
                @if($page->featured_image)
                <div class="featured-image-wrapper">
                    <img src="{{ Storage::url($page->featured_image) }}"
                         alt="{{ $page->featured_image_alt ?? $page->title }}"
                         loading="lazy">
                    @if($page->featured_image_alt)
                    <div class="p-3 bg-light text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ $page->featured_image_alt }}
                    </div>
                    @endif
                </div>
                @endif

                {{-- Excerpt --}}
                @if($page->excerpt)
                <div class="page-excerpt">
                    <i class="bi bi-quote me-2"></i>{{ $page->excerpt }}
                </div>
                @endif

                {{-- Main Content --}}
                <div class="page-content">
                    {!! $page->content !!}
                </div>

                {{-- Child Pages (if any) --}}
                @if($page->children->count() > 0)
                <div class="child-pages mt-5">
                    <h3><i class="bi bi-diagram-3 me-2"></i>Related Pages</h3>
                    <div class="row">
                        @foreach($page->children as $child)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="#" class="text-decoration-none">{{ $child->title }}</a>
                                    </h5>
                                    @if($child->excerpt)
                                    <p class="card-text text-muted">{{ $child->excerpt }}</p>
                                    @endif
                                    <span class="badge {{ $child->status_badge_class }}">
                                        {{ $child->status_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif

        </main>

        {{-- Page Footer --}}
        <footer class="page-footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h5>{{ $page->title }}</h5>
                        @if($page->excerpt)
                        <p class="text-muted mb-0">{{ Str::limit($page->excerpt, 100) }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="page-meta">
                            <div class="page-meta-item">
                                <i class="bi bi-eye"></i>
                                <span>{{ $page->visibility == 'public' ? 'Public' : 'Private' }} Page</span>
                            </div>
                            <div class="page-meta-item">
                                <i class="bi bi-layout-text-window"></i>
                                <span>Template: {{ ucwords(str_replace('-', ' ', $page->template)) }}</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Page ID: <code>{{ $page->id }}</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Custom Page JavaScript --}}
    @if($page->custom_js)
    <script>
        {{ $page->custom_js }}
    </script>
    @endif

    {{-- Preview Mode JavaScript --}}
    <script>
        // Add preview watermark
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent accidental navigation
            document.querySelectorAll('a').forEach(link => {
                if (!link.href.includes('admin/pages')) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        alert('This is a preview. Links are disabled.');
                    });
                }
            });

            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + E = Edit
                if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                    e.preventDefault();
                    window.location.href = '{{ route('admin.pages.edit', $page) }}';
                }

                // Escape = Close
                if (e.key === 'Escape') {
                    window.close();
                }
            });

            console.log('📄 Page Preview Mode Active');
            console.log('Page ID:', '{{ $page->id }}');
            console.log('Status:', '{{ $page->status }}');
            console.log('Template:', '{{ $page->template }}');
            console.log('Keyboard Shortcuts:');
            console.log('  - Ctrl/Cmd + E: Edit page');
            console.log('  - Escape: Close preview');
        });
    </script>
</body>
</html>
