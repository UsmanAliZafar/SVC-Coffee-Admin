{{-- resources/views/admin/products/partials/features.blade.php --}}

<div class="form-section">
    <h5 class="section-title">
        <i class="bi bi-stars"></i> Product Features
    </h5>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Dynamic Features:</strong> Add unlimited custom sections to your product. Each section type can be added multiple times.
        {{-- <ul class="mb-0 mt-2">
            <li><strong>Rich Text:</strong> Full editor for formatted content</li>
            <li><strong>Multiline Text:</strong> Simple textarea for long content</li>
            <li><strong>Single Line Fields:</strong> Multiple label+value pairs in one section</li>
            <li><strong>Links List:</strong> Multiple links with titles in one section</li>
        </ul> --}}
    </div>

    <!-- Features Container -->
    <div id="featuresContainer">
        <!-- Features will be rendered here dynamically -->
    </div>

    <!-- Add Feature Button -->
    <div class="d-grid gap-2 mt-3">
        <button type="button" class="btn btn-outline-primary" id="addFeatureBtn">
            <i class="bi bi-plus-circle"></i> Add Feature Section
        </button>
    </div>

    <!-- Hidden input to store features JSON -->
    <input type="hidden" name="features" id="featuresHiddenInput" value="">
</div>

<!-- Feature Type Selection Modal -->
<div class="modal fade" id="featureTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #5B914C; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Select Feature Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Choose the type of feature section you want to add. You can add multiple sections of the same type.</p>

                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action feature-type-option"
                            data-type="rich_text">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-richtext fs-4 me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1">Rich Text Editor</h6>
                                <small class="text-muted">Full-featured text editor with formatting</small>
                            </div>
                        </div>
                    </button>

                    <button type="button" class="list-group-item list-group-item-action feature-type-option"
                            data-type="multiline_text">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-textarea-t fs-4 me-3 text-info"></i>
                            <div>
                                <h6 class="mb-1">Multiline Text</h6>
                                <small class="text-muted">Textarea for longer text content</small>
                            </div>
                        </div>
                    </button>

                    <button type="button" class="list-group-item list-group-item-action feature-type-option"
                            data-type="single_line">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-input-cursor-text fs-4 me-3 text-success"></i>
                            <div>
                                <h6 class="mb-1">Single Line Fields</h6>
                                <small class="text-muted">Add multiple label+value pairs in one section</small>
                            </div>
                        </div>
                    </button>

                    <button type="button" class="list-group-item list-group-item-action feature-type-option"
                            data-type="links_list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-link-45deg fs-4 me-3 text-warning"></i>
                            <div>
                                <h6 class="mb-1">Links List</h6>
                                <small class="text-muted">Add multiple links with titles in one section</small>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Feature Item Container */
    .feature-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
        transition: all 0.3s;
    }

    .feature-item:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Feature Header */
    .feature-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }

    .feature-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #5B914C;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .feature-remove-btn {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .feature-remove-btn:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    /* Modal Feature Type Options */
    .feature-type-option {
        cursor: pointer;
        transition: all 0.2s;
        border-left: 3px solid transparent;
        margin-bottom: 0.5rem;
    }

    .feature-type-option:hover {
        background-color: #f8f9fa;
        border-left-color: #5B914C;
    }

    /* Single Line Fields & Links List Styles */
    .items-container {
        min-height: 60px;
    }

    .single-item, .link-item {
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 10px;
        position: relative;
    }

    .item-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .item-remove:hover {
        background: #c82333;
    }

    .add-item-btn {
        background: #5B914C;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .add-item-btn:hover {
        background: #4a7a3d;
    }

    /* CKEditor Styles */
    .ck-editor__editable {
        min-height: 200px;
    }

    /* Feature counter badge */
    .feature-count-badge {
        display: inline-block;
        background: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
        margin-left: 5px;
    }
</style>
@endpush

@push('scripts')
<script>
// Global variables for features management
let productFeatures = [];
let featureEditors = {}; // Store CKEditor instances
let featureIdCounter = 0;

