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
        Schema::table('store_settings', function (Blueprint $table) {
            // Remove old fields
            $table->dropColumn(['order_auto_confirm', 'order_notification_email']);

            // Add new field
            $table->decimal('order_threshold', 10, 2)->nullable()->after('order_number_length');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            // Restore old fields
            $table->boolean('order_auto_confirm')->default(false)->after('order_number_length');
            $table->boolean('order_notification_email')->default(false)->after('order_auto_confirm');

            // Remove new field
            $table->dropColumn('order_threshold');
        });
    }
};
