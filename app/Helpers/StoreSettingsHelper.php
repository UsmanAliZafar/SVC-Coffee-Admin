<?php

/**
 * ==========================================
 * STORE SETTINGS GLOBAL HELPER FUNCTIONS
 * ==========================================
 *
 * These functions provide easy access to store settings
 * throughout your entire application without repeatedly
 * calling StoreSetting::getSettings()
 */

if (!function_exists('store_settings')) {
    /**
     * Get the store settings instance or a specific setting value
     *
     * Usage:
     * - store_settings() // Returns the entire StoreSetting model instance
     * - store_settings('store_name') // Returns specific setting value
     * - store_settings('store_name', 'Default Store') // Returns value or default
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function store_settings(?string $key = null, mixed $default = null): mixed
    {
        $settings = app(\App\Models\StoreSetting::class)::getSettings();

        if (is_null($key)) {
            return $settings;
        }

        return $settings->$key ?? $default;
    }
}

if (!function_exists('settings')) {
    /**
     * Alias for store_settings() - shorter version
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        return store_settings($key, $default);
    }
}

if (!function_exists('store_name')) {
    /**
     * Get the store name
     *
     * @return string|null
     */
    function store_name(): ?string
    {
        return store_settings('store_name');
    }
}

if (!function_exists('store_email')) {
    /**
     * Get the store email
     *
     * @return string|null
     */
    function store_email(): ?string
    {
        return store_settings('store_email');
    }
}

if (!function_exists('store_phone')) {
    /**
     * Get the store phone number
     *
     * @return string|null
     */
    function store_phone(): ?string
    {
        return store_settings('store_phone');
    }
}

if (!function_exists('store_logo')) {
    /**
     * Get the store logo URL
     *
     * @return string
     */
    function store_logo(): string
    {
        return store_settings()->logo_url ?? asset('images/default-logo.png');
    }
}

if (!function_exists('store_favicon')) {
    /**
     * Get the store favicon URL
     *
     * @return string
     */
    function store_favicon(): string
    {
        return store_settings()->favicon_url ?? asset('images/default-favicon.png');
    }
}

if (!function_exists('store_banner')) {
    /**
     * Get the store banner URL
     *
     * @return string
     */
    function store_banner(): string
    {
        return store_settings()->banner_url ?? asset('images/default-banner.jpg');
    }
}

if (!function_exists('store_currency')) {
    /**
     * Get the store currency code
     *
     * @return string
     */
    function store_currency(): string
    {
        return store_settings('currency_code', 'USD');
    }
}

if (!function_exists('store_currency_symbol')) {
    /**
     * Get the store currency symbol
     *
     * @return string
     */
    function store_currency_symbol(): string
    {
        return store_settings('currency_symbol', '$');
    }
}

if (!function_exists('store_timezone')) {
    /**
     * Get the store timezone
     *
     * @return string
     */
    function store_timezone(): string
    {
        return store_settings('timezone', 'UTC');
    }
}

if (!function_exists('store_date_format')) {
    /**
     * Get the store date format
     *
     * @return string
     */
    function store_date_format(): string
    {
        return store_settings('date_format', 'Y-m-d');
    }
}

if (!function_exists('store_time_format')) {
    /**
     * Get the store time format
     *
     * @return string
     */
    function store_time_format(): string
    {
        return store_settings('time_format', 'H:i');
    }
}

// Currency formatting helper

if (!function_exists('number_format_store_price')) {
    /**
     * Format a price using store settings
     *
     * @param float|null $amount
     * @return string
     */
    function number_format_store_price($amount): string
    {
        // ✅ Handle null or invalid values
        if ($amount === null || $amount === '' || !is_numeric($amount)) {
            $amount = 0;
        }

        $settings = store_settings();

        return $settings->numberformatCurrency((float) $amount);
    }
}


if (!function_exists('format_store_price')) {
    /**
     * Format a price using store settings
     *
     * @param float $amount
     * @return string
     */
    function format_store_price(float $amount): string
    {
        $settings = store_settings();

        return $settings->formatCurrency($amount);
    }
}

if (!function_exists('price')) {
    /**
     * Alias for format_store_price() - shorter version
     *
     * @param float $amount
     * @return string
     */
    function price(float $amount): string
    {
        return format_store_price($amount);
    }
}

