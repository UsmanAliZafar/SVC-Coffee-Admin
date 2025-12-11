<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\SystemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    /**
     * Display listing page
     */
    public function index()
    {
        $statusList = SystemStatus::where('module', 'coupons')->get();
        $discountTypes = [
            'percentage' => 'Percentage Discount',
            'fixed_amount' => 'Fixed Amount',
            'free_shipping' => 'Free Shipping',
            'buy_x_get_y' => 'Buy X Get Y',
        ];

        return view('admin.coupons.index', compact('statusList', 'discountTypes'));
    }

    /**
     * Ajax DataTable data
     */
    public function getData(Request $request)
    {
        $query = Coupon::with(['createdBy', 'updatedBy'])
            ->withCount('usages')->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active()->valid();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('valid_until', '<', now());
            } elseif ($request->status === 'scheduled') {
                $query->where('valid_from', '>', now());
            }
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($coupon) {
                return '<input type="checkbox" class="coupon-checkbox" value="' . $coupon->id . '">';
            })
            ->addColumn('code_badge', function ($coupon) {
                $color = $coupon->is_active ? '#5B914C' : '#6c757d';
                return '<span class="badge" style="background-color: ' . $color . '; font-size: 0.9rem; padding: 0.5rem 0.75rem;">'
                    . $coupon->code .
                    '</span>';
            })
            ->addColumn('name_link', function ($coupon) {
                return '<a href="' . route('admin.coupons.show', $coupon->id) . '" class="fw-semibold">'
                    . $coupon->name .
                    '</a>';
            })
            ->addColumn('discount_badge', function ($coupon) {
                $icons = [
                    'percentage' => 'bi-percent',
                    'fixed_amount' => store_currency_symbol(),
                    'free_shipping' => 'bi-truck',
                    'buy_x_get_y' => 'bi-gift',
                ];
                $colors = [
                    'percentage' => 'primary',
                    'fixed_amount' => 'success',
                    'free_shipping' => 'info',
                    'buy_x_get_y' => 'warning',
                ];

                $icon = $icons[$coupon->discount_type] ?? 'bi-tag';
                $color = $colors[$coupon->discount_type] ?? 'secondary';

                return '<span class="badge bg-' . $color . '"><i class="bi ' . $icon . '"></i> '
                    . $coupon->getFormattedDiscountValue() .
                    '</span>';
            })
            ->addColumn('usage_stats', function ($coupon) {
                $used = $coupon->total_used ?? 0;
                $limit = $coupon->usage_limit_total;

                if ($limit && $limit > 0) {
                    $percentage = $limit > 0 ? min(($used / $limit) * 100, 100) : 0;

                    // Custom color logic using your brand color
                    if ($percentage >= 90) {
                        $color = '#dc3545'; // Red for critical (90-100%)
                    } elseif ($percentage >= 70) {
                        $color = '#ffc107'; // Yellow/Warning (70-89%)
                    } else {
                        $color = '#5B914C'; // Your brand green (0-69%)
                    }

                    return '<div class="text-center">
                        <div class="progress" style="height: 20px; background-color: rgba(91, 145, 76, 0.1);">
                            <div class="progress-bar" role="progressbar"
                                style="width: ' . $percentage . '%; background-color: ' . $color . ';"
                                aria-valuenow="' . $used . '"
                                aria-valuemin="0"
                                aria-valuemax="' . $limit . '">
                                <span style="color: white; font-weight: 600;">' . $used . ' / ' . $limit . '</span>
                            </div>
                        </div>
                    </div>';
                } else {
                    return '<span class="badge" style="background-color: #5B914C; color: white;">' . $used . ' / Unlimited</span>';
                }
            })
            ->addColumn('validity', function ($coupon) {
                $now = now();
                $html = '<small>';

                if ($coupon->valid_from) {
                    $html .= '<div><strong>From:</strong> ' . $coupon->valid_from->format('M d, Y') . '</div>';
                }

                if ($coupon->valid_until) {
                    $isExpired = $now->gt($coupon->valid_until);
                    $color = $isExpired ? 'danger' : 'success';
                    $html .= '<div><strong>Until:</strong> <span class="text-' . $color . '">'
                        . $coupon->valid_until->format('M d, Y') . '</span></div>';
                } else {
                    $html .= '<div><strong>Until:</strong> <span class="text-muted">No expiry</span></div>';
                }

                $html .= '</small>';
                return $html;
            })
            ->addColumn('status_badge', function ($coupon) {
                $statusLabel = $coupon->getStatusLabel();
                $badges = [
                    'Active' => 'success',
                    'Inactive' => 'secondary',
                    'Expired' => 'danger',
                    'Scheduled' => 'info',
                    'Limit Reached' => 'warning',
                ];

                $color = $badges[$statusLabel] ?? 'secondary';

                $badge = '<span class="badge bg-' . $color . '">' . $statusLabel . '</span>';

                if ($coupon->is_featured) {
                    $badge .= ' <span class="badge bg-warning text-dark ms-1"><i class="bi bi-star-fill"></i> Featured</span>';
                }

                return $badge;
            })
            ->addColumn('actions', function ($coupon) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';

                if (auth('admin')->user()->hasPermission('coupons.read')) {
                    $actions .= '<a href="' . route('admin.coupons.show', $coupon->id) . '"
                        class="btn btn-outline-primary" title="View">
                        <i class="bi bi-eye"></i>
                    </a>';
                }

                if (auth('admin')->user()->hasPermission('coupons.update')) {
                    $actions .= '<a href="' . route('admin.coupons.edit', $coupon->id) . '"
                        class="btn btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>';

                    $actions .= '<button type="button" class="btn btn-outline-secondary toggle-status"
                        data-id="' . $coupon->id . '" title="Toggle Status">
                        <i class="bi bi-' . ($coupon->is_active ? 'toggle-on' : 'toggle-off') . '"></i>
                    </button>';
                }

                if (auth('admin')->user()->hasPermission('coupons.delete')) {
                    $actions .= '<button type="button" class="btn btn-outline-danger delete-coupon"
                        data-id="' . $coupon->id . '" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'code_badge', 'name_link', 'discount_badge', 'usage_stats', 'validity', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $statusList = SystemStatus::where('module', 'coupons')->get();

        return view('admin.coupons.create', compact('statusList'));
    }

    /**
     * Store new coupon
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons')->whereNull('deleted_at')
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:percentage,fixed_amount,free_shipping,buy_x_get_y',
            'discount_value' => 'required_unless:discount_type,free_shipping|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'min_items_count' => 'nullable|integer|min:0',
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'required|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'applies_to_sale_items' => 'boolean',
            'first_order_only' => 'boolean',
            'applicable_product_ids' => 'nullable|array',
            'applicable_category_ids' => 'nullable|array',
            'excluded_product_ids' => 'nullable|array',
            'excluded_category_ids' => 'nullable|array',
            'buy_quantity' => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
            'get_quantity' => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
            'buy_product_id' => 'required_if:discount_type,buy_x_get_y|nullable|uuid',
            'get_product_id' => 'required_if:discount_type,buy_x_get_y|nullable|uuid',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        // Percentage validation
        if ($validated['discount_type'] === 'percentage' && isset($validated['discount_value']) && $validated['discount_value'] > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%',
                'errors' => ['discount_value' => ['Percentage cannot exceed 100%']]
            ], 422);
        }

        // ✅ SET DEFAULTS FOR NULLABLE FIELDS
        $validated['min_purchase_amount'] = $validated['min_purchase_amount'] ?? 0;
        $validated['min_items_count'] = $validated['min_items_count'] ?? 0;
        $validated['max_discount_amount'] = $validated['max_discount_amount'] ?? null;
        $validated['usage_limit_total'] = $validated['usage_limit_total'] ?? null;
        $validated['applies_to_sale_items'] = $request->has('applies_to_sale_items') ? 1 : 0;
        $validated['first_order_only'] = $request->has('first_order_only') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;

        // ✅ HANDLE FREE SHIPPING (no discount_value needed)
        if ($validated['discount_type'] === 'free_shipping') {
            $validated['discount_value'] = 0;
        }

        // ✅ CLEAN UP BUY X GET Y FIELDS
        if ($validated['discount_type'] !== 'buy_x_get_y') {
            $validated['buy_quantity'] = null;
            $validated['get_quantity'] = null;
            $validated['buy_product_id'] = null;
            $validated['get_product_id'] = null;
        }

        DB::beginTransaction();
        try {
            $validated['created_by'] = auth('admin')->id();
            $coupon = Coupon::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully!',
                'redirect' => route('admin.coupons.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Coupon creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Show coupon details
     */
    public function show($id)
    {
        $coupon = Coupon::with(['createdBy', 'updatedBy', 'usages.customer', 'usages.order'])
            ->withCount('usages')
            ->findOrFail($id);

        // Get usage statistics
        $statistics = [
            'total_discount_given' => CouponUsage::where('coupon_id', $id)->sum('discount_amount'),
            'unique_customers' => CouponUsage::where('coupon_id', $id)->distinct('customer_id')->count('customer_id'),
            'average_order_value' => CouponUsage::where('coupon_id', $id)->avg('order_total'),
        ];

        return view('admin.coupons.show', compact('coupon', 'statistics'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $statusList = SystemStatus::where('module', 'coupons')->get();

        return view('admin.coupons.edit', compact('coupon', 'statusList'));
    }

    /**
     * Update coupon
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons')->ignore($id)->whereNull('deleted_at')
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'sometimes|required|in:percentage,fixed_amount,free_shipping,buy_x_get_y',
            'discount_value' => 'sometimes|required_unless:discount_type,free_shipping|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'min_items_count' => 'nullable|integer|min:0',
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'sometimes|required|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'applies_to_sale_items' => 'boolean',
            'first_order_only' => 'boolean',
            'applicable_product_ids' => 'nullable|array',
            'applicable_category_ids' => 'nullable|array',
            'excluded_product_ids' => 'nullable|array',
            'excluded_category_ids' => 'nullable|array',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'buy_product_id' => 'nullable|uuid',
            'get_product_id' => 'nullable|uuid',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            // ✅ SET DEFAULTS FOR NULLABLE FIELDS
            $validated['min_purchase_amount'] = $validated['min_purchase_amount'] ?? 0;
            $validated['min_items_count'] = $validated['min_items_count'] ?? 0;
            $validated['max_discount_amount'] = $validated['max_discount_amount'] ?? null;
            $validated['usage_limit_total'] = $validated['usage_limit_total'] ?? null;
            $validated['applies_to_sale_items'] = $request->has('applies_to_sale_items') ? 1 : 0;
            $validated['first_order_only'] = $request->has('first_order_only') ? 1 : 0;
            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;

            // ✅ HANDLE FREE SHIPPING (no discount_value needed)
            if ($validated['discount_type'] === 'free_shipping') {
                $validated['discount_value'] = 0;
            }
            $validated['updated_by'] = auth('admin')->id();
            $coupon->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully!',
                'redirect' => route('admin.coupons.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete coupon
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            // Check if coupon has been used
            $usageCount = $coupon->usages()->count();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete coupon that has been used {$usageCount} time(s). Consider deactivating it instead."
                ], 400);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle coupon active status
     */
    public function toggleStatus($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->is_active = !$coupon->is_active;
            $coupon->updated_by = auth('admin')->id();
            $coupon->save();

            return response()->json([
                'success' => true,
                'message' => 'Coupon status updated successfully!',
                'is_active' => $coupon->is_active,
                'status_label' => $coupon->getStatusLabel()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate random coupon code
     */
    public function generateCode(Request $request)
    {
        try {
            $length = min(max($request->input('length', 8), 6), 20);
            $code = Coupon::generateUniqueCode($length);

            return response()->json([
                'success' => true,
                'code' => $code
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon usage statistics
     */
    public function statistics($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            $stats = [
                'total_uses' => $coupon->total_used,
                'remaining_uses' => $coupon->getRemainingUses(),
                'total_discount_given' => CouponUsage::where('coupon_id', $id)->sum('discount_amount'),
                'total_orders' => $coupon->usages()->count(),
                'unique_customers' => $coupon->usages()->distinct('customer_id')->count('customer_id'),
                'average_order_value' => CouponUsage::where('coupon_id', $id)->avg('order_total'),
                'usage_by_day' => $this->getUsageByDay($id, 30),
                'recent_usage' => $this->getRecentUsage($id, 10),
            ];

            return view('admin.coupons.statistics', compact('coupon', 'stats'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retrieve statistics: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'coupon_ids' => 'required|array',
                'coupon_ids.*' => 'uuid|exists:coupons,id',
                'is_active' => 'required|boolean',
            ]);

            $updated = Coupon::whereIn('id', $validated['coupon_ids'])
                ->update([
                    'is_active' => $validated['is_active'],
                    'updated_by' => auth('admin')->id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} coupon(s) updated successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupons: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage by day
     */
    private function getUsageByDay($couponId, $days = 30)
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->where('used_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(used_at) as date, COUNT(*) as count, SUM(discount_amount) as total_discount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get recent usage
     */
    private function getRecentUsage($couponId, $limit = 10)
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->with(['customer', 'order'])
            ->orderBy('used_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
