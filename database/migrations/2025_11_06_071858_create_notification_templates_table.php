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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('notification_type', 100); // order_created, stock_low, etc.
            $table->enum('channel', ['email', 'sms', 'push'])->default('email');
            $table->string('subject')->nullable(); // Email subject
            $table->text('body'); // Email body (HTML/Markdown) or SMS text
            $table->json('variables')->nullable(); // Available placeholders: {order_number}, {product_name}, etc.
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable(); // Template description
            $table->json('metadata')->nullable(); // Additional settings
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['notification_type', 'channel']);

            // Indexes
            $table->index('notification_type');
            $table->index('channel');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
