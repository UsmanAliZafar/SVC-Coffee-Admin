<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\ARBPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\NotificationService;
use App\Services\OrderCreationService;
//
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Coupon;

class ARBCheckoutController extends Controller
{
    protected $arbService;
    protected $notificationService;
    protected $orderService;

    public function __construct(ARBPaymentService $arbService,OrderCreationService $orderService)
    {
        $this->arbService = $arbService;
        $this->notificationService = app(NotificationService::class);
        $this->orderService = $orderService;
    }

    /**
     * Initiate ARB Payment
     * POST /api/arb-checkout/initiate
     */
    public function initiatePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email',
                'customer_mobile' => 'nullable|string|max:20',
                'cart_id' => 'required|string',
                'invoice_id' => 'nullable|string',

                // Shipping data
                'shipping_data' => 'required|array',
                'shipping_data.first_name' => 'required|string',
                'shipping_data.last_name' => 'required|string',
                'shipping_data.address' => 'required|string',
                'shipping_data.city' => 'required|string',
                'shipping_data.state' => 'required|string',
                'shipping_data.country' => 'required|string',
                'shipping_data.zip' => 'required|string',
                'shipping_data.phone' => 'required|string',

                // Billing data
                'billing_data' => 'required|array',
                'billing_data.first_name' => 'required|string',
                'billing_data.last_name' => 'required|string',
                'billing_data.address' => 'required|string',
                'billing_data.city' => 'required|string',
                'billing_data.state' => 'required|string',
                'billing_data.country' => 'required|string',
                'billing_data.zip' => 'required|string',

