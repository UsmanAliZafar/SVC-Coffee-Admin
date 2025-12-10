<?php
// app/Mail/NewsletterVerificationMail.php

namespace App\Mail;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Newsletter $newsletter;
    public string $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Newsletter $newsletter, string $token)
    {
        $this->newsletter = $newsletter;
        $this->verificationUrl = url("/newsletter/verify/{$token}");
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Newsletter Subscription',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-verification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
