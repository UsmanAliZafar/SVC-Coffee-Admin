<?php
// app/Services/NewsletterVerificationService.php

namespace App\Services;

use App\Jobs\SendNewsletterVerificationEmail;
use App\Models\Newsletter;
use Illuminate\Support\Str;

class NewsletterVerificationService
{
    /**
     * Create a new newsletter subscription and send verification email
     */
    public function createSubscription(string $email, ?string $name = null): Newsletter
    {
        // Check if email already exists
        $newsletter = Newsletter::where('email', $email)->first();

        if ($newsletter) {
            // If already verified and subscribed
            if ($newsletter->email_verified && $newsletter->is_subscribed) {
                throw new \Exception('This email is already subscribed to our newsletter.');
            }

            // If unverified, regenerate token and resend
            if (!$newsletter->email_verified) {
                $token = $newsletter->generateVerificationToken();
                $this->sendVerificationEmail($newsletter, $token);
                return $newsletter;
            }

            // If verified but unsubscribed, reactivate
            if ($newsletter->email_verified && !$newsletter->is_subscribed) {
                $newsletter->subscribe();
                return $newsletter;
            }
        }

        // Create new subscriber
        $newsletter = Newsletter::create([
            'email' => $email,
            'name' => $name,
            'is_subscribed' => false, // Will be activated after verification
            'email_verified' => false,
            'verification_token' => Str::random(64),
        ]);

        $this->sendVerificationEmail($newsletter, $newsletter->verification_token);

        return $newsletter;
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail(Newsletter $newsletter, string $token): void
    {
        SendNewsletterVerificationEmail::dispatch($newsletter, $token);
    }

    /**
     * Verify email using token
     */
    public function verifyEmail(string $token): Newsletter
    {
        $newsletter = Newsletter::where('verification_token', $token)->first();

        if (!$newsletter) {
            throw new \Exception('Invalid verification token.');
        }

        if ($newsletter->email_verified) {
            throw new \Exception('Email already verified.');
        }

        // Verify email and activate subscription
        $newsletter->update([
            'email_verified' => true,
            'email_verified_at' => now(),
            'verification_token' => null,
            'is_subscribed' => true,
            'subscribed_at' => now(),
        ]);

        return $newsletter;
    }

    /**
     * Resend verification email
     */
    public function resendVerification(string $email): Newsletter
    {
        $newsletter = Newsletter::where('email', $email)->first();

        if (!$newsletter) {
            throw new \Exception('Email not found.');
        }

        if ($newsletter->email_verified) {
            throw new \Exception('Email already verified.');
        }

        $token = $newsletter->generateVerificationToken();
        $this->sendVerificationEmail($newsletter, $token);

        return $newsletter;
    }
}
