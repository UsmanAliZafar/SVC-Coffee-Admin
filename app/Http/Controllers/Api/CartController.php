<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    /**
     * Get cart contents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cartId = $request->input('cart_id') ?? $request->header('X-Cart-ID');

            if (!$cartId) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart is empty',
                    'data' => [
                        'items' => [],
                        'totals' => $this->calculateTotals([]),
                    ],
                ]);
            }

            $cart = Cache::get("cart:{$cartId}", []);
            $cartMeta = Cache::get("cart_meta:{$cartId}", []);

            // Refresh product data and validate stock
            $cart = $this->refreshCartData($cart);

            $totals = isset($cartMeta['coupon'])
                ? $this->calculateTotalsWithCoupon($cart, $cartMeta)
                : $this->calculateTotals($cart);

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'cart_id' => $cartId,
                    'items' => array_values($cart),
                    'coupon' => $cartMeta['coupon'] ?? null,
                    'totals' => $totals,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add item to cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addItem(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cart_id' => 'nullable|string',
                'product_id' => 'required|uuid|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'variant_id' => 'nullable|uuid|exists:product_variants,id',
            ]);

            $cartId = $validated['cart_id'] ?? \Str::uuid();
            $cart = Cache::get("cart:{$cartId}", []);
            $cartMeta = Cache::get("cart_meta:{$cartId}", []);

            $product = Product::with(['images'])->findOrFail($validated['product_id']);

            // Check stock availability
            if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available',
                    'available_quantity' => $product->stock_quantity,
                ], 400);
            }

            // Check if product already in cart
            $itemKey = $validated['product_id'] . ($validated['variant_id'] ?? '');

            if (isset($cart[$itemKey])) {
                $cart[$itemKey]['quantity'] += $validated['quantity'];
            } else {
                $cart[$itemKey] = [
                    'product_id' => $product->id,
                    'variant_id' => $validated['variant_id'] ?? null,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'image' => $product->getMainImageUrl(),
                    'price' => $product->getFinalPrice(),
                    'regular_price' => (float) $product->price,
                    'product_currency' => $product->curency ?? 'USD', // Get from config
                    'quantity' => $validated['quantity'],
                    'is_taxable' => $product->is_taxable,
                    'tax_rate' => $product->tax_percentage ?? 0,
                    'max_quantity' => $product->track_inventory ? $product->stock_quantity : 999,
                    'added_at' => now()->toIso8601String(),
                ];
            }

            // Save cart (expires in 7 days)
            Cache::put("cart:{$cartId}", $cart, now()->addDays(7));

            // Recalculate totals
            $totals = isset($cartMeta['coupon'])
                ? $this->calculateTotalsWithCoupon($cart, $cartMeta)
                : $this->calculateTotals($cart);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => [
                    'cart_id' => $cartId,
                    'items' => array_values($cart),
                    'coupon' => $cartMeta['coupon'] ?? null,
                    'totals' => $totals,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateItem(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cart_id' => 'required|string',
                'product_id' => 'required|uuid',
                'variant_id' => 'nullable|uuid',
                'quantity' => 'required|integer|min:0',
            ]);

            $cart = Cache::get("cart:{$validated['cart_id']}", []);
            $cartMeta = Cache::get("cart_meta:{$validated['cart_id']}", []);
            $itemKey = $validated['product_id'] . ($validated['variant_id'] ?? '');

            if (!isset($cart[$itemKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart',
                ], 404);
            }

            if ($validated['quantity'] === 0) {
                // Remove item if quantity is 0
                unset($cart[$itemKey]);
            } else {
                // Check stock
                $product = Product::find($validated['product_id']);
                if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock available',
                        'available_quantity' => $product->stock_quantity,
                    ], 400);
                }

                $cart[$itemKey]['quantity'] = $validated['quantity'];
            }

            Cache::put("cart:{$validated['cart_id']}", $cart, now()->addDays(7));

            // Revalidate coupon if exists
            if (isset($cartMeta['coupon'])) {
                $cartMeta = $this->revalidateCoupon($cart, $cartMeta, $validated['cart_id']);
            }

            $totals = isset($cartMeta['coupon'])
                ? $this->calculateTotalsWithCoupon($cart, $cartMeta)
                : $this->calculateTotals($cart);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'items' => array_values($cart),
                    'coupon' => $cartMeta['coupon'] ?? null,
                    'totals' => $totals,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove item from cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeItem(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cart_id' => 'required|string',
                'product_id' => 'required|uuid',
                'variant_id' => 'nullable|uuid',
            ]);

            $cart = Cache::get("cart:{$validated['cart_id']}", []);
            $cartMeta = Cache::get("cart_meta:{$validated['cart_id']}", []);
            $itemKey = $validated['product_id'] . ($validated['variant_id'] ?? '');

            if (!isset($cart[$itemKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart',
                ], 404);
            }

            unset($cart[$itemKey]);
            Cache::put("cart:{$validated['cart_id']}", $cart, now()->addDays(7));

            // Revalidate coupon if exists
            if (isset($cartMeta['coupon'])) {
                $cartMeta = $this->revalidateCoupon($cart, $cartMeta, $validated['cart_id']);
            }

            $totals = isset($cartMeta['coupon'])
                ? $this->calculateTotalsWithCoupon($cart, $cartMeta)
                : $this->calculateTotals($cart);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'items' => array_values($cart),
                    'coupon' => $cartMeta['coupon'] ?? null,
                    'totals' => $totals,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $cartId = $request->input('cart_id');

            if ($cartId) {
                Cache::forget("cart:{$cartId}");
                Cache::forget("cart_meta:{$cartId}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply coupon code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cart_id' => 'required|string',
                'coupon_code' => 'required|string|max:50',
                'customer_id' => 'nullable|uuid',
                'customer_email' => 'nullable|email',
            ]);

            $cart = Cache::get("cart:{$validated['cart_id']}", []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty',
                ], 400);
            }

            // Find the coupon
            $coupon = Coupon::byCode($validated['coupon_code'])
                ->active()
                ->valid()
                ->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon code',
                ], 404);
            }

            // Validate coupon basic validity
            if (!$coupon->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon is no longer valid',
                ], 400);
            }

            // Check customer eligibility
            $customerCheck = $coupon->canBeUsedByCustomer(
                $validated['customer_id'] ?? null,
                $validated['customer_email'] ?? null
            );

            if (!$customerCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $customerCheck['message'],
                ], 400);
            }

            // Calculate cart totals
            $cartTotals = $this->calculateTotals($cart);

            // Check cart applicability
            $cartCheck = $coupon->isApplicableToCart(
                $cart,
                $cartTotals['subtotal'],
                $cartTotals['total_items']
            );

            if (!$cartCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $cartCheck['message'],
                ], 400);
            }

            // Calculate discount
            $discountDetails = $coupon->calculateDiscount($cart, $cartTotals['subtotal']);

            // Store coupon in cart
            $cartData = Cache::get("cart:{$validated['cart_id']}", []);
            $cartMeta = Cache::get("cart_meta:{$validated['cart_id']}", []);

            $cartMeta['coupon'] = [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_amount' => $discountDetails['discount_amount'],
                'free_shipping' => $discountDetails['free_shipping'],
                'applied_at' => now()->toIso8601String(),
            ];

            Cache::put("cart_meta:{$validated['cart_id']}", $cartMeta, now()->addDays(7));

            // Recalculate totals with coupon
            $newTotals = $this->calculateTotalsWithCoupon($cartData, $cartMeta);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully!',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'coupon' => $cartMeta['coupon'],
                    'totals' => $newTotals,
                    'savings' => round($discountDetails['discount_amount'], 2),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove coupon from cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cart_id' => 'required|string',
            ]);

            $cart = Cache::get("cart:{$validated['cart_id']}", []);
            $cartMeta = Cache::get("cart_meta:{$validated['cart_id']}", []);

            if (!isset($cartMeta['coupon'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No coupon applied to cart',
                ], 400);
            }

            unset($cartMeta['coupon']);
            Cache::put("cart_meta:{$validated['cart_id']}", $cartMeta, now()->addDays(7));

            $totals = $this->calculateTotals($cart);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'items' => array_values($cart),
                    'totals' => $totals,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate cart totals
     *
     * @param array $cart
     * @return array
     */
    private function calculateTotals(array $cart): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $totalItems = 0;

        foreach ($cart as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;
            $totalItems += $item['quantity'];

            if ($item['is_taxable']) {
                $taxAmount += $itemSubtotal * ($item['tax_rate'] / 100);
            }
        }

        $total = $subtotal + $taxAmount;

        return [
            'subtotal' => format_amount($subtotal),
            'tax_amount' => format_amount($taxAmount),
            'shipping_amount' => 0, // Calculate based on shipping method
            'discount_amount' => 0,
            'total_amount' => format_amount($total),
            'total_items' => $totalItems,
            'currency' => store_currency_symbol(), // Get from config
        ];
    }

    /**
     * Calculate cart totals with coupon applied
     *
     * @param array $cart
     * @param array $cartMeta
     * @return array
     */
    private function calculateTotalsWithCoupon(array $cart, array $cartMeta): array
    {
        $totals = $this->calculateTotals($cart);

        if (isset($cartMeta['coupon'])) {
            $couponDiscount = $cartMeta['coupon']['discount_amount'];
            $totals['discount_amount'] = round($couponDiscount, 2);

            // Apply free shipping if applicable
            if ($cartMeta['coupon']['free_shipping']) {
                $totals['shipping_amount'] = 0;
                $totals['free_shipping_applied'] = true;
            }

            // Recalculate total
            $totals['total_amount'] = round(
                $totals['subtotal'] + $totals['tax_amount'] + $totals['shipping_amount'] - $totals['discount_amount'],
                2
            );

            // Ensure total doesn't go negative
            if ($totals['total_amount'] < 0) {
                $totals['total_amount'] = 0;
            }
        }

        return $totals;
    }

    /**
     * Refresh cart data with latest product info
     *
     * @param array $cart
     * @return array
     */
    private function refreshCartData(array $cart): array
    {
        $productIds = array_column($cart, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $key => &$item) {
            $product = $products->get($item['product_id']);

            if (!$product || !$product->isAvailableForPurchase()) {
                // Remove unavailable products
                unset($cart[$key]);
                continue;
            }

            // Update price and stock info
            $item['price'] = $product->getFinalPrice();
            $item['regular_price'] = (float) $product->price;
            $item['max_quantity'] = $product->track_inventory ? $product->stock_quantity : 999;
            $item['is_in_stock'] = $product->isInStock();
        }

        return $cart;
    }

    /**
     * Revalidate coupon when cart changes
     *
     * @param array $cart
     * @param array $cartMeta
     * @param string $cartId
     * @return array
     */
    private function revalidateCoupon(array $cart, array $cartMeta, string $cartId): array
    {
        if (!isset($cartMeta['coupon'])) {
            return $cartMeta;
        }

        $coupon = Coupon::find($cartMeta['coupon']['id']);

        if (!$coupon || !$coupon->isValid()) {
            // Remove invalid coupon
            unset($cartMeta['coupon']);
            Cache::put("cart_meta:{$cartId}", $cartMeta, now()->addDays(7));
            return $cartMeta;
        }

        // Recalculate discount
        $cartTotals = $this->calculateTotals($cart);
        $cartCheck = $coupon->isApplicableToCart(
            $cart,
            $cartTotals['subtotal'],
            $cartTotals['total_items']
        );

        if (!$cartCheck['valid']) {
            // Remove inapplicable coupon
            unset($cartMeta['coupon']);
            Cache::put("cart_meta:{$cartId}", $cartMeta, now()->addDays(7));
            return $cartMeta;
        }

        // Update discount amount
        $discountDetails = $coupon->calculateDiscount($cart, $cartTotals['subtotal']);
        $cartMeta['coupon']['discount_amount'] = $discountDetails['discount_amount'];

        Cache::put("cart_meta:{$cartId}", $cartMeta, now()->addDays(7));

        return $cartMeta;
    }
}
