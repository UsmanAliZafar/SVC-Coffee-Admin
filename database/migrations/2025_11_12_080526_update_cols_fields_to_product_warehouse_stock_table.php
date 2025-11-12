<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            // Add variant_id column if not exists
            if (!Schema::hasColumn('product_warehouse_stock', 'variant_id')) {
                $table->uuid('variant_id')->nullable()->after('product_id');
            }
        });

        // Drop any existing similar indexes
        DB::statement('ALTER TABLE product_warehouse_stock DROP INDEX IF EXISTS idx_product_warehouse');
        // DB::statement('ALTER TABLE product_warehouse_stock DROP INDEX IF EXISTS idx_variant_warehouse');
        DB::statement('ALTER TABLE product_warehouse_stock DROP INDEX IF EXISTS unique_product_variant_warehouse');

        // Add correct unique constraint
        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            $table->unique(['product_id', 'variant_id', 'warehouse_id'], 'unique_product_variant_warehouse');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE product_warehouse_stock DROP INDEX IF EXISTS unique_product_variant_warehouse');

        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id'], 'idx_product_warehouse');
        });
    }
};
