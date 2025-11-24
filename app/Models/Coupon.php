<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_purchase_amount',
        'min_items_count',
        'usage_limit_total',
        'usage_limit_per_customer',
        'total_used',
        'valid_from',
        'valid_until',
        'applies_to_sale_items',
        'first_order_only',
        'applicable_product_ids',
        'applicable_category_ids',
        'excluded_product_ids',
        'excluded_category_ids',
        'buy_quantity',
        'get_quantity',
        'buy_product_id',
        'get_product_id',
        'applicable_customer_ids',
        'applicable_customer_groups',
        'is_active',
        'is_featured',
        'created_by',
        'updated_by',
        'admin_notes',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'min_items_count' => 'integer',
        'usage_limit_total' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'total_used' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'applies_to_sale_items' => 'boolean',
        'first_order_only' => 'boolean',
        'applicable_product_ids' => 'array',
        'applicable_category_ids' => 'array',
        'excluded_product_ids' => 'array',
        'excluded_category_ids' => 'array',
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'applicable_customer_ids' => 'array',
        'applicable_customer_groups' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->where('valid_from', '<=', $now)
              ->orWhereNull('valid_from');
        })->where(function ($q) use ($now) {
            $q->where('valid_until', '>=', $now)
              ->orWhereNull('valid_until');
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Validation Methods
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        // Check validity period
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        // Check total usage limit
        if ($this->usage_limit_total && $this->total_used >= $this->usage_limit_total) {
            return false;
        }

        return true;
    }

    public function canBeUsedByCustomer(?string $customerId, ?string $customerEmail = null): array
    {
        // Check if customer-specific restrictions exist
        if (!empty($this->applicable_customer_ids) && $customerId) {
            if (!in_array($customerId, $this->applicable_customer_ids)) {
                return [
                    'valid' => false,
                    'message' => 'This coupon is not available for your account.'
                ];
            }
        }

        // Check usage limit per customer
        if ($customerId) {
            $customerUsageCount = $this->usages()
                ->where('customer_id', $customerId)
                ->count();
        } else {
            $customerUsageCount = $this->usages()
                ->where('customer_email', $customerEmail)
                ->count();
        }

        if ($customerUsageCount >= $this->usage_limit_per_customer) {
            return [
                'valid' => false,
                'message' => 'You have already used this coupon the maximum number of times.'
            ];
        }

        // Check first order only restriction
        if ($this->first_order_only && $customerId) {
            $orderCount = Order::where('customer_id', $customerId)
                ->whereNotIn('status_key_code', ['ORDER_CANCELLED', 'ORDER_FAILED'])
                ->count();

            if ($orderCount > 0) {
                return [
                    'valid' => false,
                    'message' => 'This coupon is only valid for first-time orders.'
                ];
            }
        }

        return ['valid' => true];
    }

    public function isApplicableToCart(array $cartItems, float|string $subtotal, int $itemCount): array
    {
        // ✅ ENSURE SUBTOTAL IS FLOAT
        $subtotal = (float) $subtotal;

        // Check minimum purchase amount
        if ($subtotal < $this->min_purchase_amount) {
            return [
                'valid' => false,
                'message' => "Minimum purchase amount of " . store_currency_symbol() . number_format($this->min_purchase_amount, 2) . " required."
            ];
        }

        // Check minimum items count
        if ($itemCount < $this->min_items_count) {
            return [
                'valid' => false,
                'message' => "Minimum {$this->min_items_count} items required in cart."
            ];
        }

        // Check product restrictions
        if (!empty($this->applicable_product_ids)) {
            $hasApplicableProduct = false;
            foreach ($cartItems as $item) {
                if (in_array($item['product_id'], $this->applicable_product_ids)) {
                    $hasApplicableProduct = true;
                    break;
                }
            }
            if (!$hasApplicableProduct) {
                return [
                    'valid' => false,
                    'message' => 'This coupon is not applicable to the products in your cart.'
                ];
            }
        }

        // Check excluded products
        if (!empty($this->excluded_product_ids)) {
            foreach ($cartItems as $item) {
                if (in_array($item['product_id'], $this->excluded_product_ids)) {
                    return [
                        'valid' => false,
                        'message' => 'This coupon cannot be used with some products in your cart.'
                    ];
                }
            }
        }

        // Buy X Get Y validation
        if ($this->discount_type === 'buy_x_get_y') {
            $buyProductCount = 0;
            foreach ($cartItems as $item) {
                if ($item['product_id'] === $this->buy_product_id) {
                    $buyProductCount += $item['quantity'];
                }
            }

            if ($buyProductCount < $this->buy_quantity) {
                return [
                    'valid' => false,
                    'message' => "You need to purchase at least {$this->buy_quantity} of the required product."
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Discount Calculation
     */
    public function calculateDiscount(array $cartItems, float $subtotal): array
    {
        $discountAmount = 0;
        $freeShipping = false;

        switch ($this->discount_type) {
            case 'percentage':
                $discountAmount = $subtotal * ($this->discount_value / 100);

                // Apply max discount cap if set
                if ($this->max_discount_amount && $discountAmount > $this->max_discount_amount) {
                    $discountAmount = $this->max_discount_amount;
                }
                break;

            case 'fixed_amount':
                $discountAmount = min($this->discount_value, $subtotal);
                break;

            case 'free_shipping':
                $freeShipping = true;
                $discountAmount = 0; // Shipping amount will be set to 0 in checkout
                break;

            case 'buy_x_get_y':
                // Calculate free items discount
                $discountAmount = $this->calculateBuyXGetYDiscount($cartItems);
                break;
        }

        return [
            'discount_amount' => round($discountAmount, 2),
            'free_shipping' => $freeShipping,
            'discount_type' => $this->discount_type,
        ];
    }

    private function calculateBuyXGetYDiscount(array $cartItems): float
    {
        $discount = 0;

        // Find the "get" product in cart
        foreach ($cartItems as $item) {
            if ($item['product_id'] === $this->get_product_id) {
                // Calculate how many free items customer gets
                $freeItemsCount = min($item['quantity'], $this->get_quantity);
                $discount = $item['price'] * $freeItemsCount;
                break;
            }
        }

        return $discount;
    }

    /**
     * Usage Tracking
     */
    public function recordUsage(string $orderId, ?string $customerId, ?string $customerEmail, float $discountAmount, float $orderSubtotal, float $orderTotal, ?string $ipAddress = null): void
    {
        CouponUsage::create([
            'coupon_id' => $this->id,
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'coupon_code' => $this->code,
            'discount_amount' => $discountAmount,
            'order_subtotal' => $orderSubtotal,
            'order_total' => $orderTotal,
            'customer_email' => $customerEmail,
            'ip_address' => $ipAddress,
            'used_at' => now(),
        ]);

        $this->increment('total_used');
    }

    /**
     * Helper Methods
     */
    public function getFormattedDiscountValue(): string
    {
        return match($this->discount_type) {
            'percentage' => $this->discount_value . '%',
            'fixed_amount' =>  store_currency_symbol() . number_format($this->discount_value, 2),
            'free_shipping' => 'Free Shipping',
            'buy_x_get_y' => "Buy {$this->buy_quantity} Get {$this->get_quantity} Free",
        };
    }

    public function getRemainingUses(): ?int
    {
        if (!$this->usage_limit_total) {
            return null; // Unlimited
        }

        return max(0, $this->usage_limit_total - $this->total_used);
    }

    public function isExpired(): bool
    {
        return $this->valid_until && now()->gt($this->valid_until);
    }

    public function isNotYetValid(): bool
    {
        return $this->valid_from && now()->lt($this->valid_from);
    }

    public function getStatusLabel(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->isNotYetValid()) {
            return 'Scheduled';
        }

        if ($this->usage_limit_total && $this->total_used >= $this->usage_limit_total) {
            return 'Limit Reached';
        }

        return 'Active';
    }

    /**
     * Auto-generate unique coupon code
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(\Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($coupon) {
            // Auto-uppercase the code
            $coupon->code = strtoupper($coupon->code);
        });

        static::updating(function ($coupon) {
            // Auto-uppercase the code
            $coupon->code = strtoupper($coupon->code);
        });
    }
}
