<?php
// app/Http/Controllers/Web/NewsletterVerificationController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NewsletterVerificationService;
use Illuminate\Http\Request;

class NewsletterVerificationController extends Controller
{
    protected NewsletterVerificationService $verificationService;

    public function __construct(NewsletterVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Verify email address
     */
    public function verify(string $token)
    {
        try {
            $newsletter = $this->verificationService->verifyEmail($token);

            return view('newsletter.verified', [
                'success' => true,
                'newsletter' => $newsletter,
                'message' => 'Your email has been successfully verified! You are now subscribed to our newsletter.'
            ]);

        } catch (\Exception $e) {
            return view('newsletter.verified', [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
