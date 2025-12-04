@extends('admin.layouts.app')

@section('title', 'Menu Manager')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Menu Manager</h1>
            <p class="text-muted mb-0">Organize and manage menu items</p>
        </div>
        <div>
            <a href="{{ route('admin.pages.menus.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Menus
            </a>
        </div>
    </div>

    <!-- Location Selector -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Select Menu Location:</label>
                    <select class="form-select" id="locationSelector" onchange="window.location.href='{{ route('admin.pages.menu.manager') }}?location=' + this.value">
                        <option value="header" {{ $location == 'header' ? 'selected' : '' }}>Header Menu</option>
                        <option value="footer" {{ $location == 'footer' ? 'selected' : '' }}>Footer Menu</option>
                        <option value="both" {{ $location == 'both' ? 'selected' : '' }}>Both (Header & Footer)</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        <strong>Current Location:</strong> {{ ucfirst($location) }} Menu
                        @if($allMenus->where('location', $location)->count() > 0)
                        | <strong>Active Menus:</strong> {{ $allMenus->where('location', $location)->count() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Menu Items -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold" style="color: #5B914C;">
                        <i class="fas fa-list"></i> Pages in {{ ucfirst($location) }} Menu
                    </h6>
                    <span class="badge bg-primary">{{ $menuPages->count() }} items</span>
                </div>
                <div class="card-body">
                    @if($menuPages->count() > 0)
                    <div id="menuItemsList" class="list-group">
                        @foreach($menuPages as $page)
                        <div class="list-group-item" data-id="{{ $page->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-grip-vertical text-muted me-3" style="cursor: move;"></i>
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $page->title }}
                                                @if($page->menu_label && $page->menu_label != $page->title)
                                                <small class="text-muted">({{ $page->menu_label }})</small>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-link"></i> /{{ $page->slug }} |
                                                Order: {{ $page->display_order }}
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Child Pages -->
                                    @if($page->children->count() > 0)
                                    <div class="ms-5 border-start ps-3">
                                        @foreach($page->children as $child)
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" data-id="{{ $child->id }}">
                                            <div>
                                                <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>
                                                <strong>{{ $child->title }}</strong>
                                                <small class="text-muted ms-2">/{{ $child->slug }}</small>
                                            </div>
                                            <form method="POST" action="{{ route('admin.pages.menu.remove', $child) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from menu">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div class="ms-3">
                                    <form method="POST" action="{{ route('admin.pages.menu.remove', $page) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from menu" onclick="return confirm('Remove this page from the menu?')">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-mouse"></i> <strong>Tip:</strong> Drag items to reorder them in the menu.
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No pages in this menu</h5>
                        <p class="text-muted">Add pages from the available pages list on the right.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Available Pages -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #5B914C;">
                        <i class="fas fa-plus-circle"></i> Available Pages
                    </h6>
                </div>
                <div class="card-body">
                    @if($availablePages->count() > 0)
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchPages" placeholder="Search pages...">
                    </div>
                    <div class="list-group" id="availablePagesList">
                        @foreach($availablePages as $page)
                        <div class="list-group-item list-group-item-action available-page-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $page->title }}</h6>
                                    <small class="text-muted">/{{ $page->slug }}</small>
                                </div>
                                <form method="POST" action="{{ route('admin.pages.menu.add', $page) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="menu_location" value="{{ $location }}">
                                    <button type="submit" class="btn btn-sm btn-success" title="Add to menu">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted mb-0">All published pages are already in menus.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow mb-4" style="border-left: 4px solid #5B914C;">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-question-circle"></i> How to Use
                    </h6>
                    <ol class="small mb-0">
                        <li>Click <span class="badge bg-success"><i class="fas fa-plus"></i></span> to add pages to the menu</li>
                        <li>Drag items to reorder them</li>
                        <li>Click <span class="badge bg-danger"><i class="fas fa-trash"></i></span> to remove from menu</li>
                        <li>Changes are saved automatically</li>
                    </ol>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-chart-bar"></i> Quick Stats
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>In Menu:</td>
                            <td class="text-end"><strong>{{ $menuPages->count() }}</strong></td>
                        </tr>
                        <tr>
                            <td>Available:</td>
                            <td class="text-end"><strong>{{ $availablePages->count() }}</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td>Total Published:</td>
                            <td class="text-end"><strong>{{ $menuPages->count() + $availablePages->count() }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.list-group-item {
    transition: background-color 0.2s;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
.sortable-ghost {
    opacity: 0.4;
    background-color: #e3f2fd;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Initialize Sortable for drag and drop
const menuList = document.getElementById('menuItemsList');
if (menuList) {
    new Sortable(menuList, {
        animation: 150,
        handle: '.fa-grip-vertical',
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
            updateMenuOrder();
        }
    });
}

// Update menu order via AJAX
function updateMenuOrder() {
    const items = document.querySelectorAll('#menuItemsList > .list-group-item');
    const orders = [];

    items.forEach((item, index) => {
        orders.push({
            id: item.dataset.id,
            order: index,
            parent_id: null
        });
    });

    fetch('{{ route("admin.pages.menu.update-order") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            location: '{{ $location }}',
            orders: orders
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Menu order updated successfully', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update menu order', 'error');
    });
}

// Search available pages
document.getElementById('searchPages')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const items = document.querySelectorAll('.available-page-item');

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Show notification
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
@endsection
