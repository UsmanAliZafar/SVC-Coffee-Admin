<?php

namespace Database\Seeders;

use App\Models\SystemStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SystemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allStatuses = [
            // ============================================
            // CATEGORIES MODULE STATUSES
            // ============================================
            [
                'module' => 'categories',
                'name' => 'Active',
                'key_code' => 'CATEGORY_ACTIVE',
                'slug' => 'active',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-check-circle-fill',
                'description' => 'Category is active and visible on the website',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
            ],
            [
                'module' => 'categories',
                'name' => 'Inactive',
                'key_code' => 'CATEGORY_INACTIVE',
                'slug' => 'inactive',
                'color' => '#ffffff',
                'bg_color' => '#6c757d',
                'icon' => 'bi-dash-circle-fill',
                'description' => 'Category is inactive and hidden from the website',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'module' => 'categories',
                'name' => 'Draft',
                'key_code' => 'CATEGORY_DRAFT',
                'slug' => 'draft',
                'color' => '#856404',
                'bg_color' => '#fff3cd',
                'icon' => 'bi-pencil-square',
                'description' => 'Category is in draft mode',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
            ],

            // ============================================
            // PRODUCTS MODULE STATUSES
            // ============================================
            [
                'module' => 'products',
                'name' => 'Active',
                'key_code' => 'PRODUCT_ACTIVE',
                'slug' => 'active',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-check-circle-fill',
                'description' => 'Product is active and available for purchase',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
                'send_notification' => true,
            ],
            [
                'module' => 'products',
                'name' => 'Draft',
                'key_code' => 'PRODUCT_DRAFT',
                'slug' => 'draft',
                'color' => '#856404',
                'bg_color' => '#fff3cd',
                'icon' => 'bi-pencil-square',
                'description' => 'Product is in draft and not visible to customers',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'module' => 'products',
                'name' => 'Out of Stock',
                'key_code' => 'PRODUCT_OUT_OF_STOCK',
                'slug' => 'out-of-stock',
                'color' => '#ffffff',
                'bg_color' => '#dc3545',
                'icon' => 'bi-x-circle-fill',
                'description' => 'Product is out of stock',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'send_email' => true,
                'email_template' => 'product_out_of_stock',
            ],
            [
                'module' => 'products',
                'name' => 'Discontinued',
                'key_code' => 'PRODUCT_DISCONTINUED',
                'slug' => 'discontinued',
                'color' => '#ffffff',
                'bg_color' => '#6c757d',
                'icon' => 'bi-archive-fill',
                'description' => 'Product is discontinued and no longer available',
                'order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
            ],

            // ============================================
            // ORDERS MODULE STATUSES
            // ============================================
            [
                'module' => 'orders',
                'name' => 'Pending',
                'key_code' => 'ORDER_PENDING',
                'slug' => 'pending',
                'color' => '#856404',
                'bg_color' => '#fff3cd',
                'icon' => 'bi-clock-fill',
                'description' => 'Order received and awaiting processing',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
                'allowed_transitions' => ['ORDER_CONFIRMED', 'ORDER_CANCELLED'],
                'send_email' => true,
                'email_template' => 'order_pending',
            ],
            [
                'module' => 'orders',
                'name' => 'Confirmed',
                'key_code' => 'ORDER_CONFIRMED',
                'slug' => 'confirmed',
                'color' => '#004085',
                'bg_color' => '#cce5ff',
                'icon' => 'bi-check2-circle',
                'description' => 'Order confirmed and being prepared',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'allowed_transitions' => ['ORDER_PROCESSING', 'ORDER_CANCELLED'],
                'send_email' => true,
                'email_template' => 'order_confirmed',
            ],
            [
                'module' => 'orders',
                'name' => 'Processing',
                'key_code' => 'ORDER_PROCESSING',
                'slug' => 'processing',
                'color' => '#0c5460',
                'bg_color' => '#d1ecf1',
                'icon' => 'bi-gear-fill',
                'description' => 'Order is being processed',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'allowed_transitions' => ['ORDER_SHIPPED', 'ORDER_CANCELLED'],
            ],
            [
                'module' => 'orders',
                'name' => 'Shipped',
                'key_code' => 'ORDER_SHIPPED',
                'slug' => 'shipped',
                'color' => '#383d41',
                'bg_color' => '#d6d8db',
                'icon' => 'bi-truck',
                'description' => 'Order has been shipped',
                'order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'allowed_transitions' => ['ORDER_DELIVERED', 'ORDER_RETURNED'],
                'send_email' => true,
                'email_template' => 'order_shipped',
                'send_notification' => true,
            ],
            [
                'module' => 'orders',
                'name' => 'Delivered',
                'key_code' => 'ORDER_DELIVERED',
                'slug' => 'delivered',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-check-circle-fill',
                'description' => 'Order has been delivered successfully',
                'order' => 5,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'order_delivered',
                'send_notification' => true,
            ],
            [
                'module' => 'orders',
                'name' => 'Cancelled',
                'key_code' => 'ORDER_CANCELLED',
                'slug' => 'cancelled',
                'color' => '#ffffff',
                'bg_color' => '#dc3545',
                'icon' => 'bi-x-circle-fill',
                'description' => 'Order has been cancelled',
                'order' => 6,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'order_cancelled',
            ],
            [
                'module' => 'orders',
                'name' => 'Returned',
                'key_code' => 'ORDER_RETURNED',
                'slug' => 'returned',
                'color' => '#721c24',
                'bg_color' => '#f8d7da',
                'icon' => 'bi-arrow-return-left',
                'description' => 'Order has been returned',
                'order' => 7,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'order_returned',
            ],

            // ============================================
            // CUSTOMERS MODULE STATUSES
            // ============================================
            [
                'module' => 'customers',
                'name' => 'Active',
                'key_code' => 'CUSTOMER_ACTIVE',
                'slug' => 'active',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-person-check-fill',
                'description' => 'Customer account is active',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
            ],
            [
                'module' => 'customers',
                'name' => 'Inactive',
                'key_code' => 'CUSTOMER_INACTIVE',
                'slug' => 'inactive',
                'color' => '#ffffff',
                'bg_color' => '#6c757d',
                'icon' => 'bi-person-dash-fill',
                'description' => 'Customer account is inactive',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'module' => 'customers',
                'name' => 'Blocked',
                'key_code' => 'CUSTOMER_BLOCKED',
                'slug' => 'blocked',
                'color' => '#ffffff',
                'bg_color' => '#dc3545',
                'icon' => 'bi-person-x-fill',
                'description' => 'Customer account is blocked',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'required_permissions' => ['customers.block'],
            ],

            // ============================================
            // INVENTORY MODULE STATUSES
            // ============================================
            [
                'module' => 'inventory',
                'name' => 'In Stock',
                'key_code' => 'INVENTORY_IN_STOCK',
                'slug' => 'in-stock',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-box-seam-fill',
                'description' => 'Item is in stock',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
            ],
            [
                'module' => 'inventory',
                'name' => 'Low Stock',
                'key_code' => 'INVENTORY_LOW_STOCK',
                'slug' => 'low-stock',
                'color' => '#856404',
                'bg_color' => '#fff3cd',
                'icon' => 'bi-exclamation-triangle-fill',
                'description' => 'Item stock is running low',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'send_email' => true,
                'email_template' => 'inventory_low_stock',
            ],
            [
                'module' => 'inventory',
                'name' => 'Out of Stock',
                'key_code' => 'INVENTORY_OUT_OF_STOCK',
                'slug' => 'out-of-stock',
                'color' => '#ffffff',
                'bg_color' => '#dc3545',
                'icon' => 'bi-x-circle-fill',
                'description' => 'Item is out of stock',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'send_email' => true,
                'email_template' => 'inventory_out_of_stock',
            ],
            [
                'module' => 'inventory',
                'name' => 'Reserved',
                'key_code' => 'INVENTORY_RESERVED',
                'slug' => 'reserved',
                'color' => '#004085',
                'bg_color' => '#cce5ff',
                'icon' => 'bi-lock-fill',
                'description' => 'Item stock is reserved for pending orders',
                'order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
            ],

            // ============================================
            // PAYMENTS MODULE STATUSES
            // ============================================
            [
                'module' => 'payments',
                'name' => 'Pending',
                'key_code' => 'PAYMENT_PENDING',
                'slug' => 'pending',
                'color' => '#856404',
                'bg_color' => '#fff3cd',
                'icon' => 'bi-clock-fill',
                'description' => 'Payment is pending',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
            ],
            [
                'module' => 'payments',
                'name' => 'Processing',
                'key_code' => 'PAYMENT_PROCESSING',
                'slug' => 'processing',
                'color' => '#004085',
                'bg_color' => '#cce5ff',
                'icon' => 'bi-hourglass-split',
                'description' => 'Payment is being processed',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'module' => 'payments',
                'name' => 'Completed',
                'key_code' => 'PAYMENT_COMPLETED',
                'slug' => 'completed',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-check-circle-fill',
                'description' => 'Payment completed successfully',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'payment_completed',
            ],
            [
                'module' => 'payments',
                'name' => 'Failed',
                'key_code' => 'PAYMENT_FAILED',
                'slug' => 'failed',
                'color' => '#ffffff',
                'bg_color' => '#dc3545',
                'icon' => 'bi-x-circle-fill',
                'description' => 'Payment failed',
                'order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'payment_failed',
            ],
            [
                'module' => 'payments',
                'name' => 'Refunded',
                'key_code' => 'PAYMENT_REFUNDED',
                'slug' => 'refunded',
                'color' => '#721c24',
                'bg_color' => '#f8d7da',
                'icon' => 'bi-arrow-return-left',
                'description' => 'Payment has been refunded',
                'order' => 5,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'payment_refunded',
            ],

            // ============================================
            // SHIPMENTS MODULE STATUSES
            // ============================================
            [
                'module' => 'shipments',
                'name' => 'Pending',
                'key_code' => 'SHIPMENT_PENDING',
                'slug' => 'pending',
                'color' => '#856404',
                'bg_color' => '#fff3cd',
                'icon' => 'bi-clock-fill',
                'description' => 'Shipment is pending and awaiting pickup',
                'order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_final' => false,
            ],
            [
                'module' => 'shipments',
                'name' => 'In Transit',
                'key_code' => 'SHIPMENT_IN_TRANSIT',
                'slug' => 'in-transit',
                'color' => '#004085',
                'bg_color' => '#cce5ff',
                'icon' => 'bi-truck',
                'description' => 'Shipment is in transit',
                'order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'send_notification' => true,
            ],
            [
                'module' => 'shipments',
                'name' => 'Out for Delivery',
                'key_code' => 'SHIPMENT_OUT_FOR_DELIVERY',
                'slug' => 'out-for-delivery',
                'color' => '#0c5460',
                'bg_color' => '#d1ecf1',
                'icon' => 'bi-geo-alt-fill',
                'description' => 'Shipment is out for delivery',
                'order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'send_email' => true,
                'email_template' => 'shipment_out_for_delivery',
                'send_notification' => true,
            ],
            [
                'module' => 'shipments',
                'name' => 'Delivered',
                'key_code' => 'SHIPMENT_DELIVERED',
                'slug' => 'delivered',
                'color' => '#ffffff',
                'bg_color' => '#28a745',
                'icon' => 'bi-check-circle-fill',
                'description' => 'Shipment has been delivered',
                'order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'shipment_delivered',
                'send_notification' => true,
            ],
            [
                'module' => 'shipments',
                'name' => 'Failed Delivery',
                'key_code' => 'SHIPMENT_FAILED_DELIVERY',
                'slug' => 'failed-delivery',
                'color' => '#721c24',
                'bg_color' => '#f8d7da',
                'icon' => 'bi-exclamation-triangle-fill',
                'description' => 'Delivery attempt failed',
                'order' => 5,
                'is_active' => true,
                'is_default' => false,
                'is_final' => false,
                'send_email' => true,
                'email_template' => 'shipment_failed_delivery',
            ],
            [
                'module' => 'shipments',
                'name' => 'Returned',
                'key_code' => 'SHIPMENT_RETURNED',
                'slug' => 'returned',
                'color' => '#ffffff',
                'bg_color' => '#dc3545',
                'icon' => 'bi-arrow-return-left',
                'description' => 'Shipment has been returned',
                'order' => 6,
                'is_active' => true,
                'is_default' => false,
                'is_final' => true,
                'send_email' => true,
                'email_template' => 'shipment_returned',
            ],
        ];

        // Insert or update each status
        foreach ($allStatuses as $statusData) {
            // Ensure array fields are properly handled
            $statusData['allowed_transitions'] = isset($statusData['allowed_transitions'])
                ? json_encode($statusData['allowed_transitions'])
                : null;

            $statusData['required_permissions'] = isset($statusData['required_permissions'])
                ? json_encode($statusData['required_permissions'])
                : null;

            $statusData['automation_triggers'] = isset($statusData['automation_triggers'])
                ? json_encode($statusData['automation_triggers'])
                : null;

            $statusData['metadata'] = isset($statusData['metadata'])
                ? json_encode($statusData['metadata'])
                : null;

            // Check if status already exists by key_code
            $existingStatus = SystemStatus::where('key_code', $statusData['key_code'])->first();

            if ($existingStatus) {
                // Update existing status
                $existingStatus->update($statusData);
                $this->command->info("✓ Updated: {$statusData['key_code']}");
            } else {
                // Create new status with UUID
                SystemStatus::create(array_merge($statusData, ['id' => Str::uuid()]));
                $this->command->info("✓ Created: {$statusData['key_code']}");
            }
        }

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('System Status Seeder Completed Successfully');
        $this->command->info('===========================================');
        $this->command->info('Total Statuses: ' . count($allStatuses));
        $this->command->info('');
    }
}
