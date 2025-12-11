<?php

return [
    /**
     * Notification Types Configuration
     * Each notification type has its own settings and defaults
     */
    'types' => [
        // ==================== ORDER NOTIFICATIONS ====================
        'order_created' => [
            'label' => 'New Order Created',
            'description' => 'When a new order is placed by a customer',
            'category' => 'orders',
            'icon' => 'bi-cart-plus',
            'color' => 'success',
            'default_email' => true,
            'priority' => 'high',
            'send_to_customer' => true,
        ],
        'order_confirmed' => [
            'label' => 'Order Confirmed',
            'description' => 'When order status changes to confirmed',
            'category' => 'orders',
            'icon' => 'bi-check-circle',
            'color' => 'info',
            'default_email' => false,
            'priority' => 'normal',
            'send_to_customer' => true,
        ],
        'order_processing' => [
            'label' => 'Order Processing',
            'description' => 'When order status changes to processing',
            'category' => 'orders',
            'icon' => 'bi-gear',
            'color' => 'primary',
            'default_email' => false,
            'priority' => 'normal',
            'send_to_customer' => true,
        ],
        // 'order_packed' => [
        //     'label' => 'Order Packed',
        //     'description' => 'When order is packed and ready to ship',
        //     'category' => 'orders',
        //     'icon' => 'bi-box-seam',
        //     'color' => 'info',
        //     'default_email' => false,
        //     'priority' => 'normal',
        //     'send_to_customer' => true,
        // ],
        'order_shipped' => [
            'label' => 'Order Shipped',
            'description' => 'When order is marked as shipped',
            'category' => 'orders',
            'icon' => 'bi-truck',
            'color' => 'primary',
            'default_email' => true,
            'priority' => 'normal',
            'send_to_customer' => true,
        ],
        'order_delivered' => [
            'label' => 'Order Delivered',
            'description' => 'When order is successfully delivered',
            'category' => 'orders',
            'icon' => 'bi-house-check',
            'color' => 'success',
            'default_email' => false,
            'priority' => 'normal',
            'send_to_customer' => true,
        ],
        'order_cancelled' => [
            'label' => 'Order Cancelled',
            'description' => 'When an order is cancelled',
            'category' => 'orders',
            'icon' => 'bi-x-circle',
            'color' => 'danger',
            'default_email' => true,
            'priority' => 'high',
            'send_to_customer' => true,
        ],
        // 'order_refund_requested' => [
        //     'label' => 'Refund Requested',
        //     'description' => 'When a customer requests a refund',
        //     'category' => 'orders',
        //     'icon' => 'bi-arrow-counterclockwise',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'high',
        //     'send_to_customer' => true,
        // ],
        // 'order_refunded' => [
        //     'label' => 'Order Refunded',
        //     'description' => 'When a refund is processed',
        //     'category' => 'orders',
        //     'icon' => 'bi-cash-stack',
        //     'color' => 'info',
        //     'default_email' => true,
        //     'priority' => 'normal',
        //     'send_to_customer' => true,
        // ],
        // 'order_requires_action' => [
        //     'label' => 'Order Requires Action',
        //     'description' => 'When an order is stuck and needs attention',
        //     'category' => 'orders',
        //     'icon' => 'bi-exclamation-triangle',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        //     'send_to_customer' => true,
        // ],

        // ==================== INVENTORY NOTIFICATIONS ====================
        // 'stock_out' => [
        //     'label' => 'Product Out of Stock',
        //     'description' => 'When a product stock reaches zero',
        //     'category' => 'inventory',
        //     'icon' => 'bi-exclamation-triangle',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        // ],
        // 'stock_low' => [
        //     'label' => 'Low Stock Alert',
        //     'description' => 'When product stock falls below threshold',
        //     'category' => 'inventory',
        //     'icon' => 'bi-box-seam',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'high',
        // ],
        // 'stock_critical' => [
        //     'label' => 'Critical Stock Level',
        //     'description' => 'When product stock is critically low (50% of threshold)',
        //     'category' => 'inventory',
        //     'icon' => 'bi-exclamation-circle',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        // ],
        // 'warehouse_stock_critical' => [
        //     'label' => 'Warehouse Stock Critical',
        //     'description' => 'When warehouse has critically low stock across multiple products',
        //     'category' => 'inventory',
        //     'icon' => 'bi-building',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'high',
        // ],
        // 'negative_stock_detected' => [
        //     'label' => 'Negative Stock Detected',
        //     'description' => 'When product has negative stock (system error)',
        //     'category' => 'inventory',
        //     'icon' => 'bi-bug',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        // ],
        // 'stock_movement_large' => [
        //     'label' => 'Large Stock Movement',
        //     'description' => 'When unusually large stock movement is detected',
        //     'category' => 'inventory',
        //     'icon' => 'bi-arrow-left-right',
        //     'color' => 'warning',
        //     'default_email' => false,
        //     'priority' => 'normal',
        // ],

        // ==================== PRODUCT NOTIFICATIONS ====================
        // 'product_created' => [
        //     'label' => 'New Product Added',
        //     'description' => 'When a new product is added to the catalog',
        //     'category' => 'products',
        //     'icon' => 'bi-plus-circle',
        //     'color' => 'success',
        //     'default_email' => false,
        //     'priority' => 'low',
        // ],
        // 'product_updated' => [
        //     'label' => 'Product Updated',
        //     'description' => 'When product details are modified',
        //     'category' => 'products',
        //     'icon' => 'bi-pencil',
        //     'color' => 'info',
        //     'default_email' => false,
        //     'priority' => 'low',
        // ],
        // 'product_deleted' => [
        //     'label' => 'Product Deleted',
        //     'description' => 'When a product is deleted or deactivated',
        //     'category' => 'products',
        //     'icon' => 'bi-trash',
        //     'color' => 'danger',
        //     'default_email' => false,
        //     'priority' => 'normal',
        // ],
        // 'product_price_changed' => [
        //     'label' => 'Product Price Changed',
        //     'description' => 'When product price is updated',
        //     'category' => 'products',
        //     'icon' => 'bi-tag',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'normal',
        // ],

        // ==================== CUSTOMER NOTIFICATIONS ====================
        // 'customer_registered' => [
        //     'label' => 'New Customer Registration',
        //     'description' => 'When a new customer creates an account',
        //     'category' => 'customers',
        //     'icon' => 'bi-person-plus',
        //     'color' => 'success',
        //     'default_email' => false,
        //     'priority' => 'low',
        // ],
        // 'customer_high_value_order' => [
        //     'label' => 'High-Value Order (VIP)',
        //     'description' => 'When a customer places a high-value order',
        //     'category' => 'customers',
        //     'icon' => 'bi-star',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'high',
        // ],
        // 'customer_multiple_failed_orders' => [
        //     'label' => 'Multiple Failed Orders',
        //     'description' => 'When a customer has multiple failed order attempts',
        //     'category' => 'customers',
        //     'icon' => 'bi-exclamation-triangle',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'normal',
        // ],

        // ==================== PAYMENT NOTIFICATIONS ====================
        'payment_received' => [
            'label' => 'Payment Received',
            'description' => 'When a payment is successfully processed',
            'category' => 'payments',
            'icon' => 'bi-credit-card-check',
            'color' => 'success',
            'default_email' => false,
            'priority' => 'normal',
            'send_to_customer' => true,
        ],
        'payment_failed' => [
            'label' => 'Payment Failed',
            'description' => 'When payment processing fails',
            'category' => 'payments',
            'icon' => 'bi-x-circle',
            'color' => 'danger',
            'default_email' => true,
            'priority' => 'high',
        ],
        'payment_pending' => [
            'label' => 'Payment Pending Review',
            'description' => 'When payment requires manual review',
            'category' => 'payments',
            'icon' => 'bi-clock',
            'color' => 'warning',
            'default_email' => true,
            'priority' => 'high',
        ],
        // 'transaction_flagged' => [
        //     'label' => 'Transaction Flagged (Fraud)',
        //     'description' => 'When a transaction is flagged for potential fraud',
        //     'category' => 'payments',
        //     'icon' => 'bi-shield-exclamation',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        // ],
        'refund_processed' => [
            'label' => 'Refund Processed',
            'description' => 'When a refund is successfully processed',
            'category' => 'payments',
            'icon' => 'bi-arrow-counterclockwise',
            'color' => 'info',
            'default_email' => true,
            'priority' => 'normal',
        ],
        // 'high_value_transaction' => [
        //     'label' => 'High-Value Transaction',
        //     'description' => 'When a transaction exceeds high-value threshold',
        //     'category' => 'payments',
        //     'icon' => 'bi-cash-stack',
        //     'color' => 'warning',
        //     'default_email' => true,
        //     'priority' => 'high',
        // ],

        // ==================== SYSTEM NOTIFICATIONS ====================
        // 'daily_sales_report' => [
        //     'label' => 'Daily Sales Report',
        //     'description' => 'Daily summary of sales and orders',
        //     'category' => 'reports',
        //     'icon' => 'bi-graph-up',
        //     'color' => 'primary',
        //     'default_email' => true,
        //     'priority' => 'normal',
        // ],
        // 'weekly_inventory_report' => [
        //     'label' => 'Weekly Inventory Report',
        //     'description' => 'Weekly inventory status and alerts',
        //     'category' => 'reports',
        //     'icon' => 'bi-clipboard-data',
        //     'color' => 'info',
        //     'default_email' => true,
        //     'priority' => 'normal',
        // ],
        // 'system_error' => [
        //     'label' => 'Critical System Error',
        //     'description' => 'When a critical system error occurs',
        //     'category' => 'system',
        //     'icon' => 'bi-bug',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        // ],
        // 'backup_completed' => [
        //     'label' => 'Database Backup Completed',
        //     'description' => 'When database backup is successfully completed',
        //     'category' => 'system',
        //     'icon' => 'bi-database-check',
        //     'color' => 'success',
        //     'default_email' => false,
        //     'priority' => 'low',
        // ],
        // 'backup_failed' => [
        //     'label' => 'Database Backup Failed',
        //     'description' => 'When database backup fails',
        //     'category' => 'system',
        //     'icon' => 'bi-database-x',
        //     'color' => 'danger',
        //     'default_email' => true,
        //     'priority' => 'urgent',
        // ],
    ],

    /**
     * Notification Categories
     */
    'categories' => [
        'orders' => [
            'label' => 'Order Management',
            'icon' => 'bi-cart',
            'color' => 'primary',
        ],
        // 'inventory' => [
        //     'label' => 'Inventory & Stock',
        //     'icon' => 'bi-box-seam',
        //     'color' => 'warning',
        // ],
        'payments' => [
            'label' => 'Payments & Transactions',
            'icon' => 'bi-credit-card',
            'color' => 'success',
        ],
        // 'customers' => [
        //     'label' => 'Customer Management',
        //     'icon' => 'bi-people',
        //     'color' => 'info',
        // ],
        // 'products' => [
        //     'label' => 'Product Management',
        //     'icon' => 'bi-bag',
        //     'color' => 'secondary',
        // ],
        // 'reports' => [
        //     'label' => 'Reports & Analytics',
        //     'icon' => 'bi-graph-up',
        //     'color' => 'primary',
        // ],
        // 'system' => [
        //     'label' => 'System Alerts',
        //     'icon' => 'bi-gear',
        //     'color' => 'danger',
        // ],
    ],

    /**
     * Default Settings
     */
    'defaults' => [
        'in_app_enabled' => true,
        'email_enabled' => false,
        'sms_enabled' => false,
        'auto_expire_days' => 30, // Auto-delete notifications after 30 days
        'max_notifications_per_admin' => 100, // Keep only last 100 notifications
    ],

    /**
     * Email Settings
     */
    'email' => [
        'from_name' => env('MAIL_FROM_NAME', 'Coffee Store Admin'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'admin@coffeestore.com'),
        'max_retry_attempts' => 3,
        'retry_delay_minutes' => 15,
    ],

    /**
     * Thresholds
     */
    'thresholds' => [
        'high_value_order' => 5000, // Orders above this amount trigger VIP notification
        'high_value_transaction' => 1000, // Transactions above this amount
        'low_stock_percentage' => 50, // Alert when stock is 50% of threshold
    ],

    /**
     * Channels
     */
    'channels' => [
        'email' => true,
        'sms' => false, // Future implementation
        'push' => false, // Future implementation
    ],
];
