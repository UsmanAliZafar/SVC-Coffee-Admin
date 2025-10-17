<?php
// app/Models/UrlRedirect.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UrlRedirect extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'url_redirects';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'old_url',
        'new_url',
        'redirect_type',
        'entity_type',
        'entity_id',
        'hit_count',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'hit_count' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Set created_by
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }

            // Normalize URLs
            $model->old_url = self::normalizeUrl($model->old_url);
            $model->new_url = self::normalizeUrl($model->new_url);
        });

        static::updating(function ($model) {
            // Set updated_by
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }

            // Normalize URLs if changed
            if ($model->isDirty('old_url')) {
                $model->old_url = self::normalizeUrl($model->old_url);
            }
            if ($model->isDirty('new_url')) {
                $model->new_url = self::normalizeUrl($model->new_url);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // For polymorphic relationships (optional)
    public function entity()
    {
        if ($this->entity_type === 'product') {
            return $this->belongsTo(Product::class, 'entity_id');
        }
        // Add other entity types as needed
        return null;
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('redirect_type', $type);
    }

    public function scopeForEntity($query, $type, $id)
    {
        return $query->where('entity_type', $type)
                    ->where('entity_id', $id);
    }

    public function scopePermanent($query)
    {
        return $query->where('redirect_type', '301');
    }

    public function scopeTemporary($query)
    {
        return $query->where('redirect_type', '302');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Normalize URL (remove domain, trailing slashes, etc.)
     */
    public static function normalizeUrl($url)
    {
        // Remove protocol and domain
        $url = preg_replace('#^https?://[^/]+#i', '', $url);

        // Remove trailing slash
        $url = rtrim($url, '/');

        // Ensure starts with /
        if (!empty($url) && $url[0] !== '/') {
            $url = '/' . $url;
        }

        return $url ?: '/';
    }

    /**
     * Increment hit count
     */
    public function incrementHits()
    {
        $this->increment('hit_count');
    }

    /**
     * Check if redirect is permanent
     */
    public function isPermanent(): bool
    {
        return $this->redirect_type === '301';
    }

    /**
     * Check if redirect is temporary
     */
    public function isTemporary(): bool
    {
        return $this->redirect_type === '302';
    }

    /**
     * Activate the redirect
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the redirect
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get redirect type badge
     */
    public function getTypeBadge(): string
    {
        return match($this->redirect_type) {
            '301' => '<span class="badge bg-success">301 Permanent</span>',
            '302' => '<span class="badge bg-info">302 Temporary</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Get status badge
     */
    public function getStatusBadge(): string
    {
        return $this->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    /**
     * Find redirect by old URL
     */
    public static function findByOldUrl($url)
    {
        $normalizedUrl = self::normalizeUrl($url);
        return self::active()->where('old_url', $normalizedUrl)->first();
    }

    /**
     * Create redirect for product slug change
     */
    public static function createProductRedirect($oldSlug, $newSlug, $productId, $redirectType = '301')
    {
        $oldUrl = '/products/' . $oldSlug;
        $newUrl = '/products/' . $newSlug;

        return self::create([
            'old_url' => $oldUrl,
            'new_url' => $newUrl,
            'redirect_type' => $redirectType,
            'entity_type' => 'product',
            'entity_id' => $productId,
            'notes' => 'Auto-generated redirect due to slug change',
        ]);
    }
}
