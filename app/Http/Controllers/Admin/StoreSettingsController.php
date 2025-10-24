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
            'store_country' => 'nullable|string|max:2',
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
            'store_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
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
            'order_auto_confirm' =>'sometimes|accepted',
            'order_notification_email' => 'sometimes|accepted',
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
            'order_auto_confirm' => $request->has('order_auto_confirm'),
            'order_notification_email' => $request->has('order_notification_email'),
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
        // Check permission
        if (!auth('admin')->user()->hasPermission('settings.update')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'shipping_enabled' => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'default_shipping_cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput()
                           ->with('section', 'shipping');
        }

        $settings = StoreSetting::getSettings();
        $settings->update([
            'shipping_enabled' => $request->has('shipping_enabled'),
            'free_shipping_threshold' => $request->free_shipping_threshold,
            'default_shipping_cost' => $request->default_shipping_cost,
        ]);

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()->back()->with('success', 'Shipping settings updated successfully.');
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
}
