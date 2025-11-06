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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('notification_id')->nullable(); // Link to notification
            $table->uuid('admin_user_id')->nullable(); // Recipient admin
            $table->enum('channel', ['email', 'sms', 'push'])->default('email');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable(); // For SMS
            $table->string('subject')->nullable(); // Email subject
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced', 'delivered'])->default('pending');
            $table->timestamp('sent_at')->nullable(); // When email was sent
            $table->timestamp('delivered_at')->nullable(); // When email was delivered
            $table->timestamp('failed_at')->nullable(); // When sending failed
            $table->text('error_message')->nullable(); // Error details
            $table->integer('retry_count')->default(0); // Number of retry attempts
            $table->timestamp('next_retry_at')->nullable(); // Next retry scheduled time
            $table->json('metadata')->nullable(); // Additional data (gateway response, etc.)
            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('notification_id');
            $table->index('admin_user_id');
            $table->index('channel');
            $table->index('sent_at');
            $table->index(['status', 'retry_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
