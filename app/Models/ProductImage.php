<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'product_images';

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
        'product_id',
        'image_path',
        'image_name',
        'alt_text',
        'caption',
        'sort_order',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID on creating
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // When setting primary image, unset others
        static::saving(function ($model) {
            if ($model->is_primary && $model->isDirty('is_primary')) {
                static::where('product_id', $model->product_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_primary' => false]);
            }
        });

        // Delete image file when model is deleted
        static::deleted(function ($model) {
            if ($model->image_path && Storage::exists($model->image_path)) {
                Storage::delete($model->image_path);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the product that owns the image
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Get primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'asc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get full image URL
     */
    public function getImageUrl(): string
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }
        return asset('images/no-image.png');
    }

    /**
     * Get thumbnail URL (if using image intervention or similar)
     */
    public function getThumbnailUrl(int $width = 300, int $height = 300): string
    {
        // Implement thumbnail generation logic here
        // This is a placeholder - adjust based on your thumbnail strategy
        return $this->getImageUrl();
    }

    /**
     * Set as primary image
     */
    public function setAsPrimary(): bool
    {
        return $this->update(['is_primary' => true]);
    }

    /**
     * Check if this is the primary image
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }
}
