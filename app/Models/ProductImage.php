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

            // Set default sort order if not provided
            if (is_null($model->sort_order)) {
                $maxOrder = static::where('product_id', $model->product_id)->max('sort_order');
                $model->sort_order = $maxOrder ? $maxOrder + 1 : 0;
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
            if ($model->image_path && Storage::disk('public')->exists($model->image_path)) {
                Storage::disk('public')->delete($model->image_path);
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
     * Scope: Get secondary images
     */
    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Scope: Get images by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get full image URL
     */
    public function getImageUrl(): string
    {
        if ($this->image_path) {
            // If starts with http, return as is (external URL)
            if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
                return $this->image_path;
            }

            // Check if file exists in storage
            if (Storage::disk('public')->exists($this->image_path)) {
                return asset('storage/' . $this->image_path);
            }
        }

        // Return placeholder image
        return asset('images/placeholders/not_availble.jpg');
    }

    /**
     * Get thumbnail URL (if using image intervention or similar)
     */
    public function getThumbnailUrl(int $width = 300, int $height = 300): string
    {
        // Implement thumbnail generation logic here
        // For now, return the regular image URL
        // You can use intervention/image package for dynamic thumbnails
        return $this->getImageUrl();
    }

    /**
     * Get full storage path
     */
    public function getFullPath(): string
    {
        return storage_path('app/public/' . $this->image_path);
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

    /**
     * Check if image file exists
     */
    public function exists(): bool
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path);
    }

    /**
     * Get file size in bytes
     */
    public function getFileSize(): ?int
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->size($this->image_path);
        }
        return null;
    }

    /**
     * Get formatted file size (KB, MB, etc.)
     */
    public function getFormattedFileSize(): string
    {
        $size = $this->getFileSize();

        if (!$size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Get MIME type
     */
    public function getMimeType(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->mimeType($this->image_path);
        }
        return null;
    }

    /**
     * Get file extension
     */
    public function getExtension(): ?string
    {
        if ($this->image_path) {
            return pathinfo($this->image_path, PATHINFO_EXTENSION);
        }
        return null;
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        $mimeType = $this->getMimeType();
        return $mimeType && Str::startsWith($mimeType, 'image/');
    }

    /**
     * Get image dimensions [width, height]
     */
    public function getDimensions(): ?array
    {
        if ($this->exists() && $this->isImage()) {
            try {
                $fullPath = $this->getFullPath();
                $imageSize = getimagesize($fullPath);

                if ($imageSize) {
                    return [
                        'width' => $imageSize[0],
                        'height' => $imageSize[1],
                    ];
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get formatted dimensions
     */
    public function getFormattedDimensions(): string
    {
        $dimensions = $this->getDimensions();

        if ($dimensions) {
            return $dimensions['width'] . ' × ' . $dimensions['height'] . ' px';
        }

        return 'Unknown';
    }

    /**
     * Delete image file from storage
     */
    public function deleteFile(): bool
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->delete($this->image_path);
        }
        return false;
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(int $order): bool
    {
        return $this->update(['sort_order' => $order]);
    }

    /**
     * Move up in sort order
     */
    public function moveUp(): bool
    {
        if ($this->sort_order > 0) {
            return $this->update(['sort_order' => $this->sort_order - 1]);
        }
        return false;
    }

    /**
     * Move down in sort order
     */
    public function moveDown(): bool
    {
        return $this->update(['sort_order' => $this->sort_order + 1]);
    }

    /**
     * Get alt text with fallback
     */
    public function getAltText(): string
    {
        return $this->alt_text ?: ($this->product ? $this->product->name : 'Product Image');
    }

    /**
     * Get caption with fallback
     */
    public function getCaption(): string
    {
        return $this->caption ?: '';
    }

    /**
     * Get display name
     */
    public function getDisplayName(): string
    {
        return $this->image_name ?: basename($this->image_path);
    }
}
