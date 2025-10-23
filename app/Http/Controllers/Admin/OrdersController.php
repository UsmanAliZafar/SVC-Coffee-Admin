<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\StockManager;
use Illuminate\Support\Facades\Log;
// MODELS
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SystemStatus;
use App\Models\Transaction;


class OrdersController extends Controller
{
    /**
     * Display listing page
     */
    public function index()
    {
        $statusList = SystemStatus::where('module', 'orders')->get();
        $paymentStatusList = SystemStatus::where('module', 'payments')->get();

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'processing_orders' => Order::processing()->count(),
            'today_orders' => Order::today()->count(),
            'today_revenue' => Order::today()->paid()->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('statusList', 'paymentStatusList', 'stats'));
    }

    /**
     * Ajax DataTable data
     */
    public function getData(Request $request)
    {
        $query = Order::with(['customer', 'status', 'paymentStatus'])
            ->withCount('items');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status_key_code', $request->payment_status);
        }

        if ($request->filled('order_source')) {
            $query->where('order_source', $request->order_source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $searchTerm = is_array($request->search) ? $request->search['value'] : $request->search;
            if (!empty($searchTerm)) {
                $query->search($searchTerm);
            }
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($order) {
                return '<input type="checkbox" class="order-checkbox" value="' . $order->id . '">';
            })
            ->addColumn('order_number_link', function ($order) {
                return '<a href="' . route('admin.orders.show', $order->id) . '" class="fw-bold">' . $order->order_number . '</a>';
            })
            ->addColumn('customer_info', function ($order) {
                $html = '<div>';
                $html .= '<strong>' . htmlspecialchars($order->getCustomerName()) . '</strong><br>';
                $html .= '<small class="text-muted">' . htmlspecialchars($order->getCustomerEmail()) . '</small>';
                if ($order->customer) {
                    $html .= '<br><a href="' . route('admin.customers.show', $order->customer_id) . '" class="text-primary"><small>View Customer</small></a>';
                } else {
                    $html .= '<br><span class="badge bg-secondary badge-sm">Guest</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('items_count', function ($order) {
                return '<span class="badge bg-info">' . $order->getTotalItemsCount() . ' items</span>';
            })
            ->addColumn('total_amount', function ($order) {
                return '<strong>' . $order->getFormattedTotal() . '</strong>';
            })
            ->addColumn('payment_info', function ($order) {
                $html = '<div>';
                $html .= $order->getPaymentStatusBadge();
                if ($order->payment_method) {
                    $html .= '<br><small class="text-muted">' . ucfirst(str_replace('_', ' ', $order->payment_method)) . '</small>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('status_badge', function ($order) {
                return $order->getStatusBadge();
            })
            ->addColumn('order_source', function ($order) {
                $icons = [
                    'web' => 'bi-globe',
                    'mobile' => 'bi-phone',
                    'pos' => 'bi-shop',
                    'phone' => 'bi-telephone',
                    'email' => 'bi-envelope',
                    'admin' => 'bi-gear',
                ];
                $icon = $icons[$order->order_source] ?? 'bi-question-circle';
                return '<i class="' . $icon . '"></i> ' . ucfirst($order->order_source);
            })
            ->addColumn('created_at_formatted', function ($order) {
                return $order->created_at->format('M d, Y') . '<br><small class="text-muted">' . $order->created_at->format('h:i A') . '</small>';
            })
            ->addColumn('actions', function ($order) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';

                // Quick Update Button (NEW)
                if (auth('admin')->user()->hasPermission('orders.update')) {
                    $actions .= '<button type="button" class="btn btn-outline-secondary quick-update-btn"
                                        data-id="' . $order->id . '"
                                        data-status="' . $order->status_key_code . '"
                                        data-payment="' . $order->payment_status_key_code . '"
                                        title="Quick Update">
                                    <i class="bi bi-lightning"></i>
                                </button>';
                }

                if (auth('admin')->user()->hasPermission('orders.read')) {
                    $actions .= '<a href="' . route('admin.orders.show', $order->id) . '" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>';
                    $actions .= '<a href="' . route('admin.orders.invoice', $order->id) . '" class="btn btn-outline-info" title="Invoice" target="_blank"><i class="bi bi-file-pdf"></i></a>';
                }

                if (auth('admin')->user()->hasPermission('orders.update')) {
                    $actions .= '<a href="' . route('admin.orders.edit', $order->id) . '" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>';
                }

                if (auth('admin')->user()->hasPermission('orders.delete')) {
                    $actions .= '<button type="button" class="btn btn-outline-danger delete-order" data-id="' . $order->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'order_number_link', 'customer_info', 'items_count', 'total_amount', 'payment_info', 'status_badge', 'order_source', 'created_at_formatted', 'actions'])
            ->make(true);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $customers = Customer::active()->orderBy('first_name')->get();
        $products = Product::active()->inStock()->with('category')->get();
        $statusList = SystemStatus::where('module', 'orders')->active()->ordered()->get();
        $paymentStatusList = SystemStatus::where('module', 'payments')->active()->ordered()->get();
        $currencies = get_currencies();

        return view('admin.orders.create', compact('customers', 'products', 'statusList', 'paymentStatusList', 'currencies'));
    }

    /**
     * Store new order with proper error handling
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'nullable|uuid|exists:customers,id',
                'guest_email' => 'required_without:customer_id|email',
                'guest_name' => 'required_without:customer_id|string|max:255',
                'guest_phone' => 'nullable|string|max:20',

                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|uuid|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',

                'shipping_first_name' => 'required|string|max:100',
                'shipping_last_name' => 'required|string|max:100',
                'shipping_address_line1' => 'required|string|max:255',
                'shipping_address_line2' => 'nullable|string|max:255',
                'shipping_city' => 'required|string|max:100',
                'shipping_state' => 'nullable|string|max:100',
                'shipping_postal_code' => 'required|string|max:20',
                'shipping_country' => 'required|string|max:100',
                'shipping_phone' => 'nullable|string|max:20',

                'billing_same_as_shipping' => 'boolean',
                'billing_first_name' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_last_name' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_address_line1' => 'nullable|required_if:billing_same_as_shipping,false|string|max:255',
                'billing_address_line2' => 'nullable|string|max:255',
                'billing_city' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_state' => 'nullable|string|max:100',
                'billing_postal_code' => 'nullable|required_if:billing_same_as_shipping,false|string|max:20',
                'billing_country' => 'nullable|required_if:billing_same_as_shipping,false|string|max:100',
                'billing_phone' => 'nullable|string|max:20',

                'shipping_method' => 'nullable|string|max:100',
                'currency' => 'required|string|max:3',
                'shipping_amount' => 'nullable|numeric|min:0',
                'discount_code' => 'nullable|string|max:50',
                'discount_amount' => 'nullable|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0',
                'customer_notes' => 'nullable|string',
                'admin_notes' => 'nullable|string',
                'payment_method' => 'nullable|string|max:50',
                'status_key_code' => 'required|string',
                'payment_status_key_code' => 'required|string',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Order validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        try {
            // ============================================================
            // STEP 1: CHECK STOCK AVAILABILITY FIRST (BEFORE CREATING ORDER)
            // ============================================================
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);

                if ($product->track_inventory) {
                    if ($product->stock_quantity < $itemData['quantity']) {
                        DB::rollBack();

                        \Log::error('Insufficient stock during order creation', [
                            'product' => $product->name,
                            'requested' => $itemData['quantity'],
                            'available' => $product->stock_quantity
                        ]);

                        return back()
                            ->withErrors(['items' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}, Requested: {$itemData['quantity']}"])
                            ->withInput()
                            ->with('error', 'Cannot create order - insufficient stock!');
                    }
                }
            }

            // ============================================================
            // STEP 2: CALCULATE TOTALS
            // ============================================================
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $taxAmount = $subtotal * (($validated['tax_rate'] ?? 0) / 100);
            $discountAmount = $validated['discount_amount'] ?? 0;
            $shippingAmount = $validated['shipping_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // ============================================================
            // STEP 3: CREATE ORDER
            // ============================================================
            $orderData = array_merge($validated, [
                'order_source' => 'admin',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'currency' => $validated['currency'] ?? 'USD',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            unset($orderData['items']);

            $order = Order::create($orderData);

            \Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // ============================================================
            // STEP 4: CREATE ORDER ITEMS
            // ============================================================
            foreach ($validated['items'] as $index => $itemData) {
                $product = Product::find($itemData['product_id']);

                $itemSubtotal = $itemData['unit_price'] * $itemData['quantity'];
                $itemTaxAmount = $product->is_taxable ? ($itemSubtotal * (($product->tax_rate ?? 0) / 100)) : 0;
                $itemTotal = $itemSubtotal + $itemTaxAmount;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_description' => $product->short_description,
                    'product_image' => $product->main_image,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'cost_price' => $product->cost_price,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTaxAmount,
                    'tax_rate' => $product->tax_rate,
                    'is_taxable' => $product->is_taxable,
                    'total' => $itemTotal,
                    'sort_order' => $index,
                    'status_key_code' => 'ITEM_PENDING',
                ]);

                \Log::info('Order item created', [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity']
                ]);
            }

            // ============================================================
            // STEP 5: DEDUCT STOCK IF ORDER IS CONFIRMED
            // ============================================================
            if ($order->status_key_code === 'ORDER_CONFIRMED') {
                foreach ($validated['items'] as $itemData) {
                    $product = Product::find($itemData['product_id']);

                    if ($product->track_inventory) {
                        // ⚡ DEDUCT STOCK IMMEDIATELY
                        $product->decrement('stock_quantity', $itemData['quantity']);

                        // Mark as deducted
                        OrderItem::where('order_id', $order->id)
                                ->where('product_id', $product->id)
                                ->update([
                                    'stock_deducted' => true,
                                    'stock_deducted_at' => now(),
                                ]);

                        \Log::info('Stock deducted for new confirmed order', [
                            'order' => $order->order_number,
                            'product' => $product->name,
                            'quantity' => $itemData['quantity'],
                            'remaining_stock' => $product->fresh()->stock_quantity
                        ]);
                    }
                }
            }

            DB::commit();

            \Log::info('Order saved successfully', ['order_id' => $order->id]);

            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('success', "Order #{$order->order_number} created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Show order details
     */
    public function show($id)
    {
        $order = Order::with([
            'customer',
            'items.product',
            'transactions',
            'status',
            'paymentStatus',
            'fulfilledBy',
            'cancelledBy',
            'creator',
            'updater'
        ])->findOrFail($id);

        $statusList = SystemStatus::where('module', 'orders')->active()->ordered()->get();
        $paymentStatusList = SystemStatus::where('module', 'payments')->active()->ordered()->get();

        return view('admin.orders.show', compact('order', 'statusList', 'paymentStatusList'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $order = Order::with(['customer', 'items.product'])->findOrFail($id);
        $products = Product::active()->inStock()->with('category')->get();
        $customers = Customer::active()->orderBy('first_name')->get();
        $statusList = SystemStatus::where('module', 'orders')->active()->ordered()->get();
        $paymentStatusList = SystemStatus::where('module', 'payments')->active()->ordered()->get();
        $currencies = get_currencies();

        return view('admin.orders.edit', compact('order', 'products', 'customers', 'statusList', 'paymentStatusList', 'currencies'));
    }

    /**
     * Update order
     */
    public function update(Request $request, $id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        $validated = $request->validate([
            // Existing fields
            'shipping_first_name' => 'required|string|max:100',
            'shipping_last_name' => 'required|string|max:100',
            'shipping_address_line1' => 'required|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'nullable|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:100',
            'shipping_phone' => 'nullable|string|max:20',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_carrier' => 'nullable|string|max:100',
            'shipping_tracking_number' => 'nullable|string|max:100',
            'customer_notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',

            // NEW: Add these for full editing
            'status_key_code' => 'required|string|exists:system_statuses,key_code',
            'payment_status_key_code' => 'required|string|exists:system_statuses,key_code',
            'payment_method' => 'nullable|string|max:50',
            'currency' => 'required|string|max:3',
            'shipping_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_code' => 'nullable|string|max:50',

            // Order items
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|uuid|exists:order_items,id',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.action' => 'nullable|in:keep,update,delete,add',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status_key_code;
            $newStatus = $validated['status_key_code'];

            // Handle status change and stock management
            $this->handleStatusChange($order, $oldStatus, $newStatus);

            // Handle order items changes
            $this->handleOrderItemsUpdate($order, $validated['items']);

            // Recalculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                if (($itemData['action'] ?? 'keep') !== 'delete') {
                    $subtotal += $itemData['unit_price'] * $itemData['quantity'];
                }
            }

            $taxAmount = $subtotal * (($validated['tax_rate'] ?? 0) / 100);
            $shippingAmount = $validated['shipping_amount'] ?? 0;
            $discountAmount = $validated['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // Update order
            $order->update(array_merge($validated, [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!',
                'redirect' => route('admin.orders.show', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle status change with stock management
     * SIMPLIFIED VERSION - Much cleaner!
     */
    private function handleStatusChange($order, $oldStatus, $newStatus)
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        foreach ($order->items as $item) {
            // Skip if product doesn't track inventory
            if (!$item->product || !$item->product->track_inventory) {
                continue;
            }

            $product = $item->product;
            $quantity = $item->quantity;

            // ============================================================
            // CONFIRMED - DEDUCT STOCK IMMEDIATELY (PREVENTS OVERSELLING)
            // ============================================================
            if ($newStatus === 'ORDER_CONFIRMED' && $oldStatus === 'ORDER_PENDING') {
                if (!$item->stock_deducted) {
                    // Check if enough stock available
                    if ($product->stock_quantity >= $quantity) {
                        // ⚡ DEDUCT STOCK NOW - PREVENTS OVERSELLING
                        $product->decrement('stock_quantity', $quantity);

                        $item->update([
                            'stock_deducted' => true,
                            'stock_deducted_at' => now(),
                        ]);

                        \Log::info("Stock deducted on confirmation", [
                            'order' => $order->order_number,
                            'product' => $product->name,
                            'quantity' => $quantity,
                            'remaining_stock' => $product->fresh()->stock_quantity
                        ]);
                    } else {
                        // Not enough stock - log warning
                        \Log::warning("Insufficient stock for confirmed order", [
                            'order' => $order->order_number,
                            'product' => $product->name,
                            'requested' => $quantity,
                            'available' => $product->stock_quantity
                        ]);
                    }
                }
            }

            // ============================================================
            // CANCELLED - RESTORE STOCK
            // ============================================================
            if ($newStatus === 'ORDER_CANCELLED') {
                if ($item->stock_deducted) {
                    // ✅ RESTORE STOCK (Add it back)
                    $product->increment('stock_quantity', $quantity);

                    $item->update([
                        'stock_deducted' => false,
                        'stock_deducted_at' => null,
                    ]);

                    \Log::info("Stock restored on cancellation", [
                        'order' => $order->order_number,
                        'product' => $product->name,
                        'quantity' => $quantity,
                        'new_stock' => $product->fresh()->stock_quantity
                    ]);
                }
            }

            // ============================================================
            // UNCANCELLED - DEDUCT STOCK AGAIN
            // ============================================================
            if ($oldStatus === 'ORDER_CANCELLED' && $newStatus === 'ORDER_CONFIRMED') {
                if (!$item->stock_deducted && $product->stock_quantity >= $quantity) {
                    // Deduct stock again when reactivating cancelled order
                    $product->decrement('stock_quantity', $quantity);

                    $item->update([
                        'stock_deducted' => true,
                        'stock_deducted_at' => now(),
                    ]);
                }
            }

            // ============================================================
            // SHIPPED - Just update tracking (stock already deducted)
            // ============================================================
            if ($newStatus === 'ORDER_SHIPPED') {
                // Stock was already deducted on confirmation
                // Just mark as shipped for tracking purposes
                $item->update([
                    'shipped_at' => now(), // You may need to add this column
                ]);

                \Log::info("Order shipped", [
                    'order' => $order->order_number,
                    'product' => $product->name,
                    'quantity' => $quantity,
                    'note' => 'Stock was already deducted on confirmation'
                ]);
            }
        }
    }

    /**
     * Handle order items update/add/remove
     * SIMPLIFIED VERSION - Much easier to understand!
     */
    private function handleOrderItemsUpdate($order, $items)
    {
        $existingItemIds = [];

        foreach ($items as $index => $itemData) {
            $action = $itemData['action'] ?? 'keep';

            // === DELETE ITEM ===
            if (isset($itemData['id']) && $action === 'delete') {
                $item = OrderItem::find($itemData['id']);
                if ($item) {
                    // Restore stock if needed
                    if ($item->product && $item->product->track_inventory && $item->stock_deducted) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                    $item->delete();
                }
                continue;
            }

            // === UPDATE EXISTING ITEM ===
            if (isset($itemData['id'])) {
                $item = OrderItem::find($itemData['id']);
                if ($item) {
                    $oldQuantity = $item->quantity;
                    $newQuantity = $itemData['quantity'];
                    $quantityDiff = $newQuantity - $oldQuantity;

                    // Adjust stock if quantity changed and stock was deducted
                    if ($quantityDiff != 0 && $item->product && $item->product->track_inventory && $item->stock_deducted) {
                        if ($quantityDiff > 0) {
                            // Quantity increased - deduct more
                            $item->product->decrement('stock_quantity', $quantityDiff);
                        } else {
                            // Quantity decreased - restore some
                            $item->product->increment('stock_quantity', abs($quantityDiff));
                        }
                    }

                    // Update item details
                    $product = Product::find($itemData['product_id']);
                    $itemSubtotal = $itemData['unit_price'] * $newQuantity;
                    $itemTaxAmount = $product->is_taxable ? ($itemSubtotal * (($product->tax_rate ?? 0) / 100)) : 0;

                    $item->update([
                        'quantity' => $newQuantity,
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $itemSubtotal,
                        'tax_amount' => $itemTaxAmount,
                        'total' => $itemSubtotal + $itemTaxAmount,
                    ]);

                    $existingItemIds[] = $item->id;
                }
            }
            // === ADD NEW ITEM ===
            else {
                $product = Product::find($itemData['product_id']);
                $itemSubtotal = $itemData['unit_price'] * $itemData['quantity'];
                $itemTaxAmount = $product->is_taxable ? ($itemSubtotal * (($product->tax_rate ?? 0) / 100)) : 0;

                $newItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_description' => $product->short_description,
                    'product_image' => $product->main_image,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'cost_price' => $product->cost_price,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTaxAmount,
                    'tax_rate' => $product->tax_rate,
                    'is_taxable' => $product->is_taxable,
                    'total' => $itemSubtotal + $itemTaxAmount,
                    'sort_order' => $index,
                    'status_key_code' => 'ITEM_PENDING',
                ]);

                // Handle stock for new item based on order status
                if ($product->track_inventory) {
                    if ($order->status_key_code === 'ORDER_SHIPPED') {
                        // Deduct stock immediately
                        $product->decrement('stock_quantity', $itemData['quantity']);
                        $newItem->update(['stock_deducted' => true, 'stock_deducted_at' => now()]);
                    } elseif (in_array($order->status_key_code, ['ORDER_CONFIRMED', 'ORDER_PROCESSING'])) {
                        // Just reserve it
                        $newItem->update(['stock_reserved' => true, 'stock_reserved_at' => now()]);
                    }
                }

                $existingItemIds[] = $newItem->id;
            }
        }

        // Delete items not in the update list
        OrderItem::where('order_id', $order->id)
            ->whereNotIn('id', $existingItemIds)
            ->each(function ($item) {
                if ($item->product && $item->product->track_inventory && $item->stock_deducted) {
                    // Restore stock
                    $item->product->increment('stock_quantity', $item->quantity);
                }
                $item->delete();
            });
    }

    /**
     * Remove item from order and restore stock
     * SIMPLIFIED VERSION - Clean and simple!
     */
    public function removeItem($orderId, $itemId)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($orderId);
            $item = OrderItem::where('id', $itemId)
                            ->where('order_id', $orderId)
                            ->firstOrFail();

            // Restore stock if it was deducted
            if ($item->product && $item->product->track_inventory && $item->stock_deducted) {
                $item->product->increment('stock_quantity', $item->quantity);
            }

            // Delete the item
            $item->delete();

            // Recalculate order totals
            $order->recalculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item removed successfully!',
                'order' => [
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'total_amount' => $order->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        $validated = $request->validate([
            'status_key_code' => 'required|string|exists:system_statuses,key_code',
            'tracking_number' => 'nullable|string|max:100',
            'carrier' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status_key_code;
            $newStatus = $validated['status_key_code'];

            // Handle status-specific logic
            if ($newStatus === 'ORDER_CONFIRMED' && $oldStatus !== 'ORDER_CONFIRMED') {
                // Reserve stock for all items
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        $item->reserveStock();
                    }
                }
                $order->confirm();
            }

            if ($newStatus === 'ORDER_PROCESSING' && $oldStatus !== 'ORDER_PROCESSING') {
                $order->markAsProcessing();
            }

            if ($newStatus === 'ORDER_PACKED' && $oldStatus !== 'ORDER_PACKED') {
                $order->markAsPacked();
            }

            if ($newStatus === 'ORDER_SHIPPED' && $oldStatus !== 'ORDER_SHIPPED') {
                // Deduct stock for all items
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        $item->deductStock();
                    }
                    $item->markAsFulfilled();
                }

                $order->markAsShipped(
                    $validated['tracking_number'] ?? null,
                    $validated['carrier'] ?? null
                );
            }

            if ($newStatus === 'ORDER_DELIVERED' && $oldStatus !== 'ORDER_DELIVERED') {
                $order->markAsDelivered();
            }

            if ($newStatus === 'ORDER_CANCELLED') {
                // Release/restore stock
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        if ($item->stock_deducted) {
                            // Restore stock if already deducted
                            $item->product->addWarehouseStock(
                                $item->warehouse_id,
                                $item->quantity,
                                'Restored from cancelled order #' . $order->order_number
                            );
                            $item->update([
                                'stock_deducted' => false,
                                'stock_deducted_at' => null,
                            ]);
                        } elseif ($item->stock_reserved) {
                            // Release reserved stock
                            $item->releaseStock();
                        }
                    }
                    $item->update(['status_key_code' => 'ITEM_CANCELLED']);
                }

                $order->cancel($validated['notes'] ?? 'Cancelled by admin');
            }

            // Add status change note
            if (!empty($validated['notes'])) {
                $currentNotes = $order->admin_notes ?? '';
                $timestamp = now()->format('Y-m-d H:i:s');
                $adminName = auth('admin')->user()->name;
                $newNote = "\n[{$timestamp}] {$adminName}: Status changed from {$oldStatus} to {$newStatus}. {$validated['notes']}";

                $order->update([
                    'admin_notes' => $currentNotes . $newNote
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully!',
                'new_status' => $newStatus,
                'status_badge' => $order->fresh()->getStatusBadge()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add note to order
     */
    public function addNote(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'note' => 'required|string',
            'note_type' => 'required|in:admin,internal',
        ]);

        try {
            $timestamp = now()->format('Y-m-d H:i:s');
            $adminName = auth('admin')->user()->name;
            $newNote = "\n[{$timestamp}] {$adminName}: {$validated['note']}";

            if ($validated['note_type'] === 'admin') {
                $order->update([
                    'admin_notes' => ($order->admin_notes ?? '') . $newNote
                ]);
            } else {
                $order->update([
                    'internal_notes' => ($order->internal_notes ?? '') . $newNote
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request, $id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        $validated = $request->validate([
            'refund_type' => 'required|in:full,partial',
            'refund_amount' => 'required_if:refund_type,partial|numeric|min:0',
            'refund_reason' => 'required|string',
            'refund_items' => 'required_if:refund_type,partial|array',
            'refund_items.*.item_id' => 'required_with:refund_items|uuid|exists:order_items,id',
            'refund_items.*.quantity' => 'required_with:refund_items|integer|min:1',
            'restore_stock' => 'boolean',
        ]);

        if (!$order->canBeRefunded()) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be refunded at this time.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($validated['refund_type'] === 'full') {
                // Full refund
                $refundAmount = $order->total_amount - $order->refunded_amount;

                foreach ($order->items as $item) {
                    $item->refund($item->getRemainingQuantity(), $validated['refund_reason']);
                }

                $order->update([
                    'is_refunded' => true,
                    'refunded_amount' => $order->total_amount,
                    'refunded_at' => now(),
                    'status_key_code' => 'ORDER_REFUNDED',
                ]);

            } else {
                // Partial refund
                $refundAmount = 0;

                foreach ($validated['refund_items'] as $refundItem) {
                    $item = OrderItem::findOrFail($refundItem['item_id']);
                    $quantity = $refundItem['quantity'];

                    $itemRefundAmount = ($item->total / $item->quantity) * $quantity;
                    $refundAmount += $itemRefundAmount;

                    $item->refund($quantity, $validated['refund_reason']);
                }

                $order->update([
                    'is_refunded' => true,
                    'refunded_amount' => $order->refunded_amount + $refundAmount,
                    'refunded_at' => now(),
                    'payment_status_key_code' => 'PAYMENT_PARTIALLY_REFUNDED',
                ]);
            }

            // Create refund transaction
            $successfulTransaction = $order->transactions()
                ->where('status_key_code', 'TRANSACTION_SUCCESS')
                ->where('transaction_type', 'payment')
                ->first();

            if ($successfulTransaction) {
                Transaction::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'transaction_type' => $validated['refund_type'] === 'full' ? 'refund' : 'partial_refund',
                    'payment_gateway' => $successfulTransaction->payment_gateway,
                    'payment_method' => $successfulTransaction->payment_method,
                    'amount' => $refundAmount,
                    'currency' => $order->currency,
                    'status_key_code' => 'TRANSACTION_SUCCESS',
                    'refund_transaction_id' => $successfulTransaction->id,
                    'refund_reason' => $validated['refund_reason'],
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully!',
                'refund_amount' => number_format($refundAmount, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete order
     */
    public function destroy($id)
    {
        try {
            $order = Order::with('items')->findOrFail($id);

            // Check if order can be deleted
            if ($order->isPaid() || in_array($order->status_key_code, ['ORDER_SHIPPED', 'ORDER_DELIVERED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete paid or shipped orders. Please cancel the order instead.'
                ], 400);
            }

            DB::beginTransaction();

            // Release stock for all items
            foreach ($order->items as $item) {
                if ($item->stock_reserved) {
                    $item->releaseStock();
                }
            }

            // Delete order items
            $order->items()->delete();

            // Delete transactions
            $order->transactions()->delete();

            // Delete order
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate invoice PDF
     */
    public function invoice($id)
    {
        $order = Order::with(['customer', 'items.product'])->findOrFail($id);

        // Generate invoice number if not exists
        if (!$order->invoice_number) {
            $order->generateInvoiceNumber();
        }

        $pdf = Pdf::loadView('admin.orders.invoice-pdf', compact('order'));

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }

    /**
     * Generate shipping label PDF
     */
    public function shippingLabel($id)
    {
        $order = Order::findOrFail($id);

        $pdf = Pdf::loadView('admin.orders.shipping-label-pdf', compact('order'));

        return $pdf->download('shipping-label-' . $order->order_number . '.pdf');
    }

    /**
     * Get orders by status
     */
    public function status($status)
    {
        $statusKey = 'ORDER_' . strtoupper($status);
        $statusList = SystemStatus::where('module', 'orders')->get();

        return view('admin.orders.status', compact('status', 'statusKey', 'statusList'));
    }

    /**
     * Get today's orders
     */
    public function today()
    {
        return view('admin.orders.today');
    }

    /**
     * Get orders with notes
     */
    public function withNotes()
    {
        return view('admin.orders.with-notes');
    }

    /**
     * Invoices list
     */
    public function invoices()
    {
        return view('admin.orders.invoices');
    }

    /**
     * Shipping management
     */
    public function shipping()
    {
        return view('admin.orders.shipping');
    }

    /**
     * Refunds list
     */
    public function refunds()
    {
        return view('admin.orders.refunds');
    }

    /**
     * Orders reports
     */
    public function reports()
    {
        $stats = [
            'total_orders' => Order::count(),
            'total_revenue' => Order::paid()->sum('total_amount'),
            'average_order_value' => Order::paid()->avg('total_amount'),
            'pending_orders' => Order::pending()->count(),
            'processing_orders' => Order::processing()->count(),
            'shipped_orders' => Order::shipped()->count(),
            'delivered_orders' => Order::delivered()->count(),
            'cancelled_orders' => Order::cancelled()->count(),
            'refunded_orders' => Order::refunded()->count(),
        ];

        return view('admin.orders.reports', compact('stats'));
    }

    /**
     * Get reports data via AJAX
     */
    public function reportsData(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            // Validate dates
            if (!$dateFrom || !$dateTo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date range is required'
                ], 400);
            }

            // Build base query
            $query = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);

            // Get metrics
            $totalOrders = $query->count();
            $totalRevenue = $query->sum('total_amount');
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            $pendingOrders = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])->where('status_key_code', 'ORDER_PENDING')->count();

            // Status breakdown
            $statusBreakdown = [
                'pending' => Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('status_key_code', 'ORDER_PENDING')->count(),
                'processing' => Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('status_key_code', 'ORDER_PROCESSING')->count(),
                'shipped' => Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('status_key_code', 'ORDER_SHIPPED')->count(),
                'delivered' => Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('status_key_code', 'ORDER_DELIVERED')->count(),
                'cancelled' => Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('status_key_code', 'ORDER_CANCELLED')->count(),
                'refunded' => Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('is_refunded', true)->count(),
            ];

            // Sales trend (daily data)
            $salesTrend = $this->getSalesTrend($dateFrom, $dateTo);

            // Revenue by source
            $revenueBySource = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
                ->select('order_source', DB::raw('SUM(total_amount) as revenue'))
                ->groupBy('order_source')
                ->get();

            $revenueBySourceData = [
                'labels' => $revenueBySource->pluck('order_source')->map(fn($s) => ucfirst($s))->toArray(),
                'data' => $revenueBySource->pluck('revenue')->toArray(),
            ];

            // Payment methods
            $paymentMethods = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
                ->whereNotNull('payment_method')
                ->select('payment_method', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_method')
                ->get();

            $paymentMethodsData = [
                'labels' => $paymentMethods->pluck('payment_method')->map(fn($p) => ucfirst(str_replace('_', ' ', $p)))->toArray(),
                'data' => $paymentMethods->pluck('count')->toArray(),
            ];

            // Top products
            $topProducts = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ])
                ->whereNull('order_items.deleted_at')
                ->select(
                    'order_items.product_name as name',
                    DB::raw('SUM(order_items.quantity) as quantity'),
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->groupBy('order_items.product_id', 'order_items.product_name')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            // Top customers
            $topCustomers = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
                ->whereNotNull('customer_id')
                ->with('customer')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($orders) {
                    $customer = $orders->first()->customer;
                    return [
                        'name' => $customer ? $customer->getFullName() : 'Unknown',
                        'orders' => $orders->count(),
                        'total_spent' => $orders->sum('total_amount'),
                    ];
                })
                ->sortByDesc('total_spent')
                ->take(10)
                ->values();

            // Performance metrics
            $avgProcessingTime = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
                ->whereNotNull('confirmed_at')
                ->whereNotNull('shipped_at')
                ->get()
                ->map(function ($order) {
                    return $order->confirmed_at->diffInHours($order->shipped_at);
                })
                ->avg();

            $avgDeliveryTime = Order::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
                ->whereNotNull('shipped_at')
                ->whereNotNull('delivered_at')
                ->get()
                ->map(function ($order) {
                    return $order->shipped_at->diffInDays($order->delivered_at);
                })
                ->avg();

            return response()->json([
                'success' => true,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'avg_order_value' => $avgOrderValue,
                'pending_orders' => $pendingOrders,
                'status_breakdown' => $statusBreakdown,
                'sales_trend' => $salesTrend,
                'revenue_by_source' => $revenueBySourceData,
                'payment_methods' => $paymentMethodsData,
                'top_products' => $topProducts,
                'top_customers' => $topCustomers,
                'avg_processing_time' => $avgProcessingTime ? round($avgProcessingTime, 1) . ' hours' : '-',
                'avg_delivery_time' => $avgDeliveryTime ? round($avgDeliveryTime, 1) . ' days' : '-',
                'customer_satisfaction' => 'N/A', // Implement if you have reviews
            ]);

        } catch (\Exception $e) {
            \Log::error('Reports data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load report data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate sales trend data
     */
    private function getSalesTrend($dateFrom, $dateTo)
    {
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);
        $daysDiff = $startDate->diffInDays($endDate);

        // Determine grouping based on date range
        if ($daysDiff <= 7) {
            // Daily for 7 days or less
            $groupBy = 'DATE(created_at)';
            $format = 'M d';
        } elseif ($daysDiff <= 31) {
            // Daily for up to 31 days
            $groupBy = 'DATE(created_at)';
            $format = 'M d';
        } elseif ($daysDiff <= 90) {
            // Weekly for up to 3 months
            $groupBy = 'YEARWEEK(created_at)';
            $format = 'W\eek W';
        } else {
            // Monthly for longer periods
            $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
            $format = 'M Y';
        }

        $salesData = Order::whereBetween('created_at', [
            $dateFrom . ' 00:00:00',
            $dateTo . ' 23:59:59'
        ])
            ->select(
                DB::raw($groupBy . ' as date_group'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date_group')
            ->orderBy('date_group')
            ->get();

        $labels = [];
        $revenue = [];

        foreach ($salesData as $data) {
            if ($daysDiff <= 31) {
                // For daily data, format the date
                $date = \Carbon\Carbon::parse($data->date_group);
                $labels[] = $date->format($format);
            } else {
                // For weekly/monthly, use the group directly
                $labels[] = $data->date_group;
            }
            $revenue[] = (float) $data->revenue;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
        ];
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'required|uuid|exists:orders,id',
                'status_key_code' => 'required|string|exists:system_statuses,key_code',
            ]);

            DB::beginTransaction();

            $updated = 0;
            $failed = 0;
            $errors = [];

            foreach ($validated['order_ids'] as $orderId) {
                try {
                    $order = Order::findOrFail($orderId);

                    // Check if order can be updated
                    if (!$order->canUpdateStatus()) {
                        $errors[] = "Order {$order->order_number} cannot be updated (already {$order->getStatusLabel()})";
                        $failed++;
                        continue;
                    }

                    // Update status
                    $order->update([
                        'status_key_code' => $validated['status_key_code']
                    ]);

                    // Handle stock for shipped orders
                    if ($validated['status_key_code'] === 'ORDER_SHIPPED') {
                        foreach ($order->items as $item) {
                            if ($item->stock_reserved && !$item->stock_deducted) {
                                $item->deductStock();
                            }
                        }
                    }

                    $updated++;

                } catch (\Exception $e) {
                    $errors[] = "Order {$orderId}: " . $e->getMessage();
                    $failed++;
                }
            }

            DB::commit();

            $message = "$updated order(s) updated successfully";
            if ($failed > 0) {
                $message .= ", $failed failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update payment status
     */
    public function bulkUpdatePaymentStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'required|uuid|exists:orders,id',
                'payment_status_key_code' => 'required|string|exists:system_statuses,key_code',
            ]);

            DB::beginTransaction();

            $updated = 0;
            $failed = 0;
            $errors = [];

            foreach ($validated['order_ids'] as $orderId) {
                try {
                    $order = Order::findOrFail($orderId);

                    // Update payment status
                    $order->update([
                        'payment_status_key_code' => $validated['payment_status_key_code']
                    ]);

                    $updated++;

                } catch (\Exception $e) {
                    $errors[] = "Order {$orderId}: " . $e->getMessage();
                    $failed++;
                }
            }

            DB::commit();

            $message = "$updated order(s) payment status updated successfully";
            if ($failed > 0) {
                $message .= ", $failed failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick update status from index page (single order)
     */
    public function quickUpdateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status_key_code' => 'nullable|string|exists:system_statuses,key_code',
                'payment_status_key_code' => 'nullable|string|exists:system_statuses,key_code',
            ]);

            $order = Order::findOrFail($id);

            DB::beginTransaction();

            // Update order status if provided
            if (isset($validated['status_key_code'])) {
                if (!$order->canUpdateStatus()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order cannot be updated (already ' . $order->getStatusLabel() . ')'
                    ], 400);
                }

                $order->status_key_code = $validated['status_key_code'];

                // Handle stock for shipped orders
                if ($validated['status_key_code'] === 'ORDER_SHIPPED') {
                    foreach ($order->items as $item) {
                        if ($item->stock_reserved && !$item->stock_deducted) {
                            $item->deductStock();
                        }
                    }
                }
            }

            // Update payment status if provided
            if (isset($validated['payment_status_key_code'])) {
                $order->payment_status_key_code = $validated['payment_status_key_code'];
            }

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => [
                    'id' => $order->id,
                    'status_badge' => $order->getStatusBadge(),
                    'payment_badge' => $order->getPaymentStatusBadge(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order notes (admin and internal)
     */
    public function updateNotes(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'admin_notes' => 'nullable|string',
                'internal_notes' => 'nullable|string',
            ]);

            $order = Order::findOrFail($id);

            $order->update([
                'admin_notes' => $validated['admin_notes'] ?? $order->admin_notes,
                'internal_notes' => $validated['internal_notes'] ?? $order->internal_notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notes updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order data for notes modal (AJAX)
     */
    public function getOrderData($id)
    {
        try {
            $order = Order::with(['customer'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer
                    ? $order->customer->getFullName()
                    : ($order->guest_name ?? 'Guest Customer'),
                'customer_email' => $order->customer
                    ? $order->customer->email
                    : ($order->guest_email ?? 'N/A'),
                'customer_notes' => $order->customer_notes,
                'admin_notes' => $order->admin_notes,
                'internal_notes' => $order->internal_notes,
                'status' => $order->status ? $order->status->name : 'N/A',
                'total_amount' => $order->getFormattedTotal(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }
}
