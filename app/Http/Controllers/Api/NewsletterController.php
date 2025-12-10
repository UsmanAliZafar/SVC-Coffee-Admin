<?php
// app/Http/Controllers/Api/NewsletterController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Services\NewsletterVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    protected NewsletterVerificationService $verificationService;

    public function __construct(NewsletterVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Subscribe to newsletter
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newsletter = $this->verificationService->createSubscription(
                $request->email,
                $request->name
            );

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! Please check your email to verify your subscription.',
                'data' => [
                    'id' => $newsletter->id,
                    'email' => $newsletter->email,
                    'name' => $newsletter->name,
                    'email_verified' => $newsletter->email_verified,
                    'created_at' => $newsletter->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Resend verification email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerification(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newsletter = $this->verificationService->resendVerification($request->email);

            return response()->json([
                'success' => true,
                'message' => 'Verification email has been resent. Please check your inbox.',
                'data' => [
                    'email' => $newsletter->email,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unsubscribe from newsletter
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newsletter = Newsletter::where('email', $request->email)->first();

            if (!$newsletter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address not found in our newsletter list.',
                ], 404);
            }

            if (!$newsletter->is_subscribed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already unsubscribed from our newsletter.',
                    'data' => [
                        'email' => $newsletter->email,
                        'unsubscribed_at' => $newsletter->unsubscribed_at,
                    ]
                ], 409);
            }

            $newsletter->unsubscribe();

            return response()->json([
                'success' => true,
                'message' => 'You have been successfully unsubscribed from our newsletter. We\'re sorry to see you go!',
                'data' => [
                    'email' => $newsletter->email,
                    'unsubscribed_at' => $newsletter->unsubscribed_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
