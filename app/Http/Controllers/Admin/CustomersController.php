<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Services\CustomerStatsService;
use App\Jobs\SyncCustomerStats;
// MODELS
use App\Models\Customer;
use App\Models\Order;
use App\Models\SystemStatus;
use Carbon\Carbon;

class CustomersController extends Controller
{
    protected CustomerStatsService $statsService;

    public function __construct(CustomerStatsService $statsService)
    {
        $this->statsService = $statsService;
    }
    /**
     * Display customers listing page
     */
    public function index()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'new_customers_this_month' => Customer::thisMonth()->count(),
            'total_lifetime_value' => Customer::sum('total_spent'),
            'verified_customers' => Customer::verified()->count(),
            'newsletter_subscribers' => Customer::newsletterSubscribers()->count(),
        ];

        return view('admin.customers.index', compact('stats'));
    }

    /**
     * Get customers data for DataTables (AJAX)
     */
    public function getData(Request $request)
    {
        $query = Customer::with(['status'])
            ->withCount('orders');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === 'true');
        }

        if ($request->filled('newsletter')) {
            $query->where('is_newsletter_subscribed', $request->newsletter === 'true');
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('min_spent')) {
            $query->where('total_spent', '>=', (float)$request->min_spent);
        }
        if ($request->filled('max_spent')) {
            $query->where('total_spent', '<=', (float)$request->max_spent);
        }

        if ($request->filled('search')) {
            $searchTerm = is_array($request->search) ? $request->search['value'] : $request->search;
            if (!empty($searchTerm)) {
                $query->search($searchTerm);
            }
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($customer) {
                return '<input type="checkbox" class="customer-checkbox" value="' . $customer->id . '">';
            })
            ->addColumn('customer_info', function ($customer) {
                $html = '<div class="d-flex align-items-center">';
                $html .= '<div class="avatar-circle bg-primary text-white me-2">' . strtoupper(substr($customer->first_name, 0, 1)) . '</div>';
                $html .= '<div>';
                $html .= '<strong>' . htmlspecialchars($customer->getFullName()) . '</strong>';

                if ($customer->is_verified) {
                    $html .= ' <i class="bi bi-check-circle-fill text-success" title="Verified"></i>';
                }

                $html .= '<br><small class="text-muted"><i class="bi bi-envelope"></i> ' . htmlspecialchars($customer->email) . '</small>';

                if ($customer->phone) {
                    $html .= '<br><small class="text-muted"><i class="bi bi-telephone"></i> ' . htmlspecialchars($customer->phone) . '</small>';
                }

                if ($customer->company_name) {
                    $html .= '<br><small class="text-muted"><i class="bi bi-building"></i> ' . htmlspecialchars($customer->company_name) . '</small>';
                }

                $html .= '</div></div>';
                return $html;
            })
            ->addColumn('customer_type', function ($customer) {
                return $customer->getTypeBadge();
            })
            ->addColumn('status_badge', function ($customer) {
                return $customer->getStatusBadge();
            })
            ->addColumn('orders_info', function ($customer) {
                $html = '<div>';
                $html .= '<span class="badge bg-primary">' . $customer->total_orders . ' orders</span><br>';
                $html .= '<small class="text-muted">Total: ' . store_currency_symbol() . number_format($customer->total_spent, 2) . '</small><br>';
                $html .= '<small class="text-muted">Avg: ' . store_currency_symbol() . number_format($customer->average_order_value, 2) . '</small>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('segment', function ($customer) {
                $segment = $customer->getSegment();
                $badge = match($segment) {
                    'New' => 'bg-info',
                    'One-time Buyer' => 'bg-secondary',
                    'At Risk' => 'bg-danger',
                    'High Value' => 'bg-success',
                    'Loyal' => 'bg-primary',
                    default => 'bg-light text-dark',
                };
                return '<span class="badge ' . $badge . '">' . $segment . '</span>';
            })
            ->addColumn('last_order', function ($customer) {
                if ($customer->last_order_at) {
                    return '<span title="' . $customer->last_order_at->format('M d, Y H:i') . '">' .
                           $customer->last_order_at->diffForHumans() . '</span>';
                }
                return '<span class="text-muted">Never</span>';
            })
            ->addColumn('created_at_formatted', function ($customer) {
                return '<span title="' . $customer->created_at->format('M d, Y H:i') . '">' .
                       $customer->created_at->diffForHumans() . '</span>';
            })
            ->addColumn('actions', function ($customer) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';

                if (auth('admin')->user()->hasPermission('customers.read')) {
                    $actions .= '<a href="' . route('admin.customers.show', $customer->id) . '" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>';
                }

                if (auth('admin')->user()->hasPermission('customers.update')) {
                    $actions .= '<a href="' . route('admin.customers.edit', $customer->id) . '" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>';

                    // Sync button
                    $actions .= '<button type="button" class="btn btn-outline-info sync-customer-btn" data-id="' . $customer->id . '" title="Sync Orders"><i class="bi bi-arrow-repeat"></i></button>';

                    // Toggle status button
                    // if ($customer->isActive()) {
                    //     $actions .= '<button type="button" class="btn btn-outline-danger toggle-status-btn" data-id="' . $customer->id . '" data-action="block" title="Block"><i class="bi bi-lock"></i></button>';
                    // } else {
                    //     $actions .= '<button type="button" class="btn btn-outline-success toggle-status-btn" data-id="' . $customer->id . '" data-action="activate" title="Activate"><i class="bi bi-unlock"></i></button>';
                    // }
                }

                // if (auth('admin')->user()->hasPermission('customers.delete')) {
                //     $actions .= '<button type="button" class="btn btn-outline-danger delete-customer" data-id="' . $customer->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                // }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'customer_info', 'customer_type', 'status_badge', 'orders_info', 'segment', 'last_order', 'created_at_formatted', 'actions'])
            ->make(true);
    }

    /**
     * Show create customer form
     */
    public function create()
    {
        $statusList = SystemStatus::where('module', 'customers')->active()->ordered()->get();
        $countries = $this->getCountriesList();
        return view('admin.customers.create', compact('statusList','countries'));
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8|confirmed',
                'customer_type' => 'nullable|in:individual,business,wholesale,vip',
                'status_key_code' => 'nullable|string|exists:system_statuses,key_code',

                // Billing Address Fields
                'billing_address_line1' => 'nullable|string|max:255',
                'billing_address_line2' => 'nullable|string|max:255',
                'billing_city' => 'nullable|string|max:100',
                'billing_state' => 'nullable|string|max:100',
                'billing_postal_code' => 'nullable|string|max:20',
                'billing_country' => 'nullable|string|max:100',

                // Shipping Address Fields
                'shipping_address_line1' => 'nullable|string|max:255',
                'shipping_address_line2' => 'nullable|string|max:255',
                'shipping_city' => 'nullable|string|max:100',
                'shipping_state' => 'nullable|string|max:100',
                'shipping_postal_code' => 'nullable|string|max:20',
                'shipping_country' => 'nullable|string|max:100',

                // Business Information
                'tax_id' => 'nullable|string|max:50',
                'vat_number' => 'nullable|string|max:50',
                'business_registration' => 'nullable|string|max:100',

                // Preferences
                'preferred_language' => 'nullable|string|max:10',
                'preferred_currency' => 'nullable|string|max:3',

                // Subscriptions
                'is_newsletter_subscribed' => 'nullable|boolean',
                'is_sms_subscribed' => 'nullable|boolean',
                'is_verified' => 'nullable|boolean',

                // Additional Info
                'acquisition_source' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Set defaults for fields that might be missing
            if (empty($validated['customer_type'])) {
                $validated['customer_type'] = 'individual';
            }

            if (empty($validated['status_key_code'])) {
                $validated['status_key_code'] = 'CUSTOMER_ACTIVE';
            }

            // Set default currency from store
            if (empty($validated['preferred_currency'])) {
                $validated['preferred_currency'] = store_currency_symbol(); // e.g., 'USD', 'EUR', etc.
            }

            // Set verification status and timestamp
            if (!empty($validated['is_verified']) && $validated['is_verified']) {
                $validated['email_verified_at'] = now();
            }

            $customer = Customer::create($validated);

            DB::commit();

            // Check if request is from modal (AJAX) or regular form submission
            if ($request->ajax() || $request->wantsJson() || $request->input('request_from') === 'modal') {
                // Response for modal (AJAX request)
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created successfully',
                    'customer' => $customer->load(['status'])
                ]);
            }

            // Response for regular form submission (Create blade)
            return redirect()
                ->route('admin.customers.show', $customer->id)
                ->with('success', 'Customer created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // AJAX validation error response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            // Regular form validation error
            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            // AJAX error response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create customer: ' . $e->getMessage()
                ], 500);
            }

            // Regular form error
            return back()
                ->with('error', 'Failed to create customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display customer details
     */
    public function show($id)
    {
        $customer = Customer::with([
            'orders' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'orders.items',
            'referrals',
            'referrer',
        ])->findOrFail($id);

        // Get statistics
        $stats = [
            'total_orders' => $customer->total_orders,
            'total_spent' => $customer->total_spent,
            'average_order_value' => $customer->average_order_value,
            'pending_orders' => $customer->orders()->where('status_key_code', 'ORDER_PENDING')->count(),
            'completed_orders' => $customer->orders()->where('status_key_code', 'ORDER_DELIVERED')->count(),
            'cancelled_orders' => $customer->orders()->where('status_key_code', 'ORDER_CANCELLED')->count(),
            'lifetime_value' => $customer->getLifetimeValue(),
            'days_since_last_order' => $customer->getDaysSinceLastOrder(),
            'days_since_registration' => $customer->getDaysSinceRegistration(),
            'segment' => $customer->getSegment(),
            'login_count' => $customer->login_count,
            'referrals_count' => $customer->referrals->count(),
        ];

        // Get top purchased products
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_DELIVERED'])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_spent')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        // Get monthly spending trend (last 6 months)
        $monthlySpending = DB::table('orders')
            ->where('customer_id', $customer->id)
            ->whereIn('status_key_code', ['ORDER_COMPLETED', 'ORDER_DELIVERED'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.customers.show', compact('customer', 'stats', 'topProducts', 'monthlySpending'));
    }

    /**
     * Show edit customer form
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $statusList = SystemStatus::where('module', 'customers')->active()->ordered()->get();
        $countries = $this->getCountriesList();
        return view('admin.customers.edit', compact('customer', 'statusList','countries'));
    }

    /**
     * Update customer
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:customers,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8|confirmed',
                'customer_type' => 'required|in:individual,business,wholesale,vip',
                'status_key_code' => 'required|string|exists:system_statuses,key_code',

                // Billing Address Fields
                'billing_address_line1' => 'nullable|string|max:255',
                'billing_address_line2' => 'nullable|string|max:255',
                'billing_city' => 'nullable|string|max:100',
                'billing_state' => 'nullable|string|max:100',
                'billing_postal_code' => 'nullable|string|max:20',
                'billing_country' => 'nullable|string|max:100',

                // Shipping Address Fields
                'shipping_address_line1' => 'nullable|string|max:255',
                'shipping_address_line2' => 'nullable|string|max:255',
                'shipping_city' => 'nullable|string|max:100',
                'shipping_state' => 'nullable|string|max:100',
                'shipping_postal_code' => 'nullable|string|max:20',
                'shipping_country' => 'nullable|string|max:100',

                // Business Information
                'tax_id' => 'nullable|string|max:50',
                'vat_number' => 'nullable|string|max:50',
                'business_registration' => 'nullable|string|max:100',

                // Preferences
                'preferred_language' => 'nullable|string|max:10',
                'preferred_currency' => 'nullable|string|max:3',

                // Subscriptions
                'is_newsletter_subscribed' => 'boolean',
                'is_sms_subscribed' => 'boolean',
                'is_verified' => 'boolean',

                // Additional Info
                'acquisition_source' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
            ]);

            // Remove password if not provided
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            // Handle verification status
            if (isset($validated['is_verified'])) {
                if ($validated['is_verified'] && !$customer->is_verified) {
                    $validated['email_verified_at'] = now();
                } elseif (!$validated['is_verified']) {
                    $validated['email_verified_at'] = null;
                }
            }

            DB::beginTransaction();

            $customer->update($validated);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer updated successfully',
                    'redirect' => route('admin.customers.show', $customer->id)
                ]);
            }

            return redirect()
                ->route('admin.customers.show', $customer->id)
                ->with('success', 'Customer updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update customer: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', 'Failed to update customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete customer
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Check if customer has orders
            if ($customer->orders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete customer with existing orders. Please deactivate instead.'
                ], 400);
            }

            DB::beginTransaction();

            $customer->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle customer status (activate/block)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $action = $request->input('action'); // 'activate', 'block', 'deactivate'

            DB::beginTransaction();

            switch ($action) {
                case 'activate':
                    $customer->activate();
                    $message = 'Customer activated successfully';
                    break;
                case 'block':
                    $customer->block();
                    $message = 'Customer blocked successfully';
                    break;
                case 'deactivate':
                    $customer->deactivate();
                    $message = 'Customer deactivated successfully';
                    break;
                default:
                    throw new \Exception('Invalid action');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'status_badge' => $customer->getStatusBadge()
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
     * Sync customer order statistics
     * This will recalculate: total_orders, total_spent, average_order_value,
     * first_order_at, last_order_at, and set preferred_currency
     */
    public function syncOrderStats($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            DB::beginTransaction();

            // Get completed and delivered orders only
            $orders = $customer->orders()
                ->whereIn('status_key_code', ['ORDER_CONFIRMED', 'ORDER_DELIVERED'])
                ->get();

            $totalOrders = $orders->count();
            $totalSpent = $orders->sum('total_amount');
            $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

            $firstOrder = $orders->sortBy('created_at')->first();
            $lastOrder = $orders->sortByDesc('created_at')->first();

            // Update customer statistics
            $updateData = [
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'average_order_value' => $averageOrderValue,
                'first_order_at' => $firstOrder ? $firstOrder->created_at : null,
                'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
            ];

            // Set preferred currency from store if not already set
            if (empty($customer->preferred_currency)) {
                $updateData['preferred_currency'] = store_currency_symbol();
            }

            $customer->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer statistics synchronized successfully',
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_spent' => store_currency_symbol() . number_format($totalSpent, 2),
                    'average_order_value' => store_currency_symbol() . number_format($averageOrderValue, 2),
                    'first_order_at' => $firstOrder ? $firstOrder->created_at->format('M d, Y') : 'N/A',
                    'last_order_at' => $lastOrder ? $lastOrder->created_at->format('M d, Y') : 'N/A',
                    'segment' => $customer->fresh()->getSegment(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync customer statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkSyncStats(Request $request)
    {
        try {
            $customerIds = $request->input('customer_ids', []);
            $syncAll = $request->input('sync_all', false);

            // Option 1: Process immediately (for small batches)
            if (!$syncAll && count($customerIds) <= 50) {
                $result = $this->statsService->syncMultipleCustomers($customerIds);

                $message = "Successfully synchronized {$result['success']} customer(s)";
                if ($result['failed'] > 0) {
                    $message .= " ({$result['failed']} failed)";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $result,
                ]);
            }

            // Option 2: Queue for background processing (for large batches)
            if ($syncAll) {
                SyncCustomerStats::dispatch(); // Sync all customers
                $message = 'All customer statistics sync queued successfully';
            } else {
                SyncCustomerStats::dispatch($customerIds);
                $message = count($customerIds) . ' customer statistics sync queued successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'queued' => true,
                'count' => $syncAll ? 'all' : count($customerIds),
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk sync customer statistics failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk sync customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer statistics preview (without updating database)
     *
     * @param string $id Customer ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatsPreview($id)
    {
        try {
            $customer = \App\Models\Customer::findOrFail($id);
            $calculated = $this->statsService->calculateStats($customer);
            $needsSync = !$this->statsService->validateStats($customer);

            return response()->json([
                'success' => true,
                'current' => [
                    'total_orders' => $customer->total_orders,
                    'total_spent' => $customer->total_spent,
                    'average_order_value' => $customer->average_order_value,
                ],
                'calculated' => $calculated,
                'needs_sync' => $needsSync,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find customers with outdated statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findOutdatedStats()
    {
        try {
            $customers = $this->statsService->findCustomersNeedingSync(100);

            return response()->json([
                'success' => true,
                'count' => $customers->count(),
                'customers' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->getFullName(),
                        'email' => $customer->email,
                        'total_orders' => $customer->total_orders,
                        'last_sync' => $customer->updated_at->diffForHumans(),
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to find outdated stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display new customers (last 30 days)
     */
    public function newCustomers()
    {
        $stats = [
            'new_today' => Customer::today()->count(),
            'new_this_week' => Customer::thisWeek()->count(),
            'new_this_month' => Customer::thisMonth()->count(),
            'total_new' => Customer::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('admin.customers.new', compact('stats'));
    }

    /**
     * Display returning customers (2+ orders)
     */
    public function returningCustomers()
    {
        $stats = [
            'total_returning' => Customer::returning()->count(),
            'repeat_rate' => Customer::count() > 0 ? (Customer::returning()->count() / Customer::count() * 100) : 0,
        ];

        return view('admin.customers.returning', compact('stats'));
    }

    /**
     * Display top customers
     */
    public function topCustomers()
    {
        $topCustomers = Customer::active()
            ->orderByDesc('total_spent')
            ->limit(100)
            ->get();

        $stats = [
            'top_10_revenue' => Customer::orderByDesc('total_spent')->limit(10)->sum('total_spent'),
            'top_10_percentage' => Customer::sum('total_spent') > 0 ?
                (Customer::orderByDesc('total_spent')->limit(10)->sum('total_spent') / Customer::sum('total_spent') * 100) : 0,
        ];

        return view('admin.customers.top', compact('topCustomers', 'stats'));
    }

    /**
     * Display active customers
     */
    public function activeCustomers()
    {
        $stats = [
            'total_active' => Customer::active()->count(),
            'percentage' => Customer::count() > 0 ? (Customer::active()->count() / Customer::count() * 100) : 0,
        ];

        return view('admin.customers.active', compact('stats'));
    }

    /**
     * Display blocked customers
     */
    public function blockedCustomers()
    {
        $stats = [
            'total_blocked' => Customer::blocked()->count(),
        ];

        return view('admin.customers.blocked', compact('stats'));
    }

    /**
     * Display inactive customers
     */
    public function inactiveCustomers()
    {
        $stats = [
            'total_inactive' => Customer::inactive()->count(),
        ];

        return view('admin.customers.inactive', compact('stats'));
    }

    /**
     * Display customer reports
     */
    public function reports()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'new_this_month' => Customer::thisMonth()->count(),
            'total_lifetime_value' => Customer::sum('total_spent'),
            'avg_customer_value' => Customer::avg('total_spent'),
            'high_value_customers' => Customer::highValue(5000)->count(),
            'at_risk_customers' => Customer::atRisk(90)->count(),
        ];

        return view('admin.customers.reports', compact('stats'));
    }

    /**
     * Get customer reports data (AJAX)
     */
    public function reportsData(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $query = Customer::query();

            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ]);
            }

            $totalCustomers = $query->count();
            $activeCustomers = (clone $query)->where('status_key_code', 'CUSTOMER_ACTIVE')->count();
            $totalRevenue = Customer::sum('total_spent');

            // Customer growth trend
            $growthTrend = Customer::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Segment distribution
            $segments = Customer::all()->groupBy(function($customer) {
                return $customer->getSegment();
            })->map(function($group) {
                return $group->count();
            });

            // Customer type distribution
            $customerTypes = Customer::select('customer_type', DB::raw('COUNT(*) as count'))
                ->groupBy('customer_type')
                ->get()
                ->pluck('count', 'customer_type');

            return response()->json([
                'success' => true,
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'total_revenue' => $totalRevenue,
                'growth_trend' => [
                    'labels' => $growthTrend->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                    'data' => $growthTrend->pluck('count')->toArray(),
                ],
                'segments' => $segments,
                'customer_types' => $customerTypes,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load report data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export customers to CSV
     */
    public function export(Request $request)
    {
        $query = Customer::query();

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $customers = $query->get();

        $filename = 'customers_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Company',
                'Type',
                'Status',
                'Total Orders',
                'Total Spent',
                'Average Order Value',
                'First Order',
                'Last Order',
                'Segment',
                'Verified',
                'Newsletter',
                'Registered At'
            ]);

            // Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->first_name,
                    $customer->last_name,
                    $customer->email,
                    $customer->phone,
                    $customer->company_name,
                    $customer->getCustomerTypeLabel(),
                    $customer->status_key_code,
                    $customer->total_orders,
                    $customer->total_spent,
                    $customer->average_order_value,
                    $customer->first_order_at ? $customer->first_order_at->format('Y-m-d') : 'N/A',
                    $customer->last_order_at ? $customer->last_order_at->format('Y-m-d') : 'N/A',
                    $customer->getSegment(),
                    $customer->is_verified ? 'Yes' : 'No',
                    $customer->is_newsletter_subscribed ? 'Yes' : 'No',
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk actions handler
     */
    public function bulkAction(Request $request)
    {
        try {
            $action = $request->input('action');
            $customerIds = $request->input('customer_ids', []);

            if (empty($customerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No customers selected'
                ], 400);
            }

            DB::beginTransaction();

            $count = 0;

            switch ($action) {
                case 'activate':
                    $count = Customer::whereIn('id', $customerIds)
                        ->update(['status_key_code' => 'CUSTOMER_ACTIVE']);
                    $message = "Successfully activated {$count} customer(s)";
                    break;

                case 'deactivate':
                    $count = Customer::whereIn('id', $customerIds)
                        ->update(['status_key_code' => 'CUSTOMER_INACTIVE']);
                    $message = "Successfully deactivated {$count} customer(s)";
                    break;

                case 'block':
                    $count = Customer::whereIn('id', $customerIds)
                        ->update(['status_key_code' => 'CUSTOMER_BLOCKED']);
                    $message = "Successfully blocked {$count} customer(s)";
                    break;

                case 'verify':
                    $count = Customer::whereIn('id', $customerIds)
                        ->update([
                            'is_verified' => true,
                            'email_verified_at' => now()
                        ]);
                    $message = "Successfully verified {$count} customer(s)";
                    break;

                case 'subscribe_newsletter':
                    $count = Customer::whereIn('id', $customerIds)
                        ->update(['is_newsletter_subscribed' => true]);
                    $message = "Successfully subscribed {$count} customer(s) to newsletter";
                    break;

                case 'unsubscribe_newsletter':
                    $count = Customer::whereIn('id', $customerIds)
                        ->update(['is_newsletter_subscribed' => false]);
                    $message = "Successfully unsubscribed {$count} customer(s) from newsletter";
                    break;

                case 'delete':
                    // Check if any selected customer has orders
                    $customersWithOrders = Customer::whereIn('id', $customerIds)
                        ->has('orders')
                        ->count();

                    if ($customersWithOrders > 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "{$customersWithOrders} customer(s) have existing orders and cannot be deleted"
                        ], 400);
                    }

                    $count = Customer::whereIn('id', $customerIds)->delete();
                    $message = "Successfully deleted {$count} customer(s)";
                    break;

                default:
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid bulk action'
                    ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer quick stats (AJAX)
     */
    public function getQuickStats($id)
    {
        try {
            $customer = Customer::with(['orders'])->findOrFail($id);

            $stats = [
                'total_orders' => $customer->total_orders,
                'total_spent' => store_currency_symbol() . number_format($customer->total_spent, 2),
                'average_order_value' => store_currency_symbol() . number_format($customer->average_order_value, 2),
                'pending_orders' => $customer->orders()->where('status_key_code', 'ORDER_PENDING')->count(),
                'completed_orders' => $customer->orders()->where('status_key_code', 'ORDER_COMPLETED')->count(),
                'lifetime_value' => store_currency_symbol() . number_format($customer->getLifetimeValue(), 2),
                'segment' => $customer->getSegment(),
                'days_since_last_order' => $customer->getDaysSinceLastOrder(),
                'is_at_risk' => $customer->isAtRisk(90),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load customer stats: ' . $e->getMessage()
            ], 500);
        }
    }

     /**
     * Get countries list
     */
    private function getCountriesList(): array
    {
        return [
            'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola',
            'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria',
            'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados',
            'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan',
            'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei',
            'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon',
            'Canada', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile',
            'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica',
            'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark',
            'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador',
            'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia',
            'Eswatini', 'Ethiopia', 'Fiji', 'Finland', 'France',
            'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana',
            'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau',
            'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland',
            'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland',
            'Israel', 'Italy', 'Ivory Coast', 'Jamaica', 'Japan',
            'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kosovo',
            'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon',
            'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania',
            'Luxembourg', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives',
            'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius',
            'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia',
            'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia',
            'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua',
            'Niger', 'Nigeria', 'North Korea', 'North Macedonia', 'Norway',
            'Oman', 'Pakistan', 'Palau', 'Palestine', 'Panama',
            'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland',
            'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda',
            'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines',
            'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal',
            'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia',
            'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Korea',
            'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname',
            'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan',
            'Tanzania', 'Thailand', 'Togo', 'Tonga', 'Trinidad and Tobago',
            'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda',
            'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay',
            'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam',
            'Yemen', 'Zambia', 'Zimbabwe',
        ];
    }
}
