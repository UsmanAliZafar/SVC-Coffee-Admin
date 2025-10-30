<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * Get all coupons with pagination and filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Coupon::with(['createdBy', 'updatedBy']);

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status')) {
                switch ($request->status) {
                    case 'active':
                        $query->active()->valid();
                        break;
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                    case 'expired':
                        $query->where('valid_until', '<', now());
                        break;
                    case 'scheduled':
                        $query->where('valid_from', '>', now());
                        break;
                }
            }

            // Filter by discount type
            if ($request->has('discount_type')) {
                $query->where('discount_type', $request->discount_type);
            }

            // Filter by featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Date range filter
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            $coupons = $query->paginate($perPage);

            // Transform data
            $coupons->getCollection()->transform(function ($coupon) {
                return $this->transformCoupon($coupon);
            });

            return response()->json([
                'success' => true,
                'message' => 'Coupons retrieved successfully',
                'data' => $coupons->items(),
                'pagination' => [
                    'total' => $coupons->total(),
                    'per_page' => $coupons->perPage(),
                    'current_page' => $coupons->currentPage(),
                    'last_page' => $coupons->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupons',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single coupon details
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $coupon = Coupon::with(['createdBy', 'updatedBy', 'usages'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Coupon retrieved successfully',
                'data' => $this->transformCoupon($coupon, true),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new coupon
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
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
                'applicable_product_ids.*' => 'uuid|exists:products,id',
                'applicable_category_ids' => 'nullable|array',
                'applicable_category_ids.*' => 'uuid|exists:categories,id',
                'excluded_product_ids' => 'nullable|array',
                'excluded_product_ids.*' => 'uuid|exists:products,id',
                'excluded_category_ids' => 'nullable|array',
                'excluded_category_ids.*' => 'uuid|exists:categories,id',
                'buy_quantity' => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
                'get_quantity' => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
                'buy_product_id' => 'required_if:discount_type,buy_x_get_y|nullable|uuid|exists:products,id',
                'get_product_id' => 'required_if:discount_type,buy_x_get_y|nullable|uuid|exists:products,id',
                'applicable_customer_ids' => 'nullable|array',
                'applicable_customer_ids.*' => 'uuid|exists:customers,id',
                'applicable_customer_groups' => 'nullable|array',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'admin_notes' => 'nullable|string|max:2000',
            ]);

            // Additional validation for percentage discount
            if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Percentage discount cannot exceed 100%',
                ], 422);
            }

            DB::beginTransaction();

            $validated['created_by'] = auth()->id(); // Assuming admin authentication
            $coupon = Coupon::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'data' => $this->transformCoupon($coupon),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update coupon
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
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
                'buy_product_id' => 'nullable|uuid|exists:products,id',
                'get_product_id' => 'nullable|uuid|exists:products,id',
                'applicable_customer_ids' => 'nullable|array',
                'applicable_customer_groups' => 'nullable|array',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'admin_notes' => 'nullable|string|max:2000',
            ]);

            DB::beginTransaction();

            $validated['updated_by'] = auth()->id();
            $coupon->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully',
                'data' => $this->transformCoupon($coupon->fresh()),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete coupon (soft delete)
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $coupon = Coupon::findOrFail($id);

            // Check if coupon has been used
            $usageCount = $coupon->usages()->count();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete coupon that has been used {$usageCount} time(s). Consider deactivating it instead.",
                ], 400);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle coupon active status
     *
     * @param string $id
     * @return JsonResponse
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->is_active = !$coupon->is_active;
            $coupon->updated_by = auth()->id();
            $coupon->save();

            return response()->json([
                'success' => true,
                'message' => 'Coupon status updated successfully',
                'data' => [
                    'is_active' => $coupon->is_active,
                    'status_label' => $coupon->getStatusLabel(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get coupon usage statistics
     *
     * @param string $id
     * @return JsonResponse
     */
    public function statistics(string $id): JsonResponse
    {
        try {
            $coupon = Coupon::findOrFail($id);

            $stats = [
                'total_uses' => $coupon->total_used,
                'remaining_uses' => $coupon->getRemainingUses(),
                'total_discount_given' => CouponUsage::where('coupon_id', $id)
                    ->sum('discount_amount'),
                'total_orders' => $coupon->usages()->count(),
                'unique_customers' => $coupon->usages()
                    ->distinct('customer_id')
                    ->count('customer_id'),
                'average_order_value' => $coupon->usages()->avg('order_total'),
                'usage_by_day' => $this->getUsageByDay($id),
                'recent_usage' => $this->getRecentUsage($id, 10),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Coupon statistics retrieved successfully',
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate random coupon code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateCode(Request $request): JsonResponse
    {
        try {
            $length = $request->input('length', 8);
            $length = min(max($length, 6), 20); // Between 6 and 20

            $code = Coupon::generateUniqueCode($length);

            return response()->json([
                'success' => true,
                'data' => ['code' => $code],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk activate/deactivate coupons
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
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
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} coupon(s) updated successfully",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupons',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform coupon data for API response
     *
     * @param Coupon $coupon
     * @param bool $detailed
     * @return array
     */
    private function transformCoupon(Coupon $coupon, bool $detailed = false): array
    {
        $data = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'description' => $coupon->description,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'formatted_discount' => $coupon->getFormattedDiscountValue(),
            'is_active' => $coupon->is_active,
            'is_featured' => $coupon->is_featured,
            'status_label' => $coupon->getStatusLabel(),
            'total_used' => $coupon->total_used,
            'remaining_uses' => $coupon->getRemainingUses(),
            'valid_from' => $coupon->valid_from?->toIso8601String(),
            'valid_until' => $coupon->valid_until?->toIso8601String(),
            'created_at' => $coupon->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'max_discount_amount' => $coupon->max_discount_amount,
                'min_purchase_amount' => (float) $coupon->min_purchase_amount,
                'min_items_count' => $coupon->min_items_count,
                'usage_limit_total' => $coupon->usage_limit_total,
                'usage_limit_per_customer' => $coupon->usage_limit_per_customer,
                'applies_to_sale_items' => $coupon->applies_to_sale_items,
                'first_order_only' => $coupon->first_order_only,
                'applicable_product_ids' => $coupon->applicable_product_ids,
                'applicable_category_ids' => $coupon->applicable_category_ids,
                'excluded_product_ids' => $coupon->excluded_product_ids,
                'excluded_category_ids' => $coupon->excluded_category_ids,
                'buy_quantity' => $coupon->buy_quantity,
                'get_quantity' => $coupon->get_quantity,
                'buy_product_id' => $coupon->buy_product_id,
                'get_product_id' => $coupon->get_product_id,
                'applicable_customer_ids' => $coupon->applicable_customer_ids,
                'applicable_customer_groups' => $coupon->applicable_customer_groups,
                'admin_notes' => $coupon->admin_notes,
                'created_by' => [
                    'id' => $coupon->createdBy?->id,
                    'name' => $coupon->createdBy?->name,
                ],
                'updated_by' => [
                    'id' => $coupon->updatedBy?->id,
                    'name' => $coupon->updatedBy?->name,
                ],
                'updated_at' => $coupon->updated_at->toIso8601String(),
            ]);
        }

        return $data;
    }

    /**
     * Get usage statistics by day
     *
     * @param string $couponId
     * @param int $days
     * @return array
     */
    private function getUsageByDay(string $couponId, int $days = 30): array
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->where('used_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(used_at) as date, COUNT(*) as count, SUM(discount_amount) as total_discount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get recent coupon usage
     *
     * @param string $couponId
     * @param int $limit
     * @return array
     */
    private function getRecentUsage(string $couponId, int $limit = 10): array
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->with(['customer', 'order'])
            ->orderBy('used_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($usage) {
                return [
                    'order_number' => $usage->order->order_number ?? null,
                    'customer_email' => $usage->customer_email,
                    'discount_amount' => (float) $usage->discount_amount,
                    'order_total' => (float) $usage->order_total,
                    'used_at' => $usage->used_at->toIso8601String(),
                ];
            })
            ->toArray();
    }
}
