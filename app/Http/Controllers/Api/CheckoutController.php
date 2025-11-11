<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
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
                'payment_method' => 'required|string',
                'customer_notes' => 'nullable|string',
                'coupon_code' => 'nullable|string',
            ]);

            // Get cart
            $cart = Cache::get("cart:{$validated['cart_id']}", []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty',
                ], 400);
            }

            DB::beginTransaction();

            // Verify stock availability
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);

                if ($product->track_inventory && $product->stock_quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name}",
                        'product' => $product->name,
                        'available' => $product->stock_quantity,
                        'requested' => $item['quantity'],
                    ], 400);
                }
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $taxRate = 10; // Get from settings
            $taxAmount = $subtotal * ($taxRate / 100);
            $shippingAmount = $this->calculateShipping($validated['shipping_method'], $subtotal);
            $discountAmount = 0; // TODO: Calculate from coupon
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // Create order
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
                'customer_notes' => $validated['customer_notes'] ?? null,

                'order_source' => 'web',
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'discount_code' => $validated['coupon_code'] ?? null,
                'total_amount' => $totalAmount,

                'status_key_code' => 'ORDER_PENDING',
                'payment_status_key_code' => 'PAYMENT_PENDING',

                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            $order = Order::create($orderData);

            // Create order items
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);

                $itemSubtotal = $item['price'] * $item['quantity'];
                $itemTaxAmount = $item['is_taxable'] ? ($itemSubtotal * ($item['tax_rate'] / 100)) : 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_description' => $product->short_description,
                    'product_image' => $product->main_image,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'cost_price' => $product->cost_price,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTaxAmount,
                    'tax_rate' => $item['tax_rate'],
                    'is_taxable' => $item['is_taxable'],
                    'total' => $itemSubtotal + $itemTaxAmount,
                    'status_key_code' => 'ITEM_PENDING',
                ]);
            }

            // Clear cart
            Cache::forget("cart:{$validated['cart_id']}");

            DB::commit();

            // ✅ ADD THIS NOTIFICATION TRIGGER HERE:
            app(\App\Services\NotificationService::class)->notify('order_created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                'customer_name' => $order->customer_id
                    ? $order->customer->getFullName()
                    : ($validated['guest_name'] ?? $validated['shipping_first_name'] . ' ' . $validated['shipping_last_name']),
                'customer_email' => $order->customer_id
                    ? $order->customer->email
                    : ($validated['guest_email'] ?? ''),
                'customer_phone' => $validated['guest_phone'] ?? $validated['shipping_phone'],
                'customer_type' => $order->customer_id ? 'returning' : 'new',
                'items_count' => count($cart),
                'payment_method' => ucfirst($validated['payment_method']),
                'payment_status' => 'pending',
                'shipping_method' => ucfirst($validated['shipping_method']),
            ]);

            // ✅ CHECK IF HIGH-VALUE ORDER:
            if ($order->total_amount >= 500) { // Or use config('notifications.thresholds.high_value_order', 500)
                app(\App\Services\NotificationService::class)->notify('customer_high_value_order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_id
                        ? $order->customer->getFullName()
                        : ($validated['guest_name'] ?? ''),
                    'total_amount' => $order->currency . ' ' . number_format($order->total_amount, 2),
                    'customer_id' => $order->customer_id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'payment_required' => true,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate shipping cost
     *
     * @param string $method
     * @param float $subtotal
     * @return float
     */
    private function calculateShipping(string $method, float $subtotal): float
    {
        // TODO: Implement actual shipping calculation
        $rates = [
            'standard' => 5.00,
            'express' => 15.00,
            'overnight' => 30.00,
            'free' => 0.00,
        ];

        // Free shipping for orders over $100
        if ($subtotal >= 100) {
            return 0.00;
        }

        return $rates[$method] ?? 5.00;
    }
}
