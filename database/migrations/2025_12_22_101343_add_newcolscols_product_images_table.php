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
        Schema::table('product_images', function (Blueprint $table) {
            $table->enum('media_type', ['image', 'video'])->default('image')->after('image_path');
            $table->string('mime_type', 100)->nullable()->after('media_type');
            $table->integer('file_size')->nullable()->after('mime_type')->comment('File size in bytes');
            $table->integer('duration')->nullable()->after('file_size')->comment('Video duration in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'mime_type', 'file_size', 'duration']);
        });
    }
};
