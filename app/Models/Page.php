<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use App\Traits\HasTranslations;

class Page extends Model
{
    use HasFactory, SoftDeletes, HasUuids;
    use HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'featured_image_alt',
        'status',
        'visibility',
        'template',
        'display_order',
        'show_in_header',
        'show_in_footer',
        'menu_label',
        'custom_css_class',
        'custom_js',
        'custom_css',
        'parent_id',
        'published_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_in_header' => 'boolean',
        'show_in_footer' => 'boolean',
        'published_at' => 'datetime',
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

        // Auto-generate slug from title if not provided
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }

            // Ensure unique slug
            $originalSlug = $page->slug;
            $count = 1;
            while (static::where('slug', $page->slug)->exists()) {
                $page->slug = $originalSlug . '-' . $count;
                $count++;
            }
        });

        // Update slug when title changes
        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /**
     * Get the admin user who created the page.
     */
    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get the admin user who last updated the page.
     */
    public function updater()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    /**
     * Get the parent page.
     */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages.
     */
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('display_order');
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where(function($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    /**
     * Scope a query to only include draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include public pages.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope a query to only include pages shown in header.
     */
    public function scopeInHeader($query)
    {
        return $query->where('show_in_header', true)
                    ->orderBy('display_order');
    }

    /**
     * Scope a query to only include pages shown in footer.
     */
    public function scopeInFooter($query)
    {
        return $query->where('show_in_footer', true)
                    ->orderBy('display_order');
    }

    /**
     * Scope a query to only include parent pages (no parent_id).
     */
    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               ($this->published_at === null || $this->published_at <= now());
    }

    /**
     * Check if page is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if page is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Get the page URL.
     */
    public function getUrlAttribute(): string
    {
        return $this->slug;
    }

    /**
     * Get the menu label (uses custom label or title).
     */
    public function getMenuLabelAttribute($value): string
    {
        return $value ?? $this->title;
    }

    /**
     * Get the meta title (uses custom meta title or title).
     */
    public function getMetaTitleAttribute($value): string
    {
        return $value ?? $this->title;
    }

    /**
     * Get formatted published date.
     */
    public function getPublishedDateAttribute(): ?string
    {
        return $this->published_at?->format('M d, Y');
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'published' => 'bg-success',
            'draft' => 'bg-warning',
            'archived' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get full path (including parent pages).
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_path . ' / ' . $this->title;
        }
        return $this->title;
    }

    /**
     * Get breadcrumbs array.
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $page = $this;

        while ($page) {
            array_unshift($breadcrumbs, [
                'title' => $page->title,
                'slug' => $page->slug,
                'url' => $page->url,
            ]);
            $page = $page->parent;
        }

        return $breadcrumbs;
    }

    /**
     * Get all descendants (children, grandchildren, etc.).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if page has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Publish the page.
     */
    public function publish(): bool
    {
        $this->status = 'published';
        if (!$this->published_at) {
            $this->published_at = now();
        }
        return $this->save();
    }

    /**
     * Unpublish the page (set to draft).
     */
    public function unpublish(): bool
    {
        $this->status = 'draft';
        return $this->save();
    }

    /**
     * Archive the page.
     */
    public function archive(): bool
    {
        $this->status = 'archived';
        return $this->save();
    }

    public function getTranslatableFields(): array
    {
        return [
            'title',
            'excerpt',
            'content',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'featured_image_alt',
            'menu_label',
        ];
    }

    public function getTranslationModule(): string
    {
        return 'page';
    }
}
