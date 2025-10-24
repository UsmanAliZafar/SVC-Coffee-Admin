<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use App\Models\Transaction;
use App\Models\ProductsCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportsController extends Controller
{
    /**
     * Reports Dashboard - Main overview
     */
    public function index()
    {
        $dateRange = $this->getDefaultDateRange();

        $data = [
            'sales_summary' => $this->getSalesSummary($dateRange['start'], $dateRange['end']),
            'revenue_summary' => $this->getRevenueSummary($dateRange['start'], $dateRange['end']),
            'product_summary' => $this->getProductSummary($dateRange['start'], $dateRange['end']),
            'customer_summary' => $this->getCustomerSummary($dateRange['start'], $dateRange['end']),
            'inventory_summary' => $this->getInventorySummary(),
            'date_range' => $dateRange,
        ];

        return view('admin.reports.index', $data);
    }

    // ==================== SALES REPORTS ====================

    /**
     * Daily Sales Report
     */
    public function salesDaily(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $data = [
            'selected_date' => $selectedDate,
            'previous_date' => $selectedDate->copy()->subDay(),
            'next_date' => $selectedDate->copy()->addDay(),
            'sales_data' => $this->getDailySalesData($selectedDate),
            'hourly_sales' => $this->getHourlySales($selectedDate),
            'top_products_today' => $this->getTopProductsByDate($selectedDate),
            'comparison' => $this->getDailyComparison($selectedDate),
        ];

        return view('admin.reports.sales.daily', $data);
    }

    /**
     * Weekly Sales Report
     */
    public function salesWeekly(Request $request)
    {
        $week = $request->input('week', Carbon::now()->week);
        $year = $request->input('year', Carbon::now()->year);

        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();

        $data = [
            'week_number' => $week,
            'year' => $year,
            'start_date' => $startOfWeek,
            'end_date' => $endOfWeek,
            'sales_data' => $this->getWeeklySalesData($startOfWeek, $endOfWeek),
            'daily_breakdown' => $this->getDailyBreakdown($startOfWeek, $endOfWeek),
            'top_products_week' => $this->getTopProductsByDateRange($startOfWeek, $endOfWeek),
            'comparison' => $this->getWeeklyComparison($startOfWeek),
        ];

        return view('admin.reports.sales.weekly', $data);
    }

    /**
     * Monthly Sales Report
     */
    public function salesMonthly(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $data = [
            'month' => $month,
            'year' => $year,
            'month_name' => $startOfMonth->format('F'),
            'start_date' => $startOfMonth,
            'end_date' => $endOfMonth,
            'sales_data' => $this->getMonthlySalesData($startOfMonth, $endOfMonth),
            'daily_breakdown' => $this->getDailyBreakdown($startOfMonth, $endOfMonth),
            'weekly_breakdown' => $this->getWeeklyBreakdownForMonth($startOfMonth, $endOfMonth),
            'top_products_month' => $this->getTopProductsByDateRange($startOfMonth, $endOfMonth),
            'category_breakdown' => $this->getCategorySales($startOfMonth, $endOfMonth),
            'comparison' => $this->getMonthlyComparison($startOfMonth),
        ];

        return view('admin.reports.sales.monthly', $data);
    }

    /**
     * Yearly Sales Report
     */
    public function salesYearly(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
        $endOfYear = Carbon::create($year, 12, 31)->endOfYear();

        $data = [
            'year' => $year,
            'start_date' => $startOfYear,
            'end_date' => $endOfYear,
            'sales_data' => $this->getYearlySalesData($startOfYear, $endOfYear),
            'monthly_breakdown' => $this->getMonthlyBreakdownForYear($startOfYear, $endOfYear),
            'quarterly_breakdown' => $this->getQuarterlyBreakdown($startOfYear, $endOfYear),
            'top_products_year' => $this->getTopProductsByDateRange($startOfYear, $endOfYear, 20),
            'category_breakdown' => $this->getCategorySales($startOfYear, $endOfYear),
            'comparison' => $this->getYearlyComparison($startOfYear),
        ];

        return view('admin.reports.sales.yearly', $data);
    }

    /**
     * Custom Range Sales Report
     */
    public function salesCustomRange(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => $start->diffInDays($end) + 1,
            'sales_data' => $this->getCustomRangeSalesData($start, $end),
            'daily_breakdown' => $this->getDailyBreakdown($start, $end),
            'top_products' => $this->getTopProductsByDateRange($start, $end, 20),
            'category_breakdown' => $this->getCategorySales($start, $end),
            'customer_analysis' => $this->getCustomerAnalysis($start, $end),
        ];

        return view('admin.reports.sales.custom-range', $data);
    }

    // ==================== REVENUE REPORTS ====================

    /**
     * Revenue Overview
     */
    public function revenueIndex(Request $request)
    {
        $period = $request->input('period', 'month'); // day, week, month, year
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        $dateRange = $this->getDateRangeForPeriod($period, $date);

        $data = [
            'period' => $period,
            'date' => Carbon::parse($date),
            'date_range' => $dateRange,
            'revenue_overview' => $this->getRevenueOverview($dateRange['start'], $dateRange['end']),
            'revenue_trend' => $this->getRevenueTrend($dateRange['start'], $dateRange['end'], $period),
            'revenue_by_status' => $this->getRevenueByOrderStatus($dateRange['start'], $dateRange['end']),
            'payment_methods' => $this->getRevenueByPaymentMethod($dateRange['start'], $dateRange['end']),
            'top_revenue_products' => $this->getTopRevenueProducts($dateRange['start'], $dateRange['end']),
        ];

        return view('admin.reports.revenue.index', $data);
    }

    /**
     * Revenue by Category
     */
    public function revenueByCategory(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'category_revenue' => $this->getCategoryRevenue($start, $end),
            'category_trends' => $this->getCategoryRevenueTrends($start, $end),
            'subcategory_breakdown' => $this->getSubcategoryRevenue($start, $end),
        ];

        return view('admin.reports.revenue.by-category', $data);
    }

    /**
     * Revenue by Product
     */
    public function revenueByProduct(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $categoryId = $request->input('category_id');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $query = OrderItem::select(
                    'order_items.product_id',
                    'products.name as product_name',
                    'products.sku',
                    'products.price',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.total) as total_revenue'),
                    DB::raw('AVG(order_items.unit_price) as avg_price'),
                    DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
                )
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('order_items.product_id', 'products.name', 'products.sku', 'products.price');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $productRevenue = $query->orderBy('total_revenue', 'desc')->paginate(50);

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'category_id' => $categoryId,
            'categories' => ProductsCategories::active()->ordered()->get(),
            'product_revenue' => $productRevenue,
            'total_revenue' => $productRevenue->sum('total_revenue'),
            'total_quantity' => $productRevenue->sum('total_quantity'),
        ];

        return view('admin.reports.revenue.by-product', $data);
    }

    // ==================== PRODUCT REPORTS ====================

    /**
     * Top Selling Products
     */
    public function productsTopSelling(Request $request)
    {
        $period = $request->input('period', 'month');
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $limit = $request->input('limit', 50);

        $dateRange = $this->getDateRangeForPeriod($period, $date);

        $topProducts = $this->getTopProductsByDateRange(
            $dateRange['start'],
            $dateRange['end'],
            $limit
        );

        $data = [
            'period' => $period,
            'date_range' => $dateRange,
            'top_products' => $topProducts,
            'total_units_sold' => $topProducts->sum('total_sold'),
            'total_revenue' => $topProducts->sum('total_revenue'),
        ];

        return view('admin.reports.products.top-selling', $data);
    }

    /**
     * Products by Category
     */
    public function productsByCategory(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $categoryPerformance = ProductsCategories::select(
                'products_categories.id',
                'products_categories.title',
                DB::raw('COUNT(DISTINCT products.id) as total_products'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total), 0) as revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
            )
            ->leftJoin('products', 'products_categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) use ($start, $end) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$start, $end])
                     ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            })
            ->where('products_categories.status_key_code', 'CATEGORY_ACTIVE')
            ->groupBy('products_categories.id', 'products_categories.title')
            ->orderBy('revenue', 'desc')
            ->get();

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'category_performance' => $categoryPerformance,
            'total_revenue' => $categoryPerformance->sum('revenue'),
            'total_units_sold' => $categoryPerformance->sum('units_sold'),
        ];

        return view('admin.reports.products.by-category', $data);
    }

    /**
     * Product Performance Report
     */
    public function productsPerformance(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $categoryId = $request->input('category_id');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $query = Product::select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.stock_quantity',
                'products.low_stock_threshold',
                'products_categories.title as category_name',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total), 0) as revenue'),
                DB::raw('COALESCE(AVG(order_items.unit_price), 0) as avg_selling_price'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                DB::raw('COALESCE(SUM(order_items.total) / NULLIF(SUM(order_items.quantity), 0), 0) as revenue_per_unit')
            )
            ->leftJoin('products_categories', 'products.category_id', '=', 'products_categories.id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) use ($start, $end) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$start, $end])
                     ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            });

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $products = $query->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.stock_quantity',
                'products.low_stock_threshold',
                'products_categories.title'
            )
            ->orderBy('revenue', 'desc')
            ->paginate(50);

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'category_id' => $categoryId,
            'categories' => ProductsCategories::active()->ordered()->get(),
            'products' => $products,
            'summary' => [
                'total_products' => Product::count(),
                'products_sold' => $products->filter(fn($p) => $p->units_sold > 0)->count(),
                'products_not_sold' => $products->filter(fn($p) => $p->units_sold == 0)->count(),
                'total_revenue' => $products->sum('revenue'),
                'total_units_sold' => $products->sum('units_sold'),
            ],
        ];

        return view('admin.reports.products.performance', $data);
    }

    // ==================== INVENTORY REPORTS ====================

    /**
     * Inventory Overview
     */
    public function inventoryIndex()
    {
        $data = [
            'total_products' => Product::count(),
            'products_in_stock' => Product::where('stock_quantity', '>', 0)->where('track_inventory', true)->count(),
            'products_low_stock' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                          ->where('stock_quantity', '>', 0)
                                          ->where('track_inventory', true)
                                          ->count(),
            'products_out_of_stock' => Product::where('stock_quantity', '<=', 0)->where('track_inventory', true)->count(),
            'total_stock_value' => $this->calculateTotalStockValue(),
            'warehouse_summary' => $this->getWarehouseStockSummary(),
            'category_stock' => $this->getCategoryStockLevels(),
        ];

        return view('admin.reports.inventory.index', $data);
    }

    /**
     * Stock Levels Report
     */
    public function inventoryStockLevels(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $categoryId = $request->input('category_id');
        $status = $request->input('status'); // in_stock, low_stock, out_of_stock

        $query = Product::with(['category', 'warehouseStock'])
            ->where('track_inventory', true);

        if ($warehouseId) {
            $query->whereHas('warehouseStock', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status) {
            switch ($status) {
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                          ->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('stock_quantity', '>', DB::raw('low_stock_threshold'));
                    break;
            }
        }

        $products = $query->orderBy('stock_quantity', 'asc')->paginate(50);

        $data = [
            'products' => $products,
            'warehouse_id' => $warehouseId,
            'category_id' => $categoryId,
            'status' => $status,
            'categories' => ProductsCategories::active()->ordered()->get(),
            'warehouses' => \App\Models\Warehouse::active()->get(),
        ];

        return view('admin.reports.inventory.stock-levels', $data);
    }

    /**
     * Inventory Movement Report
     */
    public function inventoryMovement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');
        $movementType = $request->input('type');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $query = InventoryMovement::with(['product', 'warehouse'])
            ->whereBetween('created_at', [$start, $end]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($movementType) {
            $query->where('type', $movementType);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(100);

        // Movement summary
        $summary = InventoryMovement::select(
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as total_added'),
                DB::raw('SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as total_removed')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('type')
            ->get();

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'movements' => $movements,
            'summary' => $summary,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => $movementType,
            'warehouses' => \App\Models\Warehouse::active()->get(),
            'movement_types' => $this->getMovementTypes(),
        ];

        return view('admin.reports.inventory.movement', $data);
    }

    /**
     * Inventory Valuation Report
     */
    public function inventoryValuation(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $categoryId = $request->input('category_id');

        $query = Product::select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.cost_price',
                'products.stock_quantity',
                'products_categories.title as category_name',
                DB::raw('products.stock_quantity * products.price as retail_value'),
                DB::raw('products.stock_quantity * COALESCE(products.cost_price, 0) as cost_value'),
                DB::raw('(products.stock_quantity * products.price) - (products.stock_quantity * COALESCE(products.cost_price, 0)) as potential_profit')
            )
            ->leftJoin('products_categories', 'products.category_id', '=', 'products_categories.id')
            ->where('products.track_inventory', true)
            ->where('products.stock_quantity', '>', 0);

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $products = $query->orderBy('retail_value', 'desc')->get();

        $totalRetailValue = $products->sum('retail_value');
        $totalCostValue = $products->sum('cost_value');
        $totalPotentialProfit = $products->sum('potential_profit');

        // Group by category
        $categoryValuation = $products->groupBy('category_name')->map(function($items, $category) {
            return [
                'category' => $category,
                'product_count' => $items->count(),
                'total_units' => $items->sum('stock_quantity'),
                'retail_value' => $items->sum('retail_value'),
                'cost_value' => $items->sum('cost_value'),
                'potential_profit' => $items->sum('potential_profit'),
            ];
        })->values();

        $data = [
            'products' => $products,
            'category_valuation' => $categoryValuation,
            'total_retail_value' => $totalRetailValue,
            'total_cost_value' => $totalCostValue,
            'total_potential_profit' => $totalPotentialProfit,
            'total_units' => $products->sum('stock_quantity'),
            'category_id' => $categoryId,
            'warehouse_id' => $warehouseId,
            'categories' => ProductsCategories::active()->ordered()->get(),
            'warehouses' => \App\Models\Warehouse::active()->get(),
        ];

        return view('admin.reports.inventory.valuation', $data);
    }

    // ==================== CUSTOMER REPORTS ====================

    /**
     * Customer Analytics Overview
     */
    public function customersIndex(Request $request)
    {
        $period = $request->input('period', 'month');
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        $dateRange = $this->getDateRangeForPeriod($period, $date);

        $data = [
            'period' => $period,
            'date_range' => $dateRange,
            'total_customers' => Customer::count(),
            'new_customers' => $this->getNewCustomers($dateRange['start'], $dateRange['end']),
            'active_customers' => $this->getActiveCustomers($dateRange['start'], $dateRange['end']),
            'customer_retention' => $this->getCustomerRetentionRate($dateRange['start'], $dateRange['end']),
            'top_customers' => $this->getTopCustomers($dateRange['start'], $dateRange['end']),
            'customer_distribution' => $this->getCustomerDistribution(),
        ];

        return view('admin.reports.customers.index', $data);
    }

    /**
     * New vs Returning Customers
     */
    public function customersNewVsReturning(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get orders with customer info
        $ordersData = Order::select(
                'customer_id',
                'guest_email',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('MIN(created_at) as first_order_date')
            )
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('customer_id', 'guest_email')
            ->get();

        $newCustomers = $ordersData->filter(function($order) use ($start) {
            return Carbon::parse($order->first_order_date)->between($start, Carbon::now());
        });

        $returningCustomers = $ordersData->filter(function($order) use ($start) {
            return Carbon::parse($order->first_order_date)->lt($start);
        });

        $data = [
            'start_date' => $start,
            'end_date' => $end,
            'new_customers_count' => $newCustomers->count(),
            'returning_customers_count' => $returningCustomers->count(),
            'new_customers_revenue' => $newCustomers->sum('total_spent'),
            'returning_customers_revenue' => $returningCustomers->sum('total_spent'),
            'new_customers_avg_order' => $newCustomers->avg('total_spent'),
            'returning_customers_avg_order' => $returningCustomers->avg('total_spent'),
            'daily_breakdown' => $this->getNewVsReturningDaily($start, $end),
        ];

        return view('admin.reports.customers.new-vs-returning', $data);
    }

    /**
     * Customer Lifetime Value
     */
    public function customersLifetimeValue(Request $request)
    {
        $minOrders = $request->input('min_orders', 1);
        $limit = $request->input('limit', 100);

        $customers = Customer::select(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.created_at',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_amount) as lifetime_value'),
                DB::raw('AVG(orders.total_amount) as avg_order_value'),
                DB::raw('MAX(orders.created_at) as last_order_date'),
                DB::raw('DATEDIFF(MAX(orders.created_at), MIN(orders.created_at)) as customer_lifespan_days')
            )
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.created_at')
            ->having('total_orders', '>=', $minOrders)
            ->orderBy('lifetime_value', 'desc')
            ->limit($limit)
            ->get();

        $data = [
            'customers' => $customers,
            'min_orders' => $minOrders,
            'summary' => [
                'total_customers' => $customers->count(),
                'total_ltv' => $customers->sum('lifetime_value'),
                'avg_ltv' => $customers->avg('lifetime_value'),
                'avg_orders' => $customers->avg('total_orders'),
                'avg_order_value' => $customers->avg('avg_order_value'),
            ],
        ];

        return view('admin.reports.customers.lifetime-value', $data);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get default date range (last 30 days)
     */
    private function getDefaultDateRange(): array
    {
        return [
            'start' => Carbon::now()->subDays(30)->startOfDay(),
            'end' => Carbon::now()->endOfDay(),
        ];
    }

    /**
     * Get date range for period
     */
    private function getDateRangeForPeriod(string $period, string $date): array
    {
        $carbon = Carbon::parse($date);

        return match($period) {
            'day' => [
                'start' => $carbon->copy()->startOfDay(),
                'end' => $carbon->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $carbon->copy()->startOfWeek(),
                'end' => $carbon->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $carbon->copy()->startOfMonth(),
                'end' => $carbon->copy()->endOfMonth(),
            ],
            'year' => [
                'start' => $carbon->copy()->startOfYear(),
                'end' => $carbon->copy()->endOfYear(),
            ],
            default => $this->getDefaultDateRange(),
        };
    }

    /**
     * Get sales summary
     */
    private function getSalesSummary($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'total_items_sold' => OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
        ];
    }

    /**
     * Get revenue summary
     */
    private function getRevenueSummary($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'gross_revenue' => $orders->sum('subtotal'),
            'net_revenue' => $orders->sum('total_amount'),
            'tax_collected' => $orders->sum('tax_amount'),
            'shipping_revenue' => $orders->sum('shipping_amount'),
            'discounts_given' => $orders->sum('discount_amount'),
        ];
    }

    /**
     * Get product summary
     */
    private function getProductSummary($start, $end): array
    {
        $orderItems = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                               ->whereBetween('orders.created_at', [$start, $end])
                               ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'total_products_sold' => $orderItems->sum('quantity'),
            'unique_products_sold' => $orderItems->distinct('product_id')->count('product_id'),
            'avg_items_per_order' => $orderItems->avg('quantity') ?? 0,
        ];
    }

    /**
     * Get customer summary
     */
    private function getCustomerSummary($start, $end): array
    {
        $newCustomers = Customer::whereBetween('created_at', [$start, $end])->count();
        $activeCustomers = Order::whereBetween('created_at', [$start, $end])
                                ->distinct('customer_id')
                                ->count('customer_id');

        return [
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
            'total_customers' => Customer::count(),
        ];
    }

    /**
     * Get inventory summary
     */
    private function getInventorySummary(): array
    {
        return [
            'total_products' => Product::count(),
            'products_in_stock' => Product::where('stock_quantity', '>', 0)->where('track_inventory', true)->count(),
            'products_low_stock' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                          ->where('stock_quantity', '>', 0)
                                          ->where('track_inventory', true)
                                          ->count(),
            'products_out_of_stock' => Product::where('stock_quantity', '<=', 0)->where('track_inventory', true)->count(),
            'total_stock_value' => $this->calculateTotalStockValue(),
        ];
    }

    /**
     * Get daily sales data
     */
    private function getDailySalesData($date): array
    {
        $orders = Order::whereDate('created_at', $date)
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'items_sold' => OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
        ];
    }

    /**
     * Get hourly sales breakdown
     */
    private function getHourlySales($date): array
    {
        $sales = Order::select(
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->whereDate('created_at', $date)
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->get();

        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourData = $sales->firstWhere('hour', $i);
            $hourlyData[] = [
                'hour' => $i,
                'hour_label' => sprintf('%02d:00', $i),
                'order_count' => $hourData->order_count ?? 0,
                'revenue' => $hourData->revenue ?? 0,
            ];
        }

        return $hourlyData;
    }

    /**
     * Get top products by date
     */
    private function getTopProductsByDate($date, $limit = 10)
    {
        return Product::select(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'products.price',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.total) as total_revenue')
                )
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereDate('orders.created_at', $date)
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('products.id', 'products.name', 'products.sku', 'products.price')
                ->orderBy('total_sold', 'desc')
                ->limit($limit)
                ->get();
    }

    /**
     * Get top products by date range
     */
    private function getTopProductsByDateRange($start, $end, $limit = 10)
    {
        return Product::select(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'products.price',
                    'products.stock_quantity',
                    'products_categories.title as category_name',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.total) as total_revenue'),
                    DB::raw('AVG(order_items.unit_price) as avg_price'),
                    DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
                )
                ->leftJoin('products_categories', 'products.category_id', '=', 'products_categories.id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'products.price',
                    'products.stock_quantity',
                    'products_categories.title'
                )
                ->orderBy('total_sold', 'desc')
                ->limit($limit)
                ->get();
    }

    /**
     * Get daily comparison
     */
    private function getDailyComparison($date): array
    {
        $previousDate = $date->copy()->subDay();

        $current = $this->getDailySalesData($date);
        $previous = $this->getDailySalesData($previousDate);

        return [
            'revenue_change' => $this->calculatePercentageChange(
                $previous['total_revenue'],
                $current['total_revenue']
            ),
            'orders_change' => $this->calculatePercentageChange(
                $previous['total_orders'],
                $current['total_orders']
            ),
        ];
    }

    /**
     * Get weekly sales data
     */
    private function getWeeklySalesData($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'items_sold' => OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
            'avg_daily_revenue' => $orders->sum('total_amount') / 7,
        ];
    }

    /**
     * Get daily breakdown for a period
     */
    private function getDailyBreakdown($start, $end): array
    {
        $sales = Order::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('AVG(total_amount) as avg_order_value')
                )
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

        $period = CarbonPeriod::create($start, $end);
        $dailyData = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayData = $sales->firstWhere('date', $dateStr);

            $dailyData[] = [
                'date' => $dateStr,
                'date_label' => $date->format('M d, Y'),
                'day_name' => $date->format('l'),
                'order_count' => $dayData->order_count ?? 0,
                'revenue' => $dayData->revenue ?? 0,
                'avg_order_value' => $dayData->avg_order_value ?? 0,
            ];
        }

        return $dailyData;
    }

    /**
     * Get weekly comparison
     */
    private function getWeeklyComparison($startOfWeek): array
    {
        $previousWeekStart = $startOfWeek->copy()->subWeek();
        $previousWeekEnd = $startOfWeek->copy()->subDay();

        $currentStart = $startOfWeek;
        $currentEnd = $startOfWeek->copy()->endOfWeek();

        $current = $this->getWeeklySalesData($currentStart, $currentEnd);
        $previous = $this->getWeeklySalesData($previousWeekStart, $previousWeekEnd);

        return [
            'revenue_change' => $this->calculatePercentageChange(
                $previous['total_revenue'],
                $current['total_revenue']
            ),
            'orders_change' => $this->calculatePercentageChange(
                $previous['total_orders'],
                $current['total_orders']
            ),
        ];
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySalesData($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'items_sold' => OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
            'avg_daily_revenue' => $orders->sum('total_amount') / $start->daysInMonth,
        ];
    }

    /**
     * Get weekly breakdown for month
     */
    private function getWeeklyBreakdownForMonth($start, $end): array
    {
        $sales = Order::select(
                    DB::raw('WEEK(created_at, 1) as week_number'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(DB::raw('WEEK(created_at, 1)'), DB::raw('YEAR(created_at)'))
                ->orderBy('year', 'asc')
                ->orderBy('week_number', 'asc')
                ->get();

        return $sales->map(function($week) {
            return [
                'week' => $week->week_number,
                'year' => $week->year,
                'label' => "Week {$week->week_number}",
                'order_count' => $week->order_count,
                'revenue' => $week->revenue,
            ];
        })->toArray();
    }

    /**
     * Get monthly comparison
     */
    private function getMonthlyComparison($startOfMonth): array
    {
        $previousMonthStart = $startOfMonth->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $startOfMonth->copy()->subMonth()->endOfMonth();

        $currentStart = $startOfMonth;
        $currentEnd = $startOfMonth->copy()->endOfMonth();

        $current = $this->getMonthlySalesData($currentStart, $currentEnd);
        $previous = $this->getMonthlySalesData($previousMonthStart, $previousMonthEnd);

        return [
            'revenue_change' => $this->calculatePercentageChange(
                $previous['total_revenue'],
                $current['total_revenue']
            ),
            'orders_change' => $this->calculatePercentageChange(
                $previous['total_orders'],
                $current['total_orders']
            ),
        ];
    }

    /**
     * Get yearly sales data
     */
    private function getYearlySalesData($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'items_sold' => OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
            'avg_monthly_revenue' => $orders->sum('total_amount') / 12,
        ];
    }

    /**
     * Get monthly breakdown for year
     */
    private function getMonthlyBreakdownForYear($start, $end): array
    {
        $sales = Order::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('YEAR(created_at)'))
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $sales->firstWhere('month', $i);
            $monthlyData[] = [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->format('F'),
                'month_short' => Carbon::create()->month($i)->format('M'),
                'order_count' => $monthData->order_count ?? 0,
                'revenue' => $monthData->revenue ?? 0,
            ];
        }

        return $monthlyData;
    }

    /**
     * Get quarterly breakdown
     */
    private function getQuarterlyBreakdown($start, $end): array
    {
        $sales = Order::select(
                    DB::raw('QUARTER(created_at) as quarter'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(DB::raw('QUARTER(created_at)'), DB::raw('YEAR(created_at)'))
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();

        return $sales->map(function($quarter) {
            return [
                'quarter' => $quarter->quarter,
                'year' => $quarter->year,
                'label' => "Q{$quarter->quarter} {$quarter->year}",
                'order_count' => $quarter->order_count,
                'revenue' => $quarter->revenue,
            ];
        })->toArray();
    }

    /**
     * Get yearly comparison
     */
    private function getYearlyComparison($startOfYear): array
    {
        $previousYearStart = $startOfYear->copy()->subYear()->startOfYear();
        $previousYearEnd = $startOfYear->copy()->subYear()->endOfYear();

        $currentStart = $startOfYear;
        $currentEnd = $startOfYear->copy()->endOfYear();

        $current = $this->getYearlySalesData($currentStart, $currentEnd);
        $previous = $this->getYearlySalesData($previousYearStart, $previousYearEnd);

        return [
            'revenue_change' => $this->calculatePercentageChange(
                $previous['total_revenue'],
                $current['total_revenue']
            ),
            'orders_change' => $this->calculatePercentageChange(
                $previous['total_orders'],
                $current['total_orders']
            ),
        ];
    }

    /**
     * Get custom range sales data
     */
    private function getCustomRangeSalesData($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        $daysCount = $start->diffInDays($end) + 1;

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
            'items_sold' => OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
            'avg_daily_revenue' => $orders->sum('total_amount') / $daysCount,
            'days_count' => $daysCount,
        ];
    }

    /**
     * Get category sales
     */
    private function getCategorySales($start, $end): array
    {
        return ProductsCategories::select(
                    'products_categories.id',
                    'products_categories.title',
                    DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                    DB::raw('SUM(order_items.quantity) as units_sold'),
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->join('products', 'products_categories.id', '=', 'products.category_id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('products_categories.id', 'products_categories.title')
                ->orderBy('revenue', 'desc')
                ->get()
                ->map(function($category) {
                    return [
                        'category' => $category->title,
                        'order_count' => $category->order_count,
                        'units_sold' => $category->units_sold,
                        'revenue' => $category->revenue,
                    ];
                })
                ->toArray();
    }

    /**
     * Get customer analysis
     */
    private function getCustomerAnalysis($start, $end): array
    {
        $totalCustomers = Order::whereBetween('created_at', [$start, $end])
                               ->distinct('customer_id')
                               ->count('customer_id');

        $newCustomers = Customer::whereBetween('created_at', [$start, $end])->count();

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $totalCustomers - $newCustomers,
        ];
    }

    /**
     * Get revenue overview
     */
    private function getRevenueOverview($start, $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);

        return [
            'gross_revenue' => $orders->sum('subtotal'),
            'net_revenue' => $orders->sum('total_amount'),
            'total_tax' => $orders->sum('tax_amount'),
            'total_shipping' => $orders->sum('shipping_amount'),
            'total_discounts' => $orders->sum('discount_amount'),
            'avg_order_value' => $orders->avg('total_amount') ?? 0,
        ];
    }

    /**
     * Get revenue trend
     */
    private function getRevenueTrend($start, $end, $period): array
    {
        $groupBy = match($period) {
            'day' => DB::raw('HOUR(created_at)'),
            'week', 'month' => DB::raw('DATE(created_at)'),
            'year' => DB::raw('MONTH(created_at)'),
            default => DB::raw('DATE(created_at)'),
        };

        return Order::select(
                    $groupBy . ' as period',
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as order_count')
                )
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get()
                ->toArray();
    }

    /**
     * Get revenue by order status
     */
    private function getRevenueByOrderStatus($start, $end): array
    {
        return Order::select(
                    'status_key_code',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('status_key_code')
                ->orderBy('revenue', 'desc')
                ->get()
                ->map(function($status) {
                    return [
                        'status' => str_replace('ORDER_', '', $status->status_key_code),
                        'order_count' => $status->order_count,
                        'revenue' => $status->revenue,
                    ];
                })
                ->toArray();
    }

    /**
     * Get revenue by payment method
     */
    private function getRevenueByPaymentMethod($start, $end): array
    {
        return Transaction::select(
                    'payment_method',
                    DB::raw('COUNT(*) as transaction_count'),
                    DB::raw('SUM(amount) as revenue')
                )
                ->whereBetween('created_at', [$start, $end])
                ->where('transaction_type', 'payment')
                ->where('status_key_code', 'TRANSACTION_SUCCESS')
                ->groupBy('payment_method')
                ->orderBy('revenue', 'desc')
                ->get()
                ->toArray();
    }

    /**
     * Get top revenue products
     */
    private function getTopRevenueProducts($start, $end, $limit = 10)
    {
        return Product::select(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'products.price',
                    DB::raw('SUM(order_items.quantity) as units_sold'),
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('products.id', 'products.name', 'products.sku', 'products.price')
                ->orderBy('revenue', 'desc')
                ->limit($limit)
                ->get();
    }

    /**
     * Get category revenue
     */
    private function getCategoryRevenue($start, $end): array
    {
        return ProductsCategories::select(
                    'products_categories.id',
                    'products_categories.title',
                    DB::raw('SUM(order_items.total) as revenue'),
                    DB::raw('SUM(order_items.quantity) as units_sold'),
                    DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
                )
                ->join('products', 'products_categories.id', '=', 'products.category_id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('products_categories.id', 'products_categories.title')
                ->orderBy('revenue', 'desc')
                ->get()
                ->toArray();
    }

    /**
     * Get category revenue trends
     */
    private function getCategoryRevenueTrends($start, $end): array
    {
        return ProductsCategories::select(
                    'products_categories.title',
                    DB::raw('DATE(orders.created_at) as date'),
                    DB::raw('SUM(order_items.total) as daily_revenue')
                )
                ->join('products', 'products_categories.id', '=', 'products.category_id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('products_categories.title', DB::raw('DATE(orders.created_at)'))
                ->orderBy('date', 'asc')
                ->get()
                ->groupBy('title')
                ->toArray();
    }

    /**
     * Get subcategory revenue
     */
    private function getSubcategoryRevenue($start, $end): array
    {
        // If you have parent-child category relationships
        return ProductsCategories::select(
                    'parent.title as parent_category',
                    'products_categories.title as subcategory',
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->leftJoin('products_categories as parent', 'products_categories.parent_id', '=', 'parent.id')
                ->join('products', 'products_categories.id', '=', 'products.category_id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->whereNotNull('products_categories.parent_id')
                ->groupBy('parent.title', 'products_categories.title')
                ->orderBy('revenue', 'desc')
                ->get()
                ->toArray();
    }

    /**
     * Calculate total stock value
     */
    private function calculateTotalStockValue(): float
    {
        return Product::where('track_inventory', true)
                     ->where('stock_quantity', '>', 0)
                     ->selectRaw('SUM(stock_quantity * price) as total_value')
                     ->value('total_value') ?? 0;
    }

    /**
     * Get warehouse stock summary
     */
    private function getWarehouseStockSummary(): array
    {
        return \App\Models\Warehouse::select(
                    'warehouses.id',
                    'warehouses.name',
                    DB::raw('SUM(product_warehouse_stock.quantity) as total_stock'),
                    DB::raw('SUM(product_warehouse_stock.reserved_quantity) as reserved_stock'),
                    DB::raw('SUM(product_warehouse_stock.available_quantity) as available_stock'),
                    DB::raw('COUNT(DISTINCT product_warehouse_stock.product_id) as product_count')
                )
                ->leftJoin('product_warehouse_stock', 'warehouses.id', '=', 'product_warehouse_stock.warehouse_id')
                ->where('warehouses.is_active', true)
                ->groupBy('warehouses.id', 'warehouses.name')
                ->get()
                ->toArray();
    }

    /**
     * Get category stock levels
     */
    private function getCategoryStockLevels(): array
    {
        return ProductsCategories::select(
                    'products_categories.title',
                    DB::raw('COUNT(products.id) as product_count'),
                    DB::raw('SUM(products.stock_quantity) as total_stock'),
                    DB::raw('SUM(CASE WHEN products.stock_quantity <= products.low_stock_threshold THEN 1 ELSE 0 END) as low_stock_count'),
                    DB::raw('SUM(CASE WHEN products.stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count')
                )
                ->join('products', 'products_categories.id', '=', 'products.category_id')
                ->where('products.track_inventory', true)
                ->groupBy('products_categories.title')
                ->orderBy('total_stock', 'desc')
                ->get()
                ->toArray();
    }

    /**
     * Get movement types for filter
     */
    private function getMovementTypes(): array
    {
        return [
            'adjustment' => 'Manual Adjustment',
            'purchase' => 'Purchase Order',
            'sale' => 'Order Fulfilled',
            'return' => 'Customer Return',
            'transfer' => 'Warehouse Transfer',
            'sync' => 'Warehouse Sync',
            'damaged' => 'Damaged/Defective',
            'lost' => 'Lost/Missing',
            'found' => 'Found Inventory',
        ];
    }

    /**
     * Get new customers
     */
    private function getNewCustomers($start, $end): int
    {
        return Customer::whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * Get active customers
     */
    private function getActiveCustomers($start, $end): int
    {
        return Order::whereBetween('created_at', [$start, $end])
                   ->distinct('customer_id')
                   ->count('customer_id');
    }

    /**
     * Get customer retention rate
     */
    private function getCustomerRetentionRate($start, $end): float
    {
        $previousPeriodStart = $start->copy()->sub($start->diff($end));
        $previousPeriodEnd = $start->copy()->subDay();

        $previousCustomers = Order::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
                                  ->distinct('customer_id')
                                  ->pluck('customer_id');

        $returningCustomers = Order::whereBetween('created_at', [$start, $end])
                                   ->whereIn('customer_id', $previousCustomers)
                                   ->distinct('customer_id')
                                   ->count('customer_id');

        return $previousCustomers->count() > 0
            ? ($returningCustomers / $previousCustomers->count()) * 100
            : 0;
    }

    /**
     * Get top customers
     */
    private function getTopCustomers($start, $end, $limit = 10)
    {
        return Customer::select(
                    'customers.id',
                    'customers.name',
                    'customers.email',
                    DB::raw('COUNT(orders.id) as order_count'),
                    DB::raw('SUM(orders.total_amount) as total_spent'),
                    DB::raw('AVG(orders.total_amount) as avg_order_value')
                )
                ->join('orders', 'customers.id', '=', 'orders.customer_id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy('customers.id', 'customers.name', 'customers.email')
                ->orderBy('total_spent', 'desc')
                ->limit($limit)
                ->get();
    }

    /**
     * Get customer distribution
     */
    private function getCustomerDistribution(): array
    {
        $distribution = Customer::select(
                DB::raw('CASE
                    WHEN orders_count = 0 THEN "No Orders"
                    WHEN orders_count = 1 THEN "1 Order"
                    WHEN orders_count BETWEEN 2 AND 5 THEN "2-5 Orders"
                    WHEN orders_count BETWEEN 6 AND 10 THEN "6-10 Orders"
                    ELSE "10+ Orders"
                END as segment'),
                DB::raw('COUNT(*) as customer_count')
            )
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->groupBy('segment')
            ->get();

        return $distribution->pluck('customer_count', 'segment')->toArray();
    }

    /**
     * Get new vs returning daily breakdown
     */
    private function getNewVsReturningDaily($start, $end): array
    {
        $period = CarbonPeriod::create($start, $end);
        $data = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');

            $orders = Order::whereDate('created_at', $dateStr)
                          ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                          ->get();

            $newOrders = $orders->filter(function($order) use ($dateStr) {
                $firstOrder = Order::where('customer_id', $order->customer_id)
                                  ->orderBy('created_at', 'asc')
                                  ->first();
                return $firstOrder && Carbon::parse($firstOrder->created_at)->format('Y-m-d') === $dateStr;
            });

            $returningOrders = $orders->diff($newOrders);

            $data[] = [
                'date' => $dateStr,
                'date_label' => $date->format('M d'),
                'new_customers' => $newOrders->unique('customer_id')->count(),
                'returning_customers' => $returningOrders->unique('customer_id')->count(),
                'new_revenue' => $newOrders->sum('total_amount'),
                'returning_revenue' => $returningOrders->sum('total_amount'),
            ];
        }

        return $data;
    }

    /**
     * Calculate percentage change
     */
    private function calculatePercentageChange($oldValue, $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return (($newValue - $oldValue) / $oldValue) * 100;
    }

    // ==================== EXPORT METHODS ====================

    /**
     * Export report to PDF
     */
    public function exportPdf($reportType, Request $request)
    {
        // Implementation for PDF export
        // Use libraries like DomPDF or TCPDF
    }

    /**
     * Export report to Excel
     */
    public function exportExcel($reportType, Request $request)
    {
        // Implementation for Excel export
        // Use Laravel Excel package
    }

    /**
     * Export report to CSV
     */
    public function exportCsv($reportType, Request $request)
    {
        // Implementation for CSV export
    }
}
