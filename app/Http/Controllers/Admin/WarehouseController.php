<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
// Models
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;


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

        // Handle statistics request
        if ($request->has('get_stats')) {
            $warehouses = Warehouse::all();

            $stats = [
                'total' => $warehouses->count(),
                'active' => $warehouses->where('is_active', true)->count(),
                'total_stock' => $warehouses->sum(function($w) {
                    return $w->getTotalStock();
                }),
                'total_value' => $warehouses->sum(function($w) {
                    return $w->getTotalStockValue();
                })
            ];

            return response()->json(['stats' => $stats]);
        }

        // Handle card view request
        if ($request->input('view') === 'card') {
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

            $warehouses = $query->orderBy('priority', 'desc')
                            ->orderBy('is_default', 'desc')
                            ->get();

            $data = $warehouses->map(function($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'is_active' => $warehouse->is_active,
                    'is_default' => $warehouse->is_default,
                    'priority' => $warehouse->priority,
                    'full_address' => $warehouse->getFullAddress(),
                    'stock_count' => $warehouse->stock_count ?? 0,
                    'total_stock' => $warehouse->getTotalStock(),
                    'total_value' => $warehouse->getTotalStockValue(),
                    'low_stock_count' => $warehouse->getLowStockCount(),
                    'out_of_stock_count' => $warehouse->getOutOfStockCount(),
                    'has_stock' => $warehouse->hasStock(),
                    'can_update' => auth('admin')->user()->hasPermission('inventory.update'),
                    'can_delete' => auth('admin')->user()->hasPermission('inventory.delete'),
                ];
            });

            return response()->json(['data' => $data]);
        }

        // Handle DataTable request (table view)
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
                    <strong>' . e($warehouse->name) . '</strong><br>
                    <small class="text-muted">Code: ' . e($warehouse->code) . '</small>
                </div>';
            })
            ->addColumn('location', function($warehouse) {
                return $warehouse->getFullAddress() ?: '<span class="text-muted">—</span>';
            })
            ->addColumn('contact', function($warehouse) {
                $html = '';
                if ($warehouse->email) {
                    $html .= '<div><i class="bi bi-envelope"></i> ' . e($warehouse->email) . '</div>';
                }
                if ($warehouse->phone) {
                    $html .= '<div><i class="bi bi-telephone"></i> ' . e($warehouse->phone) . '</div>';
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
                    <small class="text-success">' . store_currency_symbol() . number_format($totalValue, 2) . '</small>
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
            // DataTables may send search as an array: ['value' => '...']
            $search = $request->input('search.value', $request->input('search'));

            // Normalize arrays to string (avoid "Array to string conversion")
            if (is_array($search)) {
                $search = trim(implode(' ', $search));
            }

            if ($search !== null && $search !== '') {
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }
        }

        return DataTables::of($query)
            ->addColumn('product_info', function($stock) {
                $product = $stock->product;

                // Check if product exists
                if (!$product) {
                    return '<div>
                        <span class="badge bg-danger">
                            <i class="bi bi-trash"></i> Product Deleted
                        </span><br>
                        <small class="text-muted">Product ID: ' . $stock->product_id . '</small>
                    </div>';
                }

                return '<div>
                    <strong>' . htmlspecialchars($product->name) . '</strong><br>
                    <small class="text-muted">SKU: ' . htmlspecialchars($product->sku) . '</small>
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
                // Check if product exists before calculating value
                if (!$stock->product) {
                    return '<span class="text-muted">—</span>';
                }

                $value = $stock->quantity * $stock->product->price;
                return '<span class="text-success">'. store_currency_symbol() . number_format($value, 2) . '</span>';
            })
            ->addColumn('actions', function($stock) use ($warehouse) {
                $actions = '<div class="btn-group" role="group">';

                // Check if product exists
                if (!$stock->product) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger" disabled title="Product deleted">
                        <i class="bi bi-x-circle"></i>
                    </button>';
                    $actions .= '</div>';
                    return $actions;
                }

                if (auth('admin')->user()->hasPermission('inventory.update')) {
                    // $actions .= '<button type="button" class="btn btn-sm btn-primary adjust-stock"
                    //     data-id="' . $stock->id . '"
                    //     data-product="' . htmlspecialchars($stock->product->name) . '"
                    //     data-warehouse="' . htmlspecialchars($warehouse->name) . '"
                    //     data-quantity="' . $stock->quantity . '"
                    //     title="Adjust Stock">
                    //     <i class="bi bi-pencil"></i>
                    // </button>';
                    $actions .= '<a href="' . route('admin.products.edit', $stock->product_id) . '" class="btn btn-sm btn-primary" title="Edit Product" target="_blank">
                        <i class="bi bi-pencil"></i>
                    </a>';
                }

                $actions .= '<a href="' . route('admin.products.show', $stock->product_id) . '"
                    class="btn btn-sm btn-info" title="View Product" target="_blank">
                    <i class="bi bi-eye"></i>
                </a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['product_info', 'quantity', 'available', 'reserved', 'location', 'value', 'actions'])
            ->make(true);
    }

    /**
     * Get warehouse movements data (AJAX) for DataTable
     */
    public function getMovementsData(Request $request, string $id)
    {
        if (!auth('admin')->user()->hasPermission('inventory.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $warehouse = Warehouse::findOrFail($id);

        $query = InventoryMovement::with(['product', 'variant', 'fromWarehouse', 'toWarehouse', 'creator'])
                                ->where(function($q) use ($id) {
                                    $q->where('warehouse_id', $id)
                                        ->orWhere('from_warehouse_id', $id)
                                        ->orWhere('to_warehouse_id', $id);
                                });

        // Apply filters
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
            $search = $request->input('search.value', $request->input('search'));
            if (is_array($search)) {
                $search = trim(implode(' ', $search));
            }

            if ($search !== null && $search !== '') {
                $query->where(function($q) use ($search) {
                    $q->whereHas('product', function($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                    })
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
                });
            }
        }

        return DataTables::of($query)
            ->addColumn('date', function($movement) {
                return '<div>
                    <strong>' . $movement->created_at->format('M d, Y') . '</strong><br>
                    <small class="text-muted">' . $movement->created_at->format('h:i A') . '</small>
                </div>';
            })
            ->addColumn('product_info', function($movement) {
                if (!$movement->product) {
                    return '<span class="badge bg-danger">Product Deleted</span>';
                }

                $html = '<div><strong>' . htmlspecialchars($movement->product->name) . '</strong><br>';
                $html .= '<small class="text-muted">SKU: ' . htmlspecialchars($movement->product->sku) . '</small>';

                if ($movement->variant) {
                    $html .= '<br><small class="text-info">' . htmlspecialchars($movement->variant->getFullName()) . '</small>';
                }

                $html .= '</div>';
                return $html;
            })
            ->addColumn('type_badge', function($movement) {
                return $movement->getTypeBadge();
            })
            ->addColumn('quantity_change', function($movement) {
                $class = $movement->quantity > 0 ? 'text-success' : 'text-danger';
                $icon = $movement->quantity > 0 ? 'arrow-up' : 'arrow-down';

                return '<span class="fw-bold ' . $class . '">
                    <i class="bi bi-' . $icon . '"></i> ' . $movement->getFormattedQuantity() . '
                </span>';
            })
            ->addColumn('warehouse_info', function($movement) use ($id) {
                if ($movement->type === 'transfer') {
                    $from = $movement->fromWarehouse ? htmlspecialchars($movement->fromWarehouse->name) : 'N/A';
                    $to = $movement->toWarehouse ? htmlspecialchars($movement->toWarehouse->name) : 'N/A';

                    return '<div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">' . $from . '</span>
                        <i class="bi bi-arrow-right"></i>
                        <span class="badge bg-primary">' . $to . '</span>
                    </div>';
                }

                return '<span class="text-muted">—</span>';
            })
            ->addColumn('reason_notes', function($movement) {
                $html = '<div>';
                if ($movement->reason) {
                    $html .= '<strong>' . htmlspecialchars(Str::limit($movement->reason, 40)) . '</strong>';
                }
                if ($movement->notes) {
                    $html .= '<br><small class="text-muted">' . htmlspecialchars(Str::limit($movement->notes, 50)) . '</small>';
                }
                if (!$movement->reason && !$movement->notes) {
                    $html .= '<span class="text-muted">—</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('created_by', function($movement) {
                if ($movement->creator) {
                    return '<small>' . htmlspecialchars($movement->creator->name) . '</small>';
                }
                return '<span class="text-muted">System</span>';
            })
            ->rawColumns(['date', 'product_info', 'type_badge', 'quantity_change', 'warehouse_info', 'reason_notes', 'created_by'])
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
