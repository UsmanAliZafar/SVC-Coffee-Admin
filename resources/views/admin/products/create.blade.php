@extends('admin.layouts.app')
{{--  products/create.blade.php --}}
@section('title', 'Create Product')

@push('styles')
<style>
    .form-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    .section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
    }
    .required-field::after {
        content: " *";
        color: #dc3545;
    }
    .btn-save {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        min-width: 120px;
    }
    .btn-save:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }
    .image-preview-container {
        position: relative;
        display: inline-block;
        margin: 10px;
    }
    .image-preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #ddd;
    }
    .remove-image {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        z-index: 10;
    }
    .dropzone-area {
        border: 2px dashed #5B914C;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
    }
    .dropzone-area:hover {
        background: #e9ecef;
        border-color: #4a7a3d;
    }
    .dropzone-area.dragover {
        background: #d4edda;
        border-color: #28a745;
    }
    .ck-editor__editable {
        min-height: 300px;
    }
    .tags-container {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px;
        min-height: 42px;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        cursor: text;
    }
    .tag-item {
        display: inline-flex;
        align-items: center;
        background: #5B914C;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.875rem;
    }
    .tag-item .remove-tag {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
        background: none;
        border: none;
        color: white;
        padding: 0;
        font-size: 1.2rem;
    }
    .tag-suggestions {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        width: 100%;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .tag-suggestion-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    .tag-suggestion-item:hover {
        background: #f8f9fa;
    }
    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
        padding: 5px;
    }
    .add-btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: #5B914C;
        color: white;
        border-radius: 50%;
        cursor: pointer;
        margin-left: 10px;
        transition: all 0.2s;
    }
    .add-btn-icon:hover {
        background: #4a7a3d;
        transform: scale(1.1);
    }

    /* Tags Styling */
    .tags-container {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px;
        min-height: 46px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        cursor: text;
        background: #fff;
        transition: border-color 0.15s ease-in-out;
    }

    .tags-container:hover {
        border-color: #5B914C;
    }

    .tags-container:focus-within {
        border-color: #5B914C;
        box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 4px 8px 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        gap: 6px;
        animation: tagSlideIn 0.2s ease-out;
        transition: all 0.2s;
    }

    @keyframes tagSlideIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .tag-item:hover {
        background: linear-gradient(135deg, #4a7a3d 0%, #3d6632 100%);
        transform: translateY(-1px);
    }

    .tag-item .tag-name {
        line-height: 1;
    }

    .tag-item .remove-tag {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1rem;
        line-height: 1;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .tag-item .remove-tag:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 150px;
        padding: 4px;
        font-size: 0.875rem;
    }

    .tag-suggestions {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1050;
        width: 100%;
        margin-top: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        animation: suggestionsSlideDown 0.2s ease-out;
    }

    @keyframes suggestionsSlideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tag-suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tag-suggestion-item:last-child {
        border-bottom: none;
    }

    .tag-suggestion-item:hover {
        background: #f8f9fa;
        padding-left: 20px;
    }

    .tag-suggestion-item i {
        color: #5B914C;
        font-size: 0.9rem;
    }

    .tag-suggestion-item.tag-create-new {
        background: #f0f7ed;
        font-weight: 500;
    }

    .tag-suggestion-item.tag-create-new:hover {
        background: #e1f0da;
    }

    .tag-suggestion-item.tag-create-new i {
        color: #4a7a3d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Create New Product</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <form id="productForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="session_id" id="sessionId" value="">

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">

                <!-- Basic Information -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>

                    <div class="mb-3">
                        <label class="form-label required-field">Product Name</label>
                        <input type="text" name="name" id="productName" class="form-control" placeholder="Enter product name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="productSlug" class="form-control" placeholder="Auto-generated from name">
                            <div class="form-text">Leave empty to auto-generate</div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">SKU</label>
                            <input type="text" name="sku" id="productSku" class="form-control" placeholder="Product SKU" required>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="generateSKU()">
                                <i class="bi bi-arrow-clockwise"></i> Generate SKU
                            </button>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" placeholder="Product barcode (optional)">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Type</label>
                        <input type="text" name="product_type" class="form-control" placeholder="e.g., simple, digital, etc." value="simple">
                        <div class="form-text">Simple product type by default</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="3" placeholder="Brief product description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" id="productDescription" class="form-control"></textarea>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-image"></i> Product Images</h5>

                    <div class="mb-3">
                        <label class="form-label">Main Image</label>
                        <input type="file" name="main_image" id="mainImage" class="form-control" accept="image/*">
                        <div class="form-text">Upload primary product image (max 2MB)</div>
                        <div id="mainImagePreview" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gallery Images</label>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Note:</strong> Images are uploaded immediately when selected. They will be linked to this product when you submit the form.
                        </div>
                        <div class="dropzone-area" id="dropzoneArea">
                            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #5B914C;"></i>
                            <p class="mb-2"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted mb-0">PNG, JPG, GIF, WEBP up to 2MB each</p>
                        </div>
                        <input type="file" name="images[]" id="galleryImages" class="d-none" accept="image/*" multiple>
                        <div id="galleryPreview" class="mt-3"></div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-search"></i> SEO Settings</h5>

                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" placeholder="SEO title" maxlength="60">
                        <div class="form-text">Recommended: 50-60 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" placeholder="SEO description" maxlength="160"></textarea>
                        <div class="form-text">Recommended: 150-160 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2, keyword3">
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Product Settings -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-gear"></i> Product Settings</h5>

                    <div class="mb-3">
                        <label class="form-label required-field">Status</label>
                        <select name="status_key_code" class="form-select" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status->key_code }}" {{ $status->key_code === 'PRODUCT_DRAFT' ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <div class="input-group">
                            <select name="category_id" id="categorySelect" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->indent }}{{ $category->title }}</option>
                                @endforeach
                            </select>
                            <span class="add-btn-icon" onclick="openAddCategoryModal()" title="Add New Category">
                                <i class="bi bi-plus"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <div class="input-group">
                            <select name="vendor_id" id="vendorSelect" class="form-select">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <span class="add-btn-icon" onclick="openAddVendorModal()" title="Add New Vendor">
                                <i class="bi bi-plus"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <div class="position-relative">
                            <div class="tags-container" id="tagsContainer">
                                <!-- Tags will be rendered here -->
                            </div>
                            <div class="tag-suggestions" id="tagSuggestions"></div>
                        </div>
                        <div class="form-text">Press Enter, comma, or Tab to add tags. Start typing to see suggestions.</div>
                        <input type="hidden" name="tags" id="tagsHiddenInput" value="[]">
                    </div>

                    <hr>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" id="isFeatured" class="form-check-input">
                        <label class="form-check-label" for="isFeatured">
                            <i class="bi bi-star"></i> Featured Product
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="show_on_home" id="showOnHome" class="form-check-input">
                        <label class="form-check-label" for="showOnHome">
                            <i class="bi bi-house"></i> Show on Homepage
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_available" id="isAvailable" class="form-check-input" checked>
                        <label class="form-check-label" for="isAvailable">
                            <i class="bi bi-check-circle"></i> Available for Purchase
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="track_inventory" id="trackInventory" class="form-check-input" checked>
                        <label class="form-check-label" for="trackInventory">
                            <i class="bi bi-box"></i> Track Inventory
                        </label>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-currency-dollar"></i> Pricing</h5>

                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select name="curency" class="form-select">
                            @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required-field">Regular Price</label>
                        <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sale Price</label>
                        <input type="number" name="sale_price" class="form-control" placeholder="0.00" step="0.01" min="0">
                        <div class="form-text">Leave empty if not on sale</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cost Price</label>
                        <input type="number" name="cost_price" class="form-control" placeholder="0.00" step="0.01" min="0">
                        <div class="form-text">Your cost (for profit calculation)</div>
                    </div>

                    <hr class="my-3">

                    <!-- TAX SETTINGS - ADD THIS SECTION -->
                    <h6 class="text-muted mb-3"><i class="bi bi-receipt"></i> Tax Settings</h6>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_taxable" id="isTaxable" class="form-check-input" checked>
                        <label class="form-check-label" for="isTaxable">
                            <i class="bi bi-calculator"></i> This product is taxable
                        </label>
                    </div>

                    <div id="taxSettingsSection">
                        <div class="mb-3">
                            <label class="form-label">Tax Type</label>
                            <select name="tax_type" id="taxType" class="form-select">
                                <option value="exclusive" selected>Tax Exclusive (Price + Tax)</option>
                                <option value="inclusive">Tax Inclusive (Price includes Tax)</option>
                            </select>
                            <div class="form-text" id="taxTypeHelp">
                                <small><strong>Exclusive:</strong> Tax will be added to the price</small><br>
                                <small><strong>Inclusive:</strong> Tax is already included in the price</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tax Percentage (%)</label>
                            <div class="input-group">
                                <input type="number" name="tax_percentage" id="taxPercentage" class="form-control" placeholder="0.00" step="0.01" min="0" max="100" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Enter tax rate (e.g., 15 for 15% tax)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tax Class</label>
                            <input type="text" name="tax_class" class="form-control" placeholder="e.g., Standard, Reduced, Zero">
                            <div class="form-text">Optional: Tax classification for reporting</div>
                        </div>

                        <!-- Tax Calculation Preview -->
                        <div class="alert alert-info" id="taxPreview" style="display: none;">
                            <strong>Tax Preview:</strong>
                            <div id="taxPreviewContent" class="mt-2 small"></div>
                        </div>
                    </div>
                </div>

                <!-- Stock Management -->
                <div class="form-section">
                    <h5 class="section-title"><i class="bi bi-box-seam"></i> Stock Management</h5>

                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" placeholder="0" min="0" value="0">
                        <div class="form-text">Current stock level</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control" placeholder="10" min="0" value="10">
                        <div class="form-text">Alert when stock reaches this level</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-save btn-lg">
                            <i class="bi bi-check-circle"></i> Create Product
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="saveDraft">
                            <i class="bi bi-save"></i> Save as Draft
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required-field">Category Name</label>
                        <input type="text" name="category_title" id="categoryTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select name="category_parent_id" class="form-select">
                            <option value="">None (Root Category)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->indent }}{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-filter">
                        <i class="bi bi-check"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addVendorForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required-field">Vendor Name</label>
                        <input type="text" name="vendor_name" id="vendorName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="vendor_email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="vendor_phone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-filter">
                        <i class="bi bi-check"></i> Add Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

<script>
    $(document).ready(function() {

    // Generate and store session ID
    const sessionId = generateUUID();
    $('#sessionId').val(sessionId);

    // Initialize CKEditor
    let descriptionEditor;
    ClassicEditor
        .create(document.querySelector('#productDescription'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo']
        })
        .then(editor => {
            descriptionEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    // Auto-generate slug from product name
    $('#productName').on('keyup', function() {
        let name = $(this).val();
        let slug = name.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        $('#productSlug').val(slug);
    });

    // Main image preview
    $('#mainImage').on('change', function(e) {
        let file = e.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#mainImagePreview').html(`
                    <div class="image-preview-container">
                        <img src="${e.target.result}" class="image-preview" alt="Main Image">
                        <button type="button" class="remove-image" onclick="removeMainImage()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    // Gallery images - dropzone events
    const dropzone = $('#dropzoneArea');

    dropzone.on('click', function() {
        $('#galleryImages').click();
    });

    dropzone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropzone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    dropzone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');

        const files = e.originalEvent.dataTransfer.files;
        uploadImagesToServer(files);
    });

    // Gallery images change
    $('#galleryImages').on('change', function(e) {
        const files = e.target.files;
        uploadImagesToServer(files);
    });

    // Upload images immediately to server
    function uploadImagesToServer(files) {
        if (files.length === 0) return;

        const formData = new FormData();
        formData.append('session_id', sessionId);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        Array.from(files).forEach(file => {
            formData.append('images[]', file);
        });

        $.ajax({
            url: '/admin/products/temp-images/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Uploading Images...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    response.images.forEach(image => {
                        $('#galleryPreview').append(`
                            <div class="image-preview-container" data-path="${image.path}">
                                <img src="${image.url}" class="image-preview" alt="${image.name}">
                                <button type="button" class="remove-image" onclick="deleteGalleryImage('${image.path}', this)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        `);
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Uploaded!',
                        text: `${response.images.length} image(s) uploaded successfully`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to upload images', 'error');
            }
        });
    }

    // ==================== TAGS FUNCTIONALITY ====================
    let selectedTags = [];
    let tagSearchTimeout;
    let currentTagInput = null;

    // Initialize tags container
    function initializeTags() {
        renderTags();
        attachTagInputEvents();
    }

    // Render tags
    function renderTags() {
        let html = '';

        selectedTags.forEach((tag, index) => {
            html += `
                <span class="tag-item" data-index="${index}">
                    <span class="tag-name">${escapeHtml(tag.name)}</span>
                    <button type="button" class="remove-tag" data-index="${index}" title="Remove tag">×</button>
                </span>
            `;
        });

        html += '<input type="text" class="tag-input" id="tagInput" placeholder="Add tags..." autocomplete="off">';

        $('#tagsContainer').html(html);

        // Update hidden input
        const tagIds = selectedTags.map(t => t.id);
        $('#tagsHiddenInput').val(JSON.stringify(tagIds));

        // Reattach events after rendering
        attachTagInputEvents();

        // Focus the new input
        currentTagInput = $('#tagInput');
    }

    // Attach events to tag input
    function attachTagInputEvents() {
        currentTagInput = $('#tagInput');

        // Remove tag button click
        $('.remove-tag').off('click').on('click', function(e) {
            e.stopPropagation();
            const index = parseInt($(this).data('index'));
            removeTagByIndex(index);
        });

        // Tag input events
        currentTagInput.off().on({
            'keydown': function(e) {
                const value = $(this).val().trim();

                // Enter, Comma, or Tab - Add tag
                if (e.key === 'Enter' || e.key === ',' || e.key === 'Tab') {
                    e.preventDefault();
                    if (value) {
                        addNewTag(value.replace(/,/g, ''));
                        $(this).val('');
                        $('#tagSuggestions').hide();
                    }
                    return false;
                }

                // Backspace on empty input - Remove last tag
                if (e.key === 'Backspace' && value === '' && selectedTags.length > 0) {
                    e.preventDefault();
                    removeTagByIndex(selectedTags.length - 1);
                }

                // Escape - Close suggestions
                if (e.key === 'Escape') {
                    $('#tagSuggestions').hide();
                }
            },
            'keyup': function(e) {
                // Don't search on special keys
                if (['Enter', 'Tab', 'Escape', 'ArrowUp', 'ArrowDown', ','].includes(e.key)) {
                    return;
                }

                const value = $(this).val().trim();

                // Search for tags
                if (value.length >= 2) {
                    clearTimeout(tagSearchTimeout);
                    tagSearchTimeout = setTimeout(() => searchTags(value), 300);
                } else {
                    $('#tagSuggestions').hide();
                }
            },
            'blur': function() {
                // Delay to allow click on suggestions
                setTimeout(() => {
                    $('#tagSuggestions').hide();
                }, 200);
            },
            'focus': function() {
                const value = $(this).val().trim();
                if (value.length >= 2) {
                    searchTags(value);
                }
            }
        });
    }

    // Search tags from server
    function searchTags(query) {
        $.ajax({
            url: '{{ route("admin.products.tags.search") }}',
            data: { q: query },
            method: 'GET',
            success: function(response) {
                if (response.success && response.tags.length > 0) {
                    displayTagSuggestions(response.tags, query);
                } else {
                    // Show "Create new tag" option
                    displayCreateNewTagOption(query);
                }
            },
            error: function() {
                $('#tagSuggestions').hide();
            }
        });
    }

    // Display tag suggestions
    function displayTagSuggestions(tags, query) {
        let html = '';
        let hasResults = false;

        tags.forEach(tag => {
            // Don't show already selected tags
            if (!selectedTags.find(t => t.id === tag.id)) {
                html += `
                    <div class="tag-suggestion-item" data-id="${tag.id}" data-name="${escapeHtml(tag.name)}">
                        <i class="bi bi-tag"></i> ${escapeHtml(tag.name)}
                    </div>
                `;
                hasResults = true;
            }
        });

        // Add "Create new" option if query doesn't match exactly
        const exactMatch = tags.find(t => t.name.toLowerCase() === query.toLowerCase());
        if (!exactMatch) {
            html += `
                <div class="tag-suggestion-item tag-create-new" data-name="${escapeHtml(query)}">
                    <i class="bi bi-plus-circle"></i> Create "<strong>${escapeHtml(query)}</strong>"
                </div>
            `;
            hasResults = true;
        }

        if (hasResults) {
            $('#tagSuggestions').html(html).show();
            attachSuggestionEvents();
        } else {
            $('#tagSuggestions').hide();
        }
    }

    // Display create new tag option
    function displayCreateNewTagOption(query) {
        const html = `
            <div class="tag-suggestion-item tag-create-new" data-name="${escapeHtml(query)}">
                <i class="bi bi-plus-circle"></i> Create "<strong>${escapeHtml(query)}</strong>"
            </div>
        `;
        $('#tagSuggestions').html(html).show();
        attachSuggestionEvents();
    }

    // Attach events to suggestions
    function attachSuggestionEvents() {
        $('.tag-suggestion-item').off('click').on('click', function() {
            const tagId = $(this).data('id');
            const tagName = $(this).data('name');

            if (tagId) {
                // Existing tag
                addExistingTag(tagId, tagName);
            } else {
                // Create new tag
                addNewTag(tagName);
            }

            currentTagInput.val('');
            $('#tagSuggestions').hide();
            currentTagInput.focus();
        });
    }

    // Add existing tag
    function addExistingTag(id, name) {
        // Check if tag already added
        if (selectedTags.find(t => t.id === id)) {
            showNotification('Tag already added', 'info');
            return;
        }

        selectedTags.push({ id: id, name: name });
        renderTags();
        showNotification('Tag added', 'success');
    }

    // Add new tag (create on server)
    function addNewTag(name) {
        name = name.trim();

        if (!name) return;

        // Check if tag with same name already exists
        if (selectedTags.find(t => t.name.toLowerCase() === name.toLowerCase())) {
            showNotification('Tag already added', 'info');
            return;
        }

        // Show loading
        const loadingToast = Swal.fire({
            title: 'Creating tag...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("admin.products.tags.create") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name
            },
            success: function(response) {
                loadingToast.close();

                if (response.success) {
                    selectedTags.push({
                        id: response.tag.id,
                        name: response.tag.name
                    });
                    renderTags();
                    showNotification('Tag created and added', 'success');
                } else {
                    showNotification(response.message || 'Failed to create tag', 'error');
                }
            },
            error: function(xhr) {
                loadingToast.close();
                showNotification(xhr.responseJSON?.message || 'Failed to create tag', 'error');
            }
        });
    }

    // Remove tag by index
    function removeTagByIndex(index) {
        if (index >= 0 && index < selectedTags.length) {
            const removedTag = selectedTags[index];
            selectedTags.splice(index, 1);
            renderTags();
            showNotification(`"${removedTag.name}" removed`, 'info');
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Escape HTML
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

    // Click on container focuses input
    $(document).on('click', '#tagsContainer', function(e) {
        if (e.target.id !== 'tagInput' && !$(e.target).hasClass('remove-tag')) {
            $('#tagInput').focus();
        }
    });

    // Close suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#tagsContainer, #tagSuggestions').length) {
            $('#tagSuggestions').hide();
        }
    });

    // Initialize on page load
    $(document).ready(function() {
        initializeTags();
    });

    // ==================== END TAGS FUNCTIONALITY ====================

    // Save as draft
    $('#saveDraft').on('click', function() {
        $('select[name="status_key_code"]').val('PRODUCT_DRAFT');
        $('#productForm').submit();
    });

    // Form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        // Get CKEditor content
        if (descriptionEditor) {
            $('textarea[name="description"]').val(descriptionEditor.getData());
        }

        let formData = new FormData(this);

        $.ajax({
            url: '/admin/products',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Creating Product...',
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
                        window.location.href = '/admin/products';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.close();

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').empty();

                    // Show errors
                    $.each(errors, function(field, messages) {
                        let input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form for errors'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to create product'
                    });
                }
            }
        });
    });

    // Add Category Form
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/admin/products/categories/quick-add',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                title: $('#categoryTitle').val(),
                parent_id: $('[name="category_parent_id"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#addCategoryModal').modal('hide');

                    // Add to select dropdown
                    const indent = response.category.full_path.split(' > ').length > 1 ? '— '.repeat(response.category.full_path.split(' > ').length - 1) : '';
                    $('#categorySelect').append(`<option value="${response.category.id}" selected>${indent}${response.category.title}</option>`);

                    // Reset form
                    $('#addCategoryForm')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to add category', 'error');
            }
        });
    });

    // Add Vendor Form
    $('#addVendorForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/admin/products/vendors/quick-add',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: $('#vendorName').val(),
                email: $('[name="vendor_email"]').val(),
                phone: $('[name="vendor_phone"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#addVendorModal').modal('hide');

                    // Add to select dropdown
                    $('#vendorSelect').append(`<option value="${response.vendor.id}" selected>${response.vendor.name}</option>`);

                    // Reset form
                    $('#addVendorForm')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to add vendor', 'error');
            }
        });
    });

});

