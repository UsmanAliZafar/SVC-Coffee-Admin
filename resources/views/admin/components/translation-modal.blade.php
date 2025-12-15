{{--
    Reusable Translation Modal Component

    Usage in any edit form:
    @include('admin.components.translation-modal', [
        'module' => 'product',
        'itemId' => $product->id,
        'itemName' => $product->name
    ])
--}}
@php
    $modalId = 'translationModal_' . $module . '_' . str_replace('-', '_', $itemId);
    $safeModule = $module;
    $safeItemId = str_replace('-', '_', $itemId);
    $safeItemName = isset($itemName) ? addslashes($itemName) : 'Item';
@endphp
<style>
    .swal2-popup {
        border-top: 3px solid #5B914C;
    }

    .swal2-timer-progress-bar {
        background-color: #5B914C;
    }
</style>
<!-- Translation Button (Floating Action Button Style) -->
<div class="translation-fab-container">
    <button type="button"
            class="btn translation-fab-btn"
            data-bs-toggle="modal"
            data-bs-target="#{{ $modalId }}"
            title="Manage Translations">
        <i class="bi bi-translate"></i>
        <span class="fab-text">Translations</span>
    </button>
</div>

<!-- Translation Modal -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); color: white;">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="bi bi-translate"></i>
                    Manage Translations: <strong>{{ $safeItemName }}</strong>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="row g-0" style="min-height: 500px;">
                    <!-- Left Sidebar - Language Selection -->
                    <div class="col-md-3 border-end" style="background-color: #f8f9fa;">
                        <div class="p-3">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-list"></i> Available Languages
                            </h6>

                            <div class="list-group" id="languagesList_{{ $safeModule }}_{{ $safeItemId }}">
                                <!-- Languages will be loaded here via AJAX -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2 mb-0 small">Loading languages...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Content - Translation Form -->
                    <div class="col-md-9">
                        <div class="p-4">
                            <!-- Translation Form Header -->
                            <div id="translationFormHeader_{{ $safeModule }}_{{ $safeItemId }}" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h5 class="mb-1">
                                            <span id="currentLanguageFlag_{{ $safeModule }}_{{ $safeItemId }}"></span>
                                            <span id="currentLanguageName_{{ $safeModule }}_{{ $safeItemId }}"></span>
                                        </h5>
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-info-circle"></i>
                                            Translate the fields below. Leave empty to use English version.
                                        </p>
                                    </div>
                                    <div>
                                        <span class="badge bg-info" id="completionBadge_{{ $safeModule }}_{{ $safeItemId }}">
                                            0% Complete
                                        </span>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="btn-group mb-4" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="clearCurrentTranslation_{{ $safeModule }}_{{ $safeItemId }}()">
                                        <i class="bi bi-eraser"></i> Clear All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            onclick="openCloneModal_{{ $safeModule }}_{{ $safeItemId }}()">
                                        <i class="bi bi-files"></i> Clone from...
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteCurrentTranslation_{{ $safeModule }}_{{ $safeItemId }}()">
                                        <i class="bi bi-trash"></i> Delete Translation
                                    </button>
                                </div>
                            </div>

                            <!-- Translation Form -->
                            <form id="translationForm_{{ $safeModule }}_{{ $safeItemId }}" style="display: none;">
                                <div id="translationFields_{{ $safeModule }}_{{ $safeItemId }}">
                                    <!-- Fields will be dynamically loaded here -->
                                </div>

                                <div class="mt-4 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> Auto-saved
                                    </small>
                                    <div>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x"></i> Close
                                        </button>
                                        <button type="submit" class="btn btn-success" id="saveTranslationBtn_{{ $safeModule }}_{{ $safeItemId }}">
                                            <span class="btn-text">
                                                <i class="bi bi-check-circle"></i> Save Translation
                                            </span>
                                            <span class="btn-loading" style="display: none;">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Welcome Message (shown initially) -->
                            <div id="welcomeMessage_{{ $safeModule }}_{{ $safeItemId }}" class="text-center py-5">
                                <i class="bi bi-translate" style="font-size: 4rem; color: #5B914C; opacity: 0.3;"></i>
                                <h5 class="mt-3 text-muted">Select a language to start translating</h5>
                                <p class="text-muted">Choose a language from the list on the left</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Translation Modal (Nested) -->
