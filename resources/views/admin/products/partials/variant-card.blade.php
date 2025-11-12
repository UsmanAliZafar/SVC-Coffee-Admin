<div class="variant-card mb-3" data-variant-id="{{ $variant->id }}" id="variant-{{ $variant->id }}">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Variant Image -->
                <div class="col-md-1">
                    <img src="{{ $variant->getImageUrl() }}" alt="{{ $variant->getFullName() }}"
                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                </div>

                <!-- Variant Info -->
                <div class="col-md-3">
                    <strong>{{ $variant->getFullName() }}</strong><br>
                    <small class="text-muted">SKU: {{ $variant->sku }}</small>
                    @if($variant->is_default)
                        <span class="badge bg-info ms-2">Default</span>
                    @endif
                </div>

                <!-- Pricing -->
                <div class="col-md-2">
                    <div class="small text-muted">Price</div>
                    @if($variant->isOnSale())
                        <span class="text-decoration-line-through text-muted">{{ $variant->getFormattedPrice() }}</span><br>
                        <strong class="text-success">{{ $variant->getFormattedSalePrice() }}</strong>
                        <span class="badge bg-danger ms-1">-{{ $variant->getDiscountPercentage() }}%</span>
                    @else
                        <strong>{{ $variant->getFormattedPrice() }}</strong>
                    @endif
                </div>

                <!-- Stock -->
                <div class="col-md-2">
                    <div class="small text-muted">Stock</div>
                    {!! $variant->getStockBadge() !!}
                </div>

                <!-- Dimensions -->
                <div class="col-md-2">
                    <div class="small text-muted">Dimensions</div>
                    @if($variant->hasPhysicalDimensions() || $variant->hasWeight())
                        @if($variant->hasWeight())
                            <small>{{ $variant->weight }} kg</small><br>
                        @endif
                        @if($variant->hasPhysicalDimensions())
                            <small>{{ $variant->getDimensions() }}</small>
                        @endif
                    @else
                        <small class="text-muted">Not set</small>
                    @endif
                </div>

                <!-- Actions -->
                <div class="col-md-2 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="editVariant('{{ $variant->id }}')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if(!$variant->is_default)
                        <button type="button" class="btn btn-sm btn-outline-info"
                                onclick="setDefaultVariant('{{ $variant->id }}')" title="Set as Default">
                            <i class="bi bi-star"></i>
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="deleteVariant('{{ $variant->id }}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="row mt-2">
                <div class="col-md-12">
                    {!! $variant->getStatusBadge() !!}
                </div>
            </div>
        </div>
    </div>
</div>
