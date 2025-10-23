<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
// MODELS
use App\Models\Customer;
use App\Models\Order;
use App\Models\SystemStatus;

class CustomersController extends Controller
{
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
                $html = '<div>';
                $html .= '<strong>' . htmlspecialchars($customer->getFullName()) . '</strong><br>';
                $html .= '<small class="text-muted"><i class="bi bi-envelope"></i> ' . htmlspecialchars($customer->email) . '</small>';
                if ($customer->phone) {
                    $html .= '<br><small class="text-muted"><i class="bi bi-telephone"></i> ' . htmlspecialchars($customer->phone) . '</small>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('customer_type', function ($customer) {
                return $customer->getTypeBadge();
            })
            ->addColumn('status_badge', function ($customer) {
                return $customer->getStatusBadge();
            })
            ->addColumn('orders_info', function ($customer) {
                $html = '<span class="badge bg-primary">' . $customer->total_orders . ' orders</span><br>';
                $html .= '<small class="text-muted">Total: $' . number_format($customer->total_spent, 2) . '</small>';
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
                    return $customer->last_order_at->format('M d, Y') . '<br><small class="text-muted">' . $customer->last_order_at->diffForHumans() . '</small>';
                }
                return '<span class="text-muted">Never</span>';
            })
            ->addColumn('created_at_formatted', function ($customer) {
                return $customer->created_at->format('M d, Y') . '<br><small class="text-muted">' . $customer->created_at->diffForHumans() . '</small>';
            })
            ->addColumn('actions', function ($customer) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';

                if (auth('admin')->user()->hasPermission('customers.read')) {
                    $actions .= '<a href="' . route('admin.customers.show', $customer->id) . '" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>';
                }

                if (auth('admin')->user()->hasPermission('customers.update')) {
                    $actions .= '<a href="' . route('admin.customers.edit', $customer->id) . '" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>';

                    // Toggle status button
                    if ($customer->isActive()) {
                        $actions .= '<button type="button" class="btn btn-outline-danger toggle-status-btn" data-id="' . $customer->id . '" data-action="block" title="Block"><i class="bi bi-lock"></i></button>';
                    } else {
                        $actions .= '<button type="button" class="btn btn-outline-success toggle-status-btn" data-id="' . $customer->id . '" data-action="activate" title="Activate"><i class="bi bi-unlock"></i></button>';
                    }
                }

                if (auth('admin')->user()->hasPermission('customers.delete')) {
                    $actions .= '<button type="button" class="btn btn-outline-danger delete-customer" data-id="' . $customer->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }

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

        return view('admin.customers.create', compact('statusList'));
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
                'password' => 'required|string|min:8|confirmed',
                'customer_type' => 'required|in:individual,business,wholesale,vip',
                'status_key_code' => 'required|string|exists:system_statuses,key_code',
                'billing_address_line1' => 'nullable|string|max:255',
                'billing_city' => 'nullable|string|max:100',
                'billing_state' => 'nullable|string|max:100',
                'billing_postal_code' => 'nullable|string|max:20',
                'billing_country' => 'nullable|string|max:100',
                'is_newsletter_subscribed' => 'boolean',
                'is_sms_subscribed' => 'boolean',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $customer = Customer::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'redirect' => route('admin.customers.show', $customer->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display customer details
     */
    public function show($id)
    {
        $customer = Customer::with([
            'orders' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            },
            'orders.items',
            'referrals',
            'referrer'
        ])->findOrFail($id);

        // Get statistics
        $stats = [
            'total_orders' => $customer->total_orders,
            'total_spent' => $customer->total_spent,
            'average_order_value' => $customer->average_order_value,
            'pending_orders' => $customer->orders()->where('status_key_code', 'ORDER_PENDING')->count(),
            'lifetime_value' => $customer->getLifetimeValue(),
            'days_since_last_order' => $customer->getDaysSinceLastOrder(),
            'segment' => $customer->getSegment(),
        ];

        // Get top purchased products
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_spent')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return view('admin.customers.show', compact('customer', 'stats', 'topProducts'));
    }

    /**
     * Show edit customer form
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $statusList = SystemStatus::where('module', 'customers')->active()->ordered()->get();

        return view('admin.customers.edit', compact('customer', 'statusList'));
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
                'billing_address_line1' => 'nullable|string|max:255',
                'billing_city' => 'nullable|string|max:100',
                'billing_state' => 'nullable|string|max:100',
                'billing_postal_code' => 'nullable|string|max:20',
                'billing_country' => 'nullable|string|max:100',
                'is_newsletter_subscribed' => 'boolean',
                'is_sms_subscribed' => 'boolean',
                'notes' => 'nullable|string',
            ]);

            // Remove password if not provided
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            DB::beginTransaction();

            $customer->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'redirect' => route('admin.customers.show', $customer->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage()
            ], 500);
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
            $activeCustomers = $query->where('status_key_code', 'CUSTOMER_ACTIVE')->count();
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

            return response()->json([
                'success' => true,
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'total_revenue' => $totalRevenue,
                'growth_trend' => [
                    'labels' => $growthTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray(),
                    'data' => $growthTrend->pluck('count')->toArray(),
                ],
                'segments' => $segments,
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

        $customers = $query->get();

        $filename = 'customers_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Type', 'Status',
                'Total Orders', 'Total Spent', 'Created At'
            ]);

            // Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->first_name,
                    $customer->last_name,
                    $customer->email,
                    $customer->phone,
                    $customer->customer_type,
                    $customer->status_key_code,
                    $customer->total_orders,
                    $customer->total_spent,
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
