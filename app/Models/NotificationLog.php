<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotificationLog extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'notification_logs';

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
        'notification_id',
        'admin_user_id',
        'channel',
        'recipient_email',
        'recipient_phone',
        'subject',
        'status',
        'sent_at',
        'delivered_at',
        'failed_at',
        'error_message',
        'retry_count',
        'next_retry_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'retry_count' => 'integer',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get the notification
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    /**
     * Get the admin user
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by notification
     */
    public function scopeForNotification($query, string $notificationId)
    {
        return $query->where('notification_id', $notificationId);
    }

    /**
     * Scope: Filter by admin user
     */
    public function scopeForAdmin($query, string $adminUserId)
    {
        return $query->where('admin_user_id', $adminUserId);
    }

    /**
     * Scope: Filter by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: Email logs only
     */
    public function scopeEmail($query)
    {
        return $query->where('channel', 'email');
    }

    /**
     * Scope: SMS logs only
     */
    public function scopeSms($query)
    {
        return $query->where('channel', 'sms');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Pending logs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Sent logs
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: Failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Delivered logs
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope: Bounced logs
     */
    public function scopeBounced($query)
    {
        return $query->where('status', 'bounced');
    }

    /**
     * Scope: Needs retry
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('status', 'failed')
                    ->where('retry_count', '<', 3)
                    ->where(function($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', now());
                    });
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ==================== HELPER METHODS ====================

    /**
     * Mark as sent
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark as bounced
     */
    public function markAsBounced(string $errorMessage = null): bool
    {
        return $this->update([
            'status' => 'bounced',
            'failed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Schedule retry
     */
    public function scheduleRetry(int $minutesDelay = 15): bool
    {
        if ($this->retry_count >= 3) {
            return false;
        }

        return $this->update([
            'retry_count' => $this->retry_count + 1,
            'next_retry_at' => now()->addMinutes($minutesDelay),
        ]);
    }

    /**
     * Check if can retry
     */
    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }

    /**
     * Check if should retry now
     */
    public function shouldRetryNow(): bool
    {
        if (!$this->canRetry()) {
            return false;
        }

        if (!$this->next_retry_at) {
            return true;
        }

        return $this->next_retry_at <= now();
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'sent' => '<span class="badge bg-info">Sent</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'bounced' => '<span class="badge bg-danger">Bounced</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Get channel badge HTML
     */
    public function getChannelBadge(): string
    {
        return match($this->channel) {
            'email' => '<span class="badge bg-primary"><i class="bi bi-envelope"></i> Email</span>',
            'sms' => '<span class="badge bg-info"><i class="bi bi-phone"></i> SMS</span>',
            'push' => '<span class="badge bg-success"><i class="bi bi-bell"></i> Push</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->channel) . '</span>',
        };
    }

    /**
     * Get recipient display
     */
    public function getRecipient(): string
    {
        if ($this->channel === 'email') {
            return $this->recipient_email ?? 'Unknown';
        }

        if ($this->channel === 'sms') {
            return $this->recipient_phone ?? 'Unknown';
        }

        return 'N/A';
    }

    /**
     * Get delivery time (time taken to deliver after sending)
     */
    public function getDeliveryTime(): ?int
    {
        if (!$this->sent_at || !$this->delivered_at) {
            return null;
        }

        return $this->sent_at->diffInSeconds($this->delivered_at);
    }

    /**
     * Get formatted delivery time
     */
    public function getFormattedDeliveryTime(): string
    {
        $seconds = $this->getDeliveryTime();

        if (!$seconds) {
            return 'N/A';
        }

        if ($seconds < 60) {
            return $seconds . ' seconds';
        }

        $minutes = round($seconds / 60, 1);
        return $minutes . ' minutes';
    }

    /**
     * Get statistics for admin
     */
    public static function getStatsForAdmin(string $adminUserId): array
    {
        $query = static::where('admin_user_id', $adminUserId);

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->pending()->count(),
            'sent' => (clone $query)->sent()->count(),
            'delivered' => (clone $query)->delivered()->count(),
            'failed' => (clone $query)->failed()->count(),
            'bounced' => (clone $query)->bounced()->count(),
        ];
    }

    /**
     * Get statistics by channel
     */
    public static function getStatsByChannel(): array
    {
        return [
            'email' => [
                'total' => static::email()->count(),
                'sent' => static::email()->sent()->count(),
                'failed' => static::email()->failed()->count(),
            ],
            'sms' => [
                'total' => static::sms()->count(),
                'sent' => static::sms()->sent()->count(),
                'failed' => static::sms()->failed()->count(),
            ],
        ];
    }

    /**
     * Get delivery rate
     */
    public static function getDeliveryRate(int $days = 7): float
    {
        $total = static::recent($days)->whereIn('status', ['sent', 'delivered', 'failed', 'bounced'])->count();

        if ($total === 0) {
            return 0;
        }

        $delivered = static::recent($days)->delivered()->count();

        return round(($delivered / $total) * 100, 2);
    }

    /**
     * Get average delivery time
     */
    public static function getAverageDeliveryTime(int $days = 7): ?float
    {
        $logs = static::recent($days)
                     ->delivered()
                     ->whereNotNull('sent_at')
                     ->whereNotNull('delivered_at')
                     ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $totalSeconds = $logs->sum(function($log) {
            return $log->getDeliveryTime();
        });

        return round($totalSeconds / $logs->count(), 2);
    }
}
