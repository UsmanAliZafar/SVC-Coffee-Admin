@extends('admin.layouts.app')

@section('title', 'Empty Categories')

@push('styles')
<style>
    .empty-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .empty-header {
        color: #5B914C;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .empty-illustration {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    .empty-illustration i {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    .category-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }
    .category-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    .category-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .category-icon {
        font-size: 1.5rem;
        color: #5B914C;
    }
    .alert-info-custom {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-folder-x"></i> Empty Categories</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">Empty</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to All Categories
            </a>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert-info-custom">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle" style="font-size: 2rem; margin-right: 15px;"></i>
            <div>
                <h5 class="mb-1">What are Empty Categories?</h5>
                <p class="mb-0">These categories currently have no products assigned. You may want to add products to them or consider removing them to keep your store organized.</p>
            </div>
        </div>
    </div>

    <!-- Empty Categories List -->
    <div class="empty-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="empty-header mb-0">
                <i class="bi bi-folder-x"></i> Empty Categories
                <span class="badge bg-warning text-dark">{{ $categories->count() }}</span>
            </h3>

            @if($categories->count() > 0)
            <div>
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteEmpty">
                    <i class="bi bi-trash"></i> Delete All Empty
                </button>
            </div>
            @endif
        </div>

        @if($categories->count() > 0)
            <div class="row">
                @foreach($categories as $category)
                <div class="col-md-6 mb-3">
                    <div class="category-item">
                        <div class="category-info">
                            <i class="bi bi-folder category-icon"></i>
                            <div>
                                <h6 class="mb-1">{{ $category->title }}</h6>
                                <small class="text-muted">
                                    @if($category->parent)
                                        Parent: {{ $category->parent->title }}
                                    @else
                                        <span class="badge bg-secondary">Root Category</span>
                                    @endif
                                </small>
                                <div class="mt-1">
                                    {!! $category->getStatusBadge() !!}
                                    @if($category->is_featured)
                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-star-fill"></i> Featured</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            @if(auth('admin')->user()->hasPermission('categories.read'))
                            <a href="{{ route('admin.categories.show', $category->id) }}"
                               class="btn btn-outline-primary"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif

                            @if(auth('admin')->user()->hasPermission('categories.update'))
                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                               class="btn btn-outline-warning"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif

                            @if(auth('admin')->user()->hasPermission('categories.delete'))
                            <button type="button"
                                    class="btn btn-outline-danger delete-category"
                                    data-id="{{ $category->id }}"
                                    data-title="{{ $category->title }}"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($categories->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $categories->links() }}
            </div>
            @endif

        @else
            <!-- No Empty Categories -->
            <div class="empty-illustration">
                <i class="bi bi-check-circle-fill text-success"></i>
                <h4>Great! No Empty Categories</h4>
                <p class="text-muted">All your categories have products assigned to them.</p>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-list"></i> View All Categories
                </a>
            </div>
        @endif
    </div>

    <!-- Statistics Card -->
    @if($categories->count() > 0)
    <div class="empty-card">
        <h3 class="empty-header"><i class="bi bi-bar-chart"></i> Statistics</h3>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="stat-box" style="background: linear-gradient(135deg, #dc3545 0%, #a02622 100%); color: white; padding: 20px; border-radius: 10px;">
                    <h3>{{ $categories->count() }}</h3>
                    <small>Empty Categories</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box" style="background: linear-gradient(135deg, #ffc107 0%, #cc9a06 100%); color: white; padding: 20px; border-radius: 10px;">
                    <h3>{{ $categories->where('is_featured', true)->count() }}</h3>
                    <small>Featured Empty</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box" style="background: linear-gradient(135deg, #6c757d 0%, #4e555b 100%); color: white; padding: 20px; border-radius: 10px;">
                    <h3>{{ $categories->whereNull('parent_id')->count() }}</h3>
                    <small>Root Level</small>
                </div>
            </div>
            <div class="col-md-3 d-none" style="display: none">
                <div class="stat-box" style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); color: white; padding: 20px; border-radius: 10px;">
                    <h3>{{ $categories->whereNotNull('parent_id')->count() }}</h3>
                    <small>Subcategories</small>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Delete single category
    $(document).on('click', '.delete-category', function() {
        const categoryId = $(this).data('id');
        const categoryTitle = $(this).data('title');

        Swal.fire({
            title: 'Delete "' + categoryTitle + '"?',
            text: "This category has no products, so it's safe to delete.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.categories.destroy", ":id") }}'.replace(':id', categoryId),
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
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete category', 'error');
                    }
                });
            }
        });
    });

    // Bulk delete all empty categories
    $('#bulkDeleteEmpty').on('click', function() {
        Swal.fire({
            title: 'Delete All Empty Categories?',
            text: "This will delete {{ $categories->count() }} empty categories. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them all!',
            input: 'checkbox',
            inputPlaceholder: 'I understand this cannot be undone'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const categoryIds = [
                    @foreach($categories as $category)
                        '{{ $category->id }}',
                    @endforeach
                ];

                let completed = 0;
                let failed = 0;

                // Show progress
                Swal.fire({
                    title: 'Deleting...',
                    html: 'Deleting empty categories, please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                categoryIds.forEach(function(id) {
                    $.ajax({
                        url: '{{ route("admin.categories.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { completed++; },
                        error: function() { failed++; },
                        complete: function() {
                            if (completed + failed === categoryIds.length) {
                                Swal.fire({
                                    title: 'Bulk Delete Complete',
                                    html: `Deleted: <strong>${completed}</strong><br>Failed: <strong>${failed}</strong>`,
                                    icon: failed > 0 ? 'warning' : 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        }
                    });
                });
            } else if (result.isConfirmed && !result.value) {
                Swal.fire('Cancelled', 'You must confirm by checking the box', 'info');
            }
        });
    });
});
</script>
@endpush
