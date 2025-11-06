<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotificationSetting extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'notification_settings';

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
        'notification_type',
        'is_enabled',
        'send_email',
        'email_address',
        'send_sms',
        'sms_number',
        'threshold_value',
        'custom_settings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'send_email' => 'boolean',
        'send_sms' => 'boolean',
        'threshold_value' => 'integer',
        'custom_settings' => 'array',
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
     * Get the admin user who owns this setting
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
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
     * Scope: Filter by notification type
     */
    public function scopeByType($query, string $notificationType)
    {
        return $query->where('notification_type', $notificationType);
    }

    /**
     * Scope: Get enabled notifications
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: Get email-enabled notifications
     */
    public function scopeEmailEnabled($query)
    {
        return $query->where('send_email', true);
    }

    /**
     * Scope: Get SMS-enabled notifications
     */
    public function scopeSmsEnabled($query)
    {
        return $query->where('send_sms', true);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get notification type configuration
     */
    public function getConfig(): ?array
    {
        return config("notifications.types.{$this->notification_type}");
    }

    /**
     * Get notification type label
     */
    public function getLabel(): string
    {
        $config = $this->getConfig();
        return $config['label'] ?? ucfirst(str_replace('_', ' ', $this->notification_type));
    }

    /**
     * Get notification type description
     */
    public function getDescription(): string
    {
        $config = $this->getConfig();
        return $config['description'] ?? '';
    }

    /**
     * Get notification category
     */
    public function getCategory(): string
    {
        $config = $this->getConfig();
        return $config['category'] ?? 'general';
    }

    /**
     * Get the email address to use (custom or admin's default)
     */
    public function getEmailAddress(): string
    {
        if ($this->email_address) {
            return $this->email_address;
        }

        return $this->adminUser->email ?? '';
    }

    /**
     * Get the SMS number to use (custom or admin's default)
     */
    public function getSmsNumber(): ?string
    {
        if ($this->sms_number) {
            return $this->sms_number;
        }

        return $this->adminUser->phone ?? null;
    }

    /**
     * Check if in-app notification is enabled
     */
    public function isInAppEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Check if email notification is enabled
     */
    public function isEmailEnabled(): bool
    {
        return $this->send_email;
    }

    /**
     * Check if SMS notification is enabled
     */
    public function isSmsEnabled(): bool
    {
        return $this->send_sms;
    }

    /**
     * Enable in-app notification
     */
    public function enableInApp(): bool
    {
        return $this->update(['is_enabled' => true]);
    }

    /**
     * Disable in-app notification
     */
    public function disableInApp(): bool
    {
        return $this->update(['is_enabled' => false]);
    }

    /**
     * Enable email notification
     */
    public function enableEmail(): bool
    {
        return $this->update(['send_email' => true]);
    }

    /**
     * Disable email notification
     */
    public function disableEmail(): bool
    {
        return $this->update(['send_email' => false]);
    }

    /**
     * Enable SMS notification
     */
    public function enableSms(): bool
    {
        return $this->update(['send_sms' => true]);
    }

    /**
     * Disable SMS notification
     */
    public function disableSms(): bool
    {
        return $this->update(['send_sms' => false]);
    }

    /**
     * Toggle in-app notification
     */
    public function toggleInApp(): bool
    {
        return $this->update(['is_enabled' => !$this->is_enabled]);
    }

    /**
     * Toggle email notification
     */
    public function toggleEmail(): bool
    {
        return $this->update(['send_email' => !$this->send_email]);
    }

    /**
     * Toggle SMS notification
     */
    public function toggleSms(): bool
    {
        return $this->update(['send_sms' => !$this->send_sms]);
    }

    /**
     * Update threshold value
     */
    public function updateThreshold(int $value): bool
    {
        return $this->update(['threshold_value' => $value]);
    }

    /**
     * Get or create setting for admin and notification type
     */
    public static function getOrCreateSetting(string $adminUserId, string $notificationType): self
    {
        return static::firstOrCreate(
            [
                'admin_user_id' => $adminUserId,
                'notification_type' => $notificationType,
            ],
            [
                'is_enabled' => true,
                'send_email' => config("notifications.types.{$notificationType}.default_email", false),
                'send_sms' => false,
            ]
        );
    }

    /**
     * Bulk update settings for admin
     */
    public static function bulkUpdateForAdmin(string $adminUserId, array $settings): void
    {
        foreach ($settings as $notificationType => $data) {
            static::updateOrCreate(
                [
                    'admin_user_id' => $adminUserId,
                    'notification_type' => $notificationType,
                ],
                $data
            );
        }
    }

    /**
     * Get all settings for admin
     */
    public static function getAllForAdmin(string $adminUserId): \Illuminate\Database\Eloquent\Collection
    {
        $allTypes = array_keys(config('notifications.types', []));
        $existingSettings = static::where('admin_user_id', $adminUserId)->get()->keyBy('notification_type');

        $settings = new \Illuminate\Database\Eloquent\Collection();

        foreach ($allTypes as $type) {
            if (isset($existingSettings[$type])) {
                $settings->push($existingSettings[$type]);
            } else {
                // Create default setting
                $settings->push(static::getOrCreateSetting($adminUserId, $type));
            }
        }

        return $settings;
    }

    /**
     * Reset to defaults for admin
     */
    public static function resetToDefaultsForAdmin(string $adminUserId): void
    {
        $allTypes = array_keys(config('notifications.types', []));

        foreach ($allTypes as $type) {
            $config = config("notifications.types.{$type}");

            static::updateOrCreate(
                [
                    'admin_user_id' => $adminUserId,
                    'notification_type' => $type,
                ],
                [
                    'is_enabled' => true,
                    'send_email' => $config['default_email'] ?? false,
                    'send_sms' => false,
                    'email_address' => null,
                    'sms_number' => null,
                    'threshold_value' => null,
                ]
            );
        }
    }

    /**
     * Get badge HTML for status
     */
    public function getStatusBadge(): string
    {
        if ($this->is_enabled && $this->send_email) {
            return '<span class="badge bg-success">App + Email</span>';
        } elseif ($this->is_enabled) {
            return '<span class="badge bg-info">App Only</span>';
        } elseif ($this->send_email) {
            return '<span class="badge bg-warning">Email Only</span>';
        } else {
            return '<span class="badge bg-secondary">Disabled</span>';
        }
    }
}
