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
            // Payment Methods Enablement
            $table->boolean('enable_cod')->default(true)->after('maintenance_message');
            $table->boolean('enable_online_payment')->default(false)->after('enable_cod');
            $table->boolean('enable_bank_transfer')->default(false)->after('enable_online_payment');

            // Payment Method Instructions
            $table->text('cod_instructions')->nullable()->after('enable_bank_transfer');
            $table->text('online_payment_instructions')->nullable()->after('cod_instructions');
            $table->text('bank_transfer_instructions')->nullable()->after('online_payment_instructions');

            // Payment Gateway Settings (for online payments)
            $table->string('payment_gateway')->nullable()->after('bank_transfer_instructions')->comment('stripe, paypal, razorpay, etc.');
            $table->string('payment_gateway_mode')->default('sandbox')->after('payment_gateway')->comment('sandbox or live');
            $table->text('payment_gateway_public_key')->nullable()->after('payment_gateway_mode');
            $table->text('payment_gateway_secret_key')->nullable()->after('payment_gateway_public_key');

            // Bank Transfer Details
            $table->string('bank_name')->nullable()->after('payment_gateway_secret_key');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
            $table->string('bank_iban')->nullable()->after('bank_account_number');
            $table->string('bank_swift_code')->nullable()->after('bank_iban');
            $table->string('bank_branch')->nullable()->after('bank_swift_code');

            // Additional Checkout Settings
            $table->boolean('require_phone_checkout')->default(true)->after('bank_branch');
            $table->boolean('require_address_checkout')->default(true)->after('require_phone_checkout');
            $table->boolean('enable_guest_checkout')->default(false)->after('require_address_checkout');
            $table->boolean('terms_conditions_required')->default(true)->after('enable_guest_checkout');
            $table->text('checkout_terms_text')->nullable()->after('terms_conditions_required');

            // Order Confirmation Settings
            $table->boolean('show_bank_details_on_confirmation')->default(true)->after('checkout_terms_text');
            $table->text('order_confirmation_message')->nullable()->after('show_bank_details_on_confirmation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'enable_cod',
                'enable_online_payment',
                'enable_bank_transfer',
                'cod_instructions',
                'online_payment_instructions',
                'bank_transfer_instructions',
                'payment_gateway',
                'payment_gateway_mode',
                'payment_gateway_public_key',
                'payment_gateway_secret_key',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_iban',
                'bank_swift_code',
                'bank_branch',
                'require_phone_checkout',
                'require_address_checkout',
                'enable_guest_checkout',
                'terms_conditions_required',
                'checkout_terms_text',
                'show_bank_details_on_confirmation',
                'order_confirmation_message',
            ]);
        });
    }
};
