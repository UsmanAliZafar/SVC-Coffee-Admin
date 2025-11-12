<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductWarehouseStock extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_warehouse_stock';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'variant_id',  // ← ADD THIS
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'location',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'available_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto-calculate available quantity
            $model->available_quantity = ($model->quantity ?? 0) - ($model->reserved_quantity ?? 0);
        });

        static::updating(function ($model) {
            // Auto-calculate available quantity
            $model->available_quantity = $model->quantity - $model->reserved_quantity;
        });

        // After saving, update parent product/variant stock
        static::saved(function ($model) {
            $model->syncParentStock();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if this stock is for a variant
     */
    public function isVariantStock(): bool
    {
        return !is_null($this->variant_id);
    }

    /**
     * Get the entity (product or variant) this stock belongs to
     */
    public function getEntity()
    {
        return $this->isVariantStock() ? $this->variant : $this->product;
    }

    /**
     * Add stock quantity
     */
    public function addStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
        $this->update(['available_quantity' => $this->quantity - $this->reserved_quantity]);
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): void
    {
        $newQuantity = max(0, $this->quantity - $quantity);
        $this->update([
            'quantity' => $newQuantity,
            'available_quantity' => $newQuantity - $this->reserved_quantity
        ]);
    }

    /**
     * Reserve stock for an order
     */
    public function reserveStock(int $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->increment('reserved_quantity', $quantity);
            $this->update(['available_quantity' => $this->quantity - $this->reserved_quantity]);
            return true;
        }
        return false;
    }

    /**
     * Release reserved stock
     */
    public function releaseStock(int $quantity): void
    {
        $newReserved = max(0, $this->reserved_quantity - $quantity);
        $this->update([
            'reserved_quantity' => $newReserved,
            'available_quantity' => $this->quantity - $newReserved
        ]);
    }

    /**
     * Check if has available stock
     */
    public function hasAvailableStock(int $quantity = 1): bool
    {
        return $this->available_quantity >= $quantity;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        $entity = $this->getEntity();

        if (!$entity) {
            return false;
        }

        $threshold = $entity->low_stock_threshold ?? 10;
        return $this->quantity > 0 && $this->quantity <= $threshold;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Sync parent product or variant stock_quantity
     */
    protected function syncParentStock(): void
    {
        if ($this->isVariantStock()) {
            // Update variant's stock (if variant has stock_quantity field)
            // If not, variant uses getTotalStock() which sums warehouse stocks
            // No action needed here
        } else {
            // Update product's total stock_quantity
            $this->product->updateTotalStock();
        }
    }
}
