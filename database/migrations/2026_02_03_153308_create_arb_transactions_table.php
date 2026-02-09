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
        Schema::create('arb_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique();
            $table->string('track_id')->index();
            $table->string('transaction_id')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('SAR');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_mobile')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'error', 'refunded', 'void'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('auth_code')->nullable();
            $table->string('ref_number')->nullable();
            $table->string('error_code')->nullable();
            $table->text('result_message')->nullable();
            $table->json('response_data')->nullable();
            $table->json('webhook_data')->nullable();
            $table->json('verification_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('webhook_received_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('customer_email');
        });

        Schema::create('arb_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->index();
            $table->string('track_id');
            $table->string('refund_transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('arb_saved_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('card_token')->unique();
            $table->string('card_number_masked', 20); // Last 4 digits
            $table->string('card_brand')->nullable();
            $table->string('card_expiry', 4); // YYMM format
            $table->string('cardholder_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arb_saved_cards');
        Schema::dropIfExists('arb_refunds');
        Schema::dropIfExists('arb_transactions');
    }
};
