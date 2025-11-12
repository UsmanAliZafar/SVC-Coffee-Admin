<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        'stock_quantity',           // ← ADD THIS (synced from warehouses)
        'low_stock_threshold',      // ← ADD THIS
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
        'stock_quantity' => 'integer',      // ← ADD THIS
        'low_stock_threshold' => 'integer', // ← ADD THIS
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

            // Set default stock values
            if (!isset($model->stock_quantity)) {
                $model->stock_quantity = 0;
            }
            if (!isset($model->low_stock_threshold)) {
                $model->low_stock_threshold = 10;
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

            // Delete all warehouse stock records
            $model->warehouseStock()->delete();
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
     * Get warehouse stock for this variant
     */
    public function warehouseStock(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class, 'variant_id');
    }

    /**
     * Get warehouses that have this variant in stock
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse_stock', 'variant_id', 'warehouse_id')
                    ->withPivot('quantity', 'reserved_quantity', 'available_quantity', 'location')
                    ->withTimestamps();
    }

    /**
     * Get inventory movements for this variant
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }

    /**
     * Get stock alerts for this variant
     */
    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class, 'variant_id');
    }

    /**
     * Get active/unresolved stock alerts
     */
    public function activeStockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class, 'variant_id')->where('is_resolved', false);
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
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope: Get variants out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope: Get variants with low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope: Get variants on sale
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
                    ->whereColumn('sale_price', '<', 'price');
    }

    // ==================== PRICING METHODS ====================

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

    // ==================== STOCK METHODS (MATCHING PRODUCT MODEL) ====================

    /**
     * Get total stock across all warehouses (synced value)
     */
    public function getTotalStock(): int
    {
        return $this->stock_quantity ?? 0;
    }

    /**
     * Get total stock from warehouse records (for verification)
     */
    public function getTotalWarehouseStock(): int
    {
        return $this->warehouseStock()->sum('quantity');
    }

    /**
     * Get total available stock (not reserved)
     */
    public function getTotalAvailableStock(): int
    {
        return $this->warehouseStock()->sum('available_quantity');
    }

    /**
     * Get total reserved stock
     */
    public function getTotalReservedStock(): int
    {
        return $this->warehouseStock()->sum('reserved_quantity');
    }

    /**
     * Get stock for specific warehouse
     */
    public function getWarehouseStock(string $warehouseId): int
    {
        $stock = $this->warehouseStock()->where('warehouse_id', $warehouseId)->first();
        return $stock ? $stock->quantity : 0;
    }

    /**
     * Get available stock for specific warehouse
     */
    public function getAvailableWarehouseStock(string $warehouseId): int
    {
        $stock = $this->warehouseStock()->where('warehouse_id', $warehouseId)->first();
        return $stock ? $stock->available_quantity : 0;
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if variant is low on stock
     */
    public function isLowStock(): bool
    {
        $threshold = $this->low_stock_threshold ?? 10;
        return $this->stock_quantity > 0 && $this->stock_quantity <= $threshold;
    }

    /**
     * Check if variant is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Check if has stock in any warehouse
     */
    public function hasWarehouseStock(): bool
    {
        return $this->warehouseStock()->where('quantity', '>', 0)->exists();
    }

    /**
     * Check if has available stock in any warehouse
     */
    public function hasAvailableStock(int $quantity = 1): bool
    {
        return $this->warehouseStock()->where('available_quantity', '>=', $quantity)->exists();
    }

    // ==================== WAREHOUSE STOCK MANAGEMENT ====================

    /**
     * Add stock to a specific warehouse
     */
    public function addWarehouseStock(string $warehouseId, int $quantity, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->product_id,
                    'variant_id' => $this->id,
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
                'product_id' => $this->product_id,
                'variant_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->fresh()->quantity,
                'reason' => $reason ?? 'Stock added to variant',
            ]);

            // Update variant total stock
            $this->updateTotalStock();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to add variant warehouse stock: ' . $e->getMessage());
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

            $stock = ProductWarehouseStock::where('product_id', $this->product_id)
                                        ->where('variant_id', $this->id)
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
                'product_id' => $this->product_id,
                'variant_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'quantity' => -$quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->fresh()->quantity,
                'reason' => $reason ?? 'Stock reduced from variant',
            ]);

            // Update variant total stock
            $this->updateTotalStock();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reduce variant warehouse stock: ' . $e->getMessage());
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
            $fromStock = ProductWarehouseStock::where('product_id', $this->product_id)
                                            ->where('variant_id', $this->id)
                                            ->where('warehouse_id', $fromWarehouseId)
                                            ->first();

            if (!$fromStock || $fromStock->available_quantity < $quantity) {
                DB::rollBack();
                return false;
            }

            // Get or create destination warehouse stock
            $toStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->product_id,
                    'variant_id' => $this->id,
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
                'product_id' => $this->product_id,
                'variant_id' => $this->id,
                'warehouse_id' => $toWarehouseId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'quantity' => $quantity,
                'previous_quantity' => $toPreviousQty,
                'new_quantity' => $toStock->fresh()->quantity,
                'reason' => $reason ?? 'Variant stock transfer',
            ]);

            // Total stock remains the same, no need to update
            // But we'll update anyway for consistency
            $this->updateTotalStock();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to transfer variant stock: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reserve stock for an order
     */
    public function reserveStock(string $warehouseId, int $quantity): bool
    {
        $stock = ProductWarehouseStock::where('product_id', $this->product_id)
                                    ->where('variant_id', $this->id)
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
        $stock = ProductWarehouseStock::where('product_id', $this->product_id)
                                    ->where('variant_id', $this->id)
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
        $totalStock = $this->warehouseStock()->sum('quantity');
        $this->update(['stock_quantity' => $totalStock]);

        // Also update parent product's total stock
        $this->product->updateTotalStock();
    }

    /**
     * Sync variant stock with warehouse stocks
     */
    public function syncWarehouseStock(): void
    {
        $this->updateTotalStock();

        // Check for alerts
        if ($this->isLowStock() || $this->stock_quantity <= 0) {
            $this->createStockAlerts();
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
                        'product_id' => $this->product_id,
                        'variant_id' => $this->id,
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
                        'product_id' => $this->product_id,
                        'variant_id' => $this->id,
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

    // ==================== IMAGE & DISPLAY METHODS ====================

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

    /**
     * Get shipping dimensions (with fallback to product dimensions)
     */
    public function getShippingDimensions(): ?array
    {
        if ($this->hasPhysicalDimensions()) {
            return [
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
            ];
        }

        // Fallback to product dimensions
        if ($this->product && $this->product->length && $this->product->width && $this->product->height) {
            return [
                'length' => $this->product->length,
                'width' => $this->product->width,
                'height' => $this->product->height,
            ];
        }

        return null;
    }

    // ==================== STATUS METHODS ====================

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
        $stock = $this->stock_quantity;

        if ($stock <= 0) {
            return '<span class="badge bg-danger">Out of Stock</span>';
        } elseif ($this->isLowStock()) {
            return '<span class="badge bg-warning">Low Stock (' . $stock . ')</span>';
        } else {
            return '<span class="badge bg-success">In Stock (' . $stock . ')</span>';
        }
    }

    // ==================== UTILITY METHODS ====================

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
}
