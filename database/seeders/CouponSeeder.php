<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME20',
                'name' => 'Welcome Discount',
                'description' => '20% off for new customers on their first order',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'max_discount_amount' => 50.00,
                'min_purchase_amount' => 100.00,
                'usage_limit_per_customer' => 1,
                'first_order_only' => true,
                'is_active' => true,
                'is_featured' => true,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(6),
            ],
            [
                'code' => 'SAVE10',
                'name' => '10 Off Any Order',
                'description' => 'Get $10 off on orders over $50',
                'discount_type' => 'fixed_amount',
                'discount_value' => 10.00,
                'min_purchase_amount' => 50.00,
                'usage_limit_total' => 500,
                'usage_limit_per_customer' => 3,
                'is_active' => true,
                'is_featured' => false,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
            ],
            [
                'code' => 'FREESHIP75',
                'name' => 'Free Shipping on $75+',
                'description' => 'Free shipping on all orders over $75',
                'discount_type' => 'free_shipping',
                'discount_value' => 0,
                'min_purchase_amount' => 75.00,
                'usage_limit_per_customer' => 999,
                'is_active' => true,
                'is_featured' => true,
                'valid_from' => now(),
                'valid_until' => null, // No expiry
            ],
            [
                'code' => 'SUMMER25',
                'name' => 'Summer Sale',
                'description' => '25% off all products for summer',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'max_discount_amount' => 100.00,
                'min_purchase_amount' => 0,
                'usage_limit_total' => 1000,
                'usage_limit_per_customer' => 2,
                'is_active' => true,
                'is_featured' => true,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
            ],
            [
                'code' => 'COFFEE20',
                'name' => '20% Off Coffee Beans',
                'description' => 'Special discount on all coffee beans',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_purchase_amount' => 30.00,
                'usage_limit_per_customer' => 5,
                'applies_to_sale_items' => true,
                'is_active' => true,
                'is_featured' => false,
                'valid_from' => now(),
                'valid_until' => now()->addWeeks(2),
                // Note: You would add applicable_category_ids in real scenario
            ],
            [
                'code' => 'VIP50',
                'name' => 'VIP Customer Exclusive',
                'description' => store_currency_symbol().'50 off for VIP customers only',
                'discount_type' => 'fixed_amount',
                'discount_value' => 50.00,
                'min_purchase_amount' => 200.00,
                'usage_limit_per_customer' => 1,
                'is_active' => true,
                'is_featured' => false,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
                // Note: You would add applicable_customer_ids in real scenario
            ],
            [
                'code' => 'FLASH30',
                'name' => 'Flash Sale - 30% Off',
                'description' => '24-hour flash sale',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'max_discount_amount' => 75.00,
                'min_purchase_amount' => 50.00,
                'usage_limit_total' => 100,
                'usage_limit_per_customer' => 1,
                'is_active' => true,
                'is_featured' => true,
                'valid_from' => now(),
                'valid_until' => now()->addDay(),
            ],
            [
                'code' => 'EXPIRED10',
                'name' => 'Expired Coupon',
                'description' => 'This coupon has expired (for testing)',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'usage_limit_per_customer' => 1,
                'is_active' => true,
                'valid_from' => now()->subMonth(),
                'valid_until' => now()->subDay(),
            ],
            [
                'code' => 'FUTURE15',
                'name' => 'Scheduled Future Coupon',
                'description' => 'This coupon is scheduled for future (for testing)',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'usage_limit_per_customer' => 1,
                'is_active' => true,
                'valid_from' => now()->addWeek(),
                'valid_until' => now()->addMonth(),
            ],
            [
                'code' => 'INACTIVE5',
                'name' => 'Inactive Coupon',
                'description' => 'This coupon is deactivated (for testing)',
                'discount_type' => 'fixed_amount',
                'discount_value' => 5.00,
                'usage_limit_per_customer' => 1,
                'is_active' => false,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
            ],
            [
                'code' => 'MACHINES15',
                'name' => '15% Off Coffee Machines',
                'description' => 'Special discount on coffee machines',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'max_discount_amount' => 150.00,
                'min_purchase_amount' => 200.00,
                'usage_limit_per_customer' => 2,
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
            ],
            [
                'code' => 'PARTS10',
                'name' => store_currency_symbol().' 10 Off Spare Parts',
                'description' => 'Discount on coffee machine spare parts',
                'discount_type' => 'fixed_amount',
                'discount_value' => 10.00,
                'min_purchase_amount' => 40.00,
                'usage_limit_per_customer' => 3,
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($coupons as $couponData) {
            // Check if coupon code already exists
            if (Coupon::where('code', $couponData['code'])->exists()) {
                $skipped++;
                $this->command->warn('Skipped: ' . $couponData['code'] . ' (already exists)');
                continue;
            }

            Coupon::create($couponData);
            $created++;
            $this->command->info('Created: ' . $couponData['code']);
        }

        $this->command->info('─────────────────────────────────');
        $this->command->info('Sample coupons seeding completed!');
        $this->command->info('Created: ' . $created);
        $this->command->info('Skipped: ' . $skipped);
        $this->command->info('Total: ' . count($coupons));
    }
}
