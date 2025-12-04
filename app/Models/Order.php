<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'orders';

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
        'order_number',
        'customer_id',
        'guest_email',
        'guest_phone',
        'guest_name',
        'status_key_code',
        'payment_status_key_code',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'discount_code',
        'discount_percentage',
        'discount_type',
        'shipping_method',
        'shipping_carrier',
        'shipping_tracking_number',
        'shipped_at',
        'delivered_at',
        'expected_delivery_date',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'shipping_phone',
        'billing_same_as_shipping',
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'billing_phone',
        'tax_rate',
        'tax_name',
        'tax_inclusive',
        'customer_notes',
        'admin_notes',
        'internal_notes',
        'ip_address',
        'user_agent',
        'order_source',
        'device_type',
        'fulfilled_by',
        'fulfilled_at',
        'warehouse_id',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'invoice_number',
        'invoice_generated_at',
        'payment_gateway',
        'payment_method',
        'transaction_id',
        'is_refunded',
        'refunded_amount',
        'refunded_at',
        'confirmed_at',
        'processing_at',
        'packed_at',
        'metadata',
        'custom_fields',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'billing_same_as_shipping' => 'boolean',
        'tax_inclusive' => 'boolean',
        'is_refunded' => 'boolean',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'invoice_generated_at' => 'datetime',
        'refunded_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'processing_at' => 'datetime',
        'packed_at' => 'datetime',
        'metadata' => 'array',
        'custom_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'created_by',
        'updated_by',
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

            // Auto-generate order number
            if (empty($model->order_number)) {
                $model->order_number = static::generateOrderNumber();
            }

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'ORDER_PENDING';
            }

            // Set default payment status
            if (empty($model->payment_status_key_code)) {
                $model->payment_status_key_code = 'PAYMENT_PENDING';
            }

            // Set default currency
            if (empty($model->currency)) {
                $model->currency = 'USD';
            }

            // Set default order source
            if (empty($model->order_source)) {
                $model->order_source = 'web';
            }

            // Set created_by
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            // Set updated_by
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }

            // Update timestamps based on status changes
            if ($model->isDirty('status_key_code')) {
                $newStatus = $model->status_key_code;

                if ($newStatus === 'ORDER_CONFIRMED' && is_null($model->confirmed_at)) {
                    $model->confirmed_at = now();
                }

                if ($newStatus === 'ORDER_PROCESSING' && is_null($model->processing_at)) {
                    $model->processing_at = now();
                }

                if ($newStatus === 'ORDER_PACKED' && is_null($model->packed_at)) {
                    $model->packed_at = now();
                }

                if ($newStatus === 'ORDER_SHIPPED' && is_null($model->shipped_at)) {
                    $model->shipped_at = now();
                }

                if ($newStatus === 'ORDER_DELIVERED' && is_null($model->delivered_at)) {
                    $model->delivered_at = now();
                }

                if ($newStatus === 'ORDER_CANCELLED' && is_null($model->cancelled_at)) {
                    $model->cancelled_at = now();
                    if (auth('admin')->check()) {
                        $model->cancelled_by = auth('admin')->id();
                    }
                }
            }
        });

        // Update customer statistics when order is created/updated
        static::created(function ($model) {
            if ($model->customer_id) {
                $model->customer->updateStatistics();
            }
        });

        static::updated(function ($model) {
            if ($model->customer_id) {
                $model->customer->updateStatistics();
            }
        });
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        // do {
        //     $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        // } while (static::where('order_number', $orderNumber)->exists());

        return generate_order_number();
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
     * Get the order status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get the payment status
     */
    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'payment_status_key_code', 'key_code');
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get all order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id')->orderBy('sort_order');
    }

    /**
     * Get all transactions for this order
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'order_id');
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the admin who fulfilled the order
     */
    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'fulfilled_by');
    }

    /**
     * Get the admin who cancelled the order
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'cancelled_by');
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

    /**
     * Get order status history
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status_key_code', $status);
    }

    /**
     * Scope: Pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status_key_code', 'ORDER_PENDING');
    }

    /**
     * Scope: Confirmed orders
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status_key_code', 'ORDER_CONFIRMED');
    }

    /**
     * Scope: Processing orders
     */
    public function scopeProcessing($query)
    {
        return $query->where('status_key_code', 'ORDER_PROCESSING');
    }

    /**
     * Scope: Shipped orders
     */
    public function scopeShipped($query)
    {
        return $query->where('status_key_code', 'ORDER_SHIPPED');
    }

    /**
     * Scope: Delivered orders
     */
    public function scopeDelivered($query)
    {
        return $query->where('status_key_code', 'ORDER_DELIVERED');
    }

    /**
     * Scope: Cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->where('status_key_code', 'ORDER_CANCELLED');
    }

    /**
     * Scope: Refunded orders
     */
    public function scopeRefunded($query)
    {
        return $query->where('is_refunded', true);
    }

    /**
     * Scope: Filter by payment status
     */
    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status_key_code', $status);
    }

    /**
     * Scope: Paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status_key_code', 'PAYMENT_PAID');
    }

    /**
     * Scope: Unpaid orders
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status_key_code', ['PAYMENT_PENDING', 'PAYMENT_FAILED']);
    }

    /**
     * Scope: Filter by customer
     */
    public function scopeByCustomer($query, string $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: Guest orders
     */
    public function scopeGuest($query)
    {
        return $query->whereNull('customer_id');
    }

    /**
     * Scope: Registered customer orders
     */
    public function scopeRegistered($query)
    {
        return $query->whereNotNull('customer_id');
    }

    /**
     * Scope: Filter by order source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('order_source', $source);
    }

    /**
     * Scope: Search orders
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhere('invoice_number', 'like', "%{$search}%")
              ->orWhere('guest_email', 'like', "%{$search}%")
              ->orWhere('guest_name', 'like', "%{$search}%")
              ->orWhere('shipping_first_name', 'like', "%{$search}%")
              ->orWhere('shipping_last_name', 'like', "%{$search}%")
              ->orWhere('shipping_phone', 'like', "%{$search}%")
              ->orWhereHas('customer', function ($q) use ($search) {
                  $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by total amount range
     */
    public function scopeAmountBetween($query, $min, $max)
    {
        return $query->whereBetween('total_amount', [$min, $max]);
    }

    /**
     * Scope: Today's orders
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: This week's orders
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope: This month's orders
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: Requires fulfillment
     */
    public function scopeRequiresFulfillment($query)
    {
        return $query->whereIn('status_key_code', ['ORDER_CONFIRMED', 'ORDER_PROCESSING'])
                    ->where('payment_status_key_code', 'PAYMENT_PAID');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if order is from guest
     */
    public function isGuest(): bool
    {
        return is_null($this->customer_id);
    }

    /**
     * Get customer name
     */
    public function getCustomerName(): string
    {
        if ($this->customer) {
            return $this->customer->getFullName();
        }

        return $this->guest_name ?? $this->shipping_first_name . ' ' . $this->shipping_last_name;
    }

    /**
     * Get customer email
     */
    public function getCustomerEmail(): string
    {
        if ($this->customer) {
            return $this->customer->email;
        }

        return $this->guest_email ?? '';
    }

    /**
     * Get customer phone
     */
    public function getCustomerPhone(): string
    {
        if ($this->customer) {
            return $this->customer->phone ?? '';
        }

        return $this->guest_phone ?? $this->shipping_phone ?? '';
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
     * Get full billing address
     */
    public function getBillingAddress(): string
    {
        if ($this->billing_same_as_shipping) {
            return $this->getShippingAddress();
        }

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
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'ORDER_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'ORDER_CONFIRMED' => '<span class="badge bg-info">Confirmed</span>',
            'ORDER_PROCESSING' => '<span class="badge bg-primary">Processing</span>',
            'ORDER_PACKED' => '<span class="badge bg-secondary">Packed</span>',
            'ORDER_SHIPPED' => '<span class="badge bg-purple">Shipped</span>',
            'ORDER_DELIVERED' => '<span class="badge bg-success">Delivered</span>',
            'ORDER_CANCELLED' => '<span class="badge bg-danger">Cancelled</span>',
            'ORDER_REFUNDED' => '<span class="badge bg-dark">Refunded</span>',
            'ORDER_FAILED' => '<span class="badge bg-danger">Failed</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Get payment status badge HTML
     */
    public function getPaymentStatusBadge(): string
    {
        return match($this->payment_status_key_code) {
            'PAYMENT_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'PAYMENT_PAID' => '<span class="badge bg-success">Paid</span>',
            'PAYMENT_PROCESSING' => '<span class="badge bg-info">Processing</span>',
            'PAYMENT_PARTIALLY_PAID' => '<span class="badge bg-info">Partially Paid</span>',
            'PAYMENT_FAILED' => '<span class="badge bg-danger">Failed</span>',
            'PAYMENT_REFUNDED' => '<span class="badge bg-dark">Refunded</span>',
            'PAYMENT_PARTIALLY_REFUNDED' => '<span class="badge bg-secondary">Partially Refunded</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotal(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotal(): string
    {
        return $this->currency . ' ' . number_format($this->subtotal, 2);
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedTax(): string
    {
        return $this->currency . ' ' . number_format($this->tax_amount, 2);
    }

    /**
     * Get formatted shipping amount
     */
    public function getFormattedShipping(): string
    {
        return $this->currency . ' ' . number_format($this->shipping_amount, 2);
    }

    /**
     * Get formatted discount amount
     */
    public function getFormattedDiscount(): string
    {
        return $this->currency . ' ' . number_format($this->discount_amount, 2);
    }

    /**
     * Calculate profit
     */
    public function calculateProfit(): float
    {
        $totalCost = $this->items->sum(function ($item) {
            return ($item->cost_price ?? 0) * $item->quantity;
        });

        return $this->total_amount - $totalCost - $this->shipping_amount;
    }

    /**
     * Get total items count
     */
    public function getTotalItemsCount(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status_key_code, ['ORDER_PENDING', 'ORDER_CONFIRMED']);
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->payment_status_key_code === 'PAYMENT_PAID'
            && in_array($this->status_key_code, ['ORDER_DELIVERED', 'ORDER_SHIPPED']);
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status_key_code === 'PAYMENT_PAID';
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status_key_code === 'ORDER_DELIVERED';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status_key_code === 'ORDER_CANCELLED';
    }

    /**
     * Check if order is refunded
     */
    public function isRefunded(): bool
    {
        return $this->is_refunded;
    }

    /**
     * Confirm order
     */
    public function confirm(): bool
    {
        return $this->update([
            'status_key_code' => 'ORDER_CONFIRMED',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status_key_code' => 'ORDER_PROCESSING',
            'processing_at' => now(),
        ]);
    }

    /**
     * Mark as packed
     */
    public function markAsPacked(): bool
    {
        return $this->update([
            'status_key_code' => 'ORDER_PACKED',
            'packed_at' => now(),
        ]);
    }

    /**
     * Mark as shipped
     */
    public function markAsShipped(?string $trackingNumber = null, ?string $carrier = null): bool
    {
        return $this->update([
            'status_key_code' => 'ORDER_SHIPPED',
            'shipped_at' => now(),
            'shipping_tracking_number' => $trackingNumber ?? $this->shipping_tracking_number,
            'shipping_carrier' => $carrier ?? $this->shipping_carrier,
        ]);
    }


    /**
     * Mark as delivered
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status_key_code' => 'ORDER_DELIVERED',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(?string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Release reserved stock
            foreach ($this->items as $item) {
                if ($item->stock_reserved) {
                    $item->product->releaseStock($item->warehouse_id, $item->quantity);
                    $item->update([
                        'stock_reserved' => false,
                        'stock_reserved_at' => null,
                    ]);
                }
            }

            // Update order
            $this->update([
                'status_key_code' => 'ORDER_CANCELLED',
                'cancelled_at' => now(),
                'cancelled_by' => auth('admin')->id(),
                'cancellation_reason' => $reason,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }

        do {
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (static::where('invoice_number', $invoiceNumber)->exists());

        $this->update([
            'invoice_number' => $invoiceNumber,
            'invoice_generated_at' => now(),
        ]);

        return $invoiceNumber;
    }

    /**
     * Get days since order placed
     */
    public function getDaysSinceOrdered(): int
    {
        return now()->diffInDays($this->created_at);
    }

    /**
     * Get estimated delivery date
     */
    public function getEstimatedDeliveryDate(): ?string
    {
        if ($this->expected_delivery_date) {
            return $this->expected_delivery_date->format('Y-m-d');
        }

        if ($this->shipped_at) {
            return $this->shipped_at->addDays(3)->format('Y-m-d');
        }

        return null;
    }

    /**
     * Recalculate order totals from items
     */
    public function recalculateTotals(): void
    {
        $this->items->load('product');

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->subtotal;
            $taxAmount += $item->tax_amount;
        }

        $totalAmount = $subtotal + $taxAmount + $this->shipping_amount - $this->discount_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Update payment status based on transactions
     */
    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->transactions()
            ->where('transaction_type', 'payment')
            ->where('status_key_code', 'TRANSACTION_SUCCESS')
            ->sum('amount');

        $totalRefunded = $this->transactions()
            ->whereIn('transaction_type', ['refund', 'partial_refund'])
            ->where('status_key_code', 'TRANSACTION_SUCCESS')
            ->sum('amount');

        $netPaid = $totalPaid - $totalRefunded;

        if ($netPaid <= 0) {
            if ($totalRefunded > 0) {
                $this->update(['payment_status_key_code' => 'PAYMENT_REFUNDED']);
            } else {
                $this->update(['payment_status_key_code' => 'PAYMENT_PENDING']);
            }
        } elseif ($netPaid < $this->total_amount) {
            if ($totalRefunded > 0) {
                $this->update(['payment_status_key_code' => 'PAYMENT_PARTIALLY_REFUNDED']);
            } else {
                $this->update(['payment_status_key_code' => 'PAYMENT_PARTIALLY_PAID']);
            }
        } else {
            $this->update(['payment_status_key_code' => 'PAYMENT_PAID']);
        }
    }

    /**
     * Get all status transitions history
     */
    public function getStatusHistory(): array
    {
        $history = [];

        if ($this->created_at) {
            $history[] = [
                'status' => 'ORDER_PENDING',
                'timestamp' => $this->created_at,
                'user' => $this->creator ? $this->creator->name : 'System',
            ];
        }

        if ($this->confirmed_at) {
            $history[] = [
                'status' => 'ORDER_CONFIRMED',
                'timestamp' => $this->confirmed_at,
                'user' => 'System',
            ];
        }

        if ($this->processing_at) {
            $history[] = [
                'status' => 'ORDER_PROCESSING',
                'timestamp' => $this->processing_at,
                'user' => 'System',
            ];
        }

        if ($this->packed_at) {
            $history[] = [
                'status' => 'ORDER_PACKED',
                'timestamp' => $this->packed_at,
                'user' => 'System',
            ];
        }

        if ($this->shipped_at) {
            $history[] = [
                'status' => 'ORDER_SHIPPED',
                'timestamp' => $this->shipped_at,
                'user' => $this->fulfilledBy ? $this->fulfilledBy->name : 'System',
            ];
        }

        if ($this->delivered_at) {
            $history[] = [
                'status' => 'ORDER_DELIVERED',
                'timestamp' => $this->delivered_at,
                'user' => 'System',
            ];
        }

        if ($this->cancelled_at) {
            $history[] = [
                'status' => 'ORDER_CANCELLED',
                'timestamp' => $this->cancelled_at,
                'user' => $this->cancelledBy ? $this->cancelledBy->name : 'System',
            ];
        }

        return $history;
    }

    /**
     * Get payment summary
     */
    public function getPaymentSummary(): array
    {
        $payments = $this->transactions()
            ->where('transaction_type', 'payment')
            ->where('status_key_code', 'TRANSACTION_SUCCESS')
            ->get();

        $refunds = $this->transactions()
            ->whereIn('transaction_type', ['refund', 'partial_refund'])
            ->where('status_key_code', 'TRANSACTION_SUCCESS')
            ->get();

        $totalPaid = $payments->sum('amount');
        $totalRefunded = $refunds->sum('amount');
        $netPaid = $totalPaid - $totalRefunded;
        $balance = $this->total_amount - $netPaid;

        return [
            'order_total' => $this->total_amount,
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'net_paid' => $netPaid,
            'balance' => $balance,
            'is_fully_paid' => $netPaid >= $this->total_amount,
            'is_partially_paid' => $netPaid > 0 && $netPaid < $this->total_amount,
            'is_overpaid' => $netPaid > $this->total_amount,
            'payment_count' => $payments->count(),
            'refund_count' => $refunds->count(),
        ];
    }

    /**
     * Check if order requires action
     */
    public function requiresAction(): bool
    {
        // Order is pending payment
        if ($this->payment_status_key_code === 'PAYMENT_PENDING' &&
            in_array($this->status_key_code, ['ORDER_PENDING', 'ORDER_CONFIRMED'])) {
            return true;
        }

        // Order is paid but not fulfilled
        if ($this->payment_status_key_code === 'PAYMENT_PAID' &&
            in_array($this->status_key_code, ['ORDER_CONFIRMED', 'ORDER_PROCESSING'])) {
            return true;
        }

        // Order is shipped but not delivered for more than 7 days
        if ($this->status_key_code === 'ORDER_SHIPPED' &&
            $this->shipped_at &&
            $this->shipped_at->diffInDays(now()) > 7) {
            return true;
        }

        return false;
    }

    /**
     * Get order priority
     */
    public function getPriority(): string
    {
        // High priority: paid orders waiting for fulfillment
        if ($this->payment_status_key_code === 'PAYMENT_PAID' &&
            in_array($this->status_key_code, ['ORDER_CONFIRMED', 'ORDER_PROCESSING'])) {
            return 'high';
        }

        // Medium priority: confirmed orders
        if ($this->status_key_code === 'ORDER_CONFIRMED') {
            return 'medium';
        }

        // Low priority: pending orders
        if ($this->status_key_code === 'ORDER_PENDING') {
            return 'low';
        }

        return 'normal';
    }

    /**
     * Get order timeline for display
     */
    public function getTimeline(): array
    {
        $timeline = [];

        $timeline[] = [
            'title' => 'Order Placed',
            'status' => 'completed',
            'timestamp' => $this->created_at,
            'icon' => 'bi-cart-check',
            'color' => 'success',
        ];

        if ($this->confirmed_at) {
            $timeline[] = [
                'title' => 'Order Confirmed',
                'status' => 'completed',
                'timestamp' => $this->confirmed_at,
                'icon' => 'bi-check-circle',
                'color' => 'success',
            ];
        }

        if ($this->processing_at) {
            $timeline[] = [
                'title' => 'Processing',
                'status' => 'completed',
                'timestamp' => $this->processing_at,
                'icon' => 'bi-gear',
                'color' => 'primary',
            ];
        }

        if ($this->packed_at) {
            $timeline[] = [
                'title' => 'Packed',
                'status' => 'completed',
                'timestamp' => $this->packed_at,
                'icon' => 'bi-box-seam',
                'color' => 'info',
            ];
        }

        if ($this->shipped_at) {
            $timeline[] = [
                'title' => 'Shipped',
                'status' => 'completed',
                'timestamp' => $this->shipped_at,
                'icon' => 'bi-truck',
                'color' => 'purple',
                'tracking' => $this->shipping_tracking_number,
            ];
        }

        if ($this->delivered_at) {
            $timeline[] = [
                'title' => 'Delivered',
                'status' => 'completed',
                'timestamp' => $this->delivered_at,
                'icon' => 'bi-house-check',
                'color' => 'success',
            ];
        } elseif ($this->shipped_at) {
            $timeline[] = [
                'title' => 'Out for Delivery',
                'status' => 'pending',
                'timestamp' => null,
                'icon' => 'bi-house-check',
                'color' => 'warning',
            ];
        }

        if ($this->cancelled_at) {
            $timeline[] = [
                'title' => 'Cancelled',
                'status' => 'cancelled',
                'timestamp' => $this->cancelled_at,
                'icon' => 'bi-x-circle',
                'color' => 'danger',
                'reason' => $this->cancellation_reason,
            ];
        }

        return $timeline;
    }

    // Get status badge CSS class
    public function getStatusBadgeClass()
    {
        return match($this->status_key_code) {
            'ORDER_PENDING' => 'bg-warning',
            'ORDER_CONFIRMED' => 'bg-info',
            'ORDER_PROCESSING' => 'bg-primary',
            'ORDER_PACKED' => 'bg-secondary',
            'ORDER_SHIPPED' => 'bg-info',
            'ORDER_DELIVERED' => 'bg-success',
            'ORDER_CANCELLED' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    // Get payment status badge class
    public function getPaymentStatusBadgeClass()
    {
        return match($this->payment_status_key_code) {
            'PAYMENT_PAID' => 'bg-success',
            'PAYMENT_PENDING' => 'bg-warning',
            'PAYMENT_FAILED' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    // Get status label
    public function getStatusLabel()
    {
        return ucfirst(strtolower(str_replace('ORDER_', '', $this->status_key_code)));
    }

    // Get payment status label
    public function getPaymentStatusLabel()
    {
        return ucfirst(strtolower(str_replace(['PAYMENT_', '_'], ['', ' '], $this->payment_status_key_code)));
    }


    // Check if can update status
    public function canUpdateStatus()
    {
        return !in_array($this->status_key_code, ['ORDER_DELIVERED', 'ORDER_CANCELLED']);
    }

    // Check if can ship
    public function canShip()
    {
        return in_array($this->status_key_code, ['ORDER_PROCESSING', 'ORDER_PACKED'])
            && $this->isPaid();
    }

    // Check if can refund
    public function canRefund()
    {
        return $this->isPaid() && !$this->is_refunded;
    }
}
