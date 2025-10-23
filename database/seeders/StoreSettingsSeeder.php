<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('store_settings')->insert([
            // Basic Store Information
            'store_name' => 'Coffee Store',
            'store_email' => 'info@coffeestore.com',
            'store_phone' => '+1 (555) 123-4567',
            'store_address' => '123 Coffee Street',
            'store_city' => 'Lahore',
            'store_state' => 'Punjab',
            'store_zip' => '54000',
            'store_country' => 'PK',

            // Branding
            'store_tagline' => 'Premium Coffee Equipment & Beans',
            'store_description' => 'Your one-stop shop for coffee machines, premium beans, and spare parts.',

            // Regional Settings
            'timezone' => 'Asia/Karachi',
            'date_format' => 'd/m/Y',
            'time_format' => 'h:i A',
            'currency_code' => 'PKR',
            'currency_symbol' => 'Rs.',
            'currency_position' => 'left',
            'decimal_places' => 2,
            'thousand_separator' => ',',
            'decimal_separator' => '.',

            // Order Settings
            'order_prefix' => 'CS-',
            'order_number_start' => 1000,
            'order_number_length' => 6,
            'order_auto_confirm' => false,
            'order_notification_email' => true,

            // Tax Settings
            'tax_enabled' => true,
            'tax_rate' => 17.00, // GST in Pakistan
            'tax_name' => 'GST',
            'tax_included_in_price' => false,

            // Shipping Settings
            'shipping_enabled' => true,
            'free_shipping_threshold' => 5000.00,
            'default_shipping_cost' => 200.00,

            // Inventory Settings
            'track_inventory' => true,
            'allow_backorders' => false,
            'low_stock_threshold' => 10,
            'low_stock_notifications' => true,

            // Email Settings
            'email_from_name' => 'Coffee Store',
            'email_from_address' => 'noreply@coffeestore.com',
            'customer_registration_email' => true,
            'order_confirmation_email' => true,
            'order_shipped_email' => true,

            // Business Hours (JSON format)
            'business_hours' => json_encode([
                'monday' => ['open' => '09:00', 'close' => '18:00', 'is_open' => true],
                'tuesday' => ['open' => '09:00', 'close' => '18:00', 'is_open' => true],
                'wednesday' => ['open' => '09:00', 'close' => '18:00', 'is_open' => true],
                'thursday' => ['open' => '09:00', 'close' => '18:00', 'is_open' => true],
                'friday' => ['open' => '09:00', 'close' => '18:00', 'is_open' => true],
                'saturday' => ['open' => '10:00', 'close' => '16:00', 'is_open' => true],
                'sunday' => ['open' => '10:00', 'close' => '14:00', 'is_open' => false],
            ]),

            // Maintenance Mode
            'maintenance_mode' => false,
            'maintenance_message' => 'We are currently under maintenance. Please check back soon!',

            // SEO
            'meta_title' => 'Coffee Store - Premium Coffee Equipment & Beans',
            'meta_description' => 'Shop the best coffee machines, premium beans, and spare parts. Quality products for coffee enthusiasts.',
            'meta_keywords' => 'coffee, coffee machines, coffee beans, spare parts, coffee equipment',

            // System
            'is_active' => true,
            'updated_by' => 1, // Assuming first admin user
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
