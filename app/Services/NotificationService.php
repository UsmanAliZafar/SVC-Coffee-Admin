<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\AdminUser;
use App\Jobs\SendNotificationEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;

class NotificationService
{
    /**
     * Send notification to admin(s)
     *
     * Supports independent channel control:
     * - In-App only: is_enabled=true, send_email=false
     * - Email only: is_enabled=false, send_email=true
     * - Both: is_enabled=true, send_email=true
     *
     * @param string $notificationType e.g., 'order_created', 'stock_low'
     * @param array $data Related data (order_id, product_id, etc.)
     * @param mixed $recipients null = all eligible admins, UUID = specific admin, array = multiple admins
     * @return void
     */
    public function notify(
        string $notificationType,
        array $data = [],
        $recipients = null
    ): void {
        // Get notification configuration
        $config = config("notifications.types.{$notificationType}");

        if (!$config) {
            Log::error("Unknown notification type: {$notificationType}");
            return;
        }

        // ✅ Get recipients for BOTH channels separately
        $inAppRecipients = $this->getRecipientsForInApp($recipients, $notificationType);
        $emailRecipients = $this->getRecipientsForEmail($recipients, $notificationType);

        // ✅ Merge to get ALL unique admins who want EITHER in-app OR email
        $allRecipientIds = $inAppRecipients->pluck('id')
            ->merge($emailRecipients->pluck('id'))
            ->unique();

        if ($allRecipientIds->isEmpty()) {
            Log::warning("No recipients found for notification: {$notificationType}");
            return;
        }

        // Get all unique recipient admins
        $allRecipients = AdminUser::whereIn('id', $allRecipientIds)->active()->get();

        Log::info("Processing notification", [
            'type' => $notificationType,
            'total_recipients' => $allRecipients->count(),
            'in_app_count' => $inAppRecipients->count(),
            'email_count' => $emailRecipients->count(),
        ]);

        // Send notification to each admin based on their preferences
        foreach ($allRecipients as $admin) {
            try {
                $notification = null;

                // ✅ CREATE IN-APP NOTIFICATION (if admin wants it)
                if ($inAppRecipients->contains('id', $admin->id)) {
                    $notification = $this->createNotification($admin, $notificationType, $data, $config);

                    Log::info("✅ In-app notification created", [
                        'notification_id' => $notification->id,
                        'admin_id' => $admin->id,
                        'admin_name' => $admin->name,
                        'type' => $notificationType,
                    ]);
                }

                // ✅ SEND EMAIL (if admin wants it)
                if ($emailRecipients->contains('id', $admin->id)) {
                    // Get their email settings
                    $setting = NotificationSetting::where('admin_user_id', $admin->id)
                        ->where('notification_type', $notificationType)
                        ->first();

                    if ($setting && $setting->send_email) {
                        // ✅ Create notification if not already created (for email-only scenario)
                        if (!$notification) {
                            $notification = $this->createNotification($admin, $notificationType, $data, $config);

                            Log::info("📧 Notification created for email-only", [
                                'notification_id' => $notification->id,
                                'admin_id' => $admin->id,
                                'type' => $notificationType,
                            ]);
                        }

                        // Dispatch email job (queued)
                        SendNotificationEmail::dispatch($notification, $admin);

                        Log::info("✅ Email notification queued", [
                            'notification_id' => $notification->id,
                            'admin_id' => $admin->id,
                            'admin_name' => $admin->name,
                            'email' => $setting->getEmailAddress(),
                            'type' => $notificationType,
                        ]);
                    }
                }

                // ✅ Log what was sent
                $channels = [];
                if ($inAppRecipients->contains('id', $admin->id)) $channels[] = 'in-app';
                if ($emailRecipients->contains('id', $admin->id)) $channels[] = 'email';

                Log::info("Notification sent via: " . implode(' + ', $channels), [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'type' => $notificationType,
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to send notification to admin {$admin->id}: " . $e->getMessage(), [
                    'admin_name' => $admin->name ?? 'Unknown',
                    'type' => $notificationType,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Create in-app notification
     */
    private function createNotification($admin, $type, $data, $config): Notification
    {
        return Notification::create([
            'admin_user_id' => $admin->id,
            'type' => $config['category'],
            'notification_type' => $type,
            'title' => $config['label'],
            'message' => $this->buildMessage($type, $data),
            'data' => $data,
            'icon' => $config['icon'],
            'color' => $config['color'],
            'action_url' => $this->buildActionUrl($type, $data),
            'priority' => $config['priority'],
            'expires_at' => now()->addDays(config('notifications.defaults.auto_expire_days', 30)),
        ]);
    }

    /**
     * Get recipients for IN-APP notifications
     * Returns admins who have is_enabled = true (or all active admins if no settings exist)
     */
    private function getRecipientsForInApp($recipients, $notificationType)
    {
        if (is_null($recipients)) {
            // Get admins who want in-app notifications for this type
            $adminIdsWithSettings = NotificationSetting::where('notification_type', $notificationType)
                ->where('is_enabled', true)
                ->pluck('admin_user_id');

            // If no explicit settings, get all active admins (default behavior)
            if ($adminIdsWithSettings->isEmpty()) {
                return AdminUser::active()->get();
            }

            return AdminUser::whereIn('id', $adminIdsWithSettings)->active()->get();
        }

        if (is_array($recipients)) {
            return AdminUser::whereIn('id', $recipients)->active()->get();
        }

        return AdminUser::where('id', $recipients)->active()->get();
    }

    /**
     * Get recipients for EMAIL notifications
     * Returns ONLY admins who explicitly enabled email with valid email address
     */
    private function getRecipientsForEmail($recipients, $notificationType)
    {
        if (is_null($recipients)) {
            // ONLY get admins who explicitly configured email for this notification
            $adminIdsWithSettings = NotificationSetting::where('notification_type', $notificationType)
                ->where('send_email', true)
                ->whereNotNull('email_address')
                ->where('email_address', '!=', '')
                ->pluck('admin_user_id');

            if ($adminIdsWithSettings->isEmpty()) {
                return collect([]);
            }

            return AdminUser::whereIn('id', $adminIdsWithSettings)->active()->get();
        }

        if (is_array($recipients)) {
            return AdminUser::whereIn('id', $recipients)->active()->get();
        }

        return AdminUser::where('id', $recipients)->active()->get();
    }

    /**
     * Build notification message based on type and data
     */
    private function buildMessage(string $type, array $data): string
    {
        return match($type) {
            // ORDERS
            'order_created' => "New order #{$data['order_number']} has been placed" .
                (isset($data['total_amount']) ? " for {$data['total_amount']}" : ""),
            'order_confirmed' => "Order #{$data['order_number']} has been confirmed",
            'order_processing' => "Order #{$data['order_number']} is now being processed",
            'order_packed' => "Order #{$data['order_number']} has been packed and is ready to ship",
            'order_shipped' => "Order #{$data['order_number']} has been shipped" .
                (isset($data['tracking_number']) ? " (Tracking: {$data['tracking_number']})" : ""),
            'order_delivered' => "Order #{$data['order_number']} has been delivered successfully",
            'order_cancelled' => "Order #{$data['order_number']} has been cancelled" .
                (isset($data['reason']) ? " - Reason: {$data['reason']}" : ""),
            'order_refund_requested' => "Refund requested for order #{$data['order_number']}" .
                (isset($data['amount']) ? " - Amount: {$data['amount']}" : ""),
            'order_refunded' => "Order #{$data['order_number']} has been refunded" .
                (isset($data['amount']) ? " - Amount: {$data['amount']}" : ""),
            'order_requires_action' => "Order #{$data['order_number']} requires your attention",

            // INVENTORY
            'stock_out' => "Product '{$data['product_name']}' is out of stock!",
            'stock_low' => "Product '{$data['product_name']}' is low on stock. Current: {$data['current_stock']}, Threshold: {$data['threshold']}",
            'stock_critical' => "CRITICAL: Product '{$data['product_name']}' has only {$data['current_stock']} units left",
            'warehouse_stock_critical' => "Warehouse '{$data['warehouse_name']}' has critical stock levels across {$data['product_count']} products",
            'negative_stock_detected' => "ERROR: Product '{$data['product_name']}' has negative stock ({$data['current_stock']})",
            'stock_movement_large' => "Large stock movement detected: {$data['quantity']} units of '{$data['product_name']}'",

            // PRODUCTS
            'product_created' => "New product '{$data['product_name']}' has been added to the catalog",
            'product_updated' => "Product '{$data['product_name']}' has been updated",
            'product_deleted' => "Product '{$data['product_name']}' has been deleted",
            'product_price_changed' => "Price changed for '{$data['product_name']}': {$data['old_price']} → {$data['new_price']}",

            // CUSTOMERS
            'customer_registered' => "New customer registered: {$data['customer_name']} ({$data['customer_email']})",
            'customer_high_value_order' => "VIP Alert: {$data['customer_name']} placed a high-value order of {$data['total_amount']}",
            'customer_multiple_failed_orders' => "Customer {$data['customer_name']} has {$data['failed_count']} failed order attempts",

            // PAYMENTS
            'payment_received' => "Payment of {$data['amount']} received for order #{$data['order_number']}",
            'payment_failed' => "Payment failed for order #{$data['order_number']}" .
                (isset($data['reason']) ? " - Reason: {$data['reason']}" : ""),
            'payment_pending' => "Payment pending manual review for order #{$data['order_number']}",
            'transaction_flagged' => "FRAUD ALERT: Transaction for order #{$data['order_number']} has been flagged",
            'refund_processed' => "Refund of {$data['amount']} processed for order #{$data['order_number']}",
            'high_value_transaction' => "High-value transaction alert: {$data['amount']} for order #{$data['order_number']}",

            // SYSTEM
            'daily_sales_report' => "Daily sales report is ready: {$data['total_orders']} orders, {$data['total_revenue']} revenue",
            'weekly_inventory_report' => "Weekly inventory report: {$data['low_stock_count']} low stock items, {$data['out_of_stock_count']} out of stock",
            'system_error' => "CRITICAL SYSTEM ERROR: {$data['error_message']}",
            'backup_completed' => "Database backup completed successfully at {$data['backup_time']}",
            'backup_failed' => "Database backup failed: {$data['error_message']}",

            default => 'You have a new notification',
        };
    }

    /**
     * Build action URL based on notification type and data
     */
    private function buildActionUrl(string $type, array $data): ?string
    {
        try {
            return match($type) {
                // Orders
                'order_created', 'order_confirmed', 'order_processing', 'order_packed',
                'order_shipped', 'order_delivered', 'order_cancelled', 'order_refund_requested',
                'order_refunded', 'order_requires_action', 'payment_received', 'payment_failed',
                'payment_pending', 'high_value_transaction'
                    => route('admin.orders.show', $data['order_id'] ?? '#'),

                // Inventory & Products
                'stock_out', 'stock_low', 'stock_critical', 'negative_stock_detected',
                'stock_movement_large', 'product_created', 'product_updated', 'product_deleted',
                'product_price_changed'
                    => route('admin.products.edit', $data['product_id'] ?? '#'),

                // Warehouse
                'warehouse_stock_critical'
                    => route('admin.warehouses.show', $data['warehouse_id'] ?? '#'),

                // Customers
                'customer_registered', 'customer_high_value_order', 'customer_multiple_failed_orders'
                    => route('admin.customers.show', $data['customer_id'] ?? '#'),

                // Transactions
                'transaction_flagged', 'refund_processed'
                    => isset($data['transaction_id'])
                        ? route('admin.transactions.show', $data['transaction_id'])
                        : route('admin.transactions.index'),

                // Reports
                'daily_sales_report', 'weekly_inventory_report'
                    => route('admin.reports.index'),

                // System
                'system_error', 'backup_completed', 'backup_failed'
                    => route('admin.settings.index'),

                default => null,
            };
        } catch (\Exception $e) {
            Log::warning("Could not build action URL for notification type {$type}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Batch notify multiple admins
     */
    public function notifyMultiple(array $notifications): void
    {
        foreach ($notifications as $notification) {
            $this->notify(
                $notification['type'],
                $notification['data'] ?? [],
                $notification['recipients'] ?? null
            );
        }
    }

    /**
     * Clean up old expired notifications
     */
    public function cleanupExpired(): int
    {
        return Notification::expired()->delete();
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(string $adminId): array
    {
        return Notification::getStatsForAdmin($adminId);
    }

    /**
     * Send notification to customer
     *
     * @param string $notificationType
     * @param Order $order
     * @param array $additionalData
     * @return void
     */
    public function notifyCustomer(
        string $notificationType,
        Order $order,
        array $additionalData = []
    ): void {
        // Get notification configuration
        $config = config("notifications.types.{$notificationType}");

        if (!$config || !($config['send_to_customer'] ?? false)) {
            return; // This notification type doesn't go to customers
        }

        try {
            $customerEmail = $order->customer
                ? $order->customer->email
                : $order->guest_email;

            if (!$customerEmail) {
                return;
            }

            // Send customer email
            $mailClass = $this->getCustomerMailClass($notificationType);

            if ($mailClass) {
                Mail::to($customerEmail)->send(new $mailClass($order, $additionalData));

                Log::info("Customer notification sent", [
                    'order_id' => $order->id,
                    'notification_type' => $notificationType,
                    'customer_email' => $customerEmail,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to send customer notification: " . $e->getMessage());
        }
    }

    /**
     * Get customer mail class
     */
    private function getCustomerMailClass(string $notificationType): ?string
    {
        return match($notificationType) {
            'order_confirmed' => \App\Mail\Customer\OrderConfirmedMail::class,
            'order_shipped' => \App\Mail\Customer\OrderShippedMail::class,
            'order_delivered' => \App\Mail\Customer\OrderDeliveredMail::class,
            'order_cancelled' => \App\Mail\Customer\OrderCancelledMail::class,
            'order_refunded' => \App\Mail\Customer\OrderRefundedMail::class,
            'payment_received' => \App\Mail\Customer\PaymentReceivedMail::class,
            default => null,
        };
    }
}