// ==================== HELPER FUNCTIONS ====================

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function generateSKU() {
    const sku = 'PRD-' + Math.random().toString(36).substring(2, 10).toUpperCase();
    $('#productSku').val(sku);

    Swal.fire({
        icon: 'success',
        title: 'SKU Generated!',
        text: sku,
        timer: 1000,
        showConfirmButton: false
    });
}

function removeMainImage() {
    $('#mainImage').val('');
    $('#mainImagePreview').empty();
}

function deleteGalleryImage(imagePath, button) {
    Swal.fire({
        title: 'Delete this image?',
        text: "This will remove the image from server",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/products/temp-images/delete',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    image_path: imagePath
                },
                success: function(response) {
                    if (response.success) {
                        $(button).closest('.image-preview-container').fadeOut(300, function() {
                            $(this).remove();
                        });

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Image removed successfully',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to delete image', 'error');
                }
            });
        }
    });
}

function openAddCategoryModal() {
    $('#addCategoryModal').modal('show');
}

function openAddVendorModal() {
    $('#addVendorModal').modal('show');
}

// Close tag suggestions when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#tagsContainer, #tagSuggestions').length) {
        $('#tagSuggestions').hide();
    }
});

// Focus tag input when clicking on tags container
$(document).on('click', '#tagsContainer', function() {
    $('#tagInput').focus();
});

