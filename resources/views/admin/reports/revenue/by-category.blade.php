{{-- resources/views/admin/reports/revenue/by-category.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Revenue by Category - Coffee Admin')

@push('styles')
<style>
    .category-card {
        border-radius: 15px;
        border: 2px solid #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .category-card:hover {
        border-color: #5B914C;
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(91, 145, 76, 0.15)!important;
    }

    .category-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .category-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .revenue-amount {
        font-size: 1.8rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .category-stats {
        display: flex;
        justify-content: space-around;
        padding: 15px 0;
        border-top: 1px solid #e9ecef;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #212529;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress-bar-custom {
        height: 25px;
        border-radius: 12px;
        background-color: #f0f0f0;
        position: relative;
        overflow: hidden;
        margin: 15px 0;
    }

    .progress-fill {
        height: 100%;
        border-radius: 12px;
        background: linear-gradient(90deg, #5B914C 0%, #6BA055 100%);
        transition: width 0.6s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .trend-chart-container {
        height: 300px;
        padding: 20px;
    }

    .comparison-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }

    .comparison-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .comparison-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
    }

    .comparison-table td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .comparison-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .comparison-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .category-badge {
        display: inline-block;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        background-color: #f8f9fa;
        color: #495057;
    }

    .subcategory-item {
        background: white;
        border: 2px solid #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .subcategory-item:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .chart-legend {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }

    .top-category-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .comparison-chart {
        height: 400px;
        padding: 20px;
    }

    .metric-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        margin-bottom: 15px;
    }

    .metric-box .value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .metric-box .label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 5px;
    }

    .export-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-top: 20px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .category-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }

    @media (max-width: 768px) {
        .revenue-amount {
            font-size: 1.3rem;
        }

        .category-stats {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="revenue-by-category-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-grid-3x3-gap text-brand"></i> Revenue by Category
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.revenue.index') }}">Revenue</a></li>
                    <li class="breadcrumb-item active">By Category</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group no-print">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-brand dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="filter-card no-print">
        <form method="GET" action="{{ route('admin.reports.revenue.by-category') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> Start Date
                    </label>
                    <input type="date" class="form-control" name="start_date"
                           value="{{ $start_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> End Date
                    </label>
                    <input type="date" class="form-control" name="end_date"
                           value="{{ $end_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing data from <strong>{{ $start_date->format('M d, Y') }}</strong>
                to <strong>{{ $end_date->format('M d, Y') }}</strong>
                ({{ $start_date->diffInDays($end_date) + 1 }} days)
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <div class="value">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</div>
                <div class="label">Total Revenue</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <div class="value">{{ number_format($total_units_sold) }}</div>
                <div class="label">Total Units Sold</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <div class="value">{{ count($category_revenue) }}</div>
                <div class="label">Active Categories</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <div class="value">{{ store_currency_symbol() }}{{ count($category_revenue) > 0 ? number_format($total_revenue / count($category_revenue), 2) : '0.00' }}</div>
                <div class="label">Avg per Category</div>
            </div>
        </div>
    </div>

    <!-- Category Revenue Cards Grid -->
    <div class="row mb-4">
        @if(count($category_revenue) > 0)
            @php
                $topCategory = collect($category_revenue)->sortByDesc('revenue')->first();
            @endphp
            @foreach($category_revenue as $index => $category)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="category-card shadow-sm position-relative">
                    @if($category['id'] == $topCategory['id'])
                    <div class="top-category-badge">
                        <i class="bi bi-trophy"></i> Top Revenue
                    </div>
                    @endif

                    <div class="category-header">
                        <div>
                            <h5 class="mb-0">{{ $category['title'] }}</h5>
                            <small class="opacity-75">{{ $category['order_count'] }} orders</small>
                        </div>
                        <div class="category-icon">
                            <i class="bi bi-{{ $index % 5 == 0 ? 'cup-hot' : ($index % 5 == 1 ? 'box-seam' : ($index % 5 == 2 ? 'gear' : ($index % 5 == 3 ? 'award' : 'star'))) }}"></i>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="revenue-amount">
                            {{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}
                        </div>

                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $total_revenue > 0 ? ($category['revenue'] / $total_revenue) * 100 : 0 }}%">
                                {{ $total_revenue > 0 ? number_format(($category['revenue'] / $total_revenue) * 100, 1) : 0 }}%
                            </div>
                        </div>

                        <div class="category-stats">
                            <div class="stat-item">
                                <div class="stat-value">{{ number_format($category['units_sold']) }}</div>
                                <div class="stat-label">Units Sold</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $category['units_sold'] > 0 ? store_currency_symbol() . number_format($category['revenue'] / $category['units_sold'], 2) : store_currency_symbol() . '0.00' }}</div>
                                <div class="stat-label">Avg per Unit</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $category['order_count'] }}</div>
                                <div class="stat-label">Orders</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-1 mb-3"></i>
                    <h5>No Category Data Available</h5>
                    <p class="mb-0">There are no revenue records for the selected date range.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Category Revenue Comparison Chart -->
    @if(count($category_revenue) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-line text-brand"></i> Category Revenue Comparison
                    </h5>
                </div>
                <div class="card-body">
                    <div class="comparison-chart">
                        <canvas id="categoryComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Category Revenue Trends -->
    @if(count($category_trends) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-brand"></i> Category Revenue Trends Over Time
                    </h5>
                </div>
                <div class="card-body">
                    <div class="trend-chart-container">
                        <canvas id="categoryTrendChart"></canvas>
                    </div>

                    <div class="chart-legend mt-3">
                        @php
                            $categoryColors = ['#5B914C', '#0dcaf0', '#0d6efd', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#fd7e14', '#6f42c1', '#d63384'];
                        @endphp
                        @foreach($category_revenue as $index => $category)
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: {{ $categoryColors[$index % count($categoryColors)] }}"></div>
                            <span class="small">{{ $category['title'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Comparison Table -->
    @if(count($category_revenue) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-table text-brand"></i> Detailed Category Comparison
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table comparison-table mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Category</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-center">% of Total</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Avg Order Value</th>
                                    <th class="text-end">Revenue/Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(collect($category_revenue)->sortByDesc('revenue')->values() as $index => $category)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($index < 3)
                                            <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }} me-2">
                                                #{{ $index + 1 }}
                                            </span>
                                            @else
                                            <span class="text-muted me-2">#{{ $index + 1 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-grid-3x3-gap text-brand me-2"></i>
                                            <strong>{{ $category['title'] }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-brand">
                                            {{ store_currency_symbol() }}{{ number_format($category['revenue'], 2) }}
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            {{ $total_revenue > 0 ? number_format(($category['revenue'] / $total_revenue) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($category['units_sold']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ number_format($category['order_count']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $category['order_count'] > 0 ? store_currency_symbol() . number_format($category['revenue'] / $category['order_count'], 2) : store_currency_symbol() . '0.00' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $category['units_sold'] > 0 ? store_currency_symbol() . number_format($category['revenue'] / $category['units_sold'], 2) : store_currency_symbol() . '0.00' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">TOTAL</th>
                                    <th class="text-end">
                                        <strong class="text-brand">
                                            {{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}
                                        </strong>
                                    </th>
                                    <th class="text-center">100%</th>
                                    <th class="text-center">
                                        <strong>{{ number_format($total_units_sold) }}</strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>{{ collect($category_revenue)->sum('order_count') }}</strong>
                                    </th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Subcategory Breakdown -->
    @if(count($subcategory_breakdown) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3 text-brand"></i> Subcategory Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $groupedSubcategories = collect($subcategory_breakdown)->groupBy('parent_category');
                    @endphp

                    @foreach($groupedSubcategories as $parentCategory => $subcategories)
                    <div class="mb-4">
                        <h6 class="text-brand mb-3">
                            <i class="bi bi-folder"></i> {{ $parentCategory }}
                        </h6>
                        <div class="row">
                            @foreach($subcategories as $subcategory)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="subcategory-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $subcategory['subcategory'] }}</h6>
                                            <small class="text-muted">Subcategory</small>
                                        </div>
                                        <i class="bi bi-arrow-right-circle text-brand"></i>
                                    </div>
                                    <div class="fw-bold text-brand mb-2">
                                        {{ store_currency_symbol() }}{{ number_format($subcategory['revenue'], 2) }}
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        @php
                                            $parentTotal = $subcategories->sum('revenue');
                                        @endphp
                                        <div class="progress-bar bg-brand"
                                             style="width: {{ $parentTotal > 0 ? ($subcategory['revenue'] / $parentTotal) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Export & Actions -->
    <div class="export-section no-print">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-2"><i class="bi bi-info-circle text-primary"></i> Report Information</h6>
                <p class="text-muted small mb-0">
                    This report shows revenue breakdown by product categories for the selected period.
                    Data includes all completed, shipped, and processing orders.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.reports.revenue.by-product') }}" class="btn btn-outline-brand me-2">
                    <i class="bi bi-box-seam"></i> View by Product
                </a>
                <a href="{{ route('admin.reports.revenue.index') }}" class="btn btn-brand">
                    <i class="bi bi-arrow-left"></i> Back to Revenue
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category colors
    const categoryColors = [
        '#5B914C', '#0dcaf0', '#0d6efd', '#ffc107', '#dc3545',
        '#6c757d', '#20c997', '#fd7e14', '#6f42c1', '#d63384'
    ];

    // Category Comparison Bar Chart
    const comparisonCtx = document.getElementById('categoryComparisonChart');
    if (comparisonCtx) {
        const categoryData = @json($category_revenue);

        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: categoryData.map(cat => cat.title),
                datasets: [{
                    label: 'Revenue',
                    data: categoryData.map(cat => cat.revenue),
                    backgroundColor: categoryColors.slice(0, categoryData.length),
                    borderColor: categoryColors.slice(0, categoryData.length),
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: {{ store_currency_symbol() }}' + context.parsed.y.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            },
                            afterLabel: function(context) {
                                const cat = categoryData[context.dataIndex];
                                return [
                                    'Units Sold: ' + cat.units_sold.toLocaleString(),
                                    'Orders: ' + cat.order_count.toLocaleString()
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ store_currency_symbol() }}' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Category Trend Line Chart
    const trendCtx = document.getElementById('categoryTrendChart');
    if (trendCtx) {
        const trendData = @json($category_trends);

        // Group trends by category
        const groupedTrends = {};
        Object.keys(trendData).forEach(category => {
            const trends = trendData[category];
            groupedTrends[category] = trends.map(t => ({
                date: t.date,
                revenue: t.daily_revenue
            }));
        });

        // Get all unique dates
        const allDates = [...new Set(
            Object.values(groupedTrends).flat().map(t => t.date)
        )].sort();

        // Create datasets
        const datasets = Object.keys(groupedTrends).map((category, index) => {
            const data = allDates.map(date => {
                const trend = groupedTrends[category].find(t => t.date === date);
                return trend ? trend.revenue : 0;
            });

            return {
                label: category,
                data: data,
                borderColor: categoryColors[index % categoryColors.length],
                backgroundColor: categoryColors[index % categoryColors.length] + '20',
                borderWidth: 3,
                fill: false,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            };
        });

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: allDates.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: datasets
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
                        display: false // Using custom legend
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': {{ store_currency_symbol() }}' +
                                       context.parsed.y.toLocaleString('en-US', {
                                           minimumFractionDigits: 2,
                                           maximumFractionDigits: 2
                                       });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ store_currency_symbol() }}' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Animate progress bars on page load
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});
</script>
@endpush
