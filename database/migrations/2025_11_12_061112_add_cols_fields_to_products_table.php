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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable()->comment('Base weight in kg')->after('low_stock_threshold');
            $table->decimal('length', 10, 2)->nullable()->comment('Length in cm')->after('low_stock_threshold');
            $table->decimal('width', 10, 2)->nullable()->comment('Width in cm')->after('low_stock_threshold');
            $table->decimal('height', 10, 2)->nullable()->comment('Height in cm')->after('low_stock_threshold');
            $table->decimal('volume', 10, 2)->nullable()->comment('Volume in liters')->after('low_stock_threshold');

            // Product type indicator
            $table->boolean('has_variants')->default(false)->after('low_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
