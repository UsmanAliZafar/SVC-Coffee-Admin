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
            $model->available_quantity = $model->quantity - $model->reserved_quantity;
        });

        static::updating(function ($model) {
            // Auto-calculate available quantity
            $model->available_quantity = $model->quantity - $model->reserved_quantity;
        });
    }

    // RELATIONSHIPS
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // HELPER METHODS
    public function addStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
        $this->update(['available_quantity' => $this->quantity - $this->reserved_quantity]);
    }

    public function reduceStock(int $quantity): void
    {
        $newQuantity = max(0, $this->quantity - $quantity);
        $this->update([
            'quantity' => $newQuantity,
            'available_quantity' => $newQuantity - $this->reserved_quantity
        ]);
    }

    public function reserveStock(int $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->increment('reserved_quantity', $quantity);
            $this->update(['available_quantity' => $this->quantity - $this->reserved_quantity]);
            return true;
        }
        return false;
    }

    public function releaseStock(int $quantity): void
    {
        $newReserved = max(0, $this->reserved_quantity - $quantity);
        $this->update([
            'reserved_quantity' => $newReserved,
            'available_quantity' => $this->quantity - $newReserved
        ]);
    }

    public function hasAvailableStock(int $quantity = 1): bool
    {
        return $this->available_quantity >= $quantity;
    }

    public function isLowStock(): bool
    {
        if (!$this->product) {
            return false;
        }

        return $this->quantity > 0 && $this->quantity <= $this->product->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }
}
