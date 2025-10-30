<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
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

            // Refresh product data and validate stock
            $cart = $this->refreshCartData($cart);

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'cart_id' => $cartId,
                    'items' => $cart,
                    'totals' => $this->calculateTotals($cart),
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
                    'quantity' => $validated['quantity'],
                    'is_taxable' => $product->is_taxable,
                    'tax_rate' => $product->tax_percentage ?? 0,
                    'max_quantity' => $product->track_inventory ? $product->stock_quantity : 999,
                    'added_at' => now()->toIso8601String(),
                ];
            }

            // Save cart (expires in 7 days)
            Cache::put("cart:{$cartId}", $cart, now()->addDays(7));

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => [
                    'cart_id' => $cartId,
                    'items' => $cart,
                    'totals' => $this->calculateTotals($cart),
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

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'items' => $cart,
                    'totals' => $this->calculateTotals($cart),
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
            $itemKey = $validated['product_id'] . ($validated['variant_id'] ?? '');

            if (!isset($cart[$itemKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart',
                ], 404);
            }

            unset($cart[$itemKey]);
            Cache::put("cart:{$validated['cart_id']}", $cart, now()->addDays(7));

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => [
                    'cart_id' => $validated['cart_id'],
                    'items' => $cart,
                    'totals' => $this->calculateTotals($cart),
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
                'coupon_code' => 'required|string',
            ]);

            // TODO: Implement coupon validation logic
            // For now, return a placeholder response

            return response()->json([
                'success' => false,
                'message' => 'Coupon system not yet implemented',
            ], 501);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
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
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping_amount' => 0, // Calculate based on shipping method
            'discount_amount' => 0, // Calculate based on coupons
            'total_amount' => round($total, 2),
            'total_items' => $totalItems,
            'currency' => 'USD', // Get from config
        ];
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
}
