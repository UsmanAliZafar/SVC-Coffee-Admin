@extends('admin.layouts.app')
{{-- resources/views/admin/inventory/adjust.blade.php --}}
@section('title', 'Adjust Stock')

@push('styles')
<style>
    .adjustment-card {
        background: #fff;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }

    .product-search-result {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.2s;
    }

    .product-search-result:hover {
        background-color: #f8f9fa;
    }

    .current-stock-display {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid #5B914C;
        margin: 20px 0;
    }

    .current-stock-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .current-stock-value {
        font-size: 3rem;
        font-weight: bold;
        color: #5B914C;
    }

    .action-type-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 15px;
    }

    .action-type-card:hover {
        border-color: #5B914C;
        background-color: #f8f9fa;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.2);
    }

    .action-type-card.active {
        border-color: #5B914C;
        background-color: #e7f3e7;
    }

    .action-type-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .action-type-title {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .action-type-desc {
        font-size: 0.85rem;
        color: #666;
    }

    .btn-adjust {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        padding: 12px 30px;
        font-size: 1.1rem;
    }

    .btn-adjust:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }

    .preview-box {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
    }

    .preview-label {
        font-weight: 600;
        color: #856404;
    }

    .preview-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #856404;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-slash-minus"></i> Adjust Stock</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Adjust Stock</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Form -->
        <div class="col-lg-8">
            <form id="adjustStockForm">
                @csrf

                <!-- Product Selection -->
                <div class="adjustment-card">
                    <h5 class="section-title">
                        <i class="bi bi-box-seam"></i> 1. Select Product
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Search Product</label>
                        <input type="text" id="productSearch" class="form-control form-control-lg"
                               placeholder="Search by product name or SKU..." autocomplete="off">
                        <div id="productSearchResults" class="border rounded mt-2" style="display: none; max-height: 300px; overflow-y: auto;">
                            <!-- Search results will appear here -->
                        </div>
                    </div>

                    <div id="selectedProductDisplay" style="display: none;">
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle fs-4 me-3"></i>
                            <div>
                                <strong>Selected: <span id="selectedProductName"></span></strong><br>
                                <small>SKU: <span id="selectedProductSku"></span></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearProductSelection()">
                                <i class="bi bi-x"></i> Change
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="product_id" id="selectedProductId">
                </div>

                <!-- Warehouse Selection -->
                <div class="adjustment-card">
                    <h5 class="section-title">
                        <i class="bi bi-building"></i> 2. Select Warehouse
                    </h5>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Warehouse</label>
                        <select name="warehouse_id" id="warehouseSelect" class="form-select form-select-lg" required>
                            <option value="">Choose warehouse...</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $warehouse->is_default ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                                @if($warehouse->is_default)
                                    (Default)
                                @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Action Type Selection -->
                <div class="adjustment-card">
                    <h5 class="section-title">
                        <i class="bi bi-gear"></i> 3. Choose Action Type
                    </h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="action-type-card" data-action="set">
                                <div class="action-type-icon text-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </div>
                                <div class="action-type-title">Set Stock</div>
                                <div class="action-type-desc">Replace current quantity</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="action-type-card" data-action="add">
                                <div class="action-type-icon text-success">
                                    <i class="bi bi-plus-circle"></i>
                                </div>
                                <div class="action-type-title">Add Stock</div>
                                <div class="action-type-desc">Increase quantity</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="action-type-card" data-action="reduce">
                                <div class="action-type-icon text-danger">
                                    <i class="bi bi-dash-circle"></i>
                                </div>
                                <div class="action-type-title">Reduce Stock</div>
                                <div class="action-type-desc">Decrease quantity</div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="action_type" id="selectedActionType">
                </div>

                <!-- Quantity and Reason -->
                <div class="adjustment-card">
                    <h5 class="section-title">
                        <i class="bi bi-123"></i> 4. Enter Details
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="adjustQuantity" class="form-control form-control-lg"
                               min="0" placeholder="Enter quantity" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea name="reason" class="form-control" rows="4"
                                  placeholder="Enter reason for this adjustment (optional)"></textarea>
                        <small class="text-muted">This will be recorded in the movement history</small>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="adjustment-card">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-adjust btn-lg" id="submitBtn" disabled>
                            <i class="bi bi-check-circle"></i> Adjust Stock
                        </button>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column - Preview -->
        <div class="col-lg-4">
            <!-- Current Stock Display -->
            <div class="adjustment-card">
                <h5 class="section-title">
                    <i class="bi bi-info-circle"></i> Current Stock
                </h5>

                <div class="current-stock-display" id="currentStockDisplay">
                    <div class="current-stock-label">Current Quantity</div>
                    <div class="current-stock-value" id="currentStockValue">—</div>
                    <small class="text-muted" id="warehouseNameDisplay">Select product & warehouse</small>
                </div>
            </div>

            <!-- Preview -->
            <div class="adjustment-card" id="previewCard" style="display: none;">
                <h5 class="section-title">
                    <i class="bi bi-eye"></i> Preview Changes
                </h5>

                <div class="preview-box">
                    <div class="mb-2">
                        <span class="preview-label">Action: </span>
                        <span id="previewAction" class="preview-value">—</span>
                    </div>
                    <div class="mb-2">
                        <span class="preview-label">Quantity Change: </span>
                        <span id="previewQuantity" class="preview-value">—</span>
                    </div>
                    <hr>
                    <div>
                        <span class="preview-label">New Stock: </span>
                        <span id="previewNewStock" class="preview-value">—</span>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="adjustment-card">
                <h5 class="section-title">
                    <i class="bi bi-lightbulb"></i> Tips
                </h5>

                <div class="alert alert-info mb-0">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2"><strong>Set Stock:</strong> Replace current quantity with new value</li>
                        <li class="mb-2"><strong>Add Stock:</strong> Increase current quantity (e.g., new purchase)</li>
                        <li class="mb-2"><strong>Reduce Stock:</strong> Decrease current quantity (e.g., damaged goods)</li>
                        <li class="mb-0">All adjustments are tracked in movement history</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStock = 0;
