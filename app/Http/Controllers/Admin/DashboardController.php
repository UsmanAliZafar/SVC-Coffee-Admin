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
use App\Models\ProductVariant;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with comprehensive statistics
     */
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        $period = $request->input('period', 'week'); // Default to week
        // dd($startDate);
        // Get dashboard statistics with period filter
        $stats = $this->getGeneralStats($period);
        $orderStats = $this->getOrderStats($period);
        $inventoryStats = $this->getInventoryStats();
        $revenueStats = $this->getRevenueStats($period);

        // Recent activities
        $recentOrders = $this->getRecentOrders();
        $lowStockProducts = $this->getLowStockProducts();
        $stockAlerts = $this->getStockAlerts();
        $recentInventoryMovements = $this->getRecentInventoryMovements();

        // Chart data with period
        $salesChartData = $this->getSalesChartData($period);
        $topProducts = $this->getTopProducts($period);
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
            'categoryBreakdown',
            'period'
        ));
    }

    /**
     * Get general statistics with period filter
     */
    private function getGeneralStats(string $period = 'week'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            // Overall stats (no period filter - these are totals)
            'total_products' => Product::count(),
            'active_products' => Product::where('status_key_code', 'PRODUCT_ACTIVE')->count(),
            'total_customers' => Customer::count(),
            'active_admins' => AdminUser::where('is_active', true)->count(),
            'total_warehouses' => Warehouse::where('is_active', true)->count(),

            // Period-specific stats (filtered by date)
            'total_orders' => Order::count(), // All time
            'period_orders' => Order::where('created_at', '>=', $startDate)->count(), // Period filtered
            'period_new_products' => Product::where('created_at', '>=', $startDate)->count(),
            'period_new_customers' => Customer::where('created_at', '>=', $startDate)->count(),
        ];
    }

    /**
     * Get order statistics
     */
    private function getOrderStats(string $period = 'week'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'pending_orders' => Order::where('status_key_code', 'ORDER_PENDING')->count(),
            'processing_orders' => Order::where('status_key_code', 'ORDER_PROCESSING')->count(),
            'shipped_orders' => Order::where('status_key_code', 'ORDER_SHIPPED')->count(),
            'delivered_orders' => Order::where('status_key_code', 'ORDER_DELIVERED')->count(),
            'cancelled_orders' => Order::where('status_key_code', 'ORDER_CANCELLED')->count(),
            'today_orders' => Order::whereDate('created_at', Carbon::today())->count(),
            'week_orders' => Order::where('created_at', '>=', $startDate)->count(),
            'month_orders' => Order::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'orders_require_action' => Order::whereIn('status_key_code', ['ORDER_PENDING', 'ORDER_CONFIRMED'])
                                            ->where('payment_status_key_code', 'PAYMENT_PAID')
                                            ->count(),
        ];
    }

    /**
     * Get inventory statistics (including variants)
     */
    private function getInventoryStats(): array
    {
        // Total stock from warehouse (includes both products and variants)
        $totalStock = ProductWarehouseStock::sum('quantity');
        $reservedStock = ProductWarehouseStock::sum('reserved_quantity');
        $availableStock = ProductWarehouseStock::sum('available_quantity');

        // Low stock products (simple products without variants)
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                    ->where('stock_quantity', '>', 0)
                                    ->where('track_inventory', true)
                                    ->where('has_variants', false) // Only simple products
                                    ->count();

        // Low stock variants
        $lowStockVariants = ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                        ->where('stock_quantity', '>', 0)
                                        ->count();

        // Total low stock items (products + variants)
        $lowStockCount = $lowStockProducts + $lowStockVariants;

        // Out of stock products (simple products without variants)
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
                                    ->where('track_inventory', true)
                                    ->where('has_variants', false) // Only simple products
                                    ->count();

        // Out of stock variants
        $outOfStockVariants = ProductVariant::where('stock_quantity', '<=', 0)->count();

        // Total out of stock items (products + variants)
        $outOfStockCount = $outOfStockProducts + $outOfStockVariants;

        // Active stock alerts
        $activeAlerts = StockAlert::where('is_resolved', false)->count();

        // Today's inventory movements
        $todayMovements = InventoryMovement::whereDate('created_at', Carbon::today())->count();

        return [
            'total_stock_value' => $this->getTotalStockValue(),
            'total_stock_units' => $totalStock,
            'reserved_stock' => $reservedStock,
            'available_stock' => $availableStock,

            // Combined counts
            'low_stock_items' => $lowStockCount,
            'out_of_stock_items' => $outOfStockCount,

            // Breakdown
            'low_stock_products' => $lowStockProducts,
            'low_stock_variants' => $lowStockVariants,
            'out_of_stock_products' => $outOfStockProducts,
            'out_of_stock_variants' => $outOfStockVariants,

            'active_stock_alerts' => $activeAlerts,
            'today_movements' => $todayMovements,
        ];
    }

    /**
     * Get revenue statistics with period filter
     */
    private function getRevenueStats(string $period = 'week'): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Get period start date
        $startDate = $this->getStartDate($period);

        // Period revenue (based on selected filter)
        $periodRevenue = Order::where('created_at', '>=', $startDate)
                            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                            ->sum('total_amount');

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

        // Average order value (for selected period)
        $averageOrderValue = Order::where('created_at', '>=', $startDate)
                                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                                ->avg('total_amount') ?? 0;

        // Pending payments (all time - no filter)
        $pendingPayments = Order::where('payment_status_key_code', 'PAYMENT_PENDING')
                                ->whereNotIn('status_key_code', ['ORDER_CANCELLED'])
                                ->sum('total_amount');

        // Total refunded amount (for selected period)
        $totalRefunded = Transaction::where('transaction_type', 'refund')
                                ->where('status_key_code', 'TRANSACTION_SUCCESS')
                                ->where('created_at', '>=', $startDate)
                                ->sum('amount');

        // Calculate percentage change based on period
        $revenueChangePercentage = $this->calculateRevenueChange($periodRevenue, $period);

        return [
            'today_revenue' => $todayRevenue,
            'week_revenue' => $weekRevenue,
            'month_revenue' => $monthRevenue,
            'year_revenue' => $yearRevenue,
            'total_revenue' => $totalRevenue,
            'period_revenue' => $periodRevenue, // Main revenue for selected period
            'average_order_value' => $averageOrderValue, // Now filtered by period
            'pending_payments' => $pendingPayments,
            'total_refunded' => $totalRefunded, // Now filtered by period
            'revenue_change_percentage' => $revenueChangePercentage,
            'period_label' => ucfirst($period), // Add label for display
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
    private function getSalesChartData(string $period = 'week'): array
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 7
        };
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
    private function getTopProducts(string $period = 'week')
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 7
        };
        //
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
                      ->where('orders.created_at', '>=', Carbon::now()->subDays($days))
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
     * Calculate total stock value (including variants)
     */
    private function getTotalStockValue(): float
    {
        // Simple products value (without variants)
        $productsValue = ProductWarehouseStock::whereNull('variant_id')
                            ->join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
                            ->selectRaw('SUM(product_warehouse_stock.quantity * products.price) as total_value')
                            ->value('total_value') ?? 0;

        // Variants value
        $variantsValue = ProductWarehouseStock::whereNotNull('variant_id')
                            ->join('product_variants', 'product_warehouse_stock.variant_id', '=', 'product_variants.id')
                            ->selectRaw('SUM(product_warehouse_stock.quantity * product_variants.price) as total_value')
                            ->value('total_value') ?? 0;

        return $productsValue + $variantsValue;
    }

   /**
     * Calculate revenue change percentage based on period
     */
    private function calculateRevenueChange(float $currentRevenue, string $period = 'week'): float
    {
        // Determine previous period dates
        $previousStart = match($period) {
            'week' => Carbon::now()->subWeek()->startOfWeek(),
            'month' => Carbon::now()->subMonth()->startOfMonth(),
            'year' => Carbon::now()->subYear()->startOfYear(),
            default => Carbon::now()->subWeek()->startOfWeek()
        };

        $previousEnd = match($period) {
            'week' => Carbon::now()->subWeek()->endOfWeek(),
            'month' => Carbon::now()->subMonth()->endOfMonth(),
            'year' => Carbon::now()->subYear()->endOfYear(),
            default => Carbon::now()->subWeek()->endOfWeek()
        };

        // Get previous period revenue
        $previousRevenue = Order::whereBetween('created_at', [$previousStart, $previousEnd])
                                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                                ->sum('total_amount');

        if ($previousRevenue == 0) {
            return $currentRevenue > 0 ? 100 : 0; // If no previous revenue, show 100% increase or 0%
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

    /**
     * Helper method to get start date based on period
     */
    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfWeek()
        };
    }
}
