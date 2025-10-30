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
        Schema::create('coupon_usage', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('coupon_id');
            $table->uuid('customer_id')->nullable(); // Nullable for guest checkouts
            $table->uuid('order_id');

            $table->string('coupon_code', 50);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('order_subtotal', 10, 2);
            $table->decimal('order_total', 10, 2);

            $table->string('customer_email')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamp('used_at');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // Indexes
            $table->index('coupon_id');
            $table->index('customer_id');
            $table->index('order_id');
            $table->index('used_at');
            $table->index(['coupon_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