if (!function_exists('format_store_date')) {
    /**
     * Format a date using store settings
     *
     * @param \Carbon\Carbon|string $date
     * @return string
     */
    function format_store_date(\Carbon\Carbon|string $date): string
    {
        $settings = store_settings();

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $settings->formatDate($date);
    }
}

if (!function_exists('format_store_time')) {
    /**
     * Format a time using store settings
     *
     * @param \Carbon\Carbon|string $time
     * @return string
     */
    function format_store_time(\Carbon\Carbon|string $time): string
    {
        $settings = store_settings();

        if (is_string($time)) {
            $time = \Carbon\Carbon::parse($time);
        }

        return $settings->formatTime($time);
    }
}

if (!function_exists('format_store_datetime')) {
    /**
     * Format a datetime using store settings
     *
     * @param \Carbon\Carbon|string $datetime
     * @return string
     */
    function format_store_datetime(\Carbon\Carbon|string $datetime): string
    {
        $settings = store_settings();

        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return $settings->formatDateTime($datetime);
    }
}

if (!function_exists('store_address')) {
    /**
     * Get the full store address
     *
     * @return string|null
     */
    function store_address(): ?string
    {
        return store_settings()->full_address;
    }
}

if (!function_exists('store_country')) {
    /**
     * Get the store country
     *
     * @return string|null
     */
    function store_country(): ?string
    {
        return store_settings('store_country');
    }
}

if (!function_exists('store_city')) {
    /**
     * Get the store city
     *
     * @return string|null
     */
    function store_city(): ?string
    {
        return store_settings('store_city');
    }
}

if (!function_exists('is_maintenance_mode')) {
    /**
     * Check if the store is in maintenance mode
     *
     * @return bool
     */
    function is_maintenance_mode(): bool
    {
        return store_settings('maintenance_mode', false);
    }
}

if (!function_exists('maintenance_message')) {
    /**
     * Get the maintenance mode message
     *
     * @return string|null
     */
    function maintenance_message(): ?string
    {
        return store_settings('maintenance_message');
    }
}

if (!function_exists('store_tax_enabled')) {
    /**
     * Check if tax is enabled
     *
     * @return bool
     */
    function store_tax_enabled(): bool
    {
        return store_settings('tax_enabled', false);
    }
}

if (!function_exists('store_tax_rate')) {
    /**
     * Get the store tax rate
     *
     * @return float
     */
    function store_tax_rate(): float
    {
        return (float) store_settings('tax_rate', 0);
    }
}

if (!function_exists('calculate_tax')) {
    /**
     * Calculate tax amount for a given price
     *
     * @param float $amount
     * @return float
     */
    function calculate_tax(float $amount): float
    {
        $settings = store_settings();
        return $settings->calculateTax($amount);
    }
}

if (!function_exists('store_shipping_enabled')) {
    /**
     * Check if shipping is enabled
     *
     * @return bool
     */
    function store_shipping_enabled(): bool
    {
        return store_settings('shipping_enabled', false);
    }
}

if (!function_exists('free_shipping_threshold')) {
    /**
     * Get the free shipping threshold amount
     *
     * @return float
     */
    function free_shipping_threshold(): float
    {
        return (float) store_settings('free_shipping_threshold', 0);
    }
}

if (!function_exists('is_free_shipping')) {
    /**
     * Check if order qualifies for free shipping
     *
     * @param float $orderTotal
     * @return bool
     */
    function is_free_shipping(float $orderTotal): bool
    {
        $settings = store_settings();
        return $settings->isFreeShipping($orderTotal);
    }
}

if (!function_exists('default_shipping_cost')) {
    /**
     * Get the default shipping cost
     *
     * @return float
     */
    function default_shipping_cost(): float
    {
        return (float) store_settings('default_shipping_cost', 0);
    }
}

if (!function_exists('track_inventory')) {
    /**
     * Check if inventory tracking is enabled
     *
     * @return bool
     */
    function track_inventory(): bool
    {
        return store_settings('track_inventory', true);
    }
}

if (!function_exists('allow_backorders')) {
    /**
     * Check if backorders are allowed
     *
     * @return bool
     */
    function allow_backorders(): bool
    {
        return store_settings('allow_backorders', false);
    }
}

