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
// ==================== CUSTOMERS NEW VS RETURNING EXPORT ====================

/**
 * Customers New vs Returning Export
 * File: app/Exports/CustomersNewVsReturningExport.php
 */
class CustomersNewVsReturningExport implements WithMultipleSheets
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Create multiple sheets
     */
    public function sheets(): array
    {
        return [
            new NewVsReturningSummarySheet($this->request),
            new NewVsReturningDetailSheet($this->request),
        ];
    }
}

/**
 * Summary Sheet for New vs Returning
 */
class NewVsReturningSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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

        $orders = \App\Models\Order::with('customer')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->get();

        // Classify orders
        $new_orders = collect();
        $returning_orders = collect();

        foreach ($orders as $order) {
            if ($order->customer) {
                $customer_orders_before = \App\Models\Order::where('customer_id', $order->customer_id)
                    ->where('created_at', '<', $order->created_at)
                    ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                    ->count();

                if ($customer_orders_before == 0) {
                    $new_orders->push($order);
                } else {
                    $returning_orders->push($order);
                }
            }
        }

        // Create summary data
        return collect([
            [
                'type' => 'New Customers',
                'customer_count' => $new_orders->pluck('customer_id')->unique()->count(),
                'order_count' => $new_orders->count(),
                'revenue' => $new_orders->sum('total_amount'),
                'avg_order_value' => $new_orders->count() > 0 ? $new_orders->sum('total_amount') / $new_orders->count() : 0,
            ],
            [
                'type' => 'Returning Customers',
                'customer_count' => $returning_orders->pluck('customer_id')->unique()->count(),
                'order_count' => $returning_orders->count(),
                'revenue' => $returning_orders->sum('total_amount'),
                'avg_order_value' => $returning_orders->count() > 0 ? $returning_orders->sum('total_amount') / $returning_orders->count() : 0,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Customer Type',
            'Unique Customers',
            'Total Orders',
            'Total Revenue',
            'Avg Order Value',
            'Revenue %'
        ];
    }

    public function map($row): array
    {
        $all_data = $this->collection();
        $total_revenue = $all_data->sum('revenue');
        $revenue_percentage = $total_revenue > 0 ? ($row['revenue'] / $total_revenue) * 100 : 0;

        return [
            $row['type'],
            number_format($row['customer_count']),
            number_format($row['order_count']),
            store_currency_symbol() . number_format($row['revenue'], 2),
            store_currency_symbol() . number_format($row['avg_order_value'], 2),
            number_format($revenue_percentage, 1) . '%'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B914C']]],
            2 => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1ECF1']]],
            3 => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4EDDA']]],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

/**
 * Detail Sheet for New vs Returning
 */
class NewVsReturningDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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

        return \App\Models\Order::with('customer')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Date',
            'Customer Name',
            'Customer Email',
            'Customer Type',
            'Order Amount',
            'Previous Orders'
        ];
    }

    public function map($order): array
    {
        if ($order->customer) {
            $customer_orders_before = \App\Models\Order::where('customer_id', $order->customer_id)
                ->where('created_at', '<', $order->created_at)
                ->whereIn('status_key_code', ['ORDER_DELIVERED', 'ORDER_SHIPPED', 'ORDER_PROCESSING'])
                ->count();

            $customer_type = $customer_orders_before == 0 ? 'New Customer' : 'Returning Customer';
            $customer_name = $order->customer->getFullName();
            $customer_email = $order->customer->email;
        } else {
            $customer_type = 'Unknown';
            $customer_name = 'Guest';
            $customer_email = 'N/A';
            $customer_orders_before = 0;
        }

        return [
            $order->id,
            $order->created_at->format('Y-m-d H:i:s'),
            $customer_name,
            $customer_email,
            $customer_type,
            store_currency_symbol() . number_format($order->total_amount, 2),
            number_format($customer_orders_before)
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
        return 'Order Details';
    }
}
