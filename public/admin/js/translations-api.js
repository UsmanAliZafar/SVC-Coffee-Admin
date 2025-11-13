/**
 * Translation API Helper
 *
 * Usage: Include this file in your admin layout or specific pages that need translation functionality
 *
 * <script src="{{ asset('js/translations-api.js') }}"></script>
 */

const TranslationAPI = {
    /**
     * Base URL for translation endpoints
     */
    baseUrl: '/admin/translations',

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    /**
     * Make AJAX request
     */
    async request(method, url, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'Accept': 'application/json'
            }
        };

        if (data && (method === 'POST' || method === 'DELETE')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Request failed');
            }

            return result;
        } catch (error) {
            console.error('Translation API Error:', error);
            throw error;
        }
    },

    /**
     * Get translations for specific item and language
     *
     * @param {string} module - Module name (product, category, vendor, tag)
     * @param {string} itemId - Item UUID
     * @param {string} lang - Language code (ar, es, fr, etc.)
     * @returns {Promise<Object>}
     */
    async getTranslations(module, itemId, lang) {
        const url = `${this.baseUrl}/get?module=${module}&item_id=${itemId}&lang=${lang}`;
        return await this.request('GET', url);
    },

    /**
     * Get all translations for an item (all languages)
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @returns {Promise<Object>}
     */
    async getAllTranslations(module, itemId) {
        const url = `${this.baseUrl}/get-all?module=${module}&item_id=${itemId}`;
        return await this.request('GET', url);
    },

    /**
     * Save translations for specific language
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @param {string} lang - Language code
     * @param {Object} translations - Object with field: value pairs
     * @returns {Promise<Object>}
     */
    async saveTranslations(module, itemId, lang, translations) {
        const url = `${this.baseUrl}/save`;
        return await this.request('POST', url, {
            module,
            item_id: itemId,
            lang,
            translations
        });
    },

    /**
     * Bulk save translations for multiple languages
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @param {Object} translations - Object with lang: {field: value} structure
     * @returns {Promise<Object>}
     */
    async bulkSaveTranslations(module, itemId, translations) {
        const url = `${this.baseUrl}/bulk-save`;
        return await this.request('POST', url, {
            module,
            item_id: itemId,
            translations
        });
    },

    /**
     * Delete all translations for a language
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @param {string} lang - Language code
     * @returns {Promise<Object>}
     */
    async deleteTranslation(module, itemId, lang) {
        const url = `${this.baseUrl}/delete`;
        return await this.request('DELETE', url, {
            module,
            item_id: itemId,
            lang
        });
    },

    /**
     * Delete specific field translation
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @param {string} lang - Language code
     * @param {string} field - Field name
     * @returns {Promise<Object>}
     */
    async deleteFieldTranslation(module, itemId, lang, field) {
        const url = `${this.baseUrl}/delete-field`;
        return await this.request('DELETE', url, {
            module,
            item_id: itemId,
            lang,
            field
        });
    },

    /**
     * Get translation statistics
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @returns {Promise<Object>}
     */
    async getStats(module, itemId) {
        const url = `${this.baseUrl}/stats?module=${module}&item_id=${itemId}`;
        return await this.request('GET', url);
    },

    /**
     * Clone translations from one language to another
     *
     * @param {string} module - Module name
     * @param {string} itemId - Item UUID
     * @param {string} fromLang - Source language code
     * @param {string} toLang - Target language code
     * @returns {Promise<Object>}
     */
    async cloneTranslation(module, itemId, fromLang, toLang) {
        const url = `${this.baseUrl}/clone`;
        return await this.request('POST', url, {
            module,
            item_id: itemId,
            from_lang: fromLang,
            to_lang: toLang
        });
    },

    /**
     * Get available languages
     *
     * @returns {Promise<Object>}
     */
    async getAvailableLanguages() {
        const url = `${this.baseUrl}/languages`;
        return await this.request('GET', url);
    },

    /**
     * Get translatable fields for a module
     *
     * @param {string} module - Module name
     * @returns {Promise<Object>}
     */
    async getTranslatableFields(module) {
        const url = `${this.baseUrl}/fields?module=${module}`;
        return await this.request('GET', url);
    },

    /**
     * Search translations
     *
     * @param {string} query - Search query
     * @param {string|null} lang - Language code (optional)
     * @param {string|null} module - Module name (optional)
     * @returns {Promise<Object>}
     */
    async searchTranslations(query, lang = null, module = null) {
        let url = `${this.baseUrl}/search?query=${encodeURIComponent(query)}`;
        if (lang) url += `&lang=${lang}`;
        if (module) url += `&module=${module}`;
        return await this.request('GET', url);
    }
};

/**
 * Translation Form Handler
 * Helper class for handling translation forms
 */
class TranslationFormHandler {
    constructor(module, itemId) {
        this.module = module;
        this.itemId = itemId;
        this.currentLang = null;
    }

