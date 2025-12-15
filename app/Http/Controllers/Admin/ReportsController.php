<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;
//
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\ProductWarehouseStock;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use App\Models\Transaction;
use App\Models\ProductsCategories;


class ReportsController extends Controller
{
    /**
     *  Now accepts date range from request
     */
    public function index(Request $request)
    {
        // ✅ Get date range from request or use defaults
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dateRange = [
                'start' => Carbon::parse($request->start_date)->startOfDay(),
                'end' => Carbon::parse($request->end_date)->endOfDay(),
            ];
        } else {
            $dateRange = $this->getDefaultDateRange();
        }

        $data = [
            'sales_summary' => $this->getSalesSummary($dateRange['start'], $dateRange['end']),
            'revenue_summary' => $this->getRevenueSummary($dateRange['start'], $dateRange['end']),
            'product_summary' => $this->getProductSummary($dateRange['start'], $dateRange['end']),
            'customer_summary' => $this->getCustomerSummary($dateRange['start'], $dateRange['end']),
            'inventory_summary' => $this->getInventorySummary(),
            'date_range' => $dateRange,
            'days_difference' => $this->calculateDaysDifference($dateRange['start'], $dateRange['end']),
        ];

        return view('admin.reports.index', $data);
    }

    /**
     *Calculate days between dates (inclusive, no floating point errors)
     */
    private function calculateDaysDifference($startDate, $endDate)
    {
        return $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;
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
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // Get category revenue data
        $category_revenue = Order::select(
                'products_categories.id',
                'products_categories.title',
                DB::raw('SUM(orders.total_amount) as revenue'),
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('products_categories', 'products.category_id', '=', 'products_categories.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->whereNotNull('products.category_id')
            ->groupBy('products_categories.id', 'products_categories.title')
            ->orderBy('revenue', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'revenue' => (float) $item->revenue,
                    'units_sold' => (int) $item->units_sold,
                    'order_count' => (int) $item->order_count,
                ];
            })
            ->toArray();

        // Calculate totals
        $total_revenue = collect($category_revenue)->sum('revenue');
        $total_units_sold = collect($category_revenue)->sum('units_sold');

        // Get category trends
        $category_trends = [];
        if (count($category_revenue) > 0) {
            foreach ($category_revenue as $category) {
                $trends = Order::select(
                        DB::raw('DATE(orders.created_at) as date'),
                        DB::raw('SUM(orders.total_amount) as daily_revenue')
                    )
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('products.category_id', $category['id'])
                    ->whereBetween('orders.created_at', [$start_date, $end_date])
                    ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'date' => $item->date,
                            'daily_revenue' => (float) $item->daily_revenue,
                        ];
                    })
                    ->toArray();

                $category_trends[$category['title']] = $trends;
            }
        }

        // Subcategory breakdown (optional - only if you have parent categories)
        $subcategory_breakdown = [];

        return view('admin.reports.revenue.by-category', compact(
            'start_date',
            'end_date',
            'category_revenue',
            'total_revenue',
            'total_units_sold',
            'category_trends',
            'subcategory_breakdown'
        ));
    }

    /**
     * HELPER METHOD - Add this to get category colors consistently
     */
    private function getCategoryColor($index)
    {
        $colors = [
            '#5B914C', '#0dcaf0', '#0d6efd', '#ffc107', '#dc3545',
            '#6c757d', '#20c997', '#fd7e14', '#6f42c1', '#d63384'
        ];

        return $colors[$index % count($colors)];
    }

    /**
     * Revenue by Product
     */
    public function revenueByProduct(Request $request)
    {
        // Get date range from request or default to this month
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // Get all categories for filter dropdown
        $categories = ProductsCategories::active()
            ->ordered()
            ->get();

        // Build the query
        $query = Product::select(
                'products.id',
                'products.name',
                'products.sku',
                'products.main_image',
                'products.category_id',
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'),
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.main_image', 'products.category_id');

        // Apply category filter if selected
        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('products.name', 'like', "%{$searchTerm}%")
                ->orWhere('products.sku', 'like', "%{$searchTerm}%");
            });
        }

        // Get products ordered by revenue
        $products = $query->orderBy('revenue', 'desc')
            ->with('category')
            ->get()
            ->map(function($product) {
                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'main_image' => $product->main_image,
                    'category' => $product->category,
                    'revenue' => (float) $product->revenue,
                    'units_sold' => (int) $product->units_sold,
                    'order_count' => (int) $product->order_count,
                ];
            });

        // Calculate totals
        $total_revenue = $products->sum('revenue');
        $total_units_sold = $products->sum('units_sold');
        $total_products = $products->count();

        return view('admin.reports.revenue.by-product', compact(
            'start_date',
            'end_date',
            'products',
            'categories',
            'total_revenue',
            'total_units_sold',
            'total_products'
        ));
    }

    // ==================== PRODUCT REPORTS ====================

    /**
     * Top Selling Products
     */
    public function productsTopSelling(Request $request)
    {
        // Get period and date from request
        $period = $request->input('period', 'month');
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $limit = $request->input('limit', 50);

        // Get date range based on period
        $date_range = $this->getDateRangeForPeriod($period, $date->format('Y-m-d'));

        // ✅ FIX: Get top selling products with accurate revenue
        $top_products = DB::table('order_items')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.main_image',
                'products.price as current_price', // Current catalog price
                'products_categories.title as category_name',

                // ✅ Sales metrics
                DB::raw('SUM(order_items.quantity) as total_sold'),

                // ✅ FIXED: Revenue calculation
                // Use order_items.total which includes item-level discounts and taxes
                DB::raw('SUM(order_items.total) as total_item_revenue'),

                // ✅ Add tax attributed to this product
                DB::raw('SUM(order_items.tax_amount) as total_tax'),

                // ✅ Add discount attributed to this product
                DB::raw('SUM(order_items.discount_amount) as total_discount'),

                // ✅ Subtotal before discounts
                DB::raw('SUM(order_items.subtotal) as total_subtotal'),

                // ✅ Average selling price (actual price customers paid)
                DB::raw('AVG(order_items.unit_price) as avg_price'),

                // ✅ Order metrics
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),

                // ✅ Variant tracking
                DB::raw('COUNT(DISTINCT order_items.product_variant_id) as variant_count'),
                DB::raw('SUM(CASE WHEN order_items.product_variant_id IS NOT NULL THEN order_items.quantity ELSE 0 END) as variant_sales'),
                DB::raw('SUM(CASE WHEN order_items.product_variant_id IS NULL THEN order_items.quantity ELSE 0 END) as simple_sales')
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('products_categories', 'products.category_id', '=', 'products_categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$date_range['start'], $date_range['end']])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING', 'ORDER_COMPLETED'])
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.main_image',
                'products.price',
                'products_categories.title'
            )
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(function($product) {
                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'main_image' => $product->main_image,
                    'current_price' => (float) $product->current_price,
                    'category_name' => $product->category_name,

                    // ✅ Sales data
                    'total_sold' => (int) $product->total_sold,
                    'order_count' => (int) $product->order_count,

                    // ✅ FIXED: Revenue breakdown
                    'total_revenue' => (float) $product->total_item_revenue, // This is what customer paid
                    'total_subtotal' => (float) $product->total_subtotal,    // Before discounts
                    'total_discount' => (float) $product->total_discount,    // Discounts applied
                    'total_tax' => (float) $product->total_tax,              // Tax collected

                    // ✅ Average metrics
                    'avg_price' => (float) $product->avg_price, // Actual selling price
                    'avg_order_value' => $product->order_count > 0
                        ? (float) $product->total_item_revenue / $product->order_count
                        : 0,

                    // ✅ Variant info
                    'variant_count' => (int) $product->variant_count,
                    'variant_sales' => (int) $product->variant_sales,
                    'simple_sales' => (int) $product->simple_sales,
                    'has_variants' => (int) $product->variant_count > 0,

                    // ✅ Performance metrics
                    'discount_rate' => $product->total_subtotal > 0
                        ? round(($product->total_discount / $product->total_subtotal) * 100, 2)
                        : 0,
                    'revenue_per_unit' => $product->total_sold > 0
                        ? (float) $product->total_item_revenue / $product->total_sold
                        : 0,
                ];
            });

        // ✅ FIXED: Calculate totals - now matches index dashboard
        $total_units_sold = $top_products->sum('total_sold');
        $total_revenue = $top_products->sum('total_revenue');
        $total_subtotal = $top_products->sum('total_subtotal');
        $total_discount = $top_products->sum('total_discount');
        $total_tax = $top_products->sum('total_tax');
        $order_count = $top_products->sum('order_count');

        // ✅ Calculate additional metrics
        $avg_discount_rate = $total_subtotal > 0
            ? round(($total_discount / $total_subtotal) * 100, 2)
            : 0;

        $avg_revenue_per_order = $order_count > 0
            ? $total_revenue / $order_count
            : 0;

        // ✅ VERIFY: Get actual order totals for comparison
        $verification = Order::whereBetween('created_at', [$date_range['start'], $date_range['end']])
            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING', 'ORDER_COMPLETED'])
            ->selectRaw('
                COUNT(*) as order_count,
                SUM(total_amount) as total_order_amount,
                SUM(subtotal) as total_order_subtotal,
                SUM(discount_amount) as total_order_discount,
                SUM(tax_amount) as total_order_tax,
                SUM(shipping_amount) as total_order_shipping
            ')
            ->first();

        return view('admin.reports.products.top-selling', compact(
            'period',
            'date',
            'date_range',
            'limit',
            'top_products',
            'total_units_sold',
            'total_revenue',
            'total_subtotal',
            'total_discount',
            'total_tax',
            'order_count',
            'avg_discount_rate',
            'avg_revenue_per_order',
            'verification' // ✅ NEW: For debugging/comparison
        ));
    }

    /**
     * Products by Category
     */
    public function productsByCategory(Request $request)
    {
        // Get date range from request or default to this month
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // Get category performance data with product counts
        $category_performance = ProductsCategories::select(
                'products_categories.id',
                'products_categories.title',
                DB::raw('COUNT(DISTINCT products.id) as total_products'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total), 0) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->leftJoin('products', 'products_categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('products_categories.id', 'products_categories.title')
            ->having('revenue', '>', 0)
            ->orderBy('revenue', 'desc')
            ->get()
            ->map(function($category) use ($start_date, $end_date) {
                // Get top 5 products for this category
                $top_products = Product::select(
                        'products.id',
                        'products.name',
                        'products.sku',
                        'products.main_image',
                        DB::raw('SUM(order_items.quantity) as units_sold'),
                        DB::raw('SUM(order_items.total) as revenue')
                    )
                    ->join('order_items', 'products.id', '=', 'order_items.product_id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('products.category_id', $category->id)
                    ->whereBetween('orders.created_at', [$start_date, $end_date])
                    ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                    ->groupBy('products.id', 'products.name', 'products.sku', 'products.main_image')
                    ->orderBy('units_sold', 'desc')
                    ->limit(5)
                    ->get();

                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'total_products' => (int) $category->total_products,
                    'units_sold' => (int) $category->units_sold,
                    'revenue' => (float) $category->revenue,
                    'order_count' => (int) $category->order_count,
                    'top_products' => $top_products
                ];
            });

        // Calculate totals
        $total_products_sold = $category_performance->sum('total_products');
        $total_units_sold = $category_performance->sum('units_sold');
        $total_revenue = $category_performance->sum('revenue');

        return view('admin.reports.products.by-category', compact(
            'start_date',
            'end_date',
            'category_performance',
            'total_products_sold',
            'total_units_sold',
            'total_revenue'
        ));
    }

    /**
     * Product Performance Report
     */
    public function productsPerformance(Request $request)
    {
        // Get date range from request or default to this month
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // Get performance filter
        $performance_filter = $request->input('performance_filter', 'all');

        // Build base query for products with sales data
        $query = Product::select(
                'products.id',
                'products.name',
                'products.sku',
                'products.main_image',
                'products.price',
                'products.stock_quantity',
                'products.low_stock_threshold',
                'products_categories.title as category_name',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.total), 0) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->leftJoin('products_categories', 'products.category_id', '=', 'products_categories.id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.main_image',
                'products.price',
                'products.stock_quantity',
                'products.low_stock_threshold',
                'products_categories.title'
            );

        // Apply low stock filter if selected
        if ($performance_filter == 'low_stock') {
            $query->whereRaw('products.stock_quantity <= products.low_stock_threshold');
        }

        // Get all products
        $all_products = $query->get();

        // Calculate performance scores and filter
        $products = $all_products->filter(function($product) use ($performance_filter, $all_products) {
            if ($all_products->count() == 0) return false;

            // Calculate performance score
            $max_revenue = $all_products->max('revenue');
            $revenue_score = $max_revenue > 0 ? ($product->revenue / $max_revenue) * 50 : 0;

            $max_units = $all_products->max('units_sold');
            $units_score = $max_units > 0 ? ($product->units_sold / $max_units) * 30 : 0;

            $order_score = min(($product->order_count / 10) * 20, 20);

            $performance_score = round($revenue_score + $units_score + $order_score);

            // Determine performance level
            if ($performance_score >= 80) {
                $level = 'excellent';
            } elseif ($performance_score >= 60) {
                $level = 'good';
            } elseif ($performance_score >= 40) {
                $level = 'average';
            } else {
                $level = 'poor';
            }

            // Apply filter
            if ($performance_filter == 'all' || $performance_filter == 'low_stock') {
                return true;
            }

            return $level == $performance_filter;
        })
        ->sortByDesc('revenue')
        ->values();

        // Calculate summary statistics
        $total_products = Product::count();
        $active_products = Product::count(); // Count all products since we don't have is_active
        $low_stock_count = Product::whereRaw('stock_quantity <= low_stock_threshold')->count();

        $total_revenue = $products->sum('revenue');
        $total_units_sold = $products->sum('units_sold');

        return view('admin.reports.products.performance', compact(
            'start_date',
            'end_date',
            'products',
            'total_products',
            'active_products',
            'low_stock_count',
            'total_revenue',
            'total_units_sold'
        ));
    }

    // ==================== INVENTORY REPORTS ====================

    /**
     *Inventory Index Report - Matches Inventory Module
     */
    public function inventoryIndex()
    {
        if (!auth('admin')->user()->hasPermission('reports.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();

        // ✅ Calculate accurate statistics
        $stats = $this->getInventoryStatistics();

        return view('admin.reports.inventory.index', compact('warehouses', 'stats'));
    }

    /**
     * Get accurate inventory statistics
     */
    private function getInventoryStatistics()
    {
        // 1. Count simple products (no variants)
        $simpleProductsTotal = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->count();

        // 2. Count active variants
        $variantsTotal = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->count();

        $totalProducts = $simpleProductsTotal + $variantsTotal;

        // 3. IN STOCK: Products/variants with stock > 0
        $inStockSimple = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->where(function($q) {
                // Has warehouse stock OR has product-level stock
                $q->whereHas('warehouseStock', function($wq) {
                    $wq->where('quantity', '>', 0);
                })->orWhere('stock_quantity', '>', 0);
            })->count();

        $inStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->where(function($q) {
                $q->whereHas('warehouseStock', function($wq) {
                    $wq->where('quantity', '>', 0);
                })->orWhere('stock_quantity', '>', 0);
            })->count();

        $inStock = $inStockSimple + $inStockVariants;

        // 4. LOW STOCK: Stock <= threshold AND > 0
        $lowStockSimple = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        $lowStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        $lowStock = $lowStockSimple + $lowStockVariants;

        // 5. OUT OF STOCK: Stock <= 0 everywhere
        $outOfStockSimple = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->where('stock_quantity', '<=', 0)
            ->whereDoesntHave('warehouseStock', function($q) {
                $q->where('quantity', '>', 0);
            })->count();

        $outOfStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->where('stock_quantity', '<=', 0)
            ->whereDoesntHave('warehouseStock', function($q) {
                $q->where('quantity', '>', 0);
            })->count();

        $outOfStock = $outOfStockSimple + $outOfStockVariants;

        // 6. TOTAL STOCK VALUE
        // Simple products
        $simpleProductValue = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->get()
            ->sum(function($product) {
                // Use warehouse stock if available, otherwise product stock
                $totalStock = $product->warehouseStock()->sum('quantity');
                if ($totalStock <= 0) {
                    $totalStock = $product->stock_quantity;
                }
                return $totalStock * $product->price;
            });

        // Variants
        $variantValue = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->get()
            ->sum(function($variant) {
                $totalStock = $variant->warehouseStock()->sum('quantity');
                if ($totalStock <= 0) {
                    $totalStock = $variant->stock_quantity;
                }
                return $totalStock * $variant->price;
            });

        $totalValue = $simpleProductValue + $variantValue;

        // 7. TOTAL STOCK QUANTITY
        $totalStockQty = ProductWarehouseStock::sum('quantity') +
                        Product::where('track_inventory', true)
                            ->where('has_variants', false)
                            ->whereDoesntHave('warehouseStock')
                            ->sum('stock_quantity') +
                        ProductVariant::whereHas('product', function($q) {
                                $q->where('track_inventory', true);
                            })
                            ->where('status_key_code', 'VARIANT_ACTIVE')
                            ->whereDoesntHave('warehouseStock')
                            ->sum('stock_quantity');

        return [
            'total_products' => $totalProducts,
            'in_stock' => $inStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_value' => round($totalValue, 2),
            'total_quantity' => $totalStockQty,
            'total_warehouses' => Warehouse::active()->count(),
            'total_reserved' => ProductWarehouseStock::sum('reserved_quantity'),
            'total_available' => ProductWarehouseStock::sum('available_quantity'),

            // Percentages
            'in_stock_percentage' => $totalProducts > 0 ? round(($inStock / $totalProducts) * 100, 1) : 0,
            'low_stock_percentage' => $totalProducts > 0 ? round(($lowStock / $totalProducts) * 100, 1) : 0,
            'out_of_stock_percentage' => $totalProducts > 0 ? round(($outOfStock / $totalProducts) * 100, 1) : 0,
        ];
    }
    /**
    * Stock Levels Report - DataTable compatible
    */
    public function inventoryStockLevels(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('reports.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();
        $categories = \App\Models\ProductsCategories::active()->orderBy('title')->get();

        // If AJAX request, return DataTable data
        if ($request->ajax()) {
            return $this->getStockLevelsData($request);
        }

        return view('admin.reports.inventory.stock-levels', compact('warehouses', 'categories'));
    }

    /**
     * Get stock levels data for DataTable
     */
    private function getStockLevelsData(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $categoryId = $request->get('category_id');
        $stockStatus = $request->get('stock_status');
        $search = $request->get('search');

        $items = collect();

        // 1. Get simple products
        $productsQuery = Product::with(['category', 'warehouseStock.warehouse'])
            ->where('track_inventory', true)
            ->where('has_variants', false);

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        if ($search) {
            $productsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        foreach ($productsQuery->get() as $product) {
            if ($warehouseId) {
                $warehouseStock = $product->warehouseStock()
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $currentStock = $warehouseStock ? $warehouseStock->quantity : 0;
                $reserved = $warehouseStock ? $warehouseStock->reserved_quantity : 0;
                $available = $warehouseStock ? $warehouseStock->available_quantity : 0;
            } else {
                $currentStock = $product->warehouseStock()->sum('quantity');
                if ($currentStock <= 0) {
                    $currentStock = $product->stock_quantity;
                }
                $reserved = $product->getTotalReservedStock();
                $available = $currentStock - $reserved;
            }

            // Apply stock status filter
            if ($stockStatus) {
                if ($stockStatus === 'in_stock' && $currentStock <= 0) continue;
                if ($stockStatus === 'low_stock' && !($currentStock > 0 && $currentStock <= $product->low_stock_threshold)) continue;
                if ($stockStatus === 'out_of_stock' && $currentStock > 0) continue;
            }

            $items->push([
                'type' => 'product',
                'id' => $product->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_name' => null,
                'sku' => $product->sku,
                'category' => $product->category ? $product->category->title : 'N/A',
                'current_stock' => $currentStock,
                'reserved' => $reserved,
                'available' => $available,
                'threshold' => $product->low_stock_threshold ?? 10,
                'price' => $product->price,
                'stock_value' => $currentStock * $product->price,
                'status' => $this->getStockStatus($currentStock, $product->low_stock_threshold),
            ]);
        }

        // 2. Get variants
        $variantsQuery = ProductVariant::with(['product.category', 'warehouseStock.warehouse'])
            ->whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE');

        if ($categoryId) {
            $variantsQuery->whereHas('product', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($search) {
            $variantsQuery->where(function($q) use ($search) {
                $q->where('variant_name', 'like', "%{$search}%")
                ->orWhere('variant_value', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                });
            });
        }

        foreach ($variantsQuery->get() as $variant) {
            if ($warehouseId) {
                $warehouseStock = $variant->warehouseStock()
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $currentStock = $warehouseStock ? $warehouseStock->quantity : 0;
                $reserved = $warehouseStock ? $warehouseStock->reserved_quantity : 0;
                $available = $warehouseStock ? $warehouseStock->available_quantity : 0;
            } else {
                $currentStock = $variant->warehouseStock()->sum('quantity');
                if ($currentStock <= 0) {
                    $currentStock = $variant->stock_quantity;
                }
                $reserved = $variant->getTotalReservedStock();
                $available = $currentStock - $reserved;
            }

            // Apply stock status filter
            if ($stockStatus) {
                if ($stockStatus === 'in_stock' && $currentStock <= 0) continue;
                if ($stockStatus === 'low_stock' && !($currentStock > 0 && $currentStock <= $variant->low_stock_threshold)) continue;
                if ($stockStatus === 'out_of_stock' && $currentStock > 0) continue;
            }

            $items->push([
                'type' => 'variant',
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name' => $variant->product->name,
                'variant_name' => $variant->getFullName(),
                'sku' => $variant->sku,
                'category' => $variant->product->category ? $variant->product->category->title : 'N/A',
                'current_stock' => $currentStock,
                'reserved' => $reserved,
                'available' => $available,
                'threshold' => $variant->low_stock_threshold ?? 10,
                'price' => $variant->price,
                'stock_value' => $currentStock * $variant->price,
                'status' => $this->getStockStatus($currentStock, $variant->low_stock_threshold),
            ]);
        }

        return DataTables::of($items)
            ->addColumn('product_info', function($item) {
                $variantBadge = '';
                if ($item['type'] === 'variant') {
                    $variantBadge = '<br><span class="badge bg-info">
                        <i class="bi bi-layers"></i> ' . htmlspecialchars($item['variant_name']) . '
                    </span>';
                }

                return '<div>
                    <strong>' . htmlspecialchars($item['name']) . '</strong>' . $variantBadge . '<br>
                    <small class="text-muted">SKU: ' . htmlspecialchars($item['sku']) . '</small>
                </div>';
            })
            ->addColumn('category', function($item) {
                return '<span class="badge bg-light text-dark">' . htmlspecialchars($item['category']) . '</span>';
            })
            ->addColumn('current_stock', function($item) {
                $class = $item['status'] === 'out_of_stock' ? 'text-danger' :
                        ($item['status'] === 'low_stock' ? 'text-warning' : 'text-success');

                return '<strong class="' . $class . '">' . number_format($item['current_stock']) . '</strong>';
            })
            ->addColumn('available', function($item) {
                return '<span class="badge bg-success">' . number_format($item['available']) . '</span>';
            })
            ->addColumn('reserved', function($item) {
                return $item['reserved'] > 0
                    ? '<span class="badge bg-warning">' . number_format($item['reserved']) . '</span>'
                    : '<span class="text-muted">0</span>';
            })
            ->addColumn('threshold', function($item) {
                return '<span class="badge bg-secondary">' . number_format($item['threshold']) . '</span>';
            })
            ->addColumn('stock_value', function($item) {
                return '<strong>' . store_currency_symbol() . number_format($item['stock_value'], 2) . '</strong>';
            })
            ->addColumn('status_badge', function($item) {
                return $this->getStockStatusBadge($item['status']);
            })
            ->rawColumns(['product_info', 'category', 'current_stock', 'available', 'reserved', 'threshold', 'stock_value', 'status_badge'])
            ->make(true);
    }

    /**
     *  Get stock status
     */
    private function getStockStatus($currentStock, $threshold)
    {
        if ($currentStock <= 0) {
            return 'out_of_stock';
        } elseif ($currentStock <= $threshold) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    /**
     *  Get stock status badge HTML
     */
    private function getStockStatusBadge($status)
    {
        switch ($status) {
            case 'in_stock':
                return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> In Stock</span>';
            case 'low_stock':
                return '<span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Low Stock</span>';
            case 'out_of_stock':
                return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Out of Stock</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }

    /**
     * ✅ FIXED: Inventory Movement Report
     */
    public function inventoryMovement(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('reports.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();

        // ✅ If AJAX request (DataTables), return movement data
        if ($request->ajax()) {
            return $this->getInventoryMovementData($request);
        }

        // ✅ For initial page load, return view with warehouses
        return view('admin.reports.inventory.movement', compact('warehouses'));
    }

    /**
     * ✅ EXISTING METHOD: Already implemented in previous response
     */
    private function getInventoryMovementData(Request $request)
    {
        $query = InventoryMovement::with([
                'product' => function($query) {
                    $query->withTrashed();
                },
                'variant', // ← Includes variants
                'warehouse',
                'fromWarehouse',
                'toWarehouse',
                'creator'
            ])
            ->latest();

        // Apply filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('variant', function($vq) use ($search) {
                    $vq->where('sku', 'like', "%{$search}%")
                    ->orWhere('variant_name', 'like', "%{$search}%");
                })->orWhereHas('creator', function($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        return DataTables::of($query)
            ->addColumn('product_info', function($movement) {
                if (!$movement->product) {
                    return '<div>
                        <strong class="text-danger">Product Deleted</strong><br>
                        <small class="text-muted">ID: ' . $movement->product_id . '</small>
                    </div>';
                }

                $variantBadge = '';
                if ($movement->variant_id && $movement->variant) {
                    $variantBadge = '<span class="variant-badge">
                        <i class="bi bi-layers"></i> ' . htmlspecialchars($movement->variant->getFullName()) . '
                    </span>';
                }

                return '<div>
                    <strong>' . htmlspecialchars($movement->product->name) . '</strong>' . $variantBadge . '<br>
                    <small class="text-muted">SKU: ' .
                        htmlspecialchars($movement->variant ? $movement->variant->sku : $movement->product->sku) .
                    '</small>
                </div>';
            })
            ->addColumn('type_badge', function($movement) {
                return $movement->getTypeBadge();
            })
            ->addColumn('warehouse_info', function($movement) {
                if ($movement->type === 'transfer') {
                    return '<div>
                        <small><strong>From:</strong> ' . ($movement->fromWarehouse ? htmlspecialchars($movement->fromWarehouse->name) : 'N/A') . '</small><br>
                        <small><strong>To:</strong> ' . ($movement->toWarehouse ? htmlspecialchars($movement->toWarehouse->name) : 'N/A') . '</small>
                    </div>';
                }
                return $movement->warehouse ? htmlspecialchars($movement->warehouse->name) : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('quantity_change', function($movement) {
                $class = $movement->quantity >= 0 ? 'text-success' : 'text-danger';
                $sign = $movement->quantity >= 0 ? '+' : '';
                return '<strong class="' . $class . '">' . $sign . number_format($movement->quantity) . '</strong>';
            })
            ->addColumn('stock_levels', function($movement) {
                if ($movement->previous_quantity !== null && $movement->new_quantity !== null) {
                    return '<small>' . number_format($movement->previous_quantity) . ' → ' . number_format($movement->new_quantity) . '</small>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('created_info', function($movement) {
                return '<div>
                    <small>' . $movement->created_at->format('M d, Y H:i') . '</small><br>
                    <small class="text-muted">' . ($movement->creator ? htmlspecialchars($movement->creator->name) : 'System') . '</small>
                </div>';
            })
            ->addColumn('reason', function($movement) {
                return $movement->reason ? htmlspecialchars($movement->reason) : null;
            })
            ->rawColumns(['product_info', 'type_badge', 'warehouse_info', 'quantity_change', 'stock_levels', 'created_info'])
            ->make(true);
    }

    /**
     * ✅ FIXED: Inventory Valuation Report
     */
    public function inventoryValuation(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('reports.read')) {
            abort(403, 'Unauthorized access');
        }

        $warehouses = Warehouse::active()->byPriority()->get();
        $categories = \App\Models\ProductsCategories::active()->orderBy('title')->get();

        // ✅ Calculate valuation (method provided in earlier response)
        $valuation = $this->calculateInventoryValuation(
            $request->get('warehouse_id'),
            $request->get('category_id')
        );

        // ✅ Apply search filter
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $valuation['items'] = collect($valuation['items'])->filter(function($item) use ($search) {
                return str_contains(strtolower($item['name']), $search) ||
                    str_contains(strtolower($item['sku']), $search) ||
                    (isset($item['variant_name']) && str_contains(strtolower($item['variant_name']), $search));
            })->values()->all();
        }

        // ✅ Apply sorting
        $sort = $request->get('sort', 'value_desc');
        $valuation['items'] = collect($valuation['items']);

        switch ($sort) {
            case 'value_desc':
                $valuation['items'] = $valuation['items']->sortByDesc('total_value');
                break;
            case 'value_asc':
                $valuation['items'] = $valuation['items']->sortBy('total_value');
                break;
            case 'name_asc':
                $valuation['items'] = $valuation['items']->sortBy('name');
                break;
            case 'stock_desc':
                $valuation['items'] = $valuation['items']->sortByDesc('quantity');
                break;
        }

        $valuation['items'] = $valuation['items']->values()->all();

        return view('admin.reports.inventory.valuation', compact('warehouses', 'categories', 'valuation'));
    }

    /**
     * ✅ EXISTING METHOD: Already provided in earlier response
     */
    private function calculateInventoryValuation($warehouseId = null, $categoryId = null)
    {
        $items = collect();

        // 1. Simple products
        $productsQuery = Product::where('track_inventory', true)
            ->where('has_variants', false);

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        foreach ($productsQuery->get() as $product) {
            if ($warehouseId) {
                $stock = $product->warehouseStock()
                    ->where('warehouse_id', $warehouseId)
                    ->sum('quantity');
            } else {
                $stock = $product->warehouseStock()->sum('quantity');
                if ($stock <= 0) {
                    $stock = $product->stock_quantity;
                }
            }

            if ($stock > 0) {
                $items->push([
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'variant_name' => null,
                    'sku' => $product->sku,
                    'category' => $product->category ? $product->category->title : 'N/A',
                    'quantity' => $stock,
                    'unit_cost' => $product->cost_price ?? 0,
                    'unit_price' => $product->price,
                    'total_cost' => $stock * ($product->cost_price ?? 0),
                    'total_value' => $stock * $product->price,
                    'potential_profit' => $stock * ($product->price - ($product->cost_price ?? 0)),
                ]);
            }
        }

        // 2. Variants
        $variantsQuery = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE');

        if ($categoryId) {
            $variantsQuery->whereHas('product', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        foreach ($variantsQuery->get() as $variant) {
            if ($warehouseId) {
                $stock = $variant->warehouseStock()
                    ->where('warehouse_id', $warehouseId)
                    ->sum('quantity');
            } else {
                $stock = $variant->warehouseStock()->sum('quantity');
                if ($stock <= 0) {
                    $stock = $variant->stock_quantity;
                }
            }

            if ($stock > 0) {
                $items->push([
                    'type' => 'variant',
                    'id' => $variant->id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->getFullName(),
                    'sku' => $variant->sku,
                    'category' => $variant->product->category ? $variant->product->category->title : 'N/A',
                    'quantity' => $stock,
                    'unit_cost' => $variant->cost_price ?? 0,
                    'unit_price' => $variant->price,
                    'total_cost' => $stock * ($variant->cost_price ?? 0),
                    'total_value' => $stock * $variant->price,
                    'potential_profit' => $stock * ($variant->price - ($variant->cost_price ?? 0)),
                ]);
            }
        }

        // Calculate totals
        return [
            'items' => $items->all(),
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_cost' => round($items->sum('total_cost'), 2),
            'total_value' => round($items->sum('total_value'), 2),
            'total_profit' => round($items->sum('potential_profit'), 2),
            'profit_margin' => $items->sum('total_value') > 0
                ? round(($items->sum('potential_profit') / $items->sum('total_value')) * 100, 2)
                : 0,
        ];
    }

    /**
     *Get product/variant stock (matches InventoryController logic)
     */
    private function getEntityStock($entity, $warehouseId = null)
    {
        if ($warehouseId) {
            $warehouseStock = $entity->warehouseStock()
                ->where('warehouse_id', $warehouseId)
                ->first();

            return [
                'total' => $warehouseStock ? $warehouseStock->quantity : 0,
                'reserved' => $warehouseStock ? $warehouseStock->reserved_quantity : 0,
                'available' => $warehouseStock ? $warehouseStock->available_quantity : 0,
            ];
        }

        $totalWarehouse = $entity->warehouseStock()->sum('quantity');

        return [
            'total' => $totalWarehouse > 0 ? $totalWarehouse : $entity->stock_quantity,
            'reserved' => $entity->getTotalReservedStock(),
            'available' => ($totalWarehouse > 0 ? $totalWarehouse : $entity->stock_quantity) - $entity->getTotalReservedStock(),
        ];
    }

    /**
     * Inventory Alerts - Comprehensive alert view
     */
    public function inventoryAlerts(Request $request)
    {
        // Get filter parameters
        $alert_type = $request->input('alert_type', 'all'); // all, critical, low_stock, overstock
        $category_id = $request->input('category_id');
        $search = $request->input('search');
        $sort = $request->input('sort', 'severity_desc');

        // Get critical alerts (out of stock)
        $critical_alerts_query = Product::with('category')
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 0);

        // Get low stock alerts
        $low_stock_alerts_query = Product::with('category')
            ->where('track_inventory', true)
            ->whereRaw('stock_quantity > 0 AND stock_quantity <= low_stock_threshold');

        // Get overstock alerts (optional - stock > threshold * 5)
        $overstock_alerts_query = Product::with('category')
            ->where('track_inventory', true)
            ->whereRaw('stock_quantity > (low_stock_threshold * 5)');

        // Apply filters
        if ($category_id) {
            $critical_alerts_query->where('category_id', $category_id);
            $low_stock_alerts_query->where('category_id', $category_id);
            $overstock_alerts_query->where('category_id', $category_id);
        }

        if ($search) {
            $searchClosure = function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            };

            $critical_alerts_query->where($searchClosure);
            $low_stock_alerts_query->where($searchClosure);
            $overstock_alerts_query->where($searchClosure);
        }

        // Get alerts based on filter
        $alerts = collect();

        if ($alert_type === 'all' || $alert_type === 'critical') {
            $critical = $critical_alerts_query->get()->map(function($product) {
                return [
                    'product' => $product,
                    'type' => 'critical',
                    'severity' => 3,
                    'message' => 'Out of Stock',
                    'icon' => 'bi-x-circle-fill',
                    'color' => 'danger'
                ];
            });
            $alerts = $alerts->merge($critical);
        }

        if ($alert_type === 'all' || $alert_type === 'low_stock') {
            $low_stock = $low_stock_alerts_query->get()->map(function($product) {
                $percentage = ($product->stock_quantity / $product->low_stock_threshold) * 100;

                return [
                    'product' => $product,
                    'type' => 'low_stock',
                    'severity' => 2,
                    'message' => "Low Stock ({$product->stock_quantity} remaining)",
                    'percentage' => $percentage,
                    'icon' => 'bi-exclamation-triangle-fill',
                    'color' => 'warning'
                ];
            });
            $alerts = $alerts->merge($low_stock);
        }

        if ($alert_type === 'all' || $alert_type === 'overstock') {
            $overstock = $overstock_alerts_query->get()->map(function($product) {
                $threshold = $product->low_stock_threshold * 5;

                return [
                    'product' => $product,
                    'type' => 'overstock',
                    'severity' => 1,
                    'message' => "Possible Overstock ({$product->stock_quantity} units)",
                    'threshold' => $threshold,
                    'icon' => 'bi-info-circle-fill',
                    'color' => 'info'
                ];
            });
            $alerts = $alerts->merge($overstock);
        }

        // Apply sorting
        switch ($sort) {
            case 'severity_desc':
                $alerts = $alerts->sortByDesc('severity');
                break;
            case 'severity_asc':
                $alerts = $alerts->sortBy('severity');
                break;
            case 'stock_asc':
                $alerts = $alerts->sortBy(fn($a) => $a['product']->stock_quantity);
                break;
            case 'stock_desc':
                $alerts = $alerts->sortByDesc(fn($a) => $a['product']->stock_quantity);
                break;
            case 'name_asc':
                $alerts = $alerts->sortBy(fn($a) => $a['product']->name);
                break;
        }

        // Get categories for filter
        $categories = ProductsCategories::orderBy('title')->get();

        // Calculate summary statistics
        $total_alerts = $alerts->count();
        $critical_count = $alerts->where('type', 'critical')->count();
        $low_stock_count = $alerts->where('type', 'low_stock')->count();
        $overstock_count = $alerts->where('type', 'overstock')->count();

        // Calculate value at risk (critical + low stock)
        $value_at_risk = $alerts->filter(function($alert) {
            return in_array($alert['type'], ['critical', 'low_stock']);
        })->sum(fn($alert) => $alert['product']->price * $alert['product']->stock_quantity);

        // Get recent alert history (last 30 days)
        $alert_history = $this->getAlertHistory();

        return view('admin.reports.inventory.alerts', compact(
            'alerts',
            'categories',
            'total_alerts',
            'critical_count',
            'low_stock_count',
            'overstock_count',
            'value_at_risk',
            'alert_history',
            'alert_type',
            'category_id',
            'search',
            'sort'
        ));
    }

    /**
     * Get alert history for trend analysis
     */
    private function getAlertHistory(): array
    {
        $history = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $critical = Product::where('track_inventory', true)
                ->where('stock_quantity', '<=', 0)
                ->count();

            $low_stock = Product::where('track_inventory', true)
                ->whereRaw('stock_quantity > 0 AND stock_quantity <= low_stock_threshold')
                ->count();

            $history[] = [
                'date' => $date->format('M d'),
                'critical' => $critical,
                'low_stock' => $low_stock,
                'total' => $critical + $low_stock
            ];
        }

        return $history;
    }

    // ==================== CUSTOMER REPORTS ====================

    /**
     * Customer Analytics Overview
     */
    public function customersIndex(Request $request)
    {
        // Get filter parameters
        $segment = $request->input('segment', 'all');
        $search = $request->input('search');
        $sort = $request->input('sort', 'ltv_desc');

        // Build base query
        $query = Customer::query();

        // Apply segment filter
        if ($segment == 'vip') {
            $query->highValue(1000);
        } elseif ($segment == 'new') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($segment == 'repeat') {
            $query->where('total_orders', '>', 1);
        } elseif ($segment == 'inactive') {
            $query->inactiveForDays(90);
        }

        // Apply search filter
        if ($search) {
            $query->search($search);
        }

        // Apply sorting
        switch ($sort) {
            case 'ltv_desc':
                $query->orderBySpent('desc');
                break;
            case 'orders_desc':
                $query->orderByOrders('desc');
                break;
            case 'recent':
                $query->orderByRegistration('desc');
                break;
            case 'name_asc':
                $query->orderBy('first_name', 'asc');
                break;
            default:
                $query->orderBySpent('desc');
        }

        // Get customers with their statistics
        $customers = $query->get();

        // Calculate summary statistics
        $total_customers = Customer::count();
        $new_customers = Customer::thisMonth()->count();

        $all_customers = Customer::all();
        $total_revenue = $all_customers->sum('total_spent');
        $average_ltv = $total_customers > 0 ? $total_revenue / $total_customers : 0;

        $repeat_customers = Customer::where('total_orders', '>', 1)->count();
        $repeat_rate = $total_customers > 0 ? ($repeat_customers / $total_customers) * 100 : 0;

        // Customer Segmentation
        $segments = [
            [
                'name' => 'VIP Customers',
                'count' => Customer::where('total_spent', '>=', 1000)->count(),
                'description' => 'Spent $1000+'
            ],
            [
                'name' => 'Repeat Buyers',
                'count' => Customer::where('total_orders', '>', 1)->count(),
                'description' => '2+ orders'
            ],
            [
                'name' => 'New Customers',
                'count' => Customer::where('created_at', '>=', now()->subDays(30))->count(),
                'description' => 'Joined last 30 days'
            ],
            [
                'name' => 'Inactive',
                'count' => Customer::where('last_order_at', '<', now()->subDays(90))
                                ->where('total_orders', '>', 0)
                                ->count(),
                'description' => 'No orders 90+ days'
            ]
        ];

        // Customer Growth (last 6 months)
        $customer_growth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Customer::whereYear('created_at', $month->year)
                            ->whereMonth('created_at', $month->month)
                            ->count();

            $customer_growth[] = [
                'month' => $month->format('M Y'),
                'count' => $count
            ];
        }

        // LTV Distribution
        $ltv_distribution = [
            [
                'segment' => 'High ($1000+)',
                'count' => Customer::where('total_spent', '>=', 1000)->count()
            ],
            [
                'segment' => 'Medium ($500-$999)',
                'count' => Customer::whereBetween('total_spent', [500, 999.99])->count()
            ],
            [
                'segment' => 'Low (<$500)',
                'count' => Customer::where('total_spent', '<', 500)->count()
            ]
        ];

        return view('admin.reports.customers.index', compact(
            'customers',
            'total_customers',
            'new_customers',
            'average_ltv',
            'repeat_rate',
            'segments',
            'customer_growth',
            'ltv_distribution'
        ));
    }

    /**
     * New vs Returning Customers
     */
    public function customersNewVsReturning(Request $request)
    {
        // Determine date range based on period
        $period = $request->input('period', '30days');

        if ($period == 'custom') {
            $start_date = $request->start_date
                ? Carbon::parse($request->start_date)->startOfDay()
                : now()->subDays(30)->startOfDay();
            $end_date = $request->end_date
                ? Carbon::parse($request->end_date)->endOfDay()
                : now()->endOfDay();
        } else {
            switch ($period) {
                case '7days':
                    $start_date = now()->subDays(7)->startOfDay();
                    $end_date = now()->endOfDay();
                    break;
                case '90days':
                    $start_date = now()->subDays(90)->startOfDay();
                    $end_date = now()->endOfDay();
                    break;
                case 'this_month':
                    $start_date = now()->startOfMonth();
                    $end_date = now()->endOfMonth();
                    break;
                case 'last_month':
                    $start_date = now()->subMonth()->startOfMonth();
                    $end_date = now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $start_date = now()->startOfYear();
                    $end_date = now()->endOfYear();
                    break;
                default: // 30days
                    $start_date = now()->subDays(30)->startOfDay();
                    $end_date = now()->endOfDay();
            }
        }

        // Get orders in date range with customer relationship
        $orders = Order::with('customer')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING', 'ORDER_COMPLETED'])
            ->get();

        // Classify orders as new or returning
        $new_orders = collect();
        $returning_orders = collect();

        foreach ($orders as $order) {
            if ($order->customer) {
                // Check if this was customer's first order
                $customer_orders_before = Order::where('customer_id', $order->customer_id)
                    ->where('created_at', '<', $order->created_at)
                    ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING', 'ORDER_COMPLETED'])
                    ->count();

                if ($customer_orders_before == 0) {
                    $new_orders->push($order);
                } else {
                    $returning_orders->push($order);
                }
            }
        }

        // Calculate metrics for new customers
        $new_customers_count = $new_orders->pluck('customer_id')->unique()->count();
        $new_orders_count = $new_orders->count();
        $new_revenue = $new_orders->sum('total_amount');
        $new_aov = $new_orders_count > 0 ? $new_revenue / $new_orders_count : 0;

        // Calculate metrics for returning customers
        $returning_customers_count = $returning_orders->pluck('customer_id')->unique()->count();
        $returning_orders_count = $returning_orders->count();
        $returning_revenue = $returning_orders->sum('total_amount');
        $returning_aov = $returning_orders_count > 0 ? $returning_revenue / $returning_orders_count : 0;

        // Calculate percentages
        $total_customers = $new_customers_count + $returning_customers_count;
        $new_percentage = $total_customers > 0 ? ($new_customers_count / $total_customers) * 100 : 0;
        $returning_percentage = $total_customers > 0 ? ($returning_customers_count / $total_customers) * 100 : 0;

        // Generate trend data (daily)
        $trend_data = [];
        $current_date = $start_date->copy();

        while ($current_date <= $end_date) {
            $day_orders = $orders->filter(function($order) use ($current_date) {
                return $order->created_at->isSameDay($current_date);
            });

            $day_new = collect();
            $day_returning = collect();

            foreach ($day_orders as $order) {
                if ($order->customer) {
                    $customer_orders_before = Order::where('customer_id', $order->customer_id)
                        ->where('created_at', '<', $order->created_at)
                        ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING', 'ORDER_COMPLETED'])
                        ->count();

                    if ($customer_orders_before == 0) {
                        $day_new->push($order);
                    } else {
                        $day_returning->push($order);
                    }
                }
            }

            $trend_data[] = [
                'date' => $current_date->format('M d'),
                'new_customers' => $day_new->pluck('customer_id')->unique()->count(),
                'returning_customers' => $day_returning->pluck('customer_id')->unique()->count(),
                'new_revenue' => $day_new->sum('total_amount'),
                'returning_revenue' => $day_returning->sum('total_amount')
            ];

            $current_date->addDay();
        }

        // Generate monthly breakdown
        $monthly_breakdown = [];
        $months_count = $start_date->diffInMonths($end_date) + 1;

        for ($i = 0; $i < $months_count; $i++) {
            $month_start = $start_date->copy()->addMonths($i)->startOfMonth();
            $month_end = $start_date->copy()->addMonths($i)->endOfMonth();

            if ($month_end->gt($end_date)) {
                $month_end = $end_date->copy();
            }

            $month_orders = $orders->filter(function($order) use ($month_start, $month_end) {
                return $order->created_at->between($month_start, $month_end);
            });

            $month_new = collect();
            $month_returning = collect();

            foreach ($month_orders as $order) {
                if ($order->customer) {
                    $customer_orders_before = Order::where('customer_id', $order->customer_id)
                        ->where('created_at', '<', $order->created_at)
                        ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING', 'ORDER_COMPLETED'])
                        ->count();

                    if ($customer_orders_before == 0) {
                        $month_new->push($order);
                    } else {
                        $month_returning->push($order);
                    }
                }
            }

            $monthly_breakdown[] = [
                'month' => $month_start->format('M Y'),
                'new_customers' => $month_new->pluck('customer_id')->unique()->count(),
                'returning_customers' => $month_returning->pluck('customer_id')->unique()->count(),
                'new_revenue' => $month_new->sum('total_amount'),
                'returning_revenue' => $month_returning->sum('total_amount')
            ];
        }

        return view('admin.reports.customers.new-vs-returning', compact(
            'start_date',
            'end_date',
            'new_customers_count',
            'new_orders_count',
            'new_revenue',
            'new_aov',
            'new_percentage',
            'returning_customers_count',
            'returning_orders_count',
            'returning_revenue',
            'returning_aov',
            'returning_percentage',
            'trend_data',
            'monthly_breakdown'
        ));
    }

    /**
     * Customer Lifetime Value
     */
    public function customersLifetimeValue(Request $request)
    {
        // Get filter parameters
        $segment = $request->input('segment', 'all');
        $search = $request->input('search');
        $sort = $request->input('sort', 'ltv_desc');

        // Build query
        $query = Customer::query();

        // Apply segment filter
        if ($segment == 'platinum') {
            $query->where('total_spent', '>=', 5000);
        } elseif ($segment == 'gold') {
            $query->whereBetween('total_spent', [2000, 4999.99]);
        } elseif ($segment == 'silver') {
            $query->whereBetween('total_spent', [500, 1999.99]);
        } elseif ($segment == 'bronze') {
            $query->where('total_spent', '<', 500);
        }

        // Apply search filter
        if ($search) {
            $query->search($search);
        }

        // Apply sorting
        switch ($sort) {
            case 'ltv_desc':
                $query->orderBySpent('desc');
                break;
            case 'ltv_asc':
                $query->orderBySpent('asc');
                break;
            case 'orders_desc':
                $query->orderByOrders('desc');
                break;
            case 'recent':
                $query->orderByRegistration('desc');
                break;
            default:
                $query->orderBySpent('desc');
        }

        // Get customers
        $customers = $query->get();

        // Get top customers for special display
        $top_customers = Customer::orderBySpent('desc')->take(10)->get();

        // Calculate summary statistics
        $total_customers = Customer::count();
        $all_customers = Customer::all();
        $total_ltv = $all_customers->sum('total_spent');
        $average_ltv = $total_customers > 0 ? $total_ltv / $total_customers : 0;
        $highest_ltv = $all_customers->max('total_spent') ?? 0;

        // Calculate LTV segments
        $segments = [
            [
                'name' => 'Platinum',
                'class' => 'platinum',
                'icon' => 'bi-gem',
                'description' => '$5,000+ Lifetime Value',
                'count' => Customer::where('total_spent', '>=', 5000)->count(),
                'total_value' => Customer::where('total_spent', '>=', 5000)->sum('total_spent'),
                'avg_ltv' => 0,
                'avg_orders' => 0
            ],
            [
                'name' => 'Gold',
                'class' => 'gold',
                'icon' => 'bi-award',
                'description' => '$2,000 - $4,999',
                'count' => Customer::whereBetween('total_spent', [2000, 4999.99])->count(),
                'total_value' => Customer::whereBetween('total_spent', [2000, 4999.99])->sum('total_spent'),
                'avg_ltv' => 0,
                'avg_orders' => 0
            ],
            [
                'name' => 'Silver',
                'class' => 'silver',
                'icon' => 'bi-trophy',
                'description' => '$500 - $1,999',
                'count' => Customer::whereBetween('total_spent', [500, 1999.99])->count(),
                'total_value' => Customer::whereBetween('total_spent', [500, 1999.99])->sum('total_spent'),
                'avg_ltv' => 0,
                'avg_orders' => 0
            ],
            [
                'name' => 'Bronze',
                'class' => 'bronze',
                'icon' => 'bi-star',
                'description' => 'Under $500',
                'count' => Customer::where('total_spent', '<', 500)->count(),
                'total_value' => Customer::where('total_spent', '<', 500)->sum('total_spent'),
                'avg_ltv' => 0,
                'avg_orders' => 0
            ]
        ];

        // Calculate averages for each segment
        foreach ($segments as &$seg) {
            if ($seg['count'] > 0) {
                $seg['avg_ltv'] = $seg['total_value'] / $seg['count'];

                // Get average orders for this segment
                if ($seg['name'] == 'Platinum') {
                    $segment_customers = Customer::where('total_spent', '>=', 5000)->get();
                } elseif ($seg['name'] == 'Gold') {
                    $segment_customers = Customer::whereBetween('total_spent', [2000, 4999.99])->get();
                } elseif ($seg['name'] == 'Silver') {
                    $segment_customers = Customer::whereBetween('total_spent', [500, 1999.99])->get();
                } else {
                    $segment_customers = Customer::where('total_spent', '<', 500)->get();
                }

                $seg['avg_orders'] = $segment_customers->avg('total_orders') ?? 0;
            }
        }

        // Calculate predicted LTV (simple growth projection)
        // Using last 6 months growth rate
        $six_months_ago = now()->subMonths(6);
        $recent_customers = Customer::where('created_at', '>=', $six_months_ago)->get();
        $recent_avg_ltv = $recent_customers->count() > 0 ? $recent_customers->avg('total_spent') : $average_ltv;

        // Calculate growth rate
        $ltv_growth_rate = $average_ltv > 0 ? (($recent_avg_ltv - $average_ltv) / $average_ltv) * 100 : 0;
        $predicted_ltv = $average_ltv * (1 + ($ltv_growth_rate / 100));

        return view('admin.reports.customers.lifetime-value', compact(
            'customers',
            'top_customers',
            'total_customers',
            'total_ltv',
            'average_ltv',
            'highest_ltv',
            'segments',
            'predicted_ltv',
            'ltv_growth_rate'
        ));
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
     *Get inventory summary - Includes products AND variants
     */
    private function getInventorySummary(): array
    {
        // 1. COUNT SIMPLE PRODUCTS (no variants)
        $simpleProductsTotal = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->count();

        // 2. COUNT ACTIVE VARIANTS
        $variantsTotal = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->count();

        $totalProducts = $simpleProductsTotal + $variantsTotal;

        // 3. IN STOCK: Products/variants with stock > 0
        $inStockSimple = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->where(function($q) {
                $q->whereHas('warehouseStock', function($wq) {
                    $wq->where('quantity', '>', 0);
                })->orWhere('stock_quantity', '>', 0);
            })->count();

        $inStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->where(function($q) {
                $q->whereHas('warehouseStock', function($wq) {
                    $wq->where('quantity', '>', 0);
                })->orWhere('stock_quantity', '>', 0);
            })->count();

        $productsInStock = $inStockSimple + $inStockVariants;

        // 4. LOW STOCK: Stock <= threshold AND > 0
        $lowStockSimple = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        $lowStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        $productsLowStock = $lowStockSimple + $lowStockVariants;

        // 5. OUT OF STOCK: Stock <= 0 everywhere
        $outOfStockSimple = Product::where('track_inventory', true)
            ->where('has_variants', false)
            ->where('stock_quantity', '<=', 0)
            ->whereDoesntHave('warehouseStock', function($q) {
                $q->where('quantity', '>', 0);
            })->count();

        $outOfStockVariants = ProductVariant::whereHas('product', function($q) {
                $q->where('track_inventory', true);
            })
            ->where('status_key_code', 'VARIANT_ACTIVE')
            ->where('stock_quantity', '<=', 0)
            ->whereDoesntHave('warehouseStock', function($q) {
                $q->where('quantity', '>', 0);
            })->count();

        $productsOutOfStock = $outOfStockSimple + $outOfStockVariants;

        // 6. CALCULATE TOTAL STOCK VALUE
        $totalStockValue = $this->calculateTotalStockValue();

        return [
            'total_products' => $totalProducts,
            'products_in_stock' => $productsInStock,
            'products_low_stock' => $productsLowStock,
            'products_out_of_stock' => $productsOutOfStock,
            'total_stock_value' => $totalStockValue,
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
        // Define the groupBy expression based on period
        $groupByExpression = match($period) {
            'day' => 'HOUR(created_at)',
            'week', 'month' => 'DATE(created_at)',
            'year' => 'MONTH(created_at)',
            default => 'DATE(created_at)',
        };

        // Build the query with proper DB::raw usage
        return Order::select(
                    DB::raw("{$groupByExpression} as period"),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as order_count')
                )
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->groupBy(DB::raw($groupByExpression))
                ->orderBy(DB::raw($groupByExpression), 'asc')
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
   /**
     *Calculate total stock value - Better performance
     */
    private function calculateTotalStockValue(): float
    {
        // 1. Simple Products Value (using raw SQL for better performance)
        $simpleProductsValue = DB::table('products')
            ->where('track_inventory', true)
            ->where('has_variants', false)
            ->selectRaw('SUM(stock_quantity * price) as total_value')
            ->value('total_value') ?? 0;

        // Add warehouse stock for simple products
        $warehouseSimpleValue = DB::table('product_warehouse_stock')
            ->join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
            ->where('products.track_inventory', true)
            ->where('products.has_variants', false)
            ->whereNull('product_warehouse_stock.variant_id')
            ->selectRaw('SUM(product_warehouse_stock.quantity * products.price) as total_value')
            ->value('total_value') ?? 0;

        // 2. Variants Value
        $variantsValue = DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('products.track_inventory', true)
            ->where('product_variants.status_key_code', 'VARIANT_ACTIVE')
            ->selectRaw('SUM(product_variants.stock_quantity * product_variants.price) as total_value')
            ->value('total_value') ?? 0;

        // Add warehouse stock for variants
        $warehouseVariantsValue = DB::table('product_warehouse_stock')
            ->join('product_variants', 'product_warehouse_stock.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('products.track_inventory', true)
            ->where('product_variants.status_key_code', 'VARIANT_ACTIVE')
            ->whereNotNull('product_warehouse_stock.variant_id')
            ->selectRaw('SUM(product_warehouse_stock.quantity * product_variants.price) as total_value')
            ->value('total_value') ?? 0;

        // Use warehouse value if available, otherwise use product/variant value
        $totalSimpleValue = $warehouseSimpleValue > 0 ? $warehouseSimpleValue : $simpleProductsValue;
        $totalVariantsValue = $warehouseVariantsValue > 0 ? $warehouseVariantsValue : $variantsValue;

        return round($totalSimpleValue + $totalVariantsValue, 2);
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
        try {
            // Log the report type for debugging
            \Log::info('PDF Export Request', [
                'reportType' => $reportType,
                'request_params' => $request->all()
            ]);

            // Get report data based on type
            $data = $this->getReportData($reportType, $request);

            // Get appropriate view for PDF
            $view = $this->getReportView($reportType);

            // Log the view being used
            \Log::info('Using PDF view: ' . $view);

            // Check if view exists
            if (!view()->exists("admin.reports.exports.{$view}")) {
                throw new \Exception("PDF template not found: admin.reports.exports.{$view}");
            }

            // Generate PDF
            $pdf = Pdf::loadView("admin.reports.exports.{$view}", $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            // Generate filename
            $filename = $this->generateFilename($reportType, 'pdf');

            // Download PDF
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF Export Error', [
                'reportType' => $reportType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel($reportType, Request $request)
    {
        try {
            // Generate filename
            $filename = $this->generateFilename($reportType, 'xlsx');

            // Get appropriate export class
            $exportClass = $this->getExportClass($reportType, $request);

            // Download Excel file
            return Excel::download($exportClass, $filename);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export report to CSV
     */
    public function exportCsv($reportType, Request $request)
    {
        try {
            // Get report data
            $data = $this->getReportDataForCsv($reportType, $request);

            // Generate filename
            $filename = $this->generateFilename($reportType, 'csv');

            // Create CSV content
            $csv = $this->generateCsvContent($reportType, $data);

            // Return CSV download
            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate CSV: ' . $e->getMessage());
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get report data based on type
     */
    private function getReportData($reportType, $request)
    {
        switch ($reportType) {
            // ==================== SALES REPORTS ====================
            case 'sales-daily':
                return $this->getSalesDailyData($request);

            case 'sales-weekly':
                return $this->getSalesWeeklyData($request);

            case 'sales-monthly':
                return $this->getSalesMonthlyData($request);

            case 'sales-yearly':
                return $this->getSalesYearlyData($request);

            case 'sales-custom-range':
                return $this->getSalesCustomRangeData($request);

            // ==================== REVENUE REPORTS ====================
            case 'revenue-by-category':
                return $this->getRevenueByCategoryData($request);

            case 'revenue-by-product':
                return $this->getRevenueByProductData($request);

            // ==================== PRODUCT REPORTS ====================
            case 'products-top-selling':
                return $this->getProductsTopSellingData($request);

            case 'products-by-category':
                return $this->getProductsByCategoryData($request);

            case 'products-performance':
                return $this->getProductsPerformanceData($request);

            // ==================== INVENTORY REPORTS ====================
            case 'inventory-stock-levels':
                return $this->getInventoryStockLevelsData($request);

            case 'inventory-movements':
                return $this->getInventoryMovementsData($request);

            case 'inventory-valuation':
                return $this->getInventoryValuationData($request);

            // ==================== CUSTOMER REPORTS ====================
            case 'customers-index':
                return $this->getCustomersIndexData($request);

            case 'customers-new-vs-returning':
                return $this->getCustomersNewVsReturningData($request);

            case 'customers-lifetime-value':
                return $this->getCustomersLifetimeValueData($request);

            default:
                throw new \Exception('Invalid report type: ' . $reportType);
        }
    }
        /**
     * Get Sales Daily data for export
     */
    private function getSalesDailyData($request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        return [
            'selected_date' => $selectedDate,
            'sales_data' => $this->getDailySalesData($selectedDate),
            'hourly_sales' => $this->getHourlySales($selectedDate),
            'top_products_today' => $this->getTopProductsByDate($selectedDate),
            'comparison' => $this->getDailyComparison($selectedDate),
        ];
    }

    /**
     * Get Sales Weekly data for export
     */
    private function getSalesWeeklyData($request)
    {
        $week = $request->input('week', Carbon::now()->week);
        $year = $request->input('year', Carbon::now()->year);

        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();

        return [
            'week_number' => $week,
            'year' => $year,
            'start_date' => $startOfWeek,
            'end_date' => $endOfWeek,
            'sales_data' => $this->getWeeklySalesData($startOfWeek, $endOfWeek),
            'daily_breakdown' => $this->getDailyBreakdown($startOfWeek, $endOfWeek),
            'top_products_week' => $this->getTopProductsByDateRange($startOfWeek, $endOfWeek),
            'comparison' => $this->getWeeklyComparison($startOfWeek),
        ];
    }

    /**
     * Get Sales Monthly data for export
     */
    private function getSalesMonthlyData($request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        return [
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
    }

    /**
     * Get Sales Yearly data for export
     */
    private function getSalesYearlyData($request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
        $endOfYear = Carbon::create($year, 12, 31)->endOfYear();

        return [
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
    }

    /**
     * Get Sales Custom Range data for export
     */
    private function getSalesCustomRangeData($request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => $start->diffInDays($end) + 1,
            'sales_data' => $this->getCustomRangeSalesData($start, $end),
            'daily_breakdown' => $this->getDailyBreakdown($start, $end),
            'top_products' => $this->getTopProductsByDateRange($start, $end, 20),
            'category_breakdown' => $this->getCategorySales($start, $end),
            'customer_analysis' => $this->getCustomerAnalysis($start, $end),
        ];
    }
    /**
     * Generate Sales Daily CSV
     */
    private function generateSalesDailyCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Daily Sales Report']);
        fputcsv($output, ['Date: ' . $data['selected_date']->format('l, F d, Y')]);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Summary
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Orders', number_format($data['sales_data']['total_orders'])]);
        fputcsv($output, ['Total Revenue', store_currency_symbol() . number_format($data['sales_data']['total_revenue'], 2)]);
        fputcsv($output, ['Avg Order Value', store_currency_symbol() . number_format($data['sales_data']['avg_order_value'], 2)]);
        fputcsv($output, ['Items Sold', number_format($data['sales_data']['items_sold'])]);
        fputcsv($output, []);

        // Hourly Breakdown
        fputcsv($output, ['Hourly Sales Breakdown']);
        fputcsv($output, ['Hour', 'Orders', 'Revenue']);
        foreach ($data['hourly_sales'] as $hour) {
            fputcsv($output, [
                $hour['hour_label'],
                number_format($hour['order_count']),
                number_format($hour['revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Top Products
        fputcsv($output, ['Top Selling Products']);
        fputcsv($output, ['Product', 'SKU', 'Units Sold', 'Revenue']);
        foreach ($data['top_products_today'] as $product) {
            fputcsv($output, [
                $product->name,
                $product->sku,
                number_format($product->total_sold),
                number_format($product->total_revenue, 2)
            ]);
        }
    }

    /**
     * Generate Sales Weekly CSV
     */
    private function generateSalesWeeklyCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Weekly Sales Report']);
        fputcsv($output, ['Week: ' . $data['week_number'] . ', ' . $data['year']]);
        fputcsv($output, ['Period: ' . $data['start_date']->format('M d') . ' - ' . $data['end_date']->format('M d, Y')]);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Summary
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Orders', number_format($data['sales_data']['total_orders'])]);
        fputcsv($output, ['Total Revenue', store_currency_symbol() . number_format($data['sales_data']['total_revenue'], 2)]);
        fputcsv($output, ['Avg Order Value', store_currency_symbol() . number_format($data['sales_data']['avg_order_value'], 2)]);
        fputcsv($output, ['Avg Daily Revenue', store_currency_symbol() . number_format($data['sales_data']['avg_daily_revenue'], 2)]);
        fputcsv($output, []);

        // Daily Breakdown
        fputcsv($output, ['Daily Breakdown']);
        fputcsv($output, ['Date', 'Day', 'Orders', 'Revenue', 'Avg Order Value']);
        foreach ($data['daily_breakdown'] as $day) {
            fputcsv($output, [
                $day['date_label'],
                $day['day_name'],
                number_format($day['order_count']),
                number_format($day['revenue'], 2),
                number_format($day['avg_order_value'], 2)
            ]);
        }
        fputcsv($output, []);

        // Top Products
        fputcsv($output, ['Top Selling Products']);
        fputcsv($output, ['Product', 'SKU', 'Category', 'Units Sold', 'Revenue', 'Avg Price']);
        foreach ($data['top_products_week'] as $product) {
            fputcsv($output, [
                $product->name,
                $product->sku,
                $product->category_name ?? 'N/A',
                number_format($product->total_sold),
                number_format($product->total_revenue, 2),
                number_format($product->avg_price, 2)
            ]);
        }
    }

    /**
     * Generate Sales Monthly CSV
     */
    private function generateSalesMonthlyCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Monthly Sales Report']);
        fputcsv($output, ['Month: ' . $data['month_name'] . ' ' . $data['year']]);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Summary
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Orders', number_format($data['sales_data']['total_orders'])]);
        fputcsv($output, ['Total Revenue', store_currency_symbol() . number_format($data['sales_data']['total_revenue'], 2)]);
        fputcsv($output, ['Avg Order Value', store_currency_symbol() . number_format($data['sales_data']['avg_order_value'], 2)]);
        fputcsv($output, ['Avg Daily Revenue', store_currency_symbol() . number_format($data['sales_data']['avg_daily_revenue'], 2)]);
        fputcsv($output, []);

        // Weekly Breakdown
        fputcsv($output, ['Weekly Breakdown']);
        fputcsv($output, ['Week', 'Orders', 'Revenue']);
        foreach ($data['weekly_breakdown'] as $week) {
            fputcsv($output, [
                $week['label'],
                number_format($week['order_count']),
                number_format($week['revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Category Breakdown
        fputcsv($output, ['Sales by Category']);
        fputcsv($output, ['Category', 'Units Sold', 'Revenue']);
        foreach ($data['category_breakdown'] as $category) {
            fputcsv($output, [
                $category['category'],
                number_format($category['units_sold']),
                number_format($category['revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Top Products
        fputcsv($output, ['Top Selling Products']);
        fputcsv($output, ['Rank', 'Product', 'SKU', 'Category', 'Units Sold', 'Revenue']);
        foreach ($data['top_products_month'] as $index => $product) {
            fputcsv($output, [
                $index + 1,
                $product->name,
                $product->sku,
                $product->category_name ?? 'N/A',
                number_format($product->total_sold),
                number_format($product->total_revenue, 2)
            ]);
        }
    }

    /**
     * Generate Sales Yearly CSV
     */
    private function generateSalesYearlyCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Yearly Sales Report']);
        fputcsv($output, ['Year: ' . $data['year']]);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Summary
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Orders', number_format($data['sales_data']['total_orders'])]);
        fputcsv($output, ['Total Revenue', store_currency_symbol() . number_format($data['sales_data']['total_revenue'], 2)]);
        fputcsv($output, ['Avg Order Value', store_currency_symbol() . number_format($data['sales_data']['avg_order_value'], 2)]);
        fputcsv($output, ['Avg Monthly Revenue', store_currency_symbol() . number_format($data['sales_data']['avg_monthly_revenue'], 2)]);
        fputcsv($output, []);

        // Monthly Breakdown
        fputcsv($output, ['Monthly Breakdown']);
        fputcsv($output, ['Month', 'Orders', 'Revenue']);
        foreach ($data['monthly_breakdown'] as $month) {
            fputcsv($output, [
                $month['month_name'],
                number_format($month['order_count']),
                number_format($month['revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Quarterly Breakdown
        fputcsv($output, ['Quarterly Breakdown']);
        fputcsv($output, ['Quarter', 'Orders', 'Revenue']);
        foreach ($data['quarterly_breakdown'] as $quarter) {
            fputcsv($output, [
                $quarter['label'],
                number_format($quarter['order_count']),
                number_format($quarter['revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Category Breakdown
        fputcsv($output, ['Sales by Category']);
        fputcsv($output, ['Category', 'Units Sold', 'Revenue']);
        foreach ($data['category_breakdown'] as $category) {
            fputcsv($output, [
                $category['category'],
                number_format($category['units_sold']),
                number_format($category['revenue'], 2)
            ]);
        }
    }

    /**
     * Generate Sales Custom Range CSV
     */
    private function generateSalesCustomRangeCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Custom Range Sales Report']);
        fputcsv($output, ['Period: ' . $data['start_date']->format('M d, Y') . ' - ' . $data['end_date']->format('M d, Y')]);
        fputcsv($output, ['Days: ' . $data['days_count']]);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Summary
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Orders', number_format($data['sales_data']['total_orders'])]);
        fputcsv($output, ['Total Revenue', store_currency_symbol() . number_format($data['sales_data']['total_revenue'], 2)]);
        fputcsv($output, ['Avg Order Value', store_currency_symbol() . number_format($data['sales_data']['avg_order_value'], 2)]);
        fputcsv($output, ['Avg Daily Revenue', store_currency_symbol() . number_format($data['sales_data']['avg_daily_revenue'], 2)]);
        fputcsv($output, []);

        // Daily Breakdown
        fputcsv($output, ['Daily Breakdown']);
        fputcsv($output, ['Date', 'Day', 'Orders', 'Revenue']);
        foreach ($data['daily_breakdown'] as $day) {
            fputcsv($output, [
                $day['date_label'],
                $day['day_name'],
                number_format($day['order_count']),
                number_format($day['revenue'], 2)
            ]);
        }
        fputcsv($output, []);

        // Customer Analysis
        fputcsv($output, ['Customer Analysis']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Customers', number_format($data['customer_analysis']['total_customers'])]);
        fputcsv($output, ['New Customers', number_format($data['customer_analysis']['new_customers'])]);
        fputcsv($output, ['Returning Customers', number_format($data['customer_analysis']['returning_customers'])]);
        fputcsv($output, []);

        // Category Breakdown
        fputcsv($output, ['Sales by Category']);
        fputcsv($output, ['Category', 'Units Sold', 'Revenue']);
        foreach ($data['category_breakdown'] as $category) {
            fputcsv($output, [
                $category['category'],
                number_format($category['units_sold']),
                number_format($category['revenue'], 2)
            ]);
        }
    }

    /**
     * Get report view name
     */
    private function getReportView($reportType)
    {
        $viewMap = [
            // ==================== SALES REPORTS ====================
            'sales-daily' => 'sales-daily-pdf',
            'sales-weekly' => 'sales-weekly-pdf',
            'sales-monthly' => 'sales-monthly-pdf',
            'sales-yearly' => 'sales-yearly-pdf',
            'sales-custom-range' => 'sales-custom-range-pdf',

            // ==================== REVENUE REPORTS ====================
            'revenue-index' => 'revenue-index-pdf',
            'revenue-by-category' => 'revenue-by-category-pdf',
            'revenue-by-product' => 'revenue-by-product-pdf',

            // ==================== PRODUCT REPORTS ====================
            'products-top-selling' => 'products-top-selling-pdf',
            'products-by-category' => 'products-by-category-pdf',
            'products-performance' => 'products-performance-pdf',

            // ==================== INVENTORY REPORTS ====================
            'inventory-index' => 'inventory-index-pdf',
            'inventory-stock-levels' => 'inventory-stock-levels-pdf',
            'inventory-movement' => 'inventory-movement-pdf',
            'inventory-valuation' => 'inventory-valuation-pdf',
            'inventory-alerts' => 'inventory-alerts-pdf',

            // ==================== CUSTOMER REPORTS ====================
            'customers-index' => 'customers-index-pdf',
            'customers-new-vs-returning' => 'customers-new-vs-returning-pdf',
            'customers-lifetime-value' => 'customers-lifetime-value-pdf',
        ];

        if (!isset($viewMap[$reportType])) {
            \Log::error('Unknown report type for PDF', ['reportType' => $reportType]);
            throw new \Exception("Unknown report type: {$reportType}");
        }

        return $viewMap[$reportType];
    }

    /**
     * Generate filename for export
     */
    private function generateFilename($reportType, $extension)
    {
        $reportName = str_replace('-', '_', $reportType);
        $timestamp = now()->format('Y-m-d_His');
        return "{$reportName}_{$timestamp}.{$extension}";
    }

    /**
     * Get export class for Excel
     */
    private function getExportClass($reportType, $request)
    {
        switch ($reportType) {
            case 'revenue-by-category':
                return new \App\Exports\RevenueByCategoryExport($request);

            case 'revenue-by-product':
                return new \App\Exports\RevenueByProductExport($request);

            case 'products-top-selling':
                return new \App\Exports\ProductsTopSellingExport($request);

            case 'products-by-category':
                return new \App\Exports\ProductsByCategoryExport($request);

            case 'products-performance':
                return new \App\Exports\ProductsPerformanceExport($request);

            case 'inventory-stock-levels':
                return new \App\Exports\InventoryStockLevelsExport($request);

            case 'inventory-movements':
                return new \App\Exports\InventoryMovementsExport($request);

            case 'inventory-valuation':
                return new \App\Exports\InventoryValuationExport($request);

            case 'customers-index':
                return new \App\Exports\CustomersIndexExport($request);

            case 'customers-new-vs-returning':
                return new \App\Exports\CustomersNewVsReturningExport($request);

            case 'customers-lifetime-value':
                return new \App\Exports\CustomersLifetimeValueExport($request);

            default:
                throw new \Exception('Invalid report type');
        }
    }

    /**
     * Get report data for CSV
     */
    private function getReportDataForCsv($reportType, $request)
    {
        // Reuse the same data retrieval methods
        return $this->getReportData($reportType, $request);
    }

    /**
     * Generate CSV content
     */
    private function generateCsvContent($reportType, $data)
    {
        $output = fopen('php://temp', 'r+');

        switch ($reportType) {
            // Sales Reports
            case 'sales-daily':
                $this->generateSalesDailyCsv($output, $data);
                break;
            case 'sales-weekly':
                $this->generateSalesWeeklyCsv($output, $data);
                break;
            case 'sales-monthly':
                $this->generateSalesMonthlyCsv($output, $data);
                break;
            case 'sales-yearly':
                $this->generateSalesYearlyCsv($output, $data);
                break;
            case 'sales-custom-range':
                $this->generateSalesCustomRangeCsv($output, $data);
                break;

            // Revenue Reports
            case 'revenue-by-category':
                $this->generateRevenueByCategoryCsv($output, $data);
                break;
            case 'revenue-by-product':
                $this->generateRevenueByProductCsv($output, $data);
                break;

            // Product Reports
            case 'products-top-selling':
                $this->generateProductsTopSellingCsv($output, $data);
                break;
            case 'products-by-category':
                $this->generateProductsByCategoryCsv($output, $data);
                break;
            case 'products-performance':
                $this->generateProductsPerformanceCsv($output, $data);
                break;

            // Inventory Reports
            case 'inventory-stock-levels':
                $this->generateInventoryStockLevelsCsv($output, $data);
                break;
            case 'inventory-movements':
                $this->generateInventoryMovementsCsv($output, $data);
                break;
            case 'inventory-valuation':
                $this->generateInventoryValuationCsv($output, $data);
                break;

            // Customer Reports
            case 'customers-index':
                $this->generateCustomersIndexCsv($output, $data);
                break;
            case 'customers-new-vs-returning':
                $this->generateCustomersNewVsReturningCsv($output, $data);
                break;
            case 'customers-lifetime-value':
                $this->generateCustomersLifetimeValueCsv($output, $data);
                break;
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    // ==================== DATA RETRIEVAL METHODS ====================

    /**
     * Get Revenue by Category data
     */
    private function getRevenueByCategoryData($request)
    {
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $categories = ProductsCategories::with(['products' => function($query) use ($start_date, $end_date) {
            $query->whereHas('orderItems.order', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date])
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            });
        }])->get();

        $category_data = [];
        $total_revenue = 0;

        foreach ($categories as $category) {
            $revenue = OrderItem::whereHas('order', function($query) use ($start_date, $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date])
                    ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            })
            ->whereHas('product', function($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->sum(DB::raw('quantity * price'));

            if ($revenue > 0) {
                $category_data[] = [
                    'category' => $category,
                    'revenue' => $revenue,
                    'product_count' => $category->products->count()
                ];
                $total_revenue += $revenue;
            }
        }

        return [
            'categories' => collect($category_data)->sortByDesc('revenue'),
            'total_revenue' => $total_revenue,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }

    /**
     * Get Revenue by Product data
     */
    private function getRevenueByProductData($request)
    {
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $products = Product::select(
                'products.*',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('products.id')
            ->orderByDesc('total_revenue')
            ->get();

        return [
            'products' => $products,
            'total_revenue' => $products->sum('total_revenue'),
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }

    /**
     * Get Products Top Selling data
     */
    private function getProductsTopSellingData($request)
    {
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $products = Product::select(
                'products.*',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('products.id')
            ->orderByDesc('units_sold')
            ->limit(50)
            ->get();

        return [
            'products' => $products,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }

    /**
     * Get Products by Category data
     */
    private function getProductsByCategoryData($request)
    {
        $categories = ProductsCategories::with(['products' => function($query) {
            $query->orderBy('name');
        }])->orderBy('title')->get();

        return [
            'categories' => $categories,
            'total_products' => Product::count()
        ];
    }

    /**
     * Get Products Performance data
     */
    private function getProductsPerformanceData($request)
    {
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $products = Product::select(
                'products.*',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('AVG(order_items.price) as avg_price')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) use ($start_date, $end_date) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [$start_date, $end_date])
                    ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            })
            ->groupBy('products.id')
            ->get();

        return [
            'products' => $products,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }

    /**
     * Get Inventory Stock Levels data
     */
    private function getInventoryStockLevelsData($request)
    {
        $products = Product::with('category')->where('stock_quantity', '>', 0)->get();

        return [
            'products' => $products,
            'total_valuation' => $products->sum(function($p) {
                return $p->stock_quantity * $p->price;
            })
        ];
    }

    /**
     * Get Inventory Movements data
     */
    private function getInventoryMovementsData($request)
    {
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $movements = InventoryMovement::with(['product', 'creator', 'warehouse'])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'movements' => $movements,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }

    /**
     * Get Inventory Valuation data
     */
    private function getInventoryValuationData($request)
    {
        $products = Product::with('category')
            ->where('stock_quantity', '>', 0)
            ->get();

        $total_valuation = $products->sum(function($product) {
            return $product->stock_quantity * $product->price;
        });

        return [
            'products' => $products,
            'total_valuation' => $total_valuation
        ];
    }

    /**
     * Get Customers Index data
     */
    private function getCustomersIndexData($request)
    {
        $customers = Customer::orderBySpent('desc')->get();

        return [
            'customers' => $customers,
            'total_customers' => $customers->count(),
            'average_ltv' => $customers->avg('total_spent')
        ];
    }

    /**
     * Get Customers New vs Returning data
     */
    private function getCustomersNewVsReturningData($request)
    {
        $start_date = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $orders = Order::with('customer')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->get();

        return [
            'orders' => $orders,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }

    /**
     * Get Customers Lifetime Value data
     */
    private function getCustomersLifetimeValueData($request)
    {
        $customers = Customer::orderBySpent('desc')->get();

        return [
            'customers' => $customers,
            'average_ltv' => $customers->avg('total_spent'),
            'total_ltv' => $customers->sum('total_spent')
        ];
    }

    // ==================== CSV GENERATION METHODS ====================

    /**
     * Generate Revenue by Category CSV
     */
    private function generateRevenueByCategoryCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Revenue by Category Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Period: ' . $data['start_date']->format('Y-m-d') . ' to ' . $data['end_date']->format('Y-m-d')]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Category', 'Revenue', 'Product Count', 'Percentage']);

        // Data rows
        foreach ($data['categories'] as $item) {
            $percentage = $data['total_revenue'] > 0
                ? ($item['revenue'] / $data['total_revenue']) * 100
                : 0;

            fputcsv($output, [
                $item['category']->title,
                number_format($item['revenue'], 2),
                $item['product_count'],
                number_format($percentage, 2) . '%'
            ]);
        }

        // Total
        fputcsv($output, []);
        fputcsv($output, ['TOTAL', number_format($data['total_revenue'], 2), '', '100%']);
    }

    /**
     * Generate Revenue by Product CSV
     */
    private function generateRevenueByProductCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Revenue by Product Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Product', 'SKU', 'Units Sold', 'Revenue', 'Avg Price']);

        // Data rows
        foreach ($data['products'] as $product) {
            $avg_price = $product->total_quantity > 0
                ? $product->total_revenue / $product->total_quantity
                : 0;

            fputcsv($output, [
                $product->name,
                $product->sku,
                number_format($product->total_quantity),
                number_format($product->total_revenue, 2),
                number_format($avg_price, 2)
            ]);
        }
    }

    /**
     * Generate Products Top Selling CSV
     */
    private function generateProductsTopSellingCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Top Selling Products Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Rank', 'Product', 'SKU', 'Units Sold', 'Revenue', 'Orders']);

        // Data rows
        foreach ($data['products'] as $index => $product) {
            fputcsv($output, [
                $index + 1,
                $product->name,
                $product->sku,
                number_format($product->units_sold),
                number_format($product->revenue, 2),
                number_format($product->order_count)
            ]);
        }
    }

    /**
     * Generate Products by Category CSV
     */
    private function generateProductsByCategoryCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Products by Category Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Data rows
        foreach ($data['categories'] as $category) {
            fputcsv($output, ['Category: ' . $category->title]);
            fputcsv($output, ['Product', 'SKU', 'Price', 'Stock']);

            foreach ($category->products as $product) {
                fputcsv($output, [
                    $product->name,
                    $product->sku,
                    number_format($product->price, 2),
                    number_format($product->stock_quantity)
                ]);
            }

            fputcsv($output, []);
        }
    }

    /**
     * Generate Products Performance CSV
     */
    private function generateProductsPerformanceCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Product Performance Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Product', 'SKU', 'Units Sold', 'Revenue', 'Orders', 'Performance Score']);

        // Data rows
        foreach ($data['products'] as $product) {
            // Calculate performance score (0-100)
            $max_revenue = $data['products']->max('revenue');
            $max_units = $data['products']->max('units_sold');

            $revenue_score = $max_revenue > 0 ? ($product->revenue / $max_revenue) * 50 : 0;
            $units_score = $max_units > 0 ? ($product->units_sold / $max_units) * 50 : 0;
            $performance_score = $revenue_score + $units_score;

            fputcsv($output, [
                $product->name,
                $product->sku,
                number_format($product->units_sold ?? 0),
                number_format($product->revenue ?? 0, 2),
                number_format($product->order_count ?? 0),
                number_format($performance_score, 1)
            ]);
        }
    }

    /**
     * Generate Inventory Stock Levels CSV
     */
    private function generateInventoryStockLevelsCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Inventory Stock Levels Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Product', 'SKU', 'Category', 'Stock Quantity', 'Unit Price', 'Total Value', 'Status']);

        // Data rows
        foreach ($data['products'] as $product) {
            $total_value = $product->stock_quantity * $product->price;

            if ($product->stock_quantity <= 0) {
                $status = 'Out of Stock';
            } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
                $status = 'Low Stock';
            } else {
                $status = 'In Stock';
            }

            fputcsv($output, [
                $product->name,
                $product->sku,
                $product->category->title ?? 'N/A',
                number_format($product->stock_quantity),
                number_format($product->price, 2),
                number_format($total_value, 2),
                $status
            ]);
        }
    }

    /**
     * Generate Inventory Movements CSV
     */
    private function generateInventoryMovementsCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Inventory Movements Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Period: ' . $data['start_date']->format('Y-m-d') . ' to ' . $data['end_date']->format('Y-m-d')]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Date', 'Product', 'Type', 'Quantity', 'Before', 'After', 'Reference', 'Notes']);

        // Data rows
        foreach ($data['movements'] as $movement) {
            fputcsv($output, [
                $movement->created_at->format('Y-m-d H:i:s'),
                $movement->product->name ?? 'N/A',
                $movement->getTypeLabel(),
                $movement->getFormattedQuantity(),
                number_format($movement->previous_quantity),
                number_format($movement->new_quantity),
                $movement->reference_type ? $movement->reference_type . ' #' . $movement->reference_id : 'N/A',
                $movement->notes ?? ''
            ]);
        }
    }

    /**
     * Generate Inventory Valuation CSV
     */
    private function generateInventoryValuationCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Inventory Valuation Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Total Inventory Value: ' . store_currency_symbol() . number_format($data['total_valuation'], 2)]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Product', 'SKU', 'Category', 'Stock Qty', 'Unit Cost', 'Total Value', '% of Total']);

        // Data rows
        foreach ($data['products'] as $product) {
            $product_value = $product->stock_quantity * $product->price;
            $percentage = $data['total_valuation'] > 0 ? ($product_value / $data['total_valuation']) * 100 : 0;

            fputcsv($output, [
                $product->name,
                $product->sku,
                $product->category->title ?? 'N/A',
                number_format($product->stock_quantity),
                number_format($product->price, 2),
                number_format($product_value, 2),
                number_format($percentage, 2) . '%'
            ]);
        }

        // Total
        fputcsv($output, []);
        fputcsv($output, ['TOTAL', '', '', '', '', number_format($data['total_valuation'], 2), '100%']);
    }

    /**
     * Generate Customers Index CSV
     */
    private function generateCustomersIndexCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Customer Analytics Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Total Customers: ' . number_format($data['total_customers'])]);
        fputcsv($output, ['Average LTV: ' . store_currency_symbol() . number_format($data['average_ltv'], 2)]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Customer', 'Email', 'Orders', 'Total Spent', 'Avg Order', 'First Order', 'Last Order', 'Segment']);

        // Data rows
        foreach ($data['customers'] as $customer) {
            $avg_order = $customer->total_orders > 0 ? $customer->total_spent / $customer->total_orders : 0;
            $segment = $customer->getSegment();

            fputcsv($output, [
                $customer->getFullName(),
                $customer->email,
                number_format($customer->total_orders),
                number_format($customer->total_spent, 2),
                number_format($avg_order, 2),
                $customer->first_order_at ? $customer->first_order_at->format('Y-m-d') : 'N/A',
                $customer->last_order_at ? $customer->last_order_at->format('Y-m-d') : 'Never',
                $segment
            ]);
        }
    }

    /**
     * Generate Customers New vs Returning CSV
     */
    private function generateCustomersNewVsReturningCsv($output, $data)
    {
        // Header
        fputcsv($output, ['New vs Returning Customers Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Period: ' . $data['start_date']->format('Y-m-d') . ' to ' . $data['end_date']->format('Y-m-d')]);
        fputcsv($output, []);

        // Process orders
        $new_count = 0;
        $returning_count = 0;
        $new_revenue = 0;
        $returning_revenue = 0;

        foreach ($data['orders'] as $order) {
            if ($order->customer) {
                $customer_orders_before = Order::where('customer_id', $order->customer_id)
                    ->where('created_at', '<', $order->created_at)
                    ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                    ->count();

                if ($customer_orders_before == 0) {
                    $new_count++;
                    $new_revenue += $order->total_amount;
                } else {
                    $returning_count++;
                    $returning_revenue += $order->total_amount;
                }
            }
        }

        // Summary
        fputcsv($output, ['Customer Type', 'Count', 'Revenue', 'Avg Order Value']);
        fputcsv($output, [
            'New Customers',
            number_format($new_count),
            number_format($new_revenue, 2),
            $new_count > 0 ? number_format($new_revenue / $new_count, 2) : '0.00'
        ]);
        fputcsv($output, [
            'Returning Customers',
            number_format($returning_count),
            number_format($returning_revenue, 2),
            $returning_count > 0 ? number_format($returning_revenue / $returning_count, 2) : '0.00'
        ]);
    }

    /**
     * Generate Customers Lifetime Value CSV
     */
    private function generateCustomersLifetimeValueCsv($output, $data)
    {
        // Header
        fputcsv($output, ['Customer Lifetime Value Report']);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, ['Average LTV: ' . store_currency_symbol() . number_format($data['average_ltv'], 2)]);
        fputcsv($output, ['Total LTV: ' . store_currency_symbol() . number_format($data['total_ltv'], 2)]);
        fputcsv($output, []);

        // Column headers
        fputcsv($output, ['Rank', 'Customer', 'Email', 'Lifetime Value', 'Orders', 'Avg Order', 'Segment']);

        // Data rows
        foreach ($data['customers'] as $index => $customer) {
            $avg_order = $customer->total_orders > 0 ? $customer->total_spent / $customer->total_orders : 0;

            // Determine segment
            if ($customer->total_spent >= 5000) {
                $segment = 'Platinum';
            } elseif ($customer->total_spent >= 2000) {
                $segment = 'Gold';
            } elseif ($customer->total_spent >= 500) {
                $segment = 'Silver';
            } else {
                $segment = 'Bronze';
            }

            fputcsv($output, [
                $index + 1,
                $customer->getFullName(),
                $customer->email,
                number_format($customer->total_spent, 2),
                number_format($customer->total_orders),
                number_format($avg_order, 2),
                $segment
            ]);
        }
    }
}
