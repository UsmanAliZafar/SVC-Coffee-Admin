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
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic Information
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('company_name', 255)->nullable();
            $table->text('description')->nullable();

            // Contact Information
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('website', 255)->nullable();

            // Address Information
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            // Business Information
            $table->string('tax_number', 100)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_name', 255)->nullable();
            $table->string('bank_routing_number', 100)->nullable();

            // Logo & Images
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable();

            // Social Media
            $table->json('social_media')->nullable()->comment('Social media links');

            // Payment & Terms
            $table->string('payment_terms', 100)->nullable()->comment('e.g., Net 30, Net 60');
            $table->decimal('credit_limit', 12, 2)->nullable()->comment('Maximum credit allowed');
            $table->string('currency', 10)->default('USD');

            // Status
            $table->string('status_key_code', 100)->default('VENDOR_ACTIVE');

            // Statistics
            $table->integer('products_count')->default(0);
            $table->decimal('total_purchases', 12, 2)->default(0);
            $table->integer('orders_count')->default(0);

            // Rating & Performance
            $table->decimal('rating', 3, 2)->nullable()->comment('Average rating 0-5');
            $table->integer('reviews_count')->default(0);

            // Settings
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('sort_order')->default(0);

            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();

            // Additional Data
            $table->json('attributes')->nullable()->comment('Additional vendor attributes');

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('email');
            $table->index('status_key_code');
            $table->index('is_featured');
            $table->index('is_verified');
            $table->index('sort_order');
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
        Schema::dropIfExists('vendors');
    }
};
