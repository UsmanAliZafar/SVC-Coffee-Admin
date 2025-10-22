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
       Schema::create('orders', function (Blueprint $table) {
            // Primary UUID
            $table->uuid('id')->primary();

            // Order Number (unique, human-readable)
            $table->string('order_number', 50)->unique();

            // Customer Information
            $table->uuid('customer_id')->nullable();

            // Guest Customer Info (if not registered)
            $table->string('guest_email', 255)->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->string('guest_name', 255)->nullable();

            // Order Status (using system_statuses)
            $table->string('status_key_code', 50)->default('ORDER_PENDING');

            // Payment Status (using system_statuses)
            $table->string('payment_status_key_code', 50)->default('PAYMENT_PENDING');

            // Pricing Information
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            // Discount Information
            $table->string('discount_code', 50)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping'])->nullable();

            // Shipping Information
            $table->string('shipping_method', 100)->nullable();
            $table->string('shipping_carrier', 100)->nullable();
            $table->string('shipping_tracking_number', 100)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('expected_delivery_date')->nullable();

            // Shipping Address
            $table->string('shipping_first_name', 100);
            $table->string('shipping_last_name', 100);
            $table->string('shipping_company', 255)->nullable();
            $table->string('shipping_address_line1', 255);
            $table->string('shipping_address_line2', 255)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_country', 100);
            $table->string('shipping_phone', 20)->nullable();

            // Billing Address
            $table->boolean('billing_same_as_shipping')->default(true);
            $table->string('billing_first_name', 100)->nullable();
            $table->string('billing_last_name', 100)->nullable();
            $table->string('billing_company', 255)->nullable();
            $table->string('billing_address_line1', 255)->nullable();
            $table->string('billing_address_line2', 255)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('billing_phone', 20)->nullable();

            // Tax Information
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->string('tax_name', 100)->nullable();
            $table->boolean('tax_inclusive')->default(false);

            // Additional Information
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Order Source
            $table->enum('order_source', ['web', 'mobile', 'pos', 'phone', 'email', 'admin'])->default('web');
            $table->string('device_type', 50)->nullable();

            // Fulfillment
            $table->uuid('fulfilled_by')->nullable();
            $table->timestamp('fulfilled_at')->nullable();

            // Warehouse (where order is fulfilled from)
            $table->uuid('warehouse_id')->nullable();

            // Cancellation
            $table->uuid('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Invoice
            $table->string('invoice_number', 50)->nullable()->unique();
            $table->timestamp('invoice_generated_at')->nullable();

            // Payment Gateway Info
            $table->string('payment_gateway', 50)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_id', 255)->nullable();

            // Refund Information
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();

            // Status Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('packed_at')->nullable();

            // Additional Metadata
            $table->json('metadata')->nullable();
            $table->json('custom_fields')->nullable();

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index('customer_id');
            $table->index('status_key_code');
            $table->index('payment_status_key_code');
            $table->index('guest_email');
            $table->index('invoice_number');
            $table->index('transaction_id');
            $table->index('order_source');
            $table->index('shipped_at');
            $table->index('delivered_at');
            $table->index('created_at');
            $table->index(['customer_id', 'created_at']);
            $table->index(['status_key_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
