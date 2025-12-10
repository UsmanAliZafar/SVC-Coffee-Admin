<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
// MODELS
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SystemStatus;
use App\Models\Transaction;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Models\Coupon;

class OrdersController extends Controller
{
    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Display listing page
     */
    public function index(Request $request)
    {
        $statusList = SystemStatus::where('module', 'orders')->get();
        $paymentStatusList = SystemStatus::where('module', 'payments')->get();
        $customers = Customer::active()->orderBy('first_name')->get();

        // Base query for stats with customer filter
        $statsQuery = Order::query();
        $selectedCustomerId = $request->input('customer_id');

        if ($selectedCustomerId) {
            $statsQuery->where('customer_id', $selectedCustomerId);
        }

        $stats = [
            'total_orders' => (clone $statsQuery)->count(),
            'pending_orders' => (clone $statsQuery)->pending()->count(),
            'processing_orders' => (clone $statsQuery)->processing()->count(),
            'today_orders' => (clone $statsQuery)->today()->count(),
            'today_revenue' => (clone $statsQuery)->today()->paid()->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('statusList', 'paymentStatusList', 'stats', 'customers', 'request', 'selectedCustomerId'));
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
        // dd($request->customer_id);
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
        $products = Product::active()->inStock()->with('category','warehouseStock')->get();
        $statusList = SystemStatus::where('module', 'orders')
                    ->whereNotIn('key_code', ['ORDER_CANCELLED', 'ORDER_RETURNED','ORDER_PROCESSING','ORDER_SHIPPED','ORDER_DELIVERED'])
                    ->active()
                    ->ordered()
                    ->get();
        $paymentStatusList = SystemStatus::where('module', 'payments')->active()->ordered()->get();
        $currencies = get_currencies();

        return view('admin.orders.create', compact('customers', 'products', 'statusList', 'paymentStatusList', 'currencies'));
    }

