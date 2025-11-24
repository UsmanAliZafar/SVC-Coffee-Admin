<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
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
                // ✅ Load variant if provided
                $variant = null;
                if (!empty($validated['variant_id'])) {
                    $variant = ProductVariant::find($validated['variant_id']);

                    if (!$variant || $variant->product_id !== $product->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid variant for this product',
                        ], 400);
                    }
                }

                // ✅ Check stock (variant takes priority)
                if ($variant) {
                    // Check variant stock
                    if ($product->track_inventory && $variant->stock_quantity < $validated['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock available for this variant',
                            'available_quantity' => $variant->stock_quantity,
                        ], 400);
                    }
                } else {
                    // Check main product stock (existing code)
                    if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock available',
                            'available_quantity' => $product->stock_quantity,
                        ], 400);
                    }
                }

                // ✅ Use variant data if available, otherwise use product data
                $cart[$itemKey] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'name' => $variant ? "{$product->name} - {$variant->getFullName()}" : $product->name,
                    'slug' => $product->slug,
                    'sku' => $variant ? $variant->sku : $product->sku,
                    'image' => $variant ? $variant->getImageUrl() : $product->getMainImageUrl(),
                    'price' => $variant ? $variant->getFinalPrice() : $product->getFinalPrice(),
                    'regular_price' => $variant ? (float) $variant->price : (float) $product->price,
                    'product_currency' => $product->curency ?? 'USD',
                    'quantity' => $validated['quantity'],
                    'is_taxable' => $product->is_taxable,
                    'tax_rate' => $product->tax_percentage ?? 0,
                    'max_quantity' => $variant
                        ? ($product->track_inventory ? $variant->stock_quantity : 999)
                        : ($product->track_inventory ? $product->stock_quantity : 999),
                    'variant_name' => $variant ? $variant->getFullName() : null, // ← NEW
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
                $variant = !empty($validated['variant_id']) ? ProductVariant::find($validated['variant_id']) : null;

                if ($variant) {
                    // Check variant stock
                    if ($product->track_inventory && $variant->stock_quantity < $validated['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock available for this variant',
                            'available_quantity' => $variant->stock_quantity,
                        ], 400);
                    }
                } else {
                    // Check main product stock
                    if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient stock available',
                            'available_quantity' => $product->stock_quantity,
                        ], 400);
                    }
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
     * ✅ PROPER TYPE HANDLING
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

            // Calculate cart totals (now returns numeric values)
            $cartTotals = $this->calculateTotals($cart);

            // ✅ PREPARE CART ITEMS ARRAY WITH PROPER TYPES
            $cartItemsForCoupon = [];
            foreach ($cart as $item) {
                $cartItemsForCoupon[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $item['variant_id'] ?? null,
                    'price' => is_numeric($item['price']) ? (float) $item['price'] : 0.0,
                    'quantity' => is_numeric($item['quantity']) ? (int) $item['quantity'] : 0,
                ];
            }

            // ✅ ENSURE NUMERIC VALUES
            $subtotal = is_numeric($cartTotals['subtotal']) ? (float) $cartTotals['subtotal'] : 0.0;
            $totalItems = is_numeric($cartTotals['total_items']) ? (int) $cartTotals['total_items'] : 0;

            // Check cart applicability
            $cartCheck = $coupon->isApplicableToCart(
                $cartItemsForCoupon,
                $subtotal,
                $totalItems
            );

            if (!$cartCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $cartCheck['message'],
                ], 400);
            }

            // Calculate discount
            $discountDetails = $coupon->calculateDiscount($cartItemsForCoupon, $subtotal);

            // ✅ ENSURE DISCOUNT AMOUNT IS NUMERIC
            $discountAmount = isset($discountDetails['discount_amount']) && is_numeric($discountDetails['discount_amount'])
                ? (float) $discountDetails['discount_amount']
                : 0.0;

            // Store coupon in cart metadata
            $cartMeta = Cache::get("cart_meta:{$validated['cart_id']}", []);

            $cartMeta['coupon'] = [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_amount' => $discountAmount, // ✅ NUMERIC VALUE
                'free_shipping' => $discountDetails['free_shipping'] ?? false,
                'applied_at' => now()->toIso8601String(),
            ];

            Cache::put("cart_meta:{$validated['cart_id']}", $cartMeta, now()->addDays(7));

            // Recalculate totals with coupon
            $newTotals = $this->calculateTotalsWithCoupon($cart, $cartMeta);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully!',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'coupon' => [
                        'code' => $coupon->code,
                        'name' => $coupon->name,
                        'discount_type' => $coupon->discount_type,
                        'discount_amount' => $discountAmount,
                        'formatted_discount' => format_amount($discountAmount),
                    ],
                    'totals' => $newTotals,
                    'savings' => round($discountAmount, 2),
                    'formatted_savings' => format_amount($discountAmount),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            \Log::error('Coupon application failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'code' => $validated['coupon_code'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again',
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
     * ✅ RETURNS NUMERIC VALUES, NOT FORMATTED STRINGS
     *
     * @param array $cart
     * @return array
     */
    private function calculateTotals(array $cart): array
    {
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $totalItems = 0;

        foreach ($cart as $item) {
            // ✅ ENSURE ALL VALUES ARE NUMERIC
            $price = is_numeric($item['price']) ? (float) $item['price'] : 0.0;
            $quantity = is_numeric($item['quantity']) ? (int) $item['quantity'] : 0;
            $taxRate = isset($item['tax_rate']) && is_numeric($item['tax_rate']) ? (float) $item['tax_rate'] : 0.0;

            $itemSubtotal = $price * $quantity;
            $subtotal += $itemSubtotal;
            $totalItems += $quantity;

            if (isset($item['is_taxable']) && $item['is_taxable']) {
                $taxAmount += $itemSubtotal * ($taxRate / 100);
            }
        }

        $total = $subtotal + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2), // ✅ NUMERIC, NOT FORMATTED
            'tax_amount' => round($taxAmount, 2),
            'shipping_amount' => 0.0,
            'discount_amount' => 0.0,
            'total_amount' => round($total, 2),
            'total_items' => $totalItems,
            'currency' => store_currency_symbol(),
            // ✅ ADD FORMATTED VERSIONS FOR DISPLAY
            'formatted' => [
                'subtotal' => format_amount($subtotal),
                'tax_amount' => format_amount($taxAmount),
                'shipping_amount' => format_amount(0),
                'discount_amount' => format_amount(0),
                'total_amount' => format_amount($total),
            ]
        ];
    }

    /**
     * Calculate cart totals with coupon applied
     * ✅ WORKS WITH NUMERIC VALUES
     *
     * @param array $cart
     * @param array $cartMeta
     * @return array
     */
    private function calculateTotalsWithCoupon(array $cart, array $cartMeta): array
    {
        $totals = $this->calculateTotals($cart);

        if (isset($cartMeta['coupon'])) {
            // ✅ ENSURE COUPON DISCOUNT IS NUMERIC
            $couponDiscount = isset($cartMeta['coupon']['discount_amount']) && is_numeric($cartMeta['coupon']['discount_amount'])
                ? (float) $cartMeta['coupon']['discount_amount']
                : 0.0;

            $totals['discount_amount'] = round($couponDiscount, 2);

            // Apply free shipping if applicable
            if (!empty($cartMeta['coupon']['free_shipping'])) {
                $totals['shipping_amount'] = 0.0;
                $totals['free_shipping_applied'] = true;
            }

            // ✅ ENSURE ALL VALUES ARE NUMERIC BEFORE CALCULATION
            $subtotal = is_numeric($totals['subtotal']) ? (float) $totals['subtotal'] : 0.0;
            $taxAmount = is_numeric($totals['tax_amount']) ? (float) $totals['tax_amount'] : 0.0;
            $shippingAmount = is_numeric($totals['shipping_amount']) ? (float) $totals['shipping_amount'] : 0.0;
            $discountAmount = is_numeric($totals['discount_amount']) ? (float) $totals['discount_amount'] : 0.0;

            // Recalculate total
            $newTotal = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // Ensure total doesn't go negative
            $totals['total_amount'] = round(max(0, $newTotal), 2);

            // ✅ UPDATE FORMATTED VERSIONS
            $totals['formatted'] = [
                'subtotal' => format_amount($subtotal),
                'tax_amount' => format_amount($taxAmount),
                'shipping_amount' => format_amount($shippingAmount),
                'discount_amount' => format_amount($discountAmount),
                'total_amount' => format_amount($totals['total_amount']),
            ];
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
                unset($cart[$key]);
                continue;
            }

            // ✅ Load variant if exists
            $variant = null;
            if (!empty($item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);

                // Remove if variant no longer exists or inactive
                if (!$variant || !$variant->isActive()) {
                    unset($cart[$key]);
                    continue;
                }
            }

            // ✅ Update with variant data if available
            $item['name'] = $variant ? "{$product->name} - {$variant->getFullName()}" : $product->name;
            $item['sku'] = $variant ? $variant->sku : $product->sku;
            $item['image'] = $variant ? $variant->getImageUrl() : $product->getMainImageUrl();
            $item['price'] = $variant ? $variant->getFinalPrice() : $product->getFinalPrice();
            $item['regular_price'] = $variant ? (float) $variant->price : (float) $product->price;
            $item['max_quantity'] = $variant
                ? ($product->track_inventory ? $variant->stock_quantity : 999)
                : ($product->track_inventory ? $product->stock_quantity : 999);
            $item['is_in_stock'] = $variant ? $variant->isInStock() : $product->isInStock();
            $item['variant_name'] = $variant ? $variant->getFullName() : null;
        }

        return $cart;
    }

    /**
     * Revalidate coupon when cart changes
     * ✅ PROPER TYPE HANDLING
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

        // Calculate cart totals (returns numeric values)
        $cartTotals = $this->calculateTotals($cart);

        // ✅ PREPARE CART ITEMS WITH PROPER TYPES
        $cartItemsForCoupon = [];
        foreach ($cart as $item) {
            $cartItemsForCoupon[] = [
                'product_id' => $item['product_id'] ?? null,
                'variant_id' => $item['variant_id'] ?? null,
                'price' => is_numeric($item['price']) ? (float) $item['price'] : 0.0,
                'quantity' => is_numeric($item['quantity']) ? (int) $item['quantity'] : 0,
            ];
        }

        // ✅ ENSURE NUMERIC VALUES
        $subtotal = is_numeric($cartTotals['subtotal']) ? (float) $cartTotals['subtotal'] : 0.0;
        $totalItems = is_numeric($cartTotals['total_items']) ? (int) $cartTotals['total_items'] : 0;

        // Check if still applicable
        $cartCheck = $coupon->isApplicableToCart(
            $cartItemsForCoupon,
            $subtotal,
            $totalItems
        );

        if (!$cartCheck['valid']) {
            // Remove inapplicable coupon
            unset($cartMeta['coupon']);
            Cache::put("cart_meta:{$cartId}", $cartMeta, now()->addDays(7));
            return $cartMeta;
        }

        // Update discount amount
        $discountDetails = $coupon->calculateDiscount($cartItemsForCoupon, $subtotal);

        // ✅ ENSURE NUMERIC VALUE
        $discountAmount = isset($discountDetails['discount_amount']) && is_numeric($discountDetails['discount_amount'])
            ? (float) $discountDetails['discount_amount']
            : 0.0;

        $cartMeta['coupon']['discount_amount'] = $discountAmount;

        Cache::put("cart_meta:{$cartId}", $cartMeta, now()->addDays(7));

        return $cartMeta;
    }
}
