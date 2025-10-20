<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StockAlert extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'stock_alerts';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'alert_type',
        'current_quantity',
        'threshold_quantity',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'threshold_quantity' => 'integer',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }

    // SCOPES
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeForProduct($query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse($query, string $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // HELPER METHODS
    public function resolve(): bool
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth('admin')->id(),
        ]);
    }

    public function getTypeLabel(): string
    {
        return match($this->alert_type) {
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
            'overstock' => 'Overstock',
            default => ucfirst(str_replace('_', ' ', $this->alert_type))
        };
    }

    public function getTypeBadge(): string
    {
        $badges = [
            'low_stock' => 'warning',
            'out_of_stock' => 'danger',
            'overstock' => 'info',
        ];

        $color = $badges[$this->alert_type] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->getTypeLabel() . '</span>';
    }

    public function getStatusBadge(): string
    {
        return $this->is_resolved
            ? '<span class="badge bg-success">Resolved</span>'
            : '<span class="badge bg-danger">Active</span>';
    }
}
