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
        Schema::create('system_statuses', function (Blueprint $table) {
            // Primary UUID
            $table->$table->bigInteger('votes')->nullable()->default(12);('id')->primary();

            // Module Information
            $table->string('module', 100)->comment('Module name: orders, products, categories, customers, etc.');

            // Status Information
            $table->string('name', 100)->comment('Human-readable status name');
            $table->string('key_code', 100)->unique()->comment('Unique key code for programmatic use');
            $table->string('slug', 100)->comment('URL-friendly slug');

            // Display & Styling
            $table->string('color', 50)->default('#6c757d')->comment('Hex color code for UI display');
            $table->string('bg_color', 50)->default('#f8f9fa')->comment('Background color for badges');
            $table->string('icon', 100)->nullable()->comment('Bootstrap icon class');

            // Description & Notes
            $table->text('description')->nullable()->comment('Detailed description of status');
            $table->text('admin_notes')->nullable()->comment('Internal notes for admin use');

            // Ordering & Behavior
            $table->integer('order')->default(0)->comment('Display order within module');
            $table->boolean('is_active')->default(true)->comment('Is status currently active?');
            $table->boolean('is_default')->default(false)->comment('Is this the default status for new items?');
            $table->boolean('is_final')->default(false)->comment('Is this a final/terminal status?');

            // Permissions & Automation
            $table->json('allowed_transitions')->nullable()->comment('Array of status key_codes this can transition to');
            $table->json('required_permissions')->nullable()->comment('Permissions needed to set this status');
            $table->json('automation_triggers')->nullable()->comment('Automated actions when status is set');

            // Email & Notification Settings
            $table->boolean('send_email')->default(false)->comment('Send email when status changes to this?');
            $table->string('email_template', 100)->nullable()->comment('Email template to use');
            $table->boolean('send_notification')->default(false)->comment('Send system notification?');

            // Additional Metadata
            $table->json('metadata')->nullable()->comment('Additional flexible data');

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('admin_users')
                  ->onDelete('set null');
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('admin_users')
                  ->onDelete('set null');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('module');
            $table->index('key_code');
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_default');
            $table->index(['module', 'is_active']);
            $table->index(['module', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_statuses');
    }
};
