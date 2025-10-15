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
        // Product Tags Table
        Schema::create('product_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic Information
            $table->string('name', 255);
            $table->string('slug', 255)->unique();

            // Status
            $table->string('status_key_code', 100)->default('TAG_ACTIVE');

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('status_key_code');
            $table->index('sort_order');
        });

        // Product-Tag Pivot Table (Many-to-Many)
        Schema::create('product_tag', function (Blueprint $table) {
            $table->uuid('product_id');
            $table->uuid('tag_id');

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('product_tags')->onDelete('cascade');

            // Primary Key
            $table->primary(['product_id', 'tag_id']);

            // Indexes
            $table->index('product_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('product_tags');
    }
};