if (!function_exists('low_stock_threshold')) {
    /**
     * Get the low stock threshold
     *
     * @return int
     */
    function low_stock_threshold(): int
    {
        return (int) store_settings('low_stock_threshold', 10);
    }
}

if (!function_exists('generate_order_number')) {
    /**
     * Generate the next order number
     *
     * @return string
     */
    function generate_order_number(): string
    {
        $settings = store_settings();
        return $settings->generateOrderNumber();
    }
}

if (!function_exists('order_prefix')) {
    /**
     * Get the order number prefix
     *
     * @return string
     */
    function order_prefix(): string
    {
        return store_settings('order_prefix', 'ORD-');
    }
}

if (!function_exists('store_social_links')) {
    /**
     * Get all social media links
     *
     * @return array
     */
    function store_social_links(): array
    {
        return store_settings()->social_links ?? [];
    }
}

if (!function_exists('store_facebook_url')) {
    /**
     * Get Facebook URL
     *
     * @return string|null
     */
    function store_facebook_url(): ?string
    {
        return store_settings('facebook_url');
    }
}

if (!function_exists('store_twitter_url')) {
    /**
     * Get Twitter URL
     *
     * @return string|null
     */
    function store_twitter_url(): ?string
    {
        return store_settings('twitter_url');
    }
}

if (!function_exists('store_instagram_url')) {
    /**
     * Get Instagram URL
     *
     * @return string|null
     */
    function store_instagram_url(): ?string
    {
        return store_settings('instagram_url');
    }
}

if (!function_exists('store_meta_title')) {
    /**
     * Get store meta title for SEO
     *
     * @return string|null
     */
    function store_meta_title(): ?string
    {
        return store_settings('meta_title');
    }
}

if (!function_exists('store_meta_description')) {
    /**
     * Get store meta description for SEO
     *
     * @return string|null
     */
    function store_meta_description(): ?string
    {
        return store_settings('meta_description');
    }
}

if (!function_exists('store_meta_keywords')) {
    /**
     * Get store meta keywords for SEO
     *
     * @return string|null
     */
    function store_meta_keywords(): ?string
    {
        return store_settings('meta_keywords');
    }
}

if (!function_exists('google_analytics_id')) {
    /**
     * Get Google Analytics ID
     *
     * @return string|null
     */
    function google_analytics_id(): ?string
    {
        return store_settings('google_analytics_id');
    }
}

if (!function_exists('facebook_pixel_id')) {
    /**
     * Get Facebook Pixel ID
     *
     * @return string|null
     */
    function facebook_pixel_id(): ?string
    {
        return store_settings('facebook_pixel_id');
    }
}

if (!function_exists('is_store_open_today')) {
    /**
     * Check if store is open today
     *
     * @return bool
     */
    function is_store_open_today(): bool
    {
        $settings = store_settings();
        return $settings->isOpenToday();
    }
}

if (!function_exists('today_business_hours')) {
    /**
     * Get today's business hours
     *
     * @return array|null
     */
    function today_business_hours(): ?array
    {
        $settings = store_settings();
        return $settings->getTodayHours();
    }
}

if (!function_exists('store_tagline')) {
    /**
     * Get store tagline
     *
     * @return string|null
     */
    function store_tagline(): ?string
    {
        return store_settings('store_tagline');
    }
}

if (!function_exists('store_description')) {
    /**
     * Get store description
     *
     * @return string|null
     */
    function store_description(): ?string
    {
        return store_settings('store_description');
    }
}

if (!function_exists('email_from_name')) {
    /**
     * Get email from name
     *
     * @return string
     */
    function email_from_name(): string
    {
        return store_settings('email_from_name', config('mail.from.name'));
    }
}

if (!function_exists('email_from_address')) {
    /**
     * Get email from address
     *
     * @return string
     */
    function email_from_address(): string
    {
        return store_settings('email_from_address', config('mail.from.address'));
    }
}

if (!function_exists('order_auto_confirm')) {
    /**
     * Check if orders are auto-confirmed
     *
     * @return bool
     */
    function order_auto_confirm(): bool
    {
        return store_settings('order_auto_confirm', false);
    }
}

if (!function_exists('customer_registration_email_enabled')) {
    /**
     * Check if customer registration emails are enabled
     *
     * @return bool
     */
    function customer_registration_email_enabled(): bool
    {
        return store_settings('customer_registration_email', true);
    }
}

