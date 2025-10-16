<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'product_variants';

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
        'variant_name',
        'variant_value',
        'sku',
        'price',
        'sale_price',
        'weight',
        'length',
        'width',
        'height',
        'image_path',
        'sort_order',
        'is_default',
        'status_key_code',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
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

            // Auto-generate SKU if not provided
            if (empty($model->sku)) {
                $model->sku = 'VAR-' . strtoupper(Str::random(10));
            }

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'VARIANT_ACTIVE';
            }

            // Set default sort order if not provided
            if (is_null($model->sort_order)) {
                $maxOrder = static::where('product_id', $model->product_id)->max('sort_order');
                $model->sort_order = $maxOrder ? $maxOrder + 1 : 0;
            }
        });

        // When setting default variant, unset others
        static::saving(function ($model) {
            if ($model->is_default && $model->isDirty('is_default')) {
                static::where('product_id', $model->product_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });

        // Delete variant image when model is deleted
        static::deleted(function ($model) {
            if ($model->image_path && Storage::disk('public')->exists($model->image_path)) {
                Storage::disk('public')->delete($model->image_path);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the product that owns the variant
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the status of the variant
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get inventory records for this variant
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(ProductInventory::class, 'variant_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Get active variants
     */
    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'VARIANT_ACTIVE');
    }

    /**
     * Scope: Get inactive variants
     */
    public function scopeInactive($query)
    {
        return $query->where('status_key_code', 'VARIANT_INACTIVE');
    }

    /**
     * Scope: Get default variant
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('variant_name', 'asc');
    }

    /**
     * Scope: Get variants by product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Search variants
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('variant_name', 'like', "%{$search}%")
              ->orWhere('variant_value', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Get variants in stock
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('inventory', function ($q) {
            $q->where('quantity', '>', 0);
        });
    }

    /**
     * Scope: Get variants on sale
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
                    ->whereColumn('sale_price', '<', 'price');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get final selling price (considers sale price)
     */
    public function getFinalPrice(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if variant is on sale
     */
    public function isOnSale(): bool
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): float
    {
        if ($this->isOnSale()) {
            return $this->price - $this->sale_price;
        }
        return 0;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if ($this->isOnSale() && $this->price > 0) {
            return round((($this->price - $this->sale_price) / $this->price) * 100, 2);
        }
        return 0;
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStock(): int
    {
        return $this->inventory()->sum('quantity');
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->getTotalStock() > 0;
    }

    /**
     * Get image URL with fallback
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

        // Fallback to product's primary image
        if ($this->product) {
            $primaryImage = $this->product->primaryImage();
            if ($primaryImage) {
                return $primaryImage->getImageUrl();
            }

            // Fallback to product's main_image
            if ($this->product->main_image) {
                return $this->product->getMainImageUrl();
            }
        }

        // Final fallback to placeholder
        return asset('images/placeholders/variant-placeholder.jpg');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        $currency = $this->product->curency ?? 'USD';
        return $currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get formatted sale price
     */
    public function getFormattedSalePrice(): string
    {
        if (!$this->sale_price) {
            return '';
        }
        $currency = $this->product->curency ?? 'USD';
        return $currency . ' ' . number_format($this->sale_price, 2);
    }

    /**
     * Get formatted final price
     */
    public function getFormattedFinalPrice(): string
    {
        $currency = $this->product->curency ?? 'USD';
        return $currency . ' ' . number_format($this->getFinalPrice(), 2);
    }

    /**
     * Get full variant name (name: value)
     */
    public function getFullName(): string
    {
        return $this->variant_name . ': ' . $this->variant_value;
    }

    /**
     * Get display name
     */
    public function getDisplayName(): string
    {
        return ucfirst($this->variant_value);
    }

    /**
     * Get dimensions as string
     */
    public function getDimensions(): ?string
    {
        if ($this->length && $this->width && $this->height) {
            return $this->length . ' × ' . $this->width . ' × ' . $this->height . ' cm';
        }
        return null;
    }

    /**
     * Get weight with unit
     */
    public function getFormattedWeight(): ?string
    {
        if ($this->weight) {
            return $this->weight . ' kg';
        }
        return null;
    }

    /**
     * Check if variant is active
     */
    public function isActive(): bool
    {
        return $this->status_key_code === 'VARIANT_ACTIVE';
    }

    /**
     * Check if variant is default
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Set as default variant
     */
    public function setAsDefault(): bool
    {
        return $this->update(['is_default' => true]);
    }

    /**
     * Activate the variant
     */
    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'VARIANT_ACTIVE']);
    }

    /**
     * Deactivate the variant
     */
    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'VARIANT_INACTIVE']);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'VARIANT_ACTIVE' => '<span class="badge bg-success">Active</span>',
            'VARIANT_INACTIVE' => '<span class="badge bg-secondary">Inactive</span>',
            'VARIANT_OUT_OF_STOCK' => '<span class="badge bg-danger">Out of Stock</span>',
            default => '<span class="badge bg-light">Unknown</span>',
        };
    }

    /**
     * Get stock status badge
     */
    public function getStockBadge(): string
    {
        $stock = $this->getTotalStock();

        if ($stock <= 0) {
            return '<span class="badge bg-danger">Out of Stock</span>';
        } elseif ($stock < 10) {
            return '<span class="badge bg-warning">Low Stock (' . $stock . ')</span>';
        } else {
            return '<span class="badge bg-success">In Stock (' . $stock . ')</span>';
        }
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
     * Delete variant image from storage
     */
    public function deleteImage(): bool
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->delete($this->image_path);
        }
        return false;
    }

    /**
     * Check if variant has physical dimensions
     */
    public function hasPhysicalDimensions(): bool
    {
        return !is_null($this->length) && !is_null($this->width) && !is_null($this->height);
    }

    /**
     * Check if variant has weight
     */
    public function hasWeight(): bool
    {
        return !is_null($this->weight);
    }

    /**
     * Get shipping weight (with fallback to product weight)
     */
    public function getShippingWeight(): ?float
    {
        return $this->weight ?? $this->product->weight ?? null;
    }
}
