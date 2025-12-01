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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Order Information
            $table->string('po_number', 100)->unique()->comment('Purchase Order Number');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            // Vendor Information
            $table->uuid('vendor_id');
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('restrict');

            // Reference Information
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();

            // Financial Information
            $table->string('currency', 10)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // Payment Information
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_terms', 100)->nullable()->comment('e.g., Net 30, Net 60');
            $table->string('payment_status', 50)->default('pending')->comment('pending, partial, paid, overdue');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('payment_due_date')->nullable();

            // Shipping Information
            $table->text('shipping_address')->nullable();
            $table->string('shipping_method', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();

            // Status
            $table->string('status_key_code', 100)->default('PO_DRAFT');
            $table->string('approval_status', 50)->default('pending')->comment('pending, approved, rejected');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Fulfillment
            $table->integer('total_items')->default(0);
            $table->integer('received_items')->default(0);
            $table->boolean('is_fully_received')->default(false);
            $table->date('received_date')->nullable();

            // Additional Information
            $table->string('department', 100)->nullable();
            $table->string('project_code', 100)->nullable();
            $table->json('attachments')->nullable();

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('po_number');
            $table->index('vendor_id');
            $table->index('order_date');
            $table->index('status_key_code');
            $table->index('payment_status');
            $table->index('approval_status');
            $table->index('expected_delivery_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
