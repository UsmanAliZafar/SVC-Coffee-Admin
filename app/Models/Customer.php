<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Customer extends Authenticatable
{
    use HasFactory, HasUuids, SoftDeletes, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'customers';

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
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'password',
        'email_verified_at',
        'customer_type',
        'status_key_code',
        'customer_group_id',
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'tax_id',
        'vat_number',
        'business_registration',
        'is_verified',
        'is_newsletter_subscribed',
        'is_sms_subscribed',
        'preferred_language',
        'preferred_currency',
        'acquisition_source',
        'referral_code',
        'referred_by',
        'total_orders',
        'total_spent',
        'average_order_value',
        'first_order_at',
        'last_order_at',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'notes',
        'tags',
        'preferences',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_newsletter_subscribed' => 'boolean',
        'is_sms_subscribed' => 'boolean',
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'first_order_at' => 'datetime',
        'last_order_at' => 'datetime',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
        'tags' => 'array',
        'preferences' => 'array',
        'metadata' => 'array',
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
                $model->status_key_code = 'CUSTOMER_ACTIVE';
            }

            // Set default customer type
            if (empty($model->customer_type)) {
                $model->customer_type = 'individual';
            }

            // Hash password if provided
            if (!empty($model->password) && !Str::startsWith($model->password, '$2y$')) {
                $model->password = Hash::make($model->password);
            }

            // Generate referral code
            if (empty($model->referral_code)) {
                $model->referral_code = strtoupper(Str::random(8));
            }

            // Set created_by
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            // Hash password if changed
            if ($model->isDirty('password') && !empty($model->password) && !Str::startsWith($model->password, '$2y$')) {
                $model->password = Hash::make($model->password);
            }

            // Set updated_by
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }
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
     * Get the status of the customer
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get all orders for this customer
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Get all transactions for this customer
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    /**
     * Get the customer who referred this customer
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_by');
    }

    /**
     * Get all customers referred by this customer
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Customer::class, 'referred_by');
    }

    /**
     * Get creator admin user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get updater admin user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Get only active customers
     */
    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'CUSTOMER_ACTIVE');
    }

    /**
     * Scope: Get only inactive customers
     */
    public function scopeInactive($query)
    {
        return $query->where('status_key_code', 'CUSTOMER_INACTIVE');
    }

    /**
     * Scope: Get only blocked customers
     */
    public function scopeBlocked($query)
    {
        return $query->where('status_key_code', 'CUSTOMER_BLOCKED');
    }

    /**
     * Scope: Get only verified customers
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Get unverified customers
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope: Filter by customer type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('customer_type', $type);
    }

    /**
     * Scope: Get individual customers
     */
    public function scopeIndividual($query)
    {
        return $query->where('customer_type', 'individual');
    }

    /**
     * Scope: Get business customers
     */
    public function scopeBusiness($query)
    {
        return $query->where('customer_type', 'business');
    }

    /**
     * Scope: Get wholesale customers
     */
    public function scopeWholesale($query)
    {
        return $query->where('customer_type', 'wholesale');
    }

    /**
     * Scope: Get VIP customers
     */
    public function scopeVip($query)
    {
        return $query->where('customer_type', 'vip');
    }

    /**
     * Scope: Customers with orders
     */
    public function scopeWithOrders($query)
    {
        return $query->where('total_orders', '>', 0);
    }

    /**
     * Scope: Customers without orders
     */
    public function scopeWithoutOrders($query)
    {
        return $query->where('total_orders', 0);
    }

    /**
     * Scope: Newsletter subscribers
     */
    public function scopeNewsletterSubscribers($query)
    {
        return $query->where('is_newsletter_subscribed', true);
    }

    /**
     * Scope: SMS subscribers
     */
    public function scopeSmsSubscribers($query)
    {
        return $query->where('is_sms_subscribed', true);
    }

    /**
     * Scope: Search customers
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Filter by total spent range
     */
    public function scopeSpentBetween($query, $min, $max)
    {
        return $query->whereBetween('total_spent', [$min, $max]);
    }

    /**
     * Scope: High value customers
     */
    public function scopeHighValue($query, $minSpent = 1000)
    {
        return $query->where('total_spent', '>=', $minSpent);
    }

    /**
     * Scope: Recently active customers
     */
    public function scopeRecentlyActive($query, $days = 30)
    {
        return $query->where('last_order_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Inactive customers
     */
    public function scopeInactiveForDays($query, $days = 90)
    {
        return $query->where('last_order_at', '<', now()->subDays($days))
                    ->orWhereNull('last_order_at');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get display name (with company if available)
     */
    public function getDisplayName(): string
    {
        $name = $this->getFullName();
        if ($this->company_name) {
            return $name . ' (' . $this->company_name . ')';
        }
        return $name;
    }

    /**
     * Get full billing address
     */
    public function getBillingAddress(): string
    {
        $parts = array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billing_city,
            $this->billing_state,
            $this->billing_postal_code,
            $this->billing_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get full shipping address
     */
    public function getShippingAddress(): string
    {
        $parts = array_filter([
            $this->shipping_address_line1,
            $this->shipping_address_line2,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_postal_code,
            $this->shipping_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if customer has billing address
     */
    public function hasBillingAddress(): bool
    {
        return !empty($this->billing_address_line1);
    }

    /**
     * Check if customer has shipping address
     */
    public function hasShippingAddress(): bool
    {
        return !empty($this->shipping_address_line1);
    }

    /**
     * Check if customer is active
     */
    public function isActive(): bool
    {
        return $this->status_key_code === 'CUSTOMER_ACTIVE';
    }

    /**
     * Check if customer is blocked
     */
    public function isBlocked(): bool
    {
        return $this->status_key_code === 'CUSTOMER_BLOCKED';
    }

    /**
     * Check if customer is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified && !is_null($this->email_verified_at);
    }

    /**
     * Check if customer is a business
     */
    public function isBusiness(): bool
    {
        return $this->customer_type === 'business';
    }

    /**
     * Check if customer is VIP
     */
    public function isVip(): bool
    {
        return $this->customer_type === 'vip';
    }

    /**
     * Check if customer is wholesale
     */
    public function isWholesale(): bool
    {
        return $this->customer_type === 'wholesale';
    }

    /**
     * Get customer type label
     */
    public function getCustomerTypeLabel(): string
    {
        return match($this->customer_type) {
            'individual' => 'Individual',
            'business' => 'Business',
            'wholesale' => 'Wholesale',
            'vip' => 'VIP',
            default => ucfirst($this->customer_type),
        };
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'CUSTOMER_ACTIVE' => '<span class="badge bg-success">Active</span>',
            'CUSTOMER_INACTIVE' => '<span class="badge bg-secondary">Inactive</span>',
            'CUSTOMER_BLOCKED' => '<span class="badge bg-danger">Blocked</span>',
            'CUSTOMER_PENDING' => '<span class="badge bg-warning">Pending</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Get customer type badge HTML
     */
    public function getTypeBadge(): string
    {
        return match($this->customer_type) {
            'individual' => '<span class="badge bg-primary">Individual</span>',
            'business' => '<span class="badge bg-info">Business</span>',
            'wholesale' => '<span class="badge bg-warning">Wholesale</span>',
            'vip' => '<span class="badge bg-warning text-dark">VIP</span>',
            default => '<span class="badge bg-light text-dark">' . ucfirst($this->customer_type) . '</span>',
        };
    }

    /**
     * Activate customer
     */
    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'CUSTOMER_ACTIVE']);
    }

    /**
     * Deactivate customer
     */
    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'CUSTOMER_INACTIVE']);
    }

    /**
     * Block customer
     */
    public function block(): bool
    {
        return $this->update(['status_key_code' => 'CUSTOMER_BLOCKED']);
    }

    /**
     * Verify customer
     */
    public function verify(): bool
    {
        return $this->update([
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Update customer statistics
     */
    public function updateStatistics(): void
    {
        $orders = $this->orders()->whereIn('status_key_code', ['ORDER_COMPLETED', 'ORDER_DELIVERED']);

        $totalOrders = $orders->count();
        $totalSpent = $orders->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        $firstOrder = $orders->orderBy('created_at', 'asc')->first();
        $lastOrder = $orders->orderBy('created_at', 'desc')->first();

        $this->update([
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => $averageOrderValue,
            'first_order_at' => $firstOrder ? $firstOrder->created_at : null,
            'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
        ]);
    }

    /**
     * Get lifetime value
     */
    public function getLifetimeValue(): float
    {
        return $this->total_spent;
    }

    /**
     * Get days since last order
     */
    public function getDaysSinceLastOrder(): ?int
    {
        if (!$this->last_order_at) {
            return null;
        }

        return now()->diffInDays($this->last_order_at);
    }

    /**
     * Get days since registration
     */
    public function getDaysSinceRegistration(): int
    {
        return now()->diffInDays($this->created_at);
    }

    /**
     * Check if customer is at risk (no orders in X days)
     */
    public function isAtRisk($days = 90): bool
    {
        $daysSinceLastOrder = $this->getDaysSinceLastOrder();

        if ($daysSinceLastOrder === null) {
            return false; // Never ordered
        }

        return $daysSinceLastOrder >= $days;
    }

    /**
     * Get customer segment
     */
    public function getSegment(): string
    {
        if ($this->total_orders === 0) {
            return 'New';
        }

        if ($this->total_orders === 1) {
            return 'One-time Buyer';
        }

        if ($this->isAtRisk(90)) {
            return 'At Risk';
        }

        if ($this->total_spent >= 5000) {
            return 'High Value';
        }

        if ($this->total_orders >= 5) {
            return 'Loyal';
        }

        return 'Regular';
    }

    /**
     * Add tag to customer
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remove tag from customer
     */
    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        $tags = array_filter($tags, function($t) use ($tag) {
            return $t !== $tag;
        });

        $this->update(['tags' => array_values($tags)]);
    }

    /**
     * Check if customer has tag
     */
    public function hasTag(string $tag): bool
    {
        $tags = $this->tags ?? [];
        return in_array($tag, $tags);
    }

    /**
     * Record login
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
            'login_count' => $this->login_count + 1,
        ]);
    }

    /**
     * Get formatted total spent
     */
    public function getFormattedTotalSpent(): string
    {
        return $this->preferred_currency . ' ' . number_format($this->total_spent, 2);
    }

    /**
     * Get formatted average order value
     */
    public function getFormattedAverageOrderValue(): string
    {
        return $this->preferred_currency . ' ' . number_format($this->average_order_value, 2);
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribeToNewsletter(): bool
    {
        return $this->update(['is_newsletter_subscribed' => true]);
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribeFromNewsletter(): bool
    {
        return $this->update(['is_newsletter_subscribed' => false]);
    }

    /**
     * Subscribe to SMS
     */
    public function subscribeToSms(): bool
    {
        return $this->update(['is_sms_subscribed' => true]);
    }

    /**
     * Unsubscribe from SMS
     */
    public function unsubscribeFromSms(): bool
    {
        return $this->update(['is_sms_subscribed' => false]);
    }

    /**
     * Scope: Get customers created today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Get customers created this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Get customers created this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: Get customers created this year
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    /**
     * Scope: Get returning customers (2+ orders)
     */
    public function scopeReturning($query)
    {
        return $query->where('total_orders', '>=', 2);
    }


    /**
     * Scope: Get customers at risk (no orders in X days)
     */
    public function scopeAtRisk($query, $days = 90)
    {
        return $query->where('last_order_at', '<=', now()->subDays($days))
                    ->where('total_orders', '>', 0);
    }

    /**
     * Scope: Get customers by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from && $to) {
            return $query->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ]);
        }
        return $query;
    }


    /**
     * Scope: Order by registration date
     */
    public function scopeOrderByRegistration($query, $direction = 'desc')
    {
        return $query->orderBy('created_at', $direction);
    }

    /**
     * Scope: Order by last order date
     */
    public function scopeOrderByLastOrder($query, $direction = 'desc')
    {
        return $query->orderBy('last_order_at', $direction);
    }

    /**
     * Scope: Order by total spent
     */
    public function scopeOrderBySpent($query, $direction = 'desc')
    {
        return $query->orderBy('total_spent', $direction);
    }

    /**
     * Scope: Order by total orders
     */
    public function scopeOrderByOrders($query, $direction = 'desc')
    {
        return $query->orderBy('total_orders', $direction);
    }
}
