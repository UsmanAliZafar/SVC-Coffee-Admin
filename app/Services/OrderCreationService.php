<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OrderCreationService
 *
 * Generic service for creating orders used by both CheckoutController and ARBCheckoutController
 * Based on the working logic from CheckoutController
 *
 * Place this file at: app/Services/OrderCreationService.php
 */
class OrderCreationService
{
    /**
     * Calculate cart totals with proper tax handling (inclusive/exclusive)
     * Based on CartController::calculateTotals()
     *
     * @param array $cart
     * @return array
     */
    public function calculateCartTotals(array $cart): array
    {
        $subtotal = 0.0;
        $taxAmount = 0.0; // Only EXCLUSIVE tax (to be added to total)
        $includedTaxAmount = 0.0; // Only for display (already in price)
        $totalItems = 0;
        $taxBreakdown = [];

        foreach ($cart as $item) {
            // Ensure all values are numeric
            $price = is_numeric($item['price']) ? (float) $item['price'] : 0.0;
            $quantity = is_numeric($item['quantity']) ? (int) $item['quantity'] : 0;
            $taxRate = isset($item['tax_rate']) && is_numeric($item['tax_rate']) ? (float) $item['tax_rate'] : 0.0;

            $itemSubtotal = $price * $quantity;
            $subtotal += $itemSubtotal;
            $totalItems += $quantity;

            // Handle both inclusive and exclusive tax
            if (isset($item['is_taxable']) && $item['is_taxable']) {
                $taxType = $item['tax_type'] ?? 'exclusive';

                if ($taxType === 'inclusive') {
                    // ✅ Tax ALREADY in price - extract for DISPLAY ONLY
                    $includedTax = $itemSubtotal - ($itemSubtotal / (1 + ($taxRate / 100)));
                    $includedTaxAmount += $includedTax;

                    // Track tax by rate (for display statement)
                    if (!isset($taxBreakdown[$taxRate])) {
                        $taxBreakdown[$taxRate] = 0;
                    }
                    $taxBreakdown[$taxRate] += $includedTax;

                    // ✅ DO NOT ADD TO taxAmount (it's already in subtotal!)
                } else {
                    // ✅ Exclusive tax - MUST be added to total
                    $tax = $itemSubtotal * ($taxRate / 100);
                    $taxAmount += $tax;
                }
            }
        }

        // Build tax statement for inclusive tax (display only)
        $taxStatement = null;
        if ($includedTaxAmount > 0) {
            $uniqueRates = array_keys($taxBreakdown);

            if (count($uniqueRates) === 1) {
                $rate = $uniqueRates[0];
                $taxStatement = "(includes " . format_amount($includedTaxAmount) . " SAR VAT " . number_format($rate, 0) . "%)";
            } else {
                $taxStatement = "(includes " . format_amount($includedTaxAmount) . " SAR)";
            }
        }

        // ✅ CORRECT TOTAL CALCULATION:
        // - Subtotal already contains inclusive tax
        // - Only add exclusive tax
        $total = $subtotal + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2), // Already includes inclusive tax
            'tax_amount' => round($taxAmount, 2), // Only exclusive tax (to be added)
            'included_tax_amount' => round($includedTaxAmount, 2), // For display only
            'total_items' => $totalItems,
            'tax_statement' => $taxStatement,
        ];
    }

    /**
     * Calculate final total with shipping and discount
     *
     * @param array $totals
     * @param float $shippingAmount
     * @param float $discountAmount
     * @return float
     */
    public function calculateFinalTotal(array $totals, float $shippingAmount, float $discountAmount): float
    {
        $total = $totals['subtotal'] + $totals['tax_amount'] + $shippingAmount - $discountAmount;
        return round(max(0, $total), 2);
    }

    /**
     * Create order with all related data
     * Based on CheckoutController::createOrder()
     *
     * @param array $orderData - Order table fields
     * @param array $cart - Cart items
     * @param array $cartMeta - Cart metadata (coupon, etc)
     * @return Order
     * @throws \Exception
     */
    public function createOrder(array $orderData, array $cart, array $cartMeta = []): Order
    {
        try {
            Log::info('🛒 Order creation started', [
                'order_source' => $orderData['order_source'] ?? 'unknown',
                'items_count' => count($cart),
            ]);

            // ============================================================
            // 1. CREATE ORDER
            // ============================================================
            Log::info('📝 Order data being sent to Order::create()', [
                'subtotal' => $orderData['subtotal'],
                'tax_amount' => $orderData['tax_amount'],
                'shipping_amount' => $orderData['shipping_amount'],
                'discount_amount' => $orderData['discount_amount'],
                'total_amount' => $orderData['total_amount'],
            ]);

            $order = Order::create($orderData);

            // ✅ Refresh to get actual DB values
            $order = $order->fresh();

            Log::info('✅ Order created - checking actual saved values', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'saved_subtotal' => $order->subtotal,
                'saved_tax_amount' => $order->tax_amount,
                'saved_shipping_amount' => $order->shipping_amount,
                'saved_discount_amount' => $order->discount_amount,
                'saved_total_amount' => $order->total_amount,
                'expected_total' => $orderData['total_amount'],
                'difference' => $order->total_amount - $orderData['total_amount'],
            ]);

            // ============================================================
            // 2. GET DEFAULT WAREHOUSE
            // ============================================================
            $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();

            if (!$defaultWarehouse) {
                throw new \Exception('No default warehouse configured');
            }

            // ============================================================
            // 3. CREATE ORDER ITEMS
            // ============================================================
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_id']}");
                }

                $variant = !empty($item['variant_id'])
                    ? ProductVariant::find($item['variant_id'])
                    : null;

                $itemSubtotal = $item['price'] * $item['quantity'];

                // ✅ CALCULATE TAX BASED ON TYPE (INCLUSIVE/EXCLUSIVE)
                $itemTaxAmount = 0;
                $itemTotal = $itemSubtotal; // Start with subtotal

                if ($item['is_taxable']) {
                    $taxType = $item['tax_type'] ?? 'exclusive';
                    $taxRate = $item['tax_rate'] ?? 0;

                    if ($taxType === 'inclusive') {
                        // ✅ Tax ALREADY in price - extract for DISPLAY only
                        $itemTaxAmount = $itemSubtotal - ($itemSubtotal / (1 + ($taxRate / 100)));
                        // ✅ Item total = subtotal (tax already included, don't add again!)
                        $itemTotal = $itemSubtotal;
                    } else {
                        // ✅ Exclusive tax - MUST be added to item total
                        $itemTaxAmount = $itemSubtotal * ($taxRate / 100);
                        // ✅ Item total = subtotal + tax
                        $itemTotal = $itemSubtotal + $itemTaxAmount;
                    }
                }

                // Determine item status based on order status
                $itemStatus = 'ITEM_PENDING';
                if ($orderData['status_key_code'] === 'ORDER_CONFIRMED') {
                    $itemStatus = 'ITEM_CONFIRMED';
                }

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
                    'subtotal' => round($itemSubtotal, 2),
                    'tax_amount' => round($itemTaxAmount, 2), // For display/record keeping
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'is_taxable' => $item['is_taxable'],
                    'total' => round($itemTotal, 2), // ✅ Correct total (inclusive tax not added twice)
                    'status_key_code' => $itemStatus,
                    'warehouse_id' => $defaultWarehouse->id,
                ]);
            }

            // ============================================================
            // 4. HANDLE STOCK ALLOCATION
            // ============================================================
            $shouldDeduct = ($orderData['status_key_code'] === 'ORDER_CONFIRMED');

            Log::info('📦 Stock allocation started', [
                'order' => $order->order_number,
                'mode' => $shouldDeduct ? 'DEDUCT' : 'RESERVE',
                'items_to_process' => count($cart),
            ]);

            foreach ($cart as $itemKey => $item) {
                Log::info('🔍 Processing item', [
                    'key' => $itemKey,
                    'product_id' => $item['product_id'] ?? 'N/A',
                    'variant_id' => $item['variant_id'] ?? 'N/A',
                    'quantity' => $item['quantity'] ?? 'N/A',
                ]);

                $product = Product::find($item['product_id']);

                // Skip if product doesn't track inventory
                if (!$product) {
                    Log::warning('⚠️ Product not found', [
                        'product_id' => $item['product_id'],
                    ]);
                    throw new \Exception("Product not found: {$item['product_id']}");
                }

                if (!$product->track_inventory) {
                    Log::info('⏭️ Skipping (inventory not tracked)', [
                        'product' => $product->name,
                    ]);
                    continue;
                }

                $variant = !empty($item['variant_id'])
                    ? ProductVariant::find($item['variant_id'])
                    : null;

                $quantityNeeded = $item['quantity'];
                $itemName = $variant
                    ? "{$product->name} ({$variant->getFullName()})"
                    : $product->name;

                Log::info('📊 Checking warehouse stock', [
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'quantity_needed' => $quantityNeeded,
                ]);

                // Get warehouses with available stock, prioritize default
                $warehouseStocks = \App\Models\ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->where('available_quantity', '>', 0)
                    ->join('warehouses', 'product_warehouse_stock.warehouse_id', '=', 'warehouses.id')
                    ->select('product_warehouse_stock.*', 'warehouses.name as warehouse_name', 'warehouses.is_default')
                    ->orderBy('warehouses.is_default', 'desc') // Default warehouse first
                    ->orderBy('warehouses.priority', 'desc')
                    ->get();

                Log::info('🏭 Warehouses found', [
                    'count' => $warehouseStocks->count(),
                    'total_available' => $warehouseStocks->sum('available_quantity'),
                ]);

                if ($warehouseStocks->isEmpty()) {
                    Log::error('❌ No warehouse stock found', [
                        'product' => $itemName,
                        'product_id' => $product->id,
                        'variant_id' => $variant ? $variant->id : null,
                    ]);
                    throw new \Exception("Stock not available for {$itemName}");
                }

                $fulfillmentDetails = [];
                $remainingQuantity = $quantityNeeded;

                // Allocate from warehouses until quantity is fulfilled
                foreach ($warehouseStocks as $warehouseStock) {
                    if ($remainingQuantity <= 0) break;

                    $allocateQty = min($remainingQuantity, $warehouseStock->available_quantity);

                    Log::info('🔄 Attempting allocation', [
                        'warehouse' => $warehouseStock->warehouse_name,
                        'warehouse_id' => $warehouseStock->warehouse_id,
                        'available' => $warehouseStock->available_quantity,
                        'reserved' => $warehouseStock->reserved_quantity ?? 'N/A',
                        'on_hand' => $warehouseStock->on_hand_quantity ?? 'N/A',
                        'allocating' => $allocateQty,
                        'remaining_after' => $remainingQuantity - $allocateQty,
                        'mode' => $shouldDeduct ? 'DEDUCT' : 'RESERVE',
                    ]);

                    // DEDUCT or RESERVE based on order status
                    $success = false;

                    try {
                        if ($shouldDeduct) {
                            // Log before attempting
                            Log::info('⚡ Calling reduceStock()', [
                                'quantity' => $allocateQty,
                                'current_available' => $warehouseStock->available_quantity,
                            ]);

                            // ✅ reduceStock() returns void, so we assume success if no exception
                            $warehouseStock->reduceStock($allocateQty);
                            $success = true; // If we get here, it succeeded

                            // Log result
                            Log::info('📋 reduceStock() completed', [
                                'success' => 'YES',
                                'new_available' => $warehouseStock->fresh()->available_quantity ?? 'N/A',
                            ]);
                        } else {
                            // Log before attempting
                            Log::info('🔒 Calling reserveStock()', [
                                'quantity' => $allocateQty,
                                'current_available' => $warehouseStock->available_quantity,
                            ]);

                            // ✅ reserveStock() returns bool
                            $success = $warehouseStock->reserveStock($allocateQty);

                            // Log result
                            Log::info('📋 reserveStock() result', [
                                'success' => $success ? 'YES' : 'NO',
                                'new_available' => $warehouseStock->fresh()->available_quantity ?? 'N/A',
                            ]);
                        }
                    } catch (\Exception $stockError) {
                        Log::error('💥 Exception during stock operation', [
                            'error' => $stockError->getMessage(),
                            'method' => $shouldDeduct ? 'reduceStock' : 'reserveStock',
                        ]);
                        $success = false;
                    }

                    if ($success) {
                        $action = $shouldDeduct ? 'DEDUCTED' : 'RESERVED';

                        $fulfillmentDetails[] = [
                            'warehouse_id' => $warehouseStock->warehouse_id,
                            'warehouse_name' => $warehouseStock->warehouse_name,
                            'quantity' => $allocateQty,
                            'action' => $action,
                        ];

                        Log::info("✅ {$action}", [
                            'order' => $order->order_number,
                            'product' => $itemName,
                            'quantity' => $allocateQty,
                            'warehouse' => $warehouseStock->warehouse_name,
                        ]);

                        $remainingQuantity -= $allocateQty;
                    } else {
                        Log::error('❌ Allocation failed', [
                            'warehouse' => $warehouseStock->warehouse_name,
                            'method' => $shouldDeduct ? 'reduceStock' : 'reserveStock',
                        ]);
                    }
                }

                // Verify all quantity was allocated
                if ($remainingQuantity > 0) {
                    Log::error('❌ INSUFFICIENT STOCK ALLOCATED', [
                        'product' => $itemName,
                        'quantity_needed' => $quantityNeeded,
                        'quantity_remaining' => $remainingQuantity,
                        'warehouses_checked' => $warehouseStocks->count(),
                        'fulfillment_details' => $fulfillmentDetails,
                    ]);
                    throw new \Exception("Failed to allocate stock for {$itemName}. Missing {$remainingQuantity} units.");
                }

                // Update order item with fulfillment details
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->where('product_variant_id', $variant ? $variant->id : null)
                    ->first();

                if ($orderItem) {
                    $orderItem->update([
                        'stock_reserved' => !$shouldDeduct,
                        'stock_reserved_at' => !$shouldDeduct ? now() : null,
                        'stock_deducted' => $shouldDeduct,
                        'stock_deducted_at' => $shouldDeduct ? now() : null,
                        'warehouse_id' => $fulfillmentDetails[0]['warehouse_id'],
                        'fulfillment_details' => $fulfillmentDetails,
                    ]);

                    Log::info('✅ Fulfillment details stored', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'warehouses_used' => count($fulfillmentDetails),
                    ]);
                }
            }

            Log::info('✅ Order creation completed successfully', [
                'order_number' => $order->order_number,
                'items_count' => count($cart),
            ]);

            return $order;

        } catch (\Exception $e) {
            Log::error('❌ Order creation failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            throw $e;
        }
    }
}