    /**
     * Store new order with variant support
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('orders.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Clean guest fields if customer is selected
        if ($request->filled('customer_id')) {
            $request->merge([
                'guest_email' => null,
                'guest_name' => null,
                'guest_phone' => null,
            ]);
        }

        // Build validation rules dynamically
        $rules = [
            // Customer Information
            'customer_id' => 'nullable|uuid|exists:customers,id',

            // Order Items - NOW SUPPORTS VARIANTS
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.variant_id' => 'nullable|uuid|exists:product_variants,id', // ← NEW
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',

            // Shipping Address
            'shipping_first_name' => 'required|string|max:100',
            'shipping_last_name' => 'required|string|max:100',
            'shipping_address_line1' => 'required|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'nullable|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:100',
            'shipping_phone' => 'nullable|string|max:20',

            // Billing Address
            'billing_same_as_shipping' => 'nullable|boolean',
            'billing_first_name' => 'nullable|required_if:billing_same_as_shipping,0|string|max:100',
            'billing_last_name' => 'nullable|required_if:billing_same_as_shipping,0|string|max:100',
            'billing_address_line1' => 'nullable|required_if:billing_same_as_shipping,0|string|max:255',
            'billing_address_line2' => 'nullable|string|max:255',
            'billing_city' => 'nullable|required_if:billing_same_as_shipping,0|string|max:100',
            'billing_state' => 'nullable|string|max:100',
            'billing_postal_code' => 'nullable|required_if:billing_same_as_shipping,0|string|max:20',
            'billing_country' => 'nullable|required_if:billing_same_as_shipping,0|string|max:100',
            'billing_phone' => 'nullable|string|max:20',

            // Shipping & Payment
            'shipping_method' => 'nullable|string|max:100',
            'currency' => 'required|string|max:3',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_code' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|string|max:50',

            // Order Status
            'status_key_code' => 'required|string|exists:system_statuses,key_code',
            'payment_status_key_code' => 'required|string|exists:system_statuses,key_code',

            // Notes
            'customer_notes' => 'nullable|string|max:5000',
            'admin_notes' => 'nullable|string|max:5000',
        ];

        // Only require guest fields if no customer selected
        if (!$request->filled('customer_id')) {
            $rules['guest_email'] = 'required|email|max:255';
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_phone'] = 'required|string|max:20';
        }

        // Custom validation messages (same as before, add variant message)
        $messages = [
            // Guest Customer Messages
            'guest_name.required' => 'Guest name is required when no customer is selected.',
            'guest_email.required' => 'Guest email is required when no customer is selected.',
            'guest_email.email' => 'Please enter a valid email address.',
            'guest_phone.required' => 'Guest phone is required when no customer is selected.',

            // Customer Messages
            'customer_id.uuid' => 'Invalid customer ID format.',
            'customer_id.exists' => 'Selected customer does not exist.',

            // Order Items Messages
            'items.required' => 'Please add at least one item to the order.',
            'items.min' => 'Order must contain at least one item.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'One or more selected products do not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',

            // Shipping Address Messages
            'shipping_first_name.required' => 'Shipping first name is required.',
            'shipping_first_name.max' => 'Shipping first name cannot exceed 100 characters.',
            'shipping_last_name.required' => 'Shipping last name is required.',
            'shipping_last_name.max' => 'Shipping last name cannot exceed 100 characters.',
            'shipping_address_line1.required' => 'Shipping address is required.',
            'shipping_address_line1.max' => 'Shipping address cannot exceed 255 characters.',
            'shipping_city.required' => 'Shipping city is required.',
            'shipping_city.max' => 'Shipping city cannot exceed 100 characters.',
            'shipping_postal_code.required' => 'Shipping postal code is required.',
            'shipping_postal_code.max' => 'Shipping postal code cannot exceed 20 characters.',
            'shipping_country.required' => 'Shipping country is required.',
            'shipping_country.max' => 'Shipping country cannot exceed 100 characters.',
            'shipping_phone.max' => 'Shipping phone cannot exceed 20 characters.',

            // Billing Address Messages
            'billing_same_as_shipping.boolean' => 'Billing same as shipping must be true or false.',
            'billing_first_name.required_if' => 'Billing first name is required when billing address differs from shipping.',
            'billing_last_name.required_if' => 'Billing last name is required when billing address differs from shipping.',
            'billing_address_line1.required_if' => 'Billing address is required when billing address differs from shipping.',
            'billing_city.required_if' => 'Billing city is required when billing address differs from shipping.',
            'billing_postal_code.required_if' => 'Billing postal code is required when billing address differs from shipping.',
            'billing_country.required_if' => 'Billing country is required when billing address differs from shipping.',

            // Shipping & Payment Messages
            'shipping_method.max' => 'Shipping method cannot exceed 100 characters.',
            'currency.required' => 'Currency is required.',
            'currency.max' => 'Currency code must be 3 characters.',
            'shipping_amount.numeric' => 'Shipping amount must be a valid number.',
            'shipping_amount.min' => 'Shipping amount cannot be negative.',
            'discount_code.max' => 'Discount code cannot exceed 50 characters.',
            'discount_amount.numeric' => 'Discount amount must be a valid number.',
            'discount_amount.min' => 'Discount amount cannot be negative.',
            'tax_rate.numeric' => 'Tax rate must be a valid number.',
            'tax_rate.min' => 'Tax rate cannot be negative.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'payment_method.max' => 'Payment method cannot exceed 50 characters.',

            // Order Status Messages
            'status_key_code.required' => 'Order status is required.',
            'status_key_code.exists' => 'Invalid order status selected.',
            'payment_status_key_code.required' => 'Payment status is required.',
            'payment_status_key_code.exists' => 'Invalid payment status selected.',

            // Notes Messages
            'customer_notes.max' => 'Customer notes cannot exceed 5000 characters.',
            'admin_notes.max' => 'Admin notes cannot exceed 5000 characters.',
        ];


        // ✅ Perform validation
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $validated['shipping_amount'] = $validated['shipping_amount'] ?? 0;
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        DB::beginTransaction();

        try {
            // ============================================================
            // STEP 1: CHECK STOCK AVAILABILITY (WITH VARIANT SUPPORT)
            // ============================================================
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);

                // ✅ NEW: Check if this is a variant order
                if (!empty($itemData['variant_id'])) {
                    $variant = ProductVariant::find($itemData['variant_id']);

                    if (!$variant) {
                        DB::rollBack();
                        return back()
                            ->withErrors(['items' => "Variant not found"])
                            ->withInput()
                            ->with('error', 'Invalid variant selected!');
                    }

                    // Check variant stock
                    if ($product->track_inventory && $variant->stock_quantity < $itemData['quantity']) {
                        DB::rollBack();

                        \Log::error('Insufficient variant stock during order creation', [
                            'product' => $product->name,
                            'variant' => $variant->getFullName(),
                            'requested' => $itemData['quantity'],
                            'available' => $variant->stock_quantity
                        ]);

                        return back()
                            ->withErrors(['items' => "Insufficient stock for {$product->name} ({$variant->getFullName()}). Available: {$variant->stock_quantity}, Requested: {$itemData['quantity']}"])
                            ->withInput()
                            ->with('error', 'Cannot create order - insufficient variant stock!');
                    }
                } else {
                    // Check main product stock (no variant)
                    if ($product->track_inventory && !$product->has_variants) {
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
            }

            // ============================================================
            // STEP 2: CALCULATE TOTALS (WITH PRODUCT-LEVEL TAX)
            // ============================================================
            $subtotal = 0;
            $inclusiveTaxTotal = 0;
            $exclusiveTaxTotal = 0;

            // Calculate subtotal and item-level taxes
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $item['unit_price'] * $item['quantity'];
                $subtotal += $itemSubtotal;

                // Calculate item tax
                if ($product && $product->is_taxable && $product->tax_percentage > 0) {
                    $taxRate = $product->tax_percentage / 100;

                    if ($product->tax_type === 'inclusive') {
                        // Tax already in price - extract it
                        $basePrice = $itemSubtotal / (1 + $taxRate);
                        $itemTax = $itemSubtotal - $basePrice;
                        $inclusiveTaxTotal += $itemTax;
                    } else {
                        // Tax to be added
                        $itemTax = $itemSubtotal * $taxRate;
                        $exclusiveTaxTotal += $itemTax;
                    }
                }
            }

            // Calculate additional tax (order-level admin override)
            $additionalTaxRate = $validated['tax_rate'] ?? 0;
            $additionalTax = $subtotal * ($additionalTaxRate / 100);

            // ✅ TOTAL TAX = All product taxes (inclusive + exclusive) + additional tax
            $taxAmount = $inclusiveTaxTotal + $exclusiveTaxTotal + $additionalTax;

            $discountAmount = $validated['discount_amount'] ?? 0;
            $shippingAmount = $validated['shipping_amount'] ?? 0;

            // ============================================================
            // STEP 2.5: VALIDATE AND APPLY COUPON (IF PROVIDED) ✅
            // ============================================================
            $couponId = null;
            $appliedCoupon = null;
            $freeShipping = false;

            if (!empty($validated['discount_code'])) {
                $coupon = Coupon::byCode($validated['discount_code'])
                    ->active()
                    ->valid()
                    ->first();

                if ($coupon && $coupon->isValid()) {
                    // Check customer eligibility
                    $customerCheck = $coupon->canBeUsedByCustomer(
                        $validated['customer_id'] ?? null,
                        $validated['guest_email'] ?? null
                    );

                    if ($customerCheck['valid']) {
                        // Prepare cart items for validation
                        $cartItems = [];
                        foreach ($validated['items'] as $item) {
                            $cartItems[] = [
                                'product_id' => $item['product_id'],
                                'variant_id' => $item['variant_id'] ?? null,
                                'price' => (float) $item['unit_price'],
                                'quantity' => (int) $item['quantity'],
                            ];
                        }

                        $itemCount = array_sum(array_column($cartItems, 'quantity'));

                        // Check cart applicability
                        $cartCheck = $coupon->isApplicableToCart($cartItems, $subtotal, $itemCount);

                        if ($cartCheck['valid']) {
                            // Calculate discount
                            $discountDetails = $coupon->calculateDiscount($cartItems, $subtotal);
                            $discountAmount = (float) ($discountDetails['discount_amount'] ?? 0);
                            $freeShipping = $discountDetails['free_shipping'] ?? false;

                            // ✅ Apply free shipping
                            if ($freeShipping) {
                                $shippingAmount = 0;
                            }

                            $appliedCoupon = $coupon;
                            $couponId = $coupon->id;

                            \Log::info('✅ Coupon applied during order creation', [
                                'code' => $coupon->code,
                                'discount_type' => $coupon->discount_type,
                                'discount_amount' => $discountAmount,
                                'free_shipping' => $freeShipping,
                                'original_shipping' => $validated['shipping_amount'] ?? 0,
                                'final_shipping' => $shippingAmount,
                            ]);
                        } else {
                            \Log::warning('⚠️ Coupon not applicable to cart', [
                                'code' => $coupon->code,
                                'reason' => $cartCheck['message'] ?? 'Unknown',
                            ]);
                        }
                    } else {
                        \Log::warning('⚠️ Customer not eligible for coupon', [
                            'code' => $coupon->code,
                            'reason' => $customerCheck['message'] ?? 'Unknown',
                        ]);
                    }
                } else {
                    \Log::warning('⚠️ Invalid or expired coupon', [
                        'code' => $validated['discount_code'],
                    ]);
                }
            }

            // ✅ Recalculate total with coupon applied
            $totalAmount = $subtotal + $exclusiveTaxTotal + $additionalTax + $shippingAmount - $discountAmount;
            $totalAmount = max(0, $totalAmount);
            // ============================================================
            // STEP 3: CREATE ORDER (same as before)
            // ============================================================
            $orderData = array_merge([
                // Calculated values FIRST
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'tax_rate' => $additionalTaxRate,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'coupon_id' => $couponId ?? null,
            ], $validated, [ // Then validated, then override system fields
                'order_source' => 'admin',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            unset($orderData['items']);
            $order = Order::create($orderData);
            // ============================================================
            // RECORD COUPON USAGE (After order creation) ✅
            // ============================================================
            if ($appliedCoupon && $couponId) {
                $appliedCoupon->recordUsage(
                    $order->id,
                    $validated['customer_id'] ?? null,
                    $validated['guest_email'] ?? null,
                    $discountAmount,
                    $subtotal,
                    $totalAmount,
                    $request->ip()
                );

                \Log::info('✅ Coupon usage recorded', [
                    'order_number' => $order->order_number,
                    'coupon_code' => $appliedCoupon->code,
                    'discount_amount' => $discountAmount,
                    'total_used' => $appliedCoupon->fresh()->total_used,
                ]);
            }
            // Trigger notifications (same as before)
            $this->notificationService->notify('order_created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->getFormattedTotal(),
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
            ]);

            // ... (keep other notification triggers)

            \Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // ============================================================
            // STEP 4: CREATE ORDER ITEMS (WITH VARIANT SUPPORT)
            // ============================================================
            foreach ($validated['items'] as $index => $itemData) {
                $product = Product::find($itemData['product_id']);
                $variant = !empty($itemData['variant_id']) ? ProductVariant::find($itemData['variant_id']) : null;

                // ✅ Use variant details if available
                $itemName = $variant ? "{$product->name} - {$variant->getFullName()}" : $product->name;
                $itemSku = $variant ? $variant->sku : $product->sku;
                $itemImage = $variant ? $variant->image_path : $product->main_image;

                $itemSubtotal = $itemData['unit_price'] * $itemData['quantity'];

                // ✅ Calculate tax based on product tax settings
                $itemTaxAmount = 0;
                $itemTaxRate = $product->tax_percentage ?? 0;

                if ($product->is_taxable && $itemTaxRate > 0) {
                    $taxRate = $itemTaxRate / 100;

                    if ($product->tax_type === 'inclusive') {
                        // Tax already in price - extract it
                        $basePrice = $itemSubtotal / (1 + $taxRate);
                        $itemTaxAmount = $itemSubtotal - $basePrice;
                    } else {
                        // Tax to be added
                        $itemTaxAmount = $itemSubtotal * $taxRate;
                    }
                }

                // ✅ Calculate item total (subtotal + exclusive tax only)
                // Inclusive tax is already in subtotal, so we only add exclusive tax
                if ($product->tax_type === 'exclusive') {
                    $itemTotal = $itemSubtotal + $itemTaxAmount;
                } else {
                    $itemTotal = $itemSubtotal; // Inclusive tax already in price
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null, // ← NEW
                    'product_name' => $itemName, // ← Updated
                    'product_sku' => $itemSku, // ← Updated
                    'product_description' => $product->short_description,
                    'product_image' => $itemImage, // ← Updated
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'cost_price' => $variant ? $variant->getFinalPrice() : $product->cost_price,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTaxAmount,
                    'tax_rate' => $product->tax_percentage,
                    'is_taxable' => $product->is_taxable,
                    'total' => $itemTotal,
                    'sort_order' => $index,
                    'status_key_code' => 'ITEM_PENDING',
                ]);

                \Log::info('Order item created', [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'quantity' => $itemData['quantity']
                ]);
            }

            // ============================================================
                // ✅ CREATE TRANSACTION RECORD
            // ============================================================
            $transaction = $this->createTransaction($order, $request);
            if (!$transaction) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create transaction record');
            }

            \Log::info('💳 Transaction created for admin order', [
                'order' => $order->order_number,
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
            ]);
            // ============================================================
            // STEP 5: HANDLE STOCK BASED ON ORDER STATUS (NO PAYMENT CHECKS)
            // ============================================================
            $orderStatus = $order->status_key_code;

            // Get default warehouse
            $defaultWarehouse = Warehouse::where('is_default', true)->first();

            if (!$defaultWarehouse) {
                DB::rollBack();
                \Log::error('❌ No default warehouse configured');
                return response()->json([
                    'success' => false,
                    'message' => 'System error: No default warehouse found. Please contact administrator.'
                ], 500);
            }

            \Log::info('📦 Processing stock for order', [
                'order' => $order->order_number,
                'status' => $orderStatus,
                'items_count' => count($validated['items']),
            ]);

            // ============================================================
            // DETERMINE STOCK ACTION BASED ON STATUS ONLY
            // ============================================================
            $shouldReserve = ($orderStatus === 'ORDER_PENDING');
            $shouldDeduct = in_array($orderStatus, ['ORDER_CONFIRMED', 'ORDER_PROCESSING', 'ORDER_PACKED', 'ORDER_SHIPPED', 'ORDER_DELIVERED']);
            $shouldSkip = in_array($orderStatus, ['ORDER_CANCELLED', 'ORDER_RETURNED']);

            if ($shouldSkip) {
                \Log::info('⏭️  Skipping stock (order cancelled/returned)', [
                    'order' => $order->order_number,
                ]);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => "Order #{$order->order_number} created successfully!",
                    'redirect' => route('admin.orders.show', $order->id)
                ]);
            }

            // ============================================================
            // PROCESS EACH ORDER ITEM
            // ============================================================
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);

                if (!$product || !$product->track_inventory) {
                    continue;
                }

                $quantity = $itemData['quantity'];
                $variant = !empty($itemData['variant_id']) ? ProductVariant::find($itemData['variant_id']) : null;
                $itemName = $variant ? "{$product->name} ({$variant->getFullName()})" : $product->name;

                // ✅ CHECK TOTAL AVAILABLE STOCK ACROSS ALL WAREHOUSES
                $totalAvailableStock = ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->sum('available_quantity');

                if ($totalAvailableStock < $quantity) {
                    DB::rollBack();

                    \Log::error('❌ Insufficient total stock across all warehouses', [
                        'product' => $product->name,
                        'variant' => $variant ? $variant->getFullName() : null,
                        'requested' => $quantity,
                        'total_available' => $totalAvailableStock,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$itemName}.\nTotal available: {$totalAvailableStock}\nRequested: {$quantity}"
                    ], 400);
                }

                // ✅ GET ALL WAREHOUSES WITH STOCK (ORDERED BY QUANTITY DESC)
                $warehouseStocks = ProductWarehouseStock::where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->where('available_quantity', '>', 0)
                    ->orderBy('available_quantity', 'desc')
                    ->get();

                if ($warehouseStocks->isEmpty()) {
                    DB::rollBack();

                    \Log::error('❌ No warehouse stock records found', [
                        'product' => $product->name,
                        'variant' => $variant ? $variant->getFullName() : null,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => "No warehouse stock found for {$itemName}."
                    ], 400);
                }

                // ✅ SPLIT QUANTITY ACROSS MULTIPLE WAREHOUSES IF NEEDED
                $remainingQuantity = $quantity;
                $fulfillmentDetails = [];

                foreach ($warehouseStocks as $warehouseStock) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    // Calculate how much we can take from this warehouse
                    $quantityFromWarehouse = min($remainingQuantity, $warehouseStock->available_quantity);

                    // ============================================================
                    // SCENARIO 1: PENDING → RESERVE STOCK
                    // ============================================================
                    if ($shouldReserve) {
                        if ($warehouseStock->reserveStock($quantityFromWarehouse)) {
                            $fulfillmentDetails[] = [
                                'warehouse_id' => $warehouseStock->warehouse_id,
                                'warehouse_name' => $warehouseStock->warehouse->name ?? 'Unknown',
                                'quantity' => $quantityFromWarehouse,
                                'action' => 'RESERVED',
                            ];

                            \Log::info('🔒 RESERVED from warehouse', [
                                'order' => $order->order_number,
                                'product' => $itemName,
                                'warehouse' => $warehouseStock->warehouse->name ?? 'Unknown',
                                'quantity' => $quantityFromWarehouse,
                                'remaining' => $remainingQuantity - $quantityFromWarehouse,
                            ]);

                            $remainingQuantity -= $quantityFromWarehouse;
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Failed to reserve stock from warehouse: {$warehouseStock->warehouse->name}"
                            ], 500);
                        }
                    }

                    // ============================================================
                    // SCENARIO 2: CONFIRMED/PROCESSING → DEDUCT STOCK
                    // ============================================================
                    elseif ($shouldDeduct) {
                        if ($warehouseStock->quantity < $quantityFromWarehouse) {
                            DB::rollBack();

                            \Log::error('❌ Insufficient stock in warehouse', [
                                'warehouse' => $warehouseStock->warehouse->name,
                                'available' => $warehouseStock->quantity,
                                'needed' => $quantityFromWarehouse,
                            ]);

                            return response()->json([
                                'success' => false,
                                'message' => "Insufficient stock in warehouse: {$warehouseStock->warehouse->name}"
                            ], 400);
                        }

                        $warehouseStock->reduceStock($quantityFromWarehouse);

                        $fulfillmentDetails[] = [
                            'warehouse_id' => $warehouseStock->warehouse_id,
                            'warehouse_name' => $warehouseStock->warehouse->name ?? 'Unknown',
                            'quantity' => $quantityFromWarehouse,
                            'action' => 'DEDUCTED',
                        ];

                        \Log::info('⚡ DEDUCTED from warehouse', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                            'warehouse' => $warehouseStock->warehouse->name ?? 'Unknown',
                            'quantity' => $quantityFromWarehouse,
                            'remaining' => $remainingQuantity - $quantityFromWarehouse,
                        ]);

                        $remainingQuantity -= $quantityFromWarehouse;
                    }
                }

                // ✅ VERIFY ALL QUANTITY WAS FULFILLED
                if ($remainingQuantity > 0) {
                    DB::rollBack();

                    \Log::error('❌ Could not fulfill complete order quantity', [
                        'product' => $itemName,
                        'requested' => $quantity,
                        'fulfilled' => $quantity - $remainingQuantity,
                        'remaining' => $remainingQuantity,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => "Could not fulfill complete quantity for {$itemName}"
                    ], 500);
                }

                // ✅ UPDATE ORDER ITEM WITH FULFILLMENT DETAILS
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->where('product_variant_id', $variant ? $variant->id : null)
                    ->first();

                if ($orderItem) {
                    $orderItem->update([
                        'warehouse_id' => $fulfillmentDetails[0]['warehouse_id'], // Primary warehouse
                        'stock_reserved' => $shouldReserve,
                        'stock_reserved_at' => $shouldReserve ? now() : null,
                        'stock_deducted' => $shouldDeduct,
                        'stock_deducted_at' => $shouldDeduct ? now() : null,
                        'fulfillment_details' => $fulfillmentDetails,
                    ]);
                }

                \Log::info('✅ Item fulfilled from warehouses', [
                    'product' => $itemName,
                    'total_quantity' => $quantity,
                    'warehouses_used' => count($fulfillmentDetails),
                    'details' => $fulfillmentDetails,
                ]);
            }

            \Log::info('✅ All stock operations completed', [
                'order' => $order->order_number,
                'status' => $orderStatus,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} created successfully!",
                'redirect' => route('admin.orders.show', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
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
     * Get product tax information (for order creation)
     */
    public function getProductTaxInfo($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            return response()->json([
                'success' => true,
                'tax_info' => [
                    'is_taxable' => $product->is_taxable,
                    'tax_type' => $product->tax_type,
                    'tax_percentage' => $product->tax_percentage ?? 0,
                    'tax_rate' => $product->tax_percentage ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tax info'
            ], 500);
        }
    }

