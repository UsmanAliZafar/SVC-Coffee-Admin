<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StoreSettingsController extends Controller
{
    /**
     * Display the store settings page
     */
    public function index()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.read')) {
            abort(403, 'Unauthorized access');
        }

        $settings = StoreSetting::getSettings();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update basic store information
     */
    public function updateBasicInfo(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'store_email' => 'required|email|max:255',
            'store_phone' => 'nullable|string|max:20',
            'store_address' => 'nullable|string',
            'store_city' => 'nullable|string|max:100',
            'store_state' => 'nullable|string|max:100',
            'store_zip' => 'nullable|string|max:20',
            'store_country' => 'nullable|string|max:5',
            'store_tagline' => 'nullable|string|max:255',
            'store_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'basic');
        }

        $settings = StoreSetting::getSettings();
        $settings->update($request->only([
            'store_name',
            'store_email',
            'store_phone',
            'store_address',
            'store_city',
            'store_state',
            'store_zip',
            'store_country',
            'store_tagline',
            'store_description',
        ]));

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Basic information updated successfully.');
    }

    /**
     * Update branding (logo, favicon, banner)
     */
    public function updateBranding(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'store_logo' => 'nullable|file|mimes:jpeg,png,jpg,svg|max:5120',
            'store_favicon' => 'nullable|image|mimes:png,ico|max:512',
            'store_banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->with('section', 'branding');
        }

        $settings = StoreSetting::getSettings();

        // Handle logo upload
        if ($request->hasFile('store_logo')) {
            // Delete old logo
            if ($settings->store_logo) {
                Storage::delete($settings->store_logo);
            }
            $settings->store_logo = $request->file('store_logo')->store('branding/logos', 'public');
        }

        // Handle favicon upload
        if ($request->hasFile('store_favicon')) {
            // Delete old favicon
            if ($settings->store_favicon) {
                Storage::delete($settings->store_favicon);
            }
            $settings->store_favicon = $request->file('store_favicon')->store('branding/favicons', 'public');
        }

        // Handle banner upload
        if ($request->hasFile('store_banner')) {
            // Delete old banner
            if ($settings->store_banner) {
                Storage::delete($settings->store_banner);
            }
            $settings->store_banner = $request->file('store_banner')->store('branding/banners', 'public');
        }

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Branding updated successfully.');
    }

    /**
     * Update regional settings
     */
    public function updateRegional(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'currency_position' => 'required|in:left,right',
            'decimal_places' => 'required|integer|min:0|max:4',
            'thousand_separator' => 'required|string|max:1',
            'decimal_separator' => 'required|string|max:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'regional');
        }

        $settings = StoreSetting::getSettings();
        $settings->update($request->only([
            'timezone',
            'date_format',
            'time_format',
            'currency_code',
            'currency_symbol',
            'currency_position',
            'decimal_places',
            'thousand_separator',
            'decimal_separator',
        ]));

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Regional settings updated successfully.');
    }

    /**
     * Update order settings
     */
    public function updateOrder(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'order_prefix' => 'required|string|max:12',
            'order_number_start' => 'required|integer|min:1',
            'order_number_length' => 'required|integer|min:4|max:10',
            'order_threshold' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed while submitting order section.', [
                'errors' => $validator->errors()->toArray(),
                'input' => request()->all(),
                'user_id' => auth()->id() ?? 'guest',
                'section' => 'order'
            ]);
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'order');
        }

        $settings = StoreSetting::getSettings();
        $settings->update([
            'order_prefix' => $request->order_prefix,
            'order_number_start' => $request->order_number_start,
            'order_number_length' => $request->order_number_length,
            'order_threshold' => $request->order_threshold,
        ]);

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Order settings updated successfully.');
    }

    /**
     * Update tax settings
     */
    public function updateTax(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'tax_enabled' => 'boolean',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_name' => 'required|string|max:50',
            'tax_included_in_price' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'tax');
        }

        $settings = StoreSetting::getSettings();
        $settings->update([
            'tax_enabled' => $request->has('tax_enabled'),
            'tax_rate' => $request->tax_rate,
            'tax_name' => $request->tax_name,
            'tax_included_in_price' => $request->has('tax_included_in_price'),
        ]);

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Tax settings updated successfully.');
    }

    /**
     * Update shipping settings
     */
    public function updateShipping(Request $request)
    {
        try {
            // Check permission
            if (!auth('admin')->user()->hasPermission('settings.update')) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }

            $validator = Validator::make($request->all(), [
                'shipping_enabled' => 'sometimes|accepted',  // Changed from 'boolean'
                'free_shipping_threshold' => 'nullable|numeric|min:0',
                'default_shipping_cost' => 'required|numeric|min:0',

                // Enhanced shipping validation
                'shipping_calculation_type' => 'required|in:flat_rate,per_kg,per_liter,per_item,tiered',
                'shipping_rate_per_kg' => 'nullable|numeric|min:0',
                'shipping_rate_per_liter' => 'nullable|numeric|min:0',
                'shipping_rate_per_item' => 'nullable|numeric|min:0',
                'enable_nationwide_flat_rate' => 'sometimes|accepted',  // Changed from 'boolean'
                'nationwide_flat_rate' => 'nullable|numeric|min:0',
                'enable_regional_rates' => 'sometimes|accepted',  // Changed from 'boolean'
                'minimum_order_for_shipping' => 'nullable|numeric|min:0',
                'max_weight_standard_shipping' => 'nullable|numeric|min:0',
                'max_volume_standard_shipping' => 'nullable|numeric|min:0',
                'handling_fee' => 'nullable|numeric|min:0',
                'estimated_delivery_days_min' => 'nullable|integer|min:1',
                'estimated_delivery_days_max' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('Shipping settings validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'input' => $request->except(['_token', '_method']),
                    'user_id' => auth('admin')->id(),
                ]);

                return redirect()->back()
                            ->withErrors($validator)
                            ->withInput()
                            ->with('section', 'shipping')
                            ->with('error', 'Validation failed. Please check the form.');
            }

            $settings = StoreSetting::getSettings();

            // Parse tiered rates if provided
            $tieredRates = null;
            if ($request->filled('tiered_shipping_rates')) {
                try {
                    $tieredRates = json_decode($request->tiered_shipping_rates, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Invalid JSON for tiered rates: ' . json_last_error_msg());
                    }
                    // Empty array should be null
                    if (empty($tieredRates)) {
                        $tieredRates = null;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to parse tiered shipping rates', [
                        'error' => $e->getMessage(),
                        'raw_data' => $request->tiered_shipping_rates
                    ]);
                    return redirect()->back()
                                ->withInput()
                                ->with('section', 'shipping')
                                ->with('error', 'Invalid tiered rates format.');
                }
            }

            Log::info('Attempting to update shipping settings', [
                'user_id' => auth('admin')->id(),
                'calculation_type' => $request->shipping_calculation_type
            ]);

            $updateData = [
                'shipping_enabled' => $request->has('shipping_enabled'),
                'free_shipping_threshold' => $request->free_shipping_threshold,
                'default_shipping_cost' => $request->default_shipping_cost,

                // Enhanced fields
                'shipping_calculation_type' => $request->shipping_calculation_type,
                'shipping_rate_per_kg' => $request->shipping_rate_per_kg,
                'shipping_rate_per_liter' => $request->shipping_rate_per_liter,
                'shipping_rate_per_item' => $request->shipping_rate_per_item,
                'enable_nationwide_flat_rate' => $request->has('enable_nationwide_flat_rate'),
                'nationwide_flat_rate' => $request->nationwide_flat_rate,
                'enable_regional_rates' => $request->has('enable_regional_rates'),
                'minimum_order_for_shipping' => $request->minimum_order_for_shipping,
                'max_weight_standard_shipping' => $request->max_weight_standard_shipping,
                'max_volume_standard_shipping' => $request->max_volume_standard_shipping,
                'handling_fee' => $request->handling_fee ?? 0,
                'tiered_shipping_rates' => $tieredRates,
                'estimated_delivery_days_min' => $request->estimated_delivery_days_min,
                'estimated_delivery_days_max' => $request->estimated_delivery_days_max,
                'updated_by' => auth('admin')->id(),
            ];

            Log::info('Shipping settings data to be saved', [
                'data' => $updateData
            ]);

            $result = $settings->update($updateData);

            if (!$result) {
                Log::error('Failed to update shipping settings', [
                    'settings_id' => $settings->id,
                    'user_id' => auth('admin')->id()
                ]);
                return redirect()->back()
                            ->withInput()
                            ->with('section', 'shipping')
                            ->with('error', 'Failed to save shipping settings. Please try again.');
            }

            Log::info('Shipping settings updated successfully', [
                'settings_id' => $settings->id,
                'user_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                        ->with('section', 'shipping')
                        ->with('success', 'Shipping settings updated successfully.');

        } catch (\Exception $e) {
            Log::error('Exception while updating shipping settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth('admin')->id(),
                'request_data' => $request->except(['_token', '_method'])
            ]);

            return redirect()->back()
                        ->withInput()
                        ->with('section', 'shipping')
                        ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Update inventory settings
     */
    public function updateInventory(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'track_inventory' => 'boolean',
            'allow_backorders' => 'boolean',
            'low_stock_threshold' => 'required|integer|min:0',
            'low_stock_notifications' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'inventory');
        }

        $settings = StoreSetting::getSettings();
        $settings->update([
            'track_inventory' => $request->has('track_inventory'),
            'allow_backorders' => $request->has('allow_backorders'),
            'low_stock_threshold' => $request->low_stock_threshold,
            'low_stock_notifications' => $request->has('low_stock_notifications'),
        ]);

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Inventory settings updated successfully.');
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'email_from_name' => 'required|string|max:255',
            'email_from_address' => 'required|email|max:255',
            'customer_registration_email' => 'boolean',
            'order_confirmation_email' => 'boolean',
            'order_shipped_email' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'email');
        }

        $settings = StoreSetting::getSettings();
        $settings->update([
            'email_from_name' => $request->email_from_name,
            'email_from_address' => $request->email_from_address,
            'customer_registration_email' => $request->has('customer_registration_email'),
            'order_confirmation_email' => $request->has('order_confirmation_email'),
            'order_shipped_email' => $request->has('order_shipped_email'),
        ]);

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Update social media links
     */
    public function updateSocial(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'social');
        }

        $settings = StoreSetting::getSettings();
        $settings->update($request->only([
            'facebook_url',
            'twitter_url',
            'instagram_url',
            'linkedin_url',
            'youtube_url',
        ]));

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Social media links updated successfully.');
    }

    /**
     * Update SEO settings
     */
    public function updateSeo(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
            'google_analytics_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'seo');
        }

        $settings = StoreSetting::getSettings();
        $settings->update($request->only([
            'meta_title',
            'meta_description',
            'meta_keywords',
            'google_analytics_id',
            'facebook_pixel_id',
        ]));

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'SEO settings updated successfully.');
    }

    /**
     * Update maintenance mode
     */
    public function updateMaintenance(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'maintenance');
        }

        $settings = StoreSetting::getSettings();
        $settings->update([
            'maintenance_mode' => $request->has('maintenance_mode'),
            'maintenance_message' => $request->maintenance_message,
        ]);

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Maintenance mode updated successfully.');
    }

    /**
     * Update checkout settings
     */
    public function updateCheckout(Request $request)
    {
        try {
            // Check permission
            if (!auth('admin')->user()->hasPermission('settings.update')) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }

            $validator = Validator::make($request->all(), [
                // Payment Methods
                'enable_cod' => 'sometimes|accepted',
                'enable_online_payment' => 'sometimes|accepted',
                'enable_bank_transfer' => 'sometimes|accepted',

                // Instructions
                'cod_instructions' => 'nullable|string|max:2000',
                'online_payment_instructions' => 'nullable|string|max:2000',
                'bank_transfer_instructions' => 'nullable|string|max:2000',

                // Payment Gateway Settings
                'payment_gateway' => 'nullable|string|in:stripe,paypal,razorpay,jazzcash,easypaisa',
                'payment_gateway_mode' => 'nullable|string|in:sandbox,live',
                'payment_gateway_public_key' => 'nullable|string|max:500',
                'payment_gateway_secret_key' => 'nullable|string|max:500',

                // Bank Details
                'bank_name' => 'nullable|string|max:255',
                'bank_account_name' => 'nullable|string|max:255',
                'bank_account_number' => 'nullable|string|max:100',
                'bank_iban' => 'nullable|string|max:100',
                'bank_swift_code' => 'nullable|string|max:50',
                'bank_branch' => 'nullable|string|max:255',

                // Checkout Options
                'require_phone_checkout' => 'sometimes|accepted',
                'require_address_checkout' => 'sometimes|accepted',
                'enable_guest_checkout' => 'sometimes|accepted',
                'terms_conditions_required' => 'sometimes|accepted',
                'checkout_terms_text' => 'nullable|string|max:1000',

                // Order Confirmation
                'show_bank_details_on_confirmation' => 'sometimes|accepted',
                'order_confirmation_message' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                Log::warning('Checkout settings validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'input' => $request->except(['_token', '_method', 'payment_gateway_secret_key']),
                    'user_id' => auth('admin')->id(),
                ]);

                return redirect()->back()
                            ->withErrors($validator)
                            ->withInput()
                            ->with('section', 'checkout')
                            ->with('error', 'Validation failed. Please check the form.');
            }

            // Check that at least one payment method is enabled
            $hasPaymentMethod = $request->has('enable_cod') ||
                            $request->has('enable_online_payment') ||
                            $request->has('enable_bank_transfer');

            if (!$hasPaymentMethod) {
                return redirect()->back()
                            ->withInput()
                            ->with('section', 'checkout')
                            ->with('error', 'Please enable at least one payment method.');
            }

            Log::info('Attempting to update checkout settings', [
                'user_id' => auth('admin')->id(),
            ]);

            $settings = StoreSetting::getSettings();

            $updateData = [
                // Payment Methods
                'enable_cod' => $request->has('enable_cod'),
                'enable_online_payment' => $request->has('enable_online_payment'),
                'enable_bank_transfer' => $request->has('enable_bank_transfer'),

                // Instructions
                'cod_instructions' => $request->cod_instructions,
                'online_payment_instructions' => $request->online_payment_instructions,
                'bank_transfer_instructions' => $request->bank_transfer_instructions,

                // Payment Gateway
                'payment_gateway' => $request->payment_gateway,
                'payment_gateway_mode' => $request->payment_gateway_mode ?? 'sandbox',
                'payment_gateway_public_key' => $request->payment_gateway_public_key,
                'payment_gateway_secret_key' => $request->payment_gateway_secret_key,

                // Bank Details
                'bank_name' => $request->bank_name,
                'bank_account_name' => $request->bank_account_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_iban' => $request->bank_iban,
                'bank_swift_code' => $request->bank_swift_code,
                'bank_branch' => $request->bank_branch,

                // Checkout Options
                'require_phone_checkout' => $request->has('require_phone_checkout'),
                'require_address_checkout' => $request->has('require_address_checkout'),
                'enable_guest_checkout' => $request->has('enable_guest_checkout'),
                'terms_conditions_required' => $request->has('terms_conditions_required'),
                'checkout_terms_text' => $request->checkout_terms_text,

                // Order Confirmation
                'show_bank_details_on_confirmation' => $request->has('show_bank_details_on_confirmation'),
                'order_confirmation_message' => $request->order_confirmation_message,

                'updated_by' => auth('admin')->id(),
            ];

            Log::info('Checkout settings data to be saved', [
                'data' => array_diff_key($updateData, ['payment_gateway_secret_key' => ''])
            ]);

            $result = $settings->update($updateData);

            if (!$result) {
                Log::error('Failed to update checkout settings', [
                    'settings_id' => $settings->id,
                    'user_id' => auth('admin')->id()
                ]);
                return redirect()->back()
                            ->withInput()
                            ->with('section', 'checkout')
                            ->with('error', 'Failed to save checkout settings. Please try again.');
            }

            Log::info('Checkout settings updated successfully', [
                'settings_id' => $settings->id,
                'user_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                        ->with('section', 'checkout')
                        ->with('success', 'Checkout settings updated successfully.');

        } catch (\Exception $e) {
            Log::error('Exception while updating checkout settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth('admin')->id(),
                'request_data' => $request->except(['_token', '_method', 'payment_gateway_secret_key'])
            ]);

            return redirect()->back()
                        ->withInput()
                        ->with('section', 'checkout')
                        ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
