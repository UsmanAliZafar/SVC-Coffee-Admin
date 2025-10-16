<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductTag extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'product_tags';

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
        'name',
        'slug',
        'status_key_code',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
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

            // Set default status
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'TAG_ACTIVE';
            }

            // Set created_by if admin is authenticated
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        // Update slug when name changes
        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }

            // Set updated_by if admin is authenticated
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
        return 'slug';
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the status of the tag
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Get products associated with this tag
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_tag',
            'tag_id',
            'product_id'
        )->withTimestamps();
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

    // ==================== SCOPES ====================

    /**
     * Scope: Get active tags
     */
    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'TAG_ACTIVE');
    }

    /**
     * Scope: Get inactive tags
     */
    public function scopeInactive($query)
    {
        return $query->where('status_key_code', 'TAG_INACTIVE');
    }

    /**
     * Scope: Order by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Scope: Search tags
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Scope: With product count
     */
    public function scopeWithProductCount($query)
    {
        return $query->withCount('products');
    }

    /**
     * Scope: Popular tags (tags with most products)
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->limit($limit);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get products count
     */
    public function getProductsCount(): int
    {
        return $this->products()->count();
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCount(): int
    {
        return $this->products()
                    ->where('status_key_code', 'PRODUCT_ACTIVE')
                    ->count();
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'TAG_ACTIVE' => '<span class="badge bg-success">Active</span>',
            'TAG_INACTIVE' => '<span class="badge bg-secondary">Inactive</span>',
            default => '<span class="badge bg-light">Unknown</span>',
        };
    }

    /**
     * Check if tag is active
     */
    public function isActive(): bool
    {
        return $this->status_key_code === 'TAG_ACTIVE';
    }

    /**
     * Check if tag is inactive
     */
    public function isInactive(): bool
    {
        return $this->status_key_code === 'TAG_INACTIVE';
    }

    /**
     * Activate the tag
     */
    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'TAG_ACTIVE']);
    }

    /**
     * Deactivate the tag
     */
    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'TAG_INACTIVE']);
    }

    /**
     * Check if tag has products
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Get tag display name
     */
    public function getDisplayName(): string
    {
        return ucfirst($this->name);
    }

    /**
     * Get tag URL
     */
    public function getUrl(): string
    {
        return route('tags.show', $this->slug);
    }
}
