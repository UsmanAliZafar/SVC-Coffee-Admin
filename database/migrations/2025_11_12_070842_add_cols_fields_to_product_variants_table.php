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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->after('status_key_code');
            $table->integer('low_stock_threshold')->default(10)->after('stock_quantity');

            // Add indexes for stock queries
            $table->index('stock_quantity');
            $table->index(['product_id', 'stock_quantity']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['stock_quantity']);
            $table->dropIndex(['product_id', 'stock_quantity']);
            $table->dropColumn(['stock_quantity', 'low_stock_threshold']);
        });
    }
};
