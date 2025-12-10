<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'order_items';

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
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'product_description',
        'product_image',
        'variant_options',
        'quantity',
        'unit_price',
        'sale_price',
        'cost_price',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'tax_rate',
        'is_taxable',
        'subtotal',
        'total',
        'warehouse_id',
        'fulfillment_details',
        'is_fulfilled',
        'fulfilled_at',
        'is_refunded',
        'refunded_quantity',
        'refunded_amount',
        'status_key_code',
        'stock_reserved',
        'stock_reserved_at',
        'stock_deducted',
        'stock_deducted_at',
        'notes',
        'custom_fields',
        'metadata',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_taxable' => 'boolean',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'is_fulfilled' => 'boolean',
        'fulfilled_at' => 'datetime',
        'is_refunded' => 'boolean',
        'refunded_quantity' => 'integer',
        'refunded_amount' => 'decimal:2',
        'fulfillment_details' => 'array',
        'stock_reserved' => 'boolean',
        'stock_reserved_at' => 'datetime',
        'stock_deducted' => 'boolean',
        'stock_deducted_at' => 'datetime',
        'variant_options' => 'array',
        'custom_fields' => 'array',
        'metadata' => 'array',
        'sort_order' => 'integer',
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

        static::creating(function ($model) {
            // Auto-generate UUID
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'ITEM_PENDING';
            }

            // Calculate amounts if not set
            if (is_null($model->subtotal)) {
                $model->subtotal = $model->unit_price * $model->quantity;
            }

            if (is_null($model->total)) {
                $model->total = $model->subtotal - $model->discount_amount + $model->tax_amount;
            }

            // Set sort order
            if (is_null($model->sort_order)) {
                $lastItem = static::where('order_id', $model->order_id)
                                 ->orderBy('sort_order', 'desc')
                                 ->first();
                $model->sort_order = $lastItem ? $lastItem->sort_order + 1 : 0;
            }
        });

        static::updating(function ($model) {
            // Recalculate amounts if quantity or price changed
            if ($model->isDirty(['quantity', 'unit_price'])) {
                $model->subtotal = $model->unit_price * $model->quantity;
                $model->total = $model->subtotal - $model->discount_amount + $model->tax_amount;
            }

            if ($model->isDirty(['discount_amount', 'tax_amount'])) {
                $model->total = $model->subtotal - $model->discount_amount + $model->tax_amount;
            }
        });

        // Update order totals when item is created/updated/deleted
        static::saved(function ($model) {
            $model->order->recalculateTotals();
        });

        static::deleted(function ($model) {
            $model->order->recalculateTotals();
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
     * Get the order this item belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the product variant
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the item status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by order
     */
    public function scopeByOrder($query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope: Filter by product
     */
    public function scopeByProduct($query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status_key_code', $status);
    }

    /**
     * Scope: Fulfilled items
     */
    public function scopeFulfilled($query)
    {
        return $query->where('is_fulfilled', true);
    }

    /**
     * Scope: Unfulfilled items
     */
    public function scopeUnfulfilled($query)
    {
        return $query->where('is_fulfilled', false);
    }

    /**
     * Scope: Refunded items
     */
    public function scopeRefunded($query)
    {
        return $query->where('is_refunded', true);
    }

    /**
     * Scope: Stock reserved items
     */
    public function scopeStockReserved($query)
    {
        return $query->where('stock_reserved', true);
    }

    /**
     * Scope: Stock deducted items
     */
    public function scopeStockDeducted($query)
    {
        return $query->where('stock_deducted', true);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get effective price (unit price or sale price)
     */
    public function getEffectivePrice(): float
    {
        return $this->sale_price ?? $this->unit_price;
    }

    /**
     * Get item profit
     */
    public function getProfit(): float
    {
        if (!$this->cost_price) {
            return 0;
        }

        $revenue = $this->getEffectivePrice() * $this->quantity;
        $cost = $this->cost_price * $this->quantity;

        return $revenue - $cost;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMargin(): float
    {
        if (!$this->cost_price || $this->unit_price <= 0) {
            return 0;
        }

        $profit = $this->getProfit();
        $revenue = $this->getEffectivePrice() * $this->quantity;

        return $revenue > 0 ? ($profit / $revenue) * 100 : 0;
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPrice(): string
    {
        return $this->order->currency . ' ' . number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotal(): string
    {
        return $this->order->currency . ' ' . number_format($this->total, 2);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotal(): string
    {
        return $this->order->currency . ' ' . number_format($this->subtotal, 2);
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedTax(): string
    {
        return $this->order->currency . ' ' . number_format($this->tax_amount, 2);
    }

    /**
     * Get formatted discount amount
     */
    public function getFormattedDiscount(): string
    {
        return $this->order->currency . ' ' . number_format($this->discount_amount, 2);
    }

    /**
     * Get product image URL
     */
    public function getProductImageUrl(): string
    {
        if ($this->product_image) {
            if (Str::startsWith($this->product_image, ['http://', 'https://'])) {
                return $this->product_image;
            }

            if (\Storage::disk('public')->exists($this->product_image)) {
                return asset('storage/' . $this->product_image);
            }
        }

        // Try to get from product
        if ($this->product) {
            return $this->product->getMainImageUrl();
        }

        // Return placeholder
        return asset('images/placeholders/not_availble.jpg');
    }

    /**
     * Get variant options as string
     */
    public function getVariantOptionsString(): string
    {
        if (!$this->variant_options) {
            return '';
        }

        $options = [];
        foreach ($this->variant_options as $key => $value) {
            $options[] = ucfirst($key) . ': ' . $value;
        }

        return implode(', ', $options);
    }

    /**
     * Check if item has variant
     */
    public function hasVariant(): bool
    {
        return !is_null($this->product_variant_id) || !empty($this->variant_options);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'ITEM_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'ITEM_PROCESSING' => '<span class="badge bg-primary">Processing</span>',
            'ITEM_FULFILLED' => '<span class="badge bg-success">Fulfilled</span>',
            'ITEM_SHIPPED' => '<span class="badge bg-info">Shipped</span>',
            'ITEM_DELIVERED' => '<span class="badge bg-success">Delivered</span>',
            'ITEM_CANCELLED' => '<span class="badge bg-danger">Cancelled</span>',
            'ITEM_REFUNDED' => '<span class="badge bg-dark">Refunded</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Check if item is fulfilled
     */
    public function isFulfilled(): bool
    {
        return $this->is_fulfilled;
    }

    /**
     * Check if item is refunded
     */
    public function isRefunded(): bool
    {
        return $this->is_refunded;
    }

    /**
     * Check if item can be fulfilled
     */
    public function canBeFulfilled(): bool
    {
        return !$this->is_fulfilled && !$this->is_refunded && $this->order->isPaid();
    }

    /**
     * Check if item can be refunded
     */
    public function canBeRefunded(): bool
    {
        return !$this->is_refunded && $this->order->isPaid();
    }

    /**
     * Mark as fulfilled
     */
    public function markAsFulfilled(): bool
    {
        return $this->update([
            'is_fulfilled' => true,
            'fulfilled_at' => now(),
            'status_key_code' => 'ITEM_FULFILLED',
        ]);
    }

    /**
     * Reserve stock for this item
     */
    public function reserveStock(): bool
    {
        if ($this->stock_reserved || !$this->product) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Reserve stock in warehouse
            if ($this->product->reserveStock($this->warehouse_id, $this->quantity)) {
                $this->update([
                    'stock_reserved' => true,
                    'stock_reserved_at' => now(),
                ]);

                DB::commit();
                return true;
            }

            DB::rollBack();
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Release reserved stock
     */
    public function releaseStock(): bool
    {
        if (!$this->stock_reserved || !$this->product) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Release stock in warehouse
            $this->product->releaseStock($this->warehouse_id, $this->quantity);

            $this->update([
                'stock_reserved' => false,
                'stock_reserved_at' => null,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Deduct stock (after shipping)
     */
    public function deductStock(): bool
    {
        if ($this->stock_deducted || !$this->product) {
            return false;
        }

        DB::beginTransaction();
        try {
            // If stock was reserved, release it first
            if ($this->stock_reserved) {
                $this->product->releaseStock($this->warehouse_id, $this->quantity);
            }

            // Deduct actual stock
            $this->product->reduceWarehouseStock(
                $this->warehouse_id,
                $this->quantity,
                'Order #' . $this->order->order_number . ' shipped'
            );

            $this->update([
                'stock_reserved' => false,
                'stock_reserved_at' => null,
                'stock_deducted' => true,
                'stock_deducted_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Refund item (full or partial)
     */
    public function refund(int $quantity = null, string $reason = null): bool
    {
        if (!$this->canBeRefunded()) {
            return false;
        }

        $quantity = $quantity ?? $this->quantity;

        if ($quantity > $this->quantity || $quantity <= 0) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Calculate refund amount
            $refundAmount = ($this->total / $this->quantity) * $quantity;

            // Restore stock if it was deducted
            if ($this->stock_deducted && $this->product) {
                $this->product->addWarehouseStock(
                    $this->warehouse_id,
                    $quantity,
                    'Refunded from Order #' . $this->order->order_number
                );
            }

            // Update item
            $this->update([
                'is_refunded' => true,
                'refunded_quantity' => $this->refunded_quantity + $quantity,
                'refunded_amount' => $this->refunded_amount + $refundAmount,
                'status_key_code' => 'ITEM_REFUNDED',
            ]);

            // Update order refund info
            $this->order->update([
                'is_refunded' => true,
                'refunded_amount' => $this->order->refunded_amount + $refundAmount,
                'refunded_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Get remaining quantity (not refunded)
     */
    public function getRemainingQuantity(): int
    {
        return $this->quantity - $this->refunded_quantity;
    }

    /**
     * Get remaining amount (not refunded)
     */
    public function getRemainingAmount(): float
    {
        return $this->total - $this->refunded_amount;
    }

    /**
     * Check if partially refunded
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->is_refunded && $this->refunded_quantity < $this->quantity;
    }

    /**
     * Check if fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->is_refunded && $this->refunded_quantity >= $this->quantity;
    }

    /**
     * Get tax info
     */
    public function getTaxInfo(): array
    {
        return [
            'is_taxable' => $this->is_taxable,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'amount_excluding_tax' => $this->subtotal - $this->discount_amount,
            'amount_including_tax' => $this->total,
        ];
    }

    /**
     * Calculate amount excluding tax
     */
    public function getAmountExcludingTax(): float
    {
        return $this->subtotal - $this->discount_amount;
    }

    /**
     * Calculate amount including tax
     */
    public function getAmountIncludingTax(): float
    {
        return $this->total;
    }
}
