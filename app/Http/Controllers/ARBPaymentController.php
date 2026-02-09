<?php

namespace App\Http\Controllers;

use App\Services\Payment\ARBPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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

            Log::info('========================================');
            Log::info('🚀 ARB PAYMENT INITIATION STARTED');
            Log::info('========================================');
            Log::info('Request Data:');
            Log::info(json_encode($validated, JSON_PRETTY_PRINT));
            Log::info('Customer IP: ' . $request->ip());
            Log::info('User Agent: ' . $request->userAgent());
            Log::info('----------------------------------------');

            if (empty($validated['track_id'])) {
                $validated['track_id'] = 'TRK-' . time() . '-' . uniqid();
                Log::info('✅ Generated Track ID: ' . $validated['track_id']);
            }

            // Generate payment token
            Log::info('📝 Generating Payment Token...');
            $paymentData = $this->arbPaymentService->generatePaymentToken($validated);
            Log::info('✅ Payment Token Generated Successfully');

            // Make API call to ARB Payment Gateway
            Log::info('========================================');
            Log::info('📤 SENDING REQUEST TO ARB GATEWAY');
            Log::info('========================================');
            Log::info('URL: ' . $paymentData['payment_url']);
            Log::info('Method: POST');
            Log::info('Headers:');
            Log::info('  - Content-Type: application/json');
            Log::info('  - X-FORWARDED-FOR: ' . $request->ip());
            Log::info('----------------------------------------');
            Log::info('Request Payload:');
            Log::info(json_encode($paymentData['request_payload'], JSON_PRETTY_PRINT));
            Log::info('========================================');

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-FORWARDED-FOR' => $request->ip()
                ])
                ->timeout(30)
                ->post($paymentData['payment_url'], $paymentData['request_payload']);

            Log::info('========================================');
            Log::info('📥 ARB GATEWAY RESPONSE RECEIVED');
            Log::info('========================================');
            Log::info('HTTP Status: ' . $response->status());
            Log::info('Response Headers:');
            foreach ($response->headers() as $key => $values) {
                Log::info('  - ' . $key . ': ' . implode(', ', $values));
            }
            Log::info('----------------------------------------');
            Log::info('Response Body:');
            Log::info($response->body());
            Log::info('----------------------------------------');
            Log::info('Response JSON:');
            Log::info(json_encode($response->json(), JSON_PRETTY_PRINT));
            Log::info('========================================');

            if (!$response->successful()) {
                Log::error('❌ ARB Gateway Request Failed');
                Log::error('Status Code: ' . $response->status());
                Log::error('Response: ' . $response->body());
                throw new Exception('Payment gateway request failed: ' . $response->body());
            }

            // Parse response
            Log::info('🔍 Parsing ARB Response...');
            $parsedResponse = $this->arbPaymentService->parsePaymentResponse($response->json());

            if (!$parsedResponse['success']) {
                Log::error('❌ Payment Initiation Failed');
                Log::error('Error: ' . ($parsedResponse['error_text'] ?? 'Unknown error'));
                throw new Exception($parsedResponse['error_text'] ?? 'Payment initiation failed');
            }

            // Save transaction to database
            Log::info('========================================');
            Log::info('💾 SAVING TRANSACTION TO DATABASE');
            Log::info('========================================');

            $transactionData = [
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
            ];

            Log::info('Transaction Data:');
            Log::info(json_encode($transactionData, JSON_PRETTY_PRINT));

            DB::table('arb_transactions')->insert($transactionData);

            Log::info('✅ Transaction Saved Successfully');
            Log::info('----------------------------------------');

            Log::info('========================================');
            Log::info('✅ PAYMENT INITIATION COMPLETED');
            Log::info('========================================');
            Log::info('Payment ID: ' . $parsedResponse['payment_id']);
            Log::info('Payment URL: ' . $parsedResponse['payment_page_url']);
            Log::info('Track ID: ' . $validated['track_id']);
            Log::info('Amount: SAR ' . $validated['amount']);
            Log::info('Customer: ' . $validated['customer_name']);
            Log::info('========================================');
            Log::info('🔗 REDIRECTING TO PAYMENT PAGE');
            Log::info('========================================');

            // Redirect to payment page
            return view('payment.redirect', [
                'payment_url' => $parsedResponse['payment_page_url'],
                'payment_id' => $parsedResponse['payment_id'],
                'track_id' => $validated['track_id']
            ]);

        } catch (Exception $e) {
            Log::error('========================================');
            Log::error('❌ PAYMENT INITIATION ERROR');
            Log::error('========================================');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile());
            Log::error('Error Line: ' . $e->getLine());
            Log::error('----------------------------------------');
            Log::error('Stack Trace:');
            Log::error($e->getTraceAsString());
            Log::error('========================================');

            return back()->withInput()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            Log::info('========================================');
            Log::info('📞 ARB PAYMENT CALLBACK RECEIVED');
            Log::info('========================================');
            Log::info('Timestamp: ' . now()->toDateTimeString());
            Log::info('IP Address: ' . $request->ip());
            Log::info('User Agent: ' . $request->userAgent());
            Log::info('Method: ' . $request->method());
            Log::info('----------------------------------------');
            Log::info('All Request Data:');
            Log::info(json_encode($request->all(), JSON_PRETTY_PRINT));
            Log::info('----------------------------------------');
            Log::info('Query Parameters:');
            Log::info(json_encode($request->query(), JSON_PRETTY_PRINT));
            Log::info('----------------------------------------');
            Log::info('POST Parameters:');
            Log::info(json_encode($request->post(), JSON_PRETTY_PRINT));
            Log::info('========================================');

            $callbackResult = $this->arbPaymentService->handleCallback($request->all());

            // Updated field names from documentation
            $trackId = $callbackResult['data']['trackId'] ?? null;
            $paymentId = $callbackResult['data']['paymentId'] ?? $request->input('paymentId');
            $transactionId = $callbackResult['data']['transId'] ?? null;

            Log::info('========================================');
            Log::info('🔍 CALLBACK DATA EXTRACTED');
            Log::info('========================================');
            Log::info('Track ID: ' . ($trackId ?? 'N/A'));
            Log::info('Payment ID: ' . ($paymentId ?? 'N/A'));
            Log::info('Transaction ID: ' . ($transactionId ?? 'N/A'));
            Log::info('Success: ' . ($callbackResult['success'] ? 'YES ✅' : 'NO ❌'));
            Log::info('Message: ' . $callbackResult['message']);
            Log::info('----------------------------------------');

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

                Log::info('========================================');
                Log::info('💾 UPDATING TRANSACTION IN DATABASE');
                Log::info('========================================');
                Log::info('Update Data:');
                Log::info(json_encode($updateData, JSON_PRETTY_PRINT));
                Log::info('----------------------------------------');

                $query = DB::table('arb_transactions');

                if ($trackId) {
                    $query->where('track_id', $trackId);
                    Log::info('Searching by Track ID: ' . $trackId);
                } else {
                    $query->where('payment_id', $paymentId);
                    Log::info('Searching by Payment ID: ' . $paymentId);
                }

                $affectedRows = $query->update($updateData);

                Log::info('✅ Database Update Complete');
                Log::info('Rows Affected: ' . $affectedRows);
                Log::info('----------------------------------------');
            } else {
                Log::warning('⚠️ No Track ID or Payment ID found - Skipping database update');
            }

            Log::info('========================================');
            Log::info('🎯 CALLBACK PROCESSING COMPLETE');
            Log::info('========================================');

            if ($callbackResult['success']) {
                Log::info('✅ Redirecting to Success Page');
                return redirect()->route('arb.payment.success')
                    ->with('success', 'Payment completed successfully')
                    ->with('transaction_data', $callbackResult['data']);
            } else {
                Log::info('❌ Redirecting to Failed Page');
                return redirect()->route('arb.payment.failed')
                    ->with('error', $callbackResult['message'])
                    ->with('transaction_data', $callbackResult['data']);
            }

        } catch (Exception $e) {
            Log::error('========================================');
            Log::error('❌ CALLBACK HANDLING ERROR');
            Log::error('========================================');
            Log::error('Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            Log::error('----------------------------------------');
            Log::error('Stack Trace:');
            Log::error($e->getTraceAsString());
            Log::error('========================================');

            return redirect()->route('arb.payment.error.page')
                ->with('error', 'An error occurred while processing payment response');
        }
    }

    public function handleError(Request $request)
    {
        try {
            Log::info('========================================');
            Log::info('⚠️ ARB ERROR CALLBACK RECEIVED');
            Log::info('========================================');
            Log::info('Timestamp: ' . now()->toDateTimeString());
            Log::info('All Request Data:');
            Log::info(json_encode($request->all(), JSON_PRETTY_PRINT));
            Log::info('========================================');

            $errorCode = $request->input('Error');
            $errorText = $request->input('ErrorText');
            $paymentId = $request->input('paymentid');
            $trackId = $request->input('trackid');

            Log::info('Error Details:');
            Log::info('  - Error Code: ' . ($errorCode ?? 'N/A'));
            Log::info('  - Error Text: ' . ($errorText ?? 'N/A'));
            Log::info('  - Payment ID: ' . ($paymentId ?? 'N/A'));
            Log::info('  - Track ID: ' . ($trackId ?? 'N/A'));
            Log::info('----------------------------------------');

            if ($trackId || $paymentId) {
                $updateData = [
                    'status' => 'error',
                    'error_code' => $errorCode,
                    'result_message' => $errorText,
                    'updated_at' => now(),
                ];

                Log::info('💾 Updating transaction with error status...');

                $query = DB::table('arb_transactions');

                if ($trackId) {
                    $query->where('track_id', $trackId);
                } else {
                    $query->where('payment_id', $paymentId);
                }

                $affectedRows = $query->update($updateData);

                Log::info('✅ Database Updated - Rows Affected: ' . $affectedRows);
            }

            Log::info('========================================');
            Log::info('🔄 Redirecting to Failed Page');
            Log::info('========================================');

            return redirect()->route('arb.payment.failed')
                ->with('error', $errorText ?? 'Payment failed');

        } catch (Exception $e) {
            Log::error('========================================');
            Log::error('❌ ERROR HANDLER EXCEPTION');
            Log::error('========================================');
            Log::error('Error: ' . $e->getMessage());
            Log::error('========================================');

            return redirect()->route('arb.payment.error.page')
                ->with('error', 'An unexpected error occurred');
        }
    }

    public function paymentSuccess()
    {
        Log::info('========================================');
        Log::info('✅ PAYMENT SUCCESS PAGE DISPLAYED');
        Log::info('========================================');
        Log::info('Transaction Data: ' . json_encode(session('transaction_data'), JSON_PRETTY_PRINT));
        Log::info('========================================');

        return view('payment.success');
    }

    public function paymentFailed()
    {
        Log::info('========================================');
        Log::info('❌ PAYMENT FAILED PAGE DISPLAYED');
        Log::info('========================================');
        Log::info('Error Message: ' . session('error'));
        Log::info('Transaction Data: ' . json_encode(session('transaction_data'), JSON_PRETTY_PRINT));
        Log::info('========================================');

        return view('payment.failed');
    }

    public function paymentError()
    {
        Log::info('========================================');
        Log::info('⚠️ PAYMENT ERROR PAGE DISPLAYED');
        Log::info('========================================');
        Log::info('Error Message: ' . session('error'));
        Log::info('========================================');

        return view('payment.error');
    }
}
