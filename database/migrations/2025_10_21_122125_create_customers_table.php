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
        Schema::create('customers', function (Blueprint $table) {
            // Primary UUID
            $table->uuid('id')->primary();

            // Basic Information
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('company_name', 255)->nullable();

            // Authentication (if customers can login)
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();

            // Customer Type
            $table->enum('customer_type', ['individual', 'business', 'wholesale', 'vip'])->default('individual');

            // Customer Status
            $table->string('status_key_code', 50)->default('CUSTOMER_ACTIVE');

            // Customer Group (for segmentation)
            $table->uuid('customer_group_id')->nullable();

            // Default Billing Address
            $table->string('billing_address_line1', 255)->nullable();
            $table->string('billing_address_line2', 255)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 100)->nullable();

            // Default Shipping Address
            $table->string('shipping_address_line1', 255)->nullable();
            $table->string('shipping_address_line2', 255)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 100)->nullable();

            // Business Information (for business customers)
            $table->string('tax_id', 50)->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->string('business_registration', 100)->nullable();

            // Account Settings
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_newsletter_subscribed')->default(false);
            $table->boolean('is_sms_subscribed')->default(false);
            $table->string('preferred_language', 10)->default('en');
            $table->string('preferred_currency', 3)->default('USD');

            // Marketing & Analytics
            $table->string('acquisition_source', 100)->nullable(); // how they found us
            $table->string('referral_code', 50)->nullable();
            $table->uuid('referred_by')->nullable(); // customer who referred them

            // Statistics
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('average_order_value', 12, 2)->default(0);
            $table->timestamp('first_order_at')->nullable();
            $table->timestamp('last_order_at')->nullable();

            // Account Activity
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('login_count')->default(0);

            // Notes & Tags
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('preferences')->nullable();
            $table->json('metadata')->nullable();

            // Audit Fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('phone');
            $table->index('status_key_code');
            $table->index('customer_type');
            $table->index('customer_group_id');
            $table->index('is_verified');
            $table->index('total_orders');
            $table->index('total_spent');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
