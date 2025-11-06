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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_user_id');
            $table->string('notification_type', 100); // order_created, stock_low, etc.
            $table->boolean('is_enabled')->default(true); // In-app notification enabled
            $table->boolean('send_email')->default(false); // Email notification enabled
            $table->string('email_address')->nullable(); // Custom email (default: admin's email)
            $table->boolean('send_sms')->default(false); // Future: SMS notification
            $table->string('sms_number')->nullable(); // Future: Custom SMS number
            $table->integer('threshold_value')->nullable(); // For stock alerts, etc.
            $table->json('custom_settings')->nullable(); // Additional settings per notification type
            $table->timestamps();

            // Indexes
            $table->unique(['admin_user_id', 'notification_type']);
            $table->index('notification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
