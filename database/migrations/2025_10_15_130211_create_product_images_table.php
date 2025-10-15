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
         Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');

            // Image Information
            $table->string('image_path');
            $table->string('image_name')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();

            // Image Properties
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false)->comment('Main product image');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Indexes
            $table->index('product_id');
            $table->index('is_primary');
            $table->index('sort_order');
            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