    /**
     * Handle status change with simplified stock management
     * Stock Deduction Rules:
     * - PENDING → CONFIRMED/PROCESSING/SHIPPED/DELIVERED: Deduct stock
     * - Direct creation with CONFIRMED/PROCESSING/SHIPPED/DELIVERED: Deduct stock
     * - ANY → CANCELLED/RETURNED: Restore stock
     */
    private function handleStatusChange($order, $oldStatus, $newStatus)
    {
        if ($oldStatus === $newStatus) {
            return;
        }
        $this->updateTransactionStatus($order, $oldStatus, $newStatus);
        // ✅ Define valid transitions
        $validTransitions = [
            'ORDER_PENDING' => ['ORDER_CONFIRMED', 'ORDER_PROCESSING', 'ORDER_SHIPPED', 'ORDER_DELIVERED', 'ORDER_CANCELLED'],
            'ORDER_CONFIRMED' => ['ORDER_PROCESSING', 'ORDER_PACKED', 'ORDER_SHIPPED', 'ORDER_CANCELLED'],
            'ORDER_PROCESSING' => ['ORDER_PACKED', 'ORDER_SHIPPED', 'ORDER_CANCELLED'],
            'ORDER_PACKED' => ['ORDER_SHIPPED', 'ORDER_CANCELLED'],
            'ORDER_SHIPPED' => ['ORDER_DELIVERED', 'ORDER_RETURNED', 'ORDER_CANCELLED'],
            'ORDER_DELIVERED' => ['ORDER_RETURNED'],
            'ORDER_CANCELLED' => ['ORDER_PENDING', 'ORDER_CONFIRMED', 'ORDER_PROCESSING'],
            'ORDER_RETURNED' => [], // Final state
        ];

        // Validate transition (with logging only, don't block)
        if (isset($validTransitions[$oldStatus]) &&
            !in_array($newStatus, $validTransitions[$oldStatus])) {

            \Log::warning('⚠️ Invalid status transition attempted', [
                'from' => $oldStatus,
                'to' => $newStatus,
            ]);
            return;
        }

        \Log::info('🔄 Status transition', [
            'order' => $order->order_number,
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        // ============================================================
        // DEFINE STOCK ACTION STATUSES
        // ============================================================
        $deductStatuses = ['ORDER_CONFIRMED', 'ORDER_PROCESSING', 'ORDER_SHIPPED', 'ORDER_DELIVERED'];
        $restoreStatuses = ['ORDER_CANCELLED', 'ORDER_RETURNED'];

        $shouldDeduct = in_array($newStatus, $deductStatuses);
        $shouldRestore = in_array($newStatus, $restoreStatuses);

        // ============================================================
        // PROCESS EACH ORDER ITEM
        // ============================================================
        foreach ($order->items as $item) {
            if (!$item->product || !$item->product->track_inventory) {
                continue;
            }

            $product = $item->product;
            $variant = $item->variant;
            $quantity = $item->quantity;
            $warehouseId = $item->warehouse_id;

            // Ensure warehouse is set
            if (!$warehouseId) {
                $defaultWarehouse = Warehouse::where('is_default', true)->first();
                if (!$defaultWarehouse) {
                    \Log::error('❌ No warehouse', ['order_item' => $item->id]);
                    continue;
                }
                $warehouseId = $defaultWarehouse->id;
                $item->update(['warehouse_id' => $warehouseId]);
            }

            // Get warehouse stock
            $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                ->where('variant_id', $variant ? $variant->id : null)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (!$warehouseStock) {
                \Log::error('❌ Warehouse stock not found', [
                    'product' => $product->name,
                    'warehouse_id' => $warehouseId,
                ]);
                continue;
            }

            $itemName = $variant ? "{$product->name} ({$variant->getFullName()})" : $product->name;

            // ============================================================
            // SCENARIO 1: PENDING → Reserve Stock (Optional - Keep if needed)
            // ============================================================
            if ($newStatus === 'ORDER_PENDING' && !$item->stock_reserved && !$item->stock_deducted) {
                if ($warehouseStock->reserveStock($quantity)) {
                    $item->update([
                        'stock_reserved' => true,
                        'stock_reserved_at' => now(),
                    ]);

                    \Log::info('🔒 Reserved (→ PENDING)', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                    ]);
                }
            }

            // ============================================================
            // SCENARIO 2: ANY → CONFIRMED/PROCESSING/SHIPPED/DELIVERED (Deduct Stock)
            // ============================================================
            elseif ($shouldDeduct && !$item->stock_deducted) {
                // If stock was reserved (from PENDING), release reservation first
                if ($item->stock_reserved) {
                    $warehouseStock->releaseStock($quantity);
                    \Log::info('🔓 Released reservation before deducting', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                    ]);
                }

                // Deduct actual stock
                $warehouseStock->reduceStock($quantity);

                $item->update([
                    'stock_reserved' => false,
                    'stock_reserved_at' => null,
                    'stock_deducted' => true,
                    'stock_deducted_at' => now(),
                ]);

                \Log::info('⚡ DEDUCTED', [
                    'order' => $order->order_number,
                    'product' => $itemName,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'quantity' => $quantity,
                    'remaining_stock' => $warehouseStock->fresh()->quantity,
                ]);
            }

            // ============================================================
            // SCENARIO 3: ANY → CANCELLED/RETURNED (Restore Stock)
            // ============================================================
            elseif ($shouldRestore) {
                $fulfillmentDetails = null;
                if (!empty($item->fulfillment_details)) {
                    try {
                        $fulfillmentDetails = is_string($item->fulfillment_details)
                            ? json_decode($item->fulfillment_details, true)
                            : $item->fulfillment_details;
                    } catch (\Exception $e) {
                        \Log::warning('Failed to parse fulfillment_details', [
                            'item_id' => $item->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // ============================================================
                // MULTI-WAREHOUSE RESTORATION
                // ============================================================
                if (!empty($fulfillmentDetails) && is_array($fulfillmentDetails)) {
                    \Log::info('🔄 Restoring stock to multiple warehouses', [
                        'order' => $order->order_number,
                        'product' => $itemName,
                        'warehouses_count' => count($fulfillmentDetails),
                    ]);

                    foreach ($fulfillmentDetails as $fulfillment) {
                        $warehouseId = $fulfillment['warehouse_id'] ?? null;
                        $warehouseQty = $fulfillment['quantity'] ?? 0;
                        $action = $fulfillment['action'] ?? 'UNKNOWN';

                        if (!$warehouseId || $warehouseQty <= 0) {
                            continue;
                        }

                        $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                            ->where('variant_id', $variant ? $variant->id : null)
                            ->where('warehouse_id', $warehouseId)
                            ->first();

                        if (!$warehouseStock) {
                            \Log::error('❌ Warehouse stock not found during restoration', [
                                'warehouse_id' => $warehouseId,
                                'product' => $itemName,
                            ]);
                            continue;
                        }

                        // Restore based on original action
                        if ($action === 'DEDUCTED' && $item->stock_deducted) {
                            $warehouseStock->addStock($warehouseQty);

                            \Log::info('✅ RESTORED to warehouse', [
                                'order' => $order->order_number,
                                'product' => $itemName,
                                'warehouse' => $fulfillment['warehouse_name'] ?? $warehouseId,
                                'quantity' => $warehouseQty,
                                'new_stock' => $warehouseStock->fresh()->quantity,
                            ]);

                        } elseif ($action === 'RESERVED' && $item->stock_reserved) {
                            $warehouseStock->releaseStock($warehouseQty);

                            \Log::info('✅ RELEASED from warehouse', [
                                'order' => $order->order_number,
                                'product' => $itemName,
                                'warehouse' => $fulfillment['warehouse_name'] ?? $warehouseId,
                                'quantity' => $warehouseQty,
                            ]);
                        }
                    }

                    // Update item status
                    $item->update([
                        'stock_deducted' => false,
                        'stock_deducted_at' => null,
                        'stock_reserved' => false,
                        'stock_reserved_at' => null,
                        'status_key_code' => $newStatus === 'ORDER_CANCELLED' ? 'ITEM_CANCELLED' : 'ITEM_RETURNED',
                    ]);

                }
                // ============================================================
                // SINGLE WAREHOUSE RESTORATION (FALLBACK)
                // ============================================================
                else {
                    // If stock was deducted, add it back
                    if ($item->stock_deducted) {
                        $warehouseStock->addStock($quantity);

                        $item->update([
                            'stock_deducted' => false,
                            'stock_deducted_at' => null,
                            'status_key_code' => $newStatus === 'ORDER_CANCELLED' ? 'ITEM_CANCELLED' : 'ITEM_RETURNED',
                        ]);

                        \Log::info('✅ RESTORED (single warehouse)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                            'quantity' => $quantity,
                            'new_stock' => $warehouseStock->fresh()->quantity,
                            'reason' => $newStatus,
                        ]);
                    }
                    // If stock was only reserved, release it
                    elseif ($item->stock_reserved) {
                        $warehouseStock->releaseStock($quantity);

                        $item->update([
                            'stock_reserved' => false,
                            'stock_reserved_at' => null,
                            'status_key_code' => $newStatus === 'ORDER_CANCELLED' ? 'ITEM_CANCELLED' : 'ITEM_RETURNED',
                        ]);

                        \Log::info('✅ RELEASED (single warehouse)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                            'reason' => $newStatus,
                        ]);
                    }
                }
            }

            // ============================================================
            // SCENARIO 4: CANCELLED/RETURNED → Reactivation (Re-deduct if needed)
            // ============================================================
            elseif (in_array($oldStatus, ['ORDER_CANCELLED', 'ORDER_RETURNED'])) {
                if ($newStatus === 'ORDER_PENDING') {
                    // Re-reserve stock
                    if ($warehouseStock->reserveStock($quantity)) {
                        $item->update([
                            'stock_reserved' => true,
                            'stock_reserved_at' => now(),
                            'status_key_code' => 'ITEM_PENDING',
                        ]);

                        \Log::info('🔒 Re-reserved (reactivation)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                        ]);
                    }
                } elseif ($shouldDeduct) {
                    // Re-deduct stock
                    if ($warehouseStock->quantity >= $quantity) {
                        $warehouseStock->reduceStock($quantity);

                        $item->update([
                            'stock_deducted' => true,
                            'stock_deducted_at' => now(),
                            'status_key_code' => 'ITEM_PROCESSING',
                        ]);

                        \Log::info('⚡ Re-deducted (reactivation)', [
                            'order' => $order->order_number,
                            'product' => $itemName,
                        ]);
                    }
                }
            }

            // ============================================================
            // SCENARIO 5: PACKED → No Stock Change (Already deducted)
            // ============================================================
            elseif ($newStatus === 'ORDER_PACKED') {
                \Log::info('📦 Packed (no stock change)', [
                    'order' => $order->order_number,
                    'product' => $itemName,
                ]);
            }
        }

        \Log::info('✅ Status transition completed', [
            'order' => $order->order_number,
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);
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

    public function updateStatus(Request $request, $id)
    {
        $order = Order::with([
            'items.product',
            'items.variant',
            'items.warehouse'
        ])->findOrFail($id);

        $validated = $request->validate([
            'status_key_code' => 'required|string|exists:system_statuses,key_code',
            'tracking_number' => 'nullable|string|max:100',
            'carrier' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:5000',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status_key_code;
            $newStatus = $validated['status_key_code'];
            // Auto-confirm if moving from PENDING to further statuses
            if ($oldStatus === 'ORDER_PENDING' &&
                in_array($newStatus, ['ORDER_PROCESSING', 'ORDER_PACKED', 'ORDER_SHIPPED', 'ORDER_DELIVERED'])) {

                \Log::warning('⚠️ Forcing ORDER_CONFIRMED before advancing to ' . $newStatus);

                // First confirm the order
                $this->handleStatusChange($order, $oldStatus, 'ORDER_CONFIRMED');
                $order->update([
                    'status_key_code' => 'ORDER_CONFIRMED',
                    'confirmed_at' => now(),
                ]);

                $this->notificationService->notify('order_confirmed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_confirmed', $order);

                // Update oldStatus for next transition
                $oldStatus = 'ORDER_CONFIRMED';
                $order->refresh(); // Refresh to get updated status
            }
            // Check if already in target status
            if ($oldStatus === $newStatus) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already in ' . $order->getStatusLabel() . ' status.'
                ], 400);
            }

            // Check if can update
            if (!$order->canUpdateStatus()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be updated. Current status: ' . $order->getStatusLabel()
                ], 400);
            }

            // ============================================================
            // STATUS-SPECIFIC ACTIONS (NO PAYMENT CHECKS) ✅
            // ============================================================

            if ($newStatus === 'ORDER_CONFIRMED') {
                $order->confirm();
                $this->notificationService->notify('order_confirmed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_confirmed', $order);
            }

            elseif ($newStatus === 'ORDER_PROCESSING') {
                $order->markAsProcessing();
                $this->notificationService->notify('order_processing', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_processing', $order);
            }

            elseif ($newStatus === 'ORDER_PACKED') {
                $order->markAsPacked();
                $this->notificationService->notify('order_packed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_packed', $order);
            }

            elseif ($newStatus === 'ORDER_SHIPPED') {
                $order->markAsShipped(
                    $validated['tracking_number'] ?? null,
                    $validated['carrier'] ?? null
                );

                foreach ($order->items as $item) {
                    $item->markAsFulfilled();
                }

                $this->notificationService->notify('order_shipped', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'tracking_number' => $order->shipping_tracking_number,
                ]);
                $this->notificationService->notifyCustomer('order_shipped', $order, [
                    'tracking_number' => $order->shipping_tracking_number,
                ]);
            }

            elseif ($newStatus === 'ORDER_DELIVERED') {
                $order->markAsDelivered();
                $this->notificationService->notify('order_delivered', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_delivered', $order);
            }

            elseif ($newStatus === 'ORDER_CANCELLED') {
                $order->cancel($validated['notes'] ?? 'Cancelled by admin');
                $this->notificationService->notify('order_cancelled', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_cancelled', $order, [
                    'reason' => $validated['notes'] ?? 'Cancelled by admin',
                ]);
            }

            // ============================================================
            // HANDLE STOCK OPERATIONS
            // ============================================================
            $this->handleStatusChange($order, $oldStatus, $newStatus);

            // Add admin note
            if (!empty($validated['notes'])) {
                $currentNotes = $order->admin_notes ?? '';
                $timestamp = now()->format('Y-m-d H:i:s');
                $adminName = auth('admin')->user()->name ?? 'System';
                $newNote = "\n[{$timestamp}] {$adminName}: {$oldStatus} → {$newStatus}. {$validated['notes']}";

                $order->update(['admin_notes' => $currentNotes . $newNote]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully!',
                'order' => [
                    'id' => $order->id,
                    'status_badge' => $order->fresh()->getStatusBadge(),
                ]
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

    private function processRefund(Order $order, array $refundData, Request $request)
    {
        try {
            DB::beginTransaction();

            // Get original payment transaction
            $originalTransaction = $order->transactions()
                ->where('transaction_type', 'payment')
                ->first();

            if (!$originalTransaction) {
                throw new \Exception('Original payment transaction not found');
            }

            // Determine refund type
            $isFullRefund = $refundData['refund_amount'] >= $order->total_amount;
            $refundType = $isFullRefund ? 'refund' : 'partial_refund';

            // ✅ CREATE REFUND TRANSACTION
            $refundTransaction = Transaction::createRefund(
                order: $order,
                originalTransaction: $originalTransaction,
                amount: $refundData['refund_amount'],
                reason: $refundData['reason'] ?? 'Refund processed by admin',
                refundMethod: $refundData['refund_method'] ?? $order->payment_method,
                processedBy: auth()->id()
            );

            // Update original transaction
            if ($isFullRefund) {
                $originalTransaction->update([
                    'status_key_code' => 'TRANSACTION_REFUNDED',
                    'gateway_status' => 'refunded',
                ]);
            } else {
                $originalTransaction->update([
                    'status_key_code' => 'TRANSACTION_PARTIALLY_REFUNDED',
                    'gateway_status' => 'partially_refunded',
                ]);
            }

            // Update order
            $order->update([
                'refund_amount' => $refundData['refund_amount'],
                'refund_reason' => $refundData['reason'] ?? null,
                'refund_processed_at' => now(),
                'refund_processed_by' => auth()->id(),
                'payment_status_key_code' => $isFullRefund ? 'PAYMENT_REFUNDED' : 'PAYMENT_PARTIALLY_REFUNDED',
            ]);

            DB::commit();

            \Log::info('✅ Refund transaction created', [
                'order' => $order->order_number,
                'refund_transaction' => $refundTransaction->transaction_number,
                'amount' => $refundData['refund_amount'],
                'type' => $refundType,
            ]);

            // Send notifications
            $this->notificationService->notify('refund_processed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'refund_amount' => $order->currency . ' ' . number_format($refundData['refund_amount'], 2),
                'refund_type' => $isFullRefund ? 'Full Refund' : 'Partial Refund',
            ]);

            $this->notificationService->notifyCustomer('refund_processed', $order);

            return $refundTransaction;

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Refund processing failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            throw $e;
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
        $paymentStatusList = SystemStatus::where('module', 'payments')->get();
        return view('admin.orders.status', compact('status', 'statusKey', 'statusList','paymentStatusList'));
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

    public function quickUpdateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status_key_code' => 'nullable|string|exists:system_statuses,key_code',
                'payment_status_key_code' => 'nullable|string|exists:system_statuses,key_code',
            ]);

            $order = Order::with([
                'items.product',
                'items.variant',
                'items.warehouse'
            ])->findOrFail($id);

            DB::beginTransaction();

            $oldStatus = $order->status_key_code;
            $oldPaymentStatus = $order->payment_status_key_code;

            \Log::info('📊 Quick update', [
                'order' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $validated['status_key_code'] ?? 'unchanged',
            ]);

            // ============================================================
            // UPDATE ORDER STATUS (NO PAYMENT VALIDATION) ✅
            // ============================================================
            if (isset($validated['status_key_code'])) {
                $newStatus = $validated['status_key_code'];

                if (!$order->canUpdateStatus()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Order cannot be updated (already ' . $order->getStatusLabel() . ')'
                    ], 400);
                }

                $order->status_key_code = $newStatus;

                // Handle stock operations
                if ($oldStatus !== $newStatus) {
                    $this->handleStatusChange($order, $oldStatus, $newStatus);
                    $this->triggerStatusNotification($order, $newStatus);
                }
            }

            // ============================================================
            // UPDATE PAYMENT STATUS (SEPARATE FROM STOCK)
            // ============================================================
            if (isset($validated['payment_status_key_code'])) {
                $newPaymentStatus = $validated['payment_status_key_code'];
                $order->payment_status_key_code = $newPaymentStatus;

                if ($oldPaymentStatus !== $newPaymentStatus && $newPaymentStatus === 'PAYMENT_PAID') {
                    $this->notificationService->notify('payment_received', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'amount' => $order->getFormattedTotal(),
                        'payment_method' => $order->payment_method ?? 'N/A',
                    ]);
                }
            }

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status_key_code,
                    'status_badge' => $order->getStatusBadge(),
                    'payment_badge' => $order->getPaymentStatusBadge(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Quick update failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW HELPER METHOD: Trigger notifications based on status
     */
    private function triggerStatusNotification($order, $newStatus)
    {
        switch ($newStatus) {
            case 'ORDER_CONFIRMED':
                $this->notificationService->notify('order_confirmed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->getFormattedTotal(),
                ]);
                $this->notificationService->notifyCustomer('order_confirmed', $order);
                break;

            case 'ORDER_PROCESSING':
                $this->notificationService->notify('order_processing', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->getCustomerName(),
                    'customer_email' => $order->getCustomerEmail(),
                    'total_amount' => $order->getFormattedTotal(),
                ]);
                $this->notificationService->notifyCustomer('order_processing', $order);
                break;

            case 'ORDER_PACKED':
                $this->notificationService->notify('order_packed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->getCustomerName(),
                    'items_count' => $order->getTotalItemsCount(),
                ]);
                $this->notificationService->notifyCustomer('order_packed', $order);
                break;

            case 'ORDER_SHIPPED':
                $this->notificationService->notify('order_shipped', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'tracking_number' => $order->shipping_tracking_number,
                    'carrier' => $order->shipping_carrier,
                ]);
                $this->notificationService->notifyCustomer('order_shipped', $order, [
                    'tracking_number' => $order->shipping_tracking_number,
                    'carrier' => $order->shipping_carrier,
                ]);
                break;

