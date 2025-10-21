<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use App\Models\StockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

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
            // Stock by specific warehouse
            $query = ProductWarehouseStock::with(['product', 'warehouse'])
                ->where('warehouse_id', $warehouseId);
        } else {
            // All products with total stock
            $query = Product::with(['warehouseStock', 'category'])
                ->where('track_inventory', true);
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
            return $this->getAllProductsStockDataTable($query);
        }
    }

    /**
     * DataTable for warehouse stock
     */
    private function getWarehouseStockDataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('product_info', function($stock) {
                $product = $stock->product;
                return '<div>
                    <strong>' . $product->name . '</strong><br>
                    <small class="text-muted">SKU: ' . $product->sku . '</small>
                </div>';
            })
            ->addColumn('quantity', function($stock) {
                $class = $stock->quantity <= 0 ? 'text-danger' :
                        ($stock->isLowStock() ? 'text-warning' : 'text-success');
                return '<strong class="' . $class . '">' . $stock->quantity . '</strong>';
            })
            ->addColumn('available', function($stock) {
                return '<span class="badge bg-success">' . $stock->available_quantity . '</span>';
            })
            ->addColumn('reserved', function($stock) {
                return $stock->reserved_quantity > 0 ?
                    '<span class="badge bg-warning">' . $stock->reserved_quantity . '</span>' :
                    '<span class="text-muted">0</span>';
            })
            ->addColumn('location', function($stock) {
                return $stock->location ?? '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function($stock) {
                $actions = '<div class="btn-group" role="group">';

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary adjust-stock"
                        data-id="' . $stock->id . '"
                        data-product="' . htmlspecialchars($stock->product->name) . '"
                        data-warehouse="' . htmlspecialchars($stock->warehouse->name) . '"
                        data-quantity="' . $stock->quantity . '"
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
            // FIX: Tell DataTables not to order by server-side for these columns
            ->rawColumns(['product_info', 'quantity', 'available', 'reserved', 'location', 'actions'])
            ->skipTotalRecords() // Optional: skip total records count for performance
            ->make(true);
    }

    /**
     * DataTable for all products stock
     * FIXED: Shows product stock when no warehouse stock exists
     */
    private function getAllProductsStockDataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('product_info', function($product) {
                return '<div>
                    <strong>' . $product->name . '</strong><br>
                    <small class="text-muted">SKU: ' . $product->sku . '</small>
                </div>';
            })
            ->addColumn('total_stock', function($product) {
                // Check if product has warehouse stock
                $warehouseTotal = $product->warehouseStock()->sum('quantity');

                // Use warehouse stock if available, otherwise use product stock
                $total = $warehouseTotal > 0 ? $warehouseTotal : $product->stock_quantity;

                $class = $total <= 0 ? 'text-danger' :
                        ($product->isLowStock() ? 'text-warning' : 'text-success');

                // Add indicator if using product stock vs warehouse stock
                $indicator = '';
                if ($warehouseTotal <= 0 && $product->stock_quantity > 0) {
                    $indicator = ' <i class="bi bi-info-circle text-info" title="Product stock (no warehouse assigned)" data-bs-toggle="tooltip"></i>';
                }

                return '<strong class="' . $class . '">' . $total . $indicator . '</strong>';
            })
            ->addColumn('threshold', function($product) {
                return '<span class="badge bg-secondary">' . ($product->low_stock_threshold ?? 0) . '</span>';
            })
            ->addColumn('warehouse_name', function($product) {
                $warehouses = $product->warehouseStock;

                if ($warehouses->count() === 0) {
                    return '<span class="badge bg-warning text-dark">No warehouse</span>';
                }

                if ($warehouses->count() === 1) {
                    return $warehouses->first()->warehouse->name;
                }

                return '<span class="badge bg-info">' . $warehouses->count() . ' warehouses</span>';
            })
            ->addColumn('available', function($product) {
                $warehouseAvailable = $product->warehouseStock()->sum('available_quantity');

                // If no warehouse stock, show product stock as available
                $available = $warehouseAvailable > 0 ? $warehouseAvailable : $product->stock_quantity;

                return '<span class="badge bg-success">' . $available . '</span>';
            })
            ->addColumn('reserved', function($product) {
                $reserved = $product->getTotalReservedStock();
                return $reserved > 0 ?
                    '<span class="badge bg-warning">' . $reserved . '</span>' :
                    '<span class="text-muted">0</span>';
            })
            ->addColumn('warehouses', function($product) {
                $warehouses = $product->warehouseStock;

                // If no warehouse stock, show message
                if ($warehouses->count() === 0) {
                    return '<div>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i> No warehouse assigned
                        </span>
                        <br>
                        <small class="text-muted">Stock: ' . $product->stock_quantity . ' (Product level)</small>
                    </div>';
                }

                $html = '';
                foreach ($warehouses as $stock) {
                    $html .= '<div class="mb-1">
                        <small><strong>' . $stock->warehouse->name . ':</strong> ' . $stock->quantity . '</small>
                    </div>';
                }
                return $html;
            })
            ->addColumn('actions', function($product) {
                $actions = '<div class="btn-group" role="group">';

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary adjust-product-stock"
                        data-id="' . $product->id . '"
                        data-name="' . htmlspecialchars($product->name) . '"
                        title="Adjust Stock">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                $actions .= '<a href="' . route('admin.products.show', $product->id) . '"
                    class="btn btn-sm btn-info" title="View Product">
                    <i class="bi bi-eye"></i>
                </a>';

                $actions .= '</div>';
                return $actions;
            })
            ->addColumn('product_id', function($product) {
                return $product->id;
            })
            ->addColumn('product_name', function($product) {
                return $product->name;
            })
            ->addColumn('warehouse_id', function($product) {
                $defaultWarehouse = $product->warehouseStock()->first();
                return $defaultWarehouse ? $defaultWarehouse->warehouse_id : '';
            })
            ->addColumn('quantity', function($product) {
                $warehouseTotal = $product->warehouseStock()->sum('quantity');
                return $warehouseTotal > 0 ? $warehouseTotal : $product->stock_quantity;
            })
            ->rawColumns(['product_info','threshold','warehouse_name', 'total_stock', 'available', 'reserved', 'warehouses', 'actions'])
            ->make(true);
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'action_type' => 'required|in:set,add,reduce',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500',
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
            $warehouse = Warehouse::findOrFail($request->warehouse_id);

            // Get or create stock record
            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

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
                'warehouse_id' => $warehouse->id,
                'type' => 'adjustment',
                'quantity' => $quantityChange,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $stock->fresh()->quantity,
                'reason' => $request->reason ?? 'Manual adjustment',
            ]);

            // Update product total stock
            $product->updateTotalStock();

            // Check for alerts
            $this->checkStockAlerts($product, $warehouse, $stock->fresh()->quantity);

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

        $query = InventoryMovement::with(['product', 'warehouse', 'fromWarehouse', 'toWarehouse', 'creator'])
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
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('product_info', function($movement) {
                return '<div>
                    <strong>' . $movement->product->name . '</strong><br>
                    <small class="text-muted">SKU: ' . $movement->product->sku . '</small>
                </div>';
            })
            ->addColumn('type_badge', function($movement) {
                return $movement->getTypeBadge();
            })
            ->addColumn('warehouse_info', function($movement) {
                if ($movement->type === 'transfer') {
                    return '<div>
                        <small><strong>From:</strong> ' . ($movement->fromWarehouse->name ?? 'N/A') . '</small><br>
                        <small><strong>To:</strong> ' . ($movement->toWarehouse->name ?? 'N/A') . '</small>
                    </div>';
                }
                return $movement->warehouse ? $movement->warehouse->name : '<span class="text-muted">N/A</span>';
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
                return '<div>
                    <small>' . $movement->created_at->format('M d, Y H:i') . '</small><br>
                    <small class="text-muted">' . ($movement->creator->name ?? 'System') . '</small>
                </div>';
            })
            ->rawColumns(['product_info', 'type_badge', 'warehouse_info', 'quantity_change', 'stock_levels', 'created_info'])
            ->make(true);
    }

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
            // Overall stats - Include products without warehouse stock
            $totalProducts = Product::where('track_inventory', true)->count();

            // Count products with warehouse stock > 0 OR product stock > 0
            $inStock = Product::where('track_inventory', true)
                ->where(function($q) {
                    $q->whereHas('warehouseStock', function($wq) {
                        $wq->where('quantity', '>', 0);
                    })->orWhere('stock_quantity', '>', 0);
                })->count();

            // Out of stock: warehouse stock = 0 AND product stock = 0
            $outOfStock = Product::where('track_inventory', true)
                ->where('stock_quantity', '<=', 0)
                ->whereDoesntHave('warehouseStock', function($q) {
                    $q->where('quantity', '>', 0);
                })->count();

            // Low stock products
            $lowStock = Product::where('track_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0)->count();

            $stats = [
                'total_products' => $totalProducts,
                'total_warehouses' => Warehouse::active()->count(),
                'total_stock' => ProductWarehouseStock::sum('quantity') +
                                Product::where('track_inventory', true)
                                    ->whereDoesntHave('warehouseStock')
                                    ->sum('stock_quantity'),
                'total_reserved' => ProductWarehouseStock::sum('reserved_quantity'),
                'total_available' => ProductWarehouseStock::sum('available_quantity') +
                                    Product::where('track_inventory', true)
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
    private function checkStockAlerts(Product $product, Warehouse $warehouse, int $quantity)
    {
        // Check for out of stock
        if ($quantity <= 0) {
            StockAlert::updateOrCreate(
                [
                    'product_id' => $product->id,
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
        if ($quantity > 0 && $quantity <= $product->low_stock_threshold) {
            StockAlert::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'alert_type' => 'low_stock',
                    'is_resolved' => false,
                ],
                [
                    'current_quantity' => $quantity,
                    'threshold_quantity' => $product->low_stock_threshold,
                ]
            );
        }

        // Resolve alerts if stock is back to normal
        if ($quantity > $product->low_stock_threshold) {
            StockAlert::where('product_id', $product->id)
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

        $query = InventoryMovement::query();

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

        $total = $query->count();
        $added = $query->where('quantity', '>', 0)->sum('quantity');
        $removed = $query->where('quantity', '<', 0)->sum('quantity');

        return response()->json([
            'total' => $total,
            'added' => $added,
            'removed' => $removed,
            'net' => $added + $removed
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
