<?php
// app/Jobs/SendNewsletterVerificationEmail.php

namespace App\Jobs;

use App\Mail\NewsletterVerificationMail;
use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Newsletter $newsletter;
    public string $token;

    /**
     * Create a new job instance.
     */
    public function __construct(Newsletter $newsletter, string $token)
    {
        $this->newsletter = $newsletter;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->newsletter->email)->send(
            new NewsletterVerificationMail($this->newsletter, $this->token)
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure or notify admins
        \Log::error('Newsletter verification email failed', [
            'newsletter_id' => $this->newsletter->id,
            'email' => $this->newsletter->email,
            'error' => $exception->getMessage()
        ]);
    }
}
