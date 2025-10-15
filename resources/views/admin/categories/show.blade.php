@extends('admin.layouts.app')

@section('title', 'Category Details')

@push('styles')
<style>
    .detail-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .detail-card-title {
        color: #5B914C;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .detail-row {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #666;
        margin-bottom: 5px;
    }
    .detail-value {
        color: #333;
    }
    .category-image {
        max-width: 100%;
        border-radius: 8px;
        border: 2px solid #5B914C;
        padding: 5px;
    }
    .stat-box {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 15px;
    }
    .stat-box .value {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .stat-box .label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .breadcrumb-path {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 5px;
        display: inline-block;
    }
    .children-list {
        list-style: none;
        padding-left: 0;
    }
    .children-list li {
        padding: 8px 12px;
        background: #f8f9fa;
        margin-bottom: 8px;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Category Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">{{ $category->title }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('categories.update'))
            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('categories.delete'))
            <button type="button" class="btn btn-danger" id="deleteBtn">
                <i class="bi bi-trash"></i> Delete
            </button>
            @endif
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-info-circle"></i> Basic Information</h3>

                <div class="detail-row">
                    <div class="detail-label">Title</div>
                    <div class="detail-value">
                        <h4 class="mb-0">{{ $category->title }}</h4>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Slug</div>
                    <div class="detail-value">
                        <code>{{ $category->slug }}</code>
                        <a href="#" class="ms-2" onclick="navigator.clipboard.writeText('{{ $category->slug }}')">
                            <i class="bi bi-clipboard"></i>
                        </a>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Category Path</div>
                    <div class="detail-value">
                        <span class="breadcrumb-path">{{ $category->full_path }}</span>
                    </div>
                </div>

                @if($category->short_description)
                <div class="detail-row">
                    <div class="detail-label">Short Description</div>
                    <div class="detail-value">{{ $category->short_description }}</div>
                </div>
                @endif

                @if($category->description)
                <div class="detail-row">
                    <div class="detail-label">Full Description</div>
                    <div class="detail-value">{{ $category->description }}</div>
                </div>
                @endif
            </div>

            <!-- Images -->
            @if($category->image || $category->banner_image || $category->icon || $category->thumbnail)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-image"></i> Images</h3>

                <div class="row">
                    @if($category->image)
                    <div class="col-md-6 mb-3">
                        <div class="detail-label mb-2">Main Image</div>
                        <img src="{{ $category->getImageUrl('image') }}" alt="{{ $category->title }}" class="category-image">
                    </div>
                    @endif

                    @if($category->thumbnail)
                    <div class="col-md-6 mb-3">
                        <div class="detail-label mb-2">Thumbnail</div>
                        <img src="{{ $category->getImageUrl('thumbnail') }}" alt="{{ $category->title }}" class="category-image">
                    </div>
                    @endif

                    @if($category->banner_image)
                    <div class="col-md-12 mb-3">
                        <div class="detail-label mb-2">Banner Image</div>
                        <img src="{{ $category->getImageUrl('banner_image') }}" alt="{{ $category->title }}" class="category-image">
                    </div>
                    @endif

                    @if($category->icon)
                    <div class="col-md-6 mb-3">
                        <div class="detail-label mb-2">Icon</div>
                        <img src="{{ $category->getImageUrl('icon') }}" alt="{{ $category->title }}" class="category-image" style="max-width: 100px;">
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- SEO Information -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-search"></i> SEO Information</h3>

                <div class="detail-row">
                    <div class="detail-label">Meta Title</div>
                    <div class="detail-value">{{ $category->meta_title ?: 'Not set' }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Meta Description</div>
                    <div class="detail-value">{{ $category->meta_description ?: 'Not set' }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Meta Keywords</div>
                    <div class="detail-value">
                        @if($category->meta_keywords)
                            @foreach(explode(',', $category->meta_keywords) as $keyword)
                                <span class="badge bg-secondary me-1">{{ trim($keyword) }}</span>
                            @endforeach
                        @else
                            Not set
                        @endif
                    </div>
                </div>
            </div>

            <!-- Children Categories -->
            @if($category->children->count() > 0)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-diagram-3"></i> Subcategories ({{ $category->children->count() }})</h3>

                <ul class="children-list">
                    @foreach($category->children as $child)
                    <li>
                        <div>
                            <i class="bi bi-folder-fill text-warning me-2"></i>
                            <strong>{{ $child->title }}</strong>
                            @if($child->products_count > 0)
                                <span class="badge bg-success ms-2">{{ $child->products_count }} products</span>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('admin.categories.show', $child->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Products List -->
            @if($category->products->count() > 0)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-box-seam"></i> Products ({{ $category->products->count() }})</h3>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->products->take(10) as $product)
                            <tr>
                                <td>{{ $product->title }}</td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td>{!! $product->getStatusBadge() !!}</td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($category->products->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="btn btn-outline-primary">
                            View All Products
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="stat-box">
                <div class="value">{{ $category->products_count }}</div>
                <div class="label">Total Products</div>
            </div>

            <div class="stat-box" style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                <div class="value">{{ $category->views_count }}</div>
                <div class="label">Total Views</div>
            </div>

            <div class="stat-box" style="background: linear-gradient(135deg, #fd7e14 0%, #ca6510 100%);">
                <div class="value">{{ $category->clicks_count }}</div>
                <div class="label">Total Clicks</div>
            </div>

            <!-- Status & Settings -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-gear"></i> Settings</h3>

                <div class="detail-row">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">{!! $category->getStatusBadge() !!}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Parent Category</div>
                    <div class="detail-value">
                        @if($category->parent)
                            <a href="{{ route('admin.categories.show', $category->parent->id) }}">
                                {{ $category->parent->title }}
                            </a>
                        @else
                            <span class="badge bg-secondary">Root Category</span>
                        @endif
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Display Order</div>
                    <div class="detail-value">{{ $category->order }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Category Depth</div>
                    <div class="detail-value">Level {{ $category->depth }}</div>
                </div>
            </div>

            <!-- Display Options -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-eye"></i> Display Options</h3>

                <div class="detail-row">
                    <div class="detail-label">Featured</div>
                    <div class="detail-value">
                        @if($category->is_featured)
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Show in Menu</div>
                    <div class="detail-value">
                        @if($category->show_in_menu)
                            <span class="badge bg-info"><i class="bi bi-check-circle"></i> Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Show on Homepage</div>
                    <div class="detail-value">
                        @if($category->show_on_home)
                            <span class="badge bg-primary"><i class="bi bi-check-circle"></i> Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Audit Information -->
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-clock-history"></i> Audit Information</h3>

                <div class="detail-row">
                    <div class="detail-label">Created At</div>
                    <div class="detail-value">
                        {{ $category->created_at->format('M d, Y') }}<br>
                        <small class="text-muted">{{ $category->created_at->diffForHumans() }}</small>
                    </div>
                </div>

                @if($category->creator)
                <div class="detail-row">
                    <div class="detail-label">Created By</div>
                    <div class="detail-value">{{ $category->creator->name }}</div>
                </div>
                @endif

                <div class="detail-row">
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value">
                        {{ $category->updated_at->format('M d, Y') }}<br>
                        <small class="text-muted">{{ $category->updated_at->diffForHumans() }}</small>
                    </div>
                </div>

                @if($category->updater)
                <div class="detail-row">
                    <div class="detail-label">Updated By</div>
                    <div class="detail-value">{{ $category->updater->name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Delete category
    $('#deleteBtn').on('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.categories.destroy", $category->id) }}',
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
                                window.location.href = '{{ route("admin.categories.index") }}';
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
});
</script>
@endpush
