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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('warehouse_id')->nullable();
            $table->uuid('from_warehouse_id')->nullable(); // For transfers
            $table->uuid('to_warehouse_id')->nullable(); // For transfers
            $table->enum('type', [
                'adjustment',      // Manual adjustment
                'purchase',        // Purchase order received
                'sale',           // Order fulfilled
                'return',         // Customer return
                'transfer',       // Transfer between warehouses
                'damaged',        // Damaged/Defective
                'lost',           // Lost/Stolen
                'found',          // Found inventory
                'manufacturing',  // Manufacturing/Assembly
                'sync'           // Warehouse sync
            ]);
            $table->integer('quantity'); // Can be positive or negative
            $table->integer('previous_quantity')->nullable();
            $table->integer('new_quantity')->nullable();
            $table->string('reference_type')->nullable(); // e.g., 'order', 'purchase_order'
            $table->string('reference_id')->nullable(); // ID of reference
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');

            // Indexes for faster queries
            $table->index(['product_id', 'created_at']);
            $table->index(['warehouse_id', 'created_at']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
