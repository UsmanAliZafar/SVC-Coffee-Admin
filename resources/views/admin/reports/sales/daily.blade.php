{{-- resources/views/admin/reports/sales/daily.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Daily Sales Report - Coffee Admin')

@push('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-box .icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 15px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-box .value {
        font-size: 2rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 5px;
    }

    .stat-box .label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .hourly-chart-container {
        height: 350px;
        margin: 20px 0;
    }

    .product-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }

    .product-item:hover {
        background-color: #f8f9fa;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-rank {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #5B914C;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
    }

    .comparison-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .comparison-up {
        background-color: #d4edda;
        color: #155724;
    }

    .comparison-down {
        background-color: #f8d7da;
        color: #721c24;
    }

    .date-navigator {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .hourly-table td, .hourly-table th {
        vertical-align: middle;
    }

    .hour-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 600;
        min-width: 70px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-2">
                <i class="bi bi-calendar-day text-brand"></i> Daily Sales Report
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.sales.monthly') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Daily</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export
            </button>
            <a href="{{ route('admin.reports.sales.custom-range') }}" class="btn btn-brand">
                <i class="bi bi-calendar-range"></i> Custom Range
            </a>
        </div>
    </div>

    <!-- Date Navigator -->
    <div class="date-navigator mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <a href="{{ route('admin.reports.sales.daily', ['date' => $previous_date->format('Y-m-d')]) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i> Previous Day
                </a>
            </div>
            <div class="col-md-4 text-center">
                <h4 class="mb-0">
                    {{ $selected_date->format('l, F d, Y') }}
                </h4>
                <small class="text-muted">
                    @if($selected_date->isToday())
                        Today
                    @elseif($selected_date->isYesterday())
                        Yesterday
                    @else
                        {{ $selected_date->diffForHumans() }}
                    @endif
                </small>
            </div>
            <div class="col-md-4 text-end">
                @if(!$next_date->isFuture())
                <a href="{{ route('admin.reports.sales.daily', ['date' => $next_date->format('Y-m-d')]) }}"
                   class="btn btn-outline-secondary">
                    Next Day <i class="bi bi-chevron-right"></i>
                </a>
                @else
                <button class="btn btn-outline-secondary" disabled>
                    Next Day <i class="bi bi-chevron-right"></i>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="value">{{ number_format($sales_data['total_orders']) }}</div>
                <div class="label">Total Orders</div>
                @if(isset($comparison['orders_change']))
                <div class="mt-2">
                    <span class="comparison-badge {{ $comparison['orders_change'] >= 0 ? 'comparison-up' : 'comparison-down' }}">
                        <i class="bi bi-{{ $comparison['orders_change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($comparison['orders_change']), 1) }}%
                    </span>
                </div>
                @endif
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</div>
                <div class="label">Total Revenue</div>
                @if(isset($comparison['revenue_change']))
                <div class="mt-2">
                    <span class="comparison-badge {{ $comparison['revenue_change'] >= 0 ? 'comparison-up' : 'comparison-down' }}">
                        <i class="bi bi-{{ $comparison['revenue_change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($comparison['revenue_change']), 1) }}%
                    </span>
                </div>
                @endif
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="value">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</div>
                <div class="label">Average Order Value</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-box">
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="value">{{ number_format($sales_data['items_sold']) }}</div>
                <div class="label">Items Sold</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Hourly Sales Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-brand"></i> Hourly Sales Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="hourly-chart-container">
                        <canvas id="hourlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products Today -->
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-star text-brand"></i> Top Products Today
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($top_products_today as $index => $product)
                    <div class="product-item">
                        <div class="product-rank">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                            <div class="mt-1">
                                <span class="badge bg-success">{{ number_format($product->total_sold) }} sold</span>
                                <span class="badge bg-primary ms-1">{{ store_currency_symbol() }}{{ number_format($product->total_revenue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No products sold today</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-brand"></i> Hourly Performance Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover hourly-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Hour</th>
                                    <th>Time Period</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Avg Order Value</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxRevenue = collect($hourly_sales)->max('revenue');
                                @endphp
                                @foreach($hourly_sales as $hour)
                                <tr>
                                    <td>
                                        <span class="hour-badge bg-light">{{ $hour['hour_label'] }}</span>
                                    </td>
                                    <td>
                                        @if($hour['hour'] >= 0 && $hour['hour'] < 6)
                                            <span class="badge bg-secondary">Night</span>
                                        @elseif($hour['hour'] >= 6 && $hour['hour'] < 12)
                                            <span class="badge bg-warning">Morning</span>
                                        @elseif($hour['hour'] >= 12 && $hour['hour'] < 18)
                                            <span class="badge bg-info">Afternoon</span>
                                        @else
                                            <span class="badge bg-dark">Evening</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($hour['order_count']) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ store_currency_symbol() }}{{ number_format($hour['revenue'], 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        @if($hour['order_count'] > 0)
                                            {{ store_currency_symbol() }}{{ number_format($hour['revenue'] / $hour['order_count'], 2) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-brand"
                                                 role="progressbar"
                                                 style="width: {{ $maxRevenue > 0 ? ($hour['revenue'] / $maxRevenue * 100) : 0 }}%"
                                                 aria-valuenow="{{ $hour['revenue'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="{{ $maxRevenue }}">
                                                @if($hour['revenue'] > 0)
                                                    {{ number_format(($hour['revenue'] / $maxRevenue * 100), 0) }}%
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total / Average</th>
                                    <th class="text-end">{{ number_format($sales_data['total_orders']) }}</th>
                                    <th class="text-end">{{ store_currency_symbol() }}{{ number_format($sales_data['total_revenue'], 2) }}</th>
                                    <th class="text-end">{{ store_currency_symbol() }}{{ number_format($sales_data['avg_order_value'], 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info border-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Note:</strong> All times are displayed in your store's timezone.
                        Data is calculated from completed orders (Processing, Shipped, and Delivered status).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Hourly Sales Chart
    const ctx = document.getElementById('hourlySalesChart').getContext('2d');

    const hourlySalesData = @json($hourly_sales);

    const labels = hourlySalesData.map(item => item.hour_label);
    const revenueData = hourlySalesData.map(item => item.revenue);
    const orderData = hourlySalesData.map(item => item.order_count);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenueData,
                    backgroundColor: 'rgba(91, 145, 76, 0.7)',
                    borderColor: 'rgba(91, 145, 76, 1)',
                    borderWidth: 2,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Orders',
                    data: orderData,
                    type: 'line',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.dataset.label === 'Revenue') {
                                    label += '{{ store_currency_symbol() }}' + context.parsed.y.toFixed(2);
                                } else {
                                    label += context.parsed.y + ' orders';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue ({{ store_currency_symbol() }})'
                    },
                    ticks: {
                        callback: function(value) {
                            return '{{ store_currency_symbol() }}' + value.toFixed(0);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Print functionality
    window.onbeforeprint = function() {
        document.querySelectorAll('.btn-group, .date-navigator .btn').forEach(el => {
            el.style.display = 'none';
        });
    };

    window.onafterprint = function() {
        document.querySelectorAll('.btn-group, .date-navigator .btn').forEach(el => {
            el.style.display = '';
        });
    };
</script>
@endpush
