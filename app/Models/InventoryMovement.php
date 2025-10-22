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

    /**
     * Get human-readable type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            // Core Operations
            'adjustment' => 'Manual Adjustment',
            'purchase' => 'Purchase Order',
            'sale' => 'Order Fulfilled',
            'return' => 'Customer Return',
            'transfer' => 'Warehouse Transfer',
            'sync' => 'Warehouse Sync',

            // Order Management
            'reservation' => 'Stock Reserved',
            'release_reservation' => 'Reservation Released',
            'order_cancelled' => 'Order Cancelled',
            'order_refund' => 'Order Refund',
            'exchange' => 'Product Exchange',
            'replacement' => 'Replacement Sent',

            // Quality Control
            'damaged' => 'Damaged/Defective',
            'quality_fail' => 'Failed Inspection',
            'quality_pass' => 'Passed Inspection',
            'quarantine' => 'Quarantined',
            'quarantine_release' => 'Released from Quarantine',

            // Loss & Found
            'lost' => 'Lost/Missing',
            'found' => 'Found Inventory',
            'theft' => 'Theft',
            'shrinkage' => 'Shrinkage',

            // Supplier Operations
            'supplier_return' => 'Returned to Supplier',
            'supplier_credit' => 'Supplier Credit',
            'restock' => 'Restocked',

            // Production
            'manufacturing' => 'Manufacturing',
            'assembly' => 'Assembled',
            'disassembly' => 'Disassembled',
            'consumption' => 'Production Consumption',
            'scrap' => 'Scrapped',

            // Warehouse Operations
            'receiving' => 'Receiving',
            'putaway' => 'Put Away',
            'picking' => 'Picked',
            'packing' => 'Packing',
            'shipping' => 'Shipped',
            'cycle_count' => 'Cycle Count',
            'physical_count' => 'Physical Count',
            'location_transfer' => 'Location Transfer',

            // Special Cases
            'expired' => 'Expired',
            'obsolete' => 'Obsolete',
            'write_off' => 'Written Off',
            'sample' => 'Sample',
            'promotion' => 'Promotional',
            'internal_use' => 'Internal Use',
            'warranty_replacement' => 'Warranty Replacement',

            default => ucfirst(str_replace('_', ' ', $this->type))
        };
    }

    /**
     * Get badge color for type
     */
    public function getTypeBadge(): string
    {
        $badges = [
            // Core - Blue/Info
            'adjustment' => 'primary',
            'purchase' => 'success',
            'sale' => 'info',
            'return' => 'warning',
            'transfer' => 'secondary',
            'sync' => 'dark',

            // Order Management - Purple/Info
            'reservation' => 'info',
            'release_reservation' => 'secondary',
            'order_cancelled' => 'warning',
            'order_refund' => 'warning',
            'exchange' => 'primary',
            'replacement' => 'info',

            // Quality Control - Yellow/Red
            'damaged' => 'danger',
            'quality_fail' => 'danger',
            'quality_pass' => 'success',
            'quarantine' => 'warning',
            'quarantine_release' => 'success',

            // Loss & Found - Red
            'lost' => 'danger',
            'found' => 'success',
            'theft' => 'danger',
            'shrinkage' => 'danger',

            // Supplier - Green
            'supplier_return' => 'warning',
            'supplier_credit' => 'success',
            'restock' => 'success',

            // Production - Blue
            'manufacturing' => 'info',
            'assembly' => 'info',
            'disassembly' => 'secondary',
            'consumption' => 'primary',
            'scrap' => 'danger',

            // Warehouse - Teal/Secondary
            'receiving' => 'info',
            'putaway' => 'secondary',
            'picking' => 'primary',
            'packing' => 'primary',
            'shipping' => 'success',
            'cycle_count' => 'secondary',
            'physical_count' => 'secondary',
            'location_transfer' => 'secondary',

            // Special - Various
            'expired' => 'danger',
            'obsolete' => 'dark',
            'write_off' => 'danger',
            'sample' => 'info',
            'promotion' => 'warning',
            'internal_use' => 'secondary',
            'warranty_replacement' => 'primary',
        ];

        $color = $badges[$this->type] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $this->getTypeLabel() . '</span>';
    }

    /**
     * Get icon for type
     */
    public function getTypeIcon(): string
    {
        $icons = [
            // Core Operations
            'adjustment' => 'bi-sliders',
            'purchase' => 'bi-cart-plus',
            'sale' => 'bi-cart-check',
            'return' => 'bi-arrow-return-left',
            'transfer' => 'bi-arrow-left-right',
            'sync' => 'bi-arrow-repeat',

            // Order Management
            'reservation' => 'bi-lock',
            'release_reservation' => 'bi-unlock',
            'order_cancelled' => 'bi-x-circle',
            'order_refund' => 'bi-arrow-counterclockwise',
            'exchange' => 'bi-arrow-left-right',
            'replacement' => 'bi-arrow-clockwise',

            // Quality Control
            'damaged' => 'bi-exclamation-triangle',
            'quality_fail' => 'bi-x-circle',
            'quality_pass' => 'bi-check-circle',
            'quarantine' => 'bi-shield-exclamation',
            'quarantine_release' => 'bi-shield-check',

            // Loss & Found
            'lost' => 'bi-question-circle',
            'found' => 'bi-search',
            'theft' => 'bi-shield-x',
            'shrinkage' => 'bi-graph-down',

            // Supplier Operations
            'supplier_return' => 'bi-box-arrow-up',
            'supplier_credit' => 'bi-cash-coin',
            'restock' => 'bi-box-seam',

            // Production
            'manufacturing' => 'bi-gear',
            'assembly' => 'bi-puzzle',
            'disassembly' => 'bi-tools',
            'consumption' => 'bi-dash-circle',
            'scrap' => 'bi-trash',

            // Warehouse Operations
            'receiving' => 'bi-inbox',
            'putaway' => 'bi-archive',
            'picking' => 'bi-hand-index',
            'packing' => 'bi-box-seam',
            'shipping' => 'bi-truck',
            'cycle_count' => 'bi-calculator',
            'physical_count' => 'bi-clipboard-check',
            'location_transfer' => 'bi-arrows-move',

            // Special Cases
            'expired' => 'bi-calendar-x',
            'obsolete' => 'bi-archive',
            'write_off' => 'bi-file-x',
            'sample' => 'bi-gift',
            'promotion' => 'bi-megaphone',
            'internal_use' => 'bi-building',
            'warranty_replacement' => 'bi-shield-plus',
        ];

        return $icons[$this->type] ?? 'bi-circle';
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        $categories = [
            'order' => ['sale', 'return', 'reservation', 'release_reservation', 'order_cancelled', 'order_refund', 'exchange', 'replacement'],
            'quality' => ['damaged', 'quality_fail', 'quality_pass', 'quarantine', 'quarantine_release'],
            'loss' => ['lost', 'found', 'theft', 'shrinkage'],
            'supplier' => ['purchase', 'supplier_return', 'supplier_credit', 'restock'],
            'production' => ['manufacturing', 'assembly', 'disassembly', 'consumption', 'scrap'],
            'warehouse' => ['receiving', 'putaway', 'picking', 'packing', 'shipping', 'transfer', 'location_transfer'],
            'adjustment' => ['adjustment', 'cycle_count', 'physical_count'],
        ];

        if (isset($categories[$category])) {
            return $query->whereIn('type', $categories[$category]);
        }

        return $query;
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
