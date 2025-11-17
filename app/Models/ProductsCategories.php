<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\HasTranslations;
class ProductsCategories extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasTranslations;

    /**
     * The table associated with the model.
     */
    protected $table = 'products_categories';

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
        'title',
        'slug',
        'description',
        'short_description',
        'parent_id',
        'image',
        'banner_image',
        'icon',
        'thumbnail',
        'order',
        'is_featured',
        'show_in_menu',
        'show_on_home',
        'status_key_code',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'structured_data',
        'attributes',
        'products_count',
        'views_count',
        'clicks_count',
        'requires_login',
        'visibility_settings',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_on_home' => 'boolean',
        'requires_login' => 'boolean',
        'order' => 'integer',
        'products_count' => 'integer',
        'views_count' => 'integer',
        'clicks_count' => 'integer',
        'structured_data' => 'array',
        'attributes' => 'array',
        'visibility_settings' => 'array',
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
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'full_path',
        'depth',
        'has_children',
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
                $model->slug = Str::slug($model->title);
            }

            // Set default status if not provided
            if (empty($model->status_key_code)) {
                $model->status_key_code = 'CATEGORY_ACTIVE';
            }

            // Set created_by if admin is authenticated
            if (auth('admin')->check() && empty($model->created_by)) {
                $model->created_by = auth('admin')->id();
            }
        });

        // Update slug when title changes
        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }

            // Set updated_by if admin is authenticated
            if (auth('admin')->check()) {
                $model->updated_by = auth('admin')->id();
            }
        });

        // Decrement parent products_count when deleted
        static::deleted(function ($model) {
            if ($model->parent_id) {
                $parent = static::find($model->parent_id);
                if ($parent) {
                    $parent->decrement('products_count', $model->products_count);
                }
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
     * Parent category relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductsCategories::class, 'parent_id');
    }

    /**
     * Children categories relationship
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductsCategories::class, 'parent_id')
                    ->orderBy('order', 'asc');
    }

    /**
     * Get all descendants (recursive children)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Products relationship
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Active products relationship
     */
    public function activeProducts(): HasMany
    {
        return $this->products()
                    ->where('status_key_code', 'PRODUCT_ACTIVE');
    }

    /**
     * Status relationship
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
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
     * Scope: Get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'CATEGORY_ACTIVE');
    }

    /**
     * Scope: Get only featured categories
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Get categories shown in menu
     */
    public function scopeShowInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope: Get categories shown on home
     */
    public function scopeShowOnHome($query)
    {
        return $query->where('show_on_home', true);
    }

    /**
     * Scope: Get root categories (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Order by custom order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')
                    ->orderBy('title', 'asc');
    }

    /**
     * Scope: Search by title or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: With counts
     */
    public function scopeWithCounts($query)
    {
        return $query->withCount(['products', 'children']);
    }

    /**
     * Get full category path (breadcrumb)
     */
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->title]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->title);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    /**
     * Get category depth level
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Check if category has children
     */
    public function getHasChildrenAttribute(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if category has children (method version)
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if category is root
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Get all parent categories (ancestors)
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Get all descendant IDs (recursive)
     */
    public function getDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Increment products count
     */
    public function incrementProductsCount(int $count = 1): void
    {
        $this->increment('products_count', $count);

        // Also increment parent categories
        if ($this->parent_id) {
            $parent = static::find($this->parent_id);
            if ($parent) {
                $parent->incrementProductsCount($count);
            }
        }
    }

    /**
     * Decrement products count
     */
    public function decrementProductsCount(int $count = 1): void
    {
        $this->decrement('products_count', max(0, $count));

        // Also decrement parent categories
        if ($this->parent_id) {
            $parent = static::find($this->parent_id);
            if ($parent) {
                $parent->decrementProductsCount($count);
            }
        }
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment clicks count
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    /**
     * Get image URL with fallback
     */
    public function getImageUrl(?string $imageField = 'image'): string
    {
        $image = $this->$imageField;

        if ($image) {
            // If starts with http, return as is
            if (Str::startsWith($image, ['http://', 'https://'])) {
                return $image;
            }

            // Otherwise, prepend storage path
            return asset('storage/' . $image);
        }

        // Return placeholder image
        return asset('images/placeholders/not_availble.jpg');
    }

    /**
     * Get all images as array
     */
    public function getAllImages(): array
    {
        return [
            'image' => $this->getImageUrl('image'),
            'banner_image' => $this->getImageUrl('banner_image'),
            'icon' => $this->getImageUrl('icon'),
            'thumbnail' => $this->getImageUrl('thumbnail'),
        ];
    }

    /**
     * Check if category is visible to current user
     */
    public function isVisible(): bool
    {
        // Check if active
        if ($this->status_key_code !== 'CATEGORY_ACTIVE') {
            return false;
        }

        // Check login requirement
        if ($this->requires_login && !auth()->check()) {
            return false;
        }

        // Check visibility settings
        if ($this->visibility_settings) {
            // Add custom visibility logic here
            // e.g., customer groups, countries, date ranges
        }

        return true;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return getStatusBadge($this->status_key_code);
    }

    /**
     * Get breadcrumb trail
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $ancestors = $this->getAncestors();

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'id' => $ancestor->id,
                'title' => $ancestor->title,
                'slug' => $ancestor->slug,
                'url' => route('categories.show', $ancestor->slug),
            ];
        }

        // Add current category
        $breadcrumbs[] = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => route('categories.show', $this->slug),
        ];

        return $breadcrumbs;
    }

    /**
     * Get SEO meta data
     */
    public function getSeoData(): array
    {
        return [
            'title' => $this->meta_title ?: $this->title,
            'description' => $this->meta_description ?: $this->short_description,
            'keywords' => $this->meta_keywords,
            'canonical' => $this->canonical_url ?: route('categories.show', $this->slug),
            'og_title' => $this->meta_title ?: $this->title,
            'og_description' => $this->meta_description ?: $this->short_description,
            'og_image' => $this->getImageUrl('image'),
            'structured_data' => $this->structured_data,
        ];
    }

    /**
     * Static: Get category tree
     */
    public static function getTree(?string $parentId = null)
    {
        return static::where('parent_id', $parentId)
                    ->active()
                    ->ordered()
                    ->with('children')
                    ->get();
    }

    /**
     * Static: Get flat list with indentation
     */
    public static function getFlatList(?string $parentId = null, int $level = 0)
    {
        $categories = static::where('parent_id', $parentId)
                            ->active()
                            ->ordered()
                            ->get();

        $result = collect();

        foreach ($categories as $category) {
            $category->level = $level;
            $category->indent = str_repeat('— ', $level);
            $result->push($category);

            // Get children recursively
            $children = static::getFlatList($category->id, $level + 1);
            $result = $result->merge($children);
        }

        return $result;
    }

    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'short_description',
            'description',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ];
    }

    protected function getTranslationModule(): string
    {
        return 'category';
    }

    public function urlRedirects(): HasMany
    {
        return $this->hasMany(UrlRedirect::class, 'entity_id')
                    ->where('entity_type', 'category');
    }
}
