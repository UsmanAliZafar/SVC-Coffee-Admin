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
        Schema::table('newsletters', function (Blueprint $table) {
            $table->boolean('email_verified')->default(false)->after('is_subscribed');
            $table->string('verification_token')->nullable()->unique()->after('email_verified');
            $table->timestamp('email_verified_at')->nullable()->after('verification_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropColumn(['email_verified', 'verification_token', 'email_verified_at']);
        });
    }
};
