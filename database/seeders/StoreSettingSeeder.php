<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if settings already exist
        $settings = StoreSetting::first();

        $data = [
            // Basic Store Information
            'store_name' => 'My Store',
            'store_email' => 'store@example.com',
            'store_phone' => '+92-300-1234567',
            'store_address' => '123 Main Street',
            'store_city' => 'Lahore',
            'store_state' => 'Punjab',
            'store_zip' => '54000',
            'store_country' => 'PK',
            'store_tagline' => 'Your One-Stop Shop',
            'store_description' => 'Welcome to our online store where quality meets affordability.',

            // Branding & Media (will be null by default)
            'store_logo' => null,
            'store_favicon' => null,
            'store_banner' => null,

            // Regional Settings
            'timezone' => 'Asia/Karachi',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'currency_code' => 'SAR',
            'currency_symbol' => '﷼',
            'currency_position' => 'left',
            'decimal_places' => 2,
            'thousand_separator' => ',',
            'decimal_separator' => '.',

            // Order Settings
            'order_prefix' => 'ORD-',
            'order_number_start' => 1000,
            'order_number_length' => 6,

            // Tax Settings
            'tax_enabled' => false,
            'tax_rate' => 0.00,
            'tax_name' => 'VAT',
            'tax_included_in_price' => false,

            // Shipping Settings
            'shipping_enabled' => true,
            'free_shipping_threshold' => 5000.00,
            'default_shipping_cost' => 200.00,
            'shipping_calculation_type' => 'flat_rate',
            'shipping_rate_per_kg' => null,
            'shipping_rate_per_liter' => null,
            'shipping_rate_per_item' => null,
            'enable_nationwide_flat_rate' => true,
            'nationwide_flat_rate' => 200.00,
            'enable_regional_rates' => false,
            'minimum_order_for_shipping' => null,
            'max_weight_standard_shipping' => null,
            'max_volume_standard_shipping' => null,
            'handling_fee' => 0.00,
            'tiered_shipping_rates' => null,
            'estimated_delivery_days_min' => 3,
            'estimated_delivery_days_max' => 7,

            // Inventory Settings
            'track_inventory' => true,
            'allow_backorders' => false,
            'low_stock_threshold' => 10,
            'low_stock_notifications' => true,

            // Email Settings
            'email_from_name' => 'My Store',
            'email_from_address' => 'noreply@example.com',
            'customer_registration_email' => true,
            'order_confirmation_email' => true,
            'order_shipped_email' => true,

            // Social Media Links
            'facebook_url' => null,
            'twitter_url' => null,
            'instagram_url' => null,
            'linkedin_url' => null,
            'youtube_url' => null,

            // Business Hours
            'business_hours' => json_encode([
                'monday' => ['is_open' => true, 'open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['is_open' => true, 'open' => '09:00', 'close' => '18:00'],
                'wednesday' => ['is_open' => true, 'open' => '09:00', 'close' => '18:00'],
                'thursday' => ['is_open' => true, 'open' => '09:00', 'close' => '18:00'],
                'friday' => ['is_open' => true, 'open' => '09:00', 'close' => '18:00'],
                'saturday' => ['is_open' => true, 'open' => '10:00', 'close' => '16:00'],
                'sunday' => ['is_open' => false, 'open' => null, 'close' => null],
            ]),

            // Maintenance Mode
            'maintenance_mode' => false,
            'maintenance_message' => 'We are currently performing scheduled maintenance. We will be back shortly!',

            // SEO Settings
            'meta_title' => 'My Store - Quality Products at Great Prices',
            'meta_description' => 'Shop the best products at unbeatable prices. Fast delivery, excellent customer service.',
            'meta_keywords' => 'online store, shopping, quality products, best prices',

            // Legal & Compliance
            'terms_conditions' => null,
            'privacy_policy' => null,
            'return_policy' => null,

            // Analytics
            'google_analytics_id' => null,
            'facebook_pixel_id' => null,

            // Checkout & Payment Settings
            'enable_cod' => true,
            'enable_online_payment' => false,
            'enable_bank_transfer' => true,
            'cod_instructions' => 'Please keep exact change ready. Payment is accepted in PKR only.',
            'online_payment_instructions' => 'You will be redirected to our secure payment gateway to complete your payment.',
            'bank_transfer_instructions' => 'Please transfer the amount to our bank account and send the payment screenshot to our WhatsApp number.',
            'payment_gateway' => null,
            'payment_gateway_mode' => 'sandbox',
            'payment_gateway_public_key' => null,
            'payment_gateway_secret_key' => null,
            'bank_name' => 'HBL Bank',
            'bank_account_name' => 'My Store',
            'bank_account_number' => '1234567890',
            'bank_iban' => 'PK36HBLB0000001234567890',
            'bank_swift_code' => 'HBLBPKKAXXX',
            'bank_branch' => 'Main Branch, Lahore',
            'require_phone_checkout' => true,
            'require_address_checkout' => true,
            'enable_guest_checkout' => true,
            'terms_conditions_required' => true,
            'checkout_terms_text' => 'By placing this order, you agree to our terms and conditions and privacy policy.',
            'show_bank_details_on_confirmation' => true,
            'order_confirmation_message' => 'Thank you for your order! We have received your order and will process it shortly. You will receive a confirmation email once your order is confirmed.',

            // System Settings
            'is_active' => true,
            'updated_by' => null,
        ];

        if ($settings) {
            // Update existing settings
            $settings->update($data);
            $this->command->info('Store settings updated successfully!');
        } else {
            // Create new settings
            StoreSetting::create($data);
            $this->command->info('Store settings created successfully!');
        }
    }
}
