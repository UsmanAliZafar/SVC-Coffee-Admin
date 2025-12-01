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
        Schema::table('vendors', function (Blueprint $table) {
            // Remove fields that are no longer needed
            $table->dropColumn([
                'slug',
                'logo',
                'banner_image',
                'social_media',
                'payment_terms',
                'credit_limit',
                'rating',
                'reviews_count',
                'is_featured',
                'is_verified',
                'sort_order',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'attributes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Restore removed fields
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable();
            $table->json('social_media')->nullable()->comment('Social media links');
            $table->string('payment_terms', 100)->nullable()->comment('e.g., Net 30, Net 60');
            $table->decimal('credit_limit', 12, 2)->nullable()->comment('Maximum credit allowed');
            $table->decimal('rating', 3, 2)->nullable()->comment('Average rating 0-5');
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->json('attributes')->nullable()->comment('Additional vendor attributes');
        });
    }
};
