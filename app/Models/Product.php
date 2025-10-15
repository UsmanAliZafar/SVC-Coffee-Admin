<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'products';

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
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'product_type',
        'price',
        'sale_price',
        'cost_price',
        'discount_percentage',
        'status_key_code',
        'is_featured',
        'show_on_home',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'structured_data',
        'weight',
        'length',
        'width',
        'height',
        'attributes',
        'specifications',
        'views_count',
        'sales_count',
        'wishlist_count',
        'rating_average',
        'reviews_count',
        'is_available',
        'requires_login',
        'available_from',
        'available_until',
        'visibility_settings',
        'track_inventory',
        'published_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_featured' => 'boolean',
        'show_on_home' => 'boolean',
        'is_available' => 'boolean',
        'requires_login' => 'boolean',
        'track_inventory' => 'boolean',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'sales_count' => 'integer',
        'wishlist_count' => 'integer',
        'reviews_count' => 'integer',
        'rating_average' => 'decimal:2',
        'attributes' => 'array',
        'specifications' => 'array',
        'structured_data' => 'array',
        'visibility_settings' => 'array',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'published_at' => 'datetime',
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

            // Auto-generate slug if not provided
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }

            // Auto-generate SKU if not provided
            if (empty($model->sku)) {
                $model->sku = strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the status of the product
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get categories (Many-to-Many via pivot table)
     * Assumes you have a product_category pivot table
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'product_category',
            'product_id',
            'category_id'
        )->withTimestamps();
    }

    /**
     * Get images (Many-to-Many via pivot table)
     * Assumes you have a product_image pivot table or media table
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    /**
     * Get primary/featured image
     */
    public function primaryImage()
    {
        return $this->images()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get product variants
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get active variants
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->whereHas('status', function ($query) {
            $query->where('is_active', true);
        });
    }

    /**
     * Get coffee machine details (if product is coffee machine)
     */
    public function coffeeMachine(): HasOne
    {
        return $this->hasOne(CoffeeMachine::class);
    }

    /**
     * Get coffee bean details (if product is coffee bean)
     */
    public function coffeeBean(): HasOne
    {
        return $this->hasOne(CoffeeBean::class);
    }

    /**
     * Get spare part details (if product is spare part)
     */
    public function sparePart(): HasOne
    {
        return $this->hasOne(SparePart::class);
    }

    /**
     * Get inventory records (warehouse-wise)
     * Assumes you have a product_inventory table
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Get bulk pricing tiers
     */
    public function bulkPricing(): HasMany
    {
        return $this->hasMany(BulkPricing::class)->orderBy('min_quantity');
    }

    /**
     * Get product attributes
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('sort_order');
    }

    /**
     * Get related products
     */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id'
        )->withPivot('relation_type', 'sort_order')
          ->withTimestamps();
    }

    /**
     * Get reviews
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Creator admin user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Updater admin user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Get products by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope: Get coffee machines
     */
    public function scopeCoffeeMachines($query)
    {
        return $query->where('product_type', 'coffee_machine');
    }

    /**
     * Scope: Get coffee beans
     */
    public function scopeCoffeeBeans($query)
    {
        return $query->where('product_type', 'coffee_bean');
    }

    /**
     * Scope: Get spare parts
     */
    public function scopeSpareParts($query)
    {
        return $query->where('product_type', 'spare_part');
    }

    /**
     * Scope: Get active products (using status)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope: Get published products
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope: Get featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Get products shown on home
     */
    public function scopeShowOnHome($query)
    {
        return $query->where('show_on_home', true);
    }

    /**
     * Scope: Get available products
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                    ->where(function ($q) {
                        $q->whereNull('available_from')
                          ->orWhere('available_from', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('available_until')
                          ->orWhere('available_until', '>=', now());
                    });
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc');
    }

    /**
     * Scope: Search products
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('short_description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Filter by price range
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
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
     * Check if product is on sale
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
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }
        return $this->getTotalStock() > 0;
    }

    /**
     * Get product type label
     */
    public function getProductTypeLabel(): string
    {
        return match($this->product_type) {
            'coffee_machine' => 'Coffee Machine',
            'coffee_bean' => 'Coffee Bean',
            'spare_part' => 'Spare Part',
            default => 'Unknown',
        };
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment sales count
     */
    public function incrementSales(int $quantity = 1): void
    {
        $this->increment('sales_count', $quantity);
    }

    /**
     * Update rating average
     */
    public function updateRating(): void
    {
        $average = $this->reviews()->avg('rating');
        $count = $this->reviews()->count();

        $this->update([
            'rating_average' => $average ?? 0,
            'reviews_count' => $count,
        ]);
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

    /**
     * Check if product is published
     */
    public function isPublished(): bool
    {
        return !is_null($this->published_at) && $this->published_at <= now();
    }

    /**
     * Check if product is available for purchase
     */
    public function isAvailableForPurchase(): bool
    {
        if (!$this->is_available) {
            return false;
        }

        if ($this->available_from && $this->available_from > now()) {
            return false;
        }

        if ($this->available_until && $this->available_until < now()) {
            return false;
        }

        return true;
    }

}
