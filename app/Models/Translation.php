<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Translation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'translations';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'item_id',
        'module',
        'field',
        'lang',
        'value',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'created_by',
        'updated_by',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate UUID
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Set created_by
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            // Set updated_by
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get creator admin user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get updater admin user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by module
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Filter by item ID
     */
    public function scopeForItem($query, string $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope: Filter by language
     */
    public function scopeForLanguage($query, string $lang)
    {
        return $query->where('lang', $lang);
    }

    /**
     * Scope: Filter by field
     */
    public function scopeForField($query, string $field)
    {
        return $query->where('field', $field);
    }

    /**
     * Scope: Get only active translations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get translations for specific item and module
     */
    public function scopeForEntity($query, string $module, string $itemId)
    {
        return $query->where('module', $module)
                    ->where('item_id', $itemId);
    }

    // ==================== STATIC HELPER METHODS ====================

    /**
     * Get translation for a specific field
     */
    public static function getTranslation(string $module, string $itemId, string $field, string $lang): ?string
    {
        $translation = static::forEntity($module, $itemId)
                            ->forField($field)
                            ->forLanguage($lang)
                            ->active()
                            ->first();

        return $translation?->value;
    }

    /**
     * Get all translations for an item
     */
    public static function getAllTranslations(string $module, string $itemId, string $lang): array
    {
        $translations = static::forEntity($module, $itemId)
                            ->forLanguage($lang)
                            ->active()
                            ->get();

        $result = [];
        foreach ($translations as $translation) {
            $result[$translation->field] = $translation->value;
        }

        return $result;
    }

    /**
     * Set or update translation
     */
    public static function setTranslation(string $module, string $itemId, string $field, string $lang, ?string $value): self
    {
        return static::updateOrCreate(
            [
                'item_id' => $itemId,
                'module' => $module,
                'field' => $field,
                'lang' => $lang,
            ],
            [
                'value' => $value,
                'is_active' => true,
            ]
        );
    }

    /**
     * Bulk set translations
     */
    public static function bulkSetTranslations(string $module, string $itemId, string $lang, array $translations): void
    {
        foreach ($translations as $field => $value) {
            if (!empty($value)) {
                static::setTranslation($module, $itemId, $field, $lang, $value);
            }
        }
    }

    /**
     * Delete all translations for an item
     */
    public static function deleteForItem(string $module, string $itemId): void
    {
        static::forEntity($module, $itemId)->delete();
    }

    /**
     * Get available languages for an item
     */
    public static function getAvailableLanguages(string $module, string $itemId): array
    {
        return static::forEntity($module, $itemId)
                    ->active()
                    ->distinct('lang')
                    ->pluck('lang')
                    ->toArray();
    }

    /**
     * Check if translation exists
     */
    public static function hasTranslation(string $module, string $itemId, string $field, string $lang): bool
    {
        return static::forEntity($module, $itemId)
                    ->forField($field)
                    ->forLanguage($lang)
                    ->active()
                    ->exists();
    }

    /**
     * Get translation completion percentage for a language
     */
    public static function getCompletionPercentage(string $module, string $itemId, string $lang, array $requiredFields): float
    {
        $totalFields = count($requiredFields);
        if ($totalFields === 0) {
            return 100;
        }

        $translatedCount = static::forEntity($module, $itemId)
                                ->forLanguage($lang)
                                ->whereIn('field', $requiredFields)
                                ->whereNotNull('value')
                                ->where('value', '!=', '')
                                ->active()
                                ->count();

        return round(($translatedCount / $totalFields) * 100, 2);
    }

    // ==================== INSTANCE METHODS ====================

    /**
     * Activate translation
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate translation
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get module display name
     */
    public function getModuleDisplayName(): string
    {
        return ucfirst($this->module);
    }

    /**
     * Get field display name
     */
    public function getFieldDisplayName(): string
    {
        return ucwords(str_replace('_', ' ', $this->field));
    }

    /**
     * Get language display name
     */
    public function getLanguageDisplayName(): string
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

        return $languages[$this->lang] ?? strtoupper($this->lang);
    }

    /**
     * Check if value is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->value) || trim($this->value) === '';
    }

    /**
     * Get character count
     */
    public function getCharacterCount(): int
    {
        return mb_strlen($this->value ?? '');
    }

    /**
     * Get word count
     */
    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->value ?? ''));
    }
}
