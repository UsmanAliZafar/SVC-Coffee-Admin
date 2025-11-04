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

// ==================== PRODUCTS BY CATEGORY EXPORT ====================

/**
 * Products by Category Export
 * File: app/Exports/ProductsByCategoryExport.php
 */
class ProductsByCategoryExport implements WithMultipleSheets
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Create multiple sheets - one per category
     */
    public function sheets(): array
    {
        $sheets = [];

        // Get all categories with products
        $categories = \App\Models\ProductsCategories::with(['products' => function($query) {
            $query->orderBy('name');
        }])->orderBy('title')->get();

        // Create summary sheet
        $sheets[] = new ProductsByCategorySummarySheet($categories);

        // Create a sheet for each category
        foreach ($categories as $category) {
            if ($category->products->count() > 0) {
                $sheets[] = new ProductsByCategoryDetailSheet($category);
            }
        }

        return $sheets;
    }
}

/**
 * Summary Sheet for Products by Category
 */
class ProductsByCategorySummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $categories;

    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    public function collection()
    {
        return $this->categories;
    }

    public function headings(): array
    {
        return [
            'Category Name',
            'Product Count',
            'Total Stock Value',
            'Average Price',
            'Total Units in Stock'
        ];
    }

    public function map($category): array
    {
        $products = $category->products;
        $total_stock_value = $products->sum(function($p) {
            return $p->stock_quantity * $p->price;
        });
        $avg_price = $products->avg('price');
        $total_units = $products->sum('stock_quantity');

        return [
            $category->title,
            $products->count(),
            store_currency_symbol() . number_format($total_stock_value, 2),
            store_currency_symbol() . number_format($avg_price, 2),
            number_format($total_units)
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
        return 'Summary';
    }
}

/**
 * Detail Sheet for each Category
 */
class ProductsByCategoryDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $category;

    public function __construct($category)
    {
        $this->category = $category;
    }

    public function collection()
    {
        return $this->category->products;
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Price',
            'Stock Quantity',
            'Stock Value',
            'Status'
        ];
    }

    public function map($product): array
    {
        $stock_value = $product->stock_quantity * $product->price;

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
            store_currency_symbol() . number_format($product->price, 2),
            number_format($product->stock_quantity),
            store_currency_symbol() . number_format($stock_value, 2),
            $status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B914C']]],
        ];
    }

    public function title(): string
    {
        // Limit sheet name to 31 characters (Excel limit)
        return substr($this->category->title, 0, 31);
    }
}
