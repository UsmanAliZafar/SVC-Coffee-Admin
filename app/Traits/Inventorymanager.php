<?php

namespace App\Traits;

use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use App\Models\StockAlert;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * InventoryManager Trait
 *
 * Central inventory management system for Products
 * Handles all stock operations automatically
 *
 * Usage: Add to Product model: use InventoryManager;
 */

trait InventoryManager
{
    /**
     * Boot the trait - Auto sync stock on product changes
     */
    protected static function bootInventoryManager()
    {
        // After product is updated, check if stock_quantity changed
        static::updated(function ($product) {
            if ($product->track_inventory && $product->wasChanged('stock_quantity')) {
                // If stock_quantity was manually changed, sync to default warehouse
                $product->syncStockToDefaultWarehouse();
            }
        });
    }

    // ========================================
    // STOCK MANAGEMENT METHODS
    // ========================================

    /**
     * Add stock to product (updates both product and warehouse)
     *
     * @param int $quantity Amount to add
     * @param string|null $warehouseId Warehouse ID (uses default if null)
     * @param string|null $reason Reason for adjustment
     * @param string $movementType Type of movement (default: 'adjustment')
     * @return bool Success status
     */
    public function addStock(
        int $quantity,
        ?string $warehouseId = null,
        ?string $reason = null,
        string $movementType = 'adjustment'
    ): bool {
        if (!$this->track_inventory) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Get warehouse
            $warehouse = $warehouseId
                ? Warehouse::find($warehouseId)
                : Warehouse::where('is_default', true)->first();

            if (!$warehouse) {
                throw new \Exception('No warehouse found');
            }

            // Get or create warehouse stock record
            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $previousQuantity = $stock->quantity;

            // Update warehouse stock
            $stock->quantity += $quantity;
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
            $stock->save();

            // Update product total stock
            $this->updateProductStock();

            // Create movement record
            $this->createMovementRecord(
                $warehouse->id,
                $movementType,
                $quantity,
                $previousQuantity,
                $stock->quantity,
                $reason ?? 'Stock added'
            );

            // Check for alerts
            $this->checkStockAlerts();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add stock: " . $e->getMessage(), [
                'product_id' => $this->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    /**
     * Reduce stock from product
     *
     * @param int $quantity Amount to reduce
     * @param string|null $warehouseId Warehouse ID (uses default if null)
     * @param string|null $reason Reason for reduction
     * @param string $movementType Type of movement
     * @param bool $allowNegative Allow negative stock
     * @return bool Success status
     */
    public function reduceStock(
        int $quantity,
        ?string $warehouseId = null,
        ?string $reason = null,
        string $movementType = 'sale',
        bool $allowNegative = false
    ): bool {
        if (!$this->track_inventory) {
            return true; // Always allow if not tracking
        }

        try {
            DB::beginTransaction();

            // Get warehouse
            $warehouse = $warehouseId
                ? Warehouse::find($warehouseId)
                : Warehouse::where('is_default', true)->first();

            if (!$warehouse) {
                throw new \Exception('No warehouse found');
            }

            // Get warehouse stock
            $stock = ProductWarehouseStock::where('product_id', $this->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if (!$stock) {
                throw new \Exception('Stock record not found');
            }

            // Check if enough stock available
            if (!$allowNegative && $stock->available_quantity < $quantity) {
                throw new \Exception('Insufficient stock available');
            }

            $previousQuantity = $stock->quantity;

            // Update warehouse stock
            $stock->quantity = $allowNegative
                ? $stock->quantity - $quantity
                : max(0, $stock->quantity - $quantity);
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
            $stock->save();

            // Update product total stock
            $this->updateProductStock();

            // Create movement record
            $this->createMovementRecord(
                $warehouse->id,
                $movementType,
                -$quantity,
                $previousQuantity,
                $stock->quantity,
                $reason ?? 'Stock reduced'
            );

            // Check for alerts
            $this->checkStockAlerts();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reduce stock: " . $e->getMessage(), [
                'product_id' => $this->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    /**
     * Set stock quantity directly
     *
     * @param int $quantity New quantity
     * @param string|null $warehouseId Warehouse ID
     * @param string|null $reason Reason for change
     * @return bool Success status
     */
    public function setStock(
        int $quantity,
        ?string $warehouseId = null,
        ?string $reason = null
    ): bool {
        if (!$this->track_inventory) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Get warehouse
            $warehouse = $warehouseId
                ? Warehouse::find($warehouseId)
                : Warehouse::where('is_default', true)->first();

            if (!$warehouse) {
                throw new \Exception('No warehouse found');
            }

            // Get or create warehouse stock
            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $previousQuantity = $stock->quantity;
            $difference = $quantity - $previousQuantity;

            // Update warehouse stock
            $stock->quantity = max(0, $quantity);
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
            $stock->save();

            // Update product total stock
            $this->updateProductStock();

            // Create movement record
            $this->createMovementRecord(
                $warehouse->id,
                'adjustment',
                $difference,
                $previousQuantity,
                $stock->quantity,
                $reason ?? 'Stock set to ' . $quantity
            );

            // Check for alerts
            $this->checkStockAlerts();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to set stock: " . $e->getMessage(), [
                'product_id' => $this->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    /**
     * Reserve stock for order
     *
     * @param int $quantity Amount to reserve
     * @param string|null $warehouseId Warehouse ID
     * @param string|null $orderId Order ID for reference
     * @return bool Success status
     */
    public function reserveStock(
        int $quantity,
        ?string $warehouseId = null,
        ?string $orderId = null
    ): bool {
        if (!$this->track_inventory) {
            return true;
        }

        try {
            DB::beginTransaction();

            // Get warehouse
            $warehouse = $warehouseId
                ? Warehouse::find($warehouseId)
                : Warehouse::where('is_default', true)->first();

            if (!$warehouse) {
                throw new \Exception('No warehouse found');
            }

            // Get warehouse stock
            $stock = ProductWarehouseStock::where('product_id', $this->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if (!$stock || $stock->available_quantity < $quantity) {
                throw new \Exception('Insufficient available stock');
            }

            // Update reserved quantity
            $stock->reserved_quantity += $quantity;
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
            $stock->save();

            // Create movement record
            $this->createMovementRecord(
                $warehouse->id,
                'reservation',
                0, // No quantity change, just reserved
                $stock->quantity,
                $stock->quantity,
                'Stock reserved for order' . ($orderId ? " #{$orderId}" : ''),
                'order',
                $orderId
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reserve stock: " . $e->getMessage(), [
                'product_id' => $this->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    /**
     * Release reserved stock
     *
     * @param int $quantity Amount to release
     * @param string|null $warehouseId Warehouse ID
     * @param string|null $orderId Order ID for reference
     * @return bool Success status
     */
    public function releaseReservedStock(
        int $quantity,
        ?string $warehouseId = null,
        ?string $orderId = null
    ): bool {
        if (!$this->track_inventory) {
            return true;
        }

        try {
            DB::beginTransaction();

            // Get warehouse
            $warehouse = $warehouseId
                ? Warehouse::find($warehouseId)
                : Warehouse::where('is_default', true)->first();

            if (!$warehouse) {
                throw new \Exception('No warehouse found');
            }

            // Get warehouse stock
            $stock = ProductWarehouseStock::where('product_id', $this->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if (!$stock) {
                throw new \Exception('Stock record not found');
            }

            // Update reserved quantity
            $stock->reserved_quantity = max(0, $stock->reserved_quantity - $quantity);
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
            $stock->save();

            // Create movement record
            $this->createMovementRecord(
                $warehouse->id,
                'release_reservation',
                0,
                $stock->quantity,
                $stock->quantity,
                'Reserved stock released' . ($orderId ? " for order #{$orderId}" : ''),
                'order',
                $orderId
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to release reserved stock: " . $e->getMessage(), [
                'product_id' => $this->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    /**
     * Transfer stock between warehouses
     *
     * @param string $fromWarehouseId Source warehouse
     * @param string $toWarehouseId Destination warehouse
     * @param int $quantity Amount to transfer
     * @param string|null $reason Reason for transfer
     * @return bool Success status
     */
    public function transferStock(
        string $fromWarehouseId,
        string $toWarehouseId,
        int $quantity,
        ?string $reason = null
    ): bool {
        if (!$this->track_inventory) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Get source warehouse stock
            $fromStock = ProductWarehouseStock::where('product_id', $this->id)
                ->where('warehouse_id', $fromWarehouseId)
                ->first();

            if (!$fromStock || $fromStock->available_quantity < $quantity) {
                throw new \Exception('Insufficient stock in source warehouse');
            }

            // Get or create destination warehouse stock
            $toStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->id,
                    'warehouse_id' => $toWarehouseId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            // Update source
            $fromStock->quantity -= $quantity;
            $fromStock->available_quantity = $fromStock->quantity - $fromStock->reserved_quantity;
            $fromStock->save();

            // Update destination
            $toStock->quantity += $quantity;
            $toStock->available_quantity = $toStock->quantity - $toStock->reserved_quantity;
            $toStock->save();

            // Create movement record
            InventoryMovement::create([
                'id' => \Str::uuid(),
                'product_id' => $this->id,
                'warehouse_id' => $toWarehouseId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'quantity' => $quantity,
                'previous_quantity' => $toStock->quantity - $quantity,
                'new_quantity' => $toStock->quantity,
                'reason' => $reason ?? 'Stock transfer',
                'created_by' => auth('admin')->id() ?? null,
            ]);

            // Product total remains same, but check alerts
            $this->checkStockAlerts();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to transfer stock: " . $e->getMessage(), [
                'product_id' => $this->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Update product's total stock from all warehouses
     * This is the CORE sync method
     */
    protected function updateProductStock(): void
    {
        $totalStock = ProductWarehouseStock::where('product_id', $this->id)
            ->sum('quantity');

        // Update WITHOUT triggering events (to avoid infinite loop)
        DB::table('products')
            ->where('id', $this->id)
            ->update([
                'stock_quantity' => $totalStock,
                'updated_at' => now()
            ]);

        // Refresh model instance
        $this->stock_quantity = $totalStock;
    }

    /**
     * Sync product stock to default warehouse
     * Used when stock_quantity is manually changed
     */
    protected function syncStockToDefaultWarehouse(): void
    {
        $warehouse = Warehouse::where('is_default', true)->first();

        if (!$warehouse) {
            return;
        }

        $currentWarehouseTotal = ProductWarehouseStock::where('product_id', $this->id)
            ->sum('quantity');

        $difference = $this->stock_quantity - $currentWarehouseTotal;

        if ($difference != 0) {
            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $this->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $previousQuantity = $stock->quantity;
            $stock->quantity += $difference;
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
            $stock->save();

            $this->createMovementRecord(
                $warehouse->id,
                'sync',
                $difference,
                $previousQuantity,
                $stock->quantity,
                'Stock synced from product update'
            );
        }
    }

    /**
     * Create inventory movement record
     */
    protected function createMovementRecord(
        string $warehouseId,
        string $type,
        int $quantity,
        int $previousQuantity,
        int $newQuantity,
        ?string $reason = null,
        ?string $referenceType = null,
        ?string $referenceId = null
    ): void {
        InventoryMovement::create([
            'id' => \Str::uuid(),
            'product_id' => $this->id,
            'warehouse_id' => $warehouseId,
            'type' => $type,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => auth('admin')->id() ?? null,
        ]);
    }

    /**
     * Check and create stock alerts
     */
    protected function checkStockAlerts(): void
    {
        if (!$this->track_inventory) {
            return;
        }

        $stocks = ProductWarehouseStock::where('product_id', $this->id)->get();

        foreach ($stocks as $stock) {
            // Out of stock alert
            if ($stock->quantity <= 0) {
                StockAlert::updateOrCreate(
                    [
                        'product_id' => $this->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'alert_type' => 'out_of_stock',
                        'is_resolved' => false,
                    ],
                    [
                        'current_quantity' => $stock->quantity,
                        'threshold_quantity' => 0,
                    ]
                );
            } else {
                // Resolve out of stock alert if stock is back
                StockAlert::where('product_id', $this->id)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->where('alert_type', 'out_of_stock')
                    ->where('is_resolved', false)
                    ->update(['is_resolved' => true, 'resolved_at' => now()]);
            }

            // Low stock alert
            if ($stock->quantity > 0 && $stock->quantity <= $this->low_stock_threshold) {
                StockAlert::updateOrCreate(
                    [
                        'product_id' => $this->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'alert_type' => 'low_stock',
                        'is_resolved' => false,
                    ],
                    [
                        'current_quantity' => $stock->quantity,
                        'threshold_quantity' => $this->low_stock_threshold,
                    ]
                );
            } else if ($stock->quantity > $this->low_stock_threshold) {
                // Resolve low stock alert if stock is sufficient
                StockAlert::where('product_id', $this->id)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->where('alert_type', 'low_stock')
                    ->where('is_resolved', false)
                    ->update(['is_resolved' => true, 'resolved_at' => now()]);
            }
        }
    }

    // ========================================
    // QUERY METHODS
    // ========================================

    /**
     * Get total stock from all warehouses
     */
    public function getTotalWarehouseStock(): int
    {
        return ProductWarehouseStock::where('product_id', $this->id)
            ->sum('quantity');
    }

    /**
     * Get total available stock (not reserved)
     */
    public function getTotalAvailableStock(): int
    {
        return ProductWarehouseStock::where('product_id', $this->id)
            ->sum('available_quantity');
    }

    /**
     * Get total reserved stock
     */
    public function getTotalReservedStock(): int
    {
        return ProductWarehouseStock::where('product_id', $this->id)
            ->sum('reserved_quantity');
    }

    /**
     * Get stock for specific warehouse
     */
    public function getWarehouseStock(?string $warehouseId = null): int
    {
        $warehouseId = $warehouseId ?? Warehouse::where('is_default', true)->value('id');

        return ProductWarehouseStock::where('product_id', $this->id)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    /**
     * Check if product has enough available stock
     */
    public function hasAvailableStock(int $quantity = 1, ?string $warehouseId = null): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        if ($warehouseId) {
            $stock = ProductWarehouseStock::where('product_id', $this->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            return $stock && $stock->available_quantity >= $quantity;
        }

        return $this->getTotalAvailableStock() >= $quantity;
    }

    /**
     * Check if stock is in sync
     */
    public function isStockInSync(): bool
    {
        return $this->stock_quantity === $this->getTotalWarehouseStock();
    }

    /**
     * Get stock breakdown by warehouse
     */
    public function getStockBreakdown(): array
    {
        $stocks = ProductWarehouseStock::with('warehouse')
            ->where('product_id', $this->id)
            ->get();

        return $stocks->map(function ($stock) {
            return [
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name ?? 'Unknown',
                'quantity' => $stock->quantity,
                'reserved' => $stock->reserved_quantity,
                'available' => $stock->available_quantity,
            ];
        })->toArray();
    }
}
