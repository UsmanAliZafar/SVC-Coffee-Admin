<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'notification_templates';

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
        'notification_type',
        'channel',
        'subject',
        'body',
        'variables',
        'is_active',
        'description',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'variables' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
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

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the admin who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get the admin who last updated this template
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Filter by notification type
     */
    public function scopeByType($query, string $notificationType)
    {
        return $query->where('notification_type', $notificationType);
    }

    /**
     * Scope: Filter by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: Email templates
     */
    public function scopeEmail($query)
    {
        return $query->where('channel', 'email');
    }

    /**
     * Scope: SMS templates
     */
    public function scopeSms($query)
    {
        return $query->where('channel', 'sms');
    }

    /**
     * Scope: Active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Inactive templates
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get template for notification type and channel
     */
    public static function getTemplate(string $notificationType, string $channel = 'email'): ?self
    {
        return static::where('notification_type', $notificationType)
                    ->where('channel', $channel)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Replace variables in template
     */
    public function replaceVariables(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Get rendered subject with data
     */
    public function renderSubject(array $data): string
    {
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, $value, $subject);
        }

        return $subject;
    }

    /**
     * Get rendered body with data
     */
    public function renderBody(array $data): string
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $body = str_replace($placeholder, $value, $body);
        }

        return $body;
    }

    /**
     * Get available variables list
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Check if template has variable
     */
    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->getAvailableVariables());
    }

    /**
     * Add variable to template
     */
    public function addVariable(string $variable, string $description = null): bool
    {
        $variables = $this->variables ?? [];

        if (!in_array($variable, $variables)) {
            $variables[$variable] = $description ?? $variable;
            return $this->update(['variables' => $variables]);
        }

        return false;
    }

    /**
     * Remove variable from template
     */
    public function removeVariable(string $variable): bool
    {
        $variables = $this->variables ?? [];

        if (isset($variables[$variable])) {
            unset($variables[$variable]);
            return $this->update(['variables' => $variables]);
        }

        return false;
    }

    /**
     * Activate template
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate template
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(): bool
    {
        return $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return $this->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
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
     * Get notification type label
     */
    public function getTypeLabel(): string
    {
        $config = config("notifications.types.{$this->notification_type}");
        return $config['label'] ?? ucfirst(str_replace('_', ' ', $this->notification_type));
    }

    /**
     * Validate template syntax
     */
    public function validateTemplate(): array
    {
        $errors = [];

        // Check if subject is not empty
        if (empty($this->subject) && $this->channel === 'email') {
            $errors[] = 'Email subject cannot be empty';
        }

        // Check if body is not empty
        if (empty($this->body)) {
            $errors[] = 'Template body cannot be empty';
        }

        // Check for unmatched placeholders
        preg_match_all('/\{([^}]+)\}/', $this->subject . ' ' . $this->body, $matches);
        $usedVariables = $matches[1] ?? [];
        $availableVariables = array_keys($this->variables ?? []);

        $undefined = array_diff($usedVariables, $availableVariables);
        if (!empty($undefined)) {
            $errors[] = 'Undefined variables used: ' . implode(', ', $undefined);
        }

        return $errors;
    }

    /**
     * Check if template is valid
     */
    public function isValid(): bool
    {
        return empty($this->validateTemplate());
    }

    /**
     * Create or update template
     */
    public static function createOrUpdateTemplate(
        string $notificationType,
        string $channel,
        array $data
    ): self {
        return static::updateOrCreate(
            [
                'notification_type' => $notificationType,
                'channel' => $channel,
            ],
            $data
        );
    }

    /**
     * Get all templates grouped by type
     */
    public static function getAllGroupedByType(): array
    {
        $templates = static::active()->get();
        $grouped = [];

        foreach ($templates as $template) {
            $type = $template->notification_type;
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][$template->channel] = $template;
        }

        return $grouped;
    }

    /**
     * Duplicate template
     */
    public function duplicate(string $newNotificationType = null): self
    {
        $newTemplate = $this->replicate();

        if ($newNotificationType) {
            $newTemplate->notification_type = $newNotificationType;
        } else {
            $newTemplate->notification_type = $this->notification_type . '_copy';
        }

        $newTemplate->is_active = false;
        $newTemplate->save();

        return $newTemplate;
    }

    /**
     * Get template preview
     */
    public function getPreview(array $sampleData = []): array
    {
        // Use sample data or default placeholders
        $defaultData = [
            'admin_name' => 'John Admin',
            'order_number' => 'ORD-20250101-ABC123',
            'order_total' => '$150.00',
            'product_name' => 'Premium Coffee Beans',
            'product_sku' => 'PRD-12345',
            'current_stock' => '5',
            'threshold' => '10',
            'customer_name' => 'Jane Customer',
            'customer_email' => 'customer@example.com',
            'store_name' => config('app.name'),
            'store_url' => config('app.url'),
        ];

        $data = array_merge($defaultData, $sampleData);

        return [
            'subject' => $this->renderSubject($data),
            'body' => $this->renderBody($data),
        ];
    }
}
