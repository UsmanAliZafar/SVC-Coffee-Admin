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
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('old_url', 500)->unique();
            $table->string('new_url', 500);
            $table->enum('redirect_type', ['301', '302'])->default('301');
            $table->string('entity_type', 100)->nullable(); // 'product', 'category', etc.
            $table->uuid('entity_id')->nullable();
            $table->integer('hit_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('old_url');
            $table->index('new_url');
            $table->index(['entity_type', 'entity_id']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};
