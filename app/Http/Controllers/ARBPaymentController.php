<?php

namespace App\Http\Controllers;

use App\Services\Payment\ARBPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ARBPaymentController extends Controller
{
    protected $arbPaymentService;

    public function __construct(ARBPaymentService $arbPaymentService)
    {
        $this->arbPaymentService = $arbPaymentService;
    }

    public function showPaymentForm(Request $request)
    {
        return view('payment.form', [
            'amount' => $request->query('amount'),
            'invoice_id' => $request->query('invoice_id'),
            'customer_name' => $request->query('customer_name'),
            'customer_email' => $request->query('customer_email'),
        ]);
    }

    public function initiatePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'nullable|email',
                'customer_mobile' => 'nullable|string|max:20',
                'track_id' => 'nullable|string',
                'invoice_id' => 'nullable|string',
            ]);

            Log::info('=== ARB Payment Initiation Started ===', $validated);

            if (empty($validated['track_id'])) {
                $validated['track_id'] = 'TRK-' . time() . '-' . uniqid();
            }

            $response = $this->arbPaymentService->generatePaymentToken($validated);

            Log::info('ARB Payment Data Generated', [
                'track_id' => $validated['track_id']
            ]);

            DB::table('arb_transactions')->insert([
                'payment_id' => $validated['track_id'],
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

            // ==========================================
            // POSTMAN TESTING DATA - LOG THIS
            // ==========================================
            $postmanJson = [
                'id' => config('arb-payment.test.tranportal_id'),
                'trandata' => $response['trandata'],
                'responseURL' => config('arb-payment.response_url'),
                'errorURL' => config('arb-payment.error_url')
            ];

            Log::info('==========================================');
            Log::info('📋 COPY THIS JSON FOR POSTMAN:');
            Log::info('==========================================');
            Log::info('URL: https://securepayments.neoleap.com.sa/pg/payment/hosted.htm');
            Log::info('Method: POST');
            Log::info('Content-Type: application/json');
            Log::info('------------------------------------------');
            Log::info('Body (raw JSON):');
            Log::info(json_encode($postmanJson, JSON_PRETTY_PRINT));
            Log::info('==========================================');

            // Pass individual parameters to view
            return view('payment.redirect', [
                'payment_url' => config('arb-payment.test.payment_url'),
                'trandata' => $response['trandata'],
                'response_url' => config('arb-payment.response_url'),
                'error_url' => config('arb-payment.error_url'),
                'tranportal_id' => config('arb-payment.test.tranportal_id')
            ]);

        } catch (Exception $e) {
            Log::error('=== ARB Payment Initiation Error ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            Log::info('=== ARB Callback Received ===', $request->all());

            $callbackResult = $this->arbPaymentService->handleCallback($request->all());

            $trackId = $callbackResult['data']['trackid'] ?? null;
            $paymentId = $callbackResult['data']['paymentid'] ?? $request->input('paymentid');
            $transactionId = $callbackResult['data']['tranid'] ?? null;

            if ($trackId || $paymentId) {
                $updateData = [
                    'status' => $callbackResult['success'] ? 'completed' : 'failed',
                    'transaction_id' => $transactionId,
                    'response_data' => json_encode($callbackResult['data']),
                    'result_message' => $callbackResult['message'],
                    'updated_at' => now(),
                ];

                if ($callbackResult['success']) {
                    $updateData['paid_at'] = now();
                    $updateData['payment_method'] = $callbackResult['data']['paymentMethod'] ?? null;
                    $updateData['auth_code'] = $callbackResult['data']['auth'] ?? null;
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

            if ($callbackResult['success']) {
                return redirect()->route('arb.payment.success')
                    ->with('success', 'Payment completed successfully')
                    ->with('transaction_data', $callbackResult['data']);
            } else {
                return redirect()->route('arb.payment.failed')
                    ->with('error', $callbackResult['message'])
                    ->with('transaction_data', $callbackResult['data']);
            }

        } catch (Exception $e) {
            Log::error('ARB Callback Handling Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('arb.payment.error.page')
                ->with('error', 'An error occurred while processing payment response');
        }
    }

    public function handleError(Request $request)
    {
        try {
            Log::info('=== ARB Error Handler Called ===', $request->all());

            $errorCode = $request->input('Error');
            $errorText = $request->input('ErrorText');
            $paymentId = $request->input('paymentid');
            $trackId = $request->input('trackid');

            if ($trackId || $paymentId) {
                $updateData = [
                    'status' => 'error',
                    'error_code' => $errorCode,
                    'result_message' => $errorText,
                    'updated_at' => now(),
                ];

                $query = DB::table('arb_transactions');

                if ($trackId) {
                    $query->where('track_id', $trackId);
                } else {
                    $query->where('payment_id', $paymentId);
                }

                $query->update($updateData);
            }

            return redirect()->route('arb.payment.failed')
                ->with('error', $errorText ?? 'Payment failed');

        } catch (Exception $e) {
            Log::error('Error Handler Exception', ['error' => $e->getMessage()]);
            return redirect()->route('arb.payment.error.page')
                ->with('error', 'An unexpected error occurred');
        }
    }

    public function paymentSuccess()
    {
        return view('payment.success');
    }

    public function paymentFailed()
    {
        return view('payment.failed');
    }

    public function paymentError()
    {
        return view('payment.error');
    }
}
