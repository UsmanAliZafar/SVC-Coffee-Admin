<?php

use App\Models\Translation;

if (!function_exists('get_translation')) {
    /**
     * Get translation for a specific field
     *
     * @param string $module Module name (product, category, vendor, etc.)
     * @param string $itemId Item UUID
     * @param string $field Field name to translate
     * @param string|null $lang Language code (null = current app locale)
     * @param mixed $fallback Fallback value if translation not found
     * @return string|null
     */
    function get_translation(string $module, string $itemId, string $field, ?string $lang = null, $fallback = null)
    {
        $lang = $lang ?? app()->getLocale();

        // Don't translate if language is English (default)
        if ($lang === 'en') {
            return $fallback;
        }

        $translation = Translation::getTranslation($module, $itemId, $field, $lang);

        return $translation ?? $fallback;
    }
}

if (!function_exists('set_translation')) {
    /**
     * Set or update translation
     *
     * @param string $module Module name
     * @param string $itemId Item UUID
     * @param string $field Field name
     * @param string $lang Language code
     * @param string|null $value Translation value
     * @return Translation
     */
    function set_translation(string $module, string $itemId, string $field, string $lang, ?string $value): Translation
    {
        return Translation::setTranslation($module, $itemId, $field, $lang, $value);
    }
}

if (!function_exists('translate_model')) {
    /**
     * Get translated model data
     *
     * @param mixed $model Model instance with HasTranslations trait
     * @param string|null $lang Language code
     * @return object Translated model data
     */
    function translate_model($model, ?string $lang = null): object
    {
        $lang = $lang ?? app()->getLocale();

        // If English, return original model
        if ($lang === 'en' || !method_exists($model, 'getAllTranslations')) {
            return $model;
        }

        $translations = $model->getAllTranslations($lang);

        // Create a copy of model attributes
        $translatedData = $model->getAttributes();

        // Override with translations
        foreach ($translations as $field => $value) {
            if (!empty($value)) {
                $translatedData[$field] = $value;
            }
        }

        return (object) $translatedData;
    }
}

if (!function_exists('get_available_languages')) {
    /**
     * Get all available languages in the system
     *
     * @param bool $includeEnglish Include English in the list
     * @return array
     */
    function get_available_languages(bool $includeEnglish = false): array
    {
        $languages = [
            'ar' => [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'flag' => '🇸🇦',
                'direction' => 'rtl',
            ],
            // 'es' => [
            //     'code' => 'es',
            //     'name' => 'Spanish',
            //     'native_name' => 'Español',
            //     'flag' => '🇪🇸',
            //     'direction' => 'ltr',
            // ],
            // 'fr' => [
            //     'code' => 'fr',
            //     'name' => 'French',
            //     'native_name' => 'Français',
            //     'flag' => '🇫🇷',
            //     'direction' => 'ltr',
            // ],
            // 'de' => [
            //     'code' => 'de',
            //     'name' => 'German',
            //     'native_name' => 'Deutsch',
            //     'flag' => '🇩🇪',
            //     'direction' => 'ltr',
            // ],
            // 'ur' => [
            //     'code' => 'ur',
            //     'name' => 'Urdu',
            //     'native_name' => 'اردو',
            //     'flag' => '🇵🇰',
            //     'direction' => 'rtl',
            // ],
            // 'zh' => [
            //     'code' => 'zh',
            //     'name' => 'Chinese',
            //     'native_name' => '中文',
            //     'flag' => '🇨🇳',
            //     'direction' => 'ltr',
            // ],
            // 'ja' => [
            //     'code' => 'ja',
            //     'name' => 'Japanese',
            //     'native_name' => '日本語',
            //     'flag' => '🇯🇵',
            //     'direction' => 'ltr',
            // ],
            // 'ru' => [
            //     'code' => 'ru',
            //     'name' => 'Russian',
            //     'native_name' => 'Русский',
            //     'flag' => '🇷🇺',
            //     'direction' => 'ltr',
            // ],
            // 'pt' => [
            //     'code' => 'pt',
            //     'name' => 'Portuguese',
            //     'native_name' => 'Português',
            //     'flag' => '🇵🇹',
            //     'direction' => 'ltr',
            // ],
            // 'it' => [
            //     'code' => 'it',
            //     'name' => 'Italian',
            //     'native_name' => 'Italiano',
            //     'flag' => '🇮🇹',
            //     'direction' => 'ltr',
            // ],
            // 'tr' => [
            //     'code' => 'tr',
            //     'name' => 'Turkish',
            //     'native_name' => 'Türkçe',
            //     'flag' => '🇹🇷',
            //     'direction' => 'ltr',
            // ],
        ];

        if ($includeEnglish) {
            $languages = array_merge([
                'en' => [
                    'code' => 'en',
                    'name' => 'English',
                    'native_name' => 'English',
                    'flag' => '🇬🇧',
                    'direction' => 'ltr',
                ]
            ], $languages);
        }

        return $languages;
    }
}

if (!function_exists('get_language_name')) {
    /**
     * Get language name from code
     *
     * @param string $code Language code
     * @param bool $native Return native name
     * @return string
     */
    function get_language_name(string $code, bool $native = false): string
    {
        $languages = get_available_languages(true);

        if (!isset($languages[$code])) {
            return strtoupper($code);
        }

        return $native ? $languages[$code]['native_name'] : $languages[$code]['name'];
    }
}

if (!function_exists('is_rtl_language')) {
    /**
     * Check if language is RTL
     *
     * @param string|null $lang Language code (null = current locale)
     * @return bool
     */
    function is_rtl_language(?string $lang = null): bool
    {
        $lang = $lang ?? app()->getLocale();
        $languages = get_available_languages(true);

        return ($languages[$lang]['direction'] ?? 'ltr') === 'rtl';
    }
}

if (!function_exists('get_translatable_fields')) {
    /**
     * Get translatable fields for a module
     *
     * @param string $module Module name
     * @return array
     */
    function get_translatable_fields(string $module): array
    {
        $fields = [
            'product' => [
                'name',
                'short_description',
                'description',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ],
            'category' => [
                'title',
                'short_description',
                'description',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ],
            'vendor' => [
                'name',
                'description',
                'short_description',
            ],
            'tag' => [
                'name',
                'description',
            ],
        ];

        return $fields[$module] ?? [];
    }
}

if (!function_exists('get_translation_badge')) {
    /**
     * Get translation completion badge HTML
     *
     * @param float $percentage Completion percentage
     * @return string
     */
    function get_translation_badge(float $percentage): string
    {
        if ($percentage >= 100) {
            return '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Complete</span>';
        } elseif ($percentage >= 75) {
            return '<span class="badge bg-info"><i class="bi bi-clock-fill"></i> ' . number_format($percentage, 0) . '%</span>';
        } elseif ($percentage >= 50) {
            return '<span class="badge bg-warning"><i class="bi bi-exclamation-triangle-fill"></i> ' . number_format($percentage, 0) . '%</span>';
        } elseif ($percentage > 0) {
            return '<span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> ' . number_format($percentage, 0) . '%</span>';
        } else {
            return '<span class="badge bg-secondary"><i class="bi bi-dash-circle"></i> Not Started</span>';
        }
    }
}

if (!function_exists('clear_translations_cache')) {
    /**
     * Clear translations cache (if using cache)
     *
     * @param string|null $module Specific module or all
     * @param string|null $itemId Specific item or all
     * @return void
     */
    function clear_translations_cache(?string $module = null, ?string $itemId = null): void
    {
        // Implement cache clearing logic if you add caching later
        // Cache::tags(['translations'])->flush();
    }
}
