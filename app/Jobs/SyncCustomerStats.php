<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\CustomerStatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCustomerStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The customer ID or array of customer IDs to sync
     */
    public $customerIds;

    /**
     * Whether to sync all customers
     */
    public bool $syncAll = false;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($customerIds = null, bool $syncAll = false)
    {
        // Handle different parameter types
        if ($syncAll) {
            $this->syncAll = true;
            $this->customerIds = [];
        } elseif (is_array($customerIds)) {
            $this->customerIds = $customerIds;
        } elseif ($customerIds !== null) {
            $this->customerIds = [$customerIds];
        } else {
            $this->customerIds = [];
        }

        // Set queue specific to customer stats
        $this->onQueue('customer-stats');
    }

    /**
     * Execute the job.
     */
    public function handle(CustomerStatsService $statsService): void
    {
        try {
            if ($this->syncAll) {
                Log::info('Starting bulk customer stats sync for all customers');

                $result = $statsService->syncAllCustomers(100); // Process in chunks of 100

                Log::info('Bulk customer stats sync completed', [
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                    'total' => $result['success'] + $result['failed'],
                ]);

                // Log errors if any
                if (!empty($result['errors'])) {
                    Log::warning('Some customer stats syncs failed', [
                        'error_count' => count($result['errors']),
                        'sample_errors' => array_slice($result['errors'], 0, 5),
                    ]);
                }

            } elseif (!empty($this->customerIds)) {
                Log::info('Starting customer stats sync for specific customers', [
                    'customer_count' => count($this->customerIds),
                    'customer_ids' => $this->customerIds,
                ]);

                $result = $statsService->syncMultipleCustomers($this->customerIds);

                Log::info('Customer stats sync completed', [
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                    'total' => count($this->customerIds),
                ]);

                // Log detailed errors
                if (!empty($result['errors'])) {
                    Log::error('Customer stats sync errors', [
                        'errors' => $result['errors'],
                    ]);
                }

            } else {
                Log::warning('SyncCustomerStats job called with no customer IDs and syncAll=false');
            }

        } catch (\Exception $e) {
            Log::error('SyncCustomerStats job failed', [
                'customer_ids' => $this->customerIds,
                'sync_all' => $this->syncAll,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to allow retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error('SyncCustomerStats job failed after all attempts', [
            'customer_ids' => $this->customerIds,
            'sync_all' => $this->syncAll,
            'error' => $exception->getMessage(),
            'job_attempts' => $this->attempts(),
        ]);

        // You could also send a notification here
        // Notification::send(/* admin users */, new SyncStatsFailed($this->customerIds, $exception));
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 600]; // 1 min, 5 mins, 10 mins
    }
}