// Initialize features system when document is ready
$(document).ready(function() {
    initializeFeatures();

    // Load existing features if editing product
    @if(isset($product) && $product->features)
        loadExistingFeatures({!! json_encode($product->features) !!});
    @endif
});

/**
 * Initialize features system
 */
function initializeFeatures() {
    // Add Feature button click
    $('#addFeatureBtn').on('click', function() {
        openFeatureTypeModal();
    });

    // Feature type selection
    $('.feature-type-option').on('click', function() {
        const type = $(this).data('type');

        $('#featureTypeModal').modal('hide');
        addFeature(type);
    });
}

/**
 * Open feature type selection modal
 */
function openFeatureTypeModal() {
    $('#featureTypeModal').modal('show');
}

/**
 * Add new feature
 */
function addFeature(type) {
    const featureId = `feature_${featureIdCounter++}`;

    const feature = {
        id: featureId,
        type: type,
        label: '',
        value: (type === 'links_list' || type === 'single_line') ? [] : ''
    };

    productFeatures.push(feature);
    renderFeature(feature);
    updateFeaturesInput();

    // Show success message
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: 'success',
        title: `${getFeatureTypeName(type)} section added`
    });
}

/**
 * Render feature HTML
 */
function renderFeature(feature) {
    // Count same type features
    const sameTypeCount = productFeatures.filter(f => f.type === feature.type).length;

    let html = `
        <div class="feature-item" data-feature-id="${feature.id}">
            <div class="feature-header">
                <div>
                    <span class="feature-type-badge">
                        ${getFeatureIcon(feature.type)}
                        ${getFeatureTypeName(feature.type)}
                    </span>
                    ${sameTypeCount > 1 ? `<span class="feature-count-badge">#${sameTypeCount}</span>` : ''}
                </div>
                <button type="button" class="feature-remove-btn" onclick="removeFeature('${feature.id}')">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Section Label/Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control feature-label"
                       placeholder="e.g., Warranty Information, Specifications, Technical Details, etc."
                       value="${escapeHtml(feature.label || '')}"
                       onchange="updateFeatureLabel('${feature.id}', this.value)">
            </div>

            ${renderFeatureInput(feature)}
        </div>
    `;

    $('#featuresContainer').append(html);

    // Initialize CKEditor for Rich Text
    if (feature.type === 'rich_text') {
        setTimeout(() => {
            initializeRichTextEditor(feature.id, feature.value);
        }, 100);
    }
}

/**
 * Render feature input based on type
 */
function renderFeatureInput(feature) {
    switch(feature.type) {
        case 'rich_text':
            return `
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea id="richtext_${feature.id}" class="form-control">${escapeHtml(feature.value || '')}</textarea>
                </div>
            `;

        case 'multiline_text':
            return `
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea class="form-control feature-value" rows="6"
                              placeholder="Enter content"
                              onchange="updateFeatureValue('${feature.id}', this.value)">${escapeHtml(feature.value || '')}</textarea>
                </div>
            `;

        case 'single_line':
            return `
                <div class="mb-3">
                    <label class="form-label">Text Fields</label>
                    <div id="items_${feature.id}" class="items-container">
                        ${renderSingleLineItems(feature.id, feature.value)}
                    </div>
                    <button type="button" class="add-item-btn mt-2" onclick="addSingleLineItem('${feature.id}')">
                        <i class="bi bi-plus"></i> Add Text Field
                    </button>
                </div>
            `;

        case 'links_list':
            return `
                <div class="mb-3">
                    <label class="form-label">Links</label>
                    <div id="items_${feature.id}" class="items-container">
                        ${renderLinks(feature.id, feature.value)}
                    </div>
                    <button type="button" class="add-item-btn mt-2" onclick="addLink('${feature.id}')">
                        <i class="bi bi-plus"></i> Add Link
                    </button>
                </div>
            `;

        default:
            return '';
    }
}

/**
 * Render single line items
 */
function renderSingleLineItems(featureId, items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<p class="text-muted mb-0">No text fields added yet. Click "Add Text Field" to start.</p>';
    }

    let html = '';
    items.forEach((item, index) => {
        html += `
            <div class="single-item" data-item-index="${index}">
                <button type="button" class="item-remove" onclick="removeSingleLineItem('${featureId}', ${index})">
                    <i class="bi bi-x"></i>
                </button>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control form-control-sm"
                               placeholder="Field Label (e.g., Brand, Model)"
                               value="${escapeHtml(item.label || '')}"
                               onchange="updateSingleLineItem('${featureId}', ${index}, 'label', this.value)">
                    </div>
                    <div class="col-md-8 mb-2">
                        <input type="text" class="form-control form-control-sm"
                               placeholder="Field Value"
                               value="${escapeHtml(item.value || '')}"
                               onchange="updateSingleLineItem('${featureId}', ${index}, 'value', this.value)">
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

/**
 * Render links for links_list type
 */
function renderLinks(featureId, links) {
    if (!Array.isArray(links) || links.length === 0) {
        return '<p class="text-muted mb-0">No links added yet. Click "Add Link" to start.</p>';
    }

    let html = '';
    links.forEach((link, index) => {
        html += `
            <div class="link-item" data-link-index="${index}">
                <button type="button" class="item-remove" onclick="removeLink('${featureId}', ${index})">
                    <i class="bi bi-x"></i>
                </button>
                <div class="row">
                    <div class="col-md-5 mb-2">
                        <input type="text" class="form-control form-control-sm"
                               placeholder="Link Title"
                               value="${escapeHtml(link.title || '')}"
                               onchange="updateLink('${featureId}', ${index}, 'title', this.value)">
                    </div>
                    <div class="col-md-7 mb-2">
                        <input type="url" class="form-control form-control-sm"
                               placeholder="https://example.com"
                               value="${escapeHtml(link.url || '')}"
                               onchange="updateLink('${featureId}', ${index}, 'url', this.value)">
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

/**
 * Initialize CKEditor for Rich Text
 */
function initializeRichTextEditor(featureId, content) {
    const editorElement = document.querySelector(`#richtext_${featureId}`);

    if (!editorElement) {
        console.error('Editor element not found for:', featureId);
        return;
    }

    ClassicEditor
        .create(editorElement, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', '|', 'undo', 'redo']
        })
        .then(editor => {
            featureEditors[featureId] = editor;

            // Set initial content
            if (content) {
                editor.setData(content);
            }

            // Update value on change
            editor.model.document.on('change:data', () => {
                updateFeatureValue(featureId, editor.getData());
            });
        })
        .catch(error => {
            console.error('CKEditor initialization error:', error);
        });
}

/**
 * Add single line item
 */
function addSingleLineItem(featureId) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature) {
        if (!Array.isArray(feature.value)) {
            feature.value = [];
        }

        feature.value.push({ label: '', value: '' });

        // Re-render items
        $(`#items_${featureId}`).html(renderSingleLineItems(featureId, feature.value));
        updateFeaturesInput();
    }
}