if (!function_exists('order_confirmation_email_enabled')) {
    /**
     * Check if order confirmation emails are enabled
     *
     * @return bool
     */
    function order_confirmation_email_enabled(): bool
    {
        return store_settings('order_confirmation_email', true);
    }
}

if (!function_exists('order_shipped_email_enabled')) {
    /**
     * Check if order shipped emails are enabled
     *
     * @return bool
     */
    function order_shipped_email_enabled(): bool
    {
        return store_settings('order_shipped_email', true);
    }
}

if (!function_exists('low_stock_notifications_enabled')) {
    /**
     * Check if low stock notifications are enabled
     *
     * @return bool
     */
    function low_stock_notifications_enabled(): bool
    {
        return store_settings('low_stock_notifications', true);
    }
}

if (!function_exists('store_config')) {
    /**
     * Get multiple store settings at once
     *
     * @param array $keys
     * @return array
     */
    function store_config(array $keys): array
    {
        $settings = store_settings();
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $settings->$key ?? null;
        }

        return $result;
    }
}

if (!function_exists('update_store_setting')) {
    /**
     * Update a specific store setting
     *
     * @param string $key
     * @param mixed $value
     * @return \App\Models\StoreSetting
     */
    function update_store_setting(string $key, mixed $value): \App\Models\StoreSetting
    {
        return \App\Models\StoreSetting::set($key, $value);
    }
}

if (!function_exists('price_with_tax')) {
    /**
     * Calculate price including tax
     *
     * @param float $amount
     * @return float
     */
    function price_with_tax(float $amount): float
    {
        $tax = calculate_tax($amount);
        return $amount + $tax;
    }
}

if (!function_exists('format_price_with_tax')) {
    /**
     * Format price with tax included
     *
     * @param float $amount
     * @return string
     */
    function format_price_with_tax(float $amount): string
    {
        $total = price_with_tax($amount);
        return format_store_price($total);
    }
}

if (!function_exists('shipping_cost')) {
    /**
     * Calculate shipping cost based on order total
     *
     * @param float $orderTotal
     * @return float
     */
    function shipping_cost(float $orderTotal): float
    {
        if (is_free_shipping($orderTotal)) {
            return 0;
        }

        return default_shipping_cost();
    }
}

if (!function_exists('format_shipping_cost')) {
    /**
     * Format shipping cost with currency
     *
     * @param float $orderTotal
     * @return string
     */
    function format_shipping_cost(float $orderTotal): string
    {
        $cost = shipping_cost($orderTotal);

        if ($cost == 0) {
            return 'FREE';
        }

        return format_store_price($cost);
    }
}

if (!function_exists('order_total')) {
    /**
     * Calculate complete order total (subtotal + tax + shipping)
     *
     * @param float $subtotal
     * @param float|null $shippingCost
     * @return float
     */
    function order_total(float $subtotal, ?float $shippingCost = null): float
    {
        $tax = calculate_tax($subtotal);
        $shipping = $shippingCost ?? shipping_cost($subtotal);

        return $subtotal + $tax + $shipping;
    }
}

if (!function_exists('format_order_total')) {
    /**
     * Format complete order total with currency
     *
     * @param float $subtotal
     * @param float|null $shippingCost
     * @return string
     */
    function format_order_total(float $subtotal, ?float $shippingCost = null): string
    {
        $total = order_total($subtotal, $shippingCost);
        return format_store_price($total);
    }
}

// Notification heple counter
if (!function_exists('unread_notifications_count')) {
    /**
     * Get the count of unread notifications for the admin
     *
     * @return int
     */
    function unread_notifications_count(): int
    {
        if (!auth('admin')->check()) {
            return 0;
        }

        $adminId = auth('admin')->id();
        return \App\Models\Notification::where('admin_user_id', $adminId)
            ->where('is_read', false)
            ->notExpired()
            ->count();
    }
}

if (!function_exists('recent_notifications')) {
    /**
     * Get recent unread notifications for dropdown
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function recent_notifications(int $limit = 5)
    {
        if (!auth('admin')->check()) {
            return collect([]);
        }

        $adminId = auth('admin')->id();
        return \App\Models\Notification::getRecentUnreadForAdmin($adminId, $limit);
    }
}