    /**
     * Load translations for a specific language
     */
    async loadTranslations(lang) {
        try {
            this.currentLang = lang;
            const response = await TranslationAPI.getTranslations(this.module, this.itemId, lang);

            if (response.success) {
                this.fillForm(response.data.translations);
                this.updateCompletionBadge(lang, response.data.completion);
                return response.data;
            }
        } catch (error) {
            console.error('Failed to load translations:', error);
            this.showError('Failed to load translations');
        }
    }

    /**
     * Fill form with translation data
     */
    fillForm(translations) {
        const formPrefix = `translations[${this.currentLang}]`;

        Object.keys(translations).forEach(field => {
            const input = document.querySelector(`[name="${formPrefix}[${field}]"]`);
            if (input) {
                input.value = translations[field] || '';
            }
        });
    }

    /**
     * Save current language translations
     */
    async saveCurrentLanguage() {
        if (!this.currentLang) {
            this.showError('No language selected');
            return;
        }

        const translations = this.collectFormData();

        try {
            const response = await TranslationAPI.saveTranslations(
                this.module,
                this.itemId,
                this.currentLang,
                translations
            );

            if (response.success) {
                this.showSuccess('Translations saved successfully');
                this.updateCompletionBadge(this.currentLang, response.data.completion);
                return response;
            }
        } catch (error) {
            console.error('Failed to save translations:', error);
            this.showError('Failed to save translations');
        }
    }

    /**
     * Collect form data for current language
     */
    collectFormData() {
        const formPrefix = `translations[${this.currentLang}]`;
        const translations = {};

        document.querySelectorAll(`[name^="${formPrefix}"]`).forEach(input => {
            const fieldMatch = input.name.match(/\[([^\]]+)\]$/);
            if (fieldMatch) {
                const field = fieldMatch[1];
                const value = input.value.trim();
                if (value) {
                    translations[field] = value;
                }
            }
        });

        return translations;
    }

    /**
     * Clear current language form
     */
    clearForm() {
        if (!this.currentLang) return;

        const formPrefix = `translations[${this.currentLang}]`;
        document.querySelectorAll(`[name^="${formPrefix}"]`).forEach(input => {
            input.value = '';
        });
    }

    /**
     * Delete current language translations
     */
    async deleteCurrentLanguage() {
        if (!this.currentLang) return;

        if (!confirm(`Delete all ${this.currentLang} translations? This cannot be undone.`)) {
            return;
        }

        try {
            const response = await TranslationAPI.deleteTranslation(
                this.module,
                this.itemId,
                this.currentLang
            );

            if (response.success) {
                this.showSuccess('Translation deleted successfully');
                this.clearForm();
                this.updateCompletionBadge(this.currentLang, 0);
                return response;
            }
        } catch (error) {
            console.error('Failed to delete translation:', error);
            this.showError('Failed to delete translation');
        }
    }

    /**
     * Update completion badge
     */
    updateCompletionBadge(lang, completion) {
        const tab = document.querySelector(`#lang-${lang}-tab .badge`);
        if (tab) {
            tab.textContent = `${Math.round(completion)}%`;

            // Update badge color based on completion
            tab.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'bg-secondary');
            if (completion >= 100) {
                tab.classList.add('bg-success');
            } else if (completion >= 50) {
                tab.classList.add('bg-warning');
            } else if (completion > 0) {
                tab.classList.add('bg-danger');
            } else {
                tab.classList.add('bg-secondary');
            }
        }
    }

    /**
     * Show success message (customize based on your notification library)
     */
    showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }

    /**
     * Show error message (customize based on your notification library)
     */
    showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
}

/*
╔════════════════════════════════════════════════════════════════════════╗
║                          USAGE EXAMPLES                                 ║
╚════════════════════════════════════════════════════════════════════════╝

// Example 1: Simple API calls
async function loadProductTranslations() {
    try {
        const response = await TranslationAPI.getTranslations('product', productId, 'ar');
        console.log(response.data.translations);
    } catch (error) {
        console.error(error);
    }
}

// Example 2: Save translations
async function saveProductTranslations() {
    const translations = {
        name: 'اسم المنتج',
        description: 'وصف المنتج',
        short_description: 'وصف قصير'
    };

    try {
        const response = await TranslationAPI.saveTranslations('product', productId, 'ar', translations);
        console.log('Saved!', response.data);
    } catch (error) {
        console.error(error);
    }
}

// Example 3: Using the form handler
const productId = '123e4567-e89b-12d3-a456-426614174000';
const handler = new TranslationFormHandler('product', productId);

// Load translations when tab is clicked
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function(e) {
        const lang = e.target.id.replace('-tab', '').replace('lang-', '');
        handler.loadTranslations(lang);
    });
});

// Save button
document.getElementById('saveTranslationBtn').addEventListener('click', async () => {
    await handler.saveCurrentLanguage();
});

// Clear button
document.getElementById('clearTranslationBtn').addEventListener('click', () => {
    handler.clearForm();
});

// Delete button
document.getElementById('deleteTranslationBtn').addEventListener('click', async () => {
    await handler.deleteCurrentLanguage();
});

*/
