<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'notifications';

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
        'admin_user_id',
        'type',
        'notification_type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'action_url',
        'is_read',
        'read_at',
        'priority',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
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
     * Get the admin user who receives this notification
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * Get notification logs (email delivery tracking)
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'notification_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by admin user
     */
    public function scopeForAdmin($query, string $adminUserId)
    {
        return $query->where('admin_user_id', $adminUserId);
    }

    /**
     * Scope: Filter by type (category)
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by notification type
     */
    public function scopeByNotificationType($query, string $notificationType)
    {
        return $query->where('notification_type', $notificationType);
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: Filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Urgent notifications
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope: High priority notifications
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope: Not expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope: Expired
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Scope: Recent notifications (last 7 days)
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Order by latest first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Order by priority then date
     */
    public function scopeByPriorityAndDate($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                    ->orderBy('created_at', 'desc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->is_read;
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at <= now();
    }

    /**
     * Get time ago (e.g., "2 hours ago")
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get icon HTML
     */
    public function getIconHtml(): string
    {
        $icon = $this->icon ?? 'bi-bell';
        $color = $this->color ?? 'info';

        return '<i class="' . $icon . ' text-' . $color . '"></i>';
    }

    /**
     * Get badge HTML based on type/color
     */
    public function getBadgeHtml(): string
    {
        $colorMap = [
            'success' => 'bg-success',
            'warning' => 'bg-warning',
            'danger' => 'bg-danger',
            'info' => 'bg-info',
            'primary' => 'bg-primary',
            'secondary' => 'bg-secondary',
        ];

        $badgeClass = $colorMap[$this->color] ?? 'bg-info';

        return '<span class="badge ' . $badgeClass . '">' . ucfirst($this->type) . '</span>';
    }

    /**
     * Get priority badge HTML
     */
    public function getPriorityBadge(): string
    {
        return match($this->priority) {
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
            'high' => '<span class="badge bg-warning">High</span>',
            'normal' => '<span class="badge bg-info">Normal</span>',
            'low' => '<span class="badge bg-secondary">Low</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    /**
     * Get data value by key
     */
    public function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get related entity (order, product, etc.)
     */
    public function getRelatedEntity()
    {
        if ($orderId = $this->getData('order_id')) {
            return Order::find($orderId);
        }

        if ($productId = $this->getData('product_id')) {
            return Product::find($productId);
        }

        if ($customerId = $this->getData('customer_id')) {
            return Customer::find($customerId);
        }

        return null;
    }

    /**
     * Bulk mark as read for admin
     */
    public static function markAllAsReadForAdmin(string $adminUserId): int
    {
        return static::where('admin_user_id', $adminUserId)
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                    ]);
    }

    /**
     * Delete old expired notifications
     */
    public static function deleteExpired(): int
    {
        return static::expired()->delete();
    }

    /**
     * Get unread count for admin
     */
    public static function getUnreadCountForAdmin(string $adminUserId): int
    {
        return static::where('admin_user_id', $adminUserId)
                    ->unread()
                    ->notExpired()
                    ->count();
    }

    /**
     * Get recent unread notifications for admin
     */
    public static function getRecentUnreadForAdmin(string $adminUserId, int $limit = 10)
    {
        return static::where('admin_user_id', $adminUserId)
                    ->unread()
                    ->notExpired()
                    ->byPriorityAndDate()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get all notifications for admin (paginated)
     */
    public static function getAllForAdmin(string $adminUserId, int $perPage = 20)
    {
        return static::where('admin_user_id', $adminUserId)
                    ->notExpired()
                    ->latest()
                    ->paginate($perPage);
    }

    /**
     * Create notification for admin(s)
     */
    public static function createForAdmin($adminUserId, array $data): self
    {
        return static::create(array_merge($data, [
            'admin_user_id' => $adminUserId,
        ]));
    }

    /**
     * Bulk delete notifications for admin
     */
    public static function deleteAllForAdmin(string $adminUserId): int
    {
        return static::where('admin_user_id', $adminUserId)->delete();
    }

    /**
     * Bulk delete read notifications for admin
     */
    public static function deleteReadForAdmin(string $adminUserId): int
    {
        return static::where('admin_user_id', $adminUserId)
                    ->where('is_read', true)
                    ->delete();
    }

    /**
     * Get statistics for admin
     */
    public static function getStatsForAdmin(string $adminUserId): array
    {
        $total = static::where('admin_user_id', $adminUserId)->notExpired()->count();
        $unread = static::where('admin_user_id', $adminUserId)->unread()->notExpired()->count();
        $urgent = static::where('admin_user_id', $adminUserId)->unread()->urgent()->notExpired()->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'urgent' => $urgent,
        ];
    }
}
