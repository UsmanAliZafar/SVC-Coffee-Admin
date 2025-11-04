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
// ==================== INVENTORY VALUATION EXPORT ====================
/**
 * Inventory Valuation Export
 * File: app/Exports/InventoryValuationExport.php
 */
class InventoryValuationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = \App\Models\Product::with('category')
            ->where('stock_quantity', '>', 0);

        // Apply category filter if present
        if ($this->request->category_id) {
            $query->where('category_id', $this->request->category_id);
        }

        // Apply search filter if present
        if ($this->request->search) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sort = $this->request->sort ?? 'value_desc';

        switch ($sort) {
            case 'value_desc':
                $query->orderByRaw('stock_quantity * price DESC');
                break;
            case 'value_asc':
                $query->orderByRaw('stock_quantity * price ASC');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            default:
                $query->orderByRaw('stock_quantity * price DESC');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Category',
            'Stock Quantity',
            'Unit Cost',
            'Total Value',
            '% of Total Inventory',
            'Valuation Method'
        ];
    }

    public function map($product): array
    {
        $product_value = $product->stock_quantity * $product->price;

        // Calculate total valuation for percentage
        $total_valuation = $this->collection()->sum(function($p) {
            return $p->stock_quantity * $p->price;
        });

        $percentage = $total_valuation > 0 ? ($product_value / $total_valuation) * 100 : 0;

        return [
            $product->name,
            $product->sku,
            $product->category->title ?? 'N/A',
            number_format($product->stock_quantity),
            store_currency_symbol() . number_format($product->price, 2),
            store_currency_symbol() . number_format($product_value, 2),
            number_format($percentage, 2) . '%',
            'Average Cost'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Calculate last row for totals
        $lastRow = $this->collection()->count() + 2;

        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B914C']]],
            $lastRow => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]],
        ];
    }

    public function title(): string
    {
        return 'Inventory Valuation';
    }
}
