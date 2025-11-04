<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ==================== REVENUE EXPORTS ====================

/**
 * Revenue by Category Export
 * File: app/Exports/RevenueByCategoryExport.php
 */
class RevenueByCategoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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

        $categories = \App\Models\ProductsCategories::with('products')->get();

        $data = collect();

        foreach ($categories as $category) {
            $revenue = \App\Models\OrderItem::whereHas('order', function($query) use ($start_date, $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date])
                      ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING']);
            })
            ->whereHas('product', function($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->sum(\DB::raw('quantity * price'));

            if ($revenue > 0) {
                $data->push([
                    'category' => $category,
                    'revenue' => $revenue,
                    'product_count' => $category->products->count()
                ]);
            }
        }

        return $data->sortByDesc('revenue');
    }

    public function headings(): array
    {
        return [
            'Category',
            'Revenue',
            'Product Count',
            'Percentage'
        ];
    }

    public function map($row): array
    {
        $total_revenue = $this->collection()->sum('revenue');
        $percentage = $total_revenue > 0 ? ($row['revenue'] / $total_revenue) * 100 : 0;

        return [
            $row['category']->title,
            store_currency_symbol() . number_format($row['revenue'], 2),
            $row['product_count'],
            number_format($percentage, 2) . '%'
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
        return 'Revenue by Category';
    }
}

/**
 * Revenue by Product Export
 * File: app/Exports/RevenueByProductExport.php
 */
class RevenueByProductExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
                \DB::raw('SUM(order_items.quantity) as total_quantity'),
                \DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('products.id')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Units Sold',
            'Revenue',
            'Average Price'
        ];
    }

    public function map($product): array
    {
        $avg_price = $product->total_quantity > 0 ? $product->total_revenue / $product->total_quantity : 0;

        return [
            $product->name,
            $product->sku,
            number_format($product->total_quantity),
            store_currency_symbol() . number_format($product->total_revenue, 2),
            store_currency_symbol() . number_format($avg_price, 2)
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
        return 'Revenue by Product';
    }
}

// ==================== PRODUCT EXPORTS ====================

/**
 * Products Top Selling Export
 * File: app/Exports/ProductsTopSellingExport.php
 */
class ProductsTopSellingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
                \DB::raw('SUM(order_items.quantity) as units_sold'),
                \DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                \DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->whereIn('orders.status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->groupBy('products.id')
            ->orderByDesc('units_sold')
            ->limit(50)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Product Name',
            'SKU',
            'Units Sold',
            'Revenue',
            'Orders',
            'Avg per Order'
        ];
    }

    public function map($product): array
    {
        static $rank = 0;
        $rank++;

        $avg_per_order = $product->order_count > 0 ? $product->units_sold / $product->order_count : 0;

        return [
            $rank,
            $product->name,
            $product->sku,
            number_format($product->units_sold),
            store_currency_symbol() . number_format($product->revenue, 2),
            number_format($product->order_count),
            number_format($avg_per_order, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A2:A4' => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD700']]],
        ];
    }

    public function title(): string
    {
        return 'Top Selling Products';
    }
}

// ==================== INVENTORY EXPORTS ====================

/**
 * Inventory Stock Levels Export
 * File: app/Exports/InventoryStockLevelsExport.php
 */
class InventoryStockLevelsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return \App\Models\Product::with('category')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Category',
            'Stock Quantity',
            'Unit Price',
            'Total Value',
            'Status'
        ];
    }

    public function map($product): array
    {
        $total_value = $product->stock_quantity * $product->price;

        if ($product->stock_quantity <= 0) {
            $status = 'Out of Stock';
        } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
            $status = 'Low Stock';
        } else {
            $status = 'In Stock';
        }

        return [
            $product->name,
            $product->sku,
            $product->category->title ?? 'N/A',
            number_format($product->stock_quantity),
            store_currency_symbol() . number_format($product->price, 2),
            store_currency_symbol() . number_format($total_value, 2),
            $status
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
        return 'Stock Levels';
    }
}

/**
 * Inventory Movements Export
 * File: app/Exports/InventoryMovementsExport.php
 */
class InventoryMovementsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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

        return \App\Models\InventoryMovement::with(['product', 'creator', 'warehouse'])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Product',
            'Type',
            'Quantity',
            'Before',
            'After',
            'Reference',
            'User',
            'Notes'
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->created_at->format('Y-m-d H:i:s'),
            $movement->product->name ?? 'N/A',
            $movement->getTypeLabel(),
            $movement->getFormattedQuantity(),
            number_format($movement->previous_quantity),
            number_format($movement->new_quantity),
            $movement->reference_type ? $movement->reference_type . ' #' . $movement->reference_id : 'N/A',
            $movement->creator->name ?? 'System',
            $movement->notes ?? ''
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
        return 'Inventory Movements';
    }
}

// ==================== CUSTOMER EXPORTS ====================

/**
 * Customers Index Export
 * File: app/Exports/CustomersIndexExport.php
 */
class CustomersIndexExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return \App\Models\Customer::orderBySpent('desc')->get();
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Email',
            'Phone',
            'Orders',
            'Total Spent',
            'Avg Order',
            'First Order',
            'Last Order',
            'Segment'
        ];
    }

    public function map($customer): array
    {
        $avg_order = $customer->total_orders > 0 ? $customer->total_spent / $customer->total_orders : 0;

        return [
            $customer->getFullName(),
            $customer->email,
            $customer->phone ?? 'N/A',
            number_format($customer->total_orders),
            store_currency_symbol() . number_format($customer->total_spent, 2),
            store_currency_symbol() . number_format($avg_order, 2),
            $customer->first_order_at ? $customer->first_order_at->format('Y-m-d') : 'N/A',
            $customer->last_order_at ? $customer->last_order_at->format('Y-m-d') : 'Never',
            $customer->getSegment()
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
        return 'Customer Analytics';
    }
}

/**
 * Customers Lifetime Value Export
 * File: app/Exports/CustomersLifetimeValueExport.php
 */
class CustomersLifetimeValueExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return \App\Models\Customer::orderBySpent('desc')->get();
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Customer Name',
            'Email',
            'Lifetime Value',
            'Orders',
            'Avg Order',
            'Segment',
            'Status'
        ];
    }

    public function map($customer): array
    {
        static $rank = 0;
        $rank++;

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

        return [
            $rank,
            $customer->getFullName(),
            $customer->email,
            store_currency_symbol() . number_format($customer->total_spent, 2),
            number_format($customer->total_orders),
            store_currency_symbol() . number_format($avg_order, 2),
            $segment,
            $customer->isActive() ? 'Active' : 'Inactive'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A2:A4' => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD700']]],
        ];
    }

    public function title(): string
    {
        return 'Customer Lifetime Value';
    }
}
