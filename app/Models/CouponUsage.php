<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'coupon_usage';

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'coupon_code',
        'discount_amount',
        'order_subtotal',
        'order_total',
        'customer_email',
        'ip_address',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_subtotal' => 'decimal:2',
        'order_total' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scopes
     */
    public function scopeByCustomer($query, string $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByCoupon($query, string $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('used_at', [$startDate, $endDate]);
    }
}