            case 'ORDER_DELIVERED':
                $this->notificationService->notify('order_delivered', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_delivered', $order);
                break;

            case 'ORDER_CANCELLED':
                $this->notificationService->notify('order_cancelled', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                $this->notificationService->notifyCustomer('order_cancelled', $order, [
                    'reason' => 'Cancelled by admin',
                ]);
                break;
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

    /**
     * Get product variants (AJAX) - FOR ORDER CREATION
     */
    public function getProductVariants($productId)
    {
        try {
            $product = Product::with(['variants' => function($query) {
                $query->active()->ordered();
            }])->findOrFail($productId);

            $variants = $product->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->getFullName(),
                    'display_name' => $variant->getDisplayName(),
                    'sku' => $variant->sku,
                    'price' => $variant->getFinalPrice(),
                    'formatted_price' => $variant->getFormattedFinalPrice(),
                    'stock' => $variant->stock_quantity,
                    'is_in_stock' => $variant->isInStock(),
                    'image' => $variant->getImageUrl(),
                ];
            });

            return response()->json([
                'success' => true,
                'has_variants' => $product->has_variants,
                'variants' => $variants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate and apply coupon code (for order creation/editing)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'coupon_code' => 'required|string|max:50',
                'customer_id' => 'nullable|uuid',
                'guest_email' => 'nullable|email',
                'cart_items' => 'required|array',
                'cart_items.*.product_id' => 'required|uuid',
                'cart_items.*.variant_id' => 'nullable|uuid',
                'cart_items.*.quantity' => 'required|integer|min:1',
                'cart_items.*.unit_price' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
            ]);

            // Find coupon
            $coupon = Coupon::byCode($validated['coupon_code'])
                ->active()
                ->valid()
                ->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon code',
                ], 404);
            }

            // Check if coupon is valid
            if (!$coupon->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon is no longer valid',
                ], 400);
            }

            // Check customer eligibility
            $customerCheck = $coupon->canBeUsedByCustomer(
                $validated['customer_id'] ?? null,
                $validated['guest_email'] ?? null
            );

            if (!$customerCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $customerCheck['message'],
                ], 400);
            }

            // Prepare cart items for validation
            $cartItems = [];
            foreach ($validated['cart_items'] as $item) {
                $cartItems[] = [
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'price' => (float) $item['unit_price'],
                    'quantity' => (int) $item['quantity'],
                ];
            }

            $subtotal = (float) $validated['subtotal'];
            $itemCount = array_sum(array_column($cartItems, 'quantity'));

            // Check cart applicability
            $cartCheck = $coupon->isApplicableToCart($cartItems, $subtotal, $itemCount);

            if (!$cartCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $cartCheck['message'],
                ], 400);
            }

            // Calculate discount
            $discountDetails = $coupon->calculateDiscount($cartItems, $subtotal);
            $discountAmount = (float) ($discountDetails['discount_amount'] ?? 0);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully!',
                'data' => [
                    'coupon_id' => $coupon->id,
                    'coupon_code' => $coupon->code,
                    'coupon_name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount_amount' => $discountAmount,
                    'formatted_discount' => store_currency_symbol() . ' ' . number_format($discountAmount, 2),
                    'free_shipping' => $discountDetails['free_shipping'] ?? false,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Coupon validation failed', [
                'error' => $e->getMessage(),
                'code' => $validated['coupon_code'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate coupon',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update transaction status based on order status change
     *
     * @param Order $order
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    private function updateTransactionStatus(Order $order, string $oldStatus, string $newStatus): void
    {
        try {
            // Get the payment transaction
            $transaction = $order->transactions()
                ->where('transaction_type', 'payment')
                ->first();

            if (!$transaction) {
                \Log::warning('⚠️ No payment transaction found for order', [
                    'order' => $order->order_number,
                ]);
                return;
            }

            $transactionUpdates = [];

            switch ($newStatus) {
                case 'ORDER_CONFIRMED':
                    // Order confirmed - mark transaction as processing
                    $transactionUpdates = [
                        'status_key_code' => 'TRANSACTION_PROCESSING',
                        'gateway_status' => 'processing',
                    ];
                    break;

                case 'ORDER_PROCESSING':
                    // Order being processed
                    $transactionUpdates = [
                        'status_key_code' => 'TRANSACTION_PROCESSING',
                        'gateway_status' => 'processing',
                    ];
                    break;

                case 'ORDER_SHIPPED':
                    // Order shipped - if COD, mark as authorized
                    if ($order->payment_method === 'cod') {
                        $transactionUpdates = [
                            'status_key_code' => 'TRANSACTION_AUTHORIZED',
                            'gateway_status' => 'authorized',
                        ];
                    }
                    break;

                case 'ORDER_DELIVERED':
                    // Order delivered - mark transaction as successful
                    $transactionUpdates = [
                        'status_key_code' => 'TRANSACTION_SUCCESS',
                        'gateway_status' => 'completed',
                        'completed_at' => now(),
                    ];

                    // Also update order payment status
                    $order->update([
                        'payment_status_key_code' => 'PAYMENT_PAID',
                    ]);
                    break;

                case 'ORDER_CANCELLED':
                    // Order cancelled
                    if ($transaction->status_key_code === 'TRANSACTION_PENDING') {
                        $transactionUpdates = [
                            'status_key_code' => 'TRANSACTION_CANCELLED',
                            'gateway_status' => 'cancelled',
                            'failed_at' => now(),
                            'failure_reason' => 'Order cancelled by admin',
                        ];
                    }else if (in_array($transaction->status_key_code, ['TRANSACTION_PROCESSING', 'TRANSACTION_AUTHORIZED','TRANSACTION_SUCCESS'])) {
                        $transactionUpdates = [
                            'status_key_code' => 'TRANSACTION_REFUNDED',
                            'gateway_status' => 'refunded',
                            'refunded_at' => now(),
                            'notes' => 'Order cancelled - transaction refunded',
                        ];
                    }
                    break;

                case 'ORDER_RETURNED':
                    // Order returned - will be handled by refund process
                    break;
            }

            if (!empty($transactionUpdates)) {
                $transaction->update($transactionUpdates);

                \Log::info('✅ Transaction status updated', [
                    'order' => $order->order_number,
                    'transaction' => $transaction->transaction_number,
                    'new_status' => $transactionUpdates['status_key_code'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Failed to update transaction status', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create transaction record for order
     *
     * @param Order $order
     * @param Request $request
     * @return Transaction|null
     */
    private function createTransaction(Order $order, Request $request): ?Transaction
    {
        try {
            $transactionData = [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'transaction_type' => 'payment',
                'payment_method' => $order->payment_method ?? 'online_payment',
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'fee' => 0,

                // Billing information from order
                'billing_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                'billing_email' => $order->customer_id ? $order->customer->email : $order->guest_email,
                'billing_phone' => $order->billing_phone ?? $order->shipping_phone,
                'billing_address' => $order->billing_address_line1,
                'billing_city' => $order->billing_city,
                'billing_country' => $order->billing_country,
                'billing_postal_code' => $order->billing_postal_code,

                // Request metadata
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => 'desktop', // Admin panel is typically desktop
                'notes' => 'Order created by admin (user ID: ' . auth()->id() . ')',

                // Timestamps
                'initiated_at' => now(),
            ];

            // ✅ PAYMENT METHOD SPECIFIC HANDLING
            if ($order->payment_method === 'cod') {
                // COD Transaction
                $transactionData['payment_gateway'] = 'manual';
                $transactionData['status_key_code'] = 'TRANSACTION_PENDING';
                $transactionData['gateway_status'] = 'pending_payment';
                $transactionData['notes'] = 'Cash on Delivery - Payment will be collected upon delivery (Admin created)';

            } elseif ($order->payment_method === 'bank_transfer') {
                // Bank Transfer
                $transactionData['payment_gateway'] = 'manual';
                $transactionData['status_key_code'] = 'TRANSACTION_PENDING';
                $transactionData['gateway_status'] = 'awaiting_bank_transfer';
                $transactionData['notes'] = 'Bank Transfer - Awaiting payment confirmation (Admin created)';

            } else {
                // Online Payment Transaction
                $transactionData['payment_gateway'] = $order->payment_gateway ?? 'stripe';
                $transactionData['status_key_code'] = 'TRANSACTION_PENDING';
                $transactionData['gateway_status'] = 'awaiting_payment';
                $transactionData['notes'] = 'Online payment - Created by admin (Admin created)';
            }

            $transaction = Transaction::create($transactionData);
            //
            if ($order->payment_method !== 'cod') {
                $this->notificationService->notify('payment_pending', [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'order_number' => $order->order_number,
                    'amount' => $order->currency . ' ' . number_format($transaction->amount, 2),
                    'payment_method' => ucfirst($transaction->payment_method),
                    'payment_gateway' => $transaction->payment_gateway,
                    'customer_name' => $order->getCustomerName(),
                    'customer_email' => $order->getCustomerEmail(),
                ]);
            }

            \Log::info('✅ Transaction created for admin order', [
                'order' => $order->order_number,
                'transaction' => $transaction->transaction_number,
                'payment_method' => $order->payment_method,
                'amount' => $order->total_amount,
            ]);

            return $transaction;

        } catch (\Exception $e) {
            \Log::error('❌ Failed to create transaction for admin order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
