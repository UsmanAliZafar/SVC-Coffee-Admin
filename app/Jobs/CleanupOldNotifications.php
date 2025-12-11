<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = 300; // 5 minutes

    /**
     * Number of days to keep notifications
     */
    protected $daysToKeep = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $cutoffDate = Carbon::now()->subDays($this->daysToKeep);

            Log::info('🧹 Starting notification cleanup job', [
                'current_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                'keeping_last_days' => $this->daysToKeep,
            ]);

            // ============================================================
            // DELETE OLD NOTIFICATIONS
            // ============================================================
            $notificationsQuery = Notification::where('created_at', '<', $cutoffDate);

            // Get count before deletion for logging
            $totalToDelete = $notificationsQuery->count();

            if ($totalToDelete === 0) {
                Log::info('✅ No notifications to delete', [
                    'cutoff_date' => $cutoffDate->format('Y-m-d'),
                ]);
                return;
            }

            // Get statistics before deletion
            $readCount = (clone $notificationsQuery)->where('is_read', true)->count();
            $unreadCount = (clone $notificationsQuery)->where('is_read', false)->count();
            $byType = (clone $notificationsQuery)->select('notification_type', \DB::raw('count(*) as count'))
                ->groupBy('notification_type')
                ->pluck('count', 'notification_type')
                ->toArray();

            // Delete notifications
            $deletedCount = $notificationsQuery->delete();

            Log::info('✅ Notifications deleted successfully', [
                'total_deleted' => $deletedCount,
                'read_deleted' => $readCount,
                'unread_deleted' => $unreadCount,
                'by_type' => $byType,
                'cutoff_date' => $cutoffDate->format('Y-m-d'),
            ]);

            // ============================================================
            // DELETE OLD NOTIFICATION LOGS (OPTIONAL)
            // ============================================================
            $logsQuery = NotificationLog::where('created_at', '<', $cutoffDate);
            $logsToDelete = $logsQuery->count();

            if ($logsToDelete > 0) {
                $deletedLogs = $logsQuery->delete();

                Log::info('✅ Notification logs deleted successfully', [
                    'total_deleted' => $deletedLogs,
                    'cutoff_date' => $cutoffDate->format('Y-m-d'),
                ]);
            }

            // ============================================================
            // SUMMARY LOG
            // ============================================================
            Log::info('🎯 Notification cleanup completed', [
                'notifications_deleted' => $deletedCount,
                'logs_deleted' => $logsToDelete,
                'total_records_deleted' => $deletedCount + $logsToDelete,
                'execution_time' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Notification cleanup job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ Notification cleanup job failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
