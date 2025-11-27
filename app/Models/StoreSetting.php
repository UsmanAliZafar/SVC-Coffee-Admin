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
        'order_threshold',

        // Tax Settings
        'tax_enabled',
        'tax_rate',
        'tax_name',
        'tax_included_in_price',

        // Shipping Settings
        'shipping_enabled',
        'free_shipping_threshold',
        'default_shipping_cost',
        'shipping_calculation_type',
        'shipping_rate_per_kg',
        'shipping_rate_per_liter',
        'shipping_rate_per_item',
        'enable_nationwide_flat_rate',
        'nationwide_flat_rate',
        'enable_regional_rates',
        'minimum_order_for_shipping',
        'max_weight_standard_shipping',
        'max_volume_standard_shipping',
        'handling_fee',
        'tiered_shipping_rates',
        'estimated_delivery_days_min',
        'estimated_delivery_days_max',

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

        // Checkout & Payment Settings
        'enable_cod',
        'enable_online_payment',
        'enable_bank_transfer',
        'cod_instructions',
        'online_payment_instructions',
        'bank_transfer_instructions',
        'payment_gateway',
        'payment_gateway_mode',
        'payment_gateway_public_key',
        'payment_gateway_secret_key',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_iban',
        'bank_swift_code',
        'bank_branch',
        'require_phone_checkout',
        'require_address_checkout',
        'enable_guest_checkout',
        'terms_conditions_required',
        'checkout_terms_text',
        'show_bank_details_on_confirmation',
        'order_confirmation_message',

        // System Settings
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'business_hours' => 'array',
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
        'order_threshold' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'default_shipping_cost' => 'decimal:2',
        'enable_nationwide_flat_rate' => 'boolean',
        'enable_regional_rates' => 'boolean',
        'shipping_rate_per_kg' => 'decimal:2',
        'shipping_rate_per_liter' => 'decimal:2',
        'shipping_rate_per_item' => 'decimal:2',
        'nationwide_flat_rate' => 'decimal:2',
        'minimum_order_for_shipping' => 'decimal:2',
        'max_weight_standard_shipping' => 'decimal:2',
        'max_volume_standard_shipping' => 'decimal:2',
        'handling_fee' => 'decimal:2',
        'tiered_shipping_rates' => 'array',
        'estimated_delivery_days_min' => 'integer',
        'estimated_delivery_days_max' => 'integer',
        'enable_cod' => 'boolean',
        'enable_online_payment' => 'boolean',
        'enable_bank_transfer' => 'boolean',
        'require_phone_checkout' => 'boolean',
        'require_address_checkout' => 'boolean',
        'enable_guest_checkout' => 'boolean',
        'terms_conditions_required' => 'boolean',
        'show_bank_details_on_confirmation' => 'boolean',
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
     * Format amount without currency symbol
     */
    public function formatAmount($amount)
    {
        return number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );
    }

    /**
     * Format currency amount
     */
    public function formatCurrency($amount)
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        return $this->currency_position === 'left'
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
     * Check if order meets the threshold
     */
    public function meetsOrderThreshold($orderTotal)
    {
        if (!$this->order_threshold) {
            return true; // No threshold set, all orders allowed
        }

        return $orderTotal >= $this->order_threshold;
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

    /**
     * Calculate shipping cost based on order details
     */
    public function calculateShipping($orderTotal, $totalWeight = 0, $totalVolume = 0, $itemCount = 0)
    {
        if (!$this->shipping_enabled) {
            return 0;
        }

        // Check for free shipping threshold
        if ($this->free_shipping_threshold && $orderTotal >= $this->free_shipping_threshold) {
            return 0;
        }

        // Check minimum order for shipping
        if ($this->minimum_order_for_shipping && $orderTotal < $this->minimum_order_for_shipping) {
            return null; // Indicate order doesn't meet minimum
        }

        $shippingCost = 0;

        switch ($this->shipping_calculation_type) {
            case 'flat_rate':
                $shippingCost = $this->enable_nationwide_flat_rate
                    ? $this->nationwide_flat_rate
                    : $this->default_shipping_cost;
                break;

            case 'per_kg':
                if ($totalWeight > 0 && $this->shipping_rate_per_kg) {
                    $shippingCost = $totalWeight * $this->shipping_rate_per_kg;
                }
                break;

            case 'per_liter':
                if ($totalVolume > 0 && $this->shipping_rate_per_liter) {
                    $shippingCost = $totalVolume * $this->shipping_rate_per_liter;
                }
                break;

            case 'per_item':
                if ($itemCount > 0 && $this->shipping_rate_per_item) {
                    $shippingCost = $itemCount * $this->shipping_rate_per_item;
                }
                break;

            case 'tiered':
                $shippingCost = $this->calculateTieredShipping($orderTotal, $totalWeight);
                break;

            default:
                $shippingCost = $this->default_shipping_cost;
        }

        // Add handling fee
        $shippingCost += $this->handling_fee;

        return max(0, $shippingCost); // Ensure non-negative
    }

    /**
     * Calculate tiered shipping based on order total or weight
     */
    private function calculateTieredShipping($orderTotal, $totalWeight)
    {
        if (!$this->tiered_shipping_rates || empty($this->tiered_shipping_rates)) {
            return $this->default_shipping_cost;
        }

        // Sort tiers by threshold
        $tiers = collect($this->tiered_shipping_rates)->sortBy('threshold');

        $applicableRate = $this->default_shipping_cost;

        foreach ($tiers as $tier) {
            $threshold = $tier['threshold'] ?? 0;
            $rate = $tier['rate'] ?? 0;
            $type = $tier['type'] ?? 'order_total'; // 'order_total' or 'weight'

            if ($type === 'order_total' && $orderTotal >= $threshold) {
                $applicableRate = $rate;
            } elseif ($type === 'weight' && $totalWeight >= $threshold) {
                $applicableRate = $rate;
            }
        }

        return $applicableRate;
    }

    /**
     * Get estimated delivery range
     */
    public function getEstimatedDelivery()
    {
        if (!$this->estimated_delivery_days_min && !$this->estimated_delivery_days_max) {
            return null;
        }

        $min = $this->estimated_delivery_days_min ?? 1;
        $max = $this->estimated_delivery_days_max ?? $min;

        if ($min === $max) {
            return "{$min} business day" . ($min > 1 ? 's' : '');
        }

        return "{$min}-{$max} business days";
    }

    /**
     * Check if order exceeds shipping limits
     */
    public function exceedsShippingLimits($totalWeight, $totalVolume)
    {
        if ($this->max_weight_standard_shipping && $totalWeight > $this->max_weight_standard_shipping) {
            return ['exceeds' => true, 'type' => 'weight', 'limit' => $this->max_weight_standard_shipping];
        }

        if ($this->max_volume_standard_shipping && $totalVolume > $this->max_volume_standard_shipping) {
            return ['exceeds' => true, 'type' => 'volume', 'limit' => $this->max_volume_standard_shipping];
        }

        return ['exceeds' => false];
    }

    /**
        * Get available payment methods
    */
    public function getAvailablePaymentMethodsAttribute()
    {
        $methods = [];

        if ($this->enable_cod) {
            $methods[] = [
                'key' => 'cod',
                'name' => 'Cash on Delivery',
                'icon' => 'bi-cash-coin',
                'instructions' => $this->cod_instructions,
            ];
        }

        if ($this->enable_online_payment) {
            $methods[] = [
                'key' => 'online',
                'name' => 'Online Payment',
                'icon' => 'bi-credit-card',
                'instructions' => $this->online_payment_instructions,
            ];
        }

        if ($this->enable_bank_transfer) {
            $methods[] = [
                'key' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'icon' => 'bi-bank',
                'instructions' => $this->bank_transfer_instructions,
            ];
        }

        return $methods;
    }

    /**
     * Check if at least one payment method is enabled
     */
    public function hasPaymentMethodEnabled()
    {
        return $this->enable_cod || $this->enable_online_payment || $this->enable_bank_transfer;
    }

    /**
     * Get bank details as array
     */
    public function getBankDetailsAttribute()
    {
        return [
            'bank_name' => $this->bank_name,
            'account_name' => $this->bank_account_name,
            'account_number' => $this->bank_account_number,
            'iban' => $this->bank_iban,
            'swift_code' => $this->bank_swift_code,
            'branch' => $this->bank_branch,
        ];
    }

    /**
     * Get formatted bank details for display
     */
    public function getFormattedBankDetails()
    {
        $details = [];

        if ($this->bank_name) {
            $details[] = "Bank Name: {$this->bank_name}";
        }

        if ($this->bank_account_name) {
            $details[] = "Account Name: {$this->bank_account_name}";
        }

        if ($this->bank_account_number) {
            $details[] = "Account Number: {$this->bank_account_number}";
        }

        if ($this->bank_iban) {
            $details[] = "IBAN: {$this->bank_iban}";
        }

        if ($this->bank_swift_code) {
            $details[] = "SWIFT Code: {$this->bank_swift_code}";
        }

        if ($this->bank_branch) {
            $details[] = "Branch: {$this->bank_branch}";
        }

        return implode("\n", $details);
    }

    /**
     * Check if order exceeds the threshold (for admin notification)
     */
    public function exceedsOrderThreshold($orderTotal)
    {
        if (!$this->order_threshold) {
            return false; // No threshold set, no notification needed
        }

        return $orderTotal >= $this->order_threshold;
    }
}
