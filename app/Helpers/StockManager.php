<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Simplified Stock Manager Helper Class
 *
 * Works with Product.stock_quantity directly
 * Warehouse integration is OPTIONAL
 */
class StockManager
{
    /**
     * Reserve stock for an order (doesn't reduce quantity, only marks as reserved)
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $warehouseId (OPTIONAL)
     * @param string|null $referenceType
     * @param string|null $referenceId
     * @param string|null $reason
     * @return bool
     */
    public static function reserveStock(
        Product $product,
        int $quantity,
        ?string $warehouseId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $reason = null
    ): bool {
        if (!$product->track_inventory) {
            return true;
        }

        DB::beginTransaction();
        try {
            // Check if enough stock in product table
            if ($product->stock_quantity < $quantity) {
                throw new Exception("Insufficient stock. Available: {$product->stock_quantity}, Requested: {$quantity}");
            }

            // If warehouse is specified, use warehouse stock system
            if ($warehouseId) {
                $warehouse = Warehouse::find($warehouseId);

                if ($warehouse) {
                    // Get or create warehouse stock record
                    $warehouseStock = ProductWarehouseStock::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouse->id,
                        ],
                        [
                            'quantity' => $product->stock_quantity, // Initialize with product stock
                            'reserved_quantity' => 0,
                            'available_quantity' => $product->stock_quantity,
                        ]
                    );

                    // Reserve in warehouse
                    $warehouseStock->increment('reserved_quantity', $quantity);
                    $warehouseStock->update([
                        'available_quantity' => $warehouseStock->quantity - $warehouseStock->reserved_quantity
                    ]);
                }
            }

            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'reservation',
                'quantity' => $quantity,
                'previous_quantity' => $product->stock_quantity,
                'new_quantity' => $product->stock_quantity, // Stock not deducted yet
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason ?? 'Stock reserved',
                'notes' => "Reserved {$quantity} units",
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Stock reservation failed: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            return false;
        }
    }

    /**
     * Release reserved stock (cancel reservation)
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $warehouseId
     * @param string|null $referenceType
     * @param string|null $referenceId
     * @param string|null $reason
     * @return bool
     */
    public static function releaseReservedStock(
        Product $product,
        int $quantity,
        ?string $warehouseId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $reason = null
    ): bool {
        if (!$product->track_inventory) {
            return true;
        }

        DB::beginTransaction();
        try {
            // If warehouse specified, release from warehouse
            if ($warehouseId) {
                $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($warehouseStock) {
                    $newReserved = max(0, $warehouseStock->reserved_quantity - $quantity);
                    $warehouseStock->update([
                        'reserved_quantity' => $newReserved,
                        'available_quantity' => $warehouseStock->quantity - $newReserved
                    ]);
                }
            }

            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'release_reservation',
                'quantity' => $quantity,
                'previous_quantity' => $product->stock_quantity,
                'new_quantity' => $product->stock_quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason ?? 'Reserved stock released',
                'notes' => "Released {$quantity} reserved units",
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Release reserved stock failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deduct stock (reduce actual quantity) - used when order is shipped/completed
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $warehouseId
     * @param string|null $referenceType
     * @param string|null $referenceId
     * @param string|null $reason
     * @param bool $releaseReserved
     * @return bool
     */
    public static function deductStock(
        Product $product,
        int $quantity,
        ?string $warehouseId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $reason = null,
        bool $releaseReserved = true
    ): bool {
        if (!$product->track_inventory) {
            return true;
        }

        DB::beginTransaction();
        try {
            $previousQuantity = $product->stock_quantity;

            // Check if enough stock
            if ($product->stock_quantity < $quantity) {
                throw new Exception("Cannot deduct {$quantity} units. Only {$product->stock_quantity} available.");
            }

            // Deduct from product table
            $newQuantity = $product->stock_quantity - $quantity;
            $product->update(['stock_quantity' => $newQuantity]);

            // If warehouse specified, deduct from warehouse too
            if ($warehouseId) {
                $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($warehouseStock) {
                    // Release reserved if requested
                    if ($releaseReserved && $warehouseStock->reserved_quantity >= $quantity) {
                        $warehouseStock->decrement('reserved_quantity', $quantity);
                    }

                    // Deduct actual stock
                    $warehouseNewQty = max(0, $warehouseStock->quantity - $quantity);
                    $warehouseStock->update([
                        'quantity' => $warehouseNewQty,
                        'available_quantity' => $warehouseNewQty - $warehouseStock->reserved_quantity
                    ]);
                }
            }

            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'sale',
                'quantity' => -$quantity, // Negative for deduction
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason ?? 'Stock deducted',
                'notes' => "Deducted {$quantity} units. Stock reduced from {$previousQuantity} to {$newQuantity}",
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Stock deduction failed: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            return false;
        }
    }

    /**
     * Add stock (increase quantity) - used for purchases, returns, adjustments
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $warehouseId
     * @param string $type
     * @param string|null $referenceType
     * @param string|null $referenceId
     * @param string|null $reason
     * @return bool
     */
    public static function addStock(
        Product $product,
        int $quantity,
        ?string $warehouseId = null,
        string $type = 'adjustment',
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $reason = null
    ): bool {
        if (!$product->track_inventory) {
            return true;
        }

        DB::beginTransaction();
        try {
            $previousQuantity = $product->stock_quantity;
            $newQuantity = $previousQuantity + $quantity;

            // Add to product table
            $product->update(['stock_quantity' => $newQuantity]);

            // If warehouse specified, add to warehouse too
            if ($warehouseId) {
                $warehouseStock = ProductWarehouseStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'available_quantity' => 0,
                    ]
                );

                $warehouseNewQty = $warehouseStock->quantity + $quantity;
                $warehouseStock->update([
                    'quantity' => $warehouseNewQty,
                    'available_quantity' => $warehouseNewQty - $warehouseStock->reserved_quantity
                ]);
            }

            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'quantity' => $quantity, // Positive for addition
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason ?? "Stock added via {$type}",
                'notes' => "Added {$quantity} units. Stock increased from {$previousQuantity} to {$newQuantity}",
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Add stock failed: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            return false;
        }
    }

    /**
     * Check if product has sufficient stock
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $warehouseId (OPTIONAL - if null, checks product.stock_quantity)
     * @return bool
     */
    public static function hasStock(Product $product, int $quantity, ?string $warehouseId = null): bool
    {
        if (!$product->track_inventory) {
            return true;
        }

        // If warehouse specified, check warehouse stock
        if ($warehouseId) {
            $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($warehouseStock) {
                return $warehouseStock->available_quantity >= $quantity;
            }
        }

        // Default: check product's stock_quantity
        return $product->stock_quantity >= $quantity;
    }

    /**
     * Get available stock for a product
     *
     * @param Product $product
     * @param string|null $warehouseId (OPTIONAL - if null, returns product.stock_quantity)
     * @return int
     */
    public static function getAvailableStock(Product $product, ?string $warehouseId = null): int
    {
        if (!$product->track_inventory) {
            return 999999;
        }

        // If warehouse specified, get warehouse stock
        if ($warehouseId) {
            $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($warehouseStock) {
                return $warehouseStock->available_quantity;
            }
        }

        // Default: return product's stock_quantity
        return $product->stock_quantity ?? 0;
    }

    /**
     * Transfer stock between warehouses
     */
    public static function transferStock(
        Product $product,
        int $quantity,
        string $fromWarehouseId,
        string $toWarehouseId,
        ?string $reason = null
    ): bool {
        if (!$product->track_inventory) {
            return true;
        }

        DB::beginTransaction();
        try {
            $fromWarehouse = Warehouse::findOrFail($fromWarehouseId);
            $toWarehouse = Warehouse::findOrFail($toWarehouseId);

            $fromStock = ProductWarehouseStock::where('product_id', $product->id)
                ->where('warehouse_id', $fromWarehouseId)
                ->first();

            if (!$fromStock || $fromStock->available_quantity < $quantity) {
                throw new Exception('Insufficient stock in source warehouse.');
            }

            // Deduct from source
            $fromStock->update([
                'quantity' => $fromStock->quantity - $quantity,
                'available_quantity' => ($fromStock->quantity - $quantity) - $fromStock->reserved_quantity
            ]);

            // Add to destination
            $toStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $toWarehouseId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $toStock->update([
                'quantity' => $toStock->quantity + $quantity,
                'available_quantity' => ($toStock->quantity + $quantity) - $toStock->reserved_quantity
            ]);

            // Create movement records
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $fromWarehouseId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'quantity' => -$quantity,
                'previous_quantity' => $fromStock->quantity + $quantity,
                'new_quantity' => $fromStock->quantity,
                'reason' => $reason ?? "Transfer to {$toWarehouse->name}",
            ]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $toWarehouseId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'quantity' => $quantity,
                'previous_quantity' => $toStock->quantity - $quantity,
                'new_quantity' => $toStock->quantity,
                'reason' => $reason ?? "Transfer from {$fromWarehouse->name}",
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Stock transfer failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync stock from external warehouse system (API integration)
     *
     * @param Product $product
     * @param string $warehouseId
     * @param int $newQuantity
     * @param string|null $reason
     * @return bool
     */
    public static function syncWarehouseStock(
        Product $product,
        string $warehouseId,
        int $newQuantity,
        ?string $reason = null
    ): bool {
        if (!$product->track_inventory) {
            return true;
        }

        DB::beginTransaction();
        try {
            $warehouseStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $previousQuantity = $warehouseStock->quantity;

            $warehouseStock->update([
                'quantity' => $newQuantity,
                'available_quantity' => $newQuantity - $warehouseStock->reserved_quantity
            ]);

            // Also update product's total stock
            $product->update(['stock_quantity' => $newQuantity]);

            // Create movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'sync',
                'quantity' => $newQuantity - $previousQuantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason ?? 'Warehouse stock sync',
                'notes' => "Synced from external system. Previous: {$previousQuantity}, New: {$newQuantity}",
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Warehouse sync failed: ' . $e->getMessage());
            return false;
        }
    }
}
