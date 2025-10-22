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
        Schema::create('order_items', function (Blueprint $table) {
            // Primary UUID
            $table->uuid('id')->primary();

            // Order Reference
            $table->uuid('order_id');
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');

            // Product Information
            $table->uuid('product_id')->nullable();
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');

            // Product Variant (if applicable)
            $table->uuid('product_variant_id')->nullable();

            // Snapshot of product info at time of order
            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->text('product_description')->nullable();
            $table->string('product_image')->nullable();

            // Variant information snapshot
            $table->json('variant_options')->nullable(); // e.g., {"size": "Large", "color": "Red"}

            // Quantity & Pricing
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2); // price per unit at time of order
            $table->decimal('sale_price', 12, 2)->nullable(); // if item was on sale
            $table->decimal('cost_price', 12, 2)->nullable(); // cost for profit calculation

            // Discount for this item
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->nullable();

            // Tax for this item
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->boolean('is_taxable')->default(true);

            // Total Calculations
            $table->decimal('subtotal', 12, 2); // unit_price * quantity
            $table->decimal('total', 12, 2); // subtotal - discount + tax

            // Warehouse fulfillment
            $table->uuid('warehouse_id')->nullable();
            $table->boolean('is_fulfilled')->default(false);
            $table->timestamp('fulfilled_at')->nullable();

            // Refund Information
            $table->boolean('is_refunded')->default(false);
            $table->integer('refunded_quantity')->default(0);
            $table->decimal('refunded_amount', 12, 2)->default(0);

            // Item Status (using system_statuses)
            $table->string('status_key_code', 50)->default('ITEM_PENDING');
            $table->foreign('status_key_code')
                  ->references('key_code')
                  ->on('system_statuses')
                  ->onDelete('restrict');

            // Stock reservation
            $table->boolean('stock_reserved')->default(false);
            $table->timestamp('stock_reserved_at')->nullable();
            $table->boolean('stock_deducted')->default(false);
            $table->timestamp('stock_deducted_at')->nullable();

            // Additional Information
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('metadata')->nullable();

            // Sort Order (for display)
            $table->integer('sort_order')->default(0);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_id');
            $table->index('product_id');
            $table->index('product_sku');
            $table->index('status_key_code');
            $table->index('warehouse_id');
            $table->index('is_fulfilled');
            $table->index('is_refunded');
            $table->index(['order_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
