<?php

namespace App\Traits;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslations
{
    /**
     * Get all translations for this model
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'item_id', 'id')
                    ->where('module', $this->getTranslationModule());
    }

    /**
     * Get translations for a specific language
     */
    public function translationsForLanguage(string $lang): HasMany
    {
        return $this->translations()->where('lang', $lang);
    }

    /**
     * Get translation module name (override in model if needed)
     */
    protected function getTranslationModule(): string
    {
        // Default: get table name without 's' or use model name
        $tableName = $this->getTable();

        // Handle common patterns
        if ($tableName === 'products') {
            return 'product';
        } elseif ($tableName === 'products_categories') {
            return 'category';
        } elseif ($tableName === 'vendors') {
            return 'vendor';
        } elseif ($tableName === 'product_tags') {
            return 'tag';
        }

        return rtrim($tableName, 's');
    }

    /**
     * Get translatable fields for this model (override in model)
     */
    protected function getTranslatableFields(): array
    {
        return [
            'name',
            'title',
            'description',
            'short_description',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ];
    }

    /**
     * Get translated value for a field
     */
    public function getTranslation(string $field, string $lang, bool $fallbackToOriginal = true)
    {
        $translation = Translation::getTranslation(
            $this->getTranslationModule(),
            $this->id,
            $field,
            $lang
        );

        // Fallback to original field value if no translation found
        if ($fallbackToOriginal && empty($translation) && isset($this->$field)) {
            return $this->$field;
        }

        return $translation;
    }

    /**
     * Get all translations for a specific language
     */
    public function getAllTranslations(string $lang): array
    {
        return Translation::getAllTranslations(
            $this->getTranslationModule(),
            $this->id,
            $lang
        );
    }

    /**
     * Set translation for a field
     */
    public function setTranslation(string $field, string $lang, ?string $value): Translation
    {
        return Translation::setTranslation(
            $this->getTranslationModule(),
            $this->id,
            $field,
            $lang,
            $value
        );
    }

    /**
     * Bulk set translations
     */
    public function setTranslations(string $lang, array $translations): void
    {
        Translation::bulkSetTranslations(
            $this->getTranslationModule(),
            $this->id,
            $lang,
            $translations
        );
    }

    /**
     * Delete all translations
     */
    public function deleteTranslations(): void
    {
        Translation::deleteForItem($this->getTranslationModule(), $this->id);
    }

    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array
    {
        return Translation::getAvailableLanguages(
            $this->getTranslationModule(),
            $this->id
        );
    }

    /**
     * Check if has translation for language
     */
    public function hasTranslation(string $lang): bool
    {
        return $this->translations()
                    ->where('lang', $lang)
                    ->exists();
    }

    /**
     * Get translation completion percentage
     */
    public function getTranslationCompleteness(string $lang): float
    {
        return Translation::getCompletionPercentage(
            $this->getTranslationModule(),
            $this->id,
            $lang,
            $this->getTranslatableFields()
        );
    }

    /**
     * Get translated attribute dynamically
     * Usage: $product->name('ar') or $product->description('es')
     */
    public function __call($method, $parameters)
    {
        // Check if method exists in parent
        if (method_exists(get_parent_class($this), $method)) {
            return parent::__call($method, $parameters);
        }

        // Check if it's a translatable field
        if (in_array($method, $this->getTranslatableFields()) && isset($parameters[0])) {
            $lang = $parameters[0];
            return $this->getTranslation($method, $lang);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Get all translations grouped by language
     */
    public function getTranslationsGroupedByLanguage(): array
    {
        $translations = $this->translations()
                            ->active()
                            ->get()
                            ->groupBy('lang');

        $result = [];
        foreach ($translations as $lang => $items) {
            $result[$lang] = [];
            foreach ($items as $item) {
                $result[$lang][$item->field] = $item->value;
            }
        }

        return $result;
    }

    /**
     * Check if all required fields are translated
     */
    public function isFullyTranslated(string $lang): bool
    {
        $requiredFields = $this->getTranslatableFields();

        foreach ($requiredFields as $field) {
            if (!Translation::hasTranslation(
                $this->getTranslationModule(),
                $this->id,
                $field,
                $lang
            )) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing translations for a language
     */
    public function getMissingTranslations(string $lang): array
    {
        $requiredFields = $this->getTranslatableFields();
        $existingFields = $this->translations()
                              ->where('lang', $lang)
                              ->active()
                              ->pluck('field')
                              ->toArray();

        return array_diff($requiredFields, $existingFields);
    }

    /**
     * Clone translations to another language
     */
    public function cloneTranslations(string $fromLang, string $toLang): void
    {
        $translations = $this->getAllTranslations($fromLang);
        $this->setTranslations($toLang, $translations);
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(): array
    {
        $languages = $this->getAvailableLanguages();
        $stats = [];

        foreach ($languages as $lang) {
            $stats[$lang] = [
                'language' => $lang,
                'language_name' => $this->getLanguageName($lang),
                'completion' => $this->getTranslationCompleteness($lang),
                'translated_fields' => $this->translations()->where('lang', $lang)->count(),
                'total_fields' => count($this->getTranslatableFields()),
                'missing_fields' => $this->getMissingTranslations($lang),
            ];
        }

        return $stats;
    }

    /**
     * Get language name helper
     */
    protected function getLanguageName(string $lang): string
    {
        $languages = [
            'ar' => 'Arabic',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'ur' => 'Urdu',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ru' => 'Russian',
            'pt' => 'Portuguese',
            'it' => 'Italian',
            'tr' => 'Turkish',
        ];

        return $languages[$lang] ?? strtoupper($lang);
    }
}
