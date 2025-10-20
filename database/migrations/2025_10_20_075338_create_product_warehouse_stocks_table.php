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
        Schema::create('product_warehouse_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('warehouse_id');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // For pending orders
            $table->integer('available_quantity')->default(0); // quantity - reserved_quantity
            $table->string('location')->nullable(); // Shelf/Bin location in warehouse
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');

            // Unique constraint - one record per product per warehouse
            $table->unique(['product_id', 'warehouse_id']);

            // Indexes for performance
            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_warehouse_stocks');
    }
};
