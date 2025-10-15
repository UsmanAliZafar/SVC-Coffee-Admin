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
                $model->sku = strtoupper(Str::random(12));
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

        // Delete image file when model is deleted
        static::deleted(function ($model) {
            if ($model->image_path && Storage::exists($model->image_path)) {
                Storage::delete($model->image_path);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
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
     * Get inventory records for this variant (warehouse-wise)
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
        return $query->whereHas('status', function ($q) {
            $q->where('is_active', true);
        });
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
     * Scope: Filter by variant name
     */
    public function scopeByVariantName($query, string $name)
    {
        return $query->where('variant_name', $name);
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
     * Get variant display name (combines name and value)
     */
    public function getDisplayName(): string
    {
        return "{$this->variant_name}: {$this->variant_value}";
    }

    /**
     * Get image URL
     */
    public function getImageUrl(): string
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }
        // Fallback to product's primary image
        return $this->product->primaryImage?->getImageUrl() ?? asset('images/no-image.png');
    }

    /**
     * Set as default variant
     */
    public function setAsDefault(): bool
    {
        return $this->update(['is_default' => true]);
    }

    /**
     * Check if this is the default variant
     */
    public function isDefault(): bool
    {
        return $this->is_default;
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
        if (!$this->product->track_inventory) {
            return true;
        }
        return $this->getTotalStock() > 0;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted sale price
     */
    public function getFormattedSalePrice(): string
    {
        return $this->sale_price ? '$' . number_format($this->sale_price, 2) : '';
    }
}
