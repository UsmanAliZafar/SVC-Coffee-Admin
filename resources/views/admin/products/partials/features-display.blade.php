{{-- resources/views/admin/products/partials/features-display.blade.php --}}
{{-- Use this in your show.blade.php to display product features --}}

@if($product->hasFeatures())
<div class="card mb-4">
    <div class="card-header" style="background-color: #5B914C; color: white;">
        <h5 class="mb-0">
            <i class="bi bi-stars"></i> Product Features
            <span class="badge bg-white text-dark ms-2">{{ count($product->getFeatures()) }} Section(s)</span>
        </h5>
    </div>
    <div class="card-body">
        @foreach($product->getFeatures() as $index => $feature)
            <div class="feature-display-item mb-4 pb-4 {{ $loop->last ? '' : 'border-bottom' }}">
                {{-- Feature Header --}}
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <span class="badge me-2" style="background-color: {{ getFeatureColor($feature['type']) }};">
                            <i class="bi {{ getFeatureIcon($feature['type']) }}"></i>
                            {{ getFeatureTypeName($feature['type']) }}
                        </span>
                        <h6 class="mb-0 text-dark fw-bold">{{ $feature['label'] ?: 'Untitled' }}</h6>
                    </div>
                    <small class="text-muted">#{{ $index + 1 }}</small>
                </div>

                {{-- Feature Content --}}
                <div class="feature-content p-3" style="background-color: #f8f9fa; border-left: 4px solid {{ getFeatureColor($feature['type']) }}; border-radius: 6px;">

                    @if($feature['type'] === 'rich_text')
                        {{-- Rich Text Display --}}
                        <div class="rich-text-content">
                            {!! $feature['value'] !!}
                        </div>

                    @elseif($feature['type'] === 'multiline_text')
                        {{-- Multiline Text Display --}}
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $feature['value'] }}</p>

                    @elseif($feature['type'] === 'single_line')
                        {{-- Single Line Fields Display --}}
                        @if(is_array($feature['value']) && count($feature['value']) > 0)
                            <div class="row g-3">
                                @foreach($feature['value'] as $item)
                                    <div class="col-md-6">
                                        <div class="single-field-item p-2 bg-white border rounded">
                                            <small class="text-muted d-block mb-1">{{ $item['label'] ?: 'Field' }}</small>
                                            <strong class="d-block">{{ $item['value'] ?: '—' }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 fst-italic">No fields added</p>
                        @endif

                    @elseif($feature['type'] === 'links_list')
                        {{-- Links List Display --}}
                        @if(is_array($feature['value']) && count($feature['value']) > 0)
                            <ul class="list-unstyled mb-0">
                                @foreach($feature['value'] as $link)
                                    <li class="mb-2 p-2 bg-white border rounded">
                                        <i class="bi bi-link-45deg text-primary"></i>
                                        @if(!empty($link['url']))
                                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold">
                                                {{ $link['title'] ?: $link['url'] }}
                                                <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">{{ $link['title'] ?: 'Untitled Link' }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0 fst-italic">No links added</p>
                        @endif

                    @else
                        {{-- Unknown Type --}}
                        <p class="text-muted mb-0 fst-italic">Unknown feature type</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> No features added to this product yet.
</div>
@endif

@php
/**
 * Helper function to get feature type icon
 */
function getFeatureIcon($type) {
    return match($type) {
        'rich_text' => 'bi-file-richtext',
        'single_line' => 'bi-input-cursor-text',
        'multiline_text' => 'bi-textarea-t',
        'links_list' => 'bi-link-45deg',
        default => 'bi-star'
    };
}

/**
 * Helper function to get feature type name
 */
function getFeatureTypeName($type) {
    return match($type) {
        'rich_text' => 'Rich Text',
        'single_line' => 'Single Line Fields',
        'multiline_text' => 'Multiline Text',
        'links_list' => 'Links List',
        default => ucfirst(str_replace('_', ' ', $type))
    };
}

/**
 * Helper function to get feature color
 */
function getFeatureColor($type) {
    return match($type) {
        'rich_text' => '#0d6efd',      // Blue
        'single_line' => '#198754',    // Green
        'multiline_text' => '#0dcaf0', // Cyan
        'links_list' => '#ffc107',     // Yellow
        default => '#6c757d'           // Gray
    };
}
@endphp

<style>
    .feature-display-item {
        transition: all 0.3s ease;
    }

    .feature-display-item:hover {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem !important;
        margin: -0.5rem;
        margin-bottom: 1rem !important;
    }

    .feature-content {
        line-height: 1.6;
    }

    /* Rich Text Content Styling */
    .rich-text-content {
        line-height: 1.8;
    }

    .rich-text-content h1,
    .rich-text-content h2,
    .rich-text-content h3,
    .rich-text-content h4,
    .rich-text-content h5,
    .rich-text-content h6 {
        color: #5B914C;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }

    .rich-text-content h1 { font-size: 1.5rem; }
    .rich-text-content h2 { font-size: 1.3rem; }
    .rich-text-content h3 { font-size: 1.1rem; }

    .rich-text-content p {
        margin-bottom: 1rem;
    }

    .rich-text-content ul,
    .rich-text-content ol {
        padding-left: 2rem;
        margin-bottom: 1rem;
    }

    .rich-text-content li {
        margin-bottom: 0.5rem;
    }

    .rich-text-content a {
        color: #5B914C;
        text-decoration: underline;
        font-weight: 500;
    }

    .rich-text-content a:hover {
        color: #4a7a3d;
    }

    .rich-text-content blockquote {
        border-left: 4px solid #5B914C;
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
        color: #6c757d;
    }

    .rich-text-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: monospace;
        font-size: 0.9em;
    }

    .rich-text-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 6px;
        overflow-x: auto;
    }

    .rich-text-content img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        margin: 1rem 0;
    }

    .rich-text-content table {
        width: 100%;
        margin: 1rem 0;
        border-collapse: collapse;
    }

    .rich-text-content table th,
    .rich-text-content table td {
        border: 1px solid #dee2e6;
        padding: 0.5rem;
    }

    .rich-text-content table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    /* Single Field Items */
    .single-field-item {
        transition: all 0.2s ease;
    }

    .single-field-item:hover {
        background-color: #f8f9fa !important;
        border-color: #5B914C !important;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .single-field-item small {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .single-field-item strong {
        font-size: 0.95rem;
        color: #212529;
    }

    /* Links List Items */
    .list-unstyled li {
        transition: all 0.2s ease;
    }

    .list-unstyled li:hover {
        background-color: #f8f9fa !important;
        border-color: #5B914C !important;
        transform: translateX(5px);
    }

    .list-unstyled li a {
        color: #5B914C;
        font-weight: 500;
    }

    .list-unstyled li a:hover {
        color: #4a7a3d;
        text-decoration: underline;
    }

    /* Badge Styling */
    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    /* Empty State */
    .fst-italic {
        opacity: 0.7;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .single-field-item {
            margin-bottom: 0.5rem;
        }

        .feature-display-item:hover {
            margin: 0;
            padding: 0.5rem !important;
        }
    }
</style>
