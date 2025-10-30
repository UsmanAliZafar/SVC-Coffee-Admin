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
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic Information
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Coupon Type: percentage, fixed_amount, free_shipping, buy_x_get_y
            $table->enum('discount_type', [
                'percentage',
                'fixed_amount',
                'free_shipping',
                'buy_x_get_y'
            ])->default('percentage');

            // Discount Values
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // For percentage coupons

            // Minimum Requirements
            $table->decimal('min_purchase_amount', 10, 2)->default(0);
            $table->integer('min_items_count')->default(0);

            // Usage Limits
            $table->integer('usage_limit_total')->nullable(); // Total times coupon can be used
            $table->integer('usage_limit_per_customer')->default(1);
            $table->integer('total_used')->default(0);

            // Validity Period
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            // Applicability
            $table->boolean('applies_to_sale_items')->default(true);
            $table->boolean('first_order_only')->default(false);

            // Product/Category Restrictions
            $table->json('applicable_product_ids')->nullable(); // Array of product UUIDs
            $table->json('applicable_category_ids')->nullable(); // Array of category UUIDs
            $table->json('excluded_product_ids')->nullable();
            $table->json('excluded_category_ids')->nullable();

            // Buy X Get Y Configuration (for buy_x_get_y type)
            $table->integer('buy_quantity')->nullable();
            $table->integer('get_quantity')->nullable();
            $table->uuid('buy_product_id')->nullable();
            $table->uuid('get_product_id')->nullable();

            // Customer Restrictions
            $table->json('applicable_customer_ids')->nullable(); // Array of customer UUIDs
            $table->json('applicable_customer_groups')->nullable(); // Array of customer group IDs

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Show on homepage/banners

            // Admin Management
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('discount_type');
            $table->index('is_active');
            $table->index(['valid_from', 'valid_until']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
