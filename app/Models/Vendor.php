<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'vendors';

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
        'company_name',
        'description',
        'email',
        'phone',
        'mobile',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_number',
        'registration_number',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_routing_number',
        'logo',
        'banner_image',
        'social_media',
        'payment_terms',
        'credit_limit',
        'currency',
        'status_key_code',
        'products_count',
        'total_purchases',
        'orders_count',
        'rating',
        'reviews_count',
        'is_featured',
        'is_verified',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'attributes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'credit_limit' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'rating' => 'decimal:2',
        'products_count' => 'integer',
        'orders_count' => 'integer',
        'reviews_count' => 'integer',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'social_media' => 'array',
        'attributes' => 'array',
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
                $model->status_key_code = 'VENDOR_ACTIVE';
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
     * Get the status of the vendor
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id', 'id');
    }

    public function activeProducts()
    {
        return $this->hasMany(Product::class, 'vendor_id', 'id')
                    ->where('status_key_code', 'PRODUCT_ACTIVE')
                    ->whereNull('deleted_at');
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
     * Scope: Get active vendors
     */
    public function scopeActive($query)
    {
        return $query->where('status_key_code', 'VENDOR_ACTIVE');
    }

    /**
     * Scope: Get inactive vendors
     */
    public function scopeInactive($query)
    {
        return $query->where('status_key_code', 'VENDOR_INACTIVE');
    }

    /**
     * Scope: Get featured vendors
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Get verified vendors
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc');
    }

    /**
     * Scope: Search vendors
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Filter by country
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope: Filter by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope: With product count
     */
    public function scopeWithProductCount($query)
    {
        return $query->withCount('products');
    }

    /**
     * Scope: High rated vendors (4+ stars)
     */
    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4.0);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if vendor is active
     */
    public function isActive(): bool
    {
        return $this->status_key_code === 'VENDOR_ACTIVE';
    }

    /**
     * Check if vendor is featured
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Check if vendor is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Get logo URL with fallback
     */
    public function getLogoUrl(): string
    {
        if ($this->logo) {
            // If starts with http, return as is
            if (Str::startsWith($this->logo, ['http://', 'https://'])) {
                return $this->logo;
            }

            // Check if file exists in storage
            if (Storage::disk('public')->exists($this->logo)) {
                return asset('storage/' . $this->logo);
            }
        }

        // Return placeholder
        return asset('images/placeholders/vendor-logo-placeholder.png');
    }

    /**
     * Get banner image URL with fallback
     */
    public function getBannerUrl(): string
    {
        if ($this->banner_image) {
            if (Str::startsWith($this->banner_image, ['http://', 'https://'])) {
                return $this->banner_image;
            }

            if (Storage::disk('public')->exists($this->banner_image)) {
                return asset('storage/' . $this->banner_image);
            }
        }

        return asset('images/placeholders/vendor-banner-placeholder.jpg');
    }

    /**
     * Get full address as string
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get display name (company name or name)
     */
    public function getDisplayName(): string
    {
        return $this->company_name ?: $this->name;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        return match($this->status_key_code) {
            'VENDOR_ACTIVE' => '<span class="badge bg-success">Active</span>',
            'VENDOR_INACTIVE' => '<span class="badge bg-secondary">Inactive</span>',
            'VENDOR_PENDING' => '<span class="badge bg-warning">Pending</span>',
            'VENDOR_SUSPENDED' => '<span class="badge bg-danger">Suspended</span>',
            default => '<span class="badge bg-light">Unknown</span>',
        };
    }

    /**
     * Get rating stars HTML
     */
    public function getRatingStars(): string
    {
        if (!$this->rating) {
            return '<span class="text-muted">No Rating</span>';
        }

        $fullStars = floor($this->rating);
        $halfStar = ($this->rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        $html = '';

        // Full stars
        for ($i = 0; $i < $fullStars; $i++) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        }

        // Half star
        if ($halfStar) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        }

        // Empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }

        $html .= ' <span class="text-muted">(' . number_format($this->rating, 1) . ')</span>';

        return $html;
    }

    /**
     * Get formatted total purchases
     */
    public function getFormattedTotalPurchases(): string
    {
        return $this->currency . ' ' . number_format($this->total_purchases, 2);
    }

    /**
     * Get social media links
     */
    public function getSocialMedia(): array
    {
        return $this->social_media ?? [];
    }

    /**
     * Get social media link
     */
    public function getSocialMediaLink(string $platform): ?string
    {
        return $this->social_media[$platform] ?? null;
    }

    /**
     * Update products count
     */
    public function updateProductsCount(): void
    {
        $this->products_count = $this->products()->count();
        $this->save();
    }

    /**
     * Update rating average
     */
    public function updateRating(): void
    {
        $average = $this->reviews()->avg('rating');
        $count = $this->reviews()->count();

        $this->update([
            'rating' => $average ?? 0,
            'reviews_count' => $count,
        ]);
    }

    /**
     * Activate the vendor
     */
    public function activate(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_ACTIVE']);
    }

    /**
     * Deactivate the vendor
     */
    public function deactivate(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_INACTIVE']);
    }

    /**
     * Suspend the vendor
     */
    public function suspend(): bool
    {
        return $this->update(['status_key_code' => 'VENDOR_SUSPENDED']);
    }

    /**
     * Verify the vendor
     */
    public function verify(): bool
    {
        return $this->update(['is_verified' => true]);
    }

    /**
     * Unverify the vendor
     */
    public function unverify(): bool
    {
        return $this->update(['is_verified' => false]);
    }

    /**
     * Check if vendor has reached credit limit
     */
    public function hasReachedCreditLimit(): bool
    {
        if (!$this->credit_limit) {
            return false;
        }

        $outstandingBalance = $this->purchaseOrders()
            ->where('payment_status', 'pending')
            ->sum('total_amount');

        return $outstandingBalance >= $this->credit_limit;
    }

    /**
     * Get available credit
     */
    public function getAvailableCredit(): float
    {
        if (!$this->credit_limit) {
            return 0;
        }

        $outstandingBalance = $this->purchaseOrders()
            ->where('payment_status', 'pending')
            ->sum('total_amount');

        return max(0, $this->credit_limit - $outstandingBalance);
    }

    /**
     * Get contact person name
     */
    public function getContactPerson(): string
    {
        return $this->name;
    }

    /**
     * Get primary contact method
     */
    public function getPrimaryContact(): string
    {
        return $this->email ?: $this->phone ?: $this->mobile ?: 'N/A';
    }

    /**
     * Check if has complete address
     */
    public function hasCompleteAddress(): bool
    {
        return !empty($this->address) &&
               !empty($this->city) &&
               !empty($this->country);
    }

    /**
     * Check if has bank details
     */
    public function hasBankDetails(): bool
    {
        return !empty($this->bank_name) &&
               !empty($this->bank_account_number);
    }

    /**
     * Get vendor URL
     */
    public function getUrl(): string
    {
        return route('vendors.show', $this->slug);
    }

    /**
     * Increment products count
     */
    public function incrementProductsCount(int $count = 1): void
    {
        $this->increment('products_count', $count);
    }

    /**
     * Decrement products count
     */
    public function decrementProductsCount(int $count = 1): void
    {
        $this->decrement('products_count', max(0, $count));
    }

    /**
     * Increment orders count
     */
    public function incrementOrdersCount(int $count = 1): void
    {
        $this->increment('orders_count', $count);
    }

    /**
     * Add to total purchases
     */
    public function addToPurchases(float $amount): void
    {
        $this->increment('total_purchases', $amount);
    }
}
