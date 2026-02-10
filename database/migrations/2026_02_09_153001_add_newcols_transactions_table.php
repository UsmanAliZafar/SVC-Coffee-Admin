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
        Schema::table('transactions', function (Blueprint $table) {
            // ARB Payment Gateway fields
            $table->string('track_id')->nullable()->after('transaction_number')->index();
            $table->string('arb_payment_id')->nullable()->after('gateway_transaction_id');
            $table->string('arb_transaction_id')->nullable()->after('arb_payment_id');
            $table->string('auth_resp_code')->nullable()->after('authorization_code');
            $table->string('ref_number')->nullable()->after('arb_transaction_id');
            $table->timestamp('arb_paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
