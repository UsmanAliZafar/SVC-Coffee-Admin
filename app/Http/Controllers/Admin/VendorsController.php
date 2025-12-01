<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

// Models
use App\Models\Vendor;
use App\Models\SystemStatus;

class VendorsController extends Controller
{
    /**
     * Display a listing of vendors
     */
    public function index()
    {
        if (!auth('admin')->user()->hasPermission('vendors.read')) {
            abort(403, 'Unauthorized access');
        }

        $statusList = SystemStatus::where('module', 'vendors')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.vendors.index', compact('statusList'));
    }

    /**
     * Get vendors data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('vendors.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Vendor::with(['status'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function($vendor) {
                if (auth('admin')->user()->hasPermission('vendors.delete')) {
                    return '<input type="checkbox" class="form-check-input vendor-checkbox" value="' . $vendor->id . '">';
                }
                return '';
            })
            ->addColumn('vendor_info', function($vendor) {
                $html = '<div>';
                $html .= '<strong><a href="' . route('admin.vendors.show', $vendor->id) . '">' . htmlspecialchars($vendor->name) . '</a></strong>';

                if ($vendor->company_name) {
                    $html .= '<br><small class="text-muted">' . htmlspecialchars($vendor->company_name) . '</small>';
                }

                $html .= '<br><small class="text-muted">';
                $html .= '<i class="bi bi-envelope"></i> ' . htmlspecialchars($vendor->email);

                if ($vendor->phone) {
                    $html .= ' | <i class="bi bi-telephone"></i> ' . htmlspecialchars($vendor->phone);
                }

                $html .= '</small></div>';

                return $html;
            })
            ->addColumn('location', function($vendor) {
                if ($vendor->city || $vendor->country) {
                    return '<small>' .
                        ($vendor->city ? htmlspecialchars($vendor->city) . ', ' : '') .
                        htmlspecialchars($vendor->country) .
                        '</small>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('products_count', function($vendor) {
                $count = $vendor->products_count ?? 0;
                return '<span class="badge bg-primary">' . $count . '</span>';
            })
            ->addColumn('total_purchases', function($vendor) {
                return '<span class="badge bg-success">' . $vendor->getFormattedTotalPurchases() . '</span>';
            })
            ->addColumn('status_badge', function($vendor) {
                return $vendor->getStatusBadge();
            })
            ->addColumn('actions', function($vendor) {
                $actions = '<div class="btn-group" role="group">';

                // Sync Individual Button
                if (auth('admin')->user()->hasPermission('vendors.update')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-info sync-vendor-btn"
                        data-id="' . $vendor->id . '"
                        title="Sync Product Count & Purchases">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>';
                }

                if (auth('admin')->user()->hasPermission('vendors.read')) {
                    $actions .= '<a href="' . route('admin.vendors.show', $vendor->id) . '" class="btn btn-sm btn-info" title="View">
                        <i class="bi bi-eye"></i>
                    </a>';
                }

                if (auth('admin')->user()->hasPermission('vendors.update')) {
                    $actions .= '<a href="' . route('admin.vendors.edit', $vendor->id) . '" class="btn btn-sm btn-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>';
                }

                if (auth('admin')->user()->hasPermission('vendors.delete')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger delete-vendor" data-id="' . $vendor->id . '" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['checkbox', 'vendor_info', 'location', 'products_count', 'total_purchases', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Sync ALL vendors' products count
     */
    public function syncProductsCount(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('vendors.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Reset all vendor product counts to 0
            DB::table('vendors')->update(['products_count' => 0]);

            // Get actual product counts per vendor
            $vendorCounts = DB::table('products')
                ->whereNull('deleted_at')
                ->whereNotNull('vendor_id')
                ->select('vendor_id', DB::raw('COUNT(*) as total'))
                ->groupBy('vendor_id')
                ->get();

            // Update each vendor's product count
            $updated = 0;
            foreach ($vendorCounts as $item) {
                DB::table('vendors')
                    ->where('id', $item->vendor_id)
                    ->update(['products_count' => $item->total]);
                $updated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced product counts for {$updated} vendors!",
                'vendors_updated' => $updated
            ]);

        } catch (\Exception $e) {
            \Log::error('Error syncing vendor product counts: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync product counts. Please try again.'
            ], 500);
        }
    }

    /**
     * Sync INDIVIDUAL vendor's products count and total purchases
     */
    public function syncIndividual($id)
    {
        if (!auth('admin')->user()->hasPermission('vendors.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $vendor = Vendor::findOrFail($id);

            // Sync products count
            $productsCount = $vendor->products()->count();

            // Sync total purchases (if you have purchase_orders table)
            $totalPurchases = DB::table('purchase_orders')
                ->where('vendor_id', $id)
                ->where('status_key_code', 'completed')
                ->sum('total_amount') ?? 0;

            // Update vendor
            $vendor->update([
                'products_count' => $productsCount,
                'total_purchases' => $totalPurchases
            ]);

            return response()->json([
                'success' => true,
                'message' => "Synced successfully!",
                'data' => [
                    'products_count' => $productsCount,
                    'total_purchases' => number_format($totalPurchases, 2)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error syncing vendor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync. Please try again.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new vendor
     */
    public function create()
    {
        if (!auth('admin')->user()->hasPermission('vendors.create')) {
            abort(403, 'Unauthorized access');
        }

        $statusList = SystemStatus::where('module', 'vendors')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $countries = $this->getCountriesList();
        $currencies = get_currencies();

        return view('admin.vendors.create', compact('statusList', 'countries', 'currencies'));
    }

    /**
     * Store a newly created vendor
     */
    public function store(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('vendors.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:vendors,email',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_routing_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'currency' => 'nullable|string|max:10',
            'status_key_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $vendorData = $request->all();
            $vendorData['created_by'] = auth('admin')->id();

            $vendor = Vendor::create($vendorData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully',
                'vendor_id' => $vendor->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified vendor
     */
    public function show($id)
    {
        if (!auth('admin')->user()->hasPermission('vendors.read')) {
            abort(403, 'Unauthorized access');
        }

        $vendor = Vendor::with(['status', 'products', 'creator', 'updater'])
            ->withCount('products')
            ->findOrFail($id);

        return view('admin.vendors.show', compact('vendor'));
    }

    /**
     * Show the form for editing the specified vendor
     */
    public function edit($id)
    {
        if (!auth('admin')->user()->hasPermission('vendors.update')) {
            abort(403, 'Unauthorized access');
        }

        $vendor = Vendor::findOrFail($id);

        $statusList = SystemStatus::where('module', 'vendors')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $countries = $this->getCountriesList();
        $currencies = get_currencies();

        return view('admin.vendors.edit', compact('vendor', 'statusList', 'countries', 'currencies'));
    }

    /**
     * Update the specified vendor
     */
    public function update(Request $request, $id)
    {
        if (!auth('admin')->user()->hasPermission('vendors.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $vendor = Vendor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:vendors,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_routing_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'currency' => 'nullable|string|max:10',
            'status_key_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $vendorData = $request->all();
            $vendorData['updated_by'] = auth('admin')->id();

            $vendor->update($vendorData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendor updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified vendor
     */
    public function destroy($id)
    {
        if (!auth('admin')->user()->hasPermission('vendors.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $vendor = Vendor::findOrFail($id);

            // Check if vendor has products
            if ($vendor->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete vendor with associated products'
                ], 400);
            }

            $vendor->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendor deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vendor statistics
     */
    public function statistics(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('vendors.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('status_key_code', 'VENDOR_ACTIVE')->count(),
            'inactive' => Vendor::where('status_key_code', 'VENDOR_INACTIVE')->count(),
            'total_products' => Vendor::sum('products_count'),
            'total_purchases' => Vendor::sum('total_purchases'),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk delete vendors
     */
    public function bulkDelete(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('vendors.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'vendor_ids' => 'required|array',
            'vendor_ids.*' => 'exists:vendors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $vendors = Vendor::whereIn('id', $request->vendor_ids)->get();

            foreach ($vendors as $vendor) {
                // Check for products
                if ($vendor->products()->count() > 0) {
                    continue; // Skip vendors with products
                }

                $vendor->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->vendor_ids) . ' vendor(s) deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vendors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get countries list
     */
    private function getCountriesList(): array
    {
        return [
            'United States', 'Canada', 'United Kingdom', 'Australia',
            'Germany', 'France', 'Italy', 'Spain', 'China', 'Japan',
            'India', 'Pakistan', 'Bangladesh', 'Brazil', 'Mexico',
        ];
    }
}
