<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction; // ✅ ADD THIS
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckoutController extends Controller
{
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
            try {
                $shippingAmount = $this->calculateShipping(
                    $validated['shipping_method'],
                    $subtotal,
                    $itemCount,
                    $totalWeight,
                    $totalVolume
                );
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_type' => 'shipping_calculation_error'
                ], 400);
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

                // Get warehouse stock
                $warehouseStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->where('warehouse_id', $defaultWarehouse->id)
                    ->first();

                if (!$warehouseStock) {
                    DB::rollBack();

                    $itemName = $variant
                        ? "{$product->name} ({$variant->getFullName()})"
                        : $product->name;

                    return response()->json([
                        'success' => false,
                        'message' => "Stock record not found for {$itemName}",
                    ], 400);
                }

                // ✅ Check available stock (considering reservations)
                if ($warehouseStock->available_quantity < $item['quantity']) {
                    DB::rollBack();

                    $itemName = $variant
                        ? "{$product->name} ({$variant->getFullName()})"
                        : $product->name;

                    \Log::error('❌ Insufficient stock', [
                        'product' => $product->name,
                        'variant' => $variant ? $variant->getFullName() : null,
                        'requested' => $item['quantity'],
                        'available' => $warehouseStock->available_quantity,
                        'warehouse_qty' => $warehouseStock->quantity,
                        'reserved' => $warehouseStock->reserved_quantity,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$itemName}",
                        'details' => [
                            'product' => $itemName,
                            'requested' => $item['quantity'],
                            'available' => $warehouseStock->available_quantity,
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
                'payment_method' => $validated['payment_method'],
                'payment_gateway' => $validated['payment_gateway'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,

                'order_source' => 'web',
                'currency' => $cart[array_key_first($cart)]['product_currency'] ?? 'USD',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'discount_code' => $couponDetails['code'] ?? null,
                'total_amount' => $totalAmount,

                // ✅ Status: Always start as PENDING (customer placed order)
                'status_key_code' => 'ORDER_PENDING',
                'payment_status_key_code' => 'PAYMENT_PENDING',

                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
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
            // RESERVE STOCK (STAGE 1) ✅
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

                $warehouseStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->where('warehouse_id', $defaultWarehouse->id)
                    ->first();

                if ($warehouseStock && $warehouseStock->reserveStock($item['quantity'])) {
                    // Update order item
                    OrderItem::where('order_id', $order->id)
                        ->where('product_id', $product->id)
                        ->where('product_variant_id', $variant ? $variant->id : null)
                        ->update([
                            'stock_reserved' => true,
                            'stock_reserved_at' => now(),
                        ]);

                    $itemName = $variant
                        ? "{$product->name} ({$variant->getFullName()})"
                        : $product->name;

                    \Log::info('🔒 RESERVED (Web Order)', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'quantity' => $item['quantity'],
                        'warehouse' => $defaultWarehouse->name,
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

            // Clear cart
            Cache::forget("cart:{$validated['cart_id']}");
            Cache::forget("cart_meta:{$validated['cart_id']}");

            DB::commit();

            // ============================================================
            // NOTIFICATIONS
            // ============================================================
            app(\App\Services\NotificationService::class)->notify('order_created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
                'items_count' => count($cart),
                'payment_method' => ucfirst($validated['payment_method']),
            ]);

            // High-value order notification
            if ($order->total_amount >= 500) {
                app(\App\Services\NotificationService::class)->notify('customer_high_value_order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->getCustomerName(),
                    'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                ]);
            }

            app(\App\Services\NotificationService::class)->notifyCustomer('order_created', $order);

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
     * ✅ NEW METHOD: Create transaction entry
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
     * Calculate shipping cost based on store settings and order details
     *
     * @param string $method - Shipping method selected by customer
     * @param float $subtotal - Order subtotal
     * @param int $itemCount - Total number of items
     * @param float $totalWeight - Total order weight in kg (optional)
     * @param float $totalVolume - Total order volume in liters (optional)
     * @return float
     */
    private function calculateShipping(
        string $method,
        float $subtotal,
        int $itemCount = 0,
        float $totalWeight = 0,
        float $totalVolume = 0
    ): float
    {
        try {
            // Get store settings
            $settings = \App\Models\StoreSetting::getSettings();

            // Check if shipping is disabled
            if (!$settings->shipping_enabled) {
                return 0.00;
            }

            // Check for free shipping threshold
            if ($settings->free_shipping_threshold && $subtotal >= $settings->free_shipping_threshold) {
                \Log::info('Free shipping applied due to threshold', [
                    'subtotal' => $subtotal,
                    'threshold' => $settings->free_shipping_threshold
                ]);
                return 0.00;
            }

            // Check minimum order requirement
            if ($settings->minimum_order_for_shipping && $subtotal < $settings->minimum_order_for_shipping) {
                \Log::warning('Order does not meet minimum shipping requirement', [
                    'subtotal' => $subtotal,
                    'minimum_required' => $settings->minimum_order_for_shipping
                ]);
                throw new \Exception("Minimum order value of {$settings->currency_symbol}{$settings->minimum_order_for_shipping} required for shipping");
            }

            // Check if order exceeds shipping limits
            $limitCheck = $settings->exceedsShippingLimits($totalWeight, $totalVolume);
            if ($limitCheck['exceeds']) {
                \Log::warning('Order exceeds shipping limits', $limitCheck);
                throw new \Exception("Order exceeds maximum {$limitCheck['type']} limit of {$limitCheck['limit']}");
            }

            $shippingCost = 0;

            // Calculate based on shipping calculation type
            switch ($settings->shipping_calculation_type) {
                case 'flat_rate':
                    $shippingCost = $this->calculateFlatRate($settings, $method);
                    break;

                case 'per_kg':
                    $shippingCost = $this->calculatePerKilogram($settings, $totalWeight);
                    break;

                case 'per_liter':
                    $shippingCost = $this->calculatePerLiter($settings, $totalVolume);
                    break;

                case 'per_item':
                    $shippingCost = $this->calculatePerItem($settings, $itemCount);
                    break;

                case 'tiered':
                    $shippingCost = $this->calculateTieredShipping($settings, $subtotal, $totalWeight);
                    break;

                default:
                    $shippingCost = $settings->default_shipping_cost;
                    \Log::info('Using default shipping cost', [
                        'cost' => $shippingCost,
                        'method' => $method
                    ]);
            }

            // Add handling fee
            if ($settings->handling_fee) {
                $shippingCost += $settings->handling_fee;
            }

            // Ensure non-negative
            $shippingCost = max(0, $shippingCost);

            \Log::info('Shipping cost calculated', [
                'method' => $method,
                'calculation_type' => $settings->shipping_calculation_type,
                'base_cost' => $shippingCost - ($settings->handling_fee ?? 0),
                'handling_fee' => $settings->handling_fee ?? 0,
                'total_cost' => $shippingCost,
                'subtotal' => $subtotal,
                'weight' => $totalWeight,
                'volume' => $totalVolume,
                'items' => $itemCount
            ]);

            return round($shippingCost, 2);

        } catch (\Exception $e) {
            \Log::error('Shipping calculation error', [
                'error' => $e->getMessage(),
                'method' => $method,
                'subtotal' => $subtotal,
                'weight' => $totalWeight,
                'volume' => $totalVolume,
                'items' => $itemCount
            ]);

            // Return default shipping cost on error
            return \App\Models\StoreSetting::get('default_shipping_cost', 5.00);
        }
    }

    /**
     * Calculate flat rate shipping
     *
     * @param \App\Models\StoreSetting $settings
     * @param string $method
     * @return float
     */
    private function calculateFlatRate(\App\Models\StoreSetting $settings, string $method): float
    {
        // Check if nationwide flat rate is enabled
        if ($settings->enable_nationwide_flat_rate && $settings->nationwide_flat_rate) {
            \Log::info('Using nationwide flat rate', [
                'rate' => $settings->nationwide_flat_rate
            ]);
            return $settings->nationwide_flat_rate;
        }

        // Method-specific rates (if you want different rates for standard/express/overnight)
        $methodRates = [
            'standard' => $settings->default_shipping_cost,
            'express' => $settings->default_shipping_cost * 2, // 2x for express
            'overnight' => $settings->default_shipping_cost * 3, // 3x for overnight
            'free' => 0.00,
        ];

        return $methodRates[$method] ?? $settings->default_shipping_cost;
    }

    /**
     * Calculate per kilogram shipping
     *
     * @param \App\Models\StoreSetting $settings
     * @param float $totalWeight
     * @return float
     */
    private function calculatePerKilogram(\App\Models\StoreSetting $settings, float $totalWeight): float
    {
        if (!$totalWeight || !$settings->shipping_rate_per_kg) {
            \Log::warning('Weight-based shipping requested but weight or rate not available', [
                'weight' => $totalWeight,
                'rate_per_kg' => $settings->shipping_rate_per_kg
            ]);
            return $settings->default_shipping_cost;
        }

        // Check maximum weight limit
        if ($settings->max_weight_standard_shipping && $totalWeight > $settings->max_weight_standard_shipping) {
            \Log::warning('Order exceeds maximum weight for standard shipping', [
                'weight' => $totalWeight,
                'max_weight' => $settings->max_weight_standard_shipping
            ]);
            throw new \Exception("Order weight ({$totalWeight}kg) exceeds maximum limit of {$settings->max_weight_standard_shipping}kg for standard shipping");
        }

        $cost = $totalWeight * $settings->shipping_rate_per_kg;

        \Log::info('Per-kg shipping calculated', [
            'weight' => $totalWeight,
            'rate_per_kg' => $settings->shipping_rate_per_kg,
            'cost' => $cost
        ]);

        return $cost;
    }

    /**
     * Calculate per liter shipping
     *
     * @param \App\Models\StoreSetting $settings
     * @param float $totalVolume
     * @return float
     */
    private function calculatePerLiter(\App\Models\StoreSetting $settings, float $totalVolume): float
    {
        if (!$totalVolume || !$settings->shipping_rate_per_liter) {
            \Log::warning('Volume-based shipping requested but volume or rate not available', [
                'volume' => $totalVolume,
                'rate_per_liter' => $settings->shipping_rate_per_liter
            ]);
            return $settings->default_shipping_cost;
        }

        // Check maximum volume limit
        if ($settings->max_volume_standard_shipping && $totalVolume > $settings->max_volume_standard_shipping) {
            \Log::warning('Order exceeds maximum volume for standard shipping', [
                'volume' => $totalVolume,
                'max_volume' => $settings->max_volume_standard_shipping
            ]);
            throw new \Exception("Order volume ({$totalVolume}L) exceeds maximum limit of {$settings->max_volume_standard_shipping}L for standard shipping");
        }

        $cost = $totalVolume * $settings->shipping_rate_per_liter;

        \Log::info('Per-liter shipping calculated', [
            'volume' => $totalVolume,
            'rate_per_liter' => $settings->shipping_rate_per_liter,
            'cost' => $cost
        ]);

        return $cost;
    }

    /**
     * Calculate per item shipping
     *
     * @param \App\Models\StoreSetting $settings
     * @param int $itemCount
     * @return float
     */
    private function calculatePerItem(\App\Models\StoreSetting $settings, int $itemCount): float
    {
        if (!$itemCount || !$settings->shipping_rate_per_item) {
            \Log::warning('Item-based shipping requested but item count or rate not available', [
                'item_count' => $itemCount,
                'rate_per_item' => $settings->shipping_rate_per_item
            ]);
            return $settings->default_shipping_cost;
        }

        $cost = $itemCount * $settings->shipping_rate_per_item;

        \Log::info('Per-item shipping calculated', [
            'item_count' => $itemCount,
            'rate_per_item' => $settings->shipping_rate_per_item,
            'cost' => $cost
        ]);

        return $cost;
    }

    /**
     * Calculate tiered shipping based on order total or weight
     *
     * @param \App\Models\StoreSetting $settings
     * @param float $orderTotal
     * @param float $totalWeight
     * @return float
     */
    private function calculateTieredShipping(
        \App\Models\StoreSetting $settings,
        float $orderTotal,
        float $totalWeight
    ): float
    {
        $tieredRates = $settings->tiered_shipping_rates;

        if (!$tieredRates || empty($tieredRates)) {
            \Log::warning('Tiered shipping selected but no tiers configured');
            return $settings->default_shipping_cost;
        }

        // Sort tiers by threshold (ascending)
        $tiers = collect($tieredRates)->sortBy('threshold');

        $applicableRate = $settings->default_shipping_cost;
        $appliedTier = null;

        foreach ($tiers as $tier) {
            $threshold = $tier['threshold'] ?? 0;
            $rate = $tier['rate'] ?? 0;
            $type = $tier['type'] ?? 'order_total'; // 'order_total' or 'weight'

            if ($type === 'order_total' && $orderTotal >= $threshold) {
                $applicableRate = $rate;
                $appliedTier = $tier;
            } elseif ($type === 'weight' && $totalWeight >= $threshold) {
                $applicableRate = $rate;
                $appliedTier = $tier;
            }
        }

        \Log::info('Tiered shipping calculated', [
            'order_total' => $orderTotal,
            'total_weight' => $totalWeight,
            'applied_tier' => $appliedTier,
            'rate' => $applicableRate
        ]);

        return $applicableRate;
    }

    /**
     * Confirm payment for online orders
     * ✅ UPDATED: Triggers stock deduction via status change
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

            $transaction = Transaction::with('order.items.product', 'order.items.variant')->findOrFail($validated['transaction_id']);
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
                'status_key_code' => 'ORDER_CONFIRMED', // ← This triggers stock deduction
                'confirmed_at' => now(),
            ]);

            // ✅ HANDLE STOCK: PENDING → CONFIRMED (Reserve → Deduct)
            $this->handleStatusChange($order, $oldStatus, 'ORDER_CONFIRMED');

            DB::commit();

            // Notifications
            app(\App\Services\NotificationService::class)->notify('payment_confirmed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_number' => $transaction->transaction_number,
                'amount' => $transaction->getFormattedAmount(),
            ]);

            app(\App\Services\NotificationService::class)->notifyCustomer('order_confirmed', $order);

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
            $warehouseId = $item->warehouse_id;

            if (!$warehouseId) {
                $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();
                if (!$defaultWarehouse) {
                    \Log::error('❌ No warehouse', ['item' => $item->id]);
                    continue;
                }
                $warehouseId = $defaultWarehouse->id;
                $item->update(['warehouse_id' => $warehouseId]);
            }

            $warehouseStock = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                ->where('variant_id', $variant ? $variant->id : null)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (!$warehouseStock) {
                \Log::error('❌ Warehouse stock not found', [
                    'product' => $product->name,
                ]);
                continue;
            }

            $itemName = $variant ? "{$product->name} ({$variant->getFullName()})" : $product->name;

            // ============================================================
            // PENDING → CONFIRMED: Convert reservation to deduction ⚡
            // ============================================================
            if ($newStatus === 'ORDER_CONFIRMED' && $oldStatus === 'ORDER_PENDING') {
                if ($item->stock_reserved && !$item->stock_deducted) {
                    // Release reservation
                    $warehouseStock->releaseStock($quantity);

                    // Deduct actual stock
                    $warehouseStock->reduceStock($quantity);

                    $item->update([
                        'stock_reserved' => false,
                        'stock_reserved_at' => null,
                        'stock_deducted' => true,
                        'stock_deducted_at' => now(),
                    ]);

                    \Log::info('⚡ DEDUCTED (Web Order Confirmed)', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'quantity' => $quantity,
                        'warehouse_qty' => $warehouseStock->fresh()->quantity,
                    ]);
                }
            }

            // ============================================================
            // ANY → CANCELLED: Restore stock
            // ============================================================
            elseif ($newStatus === 'ORDER_CANCELLED') {
                if ($item->stock_deducted) {
                    $warehouseStock->addStock($quantity);

                    $item->update([
                        'stock_deducted' => false,
                        'stock_deducted_at' => null,
                        'status_key_code' => 'ITEM_CANCELLED',
                    ]);

                    \Log::info('✅ RESTORED (Web Order Cancelled)', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'quantity' => $quantity,
                    ]);
                } elseif ($item->stock_reserved) {
                    $warehouseStock->releaseStock($quantity);

                    $item->update([
                        'stock_reserved' => false,
                        'stock_reserved_at' => null,
                        'status_key_code' => 'ITEM_CANCELLED',
                    ]);

                    \Log::info('✅ RELEASED (Web Order Cancelled)', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                    ]);
                }
            }
        }
    }

    /**
     * ✅ Handle payment failure for online orders
     *
     * @param Request $request
     * @return JsonResponse
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
            app(\App\Services\NotificationService::class)->notify('payment_failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_number' => $transaction->transaction_number,
                'error_message' => $validated['error_message'] ?? 'Payment failed',
            ]);

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