/**
 * Remove single line item
 */
function removeSingleLineItem(featureId, itemIndex) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature && Array.isArray(feature.value)) {
        feature.value.splice(itemIndex, 1);

        // Re-render items
        $(`#items_${featureId}`).html(renderSingleLineItems(featureId, feature.value));
        updateFeaturesInput();
    }
}

/**
 * Update single line item
 */
function updateSingleLineItem(featureId, itemIndex, field, value) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature && Array.isArray(feature.value) && feature.value[itemIndex]) {
        feature.value[itemIndex][field] = value;
        updateFeaturesInput();
    }
}

/**
 * Add link to links_list feature
 */
function addLink(featureId) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature) {
        if (!Array.isArray(feature.value)) {
            feature.value = [];
        }

        feature.value.push({ title: '', url: '' });

        // Re-render links
        $(`#items_${featureId}`).html(renderLinks(featureId, feature.value));
        updateFeaturesInput();
    }
}

/**
 * Remove link from links_list feature
 */
function removeLink(featureId, linkIndex) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature && Array.isArray(feature.value)) {
        feature.value.splice(linkIndex, 1);

        // Re-render links
        $(`#items_${featureId}`).html(renderLinks(featureId, feature.value));
        updateFeaturesInput();
    }
}

/**
 * Update link in links_list feature
 */
