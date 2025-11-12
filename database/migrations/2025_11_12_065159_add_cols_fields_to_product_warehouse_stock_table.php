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
        // 1. Add variant_id to product_warehouse_stock
        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            $table->uuid('variant_id')->nullable()->after('product_id');

            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('cascade');

            // Add composite indexes for performance
            $table->index(['product_id', 'warehouse_id'], 'idx_product_warehouse');
            $table->index(['variant_id', 'warehouse_id'], 'idx_variant_warehouse');

            // Unique constraint: one record per product/variant per warehouse
            $table->unique(['product_id', 'variant_id', 'warehouse_id'], 'unique_product_variant_warehouse');
        });

        // 2. Add variant_id to inventory_movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->uuid('variant_id')->nullable()->after('product_id');

            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('cascade');

            // Add index for querying variant movements
            $table->index(['variant_id', 'created_at'], 'idx_variant_movements');
            $table->index(['product_id', 'variant_id'], 'idx_product_variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop from inventory_movements first (has FK to product_warehouse_stock)
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('idx_variant_movements');
            $table->dropIndex('idx_product_variant');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });

        // Then drop from product_warehouse_stock
        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            $table->dropUnique('unique_product_variant_warehouse');
            $table->dropIndex('idx_product_warehouse');
            $table->dropIndex('idx_variant_warehouse');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });
    }
};
