<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use DB;
// ==================== PRODUCTS PERFORMANCE EXPORT ====================

/**
 * Products Performance Export
 * File: app/Exports/ProductsPerformanceExport.php
 */
class ProductsPerformanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $start_date = $this->request->start_date
            ? \Carbon\Carbon::parse($this->request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end_date = $this->request->end_date
            ? \Carbon\Carbon::parse($this->request->end_date)->endOfDay()
            : now()->endOfDay();

        return \App\Models\Product::select(
                'products.*',
                \DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                \DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as revenue'),
                \DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                \DB::raw('AVG(order_items.price) as avg_price')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) use ($start_date, $end_date) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$start_date, $end_date])
                     ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            })
            ->groupBy('products.id')
            ->orderByDesc('revenue')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Category',
            'Units Sold',
            'Revenue',
            'Orders',
            'Avg Price',
            'Current Stock',
            'Performance Score',
            'Grade'
        ];
    }

    public function map($product): array
    {
        // Calculate performance score (0-100)
        $all_products = $this->collection();
        $max_revenue = $all_products->max('revenue') ?: 1;
        $max_units = $all_products->max('units_sold') ?: 1;
        $max_orders = $all_products->max('order_count') ?: 1;

        $revenue_score = ($product->revenue / $max_revenue) * 40;
        $units_score = ($product->units_sold / $max_units) * 35;
        $orders_score = ($product->order_count / $max_orders) * 25;

        $performance_score = $revenue_score + $units_score + $orders_score;

        // Determine grade
        if ($performance_score >= 80) {
            $grade = 'A - Excellent';
        } elseif ($performance_score >= 60) {
            $grade = 'B - Good';
        } elseif ($performance_score >= 40) {
            $grade = 'C - Average';
        } elseif ($performance_score >= 20) {
            $grade = 'D - Below Average';
        } else {
            $grade = 'F - Poor';
        }

        return [
            $product->name,
            $product->sku,
            $product->category->title ?? 'N/A',
            number_format($product->units_sold ?? 0),
            store_currency_symbol() . number_format($product->revenue ?? 0, 2),
            number_format($product->order_count ?? 0),
            store_currency_symbol() . number_format($product->avg_price ?? 0, 2),
            number_format($product->stock_quantity),
            number_format($performance_score, 1),
            $grade
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Product Performance';
    }
}
