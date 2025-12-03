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
        Schema::table('contact_us', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);

            // Add new foreign key constraint pointing to admin_users
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('admin_users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_us', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['assigned_to']);

            // Restore the old foreign key constraint
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
