<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        return store_currency_symbol() . ' ' . number_format($this->total_purchases, 2);
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
     * Sync total purchases from orders
     */
    public function syncTotalPurchases(): void
    {
        // Assuming you have purchase_orders or similar table
        $total = \DB::table('purchase_orders')
            ->where('vendor_id', $this->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->update(['total_purchases' => $total]);
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
     * Add to total purchases
     */
    public function addToPurchases(float $amount): void
    {
        $this->increment('total_purchases', $amount);
    }

    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_ACTIVE']);
    }

    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_INACTIVE']);
    }
}
