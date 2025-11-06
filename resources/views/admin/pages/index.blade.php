@extends('admin.layouts.app')

@section('title', 'Manage Pages')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Pages Management</h1>
            <p class="text-muted mb-0">Manage dynamic content pages for your website</p>
        </div>
        @if(auth('admin')->user()->hasPermission('content.create'))
        <a href="{{ route('admin.pages.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-2"></i>Create New Page
        </a>
        @endif
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Pages</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-file-earmark fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Published</p>
                            <h3 class="mb-0 text-success">{{ $stats['published'] }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Drafts</p>
                            <h3 class="mb-0 text-warning">{{ $stats['draft'] }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-pencil-square fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Archived</p>
                            <h3 class="mb-0 text-secondary">{{ $stats['archived'] }}</h3>
                        </div>
                        <div class="text-secondary">
                            <i class="bi bi-archive fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters and Search --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.pages.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Search pages..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    {{-- Visibility Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Visibility</label>
                        <select name="visibility" class="form-select">
                            <option value="">All Visibility</option>
                            <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>

                    {{-- Parent/Child Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Hierarchy</label>
                        <select name="parent_filter" class="form-select">
                            <option value="">All Pages</option>
                            <option value="parent" {{ request('parent_filter') == 'parent' ? 'selected' : '' }}>Parent Only</option>
                            <option value="child" {{ request('parent_filter') == 'child' ? 'selected' : '' }}>Child Only</option>
                        </select>
                    </div>

                    {{-- Navigation Filter --}}
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Navigation</label>
                        <select name="navigation" class="form-select">
                            <option value="">All Navigation</option>
                            <option value="header" {{ request('navigation') == 'header' ? 'selected' : '' }}>In Header</option>
                            <option value="footer" {{ request('navigation') == 'footer' ? 'selected' : '' }}>In Footer</option>
                        </select>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-md-1">
                        <label class="form-label small text-muted d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>

                {{-- Active Filters Display --}}
                @if(request()->hasAny(['search', 'status', 'visibility', 'parent_filter', 'navigation']))
                <div class="mt-3 d-flex align-items-center">
                    <span class="text-muted small me-2">Active Filters:</span>
                    @if(request('search'))
                        <span class="badge bg-light text-dark me-2">Search: {{ request('search') }}</span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-light text-dark me-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('visibility'))
                        <span class="badge bg-light text-dark me-2">Visibility: {{ ucfirst(request('visibility')) }}</span>
                    @endif
                    @if(request('parent_filter'))
                        <span class="badge bg-light text-dark me-2">Type: {{ ucfirst(request('parent_filter')) }}</span>
                    @endif
                    @if(request('navigation'))
                        <span class="badge bg-light text-dark me-2">Navigation: {{ ucfirst(request('navigation')) }}</span>
                    @endif
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-link text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i>Clear All
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Pages Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Pages List</h5>
                    <small class="text-muted">Showing {{ $pages->firstItem() ?? 0 }} to {{ $pages->lastItem() ?? 0 }} of {{ $pages->total() }} pages</small>
                </div>
                @if(auth('admin')->user()->hasPermission('content.delete'))
                <button type="button"
                        class="btn btn-sm btn-danger"
                        id="bulkDeleteBtn"
                        style="display: none;"
                        onclick="confirmBulkDelete()">
                    <i class="bi bi-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)
                </button>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            @if($pages->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            @if(auth('admin')->user()->hasPermission('content.delete'))
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            @endif
                            <th>
                                <a href="{{ route('admin.pages.index', array_merge(request()->all(), ['sort_by' => 'title', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark">
                                    Title
                                    @if(request('sort_by') == 'title')
                                        <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Slug</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th>Visibility</th>
                            <th>Navigation</th>
                            <th>
                                <a href="{{ route('admin.pages.index', array_merge(request()->all(), ['sort_by' => 'created_at', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark">
                                    Created
                                    @if(request('sort_by') == 'created_at' || !request('sort_by'))
                                        <i class="bi bi-arrow-{{ request('sort_order') == 'desc' || !request('sort_order') ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 180px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pages as $page)
                        <tr>
                            @if(auth('admin')->user()->hasPermission('content.delete'))
                            <td>
                                <input type="checkbox"
                                       class="form-check-input page-checkbox"
                                       value="{{ $page->id }}"
                                       data-page-title="{{ $page->title }}">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($page->featured_image)
                                    <img src="{{ Storage::url($page->featured_image) }}"
                                         alt="{{ $page->title }}"
                                         class="rounded me-2"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-file-earmark text-muted"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ Str::limit($page->title, 40) }}</div>
                                        @if($page->excerpt)
                                        <small class="text-muted">{{ Str::limit($page->excerpt, 60) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="small">{{ $page->slug }}</code>
                            </td>
                            <td>
                                @if($page->parent)
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-arrow-return-right me-1"></i>{{ $page->parent->title }}
                                </span>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $page->status_badge_class }}">
                                    {{ $page->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($page->visibility == 'public')
                                <span class="badge bg-info">
                                    <i class="bi bi-eye me-1"></i>Public
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-eye-slash me-1"></i>Private
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if($page->show_in_header)
                                    <span class="badge bg-primary" title="In Header">
                                        <i class="bi bi-layout-text-window-reverse"></i>
                                    </span>
                                    @endif
                                    @if($page->show_in_footer)
                                    <span class="badge bg-dark" title="In Footer">
                                        <i class="bi bi-layout-text-window"></i>
                                    </span>
                                    @endif
                                    @if(!$page->show_in_header && !$page->show_in_footer)
                                    <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $page->created_at->format('M d, Y') }}
                                    @if($page->creator)
                                    <br>by {{ $page->creator->name }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    {{-- View --}}
                                    @if(auth('admin')->user()->hasPermission('content.read'))
                                    <a href="{{ route('admin.pages.show', $page) }}"
                                       class="btn btn-outline-info"
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif

                                    {{-- Edit --}}
                                    @if(auth('admin')->user()->hasPermission('content.update'))
                                    <a href="{{ route('admin.pages.edit', $page) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif

                                    {{-- Dropdown Actions --}}
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button"
                                                class="btn btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            {{-- Quick Status Changes --}}
                                            @if(auth('admin')->user()->hasPermission('content.update'))
                                                @if($page->status == 'draft')
                                                <li>
                                                    <form action="{{ route('admin.pages.publish', $page) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-check-circle text-success me-2"></i>Publish
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if($page->status == 'published')
                                                <li>
                                                    <form action="{{ route('admin.pages.unpublish', $page) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-pencil-square text-warning me-2"></i>Set to Draft
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if($page->status != 'archived')
                                                <li>
                                                    <form action="{{ route('admin.pages.archive', $page) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-archive text-secondary me-2"></i>Archive
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                            @endif

                                            {{-- Duplicate --}}
                                            @if(auth('admin')->user()->hasPermission('content.create'))
                                            <li>
                                                <form action="{{ route('admin.pages.duplicate', $page) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-copy text-info me-2"></i>Duplicate
                                                    </button>
                                                </form>
                                            </li>
                                            @endif

                                            {{-- Preview --}}
                                            <li>
                                                <a href="{{ route('admin.pages.preview', $page) }}"
                                                   class="dropdown-item"
                                                   target="_blank">
                                                    <i class="bi bi-eye text-primary me-2"></i>Preview
                                                </a>
                                            </li>

                                            {{-- Delete --}}
                                            @if(auth('admin')->user()->hasPermission('content.delete'))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button"
                                                        class="dropdown-item text-danger"
                                                        onclick="confirmDelete('{{ $page->id }}', '{{ $page->title }}')">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $pages->firstItem() ?? 0 }} to {{ $pages->lastItem() ?? 0 }} of {{ $pages->total() }} entries
                    </div>
                    <div>
                        {{ $pages->links() }}
                    </div>
                </div>
            </div>
            @else
            {{-- Empty State --}}
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">No Pages Found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'visibility', 'parent_filter', 'navigation']))
                        No pages match your current filters.
                        <br>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-link">Clear Filters</a>
                    @else
                        Get started by creating your first page.
                        <br>
                        @if(auth('admin')->user()->hasPermission('content.create'))
                        <a href="{{ route('admin.pages.create') }}" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-plus-circle me-1"></i>Create Page
                        </a>
                        @endif
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Delete Form (Hidden) --}}
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- Bulk Delete Form (Hidden) --}}
<form id="bulkDeleteForm" action="{{ route('admin.pages.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
    <div id="bulkDeleteInput"></div>
