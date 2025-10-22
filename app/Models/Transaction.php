<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'transactions';

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
        'transaction_number',
        'order_id',
        'customer_id',
        'transaction_type',
        'payment_gateway',
        'payment_method',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'status_key_code',
        'gateway_transaction_id',
        'gateway_order_id',
        'gateway_status',
        'gateway_response',
        'gateway_error_message',
        'gateway_error_code',
        'card_brand',
        'card_last_four',
        'card_exp_month',
        'card_exp_year',
        'card_holder_name',
        'bank_name',
        'bank_account_number',
        'bank_reference_number',
        'authorization_code',
        'authorized_at',
        'captured_at',
        'refund_transaction_id',
        'refund_reason',
        'refunded_at',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_postal_code',
        'risk_score',
        'risk_level',
        'is_flagged',
        'fraud_notes',
        'ip_address',
        'user_agent',
        'device_type',
        'is_verified',
        'verified_at',
        'verified_by',
        'is_reconciled',
        'reconciled_at',
        'reconciled_by',
        'notes',
        'admin_notes',
        'metadata',
        'gateway_metadata',
        'initiated_at',
        'completed_at',
        'failed_at',
        'retry_count',
        'last_retry_at',
        'next_retry_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'is_flagged' => 'boolean',
        'is_verified' => 'boolean',
        'is_reconciled' => 'boolean',
        'retry_count' => 'integer',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'refunded_at' => 'datetime',
        'verified_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'metadata' => 'array',
        'gateway_metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'gateway_response',
        'card_last_four',
        'card_exp_month',
        'card_exp_year',
        'bank_account_number',
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

            // Auto-generate transaction number
            if (empty($model->transaction_number)) {
                $model->transaction_number = static::generateTransactionNumber();
            }

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'TRANSACTION_PENDING';
            }

            // Set default currency
            if (empty($model->currency)) {
                $model->currency = 'USD';
            }

            // Calculate net amount
            if (is_null($model->net_amount)) {
                $model->net_amount = $model->amount - $model->fee;
            }

            // Set initiated timestamp
            if (is_null($model->initiated_at)) {
                $model->initiated_at = now();
            }

            // Set created_by
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            // Recalculate net amount if amount or fee changed
            if ($model->isDirty(['amount', 'fee'])) {
                $model->net_amount = $model->amount - $model->fee;
            }

            // Set updated_by
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }

            // Update timestamps based on status changes
            if ($model->isDirty('status_key_code')) {
                $newStatus = $model->status_key_code;

                if ($newStatus === 'TRANSACTION_SUCCESS' && is_null($model->completed_at)) {
                    $model->completed_at = now();
                }

                if ($newStatus === 'TRANSACTION_FAILED' && is_null($model->failed_at)) {
                    $model->failed_at = now();
                }

                if ($newStatus === 'TRANSACTION_REFUNDED' && is_null($model->refunded_at)) {
                    $model->refunded_at = now();
                }
            }
        });

        // Update order payment status when transaction is created/updated
        static::saved(function ($model) {
            if ($model->order && $model->transaction_type === 'payment') {
                $model->order->updatePaymentStatus();
            }
        });
    }

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        do {
            $transactionNumber = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        } while (static::where('transaction_number', $transactionNumber)->exists());

        return $transactionNumber;
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
     * Get the transaction status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the original transaction (for refunds)
     */
    public function originalTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'refund_transaction_id');
    }

    /**
     * Get refund transactions
     */
    public function refunds()
    {
        return $this->hasMany(Transaction::class, 'refund_transaction_id');
    }

    /**
     * Get the admin who verified the transaction
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    /**
     * Get the admin who reconciled the transaction
     */
    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reconciled_by');
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
     * Scope: Filter by transaction type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope: Payment transactions
     */
    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    /**
     * Scope: Refund transactions
     */
    public function scopeRefunds($query)
    {
        return $query->whereIn('transaction_type', ['refund', 'partial_refund']);
    }

    /**
     * Scope: Authorization transactions
     */
    public function scopeAuthorizations($query)
    {
        return $query->where('transaction_type', 'authorization');
    }

    /**
     * Scope: Capture transactions
     */
    public function scopeCaptures($query)
    {
        return $query->where('transaction_type', 'capture');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status_key_code', $status);
    }

    /**
     * Scope: Successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status_key_code', 'TRANSACTION_SUCCESS');
    }

    /**
     * Scope: Pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status_key_code', 'TRANSACTION_PENDING');
    }

    /**
     * Scope: Failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status_key_code', 'TRANSACTION_FAILED');
    }

    /**
     * Scope: Filter by payment gateway
     */
    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Scope: Filter by payment method
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: Filter by order
     */
    public function scopeByOrder($query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope: Filter by customer
     */
    public function scopeByCustomer($query, string $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: Flagged transactions
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Scope: Verified transactions
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Unverified transactions
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope: Reconciled transactions
     */
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    /**
     * Scope: Unreconciled transactions
     */
    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    /**
     * Scope: Search transactions
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('transaction_number', 'like', "%{$search}%")
              ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
              ->orWhere('billing_email', 'like', "%{$search}%")
              ->orWhere('card_last_four', 'like', "%{$search}%")
              ->orWhereHas('order', function ($q) use ($search) {
                  $q->where('order_number', 'like', "%{$search}%");
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
     * Scope: Filter by amount range
     */
    public function scopeAmountBetween($query, $min, $max)
    {
        return $query->whereBetween('amount', [$min, $max]);
    }

    /**
     * Scope: Today's transactions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: This month's transactions
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: Needs retry
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('status_key_code', 'TRANSACTION_FAILED')
                    ->where('retry_count', '<', 3)
                    ->where(function($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', now());
                    });
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get formatted fee
     */
    public function getFormattedFee(): string
    {
        return $this->currency . ' ' . number_format($this->fee, 2);
    }

    /**
     * Get formatted net amount
     */
    public function getFormattedNetAmount(): string
    {
        return $this->currency . ' ' . number_format($this->net_amount, 2);
    }

    /**
     * Get transaction type label
     */
    public function getTransactionTypeLabel(): string
    {
        return match($this->transaction_type) {
            'payment' => 'Payment',
            'refund' => 'Refund',
            'partial_refund' => 'Partial Refund',
            'authorization' => 'Authorization',
            'capture' => 'Capture',
            'void' => 'Void',
            'chargeback' => 'Chargeback',
            default => ucfirst(str_replace('_', ' ', $this->transaction_type)),
        };
    }

    /**
     * Get payment gateway label
     */
    public function getPaymentGatewayLabel(): string
    {
        return match($this->payment_gateway) {
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'manual' => 'Manual',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            default => ucfirst(str_replace('_', ' ', $this->payment_gateway)),
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'check' => 'Check',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    /**
     * Get masked card number
     */
    public function getMaskedCardNumber(): ?string
    {
        if (!$this->card_last_four) {
            return null;
        }

        return '**** **** **** ' . $this->card_last_four;
    }

    /**
     * Get card info
     */
    public function getCardInfo(): ?string
    {
        if (!$this->card_brand || !$this->card_last_four) {
            return null;
        }

        return ucfirst($this->card_brand) . ' ending in ' . $this->card_last_four;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'TRANSACTION_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'TRANSACTION_SUCCESS' => '<span class="badge bg-success">Success</span>',
            'TRANSACTION_FAILED' => '<span class="badge bg-danger">Failed</span>',
            'TRANSACTION_CANCELLED' => '<span class="badge bg-secondary">Cancelled</span>',
            'TRANSACTION_REFUNDED' => '<span class="badge bg-dark">Refunded</span>',
            'TRANSACTION_PROCESSING' => '<span class="badge bg-primary">Processing</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Get risk level badge HTML
     */
    public function getRiskLevelBadge(): string
    {
        if (!$this->risk_level) {
            return '<span class="badge bg-light text-dark">—</span>';
        }

        return match($this->risk_level) {
            'low' => '<span class="badge bg-success">Low Risk</span>',
            'medium' => '<span class="badge bg-warning">Medium Risk</span>',
            'high' => '<span class="badge bg-danger">High Risk</span>',
            default => '<span class="badge bg-light text-dark">' . ucfirst($this->risk_level) . '</span>',
        };
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status_key_code === 'TRANSACTION_SUCCESS';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status_key_code === 'TRANSACTION_PENDING';
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status_key_code === 'TRANSACTION_FAILED';
    }

    /**
     * Check if transaction is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status_key_code === 'TRANSACTION_REFUNDED';
    }

    /**
     * Check if transaction is a payment
     */
    public function isPayment(): bool
    {
        return $this->transaction_type === 'payment';
    }

    /**
     * Check if transaction is a refund
     */
    public function isRefund(): bool
    {
        return in_array($this->transaction_type, ['refund', 'partial_refund']);
    }

    /**
     * Mark as successful
     */
    public function markAsSuccessful(string $gatewayTransactionId = null): bool
    {
        $data = [
            'status_key_code' => 'TRANSACTION_SUCCESS',
            'completed_at' => now(),
        ];

        if ($gatewayTransactionId) {
            $data['gateway_transaction_id'] = $gatewayTransactionId;
        }

        return $this->update($data);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage = null, string $errorCode = null): bool
    {
        return $this->update([
            'status_key_code' => 'TRANSACTION_FAILED',
            'failed_at' => now(),
            'gateway_error_message' => $errorMessage,
            'gateway_error_code' => $errorCode,
        ]);
    }

    /**
     * Verify transaction
     */
    public function verify(): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth('admin')->id(),
        ]);
    }

    /**
     * Reconcile transaction
     */
    public function reconcile(): bool
    {
        return $this->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => auth('admin')->id(),
        ]);
    }

    /**
     * Flag transaction for review
     */
    public function flag(string $reason = null): bool
    {
        return $this->update([
            'is_flagged' => true,
            'fraud_notes' => $reason ?? $this->fraud_notes,
        ]);
    }

    /**
     * Unflag transaction
     */
    public function unflag(): bool
    {
        return $this->update([
            'is_flagged' => false,
        ]);
    }

    /**
     * Can retry transaction
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && $this->retry_count < 3;
    }

    /**
     * Schedule retry
     */
    public function scheduleRetry(int $minutesDelay = 15): bool
    {
        if (!$this->canRetry()) {
            return false;
        }

        return $this->update([
            'next_retry_at' => now()->addMinutes($minutesDelay),
            'last_retry_at' => now(),
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Get processing time in seconds
     */
    public function getProcessingTime(): ?int
    {
        if (!$this->initiated_at || !$this->completed_at) {
            return null;
        }

        return $this->initiated_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get age in days
     */
    public function getAgeInDays(): int
    {
        return now()->diffInDays($this->created_at);
    }

    /**
     * Create refund transaction
     */
    public function createRefund(float $amount, string $reason = null): ?Transaction
    {
        if (!$this->isSuccessful() || !$this->isPayment()) {
            return null;
        }

        if ($amount > $this->amount) {
            return null;
        }

        DB::beginTransaction();
        try {
            $refund = static::create([
                'order_id' => $this->order_id,
                'customer_id' => $this->customer_id,
                'transaction_type' => $amount < $this->amount ? 'partial_refund' : 'refund',
                'payment_gateway' => $this->payment_gateway,
                'payment_method' => $this->payment_method,
                'amount' => $amount,
                'currency' => $this->currency,
                'refund_transaction_id' => $this->id,
                'refund_reason' => $reason,
                'status_key_code' => 'TRANSACTION_PENDING',
            ]);

            DB::commit();
            return $refund;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    /**
     * Get total refunded amount
     */
    public function getTotalRefundedAmount(): float
    {
        return $this->refunds()->where('status_key_code', 'TRANSACTION_SUCCESS')->sum('amount');
    }

    /**
     * Get remaining refundable amount
     */
    public function getRemainingRefundableAmount(): float
    {
        return $this->amount - $this->getTotalRefundedAmount();
    }

    /**
     * Check if fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->getTotalRefundedAmount() >= $this->amount;
    }

    /**
     * Check if partially refunded
     */
    public function isPartiallyRefunded(): bool
    {
        $refunded = $this->getTotalRefundedAmount();
        return $refunded > 0 && $refunded < $this->amount;
    }
}
