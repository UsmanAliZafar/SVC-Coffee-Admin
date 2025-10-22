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
        Schema::create('transactions', function (Blueprint $table) {
            // Primary UUID
            $table->uuid('id')->primary();

            // Transaction Number (unique, human-readable)
            $table->string('transaction_number', 50)->unique();

            // Order Reference
            $table->uuid('order_id');

            // Customer Reference
            $table->uuid('customer_id')->nullable();


            // Transaction Type
            $table->enum('transaction_type', [
                'payment',
                'refund',
                'partial_refund',
                'authorization',
                'capture',
                'void',
                'chargeback'
            ])->default('payment');

            // Payment Gateway
            $table->string('payment_gateway', 50); // stripe, paypal, manual, etc.
            $table->string('payment_method', 50); // credit_card, debit_card, paypal, cash, bank_transfer

            // Amount Information
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('fee', 12, 2)->default(0); // gateway fees
            $table->decimal('net_amount', 12, 2); // amount - fee

            // Transaction Status (using system_statuses)
            $table->string('status_key_code', 50)->default('TRANSACTION_PENDING');

            // Gateway Response
            $table->string('gateway_transaction_id', 255)->nullable(); // ID from payment gateway
            $table->string('gateway_order_id', 255)->nullable();
            $table->string('gateway_status', 100)->nullable();
            $table->text('gateway_response')->nullable(); // Full response from gateway
            $table->text('gateway_error_message')->nullable();
            $table->string('gateway_error_code', 50)->nullable();

            // Card/Payment Details (masked for security)
            $table->string('card_brand', 50)->nullable(); // visa, mastercard, amex
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_exp_month', 2)->nullable();
            $table->string('card_exp_year', 4)->nullable();
            $table->string('card_holder_name', 255)->nullable();

            // Bank Transfer Details
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_reference_number', 100)->nullable();

            // Authorization Details
            $table->string('authorization_code', 100)->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();

            // Refund Details
            $table->uuid('refund_transaction_id')->nullable(); // Reference to original transaction
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Billing Information Snapshot
            $table->string('billing_name', 255)->nullable();
            $table->string('billing_email', 255)->nullable();
            $table->string('billing_phone', 20)->nullable();
            $table->string('billing_address', 500)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('billing_postal_code', 20)->nullable();

            // Risk & Fraud Detection
            $table->string('risk_score', 50)->nullable();
            $table->string('risk_level', 50)->nullable(); // low, medium, high
            $table->boolean('is_flagged')->default(false);
            $table->text('fraud_notes')->nullable();

            // IP & Device Information
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 50)->nullable();

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();

            // Reconciliation
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->uuid('reconciled_by')->nullable();

            // Additional Information
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('gateway_metadata')->nullable(); // Additional data from gateway

            // Processing Times
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Retry Information (for failed transactions)
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaction_number');
            $table->index('order_id');
            $table->index('customer_id');
            $table->index('transaction_type');
            $table->index('payment_gateway');
            $table->index('payment_method');
            $table->index('status_key_code');
            $table->index('gateway_transaction_id');
            $table->index('is_verified');
            $table->index('is_reconciled');
            $table->index('is_flagged');
            $table->index('created_at');
            $table->index(['order_id', 'transaction_type']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['status_key_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
