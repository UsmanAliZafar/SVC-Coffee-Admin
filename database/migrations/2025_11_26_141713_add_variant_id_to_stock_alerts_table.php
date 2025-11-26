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
        Schema::table('stock_alerts', function (Blueprint $table) {
            // Add variant_id column after product_id
            $table->uuid('variant_id')->nullable()->after('product_id');

            // Add foreign key constraint
            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('cascade');

            // Add index for better query performance
            $table->index(['product_id', 'variant_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_alerts', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['variant_id']);

            // Drop index
            $table->dropIndex(['product_id', 'variant_id', 'warehouse_id']);

            // Drop column
            $table->dropColumn('variant_id');
        });
    }
};
