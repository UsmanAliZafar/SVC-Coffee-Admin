<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
// Models
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Coupon;


class CheckoutController extends Controller
{
    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
    }
    /**
     * Create order from cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cart_id' => 'required|string',
                'customer_id' => 'nullable|uuid|exists:customers,id',

                // Guest checkout
                'guest_email' => 'required_without:customer_id|nullable|email',
                'guest_name' => 'required_without:customer_id|nullable|string',
                'guest_phone' => 'nullable|string',

                // Shipping address
                'shipping_first_name' => 'required|string|max:100',
                'shipping_last_name' => 'required|string|max:100',
                'shipping_address_line1' => 'required|string|max:255',
                'shipping_address_line2' => 'nullable|string|max:255',
                'shipping_city' => 'required|string|max:100',
                'shipping_state' => 'nullable|string|max:100',
                'shipping_postal_code' => 'required|string|max:20',
                'shipping_country' => 'required|string|max:100',
                'shipping_phone' => 'required|string|max:20',

                // Billing address
                'billing_same_as_shipping' => 'boolean',
                'billing_first_name' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_last_name' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_address_line1' => 'nullable|required_if:billing_same_as_shipping,false|string|max:255',
                'billing_address_line2' => 'nullable|string|max:255',
                'billing_city' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_state' => 'nullable|string|max:100',
                'billing_postal_code' => 'nullable|required_if:billing_same_as_shipping,false|string|max:20',
                'billing_country' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',

                // Order details
                'shipping_method' => 'required|string',
                'payment_method' => 'required|string|in:cod,online,bank_transfer',
                'payment_gateway' => 'nullable|string|in:stripe,paypal,razorpay',
                'customer_notes' => 'nullable|string',
                'coupon_code' => 'nullable|string',

                // ✅ NEW: Shipping details from external API
                'shipping_amount' => 'required|numeric|min:0',
                'shipping_calculation_type' => 'nullable|string',
                'free_shipping' => 'boolean',
                'free_shipping_reason' => 'nullable|string',
            ]);

            // Get cart
            $cart = Cache::get("cart:{$validated['cart_id']}", []);
            $cartMeta = Cache::get("cart_meta:{$validated['cart_id']}", []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty',
                ], 400);
            }

            DB::beginTransaction();

            // ============================================================
            // CALCULATE TOTALS FROM CART ITEMS (NOT FROM STORE SETTINGS)
            // ============================================================
            $subtotal = 0;
            $taxAmount = 0;
            $totalWeight = 0;
            $totalVolume = 0;
            $itemCount = 0;

            foreach ($cart as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $subtotal += $itemSubtotal;
                $itemCount += $item['quantity'];

                // ✅ Use item-level tax (from cart)
                if ($item['is_taxable']) {
                    $taxAmount += $itemSubtotal * ($item['tax_rate'] / 100);
                }

                // Get product for weight/volume
                $product = Product::find($item['product_id']);
                if ($product) {
                    $totalWeight += ($product->weight ?? 0) * $item['quantity'];
                    $totalVolume += ($product->volume ?? 0) * $item['quantity'];
                }
            }

            // ✅ Calculate shipping
            // ✅ Use shipping amount from external calculation
            $shippingAmount = $validated['shipping_amount'];
            $freeShipping = $validated['free_shipping'] ?? false;
            $freeShippingReason = $validated['free_shipping_reason'] ?? null;

            // Override shipping amount if free shipping is applied
            if ($freeShipping) {
                $shippingAmount = 0;
                \Log::info('Free shipping applied in order creation', [
                    'reason' => $freeShippingReason,
                    'original_shipping_amount' => $validated['shipping_amount']
                ]);
            }

            // ✅ Apply coupon discount if exists
            $discountAmount = 0;
            $couponDetails = null;

            if (isset($cartMeta['coupon'])) {
                $discountAmount = $cartMeta['coupon']['discount_amount'] ?? 0;
                $couponDetails = $cartMeta['coupon'];

                // Free shipping from coupon
                if ($cartMeta['coupon']['free_shipping'] ?? false) {
                    $shippingAmount = 0;
                }
            }

            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // ============================================================
            // VALIDATE STOCK AVAILABILITY BEFORE CREATING ORDER
            // ============================================================

            // Get default warehouse for reservations
            $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();

            if (!$defaultWarehouse) {
                DB::rollBack();
                \Log::error('❌ No default warehouse configured');
                return response()->json([
                    'success' => false,
                    'message' => 'System error: No default warehouse configured. Please contact support.',
                ], 500);
            }

            // Check stock for each item
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);

                if (!$product || !$product->track_inventory) {
                    continue;
                }

                $variant = !empty($item['variant_id'])
                    ? \App\Models\ProductVariant::find($item['variant_id'])
                    : null;

                // ✅ Get TOTAL available stock across ALL warehouses
                $totalAvailableStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->sum('available_quantity');

                $itemName = $variant
                    ? "{$product->name} ({$variant->getFullName()})"
                    : $product->name;

                // ✅ Check if TOTAL available stock is sufficient
                if ($totalAvailableStock < $item['quantity']) {
                    DB::rollBack();

                    \Log::error('❌ Insufficient stock across all warehouses', [
                        'product' => $product->name,
                        'variant' => $variant ? $variant->getFullName() : null,
                        'requested' => $item['quantity'],
                        'total_available' => $totalAvailableStock,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$itemName}",
                        'details' => [
                            'product' => $itemName,
                            'requested' => $item['quantity'],
                            'available' => $totalAvailableStock,
                        ],
                    ], 400);
                }
            }

            // ============================================================
            // CREATE ORDER
            // ============================================================
            $orderData = [
                'customer_id' => $validated['customer_id'] ?? null,
                'guest_email' => $validated['guest_email'] ?? null,
                'guest_name' => $validated['guest_name'] ?? null,
                'guest_phone' => $validated['guest_phone'] ?? null,

                'shipping_first_name' => $validated['shipping_first_name'],
                'shipping_last_name' => $validated['shipping_last_name'],
                'shipping_address_line1' => $validated['shipping_address_line1'],
                'shipping_address_line2' => $validated['shipping_address_line2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_state' => $validated['shipping_state'] ?? null,
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_country' => $validated['shipping_country'],
                'shipping_phone' => $validated['shipping_phone'],

                'billing_same_as_shipping' => $validated['billing_same_as_shipping'] ?? true,
                'billing_first_name' => $validated['billing_first_name'] ?? $validated['shipping_first_name'],
                'billing_last_name' => $validated['billing_last_name'] ?? $validated['shipping_last_name'],
                'billing_address_line1' => $validated['billing_address_line1'] ?? $validated['shipping_address_line1'],
                'billing_address_line2' => $validated['billing_address_line2'] ?? $validated['shipping_address_line2'],
                'billing_city' => $validated['billing_city'] ?? $validated['shipping_city'],
                'billing_state' => $validated['billing_state'] ?? $validated['shipping_state'],
                'billing_postal_code' => $validated['billing_postal_code'] ?? $validated['shipping_postal_code'],
                'billing_country' => $validated['billing_country'] ?? $validated['shipping_country'],

                'shipping_method' => $validated['shipping_method'],
                'shipping_amount' => $shippingAmount, // ✅ Use calculated shipping
                'shipping_calculation_type' => $validated['shipping_calculation_type'] ?? null, // ✅ NEW

                'payment_method' => $validated['payment_method'],
                'payment_gateway' => $validated['payment_gateway'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,

                'order_source' => 'web',
                'currency' => $cart[array_key_first($cart)]['product_currency'] ?? 'USD',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'discount_code' => $couponDetails['code'] ?? null,
                'total_amount' => $totalAmount,

                'status_key_code' => 'ORDER_PENDING',
                'payment_status_key_code' => 'PAYMENT_PENDING',

                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),

                // ✅ Optional: Store free shipping info if needed
                'metadata' => $freeShipping ? json_encode([
                    'free_shipping' => true,
                    'free_shipping_reason' => $freeShippingReason
                ]) : null,
            ];

            $order = Order::create($orderData);

            // ============================================================
            // CREATE ORDER ITEMS
            // ============================================================
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                $variant = !empty($item['variant_id'])
                    ? \App\Models\ProductVariant::find($item['variant_id'])
                    : null;

                $itemSubtotal = $item['price'] * $item['quantity'];
                $itemTaxAmount = $item['is_taxable']
                    ? ($itemSubtotal * ($item['tax_rate'] / 100))
                    : 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'product_description' => $product->short_description,
                    'product_image' => $item['image'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'cost_price' => $variant ? $variant->cost_price : $product->cost_price,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTaxAmount,
                    'tax_rate' => $item['tax_rate'],
                    'is_taxable' => $item['is_taxable'],
                    'total' => $itemSubtotal + $itemTaxAmount,
                    'status_key_code' => 'ITEM_PENDING',
                    'warehouse_id' => $defaultWarehouse->id,
                ]);
            }

            // ============================================================
            // RESERVE STOCK (STAGE 1) ✅ MULTI-WAREHOUSE ALLOCATION
            // ============================================================
            \Log::info('📦 Reserving stock for web order', [
                'order' => $order->order_number,
                'items_count' => count($cart),
            ]);

            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);

                if (!$product || !$product->track_inventory) {
                    continue;
                }

                $variant = !empty($item['variant_id'])
                    ? \App\Models\ProductVariant::find($item['variant_id'])
                    : null;

                $quantityNeeded = $item['quantity'];
                $itemName = $variant
                    ? "{$product->name} ({$variant->getFullName()})"
                    : $product->name;

                // ✅ Get warehouses with available stock, prioritize default warehouse
                $warehouseStocks = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->where('available_quantity', '>', 0)
                    ->join('warehouses', 'product_warehouse_stock.warehouse_id', '=', 'warehouses.id')
                    ->select('product_warehouse_stock.*', 'warehouses.name as warehouse_name', 'warehouses.is_default')
                    ->orderBy('warehouses.is_default', 'desc') // Default warehouse first
                    ->orderBy('warehouses.priority', 'desc')
                    ->get();

                if ($warehouseStocks->isEmpty()) {
                    DB::rollBack();
                    \Log::error('❌ No warehouse stock available during reservation', [
                        'product' => $itemName,
                        'quantity_needed' => $quantityNeeded,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Stock not available for {$itemName}",
                    ], 400);
                }

                // ✅ TRACK FULFILLMENT DETAILS
                $fulfillmentDetails = [];
                $remainingQuantity = $quantityNeeded;

                // ✅ Reserve from warehouses until quantity is fulfilled
                foreach ($warehouseStocks as $warehouseStock) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    $reserveQty = min($remainingQuantity, $warehouseStock->available_quantity);

                    if ($warehouseStock->reserveStock($reserveQty)) {
                        // ✅ ADD TO FULFILLMENT DETAILS
                        $fulfillmentDetails[] = [
                            'warehouse_id' => $warehouseStock->warehouse_id,
                            'warehouse_name' => $warehouseStock->warehouse_name,
                            'quantity' => $reserveQty,
                            'action' => 'RESERVED',
                        ];

                        \Log::info('🔒 RESERVED (Web Order)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                            'quantity' => $reserveQty,
                            'warehouse' => $warehouseStock->warehouse_name,
                            'warehouse_id' => $warehouseStock->warehouse_id,
                        ]);

                        $remainingQuantity -= $reserveQty;
                    }
                }

                // ✅ Verify all quantity was reserved
                if ($remainingQuantity > 0) {
                    DB::rollBack();
                    \Log::error('❌ Failed to reserve full quantity', [
                        'product' => $itemName,
                        'quantity_needed' => $item['quantity'],
                        'quantity_remaining' => $remainingQuantity,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Failed to reserve stock for {$itemName}",
                    ], 400);
                }

                // ✅ UPDATE ORDER ITEM WITH FULFILLMENT DETAILS
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->where('product_variant_id', $variant ? $variant->id : null)
                    ->first();

                if ($orderItem) {
                    $orderItem->update([
                        'stock_reserved' => true,
                        'stock_reserved_at' => now(),
                        'warehouse_id' => $fulfillmentDetails[0]['warehouse_id'], // ← Primary warehouse
                        'fulfillment_details' => $fulfillmentDetails, // ✅ STORE ALL WAREHOUSES
                    ]);

                    \Log::info('✅ Fulfillment details stored', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'warehouses_used' => count($fulfillmentDetails),
                        'details' => $fulfillmentDetails,
                    ]);
                }
            }
            // ============================================================
            // CREATE TRANSACTION
            // ============================================================
            $transaction = $this->createTransaction($order, $validated, $request);

            if (!$transaction) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create transaction record',
                ], 500);
            }

            // ============================================================
            // ✅ RECORD COUPON USAGE
            // ============================================================
            if (isset($cartMeta['coupon']) && $cartMeta['coupon']['id']) {
                $coupon = Coupon::find($cartMeta['coupon']['id']);

                if ($coupon) {
                    $coupon->recordUsage(
                        orderId: $order->id,
                        customerId: $order->customer_id,
                        customerEmail: $order->getCustomerEmail(),
                        discountAmount: $discountAmount,
                        orderSubtotal: $subtotal,
                        orderTotal: $totalAmount,
                        ipAddress: $request->ip()
                    );

                    \Log::info('✅ Coupon usage recorded', [
                        'coupon_code' => $coupon->code,
                        'order' => $order->order_number,
                        'discount' => $discountAmount,
                    ]);
                }
            }
            // Clear cart
            Cache::forget("cart:{$validated['cart_id']}");
            Cache::forget("cart_meta:{$validated['cart_id']}");

            DB::commit();

            // ============================================================
            // NOTIFICATIONS
            // ============================================================
            // 1. Order created notification to admins
            $this->notificationService->notify('order_created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
                'items_count' => count($cart),
                'payment_method' => ucfirst($validated['payment_method']),
            ]);

            // 2. High-value order notification (if applicable)
            if (exceeds_order_threshold($order->total_amount)) {
                $this->notificationService->notify('customer_high_value_order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->getCustomerName(),
                    'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                ]);
            }

            // 3. Notify customer
            $this->notificationService->notifyCustomer('order_created', $order);

            // ============================================================
            // PREPARE RESPONSE
            // ============================================================
            $responseData = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency,
                'payment_method' => $validated['payment_method'],
            ];

            if ($validated['payment_method'] === 'cod') {
                $responseData['payment_required'] = false;
                $responseData['message'] = 'Order placed successfully. Pay on delivery.';
                $responseData['next_step'] = 'order_confirmation';
            } else {
                $responseData['payment_required'] = true;
                $responseData['message'] = 'Order created. Please complete payment.';
                $responseData['next_step'] = 'payment_gateway';
                $responseData['payment_gateway'] = $validated['payment_gateway'] ?? 'stripe';
            }

            return response()->json([
                'success' => true,
                'message' => $responseData['message'],
                'data' => $responseData,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Checkout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     *
     * @param Order $order
     * @param array $validated
     * @param Request $request
     * @return Transaction|null
     */
    private function createTransaction(Order $order, array $validated, Request $request): ?Transaction
    {
        try {
            $transactionData = [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'transaction_type' => 'payment',
                'payment_method' => $validated['payment_method'],
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'fee' => 0, // Calculate gateway fee if applicable

                // Billing information from order
                'billing_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                'billing_email' => $order->customer_id ? $order->customer->email : $order->guest_email,
                'billing_phone' => $order->billing_phone ?? $order->shipping_phone,
                'billing_address' => $order->billing_address_line1,
                'billing_city' => $order->billing_city,
                'billing_country' => $order->billing_country,
                'billing_postal_code' => $order->billing_postal_code,

                // Request metadata
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->detectDeviceType($request),

                // Timestamps
                'initiated_at' => now(),
            ];

            // ✅ PAYMENT METHOD SPECIFIC HANDLING
            if ($validated['payment_method'] === 'cod') {
                // COD Transaction
                $transactionData['payment_gateway'] = 'manual';
                $transactionData['status_key_code'] = 'TRANSACTION_PENDING';
                $transactionData['gateway_status'] = 'pending_payment';
                $transactionData['notes'] = 'Cash on Delivery - Payment will be collected upon delivery';

            } else {
                // Online Payment Transaction
                $transactionData['payment_gateway'] = $validated['payment_gateway'] ?? 'stripe';
                $transactionData['status_key_code'] = 'TRANSACTION_PENDING';
                $transactionData['gateway_status'] = 'awaiting_payment';
                $transactionData['notes'] = 'Online payment - Awaiting customer payment confirmation';
            }

            return Transaction::create($transactionData);

        } catch (\Exception $e) {
            \Log::error('Failed to create transaction', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Detect device type from user agent
     *
     * @param Request $request
     * @return string
     */
    private function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Confirm payment for online orders
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|uuid|exists:transactions,id',
                'gateway_transaction_id' => 'required|string',
                'gateway_status' => 'required|string',
                'gateway_response' => 'nullable|array',
            ]);

            DB::beginTransaction();

            $transaction = Transaction::with('order.items.product', 'order.items.variant')
                ->findOrFail($validated['transaction_id']);
            $order = $transaction->order;

            $oldStatus = $order->status_key_code;

            // Update transaction
            $transaction->update([
                'status_key_code' => 'TRANSACTION_SUCCESS',
                'gateway_transaction_id' => $validated['gateway_transaction_id'],
                'gateway_status' => $validated['gateway_status'],
                'gateway_response' => $validated['gateway_response'] ?? null,
                'completed_at' => now(),
            ]);

            // Update order payment status
            $order->update([
                'payment_status_key_code' => 'PAYMENT_PAID',
                'status_key_code' => 'ORDER_CONFIRMED',
                'confirmed_at' => now(),
            ]);

            // Handle stock: PENDING → CONFIRMED (Reserve → Deduct)
            $this->handleStatusChange($order, $oldStatus, 'ORDER_CONFIRMED');

            DB::commit();

            // ✅ CORRECTED NOTIFICATIONS
            // 1. Payment received notification to admins
            $this->notificationService->notify('payment_received', [  // ← FIXED
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => $order->getFormattedTotal(),
                'payment_method' => $transaction->payment_method,
            ]);

            // 2. Order confirmed notification
            $this->notificationService->notify('order_confirmed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            // 3. Notify customer
            $this->notificationService->notifyCustomer('order_confirmed', $order);

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_status' => 'confirmed',
                    'payment_status' => 'paid',
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Payment confirmation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle status change with stock management
     * Same logic as Admin OrdersController
     */
    private function handleStatusChange($order, $oldStatus, $newStatus)
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        \Log::info('🔄 Web Order Status Transition', [
            'order' => $order->order_number,
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        foreach ($order->items as $item) {
            if (!$item->product || !$item->product->track_inventory) {
                continue;
            }

            $product = $item->product;
            $variant = $item->variant;
            $quantity = $item->quantity;
            $itemName = $variant ? "{$product->name} ({$variant->getFullName()})" : $product->name;

            // ============================================================
            // PENDING → CONFIRMED: Convert reservation to deduction ⚡
            // ============================================================
            if ($newStatus === 'ORDER_CONFIRMED' && $oldStatus === 'ORDER_PENDING') {
                if ($item->stock_reserved && !$item->stock_deducted) {
                    $fulfillmentDetails = $item->fulfillment_details; // ✅ Auto-decoded by model cast

                    // ✅ MULTI-WAREHOUSE DEDUCTION
                    if (!empty($fulfillmentDetails) && is_array($fulfillmentDetails)) {
                        foreach ($fulfillmentDetails as &$fulfillment) {
                            $warehouseId = $fulfillment['warehouse_id'] ?? null;
                            $warehouseQty = $fulfillment['quantity'] ?? 0;

                            if (!$warehouseId || $warehouseQty <= 0) {
                                continue;
                            }

                            $warehouseStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                                ->where('variant_id', $variant ? $variant->id : null)
                                ->where('warehouse_id', $warehouseId)
                                ->first();

                            if (!$warehouseStock) {
                                continue;
                            }

                            // Release reservation
                            $warehouseStock->releaseStock($warehouseQty);

                            // Deduct actual stock
                            $warehouseStock->reduceStock($warehouseQty);

                            // ✅ UPDATE ACTION
                            $fulfillment['action'] = 'DEDUCTED';

                            \Log::info('⚡ DEDUCTED (Web Order Confirmed)', [
                                'order' => $order->order_number,
                                'product' => $itemName,
                                'warehouse' => $fulfillment['warehouse_name'] ?? $warehouseId,
                                'quantity' => $warehouseQty,
                            ]);
                        }

                        // ✅ UPDATE ITEM WITH NEW FULFILLMENT DETAILS
                        $item->update([
                            'stock_reserved' => false,
                            'stock_reserved_at' => null,
                            'stock_deducted' => true,
                            'stock_deducted_at' => now(),
                            'fulfillment_details' => $fulfillmentDetails, // ✅ Save updated actions
                        ]);
                    }
                }
            }

            // ============================================================
            // ANY → CANCELLED: Restore stock to ALL warehouses
            // ============================================================
            elseif ($newStatus === 'ORDER_CANCELLED') {
                $fulfillmentDetails = $item->fulfillment_details; // ✅ Auto-decoded

                // ✅ MULTI-WAREHOUSE RESTORATION
                if (!empty($fulfillmentDetails) && is_array($fulfillmentDetails)) {
                    \Log::info('🔄 Restoring stock to multiple warehouses', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'warehouses_count' => count($fulfillmentDetails),
                    ]);

                    foreach ($fulfillmentDetails as $fulfillment) {
                        $warehouseId = $fulfillment['warehouse_id'] ?? null;
                        $warehouseQty = $fulfillment['quantity'] ?? 0;
                        $action = $fulfillment['action'] ?? 'UNKNOWN';

                        if (!$warehouseId || $warehouseQty <= 0) {
                            continue;
                        }

                        $warehouseStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                            ->where('variant_id', $variant ? $variant->id : null)
                            ->where('warehouse_id', $warehouseId)
                            ->first();

                        if (!$warehouseStock) {
                            \Log::error('❌ Warehouse stock not found during restoration', [
                                'warehouse_id' => $warehouseId,
                                'product' => $itemName,
                            ]);
                            continue;
                        }

                        // Restore based on original action
                        if ($action === 'DEDUCTED' && $item->stock_deducted) {
                            $warehouseStock->addStock($warehouseQty);

                            \Log::info('✅ RESTORED to warehouse', [
                                'order' => $order->order_number,
                                'product' => $itemName,
                                'warehouse' => $fulfillment['warehouse_name'] ?? $warehouseId,
                                'quantity' => $warehouseQty,
                            ]);

                        } elseif ($action === 'RESERVED' && $item->stock_reserved) {
                            $warehouseStock->releaseStock($warehouseQty);

                            \Log::info('✅ RELEASED from warehouse', [
                                'order' => $order->order_number,
                                'product' => $itemName,
                                'warehouse' => $fulfillment['warehouse_name'] ?? $warehouseId,
                                'quantity' => $warehouseQty,
                            ]);
                        }
                    }

                    // Update item status
                    $item->update([
                        'stock_deducted' => false,
                        'stock_deducted_at' => null,
                        'stock_reserved' => false,
                        'stock_reserved_at' => null,
                        'status_key_code' => 'ITEM_CANCELLED',
                    ]);
                }
                // ✅ FALLBACK: Single warehouse restoration
                else {
                    $warehouseId = $item->warehouse_id;

                    if (!$warehouseId) {
                        $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();
                        if (!$defaultWarehouse) {
                            continue;
                        }
                        $warehouseId = $defaultWarehouse->id;
                    }

                    $warehouseStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                        ->where('variant_id', $variant ? $variant->id : null)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    if (!$warehouseStock) {
                        continue;
                    }

                    if ($item->stock_deducted) {
                        $warehouseStock->addStock($quantity);
                        \Log::info('✅ RESTORED (single warehouse)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                            'quantity' => $quantity,
                        ]);
                    } elseif ($item->stock_reserved) {
                        $warehouseStock->releaseStock($quantity);
                        \Log::info('✅ RELEASED (single warehouse)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                        ]);
                    }

                    $item->update([
                        'stock_deducted' => false,
                        'stock_deducted_at' => null,
                        'stock_reserved' => false,
                        'stock_reserved_at' => null,
                        'status_key_code' => 'ITEM_CANCELLED',
                    ]);
                }
            }
        }
    }

    /**
     * Handle payment failure for online orders
     */
    public function paymentFailed(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|uuid|exists:transactions,id',
                'error_message' => 'nullable|string',
                'error_code' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $transaction = Transaction::findOrFail($validated['transaction_id']);
            $order = $transaction->order;

            // Update transaction
            $transaction->markAsFailed(
                $validated['error_message'] ?? 'Payment failed',
                $validated['error_code'] ?? null
            );

            // Update order
            $order->update([
                'payment_status_key_code' => 'PAYMENT_FAILED',
            ]);

            DB::commit();

            // ✅ SEND PAYMENT FAILED NOTIFICATION
            $this->notificationService->notify('payment_failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reason' => $validated['error_message'] ?? 'Payment gateway error',  // ← Added
            ]);
            $this->notificationService->notifyCustomer('payment_failed', $order);

            return response()->json([
                'success' => true,
                'message' => 'Payment failure recorded',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'transaction_id' => $transaction->id,
                    'can_retry' => $transaction->canRetry(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment failure',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