//
// Tax Settings Toggle
$('#isTaxable').on('change', function() {
    if ($(this).is(':checked')) {
        $('#taxSettingsSection').slideDown();
    } else {
        $('#taxSettingsSection').slideUp();
    }
    updateTaxPreview();
});

// Update tax preview when values change
$('#regularPrice, #salePrice, #taxPercentage, #taxType').on('keyup change', function() {
    updateTaxPreview();
});

// Update tax preview
function updateTaxPreview() {
    if (!$('#isTaxable').is(':checked')) {
        $('#taxPreview').hide();
        return;
    }

    const price = parseFloat($('#salePrice').val()) || parseFloat($('#regularPrice').val()) || 0;
    const taxPercentage = parseFloat($('#taxPercentage').val()) || 0;
    const taxType = $('#taxType').val();
    const currency = $('select[name="curency"]').val() || 'USD';
    const symbol = currency_symbol(currency);

    if (price <= 0 || taxPercentage <= 0) {
        $('#taxPreview').hide();
        return;
    }

    let taxAmount, priceExcludingTax, priceIncludingTax;

    if (taxType === 'inclusive') {
        // Tax is included in price
        priceExcludingTax = price / (1 + (taxPercentage / 100));
        taxAmount = price - priceExcludingTax;
        priceIncludingTax = price;
    } else {
        // Tax is exclusive
        priceExcludingTax = price;
        taxAmount = price * (taxPercentage / 100);
        priceIncludingTax = price + taxAmount;
    }

    const html = `
        <p class="mb-1"><strong>Base Price:</strong> ${symbol} ${priceExcludingTax.toFixed(2)}</p>
        <p class="mb-1"><strong>Tax (${taxPercentage}%):</strong> ${symbol} ${taxAmount.toFixed(2)}</p>
        <p class="mb-0"><strong>Final Price:</strong> ${symbol} ${priceIncludingTax.toFixed(2)}</p>
    `;

    $('#taxPreviewContent').html(html);
    $('#taxPreview').slideDown();
}

// Helper function for currency symbol (if not already defined)
function currency_symbol(code) {
    const symbols = {
        'USD': '$', 'EUR': '€', 'GBP': '£', 'PKR': '₨', 'SAR': '﷼',
        'AED': 'د.إ', 'CAD': 'C$', 'AUD': 'A$', 'JPY': '¥', 'CNY': '¥', 'INR': '₹'
    };
    return symbols[code] || code;
}

// Initialize on page load
$(document).ready(function() {
    updateTaxPreview();
});
</script>
@endpush
