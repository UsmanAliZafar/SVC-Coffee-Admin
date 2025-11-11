<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
// Models
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;


class OrdersController extends Controller
{
    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Get customer's orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get customer_id from request (you'll need to implement customer authentication)
            $customerId = $request->input('customer_id');

            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer ID is required',
                ], 400);
            }

            $query = Order::where('customer_id', $customerId)
                ->with(['items.product', 'status', 'paymentStatus']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status_key_code', $request->status);
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
            $orders = $query->paginate($perPage);

            // Transform data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformOrder($order, false);
            });

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ],
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single order details
     *
     * @param string $orderNumber
     * @return JsonResponse
     */
    public function show(string $orderNumber): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)
                ->with(['items.product', 'status', 'paymentStatus', 'transactions'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $this->transformOrder($order, true),
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track order status
     *
     * @param string $orderNumber
     * @return JsonResponse
     */
    public function track(string $orderNumber): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)
                ->with(['status'])
                ->firstOrFail();

            $tracking = [
                'order_number' => $order->order_number,
                'status' => [
                    'code' => $order->status_key_code,
                    'label' => $order->getStatusLabel(),
                ],
                'timeline' => [
                    [
                        'status' => 'Ordered',
                        'completed' => true,
                        'date' => $order->created_at->toIso8601String(),
                    ],
                    [
                        'status' => 'Confirmed',
                        'completed' => !is_null($order->confirmed_at),
                        'date' => $order->confirmed_at?->toIso8601String(),
                    ],
                    [
                        'status' => 'Processing',
                        'completed' => in_array($order->status_key_code, ['ORDER_PROCESSING', 'ORDER_PACKED', 'ORDER_SHIPPED', 'ORDER_DELIVERED']),
                        'date' => $order->processing_at?->toIso8601String(),
                    ],
                    [
                        'status' => 'Shipped',
                        'completed' => in_array($order->status_key_code, ['ORDER_SHIPPED', 'ORDER_DELIVERED']),
                        'date' => $order->shipped_at?->toIso8601String(),
                    ],
                    [
                        'status' => 'Delivered',
                        'completed' => $order->status_key_code === 'ORDER_DELIVERED',
                        'date' => $order->delivered_at?->toIso8601String(),
                    ],
                ],
                'shipping' => [
                    'carrier' => $order->shipping_carrier,
                    'tracking_number' => $order->shipping_tracking_number,
                    'tracking_url' => $order->getTrackingUrl(),
                ],
                'estimated_delivery' => $order->estimated_delivery_date?->toIso8601String(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Order tracking retrieved successfully',
                'data' => $tracking,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }
    }

    /**
     * Cancel order
     *
     * @param string $orderNumber
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(string $orderNumber, Request $request): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled at this stage',
                ], 400);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            DB::beginTransaction();

            // Cancel order and restore stock
            $order->cancel($validated['reason']);

            // Restore stock for all items
            foreach ($order->items as $item) {
                if ($item->product && $item->product->track_inventory && $item->stock_deducted) {
                    $item->product->increment('stock_quantity', $item->quantity);
                    $item->update([
                        'stock_deducted' => false,
                        'stock_deducted_at' => null,
                        'status_key_code' => 'ITEM_CANCELLED',
                    ]);
                }
            }

            DB::commit();

            $this->notificationService->notify('order_cancelled', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reason' => $validated['reason'],
                'cancelled_by' => 'Customer',
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
                'total_amount' => $order->getFormattedTotal(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $this->transformOrder($order->fresh(), true),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request return/refund
     *
     * @param string $orderNumber
     * @param Request $request
     * @return JsonResponse
     */
    public function requestReturn(string $orderNumber, Request $request): JsonResponse
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            if (!$order->canBeRefunded()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not eligible for return/refund',
                ], 400);
            }

            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.item_id' => 'required|uuid|exists:order_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.reason' => 'required|string',
                'return_method' => 'required|in:refund,replacement',
                'additional_notes' => 'nullable|string|max:1000',
            ]);

            // Create return request (you'll need to create a returns table)
            // For now, just update order notes
            $returnNote = "Return requested on " . now()->format('Y-m-d H:i:s') . "\n";
            $returnNote .= "Method: " . $validated['return_method'] . "\n";
            $returnNote .= "Items:\n";

            foreach ($validated['items'] as $item) {
                $orderItem = OrderItem::find($item['item_id']);
                $returnNote .= "- {$orderItem->product_name} (Qty: {$item['quantity']}, Reason: {$item['reason']})\n";
            }

            if (!empty($validated['additional_notes'])) {
                $returnNote .= "Notes: " . $validated['additional_notes'];
            }

            $order->update([
                'customer_notes' => ($order->customer_notes ?? '') . "\n\n" . $returnNote
            ]);

            $this->notificationService->notify('order_return_requested', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'return_method' => $validated['return_method'],
                'items_count' => count($validated['items']),
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
                'notes' => $validated['additional_notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Return request submitted successfully. Our team will review and contact you soon.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit return request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform order data for API response
     *
     * @param Order $order
     * @param bool $detailed
     * @return array
     */
    private function transformOrder(Order $order, bool $detailed = false): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => [
                'code' => $order->status_key_code,
                'label' => $order->getStatusLabel(),
            ],
            'payment_status' => [
                'code' => $order->payment_status_key_code,
                'label' => $order->getPaymentStatusLabel(),
            ],
            'totals' => [
                'subtotal' => (float) $order->subtotal,
                'tax_amount' => (float) $order->tax_amount,
                'shipping_amount' => (float) $order->shipping_amount,
                'discount_amount' => (float) $order->discount_amount,
                'total_amount' => (float) $order->total_amount,
                'currency' => $order->currency,
                'formatted_total' => $order->getFormattedTotal(),
            ],
            'items_count' => $order->getTotalItemsCount(),
            'order_date' => $order->created_at->toIso8601String(),
            'can_cancel' => $order->canBeCancelled(),
            'can_return' => $order->canBeRefunded(),
        ];

        if ($detailed) {
            $data['items'] = $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'product_image' => $item->product_image ? asset('storage/' . $item->product_image) : null,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'tax_amount' => (float) $item->tax_amount,
                    'total' => (float) $item->total,
                    'status' => $item->status_key_code,
                ];
            });

            $data['shipping_address'] = [
                'first_name' => $order->shipping_first_name,
                'last_name' => $order->shipping_last_name,
                'address_line1' => $order->shipping_address_line1,
                'address_line2' => $order->shipping_address_line2,
                'city' => $order->shipping_city,
                'state' => $order->shipping_state,
                'postal_code' => $order->shipping_postal_code,
                'country' => $order->shipping_country,
                'phone' => $order->shipping_phone,
            ];

            $data['billing_address'] = [
                'first_name' => $order->billing_first_name,
                'last_name' => $order->billing_last_name,
                'address_line1' => $order->billing_address_line1,
                'address_line2' => $order->billing_address_line2,
                'city' => $order->billing_city,
                'state' => $order->billing_state,
                'postal_code' => $order->billing_postal_code,
                'country' => $order->billing_country,
                'phone' => $order->billing_phone,
            ];

            $data['payment'] = [
                'method' => $order->payment_method,
                'status' => $order->payment_status_key_code,
            ];

            $data['shipping'] = [
                'method' => $order->shipping_method,
                'carrier' => $order->shipping_carrier,
                'tracking_number' => $order->shipping_tracking_number,
                'tracking_url' => $order->getTrackingUrl(),
            ];

            $data['dates'] = [
                'created_at' => $order->created_at->toIso8601String(),
                'confirmed_at' => $order->confirmed_at?->toIso8601String(),
                'shipped_at' => $order->shipped_at?->toIso8601String(),
                'delivered_at' => $order->delivered_at?->toIso8601String(),
                'cancelled_at' => $order->cancelled_at?->toIso8601String(),
            ];

            $data['notes'] = [
                'customer_notes' => $order->customer_notes,
            ];
        }

        return $data;
    }
}
