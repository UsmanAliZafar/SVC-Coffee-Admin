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
         Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');

            // Variant Information
            $table->string('variant_name')->comment('E.g., Size, Color, Model');
            $table->string('variant_value')->comment('E.g., Large, Red, Pro');
            $table->string('sku', 100)->unique();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();

            // Weight & Dimensions
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();

            // Display
            $table->string('image_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);

            // Status
            $table->string('status_key_code', 100)->default('VARIANT_ACTIVE');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Indexes
            $table->index('product_id');
            $table->index('sku');
            $table->index('status_key_code');
            $table->index('is_default');
            $table->index('sort_order');
            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
