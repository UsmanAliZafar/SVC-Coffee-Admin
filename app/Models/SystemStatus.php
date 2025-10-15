<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SystemStatus extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'system_statuses';

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
        'module',
        'name',
        'key_code',
        'slug',
        'color',
        'bg_color',
        'icon',
        'description',
        'admin_notes',
        'order',
        'is_active',
        'is_default',
        'is_final',
        'allowed_transitions',
        'required_permissions',
        'automation_triggers',
        'send_email',
        'email_template',
        'send_notification',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_final' => 'boolean',
        'send_email' => 'boolean',
        'send_notification' => 'boolean',
        'order' => 'integer',
        'allowed_transitions' => 'array',
        'required_permissions' => 'array',
        'automation_triggers' => 'array',
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

        // Auto-generate UUID on creating
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto-generate slug if not provided
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }

            // Auto-generate key_code if not provided
            if (empty($model->key_code)) {
                $model->key_code = strtoupper(Str::snake($model->module . '_' . $model->name));
            }
        });

        // Ensure only one default status per module
        static::saving(function ($model) {
            if ($model->is_default) {
                static::where('module', $model->module)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id'; // Use UUID for routing
    }

    /**
     * Creator admin user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Updater admin user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    /**
     * Scope: Get statuses by module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope: Get only active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get default status for module
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Get final/terminal statuses
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope: Order by custom order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')
                    ->orderBy('name', 'asc');
    }

    /**
     * Check if transition to another status is allowed
     */
    public function canTransitionTo(string $targetKeyCode): bool
    {
        if (empty($this->allowed_transitions)) {
            return true; // If no restrictions, allow all transitions
        }

        return in_array($targetKeyCode, $this->allowed_transitions);
    }

    /**
     * Get badge HTML with color styling
     */
    public function getBadgeHtml(): string
    {
        return sprintf(
            '<span class="badge" style="background-color: %s; color: %s;">%s %s</span>',
            $this->bg_color,
            $this->color,
            $this->icon ? '<i class="bi ' . $this->icon . '"></i>' : '',
            $this->name
        );
    }

    /**
     * Get module label
     */
    public function getModuleLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->module));
    }

    /**
     * Check if admin user has permission to set this status
     */
    public function userHasPermission($adminUser): bool
    {
        if (empty($this->required_permissions)) {
            return true;
        }

        foreach ($this->required_permissions as $permission) {
            if (!$adminUser->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all possible transition statuses
     */
    public function getTransitionStatuses()
    {
        if (empty($this->allowed_transitions)) {
            return static::byModule($this->module)
                        ->active()
                        ->where('id', '!=', $this->id)
                        ->ordered()
                        ->get();
        }

        return static::byModule($this->module)
                    ->active()
                    ->whereIn('key_code', $this->allowed_transitions)
                    ->ordered()
                    ->get();
    }

    /**
     * Static helper: Get default status for a module
     */
    public static function getDefaultForModule(string $module)
    {
        return static::byModule($module)
                    ->default()
                    ->active()
                    ->first();
    }

    /**
     * Static helper: Get status by key code
     */
    public static function getByKeyCode(string $keyCode)
    {
        return static::where('key_code', $keyCode)
                    ->active()
                    ->first();
    }

    /**
     * Static helper: Get all statuses for a module
     */
    public static function getForModule(string $module)
    {
        return static::byModule($module)
                    ->active()
                    ->ordered()
                    ->get();
    }
}
