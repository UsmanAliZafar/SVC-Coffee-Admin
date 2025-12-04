<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreSettingsApiController extends Controller
{
    /**
     * Get all store settings
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store settings not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Store settings retrieved successfully',
                'data' => [
                    'basic_info' => $this->getBasicInfo($settings),
                    'branding' => $this->getBranding($settings),
                    'regional' => $this->getRegionalSettings($settings),
                    'order_settings' => $this->getOrderSettings($settings),
                    'tax_settings' => $this->getTaxSettings($settings),
                    'shipping_settings' => $this->getShippingSettings($settings),
                    'inventory_settings' => $this->getInventorySettings($settings),
                    'email_settings' => $this->getEmailSettings($settings),
                    'checkout_settings' => $this->getCheckoutSettings($settings),
                    'social_media' => $this->getSocialMedia($settings),
                    'seo' => $this->getSeoSettings($settings),
                    'maintenance' => $this->getMaintenanceMode($settings),
                    'business_hours' => $settings->business_hours,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve store settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get basic store information
     *
     * @return JsonResponse
     */
    public function basicInfo(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Basic information retrieved successfully',
                'data' => $this->getBasicInfo($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve basic information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branding assets
     *
     * @return JsonResponse
     */
    public function branding(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Branding information retrieved successfully',
                'data' => $this->getBranding($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branding information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get regional settings
     *
     * @return JsonResponse
     */
    public function regional(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Regional settings retrieved successfully',
                'data' => $this->getRegionalSettings($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve regional settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipping settings
     *
     * @return JsonResponse
     */
    public function shipping(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Shipping settings retrieved successfully',
                'data' => $this->getShippingSettings($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipping settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get checkout and payment settings
     *
     * @return JsonResponse
     */
    public function checkout(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Checkout settings retrieved successfully',
                'data' => $this->getCheckoutSettings($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve checkout settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate shipping cost
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_total' => 'required|numeric|min:0',
                'total_weight' => 'nullable|numeric|min:0',
                'total_volume' => 'nullable|numeric|min:0',
                'item_count' => 'nullable|integer|min:0',
            ]);

            $settings = StoreSetting::getSettings();

            $shippingCost = $settings->calculateShipping(
                $validated['order_total'],
                $validated['total_weight'] ?? 0,
                $validated['total_volume'] ?? 0,
                $validated['item_count'] ?? 0
            );

            if ($shippingCost === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not meet minimum shipping requirements',
                    'data' => [
                        'minimum_order' => $settings->minimum_order_for_shipping,
                        'currency_symbol' => $settings->currency_symbol,
                    ]
                ], 400);
            }

            $isFreeShipping = $settings->isFreeShipping($validated['order_total']);

            return response()->json([
                'success' => true,
                'message' => 'Shipping cost calculated successfully',
                'data' => [
                    'shipping_cost' => $shippingCost,
                    'formatted_cost' => $settings->formatCurrency($shippingCost),
                    'is_free_shipping' => $isFreeShipping,
                    'calculation_type' => $settings->shipping_calculation_type,
                    'estimated_delivery' => $settings->getEstimatedDelivery(),
                    'currency_symbol' => $settings->currency_symbol,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate shipping cost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get social media links
     *
     * @return JsonResponse
     */
    public function socialMedia(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Social media links retrieved successfully',
                'data' => $this->getSocialMedia($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve social media links',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if store is in maintenance mode
     *
     * @return JsonResponse
     */
    public function maintenanceStatus(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance status retrieved successfully',
                'data' => $this->getMaintenanceMode($settings)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve maintenance status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get business hours
     *
     * @return JsonResponse
     */
    public function businessHours(): JsonResponse
    {
        try {
            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Business hours retrieved successfully',
                'data' => [
                    'business_hours' => $settings->business_hours,
                    'is_open_today' => $settings->isOpenToday(),
                    'today_hours' => $settings->getTodayHours(),
                    'timezone' => $settings->timezone,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve business hours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format currency amount
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function formatCurrency(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric',
            ]);

            $settings = StoreSetting::getSettings();

            return response()->json([
                'success' => true,
                'message' => 'Currency formatted successfully',
                'data' => [
                    'amount' => $validated['amount'],
                    'formatted' => $settings->formatCurrency($validated['amount']),
                    'currency_code' => $settings->currency_code,
                    'currency_symbol' => $settings->currency_symbol,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format currency',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Extract basic info from settings
     */
    private function getBasicInfo($settings): array
    {
        return [
            'store_name' => $settings->store_name,
            'store_email' => $settings->store_email,
            'store_phone' => $settings->store_phone,
            'store_address' => $settings->store_address,
            'store_city' => $settings->store_city,
            'store_state' => $settings->store_state,
            'store_zip' => $settings->store_zip,
            'store_country' => $settings->store_country,
            'store_tagline' => $settings->store_tagline,
            'store_description' => $settings->store_description,
            'full_address' => $settings->full_address,
        ];
    }

    /**
     * Extract branding from settings
     */
    private function getBranding($settings): array
    {
        return [
            'logo_url' => $settings->logo_url,
            'favicon_url' => $settings->favicon_url,
            'banner_url' => $settings->banner_url,
        ];
    }

    /**
     * Extract regional settings
     */
    private function getRegionalSettings($settings): array
    {
        return [
            'timezone' => $settings->timezone,
            'date_format' => $settings->date_format,
            'time_format' => $settings->time_format,
            'currency_code' => $settings->currency_code,
            'currency_symbol' => $settings->currency_symbol,
            'currency_position' => $settings->currency_position,
            'decimal_places' => $settings->decimal_places,
            'thousand_separator' => $settings->thousand_separator,
            'decimal_separator' => $settings->decimal_separator,
        ];
    }

    /**
     * Extract order settings
     */
    private function getOrderSettings($settings): array
    {
        return [
            'order_prefix' => $settings->order_prefix,
            'order_number_start' => $settings->order_number_start,
            'order_number_length' => $settings->order_number_length,
            'order_auto_confirm' => $settings->order_auto_confirm,
            'order_notification_email' => $settings->order_notification_email,
        ];
    }

    /**
     * Extract tax settings
     */
    private function getTaxSettings($settings): array
    {
        return [
            'tax_enabled' => $settings->tax_enabled,
            'tax_rate' => $settings->tax_rate,
            'tax_name' => $settings->tax_name,
            'tax_included_in_price' => $settings->tax_included_in_price,
        ];
    }

    /**
     * Extract shipping settings
     */
    private function getShippingSettings($settings): array
    {
        return [
            'shipping_enabled' => $settings->shipping_enabled,
            'free_shipping_threshold' => $settings->free_shipping_threshold,
            'default_shipping_cost' => $settings->default_shipping_cost,
            'shipping_calculation_type' => $settings->shipping_calculation_type,
            'shipping_rate_per_kg' => $settings->shipping_rate_per_kg,
            'shipping_rate_per_liter' => $settings->shipping_rate_per_liter,
            'shipping_rate_per_item' => $settings->shipping_rate_per_item,
            'enable_nationwide_flat_rate' => $settings->enable_nationwide_flat_rate,
            'nationwide_flat_rate' => $settings->nationwide_flat_rate,
            'enable_regional_rates' => $settings->enable_regional_rates,
            'minimum_order_for_shipping' => $settings->minimum_order_for_shipping,
            'max_weight_standard_shipping' => $settings->max_weight_standard_shipping,
            'max_volume_standard_shipping' => $settings->max_volume_standard_shipping,
            'handling_fee' => $settings->handling_fee,
            'tiered_shipping_rates' => $settings->tiered_shipping_rates,
            'estimated_delivery_days_min' => $settings->estimated_delivery_days_min,
            'estimated_delivery_days_max' => $settings->estimated_delivery_days_max,
            'estimated_delivery' => $settings->getEstimatedDelivery(),
        ];
    }

    /**
     * Extract inventory settings
     */
    private function getInventorySettings($settings): array
    {
        return [
            'track_inventory' => $settings->track_inventory,
            'allow_backorders' => $settings->allow_backorders,
            'low_stock_threshold' => $settings->low_stock_threshold,
            'low_stock_notifications' => $settings->low_stock_notifications,
        ];
    }

    /**
     * Extract email settings
     */
    private function getEmailSettings($settings): array
    {
        return [
            'email_from_name' => $settings->email_from_name,
            'email_from_address' => $settings->email_from_address,
            'customer_registration_email' => $settings->customer_registration_email,
            'order_confirmation_email' => $settings->order_confirmation_email,
            'order_shipped_email' => $settings->order_shipped_email,
        ];
    }

    /**
     * Extract checkout settings
     */
    private function getCheckoutSettings($settings): array
    {
        return [
            'payment_methods' => [
                'cod' => [
                    'enabled' => $settings->enable_cod,
                    'instructions' => $settings->cod_instructions,
                ],
                'online_payment' => [
                    'enabled' => $settings->enable_online_payment,
                    'gateway' => $settings->payment_gateway,
                    'mode' => $settings->payment_gateway_mode,
                    'instructions' => $settings->online_payment_instructions,
                ],
                'bank_transfer' => [
                    'enabled' => $settings->enable_bank_transfer,
                    'instructions' => $settings->bank_transfer_instructions,
                    'bank_details' => $settings->enable_bank_transfer ? [
                        'bank_name' => $settings->bank_name,
                        'account_name' => $settings->bank_account_name,
                        'account_number' => $settings->bank_account_number,
                        'iban' => $settings->bank_iban,
                        'swift_code' => $settings->bank_swift_code,
                        'branch' => $settings->bank_branch,
                        'formatted' => $settings->getFormattedBankDetails(),
                    ] : null,
                ],
            ],
            'available_payment_methods' => $settings->available_payment_methods,
            'checkout_options' => [
                'require_phone' => $settings->require_phone_checkout,
                'require_address' => $settings->require_address_checkout,
                'enable_guest_checkout' => $settings->enable_guest_checkout,
                'terms_required' => $settings->terms_conditions_required,
                'terms_text' => $settings->checkout_terms_text,
            ],
            'order_confirmation' => [
                'message' => $settings->order_confirmation_message,
                'show_bank_details' => $settings->show_bank_details_on_confirmation,
            ],
        ];
    }

    /**
     * Extract social media links
     */
    private function getSocialMedia($settings): array
    {
        // Define all social media field mappings
        $socialFields = [
            'facebook' => 'facebook_url',
            'twitter' => 'twitter_url',
            'instagram' => 'instagram_url',
            'linkedin' => 'linkedin_url',
            'youtube' => 'youtube_url',
            'tiktok' => 'tiktok_url',
            'pinterest' => 'pinterest_url',
            'whatsapp' => 'whatsapp_url',
            'telegram' => 'telegram_url',
            'snapchat' => 'snapchat_url',
            'github' => 'github_url',
            'discord' => 'discord_url',
        ];

        $result = [];
        foreach ($socialFields as $key => $field) {
            $result[$key] = $settings->$field ?? null;
        }

        // Add social_links if it exists
        if (isset($settings->social_links)) {
            $result['social_links'] = $settings->social_links;
        }

        return $result;
    }

    /**
     * Extract SEO settings
     */
    private function getSeoSettings($settings): array
    {
        return [
            'meta_title' => $settings->meta_title,
            'meta_description' => $settings->meta_description,
            'meta_keywords' => $settings->meta_keywords,
            'google_analytics_id' => $settings->google_analytics_id,
            'facebook_pixel_id' => $settings->facebook_pixel_id,
        ];
    }

    /**
     * Extract maintenance mode settings
     */
    private function getMaintenanceMode($settings): array
    {
        return [
            'maintenance_mode' => $settings->maintenance_mode,
            'maintenance_message' => $settings->maintenance_message,
            'is_in_maintenance' => $settings->isInMaintenanceMode(),
        ];
    }
}
