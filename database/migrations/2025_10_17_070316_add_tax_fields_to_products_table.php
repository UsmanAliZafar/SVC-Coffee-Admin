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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_taxable')->default(true)->after('cost_price');
            $table->enum('tax_type', ['inclusive', 'exclusive'])->default('exclusive')->after('is_taxable');
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('tax_type')->comment('Tax percentage (e.g., 15.00 for 15%)');
            $table->string('tax_class', 100)->nullable()->after('tax_percentage')->comment('Tax class/category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_taxable',
                'tax_type',
                'tax_percentage',
                'tax_class'
            ]);
        });
    }
};
