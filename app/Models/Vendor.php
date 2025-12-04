<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'vendors';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable (MINIMIZED).
     */
    protected $fillable = [
        'name',
        'company_name',
        'description',
        'email',
        'phone',
        'mobile',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_number',
        'registration_number',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_routing_number',
        'currency',
        'status_key_code',
        'products_count',
        'total_purchases',
        'orders_count',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_purchases' => 'decimal:2',
        'products_count' => 'integer',
        'orders_count' => 'integer',
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
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->status_key_code)) {
                $model->status_key_code = 'VENDOR_ACTIVE';
            }

            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id', 'id');
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id', 'id')
                    ->where('status_key_code', 'PRODUCT_ACTIVE')
                    ->whereNull('deleted_at');
    }

    /**
     * Get all order items that contain this vendor's products
     */
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Product::class,
            'vendor_id',    // Foreign key on products table
            'product_id',   // Foreign key on order_items table
            'id',           // Local key on vendors table
            'id'            // Local key on products table
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'VENDOR_ACTIVE');
    }

    public function scopeInactive($query)
    {
        return $query->where('status_key_code', 'VENDOR_INACTIVE');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    // ==================== HELPER METHODS ====================

    public function isActive(): bool
    {
        return $this->status_key_code === 'VENDOR_ACTIVE';
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

    public function getDisplayName(): string
    {
        return $this->company_name ?: $this->name;
    }

    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'VENDOR_ACTIVE' => '<span class="badge bg-success">Active</span>',
            'VENDOR_INACTIVE' => '<span class="badge bg-secondary">Inactive</span>',
            'VENDOR_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'VENDOR_SUSPENDED' => '<span class="badge bg-danger">Suspended</span>',
            default => '<span class="badge bg-light">Unknown</span>',
        };
    }

    public function getFormattedTotalPurchases(): string
    {
        return $this->currency . ' ' . number_format($this->total_purchases, 2);
    }

    /**
     * Sync products count from database
     */
    public function syncProductsCount(): void
    {
        $count = $this->products()->count();
        $this->update(['products_count' => $count]);
    }

    /**
     * Sync total purchases (product inventory value)
     */
    public function syncTotalPurchases(): void
    {
        $total = $this->products()
            ->selectRaw('SUM(COALESCE(cost_price, price) * COALESCE(stock_quantity, 0)) as total')
            ->value('total') ?? 0;

        $this->update(['total_purchases' => $total]);
    }

    /**
     * Sync orders count (count distinct orders containing vendor's products)
     */
    public function syncOrdersCount(): void
    {
        $count = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.vendor_id', $this->id)
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->distinct('order_items.order_id')
            ->count('order_items.order_id');

        $this->update(['orders_count' => $count]);
    }

    /**
     * Sync all statistics
     */
    public function syncAllStatistics(): void
    {
        // Products count
        $productsCount = $this->products()->count();

        // Total purchases (inventory value)
        $totalPurchases = $this->products()
            ->selectRaw('SUM(COALESCE(cost_price, price) * COALESCE(stock_quantity, 0)) as total')
            ->value('total') ?? 0;

        // Orders count (distinct orders containing vendor's products)
        $ordersCount = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.vendor_id', $this->id)
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->distinct('order_items.order_id')
            ->count('order_items.order_id');

        $this->update([
            'products_count' => $productsCount,
            'orders_count' => $ordersCount,
            'total_purchases' => $totalPurchases
        ]);
    }

    /**
     * Increment products count
     */
    public function incrementProductsCount(int $count = 1): void
    {
        $this->increment('products_count', $count);
    }

    /**
     * Decrement products count
     */
    public function decrementProductsCount(int $count = 1): void
    {
        $this->decrement('products_count', max(0, $count));
    }

    /**
     * Increment orders count
     */
    public function incrementOrdersCount(int $count = 1): void
    {
        $this->increment('orders_count', $count);
    }

    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_ACTIVE']);
    }

    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_INACTIVE']);
    }

    /**
     * Check if has complete address
     */
    public function hasCompleteAddress(): bool
    {
        return !empty($this->address) &&
               !empty($this->city) &&
               !empty($this->country);
    }

    /**
     * Check if has bank details
     */
    public function hasBankDetails(): bool
    {
        return !empty($this->bank_name) &&
               !empty($this->bank_account_number);
    }

    // ==================== ORDER STATISTICS ====================

    /**
     * Get unique orders containing this vendor's products
     */
    public function getOrders()
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.vendor_id', $this->id)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->select('orders.*')
            ->distinct()
            ->get();
    }

    /**
     * Get orders count by status
     */
    public function getOrdersCountByStatus(string $statusKeyCode): int
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.vendor_id', $this->id)
            ->where('orders.status_key_code', $statusKeyCode)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->distinct('orders.id')
            ->count('orders.id');
    }

    /**
     * Get total revenue from orders containing vendor's products
     */
    public function getTotalRevenue(): float
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.vendor_id', $this->id)
            ->where('orders.status_key_code', 'ORDER_COMPLETED') // Assuming completed orders
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNull('orders.deleted_at')
            ->sum('order_items.total') ?? 0;
    }

    /**
     * Get total quantity sold
     */
    public function getTotalQuantitySold(): int
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.vendor_id', $this->id)
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->sum('order_items.quantity') ?? 0;
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $totalOrders = $this->orders_count ?? 0;
        $completedOrders = $this->getOrdersCountByStatus('ORDER_COMPLETED');
        $pendingOrders = $this->getOrdersCountByStatus('ORDER_PENDING');
        $processingOrders = $this->getOrdersCountByStatus('ORDER_PROCESSING');
        $cancelledOrders = $this->getOrdersCountByStatus('ORDER_CANCELLED');

        $totalRevenue = $this->getTotalRevenue();
        $totalQuantitySold = $this->getTotalQuantitySold();

        $averageOrderValue = $completedOrders > 0
            ? round($totalRevenue / $completedOrders, 2)
            : 0;

        $completionRate = $totalOrders > 0
            ? round(($completedOrders / $totalOrders) * 100, 2)
            : 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue,
            'total_quantity_sold' => $totalQuantitySold,
            'average_order_value' => $averageOrderValue,
            'completion_rate' => $completionRate,
        ];
    }

    /**
     * Get recent orders containing vendor's products
     */
    public function getRecentOrders(int $limit = 10)
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.vendor_id', $this->id)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->select('orders.*')
            ->distinct()
            ->orderBy('orders.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
