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
        Schema::table('store_settings', function (Blueprint $table) {
            // Shipping calculation method
            $table->enum('shipping_calculation_type', [
                'flat_rate',      // Fixed rate for all orders
                'per_kg',         // Rate per kilogram
                'per_liter',      // Rate per liter
                'per_item',       // Rate per item count
                'tiered'          // Different rates based on order total/weight
            ])->default('flat_rate')->after('default_shipping_cost');

            // Per unit rates
            $table->decimal('shipping_rate_per_kg', 8, 2)->nullable()->after('shipping_calculation_type');
            $table->decimal('shipping_rate_per_liter', 8, 2)->nullable()->after('shipping_rate_per_kg');
            $table->decimal('shipping_rate_per_item', 8, 2)->nullable()->after('shipping_rate_per_liter');

            // Nationwide/Regional rates
            $table->boolean('enable_nationwide_flat_rate')->default(true)->after('shipping_rate_per_item');
            $table->decimal('nationwide_flat_rate', 8, 2)->nullable()->after('enable_nationwide_flat_rate');

            // Regional shipping (for future zone-based rates)
            $table->boolean('enable_regional_rates')->default(false)->after('nationwide_flat_rate');

            // Minimum order value for shipping
            $table->decimal('minimum_order_for_shipping', 8, 2)->nullable()->after('enable_regional_rates');

            // Maximum weight/volume for standard shipping
            $table->decimal('max_weight_standard_shipping', 8, 2)->nullable()->after('minimum_order_for_shipping');
            $table->decimal('max_volume_standard_shipping', 8, 2)->nullable()->after('max_weight_standard_shipping');

            // Handling fee
            $table->decimal('handling_fee', 8, 2)->default(0)->after('max_volume_standard_shipping');

            // Tiered shipping rates (JSON structure)
            $table->json('tiered_shipping_rates')->nullable()->after('handling_fee');

            // Estimated delivery days
            $table->integer('estimated_delivery_days_min')->nullable()->after('tiered_shipping_rates');
            $table->integer('estimated_delivery_days_max')->nullable()->after('estimated_delivery_days_min');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_calculation_type',
                'shipping_rate_per_kg',
                'shipping_rate_per_liter',
                'shipping_rate_per_item',
                'enable_nationwide_flat_rate',
                'nationwide_flat_rate',
                'enable_regional_rates',
                'minimum_order_for_shipping',
                'max_weight_standard_shipping',
                'max_volume_standard_shipping',
                'handling_fee',
                'tiered_shipping_rates',
                'estimated_delivery_days_min',
                'estimated_delivery_days_max',
            ]);
        });
    }
};
