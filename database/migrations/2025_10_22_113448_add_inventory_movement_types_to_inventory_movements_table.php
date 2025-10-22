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
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Drop old enum
            $table->dropColumn('type');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            // Add new comprehensive enum
            $table->enum('type', [
                // Core Operations
                'adjustment',              // Manual adjustment
                'purchase',                // Purchase order received
                'sale',                    // Order fulfilled
                'return',                  // Customer return
                'transfer',                // Transfer between warehouses
                'sync',                    // Warehouse API sync

                // Order Management
                'reservation',             // Stock reserved for order
                'release_reservation',     // Reservation released
                'order_cancelled',         // Order cancellation
                'order_refund',           // Order refund processed
                'exchange',               // Product exchange
                'replacement',            // Replacement sent

                // Quality Control
                'damaged',                // Damaged/Defective
                'quality_fail',           // Failed inspection
                'quality_pass',           // Passed inspection
                'quarantine',             // Moved to quarantine
                'quarantine_release',     // Released from quarantine

                // Loss & Found
                'lost',                   // Lost/Stolen
                'found',                  // Found inventory
                'theft',                  // Confirmed theft
                'shrinkage',              // Inventory shrinkage

                // Supplier Operations
                'supplier_return',        // Return to supplier
                'supplier_credit',        // Supplier credit
                'restock',               // General restocking

                // Production
                'manufacturing',          // Manufacturing/Assembly
                'assembly',              // Assembled from parts
                'disassembly',           // Broken into parts
                'consumption',           // Consumed in production
                'scrap',                 // Scrapped

                // Warehouse Operations
                'receiving',             // Goods receiving
                'putaway',              // Put into storage
                'picking',              // Picked for order
                'packing',              // Being packed
                'shipping',             // Shipped out
                'cycle_count',          // Cycle count adjustment
                'physical_count',       // Physical inventory
                'location_transfer',    // Location change

                // Special Cases
                'expired',              // Product expired
                'obsolete',             // Marked obsolete
                'write_off',            // Written off
                'sample',               // Sample given
                'promotion',            // Promotional giveaway
                'internal_use',         // Internal use
                'warranty_replacement', // Warranty replacement
            ])->after('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->enum('type', [
                'adjustment',
                'purchase',
                'sale',
                'return',
                'transfer',
                'damaged',
                'lost',
                'found',
                'manufacturing',
                'sync'
            ])->after('warehouse_id');
        });
    }
};
