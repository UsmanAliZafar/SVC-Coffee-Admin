<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryMovement extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'inventory_movements';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reference_type',
        'reference_id',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
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

            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
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

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    // SCOPES
    public function scopeForProduct($query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse($query, string $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // HELPER METHODS
    public function getFormattedQuantity(): string
    {
        return ($this->quantity >= 0 ? '+' : '') . $this->quantity;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'adjustment' => 'Manual Adjustment',
            'purchase' => 'Purchase Order',
            'sale' => 'Order Fulfilled',
            'return' => 'Customer Return',
            'transfer' => 'Warehouse Transfer',
            'damaged' => 'Damaged/Defective',
            'lost' => 'Lost/Stolen',
            'found' => 'Found Inventory',
            'manufacturing' => 'Manufacturing',
            'sync' => 'Warehouse Sync',
            default => ucfirst($this->type)
        };
    }

    public function getTypeBadge(): string
    {
        $badges = [
            'adjustment' => 'primary',
            'purchase' => 'success',
            'sale' => 'info',
            'return' => 'warning',
            'transfer' => 'secondary',
            'damaged' => 'danger',
            'lost' => 'danger',
            'found' => 'success',
            'manufacturing' => 'info',
            'sync' => 'dark',
        ];

        $color = $badges[$this->type] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->getTypeLabel() . '</span>';
    }

    public function isIncrease(): bool
    {
        return $this->quantity > 0;
    }

    public function isDecrease(): bool
    {
        return $this->quantity < 0;
    }
}
