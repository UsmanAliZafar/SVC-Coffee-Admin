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
        Schema::create('translations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Entity reference
            $table->uuid('item_id')->index()->comment('UUID of the item being translated (product_id, category_id, etc.)');
            $table->string('module', 50)->index()->comment('Module name: product, category, vendor, tag, etc.');

            // Translation details
            $table->string('field', 100)->index()->comment('Field being translated: name, title, description, short_description, meta_title, etc.');
            $table->string('lang', 10)->index()->comment('Language code: ar, es, fr, de, ur, etc.');

            // Translation content
            $table->text('value')->nullable()->comment('Translated content for the field');

            // Metadata
            $table->boolean('is_active')->default(true)->index();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite unique index to prevent duplicate translations
            $table->unique(['item_id', 'module', 'field', 'lang'], 'unique_translation');

            // Composite index for faster queries
            $table->index(['module', 'lang'], 'module_lang_index');
            $table->index(['item_id', 'lang'], 'item_lang_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
