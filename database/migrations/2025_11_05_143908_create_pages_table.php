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
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Featured Image
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();

            // Page Settings
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('template')->default('default'); // For different page layouts
            $table->integer('display_order')->default(0);

            // Navigation Settings
            $table->boolean('show_in_header')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->string('menu_label')->nullable(); // Custom label for menu

            // Advanced Settings
            $table->string('custom_css_class')->nullable();
            $table->text('custom_js')->nullable();
            $table->text('custom_css')->nullable();

            // Parent Page (for hierarchical pages)
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('pages')->onDelete('set null');

            // Publishing
            $table->timestamp('published_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('status');
            $table->index('parent_id');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
