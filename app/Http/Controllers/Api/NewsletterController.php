<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
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

            // Check if email already exists
            $existingSubscriber = Newsletter::where('email', $request->email)->first();

            if ($existingSubscriber) {
                // If already subscribed and active
                if ($existingSubscriber->is_subscribed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email is already subscribed to our newsletter.',
                        'data' => [
                            'email' => $existingSubscriber->email,
                            'subscribed_at' => $existingSubscriber->subscribed_at,
                        ]
                    ], 409); // 409 Conflict
                }

                // If was unsubscribed, reactivate
                $existingSubscriber->update([
                    'name' => $request->name ?? $existingSubscriber->name,
                    'is_subscribed' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Welcome back! You have been successfully resubscribed to our newsletter.',
                    'data' => [
                        'id' => $existingSubscriber->id,
                        'email' => $existingSubscriber->email,
                        'name' => $existingSubscriber->name,
                        'subscribed_at' => $existingSubscriber->subscribed_at,
                    ]
                ], 200);
            }

            // Create new subscriber
            $newsletter = Newsletter::create([
                'email' => $request->email,
                'name' => $request->name,
                'is_subscribed' => true,
                'subscribed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You will now receive our latest updates and newsletters.',
                'data' => [
                    'id' => $newsletter->id,
                    'email' => $newsletter->email,
                    'name' => $newsletter->name,
                    'subscribed_at' => $newsletter->subscribed_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
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

            $newsletter->update([
                'is_subscribed' => false,
                'unsubscribed_at' => now(),
            ]);

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
