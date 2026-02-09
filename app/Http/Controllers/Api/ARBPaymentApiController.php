<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\ARBPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Exception;

class ARBPaymentApiController extends Controller
{
    protected $arbPaymentService;

    public function __construct(ARBPaymentService $arbPaymentService)
    {
        $this->arbPaymentService = $arbPaymentService;
    }

    /**
     * Initiate Payment - Returns payment URL in JSON
     * POST /api/arb/payment/initiate
     */
    public function initiatePayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'nullable|email',
                'customer_mobile' => 'nullable|string|max:20',
                'track_id' => 'nullable|string',
                'invoice_id' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            Log::info('API: ARB Payment Initiation', [
                'amount' => $validated['amount'],
                'customer' => $validated['customer_name']
            ]);

            if (empty($validated['track_id'])) {
                $validated['track_id'] = 'TRK-' . time() . '-' . uniqid();
            }

            // Generate payment token
            $paymentData = $this->arbPaymentService->generatePaymentToken($validated);

            // Call ARB Payment Gateway
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-FORWARDED-FOR' => $request->ip()
                ])
                ->timeout(30)
                ->post($paymentData['payment_url'], $paymentData['request_payload']);

            if (!$response->successful()) {
                throw new Exception('Payment gateway request failed: ' . $response->body());
            }

            // Parse response
            $parsedResponse = $this->arbPaymentService->parsePaymentResponse($response->json());

            if (!$parsedResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initiation failed',
                    'error' => $parsedResponse['error_text'] ?? 'Unknown error',
                    'error_code' => $parsedResponse['error'] ?? null
                ], 400);
            }

            // Save to database
            DB::table('arb_transactions')->insert([
                'payment_id' => $parsedResponse['payment_id'],
                'track_id' => $validated['track_id'],
                'amount' => $validated['amount'],
                'currency' => config('arb-payment.currency'),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_mobile' => $validated['customer_mobile'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('API: Payment Initiated Successfully', [
                'payment_id' => $parsedResponse['payment_id'],
                'track_id' => $validated['track_id']
            ]);

            // Return JSON response with payment URL
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_id' => $parsedResponse['payment_id'],
                    'payment_url' => $parsedResponse['payment_page_url'],
                    'track_id' => $validated['track_id'],
                    'amount' => $validated['amount'],
                    'currency' => 'SAR',
                    'status' => 'pending'
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('API: Payment Initiation Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Payment Callback - Returns JSON status
     * POST /api/arb/callback
     */
    public function handleCallback(Request $request)
    {
        try {
            Log::info('API: Callback Received', $request->all());

            $callbackResult = $this->arbPaymentService->handleCallback($request->all());

            $trackId = $callbackResult['data']['trackId'] ?? null;
            $paymentId = $callbackResult['data']['paymentId'] ?? $request->input('paymentId');
            $transactionId = $callbackResult['data']['transId'] ?? null;

            if ($trackId || $paymentId) {
                $updateData = [
                    'status' => $callbackResult['success'] ? 'completed' : 'failed',
                    'transaction_id' => $transactionId,
                    'response_data' => json_encode($callbackResult['data']),
                    'result_message' => $callbackResult['data']['result'] ?? $callbackResult['message'],
                    'updated_at' => now(),
                ];

                if ($callbackResult['success']) {
                    $updateData['paid_at'] = now();
                    $updateData['payment_method'] = $callbackResult['data']['cardType'] ?? null;
                    $updateData['auth_code'] = $callbackResult['data']['authCode'] ?? null;
                    $updateData['ref_number'] = $callbackResult['data']['ref'] ?? null;
                }

                $query = DB::table('arb_transactions');
                if ($trackId) {
                    $query->where('track_id', $trackId);
                } else {
                    $query->where('payment_id', $paymentId);
                }
                $query->update($updateData);
            }

            // Return JSON response
            return response()->json([
                'success' => $callbackResult['success'],
                'message' => $callbackResult['message'],
                'data' => [
                    'payment_id' => $paymentId,
                    'track_id' => $trackId,
                    'transaction_id' => $transactionId,
                    'status' => $callbackResult['success'] ? 'completed' : 'failed',
                    'result' => $callbackResult['data']['result'] ?? null,
                    'amount' => $callbackResult['data']['amt'] ?? null,
                    'card_type' => $callbackResult['data']['cardType'] ?? null,
                    'auth_code' => $callbackResult['data']['authCode'] ?? null,
                    'ref' => $callbackResult['data']['ref'] ?? null,
                ]
            ], $callbackResult['success'] ? 200 : 400);

        } catch (Exception $e) {
            Log::error('API: Callback Error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Payment Status by Track ID or Payment ID
     * GET /api/arb/payment/status/{id}
     */
    public function getPaymentStatus($id)
    {
        try {
            $transaction = DB::table('arb_transactions')
                ->where('track_id', $id)
                ->orWhere('payment_id', $id)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction found',
                'data' => [
                    'payment_id' => $transaction->payment_id,
                    'track_id' => $transaction->track_id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'customer_name' => $transaction->customer_name,
                    'customer_email' => $transaction->customer_email,
                    'payment_method' => $transaction->payment_method,
                    'auth_code' => $transaction->auth_code,
                    'ref_number' => $transaction->ref_number,
                    'result_message' => $transaction->result_message,
                    'paid_at' => $transaction->paid_at,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('API: Get Status Error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Payment (Double-check with ARB)
     * POST /api/arb/payment/verify
     */
    public function verifyPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'track_id' => 'required_without:payment_id|string',
                'payment_id' => 'required_without:track_id|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $trackId = $request->input('track_id');
            $paymentId = $request->input('payment_id');

            // Get from database
            $transaction = DB::table('arb_transactions')
                ->where(function($query) use ($trackId, $paymentId) {
                    if ($trackId) {
                        $query->where('track_id', $trackId);
                    } else {
                        $query->where('payment_id', $paymentId);
                    }
                })
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified',
                'data' => [
                    'payment_id' => $transaction->payment_id,
                    'track_id' => $transaction->track_id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'is_paid' => $transaction->status === 'completed',
                    'verified_at' => now()
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('API: Verify Payment Error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get All Transactions (with pagination)
     * GET /api/arb/transactions
     */
    public function getAllTransactions(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $status = $request->input('status');

            $query = DB::table('arb_transactions')
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $transactions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Transactions retrieved',
                'data' => $transactions->items(),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('API: Get Transactions Error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
