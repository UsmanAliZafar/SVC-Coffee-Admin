<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\AdminUser;
use App\Mail\NotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The notification instance.
     */
    public $notification;

    /**
     * The admin user instance.
     */
    public $admin;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification, AdminUser $admin)
    {
        $this->notification = $notification;
        $this->admin = $admin;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get notification setting for custom email or use admin's default email
            $setting = NotificationSetting::where('admin_user_id', $this->admin->id)
                ->where('notification_type', $this->notification->notification_type)
                ->first();

            $recipientEmail = $setting && $setting->email_address
                ? $setting->email_address
                : $this->admin->email;

            if (!$recipientEmail) {
                Log::warning("No email address found for admin {$this->admin->id}");
                return;
            }

            // Create notification log entry
            $log = NotificationLog::create([
                'notification_id' => $this->notification->id,
                'admin_user_id' => $this->admin->id,
                'channel' => 'email',
                'recipient_email' => $recipientEmail,
                'subject' => $this->getEmailSubject(),
                'status' => 'pending',
            ]);

            // Send email
            Mail::to($recipientEmail)->send(new NotificationMail($this->notification, $this->admin));

            // Mark as sent
            $log->markAsSent();

            Log::info("Notification email sent successfully", [
                'notification_id' => $this->notification->id,
                'admin_id' => $this->admin->id,
                'email' => $recipientEmail,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send notification email", [
                'notification_id' => $this->notification->id,
                'admin_id' => $this->admin->id,
                'error' => $e->getMessage(),
            ]);

            // Update log with failure
            if (isset($log)) {
                $log->markAsFailed($e->getMessage());

                // Schedule retry if attempts remaining
                if ($this->attempts() < $this->tries) {
                    $log->scheduleRetry(config('notifications.email.retry_delay_minutes', 15));
                }
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Notification email job failed permanently", [
            'notification_id' => $this->notification->id,
            'admin_id' => $this->admin->id,
            'error' => $exception->getMessage(),
        ]);

        // Find the log and mark as failed
        $log = NotificationLog::where('notification_id', $this->notification->id)
            ->where('admin_user_id', $this->admin->id)
            ->where('channel', 'email')
            ->latest()
            ->first();

        if ($log) {
            $log->markAsFailed($exception->getMessage());
        }
    }

    /**
     * Get email subject based on notification type
     */
    private function getEmailSubject(): string
    {
        $config = config("notifications.types.{$this->notification->notification_type}");
        $label = $config['label'] ?? 'Notification';

        return "[Coffee Store Admin] {$label}";
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(24);
    }
}
