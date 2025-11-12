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

        // Get all indexes and foreign keys
        $indexes = DB::select("SHOW INDEXES FROM product_warehouse_stock");
        $indexNames = collect($indexes)->pluck('Key_name')->toArray();

        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'product_warehouse_stock'
              AND TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME IS NOT NULL
        "))->pluck('CONSTRAINT_NAME')->toArray();

        // Drop foreign keys related to variant_id (if any)
        foreach ($foreignKeys as $fk) {
            if (str_contains($fk, 'variant') || str_contains($fk, 'warehouse')) {
                DB::statement("ALTER TABLE product_warehouse_stock DROP FOREIGN KEY `$fk`");
            }
        }

        // Drop indexes if they exist (only after removing foreign keys)
        foreach (['idx_product_warehouse', 'idx_variant_warehouse', 'unique_product_variant_warehouse'] as $index) {
            if (in_array($index, $indexNames)) {
                DB::statement("ALTER TABLE product_warehouse_stock DROP INDEX `$index`");
            }
        }

        // Add correct unique constraint
        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            $table->unique(['product_id', 'variant_id', 'warehouse_id'], 'unique_product_variant_warehouse');
        });
    }

    public function down(): void
    {
        // Drop the new constraint if exists
        $indexes = DB::select("SHOW INDEXES FROM product_warehouse_stock");
        $indexNames = collect($indexes)->pluck('Key_name')->toArray();

        if (in_array('unique_product_variant_warehouse', $indexNames)) {
            DB::statement("ALTER TABLE product_warehouse_stock DROP INDEX `unique_product_variant_warehouse`");
        }

        // Recreate old unique constraint
        Schema::table('product_warehouse_stock', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id'], 'idx_product_warehouse');
        });
    }
};
