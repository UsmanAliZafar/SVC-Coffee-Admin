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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\InventoryManager;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use InventoryManager;

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
        'barcode',
        'short_description',
        'description',
        'category_id',
        'product_type',
        'vendor_id',
        'curency',
        'price',
        'sale_price',
        'cost_price',
        'discount_percentage',
        'is_taxable',
        'tax_type',
        'tax_percentage',
        'tax_class',
        'main_image',
        'status_key_code',
        'is_featured',
        'show_on_home',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'structured_data',
        'attributes',
        'specifications',
        'features',
        'is_available',
        'requires_login',
        'available_from',
        'available_until',
        'visibility_settings',
        'track_inventory',
        'stock_quantity',
        'low_stock_threshold',
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
        'tax_percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_featured' => 'boolean',
        'show_on_home' => 'boolean',
        'is_available' => 'boolean',
        'requires_login' => 'boolean',
        'track_inventory' => 'boolean',
        'sort_order' => 'integer',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'attributes' => 'array',
        'specifications' => 'array',
        'features' => 'array',
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

            // Auto-generate slug from name
            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }

            // Auto-generate SKU
            if (empty($model->sku)) {
                $model->sku = 'PRD-' . strtoupper(Str::random(8));
            }

            // Auto-generate barcode
            if (empty($model->barcode)) {
                $model->barcode = 'BAR' . time() . rand(1000, 9999);
            }

            // Set default product type
            if (empty($model->product_type)) {
                $model->product_type = 'simple';
            }

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'PRODUCT_DRAFT';
            }

            // Set default stock values
            if (!isset($model->stock_quantity)) {
                $model->stock_quantity = 0;
            }
            if (!isset($model->low_stock_threshold)) {
                $model->low_stock_threshold = 10;
            }

            // Set created_by
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            // Update slug if name changed
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }

            // Set updated_by
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }
        });

        // Clean up related data on delete
        static::deleting(function ($model) {
            // Delete all product images
            foreach ($model->images as $image) {
                if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            // Delete main image
            if ($model->main_image && Storage::disk('public')->exists($model->main_image)) {
                Storage::disk('public')->delete($model->main_image);
            }

            // Detach tags
            $model->tags()->detach();
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
     * Get the status of the product
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get the category of the product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductsCategories::class, 'category_id');
    }

    /**
     * Get the vendor of the product
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get all images of the product
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary/featured image
     */
    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first();
    }

    /**
     * Get all variants of the product
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Get only active variants
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->where('status_key_code', 'VARIANT_ACTIVE');
    }

    /**
     * Get the default variant
     */
    public function defaultVariant()
    {
        return $this->variants()->where('is_default', true)->first();
    }

    /**
     * Get all tags of the product
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductTag::class,
            'product_tag',
            'product_id',
            'tag_id'
        )->withTimestamps();
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
     * Get product reviews
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

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
     * Scope: Filter by product type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope: Get only simple products
     */
    public function scopeSimple($query)
    {
        return $query->where('product_type', 'simple');
    }

    /**
     * Scope: Get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'PRODUCT_ACTIVE');
    }

    /**
     * Scope: Get only draft products
     */
    public function scopeDraft($query)
    {
        return $query->where('status_key_code', 'PRODUCT_DRAFT');
    }

    /**
     * Scope: Get only inactive products
     */
    public function scopeInactive($query)
    {
        return $query->where('status_key_code', 'PRODUCT_INACTIVE');
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
     * Scope: Get products shown on homepage
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
     * Scope: Get products in stock
     */
    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('track_inventory', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Scope: Get out of stock products
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_inventory', true)
                    ->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope: Get low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '>', 0);
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
              ->orWhere('barcode', 'like', "%{$search}%")
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

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Filter by vendor
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: Products on sale
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
                    ->whereColumn('sale_price', '<', 'price');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get final selling price
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
     * Get total stock quantity
     */
    public function getTotalStock(): int
    {
        if (!$this->track_inventory) {
            return PHP_INT_MAX;
        }
        return $this->stock_quantity;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock(): bool
    {
        if (!$this->track_inventory) {
            return false;
        }
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Add stock quantity
     */
    public function addStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): void
    {
        $newQuantity = max(0, $this->stock_quantity - $quantity);
        $this->update(['stock_quantity' => $newQuantity]);
    }

    /**
     * Set stock quantity
     */
    public function setStock(int $quantity): void
    {
        $this->update(['stock_quantity' => max(0, $quantity)]);
    }

    /**
     * Get product type label
     */
    public function getProductTypeLabel(): string
    {
        return match($this->product_type) {
            'simple' => 'Simple Product',
            'variable' => 'Variable Product',
            'grouped' => 'Grouped Product',
            'external' => 'External/Affiliate Product',
            default => ucfirst($this->product_type),
        };
    }

    /**
     * Get main image URL
     */
    public function getMainImageUrl(): string
    {
        if ($this->main_image) {
            if (Str::startsWith($this->main_image, ['http://', 'https://'])) {
                return $this->main_image;
            }

            if (Storage::disk('public')->exists($this->main_image)) {
                return asset('storage/' . $this->main_image);
            }
        }

        // Try to get primary image from gallery
        $primaryImage = $this->primaryImage();
        if ($primaryImage) {
            return $primaryImage->getImageUrl();
        }

        // Return placeholder
        return asset('images/placeholders/not_availble.jpg');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return format_store_price($this->price);
        // return $this->curency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get formatted sale price
     */
    public function getFormattedSalePrice(): string
    {
        return format_store_price($this->sale_price);
        // return $this->sale_price ? $this->curency . ' ' . number_format($this->sale_price, 2) : '';
    }

    /**
     * Get formatted final price
     */
    public function getFormattedFinalPrice(): string
    {
        return format_store_price($this->getFinalPrice());
        // return $this->curency . ' ' . number_format($this->getFinalPrice(), 2);
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

        if (!$this->isInStock()) {
            return false;
        }

        return true;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'PRODUCT_ACTIVE' => '<span class="badge bg-success">Active</span>',
            'PRODUCT_DRAFT' => '<span class="badge bg-warning">Draft</span>',
            'PRODUCT_INACTIVE' => '<span class="badge bg-secondary">Inactive</span>',
            'PRODUCT_PENDING' => '<span class="badge bg-info">Pending</span>',
            'PRODUCT_OUT_OF_STOCK' => '<span class="badge bg-danger">OUT STOCK</span>',
            'PRODUCT_DISCONTINUED' => '<span class="badge bg-dark">Discontinued</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Get stock status badge HTML
     */
    public function getStockBadge(): string
    {
        if (!$this->track_inventory) {
            return '<span class="badge bg-info">Not Tracked</span>';
        }

        $stock = $this->stock_quantity;

        if ($stock <= 0) {
            return '<span class="badge bg-danger">Out of Stock</span>';
        } elseif ($this->isLowStock()) {
            return '<span class="badge bg-warning">Low Stock (' . $stock . ')</span>';
        } else {
            return '<span class="badge bg-success">In Stock (' . $stock . ')</span>';
        }
    }

    /**
     * Activate the product
     */
    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'PRODUCT_ACTIVE']);
    }

    /**
     * Deactivate the product
     */
    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'PRODUCT_INACTIVE']);
    }

    /**
     * Publish the product
     */
    public function publish(): bool
    {
        return $this->update(['published_at' => now()]);
    }

    /**
     * Unpublish the product
     */
    public function unpublish(): bool
    {
        return $this->update(['published_at' => null]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(): bool
    {
        return $this->update(['is_featured' => !$this->is_featured]);
    }

    /**
     * Toggle homepage visibility
     */
    public function toggleHomepage(): bool
    {
        return $this->update(['show_on_home' => !$this->show_on_home]);
    }

    // ==================== TAX CALCULATION METHODS ====================
    /**
     * Check if product is taxable
     */
    public function isTaxable(): bool
    {
        return $this->is_taxable;
    }

    /**
     * Check if tax is inclusive
     */
    public function isTaxInclusive(): bool
    {
        return $this->tax_type === 'inclusive';
    }

    /**
     * Check if tax is exclusive
     */
    public function isTaxExclusive(): bool
    {
        return $this->tax_type === 'exclusive';
    }

    /**
     * Calculate tax amount for given price
     */
    public function calculateTaxAmount(float $price = null): float
    {
        if (!$this->is_taxable || $this->tax_percentage <= 0) {
            return 0;
        }

        $price = $price ?? $this->getFinalPrice();

        if ($this->tax_type === 'inclusive') {
            // Tax is already included in price, extract it
            return round($price - ($price / (1 + ($this->tax_percentage / 100))), 2);
        } else {
            // Tax is exclusive, calculate it
            return round($price * ($this->tax_percentage / 100), 2);
        }
    }

    /**
     * Get price excluding tax
     */
    public function getPriceExcludingTax(float $price = null): float
    {
        $price = $price ?? $this->getFinalPrice();

        if (!$this->is_taxable) {
            return $price;
        }

        if ($this->tax_type === 'inclusive') {
            // Remove tax from price
            return round($price / (1 + ($this->tax_percentage / 100)), 2);
        }

        return $price;
    }

    /**
     * Get price including tax
     */
    public function getPriceIncludingTax(float $price = null): float
    {
        $price = $price ?? $this->getFinalPrice();

        if (!$this->is_taxable) {
            return $price;
        }

        if ($this->tax_type === 'exclusive') {
            // Add tax to price
            return round($price * (1 + ($this->tax_percentage / 100)), 2);
        }

        return $price;
    }

    /**
     * Get formatted price with tax info
     */
    public function getFormattedPriceWithTax(): string
    {
        $price = $this->getFinalPrice();
        $symbol = $this->curency;

        if (!$this->is_taxable) {
            return $symbol . ' ' . number_format($price, 2);
        }

        $taxAmount = $this->calculateTaxAmount($price);

        if ($this->tax_type === 'inclusive') {
            return $symbol . ' ' . number_format($price, 2) . ' (inc. tax ' . $symbol . ' ' . number_format($taxAmount, 2) . ')';
        } else {
            $priceWithTax = $this->getPriceIncludingTax($price);
            return $symbol . ' ' . number_format($price, 2) . ' + tax ' . $symbol . ' ' . number_format($taxAmount, 2) . ' = ' . $symbol . ' ' . number_format($priceWithTax, 2);
        }
    }

    /**
     * Get tax type label
     */
    public function getTaxTypeLabel(): string
    {
        return match($this->tax_type) {
            'inclusive' => 'Tax Inclusive',
            'exclusive' => 'Tax Exclusive',
            default => 'Unknown',
        };
    }

    /**
     * Get tax info as array
     */
    public function getTaxInfo(): array
    {
        if (!$this->is_taxable) {
            return [
                'is_taxable' => false,
                'tax_percentage' => 0,
                'tax_type' => null,
                'tax_amount' => 0,
                'price_excluding_tax' => $this->getFinalPrice(),
                'price_including_tax' => $this->getFinalPrice(),
            ];
        }

        $price = $this->getFinalPrice();

        return [
            'is_taxable' => true,
            'tax_percentage' => $this->tax_percentage,
            'tax_type' => $this->tax_type,
            'tax_class' => $this->tax_class,
            'tax_amount' => $this->calculateTaxAmount($price),
            'price_excluding_tax' => $this->getPriceExcludingTax($price),
            'price_including_tax' => $this->getPriceIncludingTax($price),
        ];
    }

    /**
     * Get all features
     */
    public function getFeatures(): array
    {
        return $this->features ?? [];
    }

    /**
     * Get feature by label
     */
    public function getFeatureByLabel(string $label)
    {
        $features = $this->getFeatures();

        foreach ($features as $feature) {
            if (isset($feature['label']) && $feature['label'] === $label) {
                return $feature;
            }
        }

        return null;
    }

    /**
     * Get features by type
     */
    public function getFeaturesByType(string $type): array
    {
        $features = $this->getFeatures();

        return array_filter($features, function($feature) use ($type) {
            return isset($feature['type']) && $feature['type'] === $type;
        });
    }

    /**
     * Check if product has features
     */
    public function hasFeatures(): bool
    {
        $features = $this->getFeatures();
        return !empty($features);
    }

    /**
     * Get rich text feature (only one allowed)
     */
    public function getRichTextFeature()
    {
        $features = $this->getFeaturesByType('rich_text');
        return !empty($features) ? reset($features) : null;
    }

    /**
     * Get all single line text features
     */
    public function getSingleLineFeatures(): array
    {
        return $this->getFeaturesByType('single_line');
    }

    /**
     * Get all multiline text features
     */
    public function getMultilineFeatures(): array
    {
        return $this->getFeaturesByType('multiline_text');
    }

    /**
     * Get all links list features
     */
    public function getLinksListFeatures(): array
    {
        return $this->getFeaturesByType('links_list');
    }

    /**
     * Format features for display
     */
    public function getFormattedFeatures(): array
    {
        $features = $this->getFeatures();
        $formatted = [];

        foreach ($features as $feature) {
            if (!isset($feature['type']) || !isset($feature['label'])) {
                continue;
            }

            $formatted[] = [
                'type' => $feature['type'],
                'label' => $feature['label'],
                'value' => $feature['value'] ?? '',
                'type_name' => $this->getFeatureTypeName($feature['type']),
                'icon' => $this->getFeatureIcon($feature['type'])
            ];
        }

        return $formatted;
    }

    /**
     * Get feature type name
     */
    private function getFeatureTypeName(string $type): string
    {
        return match($type) {
            'rich_text' => 'Rich Text',
            'single_line' => 'Single Line Text',
            'multiline_text' => 'Multiline Text',
            'links_list' => 'Links List',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }

    /**
     * Get feature icon
     */
    private function getFeatureIcon(string $type): string
    {
        return match($type) {
            'rich_text' => 'bi-file-richtext',
            'single_line' => 'bi-input-cursor-text',
            'multiline_text' => 'bi-textarea-t',
            'links_list' => 'bi-link-45deg',
            default => 'bi-star'
        };
    }

    /**
     * Validate features structure
     */
    public function validateFeatures(array $features): bool
    {
        $richTextCount = 0;

        foreach ($features as $feature) {
            // Check required fields
            if (!isset($feature['type']) || !isset($feature['label'])) {
                return false;
            }

            // Check valid type
            if (!in_array($feature['type'], ['rich_text', 'single_line', 'multiline_text', 'links_list'])) {
                return false;
            }

            // Count rich text features (should be max 1)
            if ($feature['type'] === 'rich_text') {
                $richTextCount++;
            }

            // Validate links_list structure
            if ($feature['type'] === 'links_list' && isset($feature['value'])) {
                if (!is_array($feature['value'])) {
                    return false;
                }

                foreach ($feature['value'] as $link) {
                    if (!isset($link['title']) || !isset($link['url'])) {
                        return false;
                    }
                }
            }
        }

        // Only one rich text allowed
        return $richTextCount <= 1;
    }

    // ==================== WAREHOUSE & INVENTORY RELATIONSHIPS ====================

    /**
     * Get all warehouse stock for this product
     */
    public function warehouseStock(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class, 'product_id');
    }

    /**
     * Get warehouses that have this product in stock
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse_stock', 'product_id', 'warehouse_id')
                    ->withPivot('quantity', 'reserved_quantity', 'available_quantity', 'location')
                    ->withTimestamps();
    }

    /**
     * Get inventory movements for this product
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id');
    }

    /**
     * Get stock alerts for this product
     */
    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class, 'product_id');
    }

    /**
     * Get active/unresolved stock alerts
     */
    public function activeStockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class, 'product_id')->where('is_resolved', false);
    }

    // ==================== WAREHOUSE STOCK HELPER METHODS ====================

    /**
     * Get total stock across all warehouses
     */
    public function getTotalWarehouseStock(): int
    {
        if (!$this->track_inventory) {
            return PHP_INT_MAX;
        }

        return $this->warehouseStock()->sum('quantity');
    }

    /**
     * Get total available stock (not reserved) across all warehouses
     */
    public function getTotalAvailableStock(): int
    {
        if (!$this->track_inventory) {
            return PHP_INT_MAX;
        }

        return $this->warehouseStock()->sum('available_quantity');
    }

    /**
     * Get total reserved stock across all warehouses
     */
    public function getTotalReservedStock(): int
    {
        if (!$this->track_inventory) {
            return 0;
        }

        return $this->warehouseStock()->sum('reserved_quantity');
    }

    /**
     * Get stock for a specific warehouse
     */
    public function getWarehouseStock(string $warehouseId): int
    {
        if (!$this->track_inventory) {
            return PHP_INT_MAX;
        }

        $stock = $this->warehouseStock()->where('warehouse_id', $warehouseId)->first();
        return $stock ? $stock->quantity : 0;
    }

    /**
     * Get available stock for a specific warehouse
     */
    public function getAvailableWarehouseStock(string $warehouseId): int
    {
        if (!$this->track_inventory) {
            return PHP_INT_MAX;
        }

        $stock = $this->warehouseStock()->where('warehouse_id', $warehouseId)->first();
        return $stock ? $stock->available_quantity : 0;
    }

    /**
     * Check if product has stock in any warehouse
     */
    public function hasWarehouseStock(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->warehouseStock()->where('quantity', '>', 0)->exists();
    }

    /**
     * Check if product has available stock in any warehouse
     */
    public function hasAvailableStock(int $quantity = 1): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->warehouseStock()->where('available_quantity', '>=', $quantity)->exists();
    }

    /**
     * Add stock to a specific warehouse
     */
    public function addWarehouseStock(string $warehouseId, int $quantity, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->id,
                    'warehouse_id' => $warehouseId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $previousQuantity = $stock->quantity;
            $stock->addStock($quantity);

            // Create movement record
            InventoryMovement::create([
                'product_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->fresh()->quantity,
                'reason' => $reason ?? 'Stock added',
            ]);

            // Update product total stock
            $this->updateTotalStock();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Reduce stock from a specific warehouse
     */
    public function reduceWarehouseStock(string $warehouseId, int $quantity, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $stock = ProductWarehouseStock::where('product_id', $this->id)
                                        ->where('warehouse_id', $warehouseId)
                                        ->first();

            if (!$stock || $stock->available_quantity < $quantity) {
                DB::rollBack();
                return false;
            }

            $previousQuantity = $stock->quantity;
            $stock->reduceStock($quantity);

            // Create movement record
            InventoryMovement::create([
                'product_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'quantity' => -$quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->fresh()->quantity,
                'reason' => $reason ?? 'Stock reduced',
            ]);

            // Update product total stock
            $this->updateTotalStock();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(string $fromWarehouseId, string $toWarehouseId, int $quantity, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            // Get source warehouse stock
            $fromStock = ProductWarehouseStock::where('product_id', $this->id)
                                            ->where('warehouse_id', $fromWarehouseId)
                                            ->first();

            if (!$fromStock || $fromStock->available_quantity < $quantity) {
                DB::rollBack();
                return false;
            }

            // Get or create destination warehouse stock
            $toStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->id,
                    'warehouse_id' => $toWarehouseId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            // Reduce from source
            $fromPreviousQty = $fromStock->quantity;
            $fromStock->reduceStock($quantity);

            // Add to destination
            $toPreviousQty = $toStock->quantity;
            $toStock->addStock($quantity);

            // Create movement record
            InventoryMovement::create([
                'product_id' => $this->id,
                'warehouse_id' => $toWarehouseId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'quantity' => $quantity,
                'previous_quantity' => $toPreviousQty,
                'new_quantity' => $toStock->fresh()->quantity,
                'reason' => $reason ?? 'Stock transfer',
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function urlRedirects(): HasMany
    {
        return $this->hasMany(UrlRedirect::class, 'entity_id')
                    ->where('entity_type', 'product');
    }

    /**
     * Reserve stock for an order
     */
    public function reserveStock(string $warehouseId, int $quantity): bool
    {
        $stock = ProductWarehouseStock::where('product_id', $this->id)
                                    ->where('warehouse_id', $warehouseId)
                                    ->first();

        if (!$stock) {
            return false;
        }

        return $stock->reserveStock($quantity);
    }

    /**
     * Release reserved stock
     */
    public function releaseStock(string $warehouseId, int $quantity): bool
    {
        $stock = ProductWarehouseStock::where('product_id', $this->id)
                                    ->where('warehouse_id', $warehouseId)
                                    ->first();

        if (!$stock) {
            return false;
        }

        $stock->releaseStock($quantity);
        return true;
    }

    /**
     * Update total stock quantity from all warehouses
     */
    public function updateTotalStock(): void
    {
        if ($this->track_inventory) {
            $totalStock = $this->warehouseStock()->sum('quantity');
            $this->update(['stock_quantity' => $totalStock]);
        }
    }

    /**
     * Sync product stock with warehouse stocks
     */
    public function syncWarehouseStock(): void
    {
        if ($this->track_inventory) {
            $this->updateTotalStock();

            // Check for alerts
            if ($this->isLowStock() || $this->stock_quantity <= 0) {
                $this->createStockAlerts();
            }
        }
    }

    /**
     * Create stock alerts for all warehouses
     */
    protected function createStockAlerts(): void
    {
        foreach ($this->warehouseStock as $stock) {
            if ($stock->quantity <= 0) {
                StockAlert::updateOrCreate(
                    [
                        'product_id' => $this->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'alert_type' => 'out_of_stock',
                        'is_resolved' => false,
                    ],
                    [
                        'current_quantity' => $stock->quantity,
                        'threshold_quantity' => 0,
                    ]
                );
            } elseif ($stock->quantity <= $this->low_stock_threshold) {
                StockAlert::updateOrCreate(
                    [
                        'product_id' => $this->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'alert_type' => 'low_stock',
                        'is_resolved' => false,
                    ],
                    [
                        'current_quantity' => $stock->quantity,
                        'threshold_quantity' => $this->low_stock_threshold,
                    ]
                );
            }
        }
    }

}
