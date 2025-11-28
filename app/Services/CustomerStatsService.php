<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Customer Statistics Service
 *
 * Handles all customer statistics calculations and updates
 */
class CustomerStatsService
{
    /**
     * Sync statistics for a single customer
     *
     * @param string|Customer $customer Customer ID or instance
     * @return array Statistics that were updated
     */
    public function syncCustomerStats($customer): array
    {
        try {
            // Get customer instance if ID was passed
            if (is_string($customer)) {
                $customer = Customer::findOrFail($customer);
            }

            // Get completed and delivered orders only
            $orders = $customer->orders()
                ->whereIn('status_key_code', ['ORDER_COMPLETED', 'ORDER_DELIVERED'])
                ->get();

            $totalOrders = $orders->count();
            $totalSpent = $orders->sum('total_amount');
            $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

            $firstOrder = $orders->sortBy('created_at')->first();
            $lastOrder = $orders->sortByDesc('created_at')->first();

            // Prepare update data
            $updateData = [
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'average_order_value' => $averageOrderValue,
                'first_order_at' => $firstOrder ? $firstOrder->created_at : null,
                'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
            ];

            // Set preferred currency from store if not already set
            if (empty($customer->preferred_currency)) {
                $updateData['preferred_currency'] = store_currency_code();
            }

            // Update customer
            $customer->update($updateData);

            Log::info('Customer stats synced successfully', [
                'customer_id' => $customer->id,
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
            ]);

            return $updateData;

        } catch (\Exception $e) {
            Log::error('Failed to sync customer stats', [
                'customer_id' => is_string($customer) ? $customer : $customer->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync statistics for multiple customers
     *
     * @param array $customerIds Array of customer IDs (empty = all customers)
     * @return array ['success' => count, 'failed' => count, 'errors' => []]
     */
    public function syncMultipleCustomers(array $customerIds = []): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        // Get customers
        if (empty($customerIds)) {
            $customers = Customer::all();
        } else {
            $customers = Customer::whereIn('id', $customerIds)->get();
        }

        foreach ($customers as $customer) {
            try {
                $this->syncCustomerStats($customer);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->getFullName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Update stats when an order status changes
     * Only syncs if order becomes completed or delivered
     *
     * @param Order $order
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public function handleOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        // Only sync if customer exists and order became completed/delivered
        if (!$order->customer_id) {
            return;
        }

        $triggerStatuses = ['ORDER_COMPLETED', 'ORDER_DELIVERED'];

        // Sync if order just became completed/delivered
        if (in_array($newStatus, $triggerStatuses) && !in_array($oldStatus, $triggerStatuses)) {
            $this->syncCustomerStats($order->customer_id);

            Log::info('Customer stats auto-synced after order status change', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }

        // Also sync if order was completed/delivered and is now cancelled
        if (in_array($oldStatus, $triggerStatuses) && $newStatus === 'ORDER_CANCELLED') {
            $this->syncCustomerStats($order->customer_id);

            Log::info('Customer stats auto-synced after order cancellation', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
            ]);
        }
    }

    /**
     * Sync customer stats after order creation
     *
     * @param Order $order
     * @return void
     */
    public function handleOrderCreated(Order $order): void
    {
        // Only sync if customer exists and order is completed/delivered
        if (!$order->customer_id) {
            return;
        }

        if (in_array($order->status_key_code, ['ORDER_COMPLETED', 'ORDER_DELIVERED'])) {
            $this->syncCustomerStats($order->customer_id);

            Log::info('Customer stats auto-synced after order creation', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
            ]);
        }
    }

    /**
     * Get customer statistics without updating database
     * Useful for preview/display purposes
     *
     * @param string|Customer $customer
     * @return array
     */
    public function calculateStats($customer): array
    {
        // Get customer instance if ID was passed
        if (is_string($customer)) {
            $customer = Customer::findOrFail($customer);
        }

        $orders = $customer->orders()
            ->whereIn('status_key_code', ['ORDER_COMPLETED', 'ORDER_DELIVERED'])
            ->get();

        $totalOrders = $orders->count();
        $totalSpent = $orders->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        $firstOrder = $orders->sortBy('created_at')->first();
        $lastOrder = $orders->sortByDesc('created_at')->first();

        return [
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => $averageOrderValue,
            'first_order_at' => $firstOrder ? $firstOrder->created_at : null,
            'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
            'formatted_total_spent' => store_currency_symbol() . ' ' . number_format($totalSpent, 2),
            'formatted_average' => store_currency_symbol() . ' ' . number_format($averageOrderValue, 2),
        ];
    }

    /**
     * Sync stats for all customers (use with caution on large datasets)
     * Better to use via job for large batches
     *
     * @param int $chunkSize Process customers in chunks
     * @return array
     */
    public function syncAllCustomers(int $chunkSize = 100): array
    {
        $totalSuccess = 0;
        $totalFailed = 0;
        $errors = [];

        Customer::chunk($chunkSize, function ($customers) use (&$totalSuccess, &$totalFailed, &$errors) {
            foreach ($customers as $customer) {
                try {
                    $this->syncCustomerStats($customer);
                    $totalSuccess++;
                } catch (\Exception $e) {
                    $totalFailed++;
                    $errors[] = [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->getFullName(),
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return [
            'success' => $totalSuccess,
            'failed' => $totalFailed,
            'errors' => $errors,
        ];
    }

    /**
     * Validate customer stats (check if they need syncing)
     *
     * @param Customer $customer
     * @return bool True if stats are accurate, false if need sync
     */
    public function validateStats(Customer $customer): bool
    {
        $calculated = $this->calculateStats($customer);

        // Compare with stored values
        return (
            $customer->total_orders == $calculated['total_orders'] &&
            abs($customer->total_spent - $calculated['total_spent']) < 0.01 &&
            abs($customer->average_order_value - $calculated['average_order_value']) < 0.01
        );
    }

    /**
     * Find customers with outdated statistics
     *
     * @param int $limit Maximum customers to return
     * @return \Illuminate\Support\Collection
     */
    public function findCustomersNeedingSync(int $limit = 100): \Illuminate\Support\Collection
    {
        return Customer::whereHas('orders', function ($query) {
            $query->whereIn('status_key_code', ['ORDER_COMPLETED', 'ORDER_DELIVERED'])
                  ->where('updated_at', '>', DB::raw('customers.updated_at'));
        })
        ->limit($limit)
        ->get();
    }
}
