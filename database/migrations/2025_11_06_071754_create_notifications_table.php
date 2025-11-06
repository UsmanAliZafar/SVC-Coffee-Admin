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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_user_id'); // Recipient
            $table->string('type', 50); // Category: orders, inventory, payments, customers, system
            $table->string('notification_type', 100); // Specific type: order_created, stock_low, etc.
            $table->string('title'); // Notification title
            $table->text('message'); // Notification message
            $table->json('data')->nullable(); // Related data (order_id, product_id, etc.)
            $table->string('icon', 50)->nullable(); // Bootstrap icon class
            $table->string('color', 20)->default('info'); // success, warning, danger, info, primary
            $table->string('action_url')->nullable(); // Link to related resource
            $table->boolean('is_read')->default(false); // Read status
            $table->timestamp('read_at')->nullable(); // When it was read
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('expires_at')->nullable(); // Auto-delete after this date
            $table->timestamps();

            // Indexes for performance
            $table->index(['admin_user_id', 'is_read']);
            $table->index(['admin_user_id', 'created_at']);
            $table->index('notification_type');
            $table->index('type');
            $table->index('priority');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
