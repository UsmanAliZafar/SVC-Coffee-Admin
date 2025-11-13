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

    .detail-card .table {
        margin-bottom: 0;
    }

    .detail-card .table code {
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        display: inline-block;
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .detail-card .table code.text-danger {
        background: #fff5f5;
        border: 1px solid #fecaca;
    }

    .detail-card .table code.text-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }

    .table-responsive {
        border-radius: 6px;
        overflow: hidden;
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
            <!-- URL Redirects -->
            @if(isset($categoryRedirects) && $categoryRedirects->count() > 0)
            <div class="detail-card">
                <h3 class="detail-card-title"><i class="bi bi-arrow-repeat"></i> URL Redirects ({{ $categoryRedirects->count() }})</h3>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Old URL</th>
                                <th>New URL</th>
                                <th>Type</th>
                                <th>Hits</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryRedirects as $redirect)
                            <tr>
                                <td>
                                    <code class="text-danger">{{ $redirect->old_url }}</code>
                                </td>
                                <td>
                                    <i class="bi bi-arrow-right text-muted mx-2"></i>
                                    <code class="text-success">{{ $redirect->new_url }}</code>
                                </td>
                                <td>{!! $redirect->getTypeBadge() !!}</td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bi bi-bar-chart"></i> {{ $redirect->hit_count }}
                                    </span>
                                </td>
                                <td>{!! $redirect->getStatusBadge() !!}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $redirect->created_at->format('M d, Y') }}<br>
                                        {{ $redirect->created_at->diffForHumans() }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($redirect->notes)
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        <strong>Last Note:</strong> {{ $categoryRedirects->first()->notes }}
                    </small>
                </div>
                @endif
            </div>
            @endif

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
                                <td>{{ $product->name }}</td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>{{ store_currency_symbol() }}{{ number_format($product->price, 2) }}</td>
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
            <!-- Product Variants Summary -->
            @php
                $totalVariants = 0;
                $variantsByProduct = [];

                foreach($category->products as $product) {
                    if ($product->has_variants && $product->variants->count() > 0) {
                        $totalVariants += $product->variants->count();
                        $variantsByProduct[$product->id] = [
                            'product' => $product,
                            'variants' => $product->variants
                        ];
                    }
                }
            @endphp

            @if($totalVariants > 0)
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="detail-card-title mb-0">
                        <i class="bi bi-grid-3x3-gap"></i> Product Variants
                        <span class="badge bg-primary ms-2">{{ $totalVariants }} Total</span>
                    </h3>
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#variantsCollapse">
                        <i class="bi bi-chevron-down"></i> Toggle All
                    </button>
                </div>

                <div class="collapse show" id="variantsCollapse">
                    <!-- Variants Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-box-seam fs-2 text-primary"></i>
                                    <h4 class="mt-2 mb-0">{{ count($variantsByProduct) }}</h4>
                                    <small class="text-muted">Products with Variants</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-check-circle fs-2 text-success"></i>
                                    <h4 class="mt-2 mb-0">
                                        @php
                                            $inStockVariants = 0;
                                            foreach($variantsByProduct as $data) {
                                                $inStockVariants += $data['variants']->filter(fn($v) => $v->isInStock())->count();
                                            }
                                            echo $inStockVariants;
                                        @endphp
                                    </h4>
                                    <small class="text-muted">In Stock Variants</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <i class="bi bi-x-circle fs-2 text-danger"></i>
                                    <h4 class="mt-2 mb-0">
                                        @php
                                            $outOfStockVariants = 0;
                                            foreach($variantsByProduct as $data) {
                                                $outOfStockVariants += $data['variants']->filter(fn($v) => $v->isOutOfStock())->count();
                                            }
                                            echo $outOfStockVariants;
                                        @endphp
                                    </h4>
                                    <small class="text-muted">Out of Stock Variants</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products with Variants Accordion -->
                    <div class="accordion" id="variantsAccordion">
                        @foreach($variantsByProduct as $productId => $data)
                        @php
                            $product = $data['product'];
                            $variants = $data['variants'];
                        @endphp
                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="heading{{ $product->id }}">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $product->id }}"
                                        aria-expanded="false"
                                        aria-controls="collapse{{ $product->id }}">
                                    <div class="d-flex align-items-center w-100">
                                        <img src="{{ $product->getMainImageUrl() }}"
                                            alt="{{ $product->name }}"
                                            class="me-3"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        <div class="flex-grow-1">
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                                        </div>
                                        <div class="text-end me-3">
                                            <span class="badge bg-primary">{{ $variants->count() }} Variants</span>
                                            <span class="badge bg-success">{{ $variants->filter(fn($v) => $v->isInStock())->count() }} In Stock</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $product->id }}"
                                class="accordion-collapse collapse"
                                aria-labelledby="heading{{ $product->id }}"
                                data-bs-parent="#variantsAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 60px;">Image</th>
                                                    <th>Variant</th>
                                                    <th>SKU</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th>Dimensions</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($variants->sortBy('sort_order') as $variant)
                                                <tr class="{{ $variant->is_default ? 'table-primary' : '' }}">
                                                    <!-- Image -->
                                                    <td>
                                                        <img src="{{ $variant->getImageUrl() }}"
                                                            alt="{{ $variant->getFullName() }}"
                                                            class="img-thumbnail"
                                                            style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                                            onclick="viewVariantImage('{{ $variant->getImageUrl() }}', '{{ $variant->getFullName() }}')">
                                                    </td>

                                                    <!-- Variant Name -->
                                                    <td>
                                                        <strong>{{ $variant->getFullName() }}</strong>
                                                        @if($variant->is_default)
                                                            <span class="badge bg-info badge-sm ms-1">Default</span>
                                                        @endif
                                                        <br>
                                                        <small class="text-muted">{{ $variant->variant_name }}: {{ $variant->variant_value }}</small>
                                                    </td>

                                                    <!-- SKU -->
                                                    <td>
                                                        <code class="small">{{ $variant->sku }}</code>
                                                    </td>

                                                    <!-- Price -->
                                                    <td>
                                                        <div>
                                                            @if($variant->isOnSale())
                                                                <span class="text-decoration-line-through text-muted small">
                                                                    {{ $variant->getFormattedPrice() }}
                                                                </span>
                                                                <br>
                                                                <strong class="text-success">{{ $variant->getFormattedSalePrice() }}</strong>
                                                                <br>
                                                                <span class="badge bg-danger badge-sm">
                                                                    -{{ $variant->getDiscountPercentage() }}%
                                                                </span>
                                                            @else
                                                                <strong>{{ $variant->getFormattedPrice() }}</strong>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <!-- Stock -->
                                                    <td>
                                                        {!! $variant->getStockBadge() !!}
                                                        @if($variant->isLowStock())
                                                            <br>
                                                            <small class="text-warning">
                                                                <i class="bi bi-exclamation-triangle"></i> Low Stock
                                                            </small>
                                                        @endif
                                                    </td>

                                                    <!-- Dimensions -->
                                                    <td>
                                                        @if($variant->hasPhysicalDimensions() || $variant->hasWeight())
                                                            <small>
                                                                @if($variant->hasWeight())
                                                                    <i class="bi bi-box"></i> {{ $variant->getFormattedWeight() }}
                                                                    <br>
                                                                @endif
                                                                @if($variant->hasPhysicalDimensions())
                                                                    <i class="bi bi-rulers"></i> {{ $variant->getDimensions() }}
                                                                @endif
                                                            </small>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>

                                                    <!-- Status -->
                                                    <td>{!! $variant->getStatusBadge() !!}</td>

                                                    <!-- Actions -->
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button"
                                                                    class="btn btn-outline-info btn-sm"
                                                                    onclick="viewVariantDetails('{{ $variant->id }}')"
                                                                    title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            @if(auth('admin')->user()->hasPermission('products.update'))
                                                            <a href="{{ route('admin.products.edit', $product->id) }}#variants"
                                                            class="btn btn-outline-warning btn-sm"
                                                            title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Product Actions -->
                                    <div class="mt-3 text-end">
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Product Details
                                        </a>
                                        @if(auth('admin')->user()->hasPermission('products.update'))
                                        <a href="{{ route('admin.products.edit', $product->id) }}#variants" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i> Edit Variants
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
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