<div class="modal fade" id="cloneModal_{{ $safeModule }}_{{ $safeItemId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-files"></i> Clone Translation
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Copy translations from one language to another</p>

                <div class="mb-3">
                    <label class="form-label">From Language</label>
                    <select id="cloneFromLang_{{ $safeModule }}_{{ $safeItemId }}" class="form-select">
                        <option value="">Select source language...</option>
                    </select>
                </div>

                <div class="text-center my-3">
                    <i class="bi bi-arrow-down" style="font-size: 1.5rem; color: #5B914C;"></i>
                </div>

                <div class="mb-3">
                    <label class="form-label">To Language</label>
                    <select id="cloneToLang_{{ $safeModule }}_{{ $safeItemId }}" class="form-select">
                        <option value="">Select target language...</option>
                    </select>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <small>This will overwrite existing translations in the target language.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeClone_{{ $safeModule }}_{{ $safeItemId }}()">
                    <i class="bi bi-files"></i> Clone Translation
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Floating Action Button */
.translation-fab-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.translation-fab-btn {
    background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(91, 145, 76, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.translation-fab-btn:hover {
    background: linear-gradient(135deg, #4a7a3d 0%, #3d6432 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(91, 145, 76, 0.6);
}

.translation-fab-btn i {
    font-size: 24px;
}

.translation-fab-btn .fab-text {
    font-size: 14px;
}

/* Language List Items */
.language-list-item {
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.language-list-item:hover {
    background-color: #e9ecef;
    border-left-color: #5B914C;
}

.language-list-item.active {
    background-color: #5B914C;
    color: white;
    border-left-color: #4a7a3d;
}

.language-list-item .completion-badge {
    font-size: 11px;
    padding: 2px 8px;
}

/* Translation Form Fields */
.translation-field-group {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.translation-field-group:last-child {
    border-bottom: none;
}

.field-comparison {
    background-color: #f8f9fa;
    border-left: 3px solid #5B914C;
    padding: 10px 15px;
    margin-top: 8px;
    border-radius: 4px;
}

.field-comparison small {
    color: #6c757d;
}

/* RTL Support */
.rtl-field {
    direction: rtl;
    text-align: right;
}

/* Loading State */
.translation-loading {
    text-align: center;
    padding: 40px;
}

.translation-loading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3em;
}

/* Modal Enhancements */
.modal-xl {
    max-width: 1200px;
}

@media (max-width: 768px) {
    .translation-fab-btn .fab-text {
        display: none;
    }

    .translation-fab-btn {
        border-radius: 50%;
        padding: 15px;
        width: 60px;
        height: 60px;
        justify-content: center;
    }
}
/* CKEditor in Translation Modal */
.translation-editor-full .ck-editor__editable {
    min-height: 300px;
}

.translation-editor-simple .ck-editor__editable {
    min-height: 100px;
}

.ck-editor__editable.ck-blurred .ck-placeholder::before {
    color: #adb5bd;
}

/* RTL Support for CKEditor */
.ck-editor__editable[dir="rtl"] {
    text-align: right;
}
/* Save Button Loading State */
#saveTranslationBtn_{{ $safeModule }}_{{ $safeItemId }}:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-loading .spinner-border {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}

.btn-success:disabled {
    background-color: #5B914C;
    border-color: #5B914C;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
<script>
(function() {
    'use strict';

    console.log('🔍 Translation Modal Script Loading...');

    // Safe identifiers
    const safeModule = '{{ $safeModule }}';
    const safeItemId = '{{ $safeItemId }}';
    const actualItemId = '{{ $itemId }}';
    const managerName = 'TranslationManager_' + safeModule + '_' + safeItemId;

    // Initialize Translation Manager for this module and item
    window[managerName] = {
        module: safeModule,
        itemId: actualItemId,
        currentLang: null,
        languages: {},
        fields: [],
        translations: {},
        originalValues: {},
        stats: {},

        // Initialize when modal opens
        init: async function() {
            console.log('🚀 Initializing Translation Manager', {
                module: this.module,
                itemId: this.itemId
            });

            try {
                // Load available languages
                console.log('📋 Loading languages...');
                await this.loadLanguages();
                console.log('✅ Languages loaded:', this.languages);

                // Load translatable fields for this module
                console.log('📝 Loading fields...');
                await this.loadFields();
                console.log('✅ Fields loaded:', this.fields);

                // Load existing translation stats
                console.log('📊 Loading stats...');
                await this.loadStats();
                console.log('✅ Stats loaded');

            } catch (error) {
                console.error('❌ Translation Manager Init Error:', error);
                this.showError('Failed to initialize: ' + error.message);
            }
        },

        // Initialize CKEditor for translation fields
        initializeTranslationEditors: function(lang) {
            const manager = this;
            const isRTL = this.languages[lang].direction === 'rtl';

            // Destroy existing editors first
            if (window.translationEditors) {
                Object.values(window.translationEditors).forEach(editor => {
                    if (editor) editor.destroy();
                });
            }
            window.translationEditors = {};

            // Initialize full editors (for description field)
            document.querySelectorAll('.translation-editor-full').forEach(textarea => {
                ClassicEditor
                    .create(textarea, {
                        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
                        language: {
                            ui: 'en',
                            content: isRTL ? 'ar' : 'en'
                        }
                    })
                    .then(editor => {
                        window.translationEditors[textarea.id] = editor;
                    })
                    .catch(error => console.error(error));
            });

            // Initialize simple editors (for short_description, meta_description, etc.)
            document.querySelectorAll('.translation-editor-simple').forEach(textarea => {
                ClassicEditor
                    .create(textarea, {
                        toolbar: ['bold', 'italic', 'link', '|', 'undo', 'redo'],
                        language: {
                            ui: 'en',
                            content: isRTL ? 'ar' : 'en'
                        }
                    })
                    .then(editor => {
                        window.translationEditors[textarea.id] = editor;
                    })
                    .catch(error => console.error(error));
            });
        },

        // Load available languages
        loadLanguages: async function() {
            try {
                const response = await TranslationAPI.getAvailableLanguages();
                console.log('📡 Languages API Response:', response);

                if (!response.success) {
                    throw new Error('API returned success=false');
                }

                this.languages = response.data;
                this.renderLanguagesList();
            } catch (error) {
                console.error('❌ Load languages error:', error);
                const container = document.getElementById('languagesList_' + safeModule + '_' + safeItemId);
                if (container) {
                    container.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <strong>Error loading languages</strong>
                            <p class="mb-0 small">${error.message}</p>
                            <p class="mb-0 small">Check browser console (F12) for details</p>
                        </div>
                    `;
                }
                throw error;
            }
        },

        // Load translatable fields
        loadFields: async function() {
            try {
                const response = await TranslationAPI.getTranslatableFields(this.module);
                console.log('📡 Fields Response:', response);

                if (!response.success) {
                    throw new Error('API returned success=false');
                }

                this.fields = response.data.fields;
            } catch (error) {
                console.error('❌ Load fields error:', error);
                throw error;
            }
        },

        // Load stats
        loadStats: async function() {
            try {
                const response = await TranslationAPI.getStats(this.module, this.itemId);
                console.log('📡 Stats Response:', response);

                if (response.success) {
                    this.updateLanguagesWithStats(response.data);
                }
            } catch (error) {
                console.error('⚠️ Load stats error (non-critical):', error);
            }
        },

        // Render languages list
        renderLanguagesList: function() {
            console.log('🎨 Rendering languages list...');
            const container = document.getElementById('languagesList_' + safeModule + '_' + safeItemId);

            if (!this.languages || Object.keys(this.languages).length === 0) {
                console.warn('⚠️ No languages to render');
                container.innerHTML = '<div class="alert alert-warning m-3"><p class="mb-0">No languages available</p></div>';
                return;
            }

            let html = '';
            for (const [code, lang] of Object.entries(this.languages)) {
                const completion = this.getCompletionForLanguage(code);
                const badgeClass = completion >= 100 ? 'bg-success' : completion >= 50 ? 'bg-warning' : 'bg-secondary';

                html += `
                    <div class="list-group-item list-group-item-action language-list-item"
                         data-lang="${code}"
                         onclick="${managerName}.selectLanguage('${code}')">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span style="font-size: 20px; margin-right: 8px;">${lang.flag}</span>
                                <strong>${lang.name}</strong>
                                <br>
                                <small class="text-muted">${lang.native_name}</small>
                            </div>
                            <span class="badge ${badgeClass} completion-badge">
                                ${Math.round(completion)}%
                            </span>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;
            console.log('✅ Languages rendered successfully');
        },

        // Select language
        selectLanguage: async function(lang) {
            console.log('🔤 Selecting language:', lang);
            this.currentLang = lang;

            const items = document.querySelectorAll('#languagesList_' + safeModule + '_' + safeItemId + ' .language-list-item');
            items.forEach(item => item.classList.remove('active'));

            const selectedItem = document.querySelector('[data-lang="' + lang + '"]');
            if (selectedItem) selectedItem.classList.add('active');

            document.getElementById('translationFormHeader_' + safeModule + '_' + safeItemId).style.display = 'block';
            document.getElementById('translationForm_' + safeModule + '_' + safeItemId).style.display = 'block';
            document.getElementById('welcomeMessage_' + safeModule + '_' + safeItemId).style.display = 'none';

            const langData = this.languages[lang];
            document.getElementById('currentLanguageFlag_' + safeModule + '_' + safeItemId).textContent = langData.flag;
            document.getElementById('currentLanguageName_' + safeModule + '_' + safeItemId).textContent = langData.name + ' (' + langData.native_name + ')';

            await this.loadTranslations(lang);
            this.renderFormFields(lang);
            this.initializeTranslationEditors(lang);
        },

        // Load translations
        loadTranslations: async function(lang) {
            try {
                const response = await TranslationAPI.getTranslations(this.module, this.itemId, lang);
                this.translations = response.data.translations;
                this.originalValues = response.data.original_values;
                this.updateCompletionBadge(response.data.completion);
            } catch (error) {
                console.error('❌ Load translations error:', error);
                this.translations = {};
            }
        },

        // Render form fields
        renderFormFields: function(lang) {
            const container = document.getElementById('translationFields_' + safeModule + '_' + safeItemId);
            const langData = this.languages[lang];
            const isRTL = langData.direction === 'rtl';

            let html = '';

            this.fields.forEach(field => {
                const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const value = (this.translations[field] || '').replace(/"/g, '&quot;');
                const originalValue = (this.originalValues[field] || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const fieldId = 'translation_' + field + '_' + lang;

                const isTextarea = ['description', 'content', 'short_description', 'meta_description'].includes(field);
                const rows = field === 'description' ? 8 : 3;

                html += '<div class="translation-field-group">';
                html += '<label for="' + fieldId + '" class="form-label">';
                html += '<strong>' + fieldName + '</strong>';
                if (isRTL) html += '<span class="badge bg-info ms-2">RTL</span>';
                html += '</label>';

                if (isTextarea) {
                    const editorClass = field === 'description' ? 'translation-editor-full' : 'translation-editor-simple';
                    html += '<textarea id="' + fieldId + '" name="' + field + '" class="form-control ' + editorClass + ' ' + (isRTL ? 'rtl-field' : '') + '" rows="' + rows + '" placeholder="Enter ' + fieldName + ' in ' + langData.name + '" dir="' + langData.direction + '">' + value + '</textarea>';
                } else {
                    html += '<input type="text" id="' + fieldId + '" name="' + field + '" class="form-control ' + (isRTL ? 'rtl-field' : '') + '" value="' + value + '" placeholder="Enter ' + fieldName + ' in ' + langData.name + '" dir="' + langData.direction + '">';
                }

                if (originalValue) {
                    const truncated = originalValue.length > 150 ? originalValue.substring(0, 150) + '...' : originalValue;
                    html += '<div class="field-comparison mt-2"><small><strong>English:</strong> ' + truncated + '</small></div>';
                }

                html += '</div>';
            });

            container.innerHTML = html;
        },

        // Update completion badge
        updateCompletionBadge: function(completion) {
            const badge = document.getElementById('completionBadge_' + safeModule + '_' + safeItemId);
            const percent = Math.round(completion);
            badge.textContent = percent + '% Complete';
            badge.className = 'badge';
            if (percent >= 100) badge.classList.add('bg-success');
            else if (percent >= 50) badge.classList.add('bg-warning');
            else if (percent > 0) badge.classList.add('bg-info');
            else badge.classList.add('bg-secondary');
        },

        // Save translation
        saveTranslation: async function(event) {
            event.preventDefault();
            if (!this.currentLang) return;

            //Get the save button
            const saveBtn = document.getElementById('saveTranslationBtn_' + safeModule + '_' + safeItemId);
            const btnText = saveBtn.querySelector('.btn-text');
            const btnLoading = saveBtn.querySelector('.btn-loading');

            const formData = {};
            this.fields.forEach(field => {
                const fieldId = 'translation_' + field + '_' + this.currentLang;
                // Check if field has CKEditor
                if (window.translationEditors && window.translationEditors[fieldId]) {
                    const editorData = window.translationEditors[fieldId].getData();
                    if (editorData.trim()) {
                        formData[field] = editorData;
                    }
                } else {
                    // Regular input/textarea
                    const input = document.querySelector('#translationFields_' + safeModule + '_' + safeItemId + ' [name="' + field + '"]');
                    if (input && input.value.trim()) {
                        formData[field] = input.value.trim();
                    }
                }
                //
                const input = document.querySelector('#translationFields_' + safeModule + '_' + safeItemId + ' [name="' + field + '"]');
                if (input && input.value.trim()) {
                    formData[field] = input.value.trim();
                }
            });

            try {
                saveBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-block';
                const response = await TranslationAPI.saveTranslations(this.module, this.itemId, this.currentLang, formData);
                if (response.success) {
                    this.showSuccess('Translation saved successfully!');
                    this.updateCompletionBadge(response.data.completion);
                    await this.loadStats();
                    this.renderLanguagesList();
                }
            } catch (error) {
                console.error('❌ Save error:', error);
                this.showError('Failed to save translation');
            } finally {
                //Reset button state
                saveBtn.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
            }
        },

        getCompletionForLanguage: function(lang) {
            return this.stats[lang]?.completion || 0;
        },

        updateLanguagesWithStats: function(stats) {
            this.stats = stats;
            this.renderLanguagesList();
        },

        showSuccess: function(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert(message);
            }
        },

        showError: function(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message,
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonColor: '#5B914C'
                });
            } else {
                alert(message);
            }
        }
    };

    // Global helper functions
    window['clearCurrentTranslation_' + safeModule + '_' + safeItemId] = function() {
        const manager = window[managerName];
        if (!manager.currentLang) return;
        if (confirm('Clear all ' + manager.languages[manager.currentLang].name + ' translations?')) {
            manager.fields.forEach(field => {
                const input = document.querySelector('#translationFields_' + safeModule + '_' + safeItemId + ' [name="' + field + '"]');
                if (input) input.value = '';
            });
            manager.showSuccess('Fields cleared');
        }
    };

    window['deleteCurrentTranslation_' + safeModule + '_' + safeItemId] = async function() {
        const manager = window[managerName];
        if (!manager.currentLang) return;
        const langName = manager.languages[manager.currentLang].name;
        if (confirm('Delete all ' + langName + ' translations? This cannot be undone.')) {
            try {
                const response = await TranslationAPI.deleteTranslation(safeModule, actualItemId, manager.currentLang);
                if (response.success) {
                    manager.showSuccess('Translation deleted successfully');
                    manager.fields.forEach(field => {
                        const input = document.querySelector('#translationFields_' + safeModule + '_' + safeItemId + ' [name="' + field + '"]');
                        if (input) input.value = '';
                    });
                    manager.updateCompletionBadge(0);
                    await manager.loadStats();
                    manager.renderLanguagesList();
                }
            } catch (error) {
                manager.showError('Failed to delete translation');
            }
        }
    };

    window['openCloneModal_' + safeModule + '_' + safeItemId] = function() {
        const cloneModal = new bootstrap.Modal(document.getElementById('cloneModal_' + safeModule + '_' + safeItemId));
        const manager = window[managerName];
        const fromSelect = document.getElementById('cloneFromLang_' + safeModule + '_' + safeItemId);
        const toSelect = document.getElementById('cloneToLang_' + safeModule + '_' + safeItemId);

        let options = '<option value="">Select language...</option>';
        for (const [code, lang] of Object.entries(manager.languages)) {
            options += '<option value="' + code + '">' + lang.flag + ' ' + lang.name + '</option>';
        }

        fromSelect.innerHTML = options;
        toSelect.innerHTML = options;
        cloneModal.show();
    };

    window['executeClone_' + safeModule + '_' + safeItemId] = async function() {
        const fromLang = document.getElementById('cloneFromLang_' + safeModule + '_' + safeItemId).value;
        const toLang = document.getElementById('cloneToLang_' + safeModule + '_' + safeItemId).value;

        if (!fromLang || !toLang) {
            alert('Please select both languages');
            return;
        }

        if (fromLang === toLang) {
            alert('Source and target languages must be different');
            return;
        }

        try {
            const response = await TranslationAPI.cloneTranslation(safeModule, actualItemId, fromLang, toLang);
            if (response.success) {
                const manager = window[managerName];
                manager.showSuccess('Translation cloned successfully!');
                bootstrap.Modal.getInstance(document.getElementById('cloneModal_' + safeModule + '_' + safeItemId)).hide();
                if (manager.currentLang === toLang) {
                    await manager.loadTranslations(toLang);
                    manager.renderFormFields(toLang);
                }
                await manager.loadStats();
                manager.renderLanguagesList();
            }
        } catch (error) {
            alert('Failed to clone translation');
        }
    };

    // Initialize when modal opens
    document.getElementById('{{ $modalId }}').addEventListener('shown.bs.modal', function() {
        console.log('🎭 Modal opened, initializing...');
        window[managerName].init();
    });

    // Handle form submission
    document.getElementById('translationForm_' + safeModule + '_' + safeItemId).addEventListener('submit', function(e) {
        console.log('📤 Form submitted');
        window[managerName].saveTranslation(e);
    });

    console.log('✅ Translation Modal Script Loaded for:', safeModule, safeItemId);
})();
</script>
@endpush
