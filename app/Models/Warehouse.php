<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
        'is_default',
        'priority',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto-generate code if not provided
            if (empty($model->code)) {
                $model->code = 'WH-' . strtoupper(Str::random(6));
            }

            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }

            // If this is set as default, unset others
            if ($model->is_default) {
                static::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function ($model) {
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }

            // If this is set as default, unset others
            if ($model->is_default && $model->isDirty('is_default')) {
                static::where('id', '!=', $model->id)
                      ->where('is_default', true)
                      ->update(['is_default' => false]);
            }
        });
    }

    // RELATIONSHIPS
    public function stock(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse_stock')
                    ->withPivot('quantity', 'reserved_quantity', 'available_quantity', 'location')
                    ->withTimestamps();
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->orderBy('created_at', 'desc');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }

    // HELPER METHODS
    public function getTotalStock(): int
    {
        return $this->stock()->sum('quantity');
    }

    public function getTotalStockValue(): float
    {
        return $this->stock()
                    ->join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
                    ->selectRaw('SUM(product_warehouse_stock.quantity * products.price) as total_value')
                    ->value('total_value') ?? 0;
    }

    public function getProductStock(string $productId): int
    {
        return $this->stock()
                    ->where('product_id', $productId)
                    ->value('quantity') ?? 0;
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getStatusBadge(): string
    {
        return $this->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    public function hasStock(): bool
    {
        return $this->stock()->where('quantity', '>', 0)->exists();
    }

    public function getLowStockCount(): int
    {
        return $this->stock()
                    ->join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
                    ->where('product_warehouse_stock.quantity', '>', 0)
                    ->whereColumn('product_warehouse_stock.quantity', '<=', 'products.low_stock_threshold')
                    ->count();
    }

    public function getOutOfStockCount(): int
    {
        return $this->stock()
                    ->where('quantity', '<=', 0)
                    ->count();
    }
}
