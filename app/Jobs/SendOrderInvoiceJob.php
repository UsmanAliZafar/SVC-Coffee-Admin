<?php

namespace App\Jobs;

use App\Mail\Customer\SendInvoiceMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(
        public Order  $order,
        public string $toEmail,
        public string $subject,
        public string $message,
    ) {}

    public function handle(): void
    {
        Mail::to($this->toEmail)
            ->send(new SendInvoiceMail($this->order, $this->subject, $this->message));

        Log::info('📧 Invoice email sent', [
            'order'   => $this->order->order_number,
            'to'      => $this->toEmail,
            'subject' => $this->subject,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('❌ Invoice email failed', [
            'order' => $this->order->order_number,
            'to'    => $this->toEmail,
            'error' => $e->getMessage(),
        ]);
    }
}
