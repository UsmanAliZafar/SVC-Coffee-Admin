<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic Store Information
        'store_name',
        'store_email',
        'store_phone',
        'store_address',
        'store_city',
        'store_state',
        'store_zip',
        'store_country',

        // Branding & Media
        'store_logo',
        'store_favicon',
        'store_banner',
        'store_description',
        'store_tagline',

        // Regional Settings
        'timezone',
        'date_format',
        'time_format',
        'currency_code',
        'currency_symbol',
        'currency_position',
        'decimal_places',
        'thousand_separator',
        'decimal_separator',

        // Order Settings
        'order_prefix',
        'order_number_start',
        'order_number_length',
        'order_auto_confirm',
        'order_notification_email',

        // Tax Settings
        'tax_enabled',
        'tax_rate',
        'tax_name',
        'tax_included_in_price',

        // Shipping Settings
        'shipping_enabled',
        'free_shipping_threshold',
        'default_shipping_cost',

        // Inventory Settings
        'track_inventory',
        'allow_backorders',
        'low_stock_threshold',
        'low_stock_notifications',

        // Email Settings
        'email_from_name',
        'email_from_address',
        'customer_registration_email',
        'order_confirmation_email',
        'order_shipped_email',

        // Social Media Links
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',

        // Business Hours
        'business_hours',

        // Maintenance Mode
        'maintenance_mode',
        'maintenance_message',

        // SEO Settings
        'meta_title',
        'meta_description',
        'meta_keywords',

        // Legal & Compliance
        'terms_conditions',
        'privacy_policy',
        'return_policy',

        // Analytics
        'google_analytics_id',
        'facebook_pixel_id',

        // System Settings
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'order_auto_confirm' => 'boolean',
        'order_notification_email' => 'boolean',
        'tax_enabled' => 'boolean',
        'tax_included_in_price' => 'boolean',
        'shipping_enabled' => 'boolean',
        'track_inventory' => 'boolean',
        'allow_backorders' => 'boolean',
        'low_stock_notifications' => 'boolean',
        'customer_registration_email' => 'boolean',
        'order_confirmation_email' => 'boolean',
        'order_shipped_email' => 'boolean',
        'maintenance_mode' => 'boolean',
        'is_active' => 'boolean',
        'tax_rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'default_shipping_cost' => 'decimal:2',
    ];

    /**
     * Relationship with admin user who last updated settings
     */
    public function updatedBy()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    /**
     * Get the store settings instance (singleton pattern)
     */
    public static function getSettings()
    {
        return self::first() ?? self::create([]);
    }

    /**
     * Get a specific setting value
     */
    public static function get($key, $default = null)
    {
        $settings = self::getSettings();
        return $settings->$key ?? $default;
    }

    /**
     * Set a specific setting value
     */
    public static function set($key, $value)
    {
        $settings = self::getSettings();
        $settings->$key = $value;
        $settings->updated_by = auth('admin')->id();
        $settings->save();
        return $settings;
    }

    /**
     * Get full store logo URL
     */
    public function getLogoUrlAttribute()
    {
        return $this->store_logo
            ? Storage::url($this->store_logo)
            : asset('images/default-logo.png');
    }

    /**
     * Get full favicon URL
     */
    public function getFaviconUrlAttribute()
    {
        return $this->store_favicon
            ? Storage::url($this->store_favicon)
            : asset('images/default-favicon.png');
    }

    /**
     * Get full banner URL
     */
    public function getBannerUrlAttribute()
    {
        return $this->store_banner
            ? Storage::url($this->store_banner)
            : asset('images/default-banner.jpg');
    }

    /**
     * Format currency amount
     */
    // number format($number, 2, '.', ',')
    public function numberformatCurrency($amount)
    {
        // ✅ Handle null or invalid values
        if ($amount === null || $amount === '' || !is_numeric($amount)) {
            $amount = 0;
        }

        $formatted = number_format(
            (float) $amount,
            $this->decimal_places ?? 2,
            $this->decimal_separator ?? '.',
            $this->thousand_separator ?? ','
        );

        return $formatted;
    }

    public function formatCurrency($amount)
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        return $this->currency_symbol . $formatted
            ? $this->currency_symbol . $formatted
            : $formatted . $this->currency_symbol;
    }

    /**
     * Generate next order number
     */
    public function generateOrderNumber()
    {
        $lastOrder = \App\Models\Order::latest('id')->first();
        $nextNumber = $lastOrder
            ? (int)substr($lastOrder->order_number, strlen($this->order_prefix)) + 1
            : $this->order_number_start;

        return $this->order_prefix . str_pad(
            $nextNumber,
            $this->order_number_length,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Check if store is open today
     */
    public function isOpenToday()
    {
        if (!$this->business_hours) {
            return true;
        }

        $today = strtolower(now($this->timezone)->format('l'));
        $hours = $this->business_hours[$today] ?? null;

        return $hours['is_open'] ?? true;
    }

    /**
     * Get today's business hours
     */
    public function getTodayHours()
    {
        if (!$this->business_hours) {
            return null;
        }

        $today = strtolower(now($this->timezone)->format('l'));
        return $this->business_hours[$today] ?? null;
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax($amount)
    {
        if (!$this->tax_enabled) {
            return 0;
        }

        return ($amount * $this->tax_rate) / 100;
    }

    /**
     * Check if free shipping applies
     */
    public function isFreeShipping($orderTotal)
    {
        if (!$this->shipping_enabled || !$this->free_shipping_threshold) {
            return false;
        }

        return $orderTotal >= $this->free_shipping_threshold;
    }

    /**
     * Get formatted date
     */
    public function formatDate($date)
    {
        return $date->timezone($this->timezone)->format($this->date_format);
    }

    /**
     * Get formatted time
     */
    public function formatTime($time)
    {
        return $time->timezone($this->timezone)->format($this->time_format);
    }

    /**
     * Get formatted datetime
     */
    public function formatDateTime($datetime)
    {
        return $datetime->timezone($this->timezone)
            ->format($this->date_format . ' ' . $this->time_format);
    }

    /**
     * Get full store address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->store_address,
            $this->store_city,
            $this->store_state,
            $this->store_zip,
            $this->store_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if in maintenance mode
     */
    public function isInMaintenanceMode()
    {
        return $this->maintenance_mode;
    }

    /**
     * Get social media links
     */
    public function getSocialLinksAttribute()
    {
        return array_filter([
            'facebook' => $this->facebook_url,
            'twitter' => $this->twitter_url,
            'instagram' => $this->instagram_url,
            'linkedin' => $this->linkedin_url,
            'youtube' => $this->youtube_url,
        ]);
    }
}
