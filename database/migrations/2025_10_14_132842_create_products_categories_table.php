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
        Schema::create('products_categories', function (Blueprint $table) {
            // Primary UUID
            $table->uuid('id')->primary();

            // Basic Information
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Parent-Child Relationship for nested categories
            $table->uuid('parent_id')->nullable();

            // Images
            $table->string('image')->nullable()->comment('Main category image');
            $table->string('banner_image')->nullable()->comment('Category banner for listing page');
            $table->string('icon')->nullable()->comment('Icon for category display');
            $table->string('thumbnail')->nullable()->comment('Small thumbnail image');

            // Display & Ordering
            $table->integer('order')->default(0)->comment('Display order');
            $table->boolean('is_featured')->default(false)->comment('Show in featured categories');
            $table->boolean('show_in_menu')->default(true)->comment('Display in navigation menu');
            $table->boolean('show_on_home')->default(false)->comment('Display on homepage');

            // Status (using system_statuses table)
            $table->string('status_key_code', 100)->default('CATEGORY_ACTIVE');

            // SEO Fields
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->json('structured_data')->nullable()->comment('JSON-LD structured data for SEO');

            // Category-Specific Attributes (JSON for flexibility)
            $table->json('attributes')->nullable()->comment('Additional category-specific data');

            // Statistics & Counters
            $table->integer('products_count')->default(0)->comment('Number of products in category');
            $table->integer('views_count')->default(0)->comment('Category page views');
            $table->integer('clicks_count')->default(0)->comment('Category click tracking');

            // Visibility & Settings
            $table->boolean('requires_login')->default(false)->comment('Category requires user login');
            $table->json('visibility_settings')->nullable()->comment('Advanced visibility rules');

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Basic Indexes
            $table->index('slug');
            $table->index('parent_id');
            $table->index('status_key_code');
            $table->index('order');
            $table->index('is_featured');
            $table->index('show_in_menu');
            $table->index('show_on_home');
            $table->index(['parent_id', 'order']);
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
        Schema::dropIfExists('products_categories');
    }
};
