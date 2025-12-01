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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Purchase Order Reference
            $table->uuid('purchase_order_id');
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('purchase_orders')
                  ->onDelete('cascade');

            // Product Reference
            $table->uuid('product_id')->nullable();
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');

            // Item Information (stored for historical record)
            $table->string('product_name', 255);
            $table->string('product_sku', 100)->nullable();
            $table->text('description')->nullable();

            // Quantity & Pricing
            $table->integer('quantity_ordered')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // Status
            $table->string('status', 50)->default('pending')->comment('pending, partial, received, cancelled');
            $table->date('expected_date')->nullable();
            $table->date('received_date')->nullable();

            // Additional Information
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('purchase_order_id');
            $table->index('product_id');
            $table->index('product_sku');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