</form>

@endsection

@push('styles')
<style>
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .form-check-input:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .dropdown-menu {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    // Select All Checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.page-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // Individual Checkbox
    document.querySelectorAll('.page-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();

            // Update "Select All" checkbox state
            const allCheckboxes = document.querySelectorAll('.page-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.page-checkbox:checked');
            document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
        });
    });

    // Update Bulk Delete Button Visibility
    function updateBulkDeleteButton() {
        const checkedCheckboxes = document.querySelectorAll('.page-checkbox:checked');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCount = document.getElementById('selectedCount');

        if (checkedCheckboxes.length > 0) {
            bulkDeleteBtn.style.display = 'block';
            selectedCount.textContent = checkedCheckboxes.length;
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
    }

    // Confirm Single Delete
    function confirmDelete(pageId, pageTitle) {
        if (confirm(`Are you sure you want to delete the page "${pageTitle}"?\n\nThis action cannot be undone.`)) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/pages/${pageId}`;
            form.submit();
        }
    }

    // Confirm Bulk Delete
    function confirmBulkDelete() {
        const checkedCheckboxes = document.querySelectorAll('.page-checkbox:checked');
        const count = checkedCheckboxes.length;

        if (count === 0) {
            alert('Please select at least one page to delete.');
            return;
        }

        if (confirm(`Are you sure you want to delete ${count} page(s)?\n\nThis action cannot be undone.`)) {
            const form = document.getElementById('bulkDeleteForm');
            const inputContainer = document.getElementById('bulkDeleteInput');
            inputContainer.innerHTML = '';

            checkedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'page_ids[]';
                input.value = checkbox.value;
                inputContainer.appendChild(input);
            });

            form.submit();
        }
    }

    // Auto-submit filters on change
    document.querySelectorAll('#filterForm select').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush
