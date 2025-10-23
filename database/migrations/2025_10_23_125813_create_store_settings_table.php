<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();

            // Basic Store Information
            $table->string('store_name')->default('Coffee Store');
            $table->string('store_email')->nullable();
            $table->string('store_phone')->nullable();
            $table->text('store_address')->nullable();
            $table->string('store_city')->nullable();
            $table->string('store_state')->nullable();
            $table->string('store_zip')->nullable();
            $table->string('store_country')->default('US');

            // Branding & Media
            $table->string('store_logo')->nullable();
            $table->string('store_favicon')->nullable();
            $table->string('store_banner')->nullable();
            $table->text('store_description')->nullable();
            $table->string('store_tagline')->nullable();

            // Regional Settings
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i:s');
            $table->string('currency_code')->default('USD');
            $table->string('currency_symbol')->default('$');
            $table->enum('currency_position', ['left', 'right'])->default('left');
            $table->integer('decimal_places')->default(2);
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');

            // Order Settings
            $table->string('order_prefix')->default('ORD-');
            $table->integer('order_number_start')->default(1000);
            $table->integer('order_number_length')->default(6);
            $table->boolean('order_auto_confirm')->default(false);
            $table->boolean('order_notification_email')->default(true);

            // Tax Settings
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('tax_name')->default('VAT');
            $table->boolean('tax_included_in_price')->default(false);

            // Shipping Settings
            $table->boolean('shipping_enabled')->default(true);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->decimal('default_shipping_cost', 10, 2)->default(0.00);

            // Inventory Settings
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorders')->default(false);
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('low_stock_notifications')->default(true);

            // Email Settings
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->boolean('customer_registration_email')->default(true);
            $table->boolean('order_confirmation_email')->default(true);
            $table->boolean('order_shipped_email')->default(true);

            // Social Media Links
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('youtube_url')->nullable();

            // Business Hours
            $table->json('business_hours')->nullable();

            // Maintenance Mode
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();

            // SEO Settings
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Legal & Compliance
            $table->text('terms_conditions')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('return_policy')->nullable();

            // Analytics
            $table->string('google_analytics_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();

            // System Settings
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('updated_by')->references('id')->on('admin_users')->onDelete('set null');

            // Indexes
            $table->index('store_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
