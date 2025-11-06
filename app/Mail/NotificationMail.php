<?php

namespace App\Mail;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\AdminUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The notification instance.
     */
    public $notification;

    /**
     * The admin user instance.
     */
    public $admin;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification, AdminUser $admin)
    {
        $this->notification = $notification;
        $this->admin = $admin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $config = config("notifications.types.{$this->notification->notification_type}");
        $label = $config['label'] ?? 'Notification';

        return new Envelope(
            subject: "[Coffee Store Admin] {$label}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Check if custom template exists
        $template = NotificationTemplate::getTemplate(
            $this->notification->notification_type,
            'email'
        );

        if ($template) {
            // Use custom template
            $rendered = $template->replaceVariables($this->getTemplateData());

            return new Content(
                view: 'emails.notifications.custom',
                with: [
                    'notification' => $this->notification,
                    'admin' => $this->admin,
                    'customBody' => $rendered['body'],
                ],
            );
        }

        // Use default template based on notification type
        $viewName = $this->getDefaultViewName();

        return new Content(
            view: $viewName,
            with: [
                'notification' => $this->notification,
                'admin' => $this->admin,
                'data' => $this->notification->data,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get template data for variable replacement
     */
    private function getTemplateData(): array
    {
        $data = $this->notification->data;
        $config = config("notifications.types.{$this->notification->notification_type}");

        return array_merge([
            'admin_name' => $this->admin->name,
            'admin_email' => $this->admin->email,
            'notification_title' => $this->notification->title,
            'notification_message' => $this->notification->message,
            'action_url' => $this->notification->action_url ?? url('/admin'),
            'store_name' => config('app.name'),
            'store_url' => config('app.url'),
            'notification_date' => now()->format('F d, Y'),
            'notification_time' => now()->format('h:i A'),
        ], $data);
    }

    /**
     * Get default view name based on notification type
     */
    private function getDefaultViewName(): string
    {
        $type = $this->notification->notification_type;

        // Map specific notification types to views
        $viewMap = [
            // Orders
            'order_created' => 'emails.notifications.order-created',
            'order_confirmed' => 'emails.notifications.order-confirmed',
            'order_shipped' => 'emails.notifications.order-shipped',
            'order_cancelled' => 'emails.notifications.order-cancelled',
            'order_refunded' => 'emails.notifications.order-refunded',

            // Inventory
            'stock_out' => 'emails.notifications.stock-out',
            'stock_low' => 'emails.notifications.stock-low',
            'stock_critical' => 'emails.notifications.stock-critical',

            // Payments
            'payment_failed' => 'emails.notifications.payment-failed',
            'transaction_flagged' => 'emails.notifications.transaction-flagged',

            // Reports
            'daily_sales_report' => 'emails.notifications.daily-sales-report',
            'weekly_inventory_report' => 'emails.notifications.weekly-inventory-report',

            // System
            'system_error' => 'emails.notifications.system-error',
        ];

        // Return specific view if mapped, otherwise use generic template
        return $viewMap[$type] ?? 'emails.notifications.generic';
    }
}
