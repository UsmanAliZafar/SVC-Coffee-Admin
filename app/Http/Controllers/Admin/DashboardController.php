<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Models\StockAlert;
use App\Models\Transaction;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with comprehensive statistics
     */
    public function index()
    {
        $user = auth('admin')->user();

        // Get dashboard statistics
        $stats = $this->getGeneralStats();
        $orderStats = $this->getOrderStats();
        $inventoryStats = $this->getInventoryStats();
        $revenueStats = $this->getRevenueStats();

        // Recent activities
        $recentOrders = $this->getRecentOrders();
        $lowStockProducts = $this->getLowStockProducts();
        $stockAlerts = $this->getStockAlerts();
        $recentInventoryMovements = $this->getRecentInventoryMovements();

        // Chart data
        $salesChartData = $this->getSalesChartData();
        $topProducts = $this->getTopProducts();
        $categoryBreakdown = $this->getCategoryBreakdown();

        return view('admin.dashboard', compact(
            'user',
            'stats',
            'orderStats',
            'inventoryStats',
            'revenueStats',
            'recentOrders',
            'lowStockProducts',
            'stockAlerts',
            'recentInventoryMovements',
            'salesChartData',
            'topProducts',
            'categoryBreakdown'
        ));
    }

    /**
     * Get general statistics
     */
    private function getGeneralStats(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('status_key_code', 'PRODUCT_ACTIVE')->count(),
            'total_orders' => Order::count(),
            'total_customers' => Customer::count(),
            'active_admins' => AdminUser::where('is_active', true)->count(),
            'total_warehouses' => Warehouse::where('is_active', true)->count(),
        ];
    }

    /**
     * Get order statistics
     */
    private function getOrderStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'pending_orders' => Order::where('status_key_code', 'ORDER_PENDING')->count(),
            'processing_orders' => Order::where('status_key_code', 'ORDER_PROCESSING')->count(),
            'shipped_orders' => Order::where('status_key_code', 'ORDER_SHIPPED')->count(),
            'delivered_orders' => Order::where('status_key_code', 'ORDER_DELIVERED')->count(),
            'cancelled_orders' => Order::where('status_key_code', 'ORDER_CANCELLED')->count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'week_orders' => Order::where('created_at', '>=', $thisWeek)->count(),
            'month_orders' => Order::where('created_at', '>=', $thisMonth)->count(),
            'orders_require_action' => Order::whereIn('status_key_code', ['ORDER_PENDING', 'ORDER_CONFIRMED'])
                                            ->where('payment_status_key_code', 'PAYMENT_PAID')
                                            ->count(),
        ];
    }

    /**
     * Get inventory statistics
     */
    private function getInventoryStats(): array
    {
        $totalStock = ProductWarehouseStock::sum('quantity');
        $reservedStock = ProductWarehouseStock::sum('reserved_quantity');
        $availableStock = ProductWarehouseStock::sum('available_quantity');

        $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                ->where('stock_quantity', '>', 0)
                                ->where('track_inventory', true)
                                ->count();

        $outOfStockCount = Product::where('stock_quantity', '<=', 0)
                                  ->where('track_inventory', true)
                                  ->count();

        $activeAlerts = StockAlert::where('is_resolved', false)->count();

        $todayMovements = InventoryMovement::whereDate('created_at', Carbon::today())->count();

        return [
            'total_stock_value' => $this->getTotalStockValue(),
            'total_stock_units' => $totalStock,
            'reserved_stock' => $reservedStock,
            'available_stock' => $availableStock,
            'low_stock_products' => $lowStockCount,
            'out_of_stock_products' => $outOfStockCount,
            'active_stock_alerts' => $activeAlerts,
            'today_movements' => $todayMovements,
        ];
    }

    /**
     * Get revenue statistics
     */
    private function getRevenueStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Today's revenue
        $todayRevenue = Order::whereDate('created_at', $today)
                            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                            ->sum('total_amount');

        // This week's revenue
        $weekRevenue = Order::where('created_at', '>=', $thisWeek)
                           ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                           ->sum('total_amount');

        // This month's revenue
        $monthRevenue = Order::where('created_at', '>=', $thisMonth)
                            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                            ->sum('total_amount');

        // This year's revenue
        $yearRevenue = Order::where('created_at', '>=', $thisYear)
                           ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                           ->sum('total_amount');

        // Total revenue (all time)
        $totalRevenue = Order::whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                            ->sum('total_amount');

        // Average order value
        $averageOrderValue = Order::whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                                  ->avg('total_amount') ?? 0;

        // Pending payments
        $pendingPayments = Order::where('payment_status_key_code', 'PAYMENT_PENDING')
                                ->whereNotIn('status_key_code', ['ORDER_CANCELLED'])
                                ->sum('total_amount');

        // Total refunded amount
        $totalRefunded = Transaction::where('transaction_type', 'refund')
                                   ->where('status_key_code', 'TRANSACTION_SUCCESS')
                                   ->sum('amount');

        return [
            'today_revenue' => $todayRevenue,
            'week_revenue' => $weekRevenue,
            'month_revenue' => $monthRevenue,
            'year_revenue' => $yearRevenue,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'pending_payments' => $pendingPayments,
            'total_refunded' => $totalRefunded,
            // Calculate percentage changes (compared to previous period)
            'revenue_change_percentage' => $this->calculateRevenueChange($monthRevenue),
        ];
    }

    /**
     * Get recent orders (last 10)
     */
    private function getRecentOrders()
    {
        return Order::with(['customer', 'items', 'warehouse'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
    }

    /**
     * Get low stock products
     */
    private function getLowStockProducts()
    {
        return Product::with(['category', 'warehouseStock'])
                      ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                      ->where('stock_quantity', '>', 0)
                      ->where('track_inventory', true)
                      ->orderBy('stock_quantity', 'asc')
                      ->take(10)
                      ->get();
    }

    /**
     * Get active stock alerts
     */
    private function getStockAlerts()
    {
        return StockAlert::with(['product', 'warehouse'])
                         ->where('is_resolved', false)
                         ->orderBy('created_at', 'desc')
                         ->take(10)
                         ->get();
    }

    /**
     * Get recent inventory movements
     */
    private function getRecentInventoryMovements()
    {
        return InventoryMovement::with(['product', 'warehouse'])
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get();
    }

    /**
     * Get sales chart data for the last 30 days
     */
    private function getSalesChartData(): array
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days);

        $salesData = Order::select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as order_count'),
                        DB::raw('SUM(total_amount) as total_sales')
                    )
                    ->where('created_at', '>=', $startDate)
                    ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();

        $labels = [];
        $orderCounts = [];
        $salesAmounts = [];

        foreach ($salesData as $data) {
            $labels[] = Carbon::parse($data->date)->format('M d');
            $orderCounts[] = $data->order_count;
            $salesAmounts[] = number_format($data->total_sales, 2, '.', '');
        }

        return [
            'labels' => $labels,
            'order_counts' => $orderCounts,
            'sales_amounts' => $salesAmounts,
        ];
    }

    /**
     * Get top selling products
     */
    private function getTopProducts()
    {
        return Product::select(
                        'products.id',
                        'products.name',
                        'products.sku',
                        'products.price',
                        'products.stock_quantity',
                        'products.low_stock_threshold',
                        'products.category_id',
                        DB::raw('SUM(order_items.quantity) as total_sold')
                    )
                      ->join('order_items', 'products.id', '=', 'order_items.product_id')
                      ->join('orders', 'order_items.order_id', '=', 'orders.id')
                      ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED'])
                      ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
                      ->groupBy(
                          'products.id',
                          'products.name',
                          'products.sku',
                          'products.price',
                          'products.stock_quantity',
                          'products.low_stock_threshold',
                          'products.category_id'
                      )
                      ->with('category')
                      ->orderBy('total_sold', 'desc')
                      ->take(5)
                      ->get();
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown(): array
    {
        $breakdown = Product::select('products_categories.title as category_name', DB::raw('COUNT(*) as product_count'))
                            ->join('products_categories', 'products.category_id', '=', 'products_categories.id')
                            ->groupBy('products_categories.id', 'products_categories.title')
                            ->get();

        $labels = [];
        $counts = [];

        foreach ($breakdown as $item) {
            $labels[] = $item->category_name;
            $counts[] = $item->product_count;
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }

    /**
     * Calculate total stock value
     */
    private function getTotalStockValue(): float
    {
        return ProductWarehouseStock::join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
                                    ->selectRaw('SUM(product_warehouse_stock.quantity * products.price) as total_value')
                                    ->value('total_value') ?? 0;
    }

    /**
     * Calculate revenue change percentage
     */
    private function calculateRevenueChange(float $currentRevenue): float
    {
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $previousRevenue = Order::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
                                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                                ->sum('total_amount');

        if ($previousRevenue == 0) {
            return 0;
        }

        return (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
    }

    /**
     * Get quick stats for AJAX refresh
     */
    public function getQuickStats(Request $request)
    {
        return response()->json([
            'pending_orders' => Order::where('status_key_code', 'ORDER_PENDING')->count(),
            'low_stock_alerts' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                         ->where('stock_quantity', '>', 0)
                                         ->where('track_inventory', true)
                                         ->count(),
            'active_alerts' => StockAlert::where('is_resolved', false)->count(),
            'today_revenue' => Order::whereDate('created_at', Carbon::today())
                                   ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                                   ->sum('total_amount'),
        ]);
    }
}
