<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\CustomerStatsService;
use App\Jobs\SyncCustomerStats;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected CustomerStatsService $statsService;

    public function __construct(CustomerStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Only process if order has a customer and is in a completed state
        if ($order->customer_id && in_array($order->status_key_code, ['ORDER_COMPLETED', 'ORDER_DELIVERED'])) {
            // Queue the sync job for better performance
            SyncCustomerStats::dispatch($order->customer_id)
                ->onQueue('customer-stats')
                ->delay(now()->addSeconds(5)); // Small delay to ensure order is fully saved

            Log::info('Customer stats sync queued after order creation', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'status' => $order->status_key_code,
            ]);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status changed and it's relevant for customer stats
        if ($order->isDirty('status_key_code')) {
            $oldStatus = $order->getOriginal('status_key_code');
            $newStatus = $order->status_key_code;

            // Handle status changes that affect customer statistics
            $this->statsService->handleOrderStatusChange($order, $oldStatus, $newStatus);
        }

        // Also sync if payment status changes to paid and order is completed/delivered
        if ($order->isDirty('payment_status_key_code') &&
            $order->payment_status_key_code === 'PAYMENT_PAID' &&
            in_array($order->status_key_code, ['ORDER_COMPLETED', 'ORDER_DELIVERED']) &&
            $order->customer_id) {

            SyncCustomerStats::dispatch($order->customer_id)
                ->onQueue('customer-stats');

            Log::info('Customer stats sync queued after payment status change', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'payment_status' => $order->payment_status_key_code,
            ]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // If a completed/delivered order is deleted, sync customer stats
        if ($order->customer_id && in_array($order->status_key_code, ['ORDER_COMPLETED', 'ORDER_DELIVERED'])) {
            SyncCustomerStats::dispatch($order->customer_id)
                ->onQueue('customer-stats');

            Log::info('Customer stats sync queued after order deletion', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
            ]);
        }
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        // Same logic as soft delete
        $this->deleted($order);
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        // If a completed/delivered order is restored, sync customer stats
        if ($order->customer_id && in_array($order->status_key_code, ['ORDER_COMPLETED', 'ORDER_DELIVERED'])) {
            SyncCustomerStats::dispatch($order->customer_id)
                ->onQueue('customer-stats');

            Log::info('Customer stats sync queued after order restoration', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
            ]);
        }
    }
}
