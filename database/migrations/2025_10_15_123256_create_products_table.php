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
         Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic Information
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('sku', 255)->unique();
            $table->string('barcode', 255)->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            // Catgory
            $table->string('category_id')->nullable();
            // Product Type
            $table->string('product_type', 100)->default('simple')->comment('simple, variable, grouped, external');
            //Vendor
            $table->uuid('vendor_id')->nullable();
            //
            // Pricing
            $table->string('curency', 10)->default('USD');
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();

            // Featured Image
            $table->string('main_image')->nullable();
            // Status (using system_statuses table)
            $table->string('status_key_code', 100)->default('PRODUCT_DRAFT');

            // Display & Features
            $table->boolean('is_featured')->default(false)->comment('Show in featured products');
            $table->boolean('show_on_home')->default(false)->comment('Display on homepage');
            $table->integer('sort_order')->default(0)->comment('Display order');

            // SEO Fields
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->json('structured_data')->nullable()->comment('JSON-LD structured data for SEO');

            // Product Attributes (JSON for flexibility)
            $table->json('attributes')->nullable()->comment('Additional product-specific data');
            $table->json('specifications')->nullable()->comment('Technical specifications');

            // Availability Settings
            $table->boolean('is_available')->default(true)->comment('Available for purchase');
            $table->boolean('requires_login')->default(false)->comment('Product requires user login');
            $table->timestamp('available_from')->nullable()->comment('Product available from date');
            $table->timestamp('available_until')->nullable()->comment('Product available until date');

            // Visibility & Settings
            $table->json('visibility_settings')->nullable()->comment('Advanced visibility rules');
            $table->boolean('track_inventory')->default(true)->comment('Enable inventory tracking');

            // Publishing
            $table->timestamp('published_at')->nullable();

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('sku');
            $table->index('product_type');
            $table->index('status_key_code');
            $table->index('is_featured');
            $table->index('show_on_home');
            $table->index('sort_order');
            $table->index('is_available');
            $table->index('published_at');
            $table->index(['product_type', 'status_key_code']);
            $table->index(['is_featured', 'sort_order']);
            $table->index('created_at');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
