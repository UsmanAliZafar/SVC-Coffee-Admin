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
            // Add new social media fields
            $table->string('tiktok_url')->nullable()->after('youtube_url');
            $table->string('pinterest_url')->nullable()->after('tiktok_url');
            $table->string('whatsapp_url')->nullable()->after('pinterest_url');
            $table->string('telegram_url')->nullable()->after('whatsapp_url');
            $table->string('snapchat_url')->nullable()->after('telegram_url');

            // You can also add these if needed:
            $table->string('github_url')->nullable()->after('snapchat_url');
            $table->string('discord_url')->nullable()->after('github_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tiktok_url',
                'pinterest_url',
                'whatsapp_url',
                'telegram_url',
                'snapchat_url',
                'github_url',
                'discord_url'
            ]);
        });
    }
};
