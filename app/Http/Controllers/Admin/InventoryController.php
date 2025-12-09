<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
// MODELS
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use App\Models\StockAlert;
use App\Models\ProductVariant;
class InventoryController extends Controller
{
    /**
     * Display inventory overview
     */
    public function index()
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();
        $defaultWarehouse = Warehouse::default()->first();

        return view('admin.inventory.index', compact('warehouses', 'defaultWarehouse'));
    }

    /**
     * Get inventory data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouseId = $request->get('warehouse_id');

        if ($warehouseId) {
            $query = ProductWarehouseStock::with(['product', 'variant', 'warehouse'])
                ->where('warehouse_id', $warehouseId)
                ->where(function($q) {
                    // Include records that have a variant_id (variant stock)
                    $q->whereNotNull('variant_id')
                    // OR include records for simple products (products without variants)
                    ->orWhereHas('product', function($pq) {
                        $pq->where('has_variants', false);
                    });
                });
        } else {
            $query = Product::with(['warehouseStock.variant', 'variants.warehouseStock', 'category'])
            ->where('track_inventory', true)->where('has_variants', false);
        }

        // Apply filters
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    if ($warehouseId) {
                        $query->where('quantity', '>', 0);
                    } else {
                        // Check both warehouse stock AND product stock
                        $query->where(function($q) {
                            $q->whereHas('warehouseStock', function($wq) {
                                $wq->where('quantity', '>', 0);
                            })->orWhere('stock_quantity', '>', 0);
                        });
                    }
                    break;
                case 'low_stock':
                    if ($warehouseId) {
                        $query->whereHas('product', function($q) {
                            $q->whereColumn('product_warehouse_stock.quantity', '<=', 'products.low_stock_threshold')
                            ->where('product_warehouse_stock.quantity', '>', 0);
                        });
                    } else {
                        $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                            ->where('stock_quantity', '>', 0);
                    }
                    break;
                case 'out_of_stock':
                    if ($warehouseId) {
                        $query->where('quantity', '<=', 0);
                    } else {
                        // Only show out of stock if BOTH warehouse and product stock are zero
                        $query->where(function($q) {
                            $q->where('stock_quantity', '<=', 0)
                              ->whereDoesntHave('warehouseStock', function($wq) {
                                  $wq->where('quantity', '>', 0);
                              });
                        });
                    }
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            if ($warehouseId) {
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            } else {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }
        }


        if ($warehouseId) {
            return $this->getWarehouseStockDataTable($query);
        } else {
            $search = $request->filled('search') ? $request->search : null;
            return $this->getAllProductsStockDataTable($query, $search);
        }

    }

    /**
     * DataTable for warehouse stock
     * FIXED: Handles variants properly, excludes parent products with variants
     */
    private function getWarehouseStockDataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('product_info', function ($stock) {
                $product = $stock->product;

                if (!$product) {
                    return '<div>
                        <strong class="text-danger">Product Deleted</strong><br>
                        <small class="text-muted">ID: ' . e($stock->product_id) . '</small>
                    </div>';
                }

                // ✅ CRITICAL CHECK: Skip parent products that have variants
                // This stock record should be for a variant, not the parent
                if ($product->has_variants && !$stock->variant_id) {
                    return '<div>
                        <strong class="text-warning">⚠ Parent Product (Has Variants)</strong><br>
                        <small class="text-muted">' . e($product->name) . '</small><br>
                        <small class="text-danger">This stock should be moved to variants</small>
                    </div>';
                }

                // Check if this is a variant
                $variantInfo = '';
                if ($stock->variant_id && $stock->variant) {
                    $variantInfo = '<br><span class="badge bg-info">
                        <i class="bi bi-layers"></i> Variant: ' . e($stock->variant->getFullName()) . '
                    </span>';
                }

                return '<div>
                    <strong>' . e($product->name) . '</strong>' . $variantInfo . '<br>
                    <small class="text-muted">SKU: ' . e($stock->variant ? $stock->variant->sku : $product->sku) . '</small>
                </div>';
            })
            ->addColumn('quantity', function ($stock) {
                // Check if product exists before accessing isLowStock
                $product = $stock->product;
                if (!$product) {
                    return '<strong class="text-muted">' . $stock->quantity . '</strong>';
                }

                // ✅ Use variant's threshold if it's a variant, otherwise use product's
                $entity = $stock->variant ?: $product;
                $isLowStock = $stock->quantity > 0 && $stock->quantity <= ($entity->low_stock_threshold ?? 10);

                $class = $stock->quantity <= 0 ? 'text-danger'
                        : ($isLowStock ? 'text-warning' : 'text-success');

                return '<strong class="' . $class . '">' . $stock->quantity . '</strong>';
            })
            ->addColumn('available', function ($stock) {
                return '<span class="badge bg-success">' . $stock->available_quantity . '</span>';
            })
            ->addColumn('reserved', function ($stock) {
                return $stock->reserved_quantity > 0
                    ? '<span class="badge bg-warning">' . $stock->reserved_quantity . '</span>'
                    : '<span class="text-muted">0</span>';
            })
            ->addColumn('location', function ($stock) {
                $product = $stock->product;

                if (!$product) {
                    return '<span class="text-muted">N/A</span>';
                }

                if ($product->has_variants && !$stock->variant_id) {
                    return '<span class="text-muted">—</span>';
                }

                if ($stock->location) {
                    return '<span class="badge bg-info">
                        <i class="bi bi-geo-alt-fill"></i> ' . e($stock->location) . '
                    </span>';
                }

                return '<span class="text-muted">
                    <i class="bi bi-dash-circle"></i> Not Set
                </span>';
            })
            ->addColumn('actions', function ($stock) {
                $product = $stock->product;

                // If product is deleted, only show delete stock option
                if (!$product) {
                    $actions = '<div class="btn-group" role="group">';

                    if (auth('admin')->user()->hasPermission('inventory.delete')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger delete-orphan-stock"
                            data-id="' . $stock->id . '"
                            title="Delete Stock Record">
                            <i class="bi bi-trash"></i>
                        </button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                }

                // ✅ If this is a parent product with variants, show warning action
                if ($product->has_variants && !$stock->variant_id) {
                    $actions = '<div class="btn-group" role="group">';

                    $actions .= '<button type="button" class="btn btn-sm btn-warning migrate-to-variants"
                        data-id="' . $stock->id . '"
                        data-product-id="' . $product->id . '"
                        data-warehouse-id="' . $stock->warehouse_id . '"
                        data-quantity="' . $stock->quantity . '"
                        title="Migrate to Variants">
                        <i class="bi bi-arrow-right-circle"></i> Migrate
                    </button>';

                    if (auth('admin')->user()->hasPermission('inventory.delete')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger delete-orphan-stock"
                            data-id="' . $stock->id . '"
                            title="Delete Stock Record">
                            <i class="bi bi-trash"></i>
                        </button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                }

                $productName = $product->name;
                $variantName = $stock->variant ? $stock->variant->getFullName() : '';

                $actions = '<div class="btn-group" role="group">';

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    // ✅ FIXED: Changed class to match blade JavaScript
                    $actions .= '<button type="button" class="btn btn-sm btn-primary adjust-product-stock"
                        data-id="' . $stock->product_id . '"
                        data-variant-id="' . ($stock->variant_id ?: '') . '"
                        data-warehouse-id="' . $stock->warehouse_id . '"
                        data-name="' . e($productName) . '"
                        title="Adjust Stock">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                $actions .= '<a href="' . route('admin.products.show', $stock->product_id) . '"
                    class="btn btn-sm btn-info" title="View Product">
                    <i class="bi bi-eye"></i>
                </a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['product_info', 'quantity', 'available', 'reserved', 'location', 'actions'])
            ->make(true);
    }

    /**
     * DataTable for all products stock
     * FIXED: Shows variants instead of parent products
     */
    private function getAllProductsStockDataTable($query, $search = null)
    {
        // Collect both simple products and variants
        $items = collect();

        // 1. Get simple products (no variants)
        $simpleProducts = $query->get();

        foreach ($simpleProducts as $product) {
            $warehouseTotal = $product->warehouseStock()->sum('quantity');
            $total = $warehouseTotal > 0 ? $warehouseTotal : $product->stock_quantity;

            $items->push([
                'type' => 'product',
                'id' => $product->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'sku' => $product->sku,
                'category' => $product->category ? $product->category->title : 'N/A',
                'total_stock' => $total,
                'threshold' => $product->low_stock_threshold ?? 0,
                'warehouse_count' => $product->warehouseStock->count(),
                'warehouses' => $product->warehouseStock,
                'available' => $warehouseTotal > 0 ? $product->warehouseStock()->sum('available_quantity') : $product->stock_quantity,
                'reserved' => $product->getTotalReservedStock(),
                'is_low_stock' => $product->isLowStock(),
            ]);
        }

        // 2. Get ALL variants from variant products
        $variantProducts = Product::with(['variants.warehouseStock.warehouse', 'category'])
            ->where('track_inventory', true)
            ->where('has_variants', true);

        // ✅ FIX: Apply search filter to variant products
        if ($search) {
            $variantProducts->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhereHas('variants', function($vq) use ($search) {
                    $vq->where('variant_name', 'like', "%{$search}%")
                        ->orWhere('variant_value', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            });
        }

        $variantProducts = $variantProducts->get();

        foreach ($variantProducts as $product) {
            $variants = $product->variants()->active();

            // ✅ FIX: Apply search filter to individual variants
            if ($search) {
                $variants->where(function($q) use ($search) {
                    $q->where('variant_name', 'like', "%{$search}%")
                    ->orWhere('variant_value', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            foreach ($product->variants()->active()->get() as $variant) {
                $warehouseTotal = $variant->warehouseStock()->sum('quantity');
                $total = $warehouseTotal > 0 ? $warehouseTotal : $variant->stock_quantity;

                $items->push([
                    'type' => 'variant',
                    'id' => $variant->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'name' => $product->name,
                    'variant_name' => $variant->getFullName(),
                    'sku' => $variant->sku,
                    'category' => $product->category ? $product->category->title : 'N/A',
                    'total_stock' => $total,
                    'threshold' => $variant->low_stock_threshold ?? 0,
                    'warehouse_count' => $variant->warehouseStock->count(),
                    'warehouses' => $variant->warehouseStock,
                    'available' => $warehouseTotal > 0 ? $variant->warehouseStock()->sum('available_quantity') : $variant->stock_quantity,
                    'reserved' => $variant->getTotalReservedStock(),
                    'is_low_stock' => $variant->isLowStock(),
                ]);
            }
        }

        return DataTables::of($items)
            ->addColumn('product_info', function($item) {
                $variantBadge = '';
                if ($item['type'] === 'variant') {
                    $variantBadge = '<br><span class="badge bg-info">
                        <i class="bi bi-layers"></i> Variant: ' . htmlspecialchars($item['variant_name']) . '
                    </span>';
                }

                return '<div>
                    <strong>' . htmlspecialchars($item['name']) . '</strong>' . $variantBadge . '<br>
                    <small class="text-muted">SKU: ' . htmlspecialchars($item['sku']) . '</small>
                </div>';
            })
            ->addColumn('total_stock', function($item) {
                $class = $item['total_stock'] <= 0 ? 'text-danger' :
                        ($item['is_low_stock'] ? 'text-warning' : 'text-success');

                $indicator = '';
                if ($item['warehouse_count'] === 0 && $item['total_stock'] > 0) {
                    $indicator = ' <i class="bi bi-info-circle text-info" title="Product stock (no warehouse assigned)" data-bs-toggle="tooltip"></i>';
                }

                return '<strong class="' . $class . '">' . $item['total_stock'] . $indicator . '</strong>';
            })
            ->addColumn('threshold', function($item) {
                return '<span class="badge bg-secondary">' . $item['threshold'] . '</span>';
            })
            ->addColumn('warehouse_name', function($item) {
                if ($item['warehouse_count'] === 0) {
                    return '<span class="badge bg-warning text-dark">No warehouse</span>';
                }

                if ($item['warehouse_count'] === 1) {
                    return htmlspecialchars($item['warehouses']->first()->warehouse->name);
                }

                return '<span class="badge bg-info">' . $item['warehouse_count'] . ' warehouses</span>';
            })
            ->addColumn('available', function($item) {
                return '<span class="badge bg-success">' . $item['available'] . '</span>';
            })
            ->addColumn('reserved', function($item) {
                return $item['reserved'] > 0 ?
                    '<span class="badge bg-warning">' . $item['reserved'] . '</span>' :
                    '<span class="text-muted">0</span>';
            })
           ->addColumn('warehouses', function($item) {
                if ($item['warehouse_count'] === 0) {
                    return '<div>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i> No warehouse assigned
                        </span>
                        <br>
                        <small class="text-muted">Stock: ' . $item['total_stock'] . ' (Product level)</small>
                    </div>';
                }

                $html = '';

                foreach ($item['warehouses'] as $stock) {
                    $warehouseName = htmlspecialchars($stock->warehouse->name);
                    $totalQty = $stock->quantity;
                    $reservedQty = $stock->reserved_quantity;
                    $availableQty = $stock->available_quantity;

                    // Determine stock status color
                    $stockClass = $totalQty <= 0 ? 'text-danger' :
                                ($availableQty <= 0 ? 'text-warning' : 'text-success');

                    $html .= '<div class="mb-1 small">
                        <strong class="' . $stockClass . '">
                            <i class="bi bi-building"></i> ' . $warehouseName . ':
                        </strong>
                        <span class="badge bg-secondary">' . $totalQty . '</span>';

                    // Available quantity
                    $html .= ' <span class="text-success">(<i class="bi bi-check-circle"></i> ' . $availableQty . '</span>';

                    // Reserved quantity (only show if > 0)
                    if ($reservedQty > 0) {
                        $html .= ' <span class="text-warning">| <i class="bi bi-lock"></i> ' . $reservedQty . '</span>';
                    }

                    $html .= ')';

                    // Location (only show if set)
                    if (!empty($stock->location)) {
                        $html .= ' <span class="text-muted">- <i class="bi bi-geo-alt"></i> ' . htmlspecialchars($stock->location) . '</span>';
                    }

                    $html .= '</div>';
                }

                return $html;
            })
            ->addColumn('actions', function($item) {
                $actions = '<div class="btn-group" role="group">';

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    // ✅ FIXED: Changed class to match blade JavaScript
                    $actions .= '<button type="button" class="btn btn-sm btn-primary adjust-product-stock"
                        data-id="' . $item['product_id'] . '"
                        data-variant-id="' . ($item['variant_id'] ?: '') . '"
                        data-name="' . htmlspecialchars($item['name']) . '"
                        title="Adjust Stock">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                $actions .= '<a href="' . route('admin.products.show', $item['product_id']) . '"
                    class="btn btn-sm btn-info" title="View Product">
                    <i class="bi bi-eye"></i>
                </a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['product_info', 'threshold', 'warehouse_name', 'total_stock', 'available', 'reserved', 'warehouses', 'actions'])
            ->make(true);
    }

    public function cleanupOrphanedStock(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Get all stock records where product doesn't exist (soft deleted)
            $orphanedStocks = ProductWarehouseStock::whereDoesntHave('product')->get();

            $count = $orphanedStocks->count();

            foreach ($orphanedStocks as $stock) {
                // Log before deleting
                \Log::info('Deleting orphaned stock', [
                    'stock_id' => $stock->id,
                    'product_id' => $stock->product_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'quantity' => $stock->quantity
                ]);

                $stock->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$count} orphaned stock record(s)",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to cleanup orphaned stock: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup orphaned stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse stock counts (AJAX)
     */
    public function getWarehouseCounts(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouses = Warehouse::active()->byPriority()->get();
        $counts = [];

        // Count for "All Warehouses"
        $allCount = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->count();

        $allCount += ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->count();

        $counts['all'] = $allCount;

        // Count for each warehouse
        foreach ($warehouses as $warehouse) {
            $counts[$warehouse->id] = ProductWarehouseStock::where('warehouse_id', $warehouse->id)
                ->where(function($q) {
                    $q->whereNotNull('variant_id')
                    ->orWhereHas('product', function($pq) {
                        $pq->where('has_variants', false);
                    });
                })
                ->count();
        }

        return response()->json($counts);
    }

    /**
     * Delete single orphaned stock record
     * NEW METHOD
     */
    public function deleteOrphanedStock(Request $request, $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $stock = ProductWarehouseStock::findOrFail($id);

            // Verify it's actually orphaned
            if ($stock->product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: Product still exists'
                ], 400);
            }

            \Log::info('Deleting single orphaned stock', [
                'stock_id' => $stock->id,
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'quantity' => $stock->quantity
            ]);

            $stock->delete();

            return response()->json([
                'success' => true,
                'message' => 'Orphaned stock record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete stock record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show adjust stock page
     */
    public function adjust()
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();

        return view('admin.inventory.adjust', compact('warehouses'));
    }

    /**
     * Process stock adjustment (AJAX)
     */
    public function adjustStock(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',  // ← ADD THIS
            'warehouse_id' => 'required|exists:warehouses,id',
            'action_type' => 'required|in:set,add,reduce',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);
            $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;
            $warehouse = Warehouse::findOrFail($request->warehouse_id);

            // ✅ FIXED: Get or create stock record (handle variants)
            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,  // ← CRITICAL
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );
            if ($request->filled('location')) {
                $stock->location = $request->location;
                $stock->save();
            }
            $previousQuantity = $stock->quantity;
            $quantityChange = 0;

            switch ($request->action_type) {
                case 'set':
                    $quantityChange = $request->quantity - $stock->quantity;
                    $stock->update(['quantity' => $request->quantity]);
                    break;
                case 'add':
                    $quantityChange = $request->quantity;
                    $stock->addStock($request->quantity);
                    break;
                case 'reduce':
                    $quantityChange = -$request->quantity;
                    $stock->reduceStock($request->quantity);
                    break;
            }

            // Create movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'variant_id' => $variant ? $variant->id : null,  // ← ADD THIS
                'warehouse_id' => $warehouse->id,
                'type' => 'adjustment',
                'quantity' => $quantityChange,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->fresh()->quantity,
                'reason' => $request->reason ?? 'Manual adjustment',
            ]);

            // ✅ Sync will happen automatically via ProductWarehouseStock::saved() event

            // Check for alerts
            if ($variant) {
                $this->checkStockAlerts($product, $warehouse, $stock->fresh()->quantity, $variant);
            } else {
                $this->checkStockAlerts($product, $warehouse, $stock->fresh()->quantity);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'new_quantity' => $stock->fresh()->quantity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show inventory movements page
     */
    public function movement(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();

        return view('admin.inventory.movement', compact('warehouses'));
    }

    /**
     * Get movements data for DataTable (AJAX)
     */
    public function getMovementsData(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = InventoryMovement::with([
                'product' => function($query) {
                    $query->withTrashed(); // Include soft deleted products
                },
                'warehouse',
                'fromWarehouse',
                'toWarehouse',
                'creator'
            ])
            ->latest();

        // Apply filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('variant', function($variantQuery) use ($search) {
                    $variantQuery->where('sku', 'like', "%{$search}%");
                })->orWhereHas('creator', function($creatorQuery) use ($search) {
                    $creatorQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('type', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('product_info', function($movement) {
                if (!$movement->product) {
                    return '<div>
                        <strong class="text-danger">Product Deleted</strong><br>
                        <small class="text-muted">ID: ' . $movement->product_id . '</small>
                    </div>';
                }

                $deletedBadge = $movement->product->trashed()
                    ? ' <span class="badge badge-sm badge-danger">Deleted</span>'
                    : '';

                // Check if it's a variant movement
                $variantBadge = $movement->variant_id
                    ? ' <span class="badge bg-warning badge-sm">Variant</span>'
                    : '';

                return '<div>
                    <strong>' . e($movement->product->name) . '</strong>' . $deletedBadge . $variantBadge . '<br>
                    <small class="text-muted">SKU: ' .
                        ($movement->variant_id && $movement->variant ?
                            e($movement->variant->sku) :
                            e($movement->product->sku)
                        ) .
                    '</small>
                </div>';
            })
            ->addColumn('type_badge', function($movement) {
                return $movement->getTypeBadge();
            })
            ->addColumn('warehouse_info', function($movement) {
                if ($movement->type === 'transfer') {
                    $fromName = $movement->fromWarehouse ? e($movement->fromWarehouse->name) : 'N/A';
                    $toName = $movement->toWarehouse ? e($movement->toWarehouse->name) : 'N/A';

                    return '<div>
                        <small><strong>From:</strong> ' . $fromName . '</small><br>
                        <small><strong>To:</strong> ' . $toName . '</small>
                    </div>';
                }
                return $movement->warehouse
                    ? e($movement->warehouse->name)
                    : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('quantity_change', function($movement) {
                $class = $movement->quantity >= 0 ? 'text-success' : 'text-danger';
                $sign = $movement->quantity >= 0 ? '+' : '';
                return '<strong class="' . $class . '">' . $sign . $movement->quantity . '</strong>';
            })
            ->addColumn('stock_levels', function($movement) {
                if ($movement->previous_quantity !== null && $movement->new_quantity !== null) {
                    return '<small>' . $movement->previous_quantity . ' → ' . $movement->new_quantity . '</small>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('created_info', function($movement) {
                $creatorName = $movement->creator ? e($movement->creator->name) : 'System';
                return '<div>
                    <small>' . $movement->created_at->format('M d, Y H:i') . '</small><br>
                    <small class="text-muted">' . $creatorName . '</small>
                </div>';
            })
            ->rawColumns(['product_info', 'type_badge', 'warehouse_info', 'quantity_change', 'stock_levels', 'created_info'])
            ->make(true);
    }

    /**
     * Show low stock products page
     */
    /**
     * Show low stock products page
     */
    public function lowStock()
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();

        return view('admin.inventory.low-stock', compact('warehouses'));
    }

    /**
     * Get low stock data for DataTable (AJAX)
     * INCLUDES PRODUCT VARIANTS
     */
    public function getLowStockData(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouseId = $request->get('warehouse_id');
        $priority = $request->get('priority');
        $category = $request->get('category');
        $search = $request->get('search');

        // Collect both products and variants
        $items = collect();

        // 1. Get low stock PRODUCTS (simple products without variants)
        $productsQuery = Product::with(['category', 'warehouseStock.warehouse'])
            ->where('track_inventory', true)
            // ->where('has_variants', false)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);

        // Apply warehouse filter for products
        if ($warehouseId) {
            $productsQuery->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                ->whereColumn('quantity', '<=', DB::raw('(SELECT low_stock_threshold FROM products WHERE products.id = product_warehouse_stock.product_id)'))
                ->where('quantity', '>', 0);
            });
        }

        // Apply category filter
        if ($category) {
            $productsQuery->whereHas('category', function($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Apply search filter
        if ($search) {
            $productsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $productsQuery->get();

        // 2. Get low stock VARIANTS
        $variantsQuery = ProductVariant::with(['product.category', 'warehouseStock.warehouse', 'status'])
            ->whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);

        // Apply warehouse filter for variants
        if ($warehouseId) {
            $variantsQuery->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                ->whereColumn('quantity', '<=', DB::raw('(SELECT low_stock_threshold FROM product_variants WHERE product_variants.id = product_warehouse_stock.variant_id)'))
                ->where('quantity', '>', 0);
            });
        }

        // Apply category filter for variants
        if ($category) {
            $variantsQuery->whereHas('product.category', function($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Apply search filter for variants
        if ($search) {
            $variantsQuery->where(function($q) use ($search) {
                $q->where('variant_name', 'like', "%{$search}%")
                ->orWhere('variant_value', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $variants = $variantsQuery->get();

        // 3. Transform products to standard format
        foreach ($products as $product) {
            $warehouseStock = $warehouseId
                ? $product->warehouseStock->where('warehouse_id', $warehouseId)->first()
                : null;

            $currentStock = $warehouseId && $warehouseStock
                ? $warehouseStock->quantity
                : $product->stock_quantity;

            $items->push([
                'id' => 'product_' . $product->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'is_variant' => false,
                'product_name' => $product->name,
                'variant_name' => null,
                'sku' => $product->sku,
                'category' => $product->category ? $product->category->title : 'N/A',
                'current_stock' => $currentStock,
                'threshold' => $product->low_stock_threshold,
                'warehouse_id' => $warehouseId ?: '',
                'warehouse_name' => $warehouseId && $warehouseStock
                    ? $warehouseStock->warehouse->name
                    : 'All Warehouses',
                'priority' => $this->calculatePriority($currentStock, $product->low_stock_threshold),
                'created_at' => $product->created_at,
            ]);
        }

        // 4. Transform variants to standard format
        foreach ($variants as $variant) {
            $warehouseStock = $warehouseId
                ? $variant->warehouseStock->where('warehouse_id', $warehouseId)->first()
                : null;

            $currentStock = $warehouseId && $warehouseStock
                ? $warehouseStock->quantity
                : $variant->stock_quantity;

            $items->push([
                'id' => 'variant_' . $variant->id,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'is_variant' => true,
                'product_name' => $variant->product->name,
                'variant_name' => $variant->getFullName(),
                'sku' => $variant->sku,
                'category' => $variant->product->category ? $variant->product->category->title : 'N/A',
                'current_stock' => $currentStock,
                'threshold' => $variant->low_stock_threshold,
                'warehouse_id' => $warehouseId ?: '',
                'warehouse_name' => $warehouseId && $warehouseStock
                    ? $warehouseStock->warehouse->name
                    : 'All Warehouses',
                'priority' => $this->calculatePriority($currentStock, $variant->low_stock_threshold),
                'created_at' => $variant->created_at,
            ]);
        }

        // 5. Apply priority filter
        if ($priority) {
            $items = $items->filter(function($item) use ($priority) {
                return $item['priority'] === $priority;
            });
        }

        // 6. Sort items
        $items = $items->sortBy(function($item) {
            $priorityOrder = ['critical' => 1, 'high' => 2, 'medium' => 3];
            return [$priorityOrder[$item['priority']] ?? 4, $item['current_stock']];
        })->values();

        return DataTables::of($items)
            ->addColumn('checkbox', function($item) {
                return '<input type="checkbox" class="form-check-input item-checkbox"
                            data-id="' . $item['id'] . '"
                            data-product-id="' . $item['product_id'] . '"
                            data-variant-id="' . ($item['variant_id'] ?: '') . '"
                            data-warehouse-id="' . $item['warehouse_id'] . '"
                            data-current="' . $item['current_stock'] . '"
                            data-threshold="' . $item['threshold'] . '"
                            data-name="' . htmlspecialchars($item['product_name']) . '"
                            data-variant-name="' . ($item['variant_name'] ? htmlspecialchars($item['variant_name']) : '') . '">';
            })
            ->addColumn('product_info', function($item) {
                $variantBadge = '';
                if ($item['is_variant']) {
                    $variantBadge = '<br><span class="badge bg-info">
                        <i class="bi bi-layers"></i> Variant: ' . htmlspecialchars($item['variant_name']) . '
                    </span>';
                }

                return '<div>
                    <strong>' . htmlspecialchars($item['product_name']) . '</strong>' . $variantBadge . '<br>
                    <small class="text-muted">SKU: ' . htmlspecialchars($item['sku']) . '</small><br>
                    <small class="text-muted">Category: ' . htmlspecialchars($item['category']) . '</small>
                </div>';
            })
            ->addColumn('total_stock', function($item) {
                $className = $item['current_stock'] <= 5 ? 'text-danger' :
                            ($item['current_stock'] <= 10 ? 'text-warning' : 'text-secondary');
                $icon = $item['current_stock'] <= 5 ? '🔴' :
                    ($item['current_stock'] <= 10 ? '🟠' : '🟡');

                return '<strong class="' . $className . '">' . $icon . ' ' . $item['current_stock'] . '</strong>';
            })
            ->addColumn('threshold', function($item) {
                return '<span class="badge bg-secondary">' . $item['threshold'] . '</span>';
            })
            ->addColumn('stock_level', function($item) {
                $percentage = min(($item['current_stock'] / $item['threshold']) * 100, 100);

                return '<div class="stock-level-bar">
                    <div class="stock-level-fill" style="width: ' . $percentage . '%"></div>
                </div>
                <small class="text-muted mt-1 d-block">' . round($percentage) . '% of threshold</small>';
            })
            ->addColumn('priority', function($item) {
                $badgeClass = 'priority-' . $item['priority'];
                $label = ucfirst($item['priority']);

                return '<span class="priority-badge ' . $badgeClass . '">' . $label . '</span>';
            })
            ->addColumn('warehouse_name', function($item) {
                return $item['warehouse_name'] ?: '<span class="text-muted">All Warehouses</span>';
            })
            ->addColumn('actions', function($item) {
                $productName = htmlspecialchars($item['product_name'], ENT_QUOTES);
                $variantName = $item['variant_name'] ? htmlspecialchars($item['variant_name'], ENT_QUOTES) : '';

                return '<div class="action-buttons">
                    <button class="btn btn-sm btn-warning"
                            onclick="quickRestock(\'' . $item['product_id'] . '\', \'' . ($item['variant_id'] ?: '') . '\', \'' . $item['warehouse_id'] . '\', \'' . $productName . '\', \'' . $variantName . '\', ' . $item['current_stock'] . ', ' . $item['threshold'] . ')"
                            title="Quick Restock">
                        <i class="bi bi-box-seam"></i>
                    </button>
                    <a href="' . route('admin.products.show', $item['product_id']) . '"
                    class="btn btn-sm btn-info"
                    title="View Product">
                        <i class="bi bi-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="viewHistory(\'' . $item['product_id'] . '\', \'' . ($item['variant_id'] ?: '') . '\')"
                            title="View History">
                        <i class="bi bi-clock-history"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['checkbox', 'product_info', 'total_stock', 'threshold', 'stock_level', 'priority', 'warehouse_name', 'actions'])
            ->make(true);
    }

    /**
     * Calculate priority level based on stock vs threshold
     */
    private function calculatePriority(int $currentStock, int $threshold): string
    {
        if ($currentStock <= 5) {
            return 'critical';
        } elseif ($currentStock <= 10) {
            return 'high';
        } else {
            return 'medium';
        }
    }

    /**
     * Get low stock statistics (AJAX)
     */
    public function getLowStockStatistics(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouseId = $request->get('warehouse_id');

        // Count low stock products
        $lowStockProducts = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);

        if ($warehouseId) {
            $lowStockProducts->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->whereColumn('quantity', '<=', DB::raw('(SELECT low_stock_threshold FROM products WHERE products.id = product_warehouse_stock.product_id)'))
                  ->where('quantity', '>', 0);
            });
        }

        // Count low stock variants
        $lowStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);

        if ($warehouseId) {
            $lowStockVariants->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->whereColumn('quantity', '<=', DB::raw('(SELECT low_stock_threshold FROM product_variants WHERE product_variants.id = product_warehouse_stock.variant_id)'))
                  ->where('quantity', '>', 0);
            });
        }

        $totalLowStock = $lowStockProducts->count() + $lowStockVariants->count();

        // Count critical items (stock <= 5)
        $criticalProducts = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->where('stock_quantity', '<=', 5)
            ->where('stock_quantity', '>', 0);

        $criticalVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->where('stock_quantity', '<=', 5)
            ->where('stock_quantity', '>', 0);

        if ($warehouseId) {
            $criticalProducts->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)->where('quantity', '<=', 5)->where('quantity', '>', 0);
            });
            $criticalVariants->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)->where('quantity', '<=', 5)->where('quantity', '>', 0);
            });
        }

        $criticalCount = $criticalProducts->count() + $criticalVariants->count();

        // Calculate value at risk
        $productValue = $lowStockProducts->get()->sum(function($p) {
            return $p->stock_quantity * $p->price;
        });

        $variantValue = $lowStockVariants->get()->sum(function($v) {
            return $v->stock_quantity * $v->price;
        });

        $valueAtRisk = $productValue + $variantValue;

        return response()->json([
            'low_stock' => $totalLowStock,
            'critical_count' => $criticalCount,
            'value_at_risk' => round($valueAtRisk, 2),
            'avg_days_restock' => '3-5 days', // This could be calculated from historical data
        ]);
    }

    /**
     * Show out of stock products page
     */
    public function outOfStock()
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();

        return view('admin.inventory.out-of-stock', compact('warehouses'));
    }

    /**
     * Get out of stock data for DataTable (AJAX)
     * IMPROVED VERSION - Includes Products and Variants
     */
    public function getOutOfStockData(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $warehouseId = $request->get('warehouse_id');
            $impact = $request->get('impact');
            $daysOut = $request->get('days_out');
            $category = $request->get('category');
            $search = $request->get('search');

            // Collect both products and variants
            $items = collect();

            // 1. Get out of stock PRODUCTS (simple products without variants)
            $productsQuery = Product::with(['category', 'warehouseStock.warehouse'])
                ->where('track_inventory', true)
                ->where('has_variants', false);

            if ($warehouseId) {
                // Specific warehouse out of stock
                $productsQuery->whereHas('warehouseStock', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                    ->where('quantity', '<=', 0);
                });
            } else {
                // Overall out of stock (no warehouse has stock)
                $productsQuery->where('stock_quantity', '<=', 0)
                    ->whereDoesntHave('warehouseStock', function($q) {
                        $q->where('quantity', '>', 0);
                    });
            }

            // Apply category filter
            if ($category) {
                $productsQuery->whereHas('category', function($q) use ($category) {
                    $q->where('slug', $category);
                });
            }

            // Apply search filter
            if ($search) {
                $productsQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            $products = $productsQuery->get();

            // 2. Get out of stock VARIANTS
            $variantsQuery = ProductVariant::with(['product.category', 'warehouseStock.warehouse', 'status'])
                ->whereHas('product', function($q) {
                    $q->where('track_inventory', true);
                })
                ->where('status_key_code', 'VARIANT_ACTIVE');

            if ($warehouseId) {
                // Specific warehouse out of stock
                $variantsQuery->whereHas('warehouseStock', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                    ->where('quantity', '<=', 0);
                });
            } else {
                // Overall out of stock
                $variantsQuery->where('stock_quantity', '<=', 0)
                    ->whereDoesntHave('warehouseStock', function($q) {
                        $q->where('quantity', '>', 0);
                    });
            }

            // Apply category filter for variants
            if ($category) {
                $variantsQuery->whereHas('product.category', function($q) use ($category) {
                    $q->where('slug', $category);
                });
            }

            // Apply search filter for variants
            if ($search) {
                $variantsQuery->where(function($q) use ($search) {
                    $q->where('variant_name', 'like', "%{$search}%")
                    ->orWhere('variant_value', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('product', function($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
                });
            }

            $variants = $variantsQuery->get();

            // 3. Transform products to standard format
            foreach ($products as $product) {
                $warehouseStock = $warehouseId
                    ? $product->warehouseStock->where('warehouse_id', $warehouseId)->first()
                    : $product->warehouseStock->first();

                // Calculate days out of stock
                $daysOutOfStock = $this->calculateDaysOutOfStock($product, $warehouseStock);

                // Calculate impact level
                $impactLevel = $this->calculateImpactLevel($product, $daysOutOfStock);

                // Estimate lost sales
                $lostSalesEstimate = $this->estimateLostSales($product, $daysOutOfStock);

                $items->push([
                    'id' => 'product_' . $product->id,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'is_variant' => false,
                    'product_name' => $product->name,
                    'variant_name' => null,
                    'sku' => $product->sku,
                    'category' => $product->category ? $product->category->title : 'N/A',
                    'warehouse_id' => $warehouseId ?: '',
                    'warehouse_name' => $warehouseId && $warehouseStock
                        ? $warehouseStock->warehouse->name
                        : 'All Warehouses',
                    'days_out' => $daysOutOfStock,
                    'impact_level' => $impactLevel,
                    'lost_sales_estimate' => $lostSalesEstimate,
                    'threshold' => $product->low_stock_threshold ?? 10,
                    'urgency' => $this->calculateUrgency($daysOutOfStock, $impactLevel),
                    'created_at' => $product->created_at,
                ]);
            }

            // 4. Transform variants to standard format
            foreach ($variants as $variant) {
                $warehouseStock = $warehouseId
                    ? $variant->warehouseStock->where('warehouse_id', $warehouseId)->first()
                    : $variant->warehouseStock->first();

                // Calculate days out of stock
                $daysOutOfStock = $this->calculateDaysOutOfStock($variant, $warehouseStock);

                // Calculate impact level
                $impactLevel = $this->calculateImpactLevel($variant, $daysOutOfStock);

                // Estimate lost sales
                $lostSalesEstimate = $this->estimateLostSales($variant, $daysOutOfStock);

                $items->push([
                    'id' => 'variant_' . $variant->id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'is_variant' => true,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->getFullName(),
                    'sku' => $variant->sku,
                    'category' => $variant->product->category ? $variant->product->category->title : 'N/A',
                    'warehouse_id' => $warehouseId ?: '',
                    'warehouse_name' => $warehouseId && $warehouseStock
                        ? $warehouseStock->warehouse->name
                        : 'All Warehouses',
                    'days_out' => $daysOutOfStock,
                    'impact_level' => $impactLevel,
                    'lost_sales_estimate' => $lostSalesEstimate,
                    'threshold' => $variant->low_stock_threshold ?? 10,
                    'urgency' => $this->calculateUrgency($daysOutOfStock, $impactLevel),
                    'created_at' => $variant->created_at,
                ]);
            }

            // 5. Apply impact filter
            if ($impact) {
                $items = $items->filter(function($item) use ($impact) {
                    return $item['impact_level'] === $impact;
                });
            }

            // 6. Apply days out filter
            if ($daysOut) {
                $items = $items->filter(function($item) use ($daysOut) {
                    switch($daysOut) {
                        case '1-7':
                            return $item['days_out'] >= 1 && $item['days_out'] <= 7;
                        case '8-30':
                            return $item['days_out'] >= 8 && $item['days_out'] <= 30;
                        case '30+':
                            return $item['days_out'] > 30;
                    }
                    return true;
                });
            }

            // 7. Sort items by urgency (days out, impact level)
            $items = $items->sortByDesc(function($item) {
                $urgencyScore = $item['days_out'] * 10;
                if ($item['impact_level'] === 'high') $urgencyScore += 100;
                elseif ($item['impact_level'] === 'medium') $urgencyScore += 50;
                return $urgencyScore;
            })->values();

            return DataTables::of($items)
                ->addColumn('checkbox', function($item) {
                    return '<input type="checkbox" class="form-check-input item-checkbox"
                                data-id="' . $item['id'] . '"
                                data-product-id="' . $item['product_id'] . '"
                                data-variant-id="' . ($item['variant_id'] ?: '') . '"
                                data-warehouse-id="' . $item['warehouse_id'] . '"
                                data-threshold="' . $item['threshold'] . '"
                                data-name="' . htmlspecialchars($item['product_name']) . '"
                                data-variant-name="' . ($item['variant_name'] ? htmlspecialchars($item['variant_name']) : '') . '"
                                data-sku="' . htmlspecialchars($item['sku']) . '">';
                })
                ->addColumn('product_info', function($item) {
                    $variantBadge = '';
                    if ($item['is_variant']) {
                        $variantBadge = '<br><span class="badge bg-info">
                            <i class="bi bi-layers"></i> Variant: ' . htmlspecialchars($item['variant_name']) . '
                        </span>';
                    }

                    return '<div>
                        <strong>' . htmlspecialchars($item['product_name']) . '</strong>' . $variantBadge . '<br>
                        <small class="text-muted">SKU: ' . htmlspecialchars($item['sku']) . '</small><br>
                        <small class="text-muted">Category: ' . htmlspecialchars($item['category']) . '</small>
                    </div>';
                })
                ->addColumn('status_badge', function($item) {
                    return '<span class="out-of-stock-badge">OUT OF STOCK</span>';
                })
                ->addColumn('days_out_badge', function($item) {
                    $class = $item['days_out'] > 30 ? 'bg-danger' :
                            ($item['days_out'] > 7 ? 'bg-warning' : 'bg-secondary');

                    return '<span class="badge ' . $class . ' days-out-badge">
                        <i class="bi bi-calendar-x"></i> ' . $item['days_out'] . ' days
                    </span>';
                })
                ->addColumn('impact_badge', function($item) {
                    $badgeClass = 'impact-' . $item['impact_level'];
                    $label = ucfirst($item['impact_level']) . ' Impact';

                    return '<span class="impact-level ' . $badgeClass . '">' . $label . '</span>';
                })
                ->addColumn('urgency_indicator', function($item) {
                    $activeDots = min(5, ceil($item['urgency'] / 20));

                    $html = '<div class="restock-urgency" title="Urgency: ' . $item['urgency'] . '%">';
                    for ($i = 1; $i <= 5; $i++) {
                        $class = $i <= $activeDots ? 'active' : 'inactive';
                        $html .= '<span class="urgency-dot ' . $class . '"></span>';
                    }
                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('warehouse_name', function($item) {
                    return $item['warehouse_name'] ?: '<span class="text-muted">All Warehouses</span>';
                })
                ->addColumn('lost_sales_display', function($item) {
                    return '<div class="lost-sales-estimate">
                        <i class="bi bi-currency-dollar text-warning"></i>
                        <strong>' . store_currency_symbol() . number_format($item['lost_sales_estimate'], 2) . '</strong>
                    </div>';
                })
                ->addColumn('actions', function($item) {
                    $productName = htmlspecialchars($item['product_name'], ENT_QUOTES);
                    $variantName = $item['variant_name'] ? htmlspecialchars($item['variant_name'], ENT_QUOTES) : '';
                    $sku = htmlspecialchars($item['sku'], ENT_QUOTES);

                    return '<div class="action-buttons">
                        <button class="btn btn-sm btn-danger"
                                onclick="urgentRestock(\'' . $item['product_id'] . '\', \'' . ($item['variant_id'] ?: '') . '\', \'' . $item['warehouse_id'] . '\', \'' . $productName . '\', \'' . $variantName . '\', \'' . $sku . '\', ' . $item['days_out'] . ', ' . $item['threshold'] . ')"
                                title="Urgent Restock">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </button>
                        <a href="' . route('admin.products.show', $item['product_id']) . '"
                        class="btn btn-sm btn-info"
                        title="View Product">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick="viewHistory(\'' . $item['product_id'] . '\', \'' . ($item['variant_id'] ?: '') . '\')"
                                title="View History">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['checkbox', 'product_info', 'status_badge', 'days_out_badge', 'impact_badge', 'urgency_indicator', 'warehouse_name', 'lost_sales_display', 'actions'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Out of Stock Data Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'draw' => $request->get('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load data'
            ], 500);
        }
    }

    /**
     * Calculate days out of stock
     */
    private function calculateDaysOutOfStock($item, $warehouseStock = null)
    {
        // Check inventory movements for last stock-out date
        $lastStockOut = InventoryMovement::where('product_id', $item->id)
            ->where(function($q) use ($item) {
                if (method_exists($item, 'product_id')) {
                    // It's a variant
                    $q->where('variant_id', $item->id);
                }
            })
            ->where('new_quantity', '<=', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastStockOut) {
            return now()->diffInDays($lastStockOut->created_at);
        }

        // Fallback: estimate based on updated_at
        return now()->diffInDays($item->updated_at);
    }

    /**
     * Calculate impact level based on product popularity and days out
     */
    private function calculateImpactLevel($item, $daysOut)
    {
        // You can enhance this with actual sales data
        $threshold = $item->low_stock_threshold ?? 10;

        if ($daysOut > 30 || $threshold > 50) {
            return 'high';
        } elseif ($daysOut > 14 || $threshold > 20) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Estimate lost sales
     */
    private function estimateLostSales($item, $daysOut)
    {
        // Simple estimation: average daily sales * days out * price
        // You should replace this with actual sales data
        $price = $item->price ?? 0;
        $avgDailySales = ($item->low_stock_threshold ?? 10) / 30; // Rough estimate

        return round($avgDailySales * $daysOut * $price, 2);
    }

    /**
     * Calculate urgency percentage (0-100)
     */
    private function calculateUrgency($daysOut, $impactLevel)
    {
        $urgency = min(100, ($daysOut / 30) * 100); // Base on days

        // Adjust based on impact
        if ($impactLevel === 'high') {
            $urgency = min(100, $urgency * 1.5);
        } elseif ($impactLevel === 'medium') {
            $urgency = min(100, $urgency * 1.2);
        }

        return round($urgency);
    }

    /**
     * Transfer stock between warehouses (AJAX)
     */
    public function transfer(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        $success = $product->transferStock(
            $request->from_warehouse_id,
            $request->to_warehouse_id,
            $request->quantity,
            $request->reason
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Stock transferred successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to transfer stock. Insufficient quantity or other error.'
        ], 400);
    }

    /**
     * Get inventory statistics (AJAX)
     * FIXED: Shows product stock when no warehouse stock exists
     */
    public function statistics(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouseId = $request->get('warehouse_id');

        if ($warehouseId) {
            // Stats for specific warehouse
            $warehouse = Warehouse::findOrFail($warehouseId);

            $stats = [
                'total_products' => $warehouse->stock()->count(),
                'total_stock' => $warehouse->getTotalStock(),
                'in_stock' => $warehouse->stock()->where('quantity', '>', 0)->count(),
                'out_of_stock' => $warehouse->getOutOfStockCount(),
                'low_stock' => $warehouse->getLowStockCount(),
                'total_value' => $warehouse->getTotalStockValue(),
            ];
        } else {
            // ✅ FIXED: Count simple products + variants separately
            $simpleProducts = Product::where('track_inventory', true)
                ->where('has_variants', false)
                ->count();

            $totalVariants = ProductVariant::whereHas('product', function($q) {
                    $q->where('track_inventory', true);
                })
                ->where('status_key_code', 'VARIANT_ACTIVE')
                ->count();

            $totalProducts = $simpleProducts + $totalVariants;

            // In stock: simple products + variants
            $inStockSimple = Product::where('track_inventory', true)
                ->where('has_variants', false)
                ->where(function($q) {
                    $q->whereHas('warehouseStock', function($wq) {
                        $wq->where('quantity', '>', 0);
                    })->orWhere('stock_quantity', '>', 0);
                })->count();

            $inStockVariants = ProductVariant::whereHas('product', function($q) {
                    $q->where('track_inventory', true);
                })
                ->where('status_key_code', 'VARIANT_ACTIVE')
                ->where('stock_quantity', '>', 0)
                ->count();

            $inStock = $inStockSimple + $inStockVariants;

            // Out of stock
            $outOfStockSimple = Product::where('track_inventory', true)
                ->where('has_variants', false)
                ->where('stock_quantity', '<=', 0)
                ->whereDoesntHave('warehouseStock', function($q) {
                    $q->where('quantity', '>', 0);
                })->count();

            $outOfStockVariants = ProductVariant::whereHas('product', function($q) {
                    $q->where('track_inventory', true);
                })
                ->where('status_key_code', 'VARIANT_ACTIVE')
                ->where('stock_quantity', '<=', 0)
                ->count();

            $outOfStock = $outOfStockSimple + $outOfStockVariants;

            // Low stock
            $lowStockSimple = Product::where('track_inventory', true)
                ->where('has_variants', false)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0)->count();

            $lowStockVariants = ProductVariant::whereHas('product', function($q) {
                    $q->where('track_inventory', true);
                })
                ->where('status_key_code', 'VARIANT_ACTIVE')
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0)->count();

            $lowStock = $lowStockSimple + $lowStockVariants;

            $stats = [
                'total_products' => $totalProducts,
                'total_warehouses' => Warehouse::active()->count(),
                'total_stock' => ProductWarehouseStock::sum('quantity') +
                                Product::where('track_inventory', true)
                                    ->where('has_variants', false)
                                    ->whereDoesntHave('warehouseStock')
                                    ->sum('stock_quantity'),
                'total_reserved' => ProductWarehouseStock::sum('reserved_quantity'),
                'total_available' => ProductWarehouseStock::sum('available_quantity') +
                                    Product::where('track_inventory', true)
                                        ->where('has_variants', false)
                                        ->whereDoesntHave('warehouseStock')
                                        ->sum('stock_quantity'),
                'in_stock' => $inStock,
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock,
            ];
        }

        return response()->json($stats);
    }

    /**
     * Check and create stock alerts
     */
    private function checkStockAlerts(Product $product, Warehouse $warehouse, int $quantity, ProductVariant $variant = null)
    {
        $entity = $variant ?: $product;
        $entityId = $variant ? $variant->id : null;

        // Check for out of stock
        if ($quantity <= 0) {
            StockAlert::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_id' => $entityId,  // ← ADD THIS
                    'warehouse_id' => $warehouse->id,
                    'alert_type' => 'out_of_stock',
                    'is_resolved' => false,
                ],
                [
                    'current_quantity' => $quantity,
                    'threshold_quantity' => 0,
                ]
            );
        }

        // Check for low stock
        if ($quantity > 0 && $quantity <= $entity->low_stock_threshold) {
            StockAlert::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_id' => $entityId,  // ← ADD THIS
                    'warehouse_id' => $warehouse->id,
                    'alert_type' => 'low_stock',
                    'is_resolved' => false,
                ],
                [
                    'current_quantity' => $quantity,
                    'threshold_quantity' => $entity->low_stock_threshold,
                ]
            );
        }

        // Resolve alerts if stock is back to normal
        if ($quantity > $entity->low_stock_threshold) {
            StockAlert::where('product_id', $product->id)
                ->where('variant_id', $entityId)  // ← ADD THIS
                ->where('warehouse_id', $warehouse->id)
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'resolved_by' => auth('admin')->id(),
                ]);
        }
    }

    //
    /**
     * Get movement statistics (AJAX)
     */
    public function getMovementStatistics(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Base query builder
        $baseQuery = InventoryMovement::query();

        // Apply common filters to base query
        if ($request->filled('warehouse_id')) {
            $baseQuery->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('type')) {
            $baseQuery->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        // Clone the query for each calculation
        $totalQuery = clone $baseQuery;
        $addedQuery = clone $baseQuery;
        $removedQuery = clone $baseQuery;

        // Calculate statistics
        $total = $totalQuery->count();
        $added = $addedQuery->where('quantity', '>', 0)->sum('quantity');
        $removed = abs($removedQuery->where('quantity', '<', 0)->sum('quantity')); // Use abs() for removed count

        return response()->json([
            'total' => $total,
            'added' => $added,
            'removed' => $removed,
            'net' => $added - $removed // Changed from + to - since removed is positive now
        ]);
    }

    /**
     * Export movements to CSV
     */
    public function exportMovements(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $query = InventoryMovement::with(['product', 'warehouse', 'creator'])
            ->latest();

        // Apply filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            })->orWhere('reason', 'like', "%{$search}%");
        }

        $movements = $query->get();

        // Generate CSV
        $filename = 'inventory_movements_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $csv = "Date,Time,Product,SKU,Type,Warehouse,Quantity Change,Previous Qty,New Qty,By,Reason\n";

        foreach ($movements as $movement) {
            $csv .= '"' . $movement->created_at->format('Y-m-d') . '",';
            $csv .= '"' . $movement->created_at->format('H:i:s') . '",';
            $csv .= '"' . ($movement->product->name ?? 'N/A') . '",';
            $csv .= '"' . ($movement->product->sku ?? 'N/A') . '",';
            $csv .= '"' . $movement->getTypeLabel() . '",';
            $csv .= '"' . ($movement->warehouse->name ?? 'N/A') . '",';
            $csv .= $movement->quantity . ',';
            $csv .= ($movement->previous_quantity ?? 0) . ',';
            $csv .= ($movement->new_quantity ?? 0) . ',';
            $csv .= '"' . ($movement->creator->name ?? 'System') . '",';
            $csv .= '"' . str_replace('"', '""', $movement->reason ?? '') . '"';
            $csv .= "\n";
        }

        return response($csv, 200, $headers);
    }

    /**
     * Show single movement details
     */
    public function showMovement(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $movement = InventoryMovement::with(['product', 'warehouse', 'fromWarehouse', 'toWarehouse', 'creator'])
            ->findOrFail($id);

        $html = '
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Product Information</h6>
                    <p><strong>Name:</strong> ' . $movement->product->name . '</p>
                    <p><strong>SKU:</strong> ' . $movement->product->sku . '</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Movement Information</h6>
                    <p><strong>Type:</strong> ' . $movement->getTypeBadge() . '</p>
                    <p><strong>Date:</strong> ' . $movement->created_at->format('M d, Y H:i') . '</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Warehouse</h6>
                    <p>' . ($movement->warehouse->name ?? 'N/A') . '</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Quantity Change</h6>
                    <p class="' . ($movement->quantity >= 0 ? 'text-success' : 'text-danger') . '">
                        <strong>' . ($movement->quantity >= 0 ? '+' : '') . $movement->quantity . '</strong>
                    </p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6 class="text-muted">Stock Levels</h6>
                    <p>Previous: <strong>' . ($movement->previous_quantity ?? 'N/A') . '</strong> →
                    New: <strong>' . ($movement->new_quantity ?? 'N/A') . '</strong></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6 class="text-muted">Reason</h6>
                    <p>' . ($movement->reason ?? '<em class="text-muted">No reason provided</em>') . '</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h6 class="text-muted">Created By</h6>
                    <p>' . ($movement->creator->name ?? 'System') . '</p>
                </div>
            </div>
        ';

        return response($html);
    }
}
