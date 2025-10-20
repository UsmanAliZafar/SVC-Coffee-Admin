<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    /**
     * Display warehouses list
     */
    public function index()
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.warehouses.index');
    }

    /**
     * Get warehouses data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Warehouse::withCount(['stock', 'movements', 'alerts'])
                          ->with(['stock' => function($q) {
                              $q->where('quantity', '>', 0);
                          }]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('info', function($warehouse) {
                return '<div>
                    <strong>' . $warehouse->name . '</strong><br>
                    <small class="text-muted">Code: ' . $warehouse->code . '</small>
                </div>';
            })
            ->addColumn('location', function($warehouse) {
                return $warehouse->getFullAddress() ?: '<span class="text-muted">—</span>';
            })
            ->addColumn('contact', function($warehouse) {
                $html = '';
                if ($warehouse->email) {
                    $html .= '<div><i class="bi bi-envelope"></i> ' . $warehouse->email . '</div>';
                }
                if ($warehouse->phone) {
                    $html .= '<div><i class="bi bi-telephone"></i> ' . $warehouse->phone . '</div>';
                }
                return $html ?: '<span class="text-muted">—</span>';
            })
            ->addColumn('stock_info', function($warehouse) {
                $totalStock = $warehouse->getTotalStock();
                $totalValue = $warehouse->getTotalStockValue();
                $stockCount = $warehouse->stock_count ?? 0;

                return '<div>
                    <strong>' . number_format($totalStock) . '</strong> units<br>
                    <small class="text-muted">' . $stockCount . ' products</small><br>
                    <small class="text-success">$' . number_format($totalValue, 2) . '</small>
                </div>';
            })
            ->addColumn('alerts', function($warehouse) {
                $lowStock = $warehouse->getLowStockCount();
                $outOfStock = $warehouse->getOutOfStockCount();

                $html = '';
                if ($outOfStock > 0) {
                    $html .= '<span class="badge bg-danger">' . $outOfStock . ' Out</span> ';
                }
                if ($lowStock > 0) {
                    $html .= '<span class="badge bg-warning text-dark">' . $lowStock . ' Low</span>';
                }

                return $html ?: '<span class="badge bg-success">All Good</span>';
            })
            ->addColumn('status', function($warehouse) {
                $badge = $warehouse->is_active ?
                    '<span class="badge bg-success">Active</span>' :
                    '<span class="badge bg-secondary">Inactive</span>';

                if ($warehouse->is_default) {
                    $badge .= ' <span class="badge bg-primary ms-1">Default</span>';
                }

                return $badge;
            })
            ->addColumn('priority', function($warehouse) {
                return '<span class="badge bg-info">' . $warehouse->priority . '</span>';
            })
            ->addColumn('actions', function($warehouse) {
                $actions = '<div class="btn-group" role="group">';

                if (auth('admin')->user()->hasPermission('inventory.read')) {
                    $actions .= '<a href="' . route('admin.warehouses.show', $warehouse->id) . '"
                        class="btn btn-sm btn-info" title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>';
                }

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    $actions .= '<a href="' . route('admin.warehouses.edit', $warehouse->id) . '"
                        class="btn btn-sm btn-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>';

                    if (!$warehouse->is_default) {
                        $actions .= '<button type="button" class="btn btn-sm btn-success"
                            onclick="setDefaultWarehouse(\'' . $warehouse->id . '\')" title="Set as Default">
                            <i class="bi bi-star"></i>
                        </button>';
                    }

                    $statusIcon = $warehouse->is_active ? 'toggle-on' : 'toggle-off';
                    $actions .= '<button type="button" class="btn btn-sm btn-warning"
                        onclick="toggleWarehouseStatus(\'' . $warehouse->id . '\')" title="Toggle Status">
                        <i class="bi bi-' . $statusIcon . '"></i>
                    </button>';
                }

                if (auth('admin')->user()->hasPermission('inventory.delete') && !$warehouse->is_default && !$warehouse->hasStock()) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger"
                        onclick="deleteWarehouse(\'' . $warehouse->id . '\')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['info', 'location', 'contact', 'stock_info', 'alerts', 'status', 'priority', 'actions'])
            ->make(true);
    }

    /**
     * Show create warehouse form
     */
    public function create()
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.warehouses.create');
    }

    /**
     * Store new warehouse
     */
    public function store(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $warehouse = Warehouse::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully',
                'warehouse' => $warehouse,
                'redirect' => route('admin.warehouses.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show warehouse details
     */
    public function show(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouse = Warehouse::with(['stock.product'])
                              ->withCount(['stock', 'movements', 'alerts'])
                              ->findOrFail($id);

        return view('admin.warehouses.show', compact('warehouse'));
    }

    /**
     * Show warehouse stock
     */
    public function stock(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouse = Warehouse::findOrFail($id);

        return view('admin.warehouses.stock', compact('warehouse'));
    }

    /**
     * Get warehouse stock data (AJAX)
     */
    public function getStockData(Request $request, string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouse = Warehouse::findOrFail($id);

        $query = ProductWarehouseStock::with(['product'])
                                      ->where('warehouse_id', $id);

        // Apply filters
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereHas('product', function($q) {
                        $q->whereColumn('product_warehouse_stock.quantity', '<=', 'products.low_stock_threshold')
                          ->where('product_warehouse_stock.quantity', '>', 0);
                    });
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

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
            ->addColumn('value', function($stock) {
                $value = $stock->quantity * $stock->product->price;
                return '<span class="text-success">$' . number_format($value, 2) . '</span>';
            })
            ->addColumn('actions', function($stock) use ($warehouse) {
                $actions = '<div class="btn-group" role="group">';

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary adjust-stock"
                        data-id="' . $stock->id . '"
                        data-product="' . htmlspecialchars($stock->product->name) . '"
                        data-warehouse="' . htmlspecialchars($warehouse->name) . '"
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
            ->rawColumns(['product_info', 'quantity', 'available', 'reserved', 'location', 'value', 'actions'])
            ->make(true);
    }

    /**
     * Show edit warehouse form
     */
    public function edit(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            abort(403, 'Unauthorized access');
        }

        $warehouse = Warehouse::findOrFail($id);

        return view('admin.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update warehouse
     */
    public function update(Request $request, string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $warehouse = Warehouse::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $warehouse->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully',
                'warehouse' => $warehouse,
                'redirect' => route('admin.warehouses.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update(['is_active' => !$warehouse->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse status updated successfully',
            'is_active' => $warehouse->is_active
        ]);
    }

    /**
     * Set warehouse as default
     */
    public function setDefault(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            // Remove default from all warehouses
            Warehouse::where('is_default', true)->update(['is_default' => false]);

            // Set new default
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->update(['is_default' => true, 'is_active' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Default warehouse updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to set default warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete warehouse
     */
    public function destroy(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $warehouse = Warehouse::findOrFail($id);

        // Check if it's the default warehouse
        if ($warehouse->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the default warehouse. Please set another warehouse as default first.'
            ], 400);
        }

        // Check if warehouse has stock
        if ($warehouse->hasStock()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with existing stock. Please transfer or remove all stock first.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $warehouse->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse statistics
     */
    public function statistics(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouse = Warehouse::findOrFail($id);

        $stats = [
            'total_products' => $warehouse->stock()->count(),
            'total_stock' => $warehouse->getTotalStock(),
            'total_value' => $warehouse->getTotalStockValue(),
            'in_stock' => $warehouse->stock()->where('quantity', '>', 0)->count(),
            'out_of_stock' => $warehouse->getOutOfStockCount(),
            'low_stock' => $warehouse->getLowStockCount(),
            'available_stock' => $warehouse->stock()->sum('available_quantity'),
            'reserved_stock' => $warehouse->stock()->sum('reserved_quantity'),
            'recent_movements' => $warehouse->movements()->recent(7)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export warehouse stock report
     */
    public function exportStock(string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouse = Warehouse::findOrFail($id);

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="warehouse_' . $warehouse->code . '_stock_' . date('Y-m-d') . '.csv"',
        ];

        $csv = "Product Name,SKU,Quantity,Available,Reserved,Location,Value\n";

        foreach ($warehouse->stock as $stock) {
            $product = $stock->product;
            $value = $stock->quantity * $product->price;

            $csv .= '"' . $product->name . '",';
            $csv .= '"' . $product->sku . '",';
            $csv .= $stock->quantity . ',';
            $csv .= $stock->available_quantity . ',';
            $csv .= $stock->reserved_quantity . ',';
            $csv .= '"' . ($stock->location ?? '') . '",';
            $csv .= number_format($value, 2) . "\n";
        }

        return response($csv, 200, $headers);
    }
}