                // Payment info
                'payment_method' => 'required|string',
                'payment_gateway' => 'nullable|string',
                'shipping_method' => 'required|string',
                'shipping_amount' => 'required|numeric|min:0',
                'free_shipping' => 'nullable|boolean',
                'free_shipping_reason' => 'nullable|string',
                'shipping_calculation_type' => 'nullable|string',
                'coupon_code' => 'nullable|string',
            ]);

            // Generate track_id
            $trackId = 'TRK-' . time() . '-' . uniqid();

            // Store pending payment data in cache
            Cache::put("pending_payment:{$trackId}", [
                'cart_id' => $validated['cart_id'],
                'amount' => $validated['amount'],
                'customer_email' => $validated['customer_email'],
                'customer_name' => $validated['customer_name'],
                'customer_mobile' => $validated['customer_mobile'] ?? null,
                'shipping_data' => $validated['shipping_data'],
                'billing_data' => $validated['billing_data'],
                'payment_method' => $validated['payment_method'],
                'payment_gateway' => $validated['payment_gateway'] ?? 'arb',
                'shipping_method' => $validated['shipping_method'],
                'shipping_amount' => $validated['shipping_amount'],
                'free_shipping' => $validated['free_shipping'] ?? false,
                'free_shipping_reason' => $validated['free_shipping_reason'] ?? null,
                'shipping_calculation_type' => $validated['shipping_calculation_type'] ?? null,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'invoice_id' => $validated['invoice_id'] ?? null,
                'created_at' => now(),
            ], now()->addHours(2)); // Expire after 2 hours

            // Prepare payment data for ARB
            $paymentData = $this->arbService->generatePaymentToken([
                'amount' => $validated['amount'],
                'track_id' => $trackId,
                'customer_email' => $validated['customer_email'],
                'customer_name' => $validated['customer_name'],
            ]);

            // Call ARB Gateway
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-FORWARDED-FOR' => $request->ip()
                ])
                ->timeout(30)
                ->post($paymentData['payment_url'], $paymentData['request_payload']);

            if (!$response->successful()) {
                throw new \Exception('ARB Gateway Error: ' . $response->body());
            }

            $parsedResponse = $this->arbService->parsePaymentResponse($response->json());

            if (!$parsedResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initiation failed',
                    'error' => $parsedResponse['error_text'] ?? 'Unknown error'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_url' => $parsedResponse['payment_page_url'],
                    'payment_id' => $parsedResponse['payment_id'],
                    'track_id' => $trackId,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('ARB Payment Initiation Failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ARB Callback Handler
     * POST /api/arb-checkout/callback
    */
    public function handleCallback(Request $request)
    {
        try {
            Log::info('========================================');
            Log::info('📥 ARB CHECKOUT CALLBACK RECEIVED');
            Log::info('========================================');
            Log::info('Raw Callback Data:', $request->all());

            $callbackResult = $this->arbService->handleCallback($request->all());

            $trackId = $callbackResult['data']['trackId'] ?? null;

            if (!$trackId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid callback data - missing trackId'
                ], 400);
            }

            // Retrieve pending payment data from cache
            $pendingData = Cache::get("pending_payment:{$trackId}");

            if (!$pendingData) {
                Log::error('❌ Pending payment data not found', ['track_id' => $trackId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment session expired or not found'
                ], 404);
            }

            Log::info('✅ Pending payment data retrieved', [
                'track_id' => $trackId,
                'cart_id' => $pendingData['cart_id'],
            ]);

            // Start database transaction
            DB::beginTransaction();

            if ($callbackResult['success']) {
                Log::info('========================================');
                Log::info('✅ PAYMENT SUCCESSFUL - CREATING ORDER');
                Log::info('========================================');

                // ============================================================
                // GET CART DATA
                // ============================================================
                $cart = Cache::get("cart:{$pendingData['cart_id']}", []);
                $cartMeta = Cache::get("cart_meta:{$pendingData['cart_id']}", []);

                if (empty($cart)) {
                    DB::rollBack();
                    Log::error('❌ Cart is empty or expired', ['cart_id' => $pendingData['cart_id']]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart is empty or expired'
                    ], 400);
                }

                Log::info('✅ Cart data retrieved', [
                    'items_count' => count($cart),
                ]);

                // ============================================================
                // CALCULATE TOTALS USING SERVICE
                // ============================================================
                $totals = $this->orderService->calculateCartTotals($cart);

                Log::info('========================================');
                Log::info('📊 TAX CALCULATION BREAKDOWN');
                Log::info('========================================');
                Log::info('Subtotal: ' . $totals['subtotal'] . ' SAR (includes any inclusive tax)');
                Log::info('Exclusive Tax (to add): ' . $totals['tax_amount'] . ' SAR');
                Log::info('Inclusive Tax (for display): ' . $totals['included_tax_amount'] . ' SAR');
                Log::info('Tax Statement: ' . ($totals['tax_statement'] ?? 'N/A'));
                Log::info('========================================');

                // Get discount from coupon
                $discountAmount = 0;
                if (isset($cartMeta['coupon'])) {
                    $discountAmount = $cartMeta['coupon']['discount_amount'] ?? 0;
                    Log::info('💰 Coupon discount applied', [
                        'code' => $cartMeta['coupon']['code'],
                        'discount' => $discountAmount,
                    ]);
                }

                // Calculate final total
                $totalAmount = $this->orderService->calculateFinalTotal(
                    $totals,
                    (float) $pendingData['shipping_amount'],
                    (float) $discountAmount
                );

                Log::info('💵 Final total calculated', [
                    'total_amount' => $totalAmount,
                ]);

                // ============================================================
                // VERIFY PAYMENT AMOUNT
                // ============================================================
                $paidAmount = isset($callbackResult['data']['amt']) ? (float) $callbackResult['data']['amt'] : 0;

                Log::info('========================================');
                Log::info('💰 AMOUNT VERIFICATION');
                Log::info('========================================');
                Log::info('Expected: ' . $totalAmount);
                Log::info('Paid: ' . $paidAmount);
                Log::info('Match: ' . (abs($totalAmount - $paidAmount) < 0.01 ? 'YES ✅' : 'NO ❌'));
                Log::info('========================================');

                if (abs($totalAmount - $paidAmount) > 0.01) {
                    Log::warning('⚠️ PAYMENT AMOUNT MISMATCH', [
                        'track_id' => $trackId,
                        'expected' => $totalAmount,
                        'paid' => $paidAmount,
                        'difference' => $paidAmount - $totalAmount,
                    ]);
                }

                // ============================================================
                // CREATE ORDER USING SERVICE
                // ============================================================
                Log::info('========================================');
                Log::info('🛒 CREATING ORDER');
                Log::info('========================================');

                try {
                    $order = $this->orderService->createOrder([
                    'customer_id' => null,
                    'guest_email' => $pendingData['customer_email'],
                    'guest_name' => $pendingData['customer_name'],
                    'guest_phone' => $pendingData['customer_mobile'],

                    'shipping_first_name' => $pendingData['shipping_data']['first_name'],
                    'shipping_last_name' => $pendingData['shipping_data']['last_name'],
                    'shipping_address_line1' => $pendingData['shipping_data']['address'],
                    'shipping_address_line2' => null,
                    'shipping_city' => $pendingData['shipping_data']['city'],
                    'shipping_state' => $pendingData['shipping_data']['state'],
                    'shipping_country' => $pendingData['shipping_data']['country'],
                    'shipping_postal_code' => $pendingData['shipping_data']['zip'],
                    'shipping_phone' => $pendingData['shipping_data']['phone'],

                    'billing_same_as_shipping' => true,
                    'billing_first_name' => $pendingData['billing_data']['first_name'],
                    'billing_last_name' => $pendingData['billing_data']['last_name'],
                    'billing_address_line1' => $pendingData['billing_data']['address'],
                    'billing_address_line2' => null,
                    'billing_city' => $pendingData['billing_data']['city'],
                    'billing_state' => $pendingData['billing_data']['state'],
                    'billing_country' => $pendingData['billing_data']['country'],
                    'billing_postal_code' => $pendingData['billing_data']['zip'],

                    'payment_method' => 'online',
                    'payment_gateway' => 'arb',
                    'shipping_method' => $pendingData['shipping_method'],
                    'shipping_amount' => (float) $pendingData['shipping_amount'],
                    'shipping_calculation_type' => $pendingData['shipping_calculation_type'],

                    'order_source' => 'web',
                    'currency' => 'SAR',
                    'subtotal' => $totals['subtotal'],
                    'tax_amount' => $totals['tax_amount'],
                    'discount_amount' => $discountAmount,
                    'discount_code' => $pendingData['coupon_code'],
                    'total_amount' => $totalAmount,

                    'status_key_code' => 'ORDER_CONFIRMED', // ✅ DEDUCT STOCK IMMEDIATELY
                    'payment_status_key_code' => 'PAYMENT_PAID',
                    'confirmed_at' => now(),

                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),

                    'metadata' => $pendingData['free_shipping'] ? json_encode([
                        'free_shipping' => true,
                        'free_shipping_reason' => $pendingData['free_shipping_reason']
                    ]) : null,
                ], $cart, $cartMeta);

                } catch (\Exception $orderError) {
                    DB::rollBack();

                    Log::error('========================================');
                    Log::error('❌ ORDER CREATION FAILED');
                    Log::error('========================================');
                    Log::error('Error Message: ' . $orderError->getMessage());
                    Log::error('Error Line: ' . $orderError->getLine());
                    Log::error('Error File: ' . $orderError->getFile());
                    Log::error('========================================');

                    // Return error as JSON for debugging
                    return response()->json([
                        'success' => false,
                        'message' => 'Order creation failed',
                        'error' => $orderError->getMessage(),
                        'line' => $orderError->getLine(),
                        'file' => basename($orderError->getFile()),
                    ], 500);
                }

                Log::info('========================================');
                Log::info('✅ ORDER CREATED SUCCESSFULLY');
                Log::info('========================================');
                Log::info('Order Number: ' . $order->order_number);
                Log::info('Order ID: ' . $order->id);
                Log::info('Total: ' . $order->total_amount . ' ' . $order->currency);
                Log::info('========================================');

                // ============================================================
                // CREATE TRANSACTION RECORD
                // ============================================================
                $transaction = Transaction::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'transaction_type' => 'payment',
                    'payment_method' => 'online',
                    'payment_gateway' => 'arb',
                    'amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'status_key_code' => 'TRANSACTION_SUCCESS',

                    // ARB specific
                    'track_id' => $trackId,
                    'arb_payment_id' => $callbackResult['data']['paymentId'] ?? null,
                    'arb_transaction_id' => $callbackResult['data']['transId'] ?? null,
                    'gateway_transaction_id' => $callbackResult['data']['transId'] ?? null,
                    'auth_resp_code' => $callbackResult['data']['authRespCode'] ?? null,
                    'authorization_code' => $callbackResult['data']['authCode'] ?? null,
                    'ref_number' => $callbackResult['data']['ref'] ?? null,
                    'card_brand' => $callbackResult['data']['cardType'] ?? null,
                    'card_last_four' => isset($callbackResult['data']['card'])
                        ? substr($callbackResult['data']['card'], -4)
                        : null,
                    'gateway_response' => json_encode($callbackResult['data']),
                    'gateway_status' => 'captured',

                    // Billing info
                    'billing_name' => $pendingData['customer_name'],
                    'billing_email' => $pendingData['customer_email'],
                    'billing_phone' => $pendingData['customer_mobile'],

                    // Timestamps
                    'initiated_at' => $pendingData['created_at'],
                    'arb_paid_at' => now(),
                    'completed_at' => now(),

                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                Log::info('✅ Transaction record created', [
                    'transaction_number' => $transaction->transaction_number,
                ]);

                // ============================================================
                // RECORD COUPON USAGE
                // ============================================================
                if (!empty($pendingData['coupon_code'])) {
                    $coupon = Coupon::where('code', $pendingData['coupon_code'])->first();
                    if ($coupon) {
                        $coupon->recordUsage(
                            orderId: $order->id,
                            customerId: null,
                            customerEmail: $order->guest_email,
                            discountAmount: $order->discount_amount,
                            orderSubtotal: $order->subtotal,
                            orderTotal: $order->total_amount,
                            ipAddress: $request->ip()
                        );

                        Log::info('✅ Coupon usage recorded', [
                            'code' => $coupon->code,
                        ]);
                    }
                }

                // ============================================================
                // CLEAR CART AND PENDING DATA
                // ============================================================
                Cache::forget("cart:{$pendingData['cart_id']}");
                Cache::forget("cart_meta:{$pendingData['cart_id']}");
                Cache::forget("pending_payment:{$trackId}");

                Log::info('✅ Cache cleared');

                // Commit transaction
                DB::commit();

                Log::info('========================================');
                Log::info('✅ DATABASE TRANSACTION COMMITTED');
                Log::info('========================================');

                // ============================================================
                // SEND NOTIFICATIONS
                // ============================================================
                try {
                    app(NotificationService::class)->notify('order_created', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                        'customer_name' => $order->guest_name,
                        'customer_email' => $order->guest_email,
                    ]);

                    app(NotificationService::class)->notifyCustomer('order_confirmed', $order);

                    Log::info('✅ Notifications sent');
                } catch (\Exception $e) {
                    Log::warning('⚠️ Notification sending failed (non-critical)', [
                        'error' => $e->getMessage(),
                    ]);
                }

                // ============================================================
                // STORE TRANSACTION DATA FOR SUCCESS PAGE
                // ============================================================
                session()->put('transaction_data', [
                    'amt' => $order->total_amount,
                    'currency' => $order->currency,
                    'transId' => $callbackResult['data']['transId'] ?? null,
                    'paymentId' => $callbackResult['data']['paymentId'] ?? null,
                    'ref' => $callbackResult['data']['ref'] ?? null,
                    'authCode' => $callbackResult['data']['authCode'] ?? null,
                    'cardType' => $callbackResult['data']['cardType'] ?? null,
                    'trackId' => $trackId,
                ]);

                Log::info('========================================');
                Log::info('✅ PAYMENT PROCESSING COMPLETE');
                Log::info('========================================');

                // ============================================================
                // RETURN SUCCESS BLADE VIEW
                // ============================================================
                return view('payment.payment_success', [
                    'order' => $order,
                    'transaction' => $transaction,
                ]);

            } else {
                // ============================================================
                // PAYMENT FAILED OR CANCELLED
                // ============================================================
                Log::warning('========================================');
                Log::warning('❌ PAYMENT FAILED OR CANCELLED');
                Log::warning('========================================');
                Log::warning('Message: ' . ($callbackResult['message'] ?? 'Unknown'));
                Log::warning('========================================');

                DB::rollBack();

                // Determine if it's a cancellation or failure
                $isCancelled = stripos($callbackResult['message'], 'cancel') !== false;

                // Return blade view
                return view('payment.payment_failed', [
                    'status' => $isCancelled ? 'cancelled' : 'failed',
                    'subtitle' => $isCancelled
                        ? 'You have cancelled the payment'
                        : 'Your payment could not be processed',
                    'message' => $callbackResult['message'] ?? 'Payment transaction failed',
                    'trackId' => $trackId,
                    'canRetry' => true,
                    'errorCode' => $callbackResult['data']['error_code'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('========================================');
            Log::error('❌ ARB CALLBACK ERROR');
            Log::error('========================================');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            Log::error('========================================');
            Log::error('Stack Trace:');
            Log::error($e->getTraceAsString());
            Log::error('========================================');

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleError(Request $request)
    {
        Log::warning('ARB Error Callback', $request->all());

        $trackId = $request->input('trackId') ?? $request->input('track_id');
        $errorMessage = $request->input('ErrorText') ?? $request->input('errorText') ?? 'Payment failed';
        $errorCode = $request->input('Error') ?? $request->input('error');

        return view('payment.payment_failed', [
            'status' => 'failed',
            'subtitle' => 'Transaction could not be completed',
            'message' => $errorMessage,
            'trackId' => $trackId,
            'canRetry' => true,
            'errorCode' => $errorCode,
        ]);
    }
    /**
     * Check Payment Status
     * GET /api/arb-checkout/status/{transaction_id}
     */
    public function checkStatus($trackId)
    {
        try {
            // ✅ CHANGED: Accept track_id instead of transaction_id

            // Check if payment data exists
            $pendingData = Cache::get("pending_payment:{$trackId}");

            if ($pendingData) {
                // Payment initiated but not completed yet
                return response()->json([
                    'success' => false,
                    'data' => [
                        'status' => 'PENDING',
                        'message' => 'Payment in progress',
                    ]
                ], 200);
            }

            // Look for completed transaction with this track_id
            $transaction = Transaction::where('track_id', $trackId)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            $order = $transaction->order;

            return response()->json([
                'success' => $transaction->status_key_code === 'TRANSACTION_SUCCESS',
                'data' => [
                    'track_id' => $trackId,
                    'status' => $transaction->status_key_code,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'is_paid' => $transaction->status_key_code === 'TRANSACTION_SUCCESS',
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status'
            ], 500);
        }
    }
}