function updateLink(featureId, linkIndex, field, value) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature && Array.isArray(feature.value) && feature.value[linkIndex]) {
        feature.value[linkIndex][field] = value;
        updateFeaturesInput();
    }
}

/**
 * Update feature label
 */
function updateFeatureLabel(featureId, label) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature) {
        feature.label = label;
        updateFeaturesInput();
    }
}

/**
 * Update feature value
 */
function updateFeatureValue(featureId, value) {
    const feature = productFeatures.find(f => f.id === featureId);
    if (feature) {
        feature.value = value;
        updateFeaturesInput();
    }
}

/**
 * Remove feature
 */
function removeFeature(featureId) {
    Swal.fire({
        title: 'Remove this feature section?',
        text: "This will remove the entire section and all items within it",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Remove from array
            const index = productFeatures.findIndex(f => f.id === featureId);
            if (index !== -1) {
                // Destroy CKEditor instance if exists
                if (featureEditors[featureId]) {
                    featureEditors[featureId].destroy();
                    delete featureEditors[featureId];
                }

                productFeatures.splice(index, 1);
            }

            // Remove from DOM
            $(`.feature-item[data-feature-id="${featureId}"]`).fadeOut(300, function() {
                $(this).remove();
            });

            updateFeaturesInput();

            Swal.fire({
                icon: 'success',
                title: 'Removed!',
                text: 'Feature section has been removed.',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

/**
 * Update hidden input with features JSON
 */
function updateFeaturesInput() {
    // Prepare features for saving (remove internal IDs)
    const featuresToSave = productFeatures.map(f => ({
        type: f.type,
        label: f.label,
        value: f.value
    }));

    $('#featuresHiddenInput').val(JSON.stringify(featuresToSave));
}

/**
 * Load existing features (for edit mode)
 */
function loadExistingFeatures(features) {
    if (!Array.isArray(features)) return;

    features.forEach(feature => {
        const featureId = `feature_${featureIdCounter++}`;

        const featureObj = {
            id: featureId,
            type: feature.type,
            label: feature.label || '',
            value: feature.value || ((feature.type === 'links_list' || feature.type === 'single_line') ? [] : '')
        };

        productFeatures.push(featureObj);
        renderFeature(featureObj);
    });

    updateFeaturesInput();
}

/**
 * Helper: Get feature icon
 */
function getFeatureIcon(type) {
    const icons = {
        'rich_text': '<i class="bi bi-file-richtext"></i>',
        'single_line': '<i class="bi bi-input-cursor-text"></i>',
        'multiline_text': '<i class="bi bi-textarea-t"></i>',
        'links_list': '<i class="bi bi-link-45deg"></i>'
    };
    return icons[type] || '<i class="bi bi-star"></i>';
}

/**
 * Helper: Get feature type name
 */
function getFeatureTypeName(type) {
    const names = {
        'rich_text': 'Rich Text',
        'single_line': 'Single Line Fields',
        'multiline_text': 'Multiline Text',
        'links_list': 'Links List'
    };
    return names[type] || type;
}

/**
 * Helper: Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

/**
 * Before form submit - Update all CKEditor instances
 * Call this function before submitting the product form
 */
window.beforeProductFormSubmit = function() {
    Object.keys(featureEditors).forEach(featureId => {
        const editor = featureEditors[featureId];
        if (editor) {
            updateFeatureValue(featureId, editor.getData());
        }
    });
};
</script>
@endpush