let searchTimeout;

$(document).ready(function() {
    // Product search
    $('#productSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        if (query.length >= 2) {
            searchTimeout = setTimeout(() => searchProducts(query), 300);
        } else {
            $('#productSearchResults').hide();
        }
    });

    // Action type selection
    $('.action-type-card').on('click', function() {
        $('.action-type-card').removeClass('active');
        $(this).addClass('active');
        $('#selectedActionType').val($(this).data('action'));
        validateForm();
        updatePreview();
    });

    // Warehouse selection change
    $('#warehouseSelect').on('change', function() {
        loadCurrentStock();
        validateForm();
    });

    // Quantity input change
    $('#adjustQuantity').on('input', function() {
        validateForm();
        updatePreview();
    });

    // Form validation on all inputs
    $('#adjustStockForm input, #adjustStockForm select').on('change input', function() {
        validateForm();
    });
});

// Search products - FIXED
function searchProducts(query) {
    $.ajax({
        url: '{{ route("admin.products.ajax-list") }}',
        method: 'GET',
        data: {
            search: query,
            track_inventory: 1,
            limit: 50
        },
        success: function(response) {
            console.log('Search response:', response); // Debug log

            let html = '';

            if (response.success && response.products && response.products.length > 0) {
                response.products.forEach(product => {
                    html += `
                        <div class="product-search-result" onclick="selectProduct('${product.id}', '${escapeHtml(product.name)}', '${product.sku}')">
                            <strong>${escapeHtml(product.name)}</strong><br>
                            <small class="text-muted">SKU: ${product.sku} | Stock: ${product.stock_quantity}</small>
                        </div>
                    `;
                });
            } else {
                html = '<div class="p-3 text-muted text-center">No products found</div>';
            }

            $('#productSearchResults').html(html).show();
        },
        error: function(xhr) {
            console.error('Search error:', xhr);
            $('#productSearchResults').html('<div class="p-3 text-danger text-center">Error loading products</div>').show();
        }
    });
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Select product
function selectProduct(id, name, sku) {
    $('#selectedProductId').val(id);
    $('#selectedProductName').text(name);
    $('#selectedProductSku').text(sku);
    $('#selectedProductDisplay').show();
    $('#productSearch').val('');
    $('#productSearchResults').hide();

    loadCurrentStock();
    validateForm();
}

// Clear product selection
function clearProductSelection() {
    $('#selectedProductId').val('');
    $('#selectedProductDisplay').hide();
    $('#currentStockValue').text('—');
    $('#warehouseNameDisplay').text('Select product & warehouse');
    currentStock = 0;
    validateForm();
    updatePreview();
}

// Load current stock - FIXED
function loadCurrentStock() {
    const productId = $('#selectedProductId').val();
    const warehouseId = $('#warehouseSelect').val();

    if (!productId || !warehouseId) {
        return;
    }

    $.ajax({
        url: '{{ route("admin.products.ajax-details", ":id") }}'.replace(':id', productId),
        method: 'GET',
        success: function(response) {
            console.log('Product details:', response); // Debug log

            if (response.success && response.product) {
                const product = response.product;
                const warehouseStock = product.warehouse_stock.find(ws => ws.warehouse_id === warehouseId);

                if (warehouseStock) {
                    currentStock = warehouseStock.quantity;
                    $('#warehouseNameDisplay').html(
                        $('#warehouseSelect option:selected').text() +
                        ' <small class="text-muted">(Available: ' + warehouseStock.available_quantity + ')</small>'
                    );
                } else {
                    currentStock = product.stock_quantity;
                    $('#warehouseNameDisplay').html(
                        $('#warehouseSelect option:selected').text() +
                        ' <small class="text-warning">(No stock in this warehouse)</small>'
                    );
                }

                $('#currentStockValue').text(currentStock);
                updatePreview();
            }
        },
        error: function(xhr) {
            console.error('Error loading product details:', xhr);
            currentStock = 0;
            $('#currentStockValue').text('—');
            $('#warehouseNameDisplay').text('Error loading stock');
        }
    });
}

// Validate form
function validateForm() {
    const productId = $('#selectedProductId').val();
    const warehouseId = $('#warehouseSelect').val();
    const actionType = $('#selectedActionType').val();
    const quantity = $('#adjustQuantity').val();

    const isValid = productId && warehouseId && actionType && quantity && quantity >= 0;

    $('#submitBtn').prop('disabled', !isValid);
}

// Update preview
function updatePreview() {
    const actionType = $('#selectedActionType').val();
    const quantity = parseInt($('#adjustQuantity').val()) || 0;

    if (!actionType || quantity === 0) {
        $('#previewCard').hide();
        return;
    }

    let newStock = currentStock;
    let actionText = '';
    let quantityChange = '';

    switch(actionType) {
        case 'set':
            newStock = quantity;
            actionText = 'Set Stock';
            quantityChange = quantity;
            break;
        case 'add':
            newStock = currentStock + quantity;
            actionText = 'Add Stock';
            quantityChange = '+' + quantity;
            break;
        case 'reduce':
            newStock = Math.max(0, currentStock - quantity);
            actionText = 'Reduce Stock';
            quantityChange = '-' + quantity;
            break;
    }

    $('#previewAction').text(actionText);
    $('#previewQuantity').text(quantityChange);
    $('#previewNewStock').text(newStock);
    $('#previewCard').show();
}

// Form submission
$('#adjustStockForm').on('submit', function(e) {
    e.preventDefault();

    const formData = $(this).serialize();

    Swal.fire({
        title: 'Confirm Stock Adjustment',
        text: 'Are you sure you want to adjust the stock?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, adjust it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.inventory.adjust.store") }}',
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    Swal.fire({
                        title: 'Adjusting Stock...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '{{ route("admin.inventory.index") }}';
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to adjust stock';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('<br>');
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage
                    });
                }
            });
        }
    });
});

// Hide search results when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#productSearch, #productSearchResults').length) {
        $('#productSearchResults').hide();
    }
});
</script>
@endpush
