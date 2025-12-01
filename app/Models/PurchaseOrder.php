<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'po_number',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'vendor_id',
        'reference_number',
        'notes',
        'terms_and_conditions',
        'currency',
        'subtotal',
        'tax_amount',
        'tax_percentage',
        'discount_amount',
        'discount_percentage',
        'shipping_cost',
        'other_charges',
        'total_amount',
        'payment_method',
        'payment_terms',
        'payment_status',
        'paid_amount',
        'payment_due_date',
        'shipping_address',
        'shipping_method',
        'tracking_number',
        'status_key_code',
        'approval_status',
        'approved_by',
        'approved_at',
        'total_items',
        'received_items',
        'is_fully_received',
        'received_date',
        'department',
        'project_code',
        'attachments',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'payment_due_date' => 'date',
        'received_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'total_items' => 'integer',
        'received_items' => 'integer',
        'is_fully_received' => 'boolean',
        'attachments' => 'array',
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

            // Auto-generate PO number if not provided
            if (empty($model->po_number)) {
                $model->po_number = self::generatePONumber();
            }

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'PO_DRAFT';
            }

            // Set created_by if admin is authenticated
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($model) {
            // Set updated_by if admin is authenticated
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_by');
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

    public function scopeDraft($query)
    {
        return $query->where('status_key_code', 'PO_DRAFT');
    }

    public function scopePending($query)
    {
        return $query->where('status_key_code', 'PO_PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_key_code', 'PO_APPROVED');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status_key_code', 'PO_COMPLETED');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status_key_code', 'PO_CANCELLED');
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
                    ->where('payment_due_date', '<', now());
    }

    // ==================== HELPER METHODS ====================

    /**
     * Generate unique PO number
     */
    public static function generatePONumber(): string
    {
        $prefix = 'PO';
        $year = date('Y');
        $month = date('m');

        // Get last PO number for this month
        $lastPO = self::where('po_number', 'like', "{$prefix}-{$year}{$month}-%")
                     ->orderBy('po_number', 'desc')
                     ->first();

        if ($lastPO) {
            // Extract sequence number and increment
            $lastNumber = (int) substr($lastPO->po_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf("%s-%s%s-%04d", $prefix, $year, $month, $newNumber);
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $taxAmount = 0;
        $totalItems = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->subtotal;
            $taxAmount += $item->tax_amount;
            $totalItems++;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_items = $totalItems;

        // Calculate final total
        $this->total_amount = $subtotal
                            + $taxAmount
                            - $this->discount_amount
                            + $this->shipping_cost
                            + $this->other_charges;

        $this->save();
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalance(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotal(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted remaining balance
     */
    public function getFormattedBalance(): string
    {
        return $this->currency . ' ' . number_format($this->getRemainingBalance(), 2);
    }

    /**
     * Check if PO is overdue
     */
    public function isOverdue(): bool
    {
        return $this->payment_status !== 'paid'
            && $this->payment_due_date
            && $this->payment_due_date->isPast();
    }

    /**
     * Check if PO is fully received
     */
    public function isFullyReceived(): bool
    {
        return $this->is_fully_received;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'PO_DRAFT' => '<span class="badge bg-secondary">Draft</span>',
            'PO_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'PO_APPROVED' => '<span class="badge bg-info">Approved</span>',
            'PO_COMPLETED' => '<span class="badge bg-success">Completed</span>',
            'PO_CANCELLED' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-light">Unknown</span>',
        };
    }

    /**
     * Get payment status badge HTML
     */
    public function getPaymentStatusBadge(): string
    {
        return match($this->payment_status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'partial' => '<span class="badge bg-info">Partial</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Approve the purchase order
     */
    public function approve(): bool
    {
        if (auth('admin')->check()) {
            return $this->update([
                'approval_status' => 'approved',
                'approved_by' => auth('admin')->id(),
                'approved_at' => now(),
                'status_key_code' => 'PO_APPROVED'
            ]);
        }
        return false;
    }

    /**
     * Reject the purchase order
     */
    public function reject(): bool
    {
        return $this->update([
            'approval_status' => 'rejected',
            'status_key_code' => 'PO_CANCELLED'
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status_key_code' => 'PO_COMPLETED',
            'is_fully_received' => true,
            'received_date' => now()
        ]);
    }

    /**
     * Update vendor's total purchases
     */
    public function updateVendorPurchases(): void
    {
        if ($this->vendor && $this->status_key_code === 'PO_COMPLETED') {
            $this->vendor->addToPurchases($this->total_amount);
        }
    }
}
