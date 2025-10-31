{{-- resources/views/admin/reports/revenue/by-product.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Revenue by Product - Coffee Admin')

@push('styles')
<style>
    .product-card {
        border-radius: 15px;
        border: 2px solid #f8f9fa;
        transition: all 0.3s ease;
        overflow: hidden;
        background: white;
    }

    .product-card:hover {
        border-color: #5B914C;
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1.5rem rgba(91, 145, 76, 0.2)!important;
    }

    .product-image-container {
        position: relative;
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-rank-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .top-seller-badge {
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

    .product-info {
        padding: 20px;
    }

    .product-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-sku {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 15px;
    }

    .product-revenue {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
        margin-bottom: 10px;
    }

    .product-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 1rem;
        font-weight: bold;
        color: #212529;
        display: block;
    }

    .stat-label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-top: 3px;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .summary-box {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .summary-box .row {
        align-items: center;
    }

    .summary-metric {
        text-align: center;
        padding: 10px;
    }

    .summary-metric .value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .summary-metric .label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .comparison-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
        padding: 15px;
        vertical-align: middle;
    }

    .comparison-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .comparison-table tbody tr:hover {
        background-color: #f8fdf6;
        transform: scale(1.01);
    }

    .product-thumbnail {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .chart-container {
        height: 400px;
        padding: 20px;
    }

    .category-filter-btn {
        border: 2px solid #e9ecef;
        border-radius: 20px;
        padding: 8px 20px;
        margin: 5px;
        background: white;
        color: #6c757d;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .category-filter-btn:hover,
    .category-filter-btn.active {
        border-color: #5B914C;
        background-color: #5B914C;
        color: white;
    }

    .performance-indicator {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .performance-excellent {
        background-color: #d4edda;
        color: #155724;
    }

    .performance-good {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .performance-average {
        background-color: #fff3cd;
        color: #856404;
    }

    .performance-poor {
        background-color: #f8d7da;
        color: #721c24;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding-left: 40px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .pagination-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .product-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }

    @media (max-width: 768px) {
        .product-stats {
            grid-template-columns: 1fr;
        }

        .summary-box .summary-metric {
            margin-bottom: 15px;
        }
    }
</style>
@endpush

@section('content')
<div class="revenue-by-product-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-box-seam text-brand"></i> Revenue by Product
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.revenue.index') }}">Revenue</a></li>
                    <li class="breadcrumb-item active">By Product</li>
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

    <!-- Filters & Search -->
    <div class="filter-card no-print">
        <form method="GET" action="{{ route('admin.reports.revenue.by-product') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> Start Date
                    </label>
                    <input type="date" class="form-control" name="start_date"
                           value="{{ $start_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> End Date
                    </label>
                    <input type="date" class="form-control" name="end_date"
                           value="{{ $end_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-grid-3x3-gap"></i> Category
                    </label>
                    <select class="form-select" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>

            <!-- Search Box -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" name="search"
                               placeholder="Search by product name or SKU..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    @if(request()->hasAny(['start_date', 'end_date', 'category_id', 'search']))
                    <a href="{{ route('admin.reports.revenue.by-product') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                    @endif
                </div>
            </div>

            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing data from <strong>{{ $start_date->format('M d, Y') }}</strong>
                to <strong>{{ $end_date->format('M d, Y') }}</strong>
                @if(request('search'))
                | Search: <strong>"{{ request('search') }}"</strong>
                @endif
                @if(request('category_id'))
                | Category: <strong>{{ $categories->firstWhere('id', request('category_id'))->title ?? 'Unknown' }}</strong>
                @endif
            </div>
        </form>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="row">
            <div class="col-md-3 summary-metric">
                <div class="value">{{ store_currency_symbol() }}{{ number_format($total_revenue, 2) }}</div>
                <div class="label">Total Revenue</div>
            </div>
            <div class="col-md-3 summary-metric border-start border-white border-opacity-25">
                <div class="value">{{ number_format($total_products) }}</div>
                <div class="label">Products Sold</div>
            </div>
            <div class="col-md-3 summary-metric border-start border-white border-opacity-25">
                <div class="value">{{ number_format($total_units_sold) }}</div>
                <div class="label">Total Units</div>
            </div>
            <div class="col-md-3 summary-metric border-start border-white border-opacity-25">
                <div class="value">{{ store_currency_symbol() }}{{ $total_units_sold > 0 ? number_format($total_revenue / $total_units_sold, 2) : '0.00' }}</div>
                <div class="label">Avg Price/Unit</div>
            </div>
        </div>
    </div>

    <!-- Top Products Grid -->
    @if(count($products) > 0)
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="bi bi-trophy text-brand"></i> Top Revenue Products</h5>
        </div>
        @foreach($products->take(12) as $index => $product)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="product-card shadow-sm">
                <div class="product-image-container">
                    @if($product->main_image)
                    <img src="{{ asset('storage/' . $product->main_image) }}"
                         alt="{{ $product->name }}"
                         class="product-image"
                         onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                    @else
                    <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                    @endif

                    <div class="product-rank-badge">#{{ $index + 1 }}</div>

                    @if($index < 3)
                    <div class="top-seller-badge">
                        <i class="bi bi-star-fill"></i> Top {{ $index + 1 }}
                    </div>
                    @endif
                </div>

                <div class="product-info">
                    <h6 class="product-name">{{ $product->name }}</h6>
                    <div class="product-sku">
                        <i class="bi bi-upc"></i> {{ $product->sku }}
                    </div>

                    <div class="product-revenue">
                        {{ store_currency_symbol() }}{{ number_format($product->revenue, 2) }}
                    </div>

                    <div class="mb-2">
                        @php
                            $percentage = $total_revenue > 0 ? ($product->revenue / $total_revenue) * 100 : 0;
                            $performanceClass = $percentage > 10 ? 'excellent' : ($percentage > 5 ? 'good' : ($percentage > 2 ? 'average' : 'poor'));
                        @endphp
                        <span class="performance-indicator performance-{{ $performanceClass }}">
                            {{ number_format($percentage, 1) }}% of total
                        </span>
                    </div>

                    <div class="product-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ number_format($product->units_sold) }}</span>
                            <span class="stat-label">Units</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ number_format($product->order_count) }}</span>
                            <span class="stat-label">Orders</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ store_currency_symbol() }}{{ $product->units_sold > 0 ? number_format($product->revenue / $product->units_sold, 2) : '0.00' }}</span>
                            <span class="stat-label">Per Unit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-inbox fs-1 mb-3"></i>
        <h5>No Products Found</h5>
        <p class="mb-0">No revenue data available for the selected filters.</p>
    </div>
    @endif

    <!-- Revenue Comparison Chart -->
    @if(count($products) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart text-brand"></i> Product Revenue Comparison (Top 15)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="productRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Product Table -->
    @if(count($products) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-table text-brand"></i> Detailed Product Revenue Report
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table comparison-table mb-0" id="productTable">
                            <thead>
                                <tr>
                                    <th width="50">Rank</th>
                                    <th width="80">Image</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-center">% Total</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Avg/Unit</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $index => $product)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                        <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }}">
                                            #{{ $index + 1 }}
                                        </span>
                                        @else
                                        <span class="text-muted">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}"
                                             alt="{{ $product->name }}"
                                             class="product-thumbnail"
                                             onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                                        @else
                                        <div class="product-thumbnail d-flex align-items-center justify-content-center bg-light">
                                            <i class="bi bi-box-seam text-muted"></i>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                    </td>
                                    <td>
                                        <code class="text-muted">{{ $product->sku }}</code>
                                    </td>
                                    <td>
                                        @if($product->category)
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-folder"></i> {{ $product->category->title }}
                                        </span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-brand">
                                            {{ store_currency_symbol() }}{{ number_format($product->revenue, 2) }}
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            {{ $total_revenue > 0 ? number_format(($product->revenue / $total_revenue) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($product->units_sold) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ number_format($product->order_count) }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $product->units_sold > 0 ? store_currency_symbol() . number_format($product->revenue / $product->units_sold, 2) : store_currency_symbol() . '0.00' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $percentage = $total_revenue > 0 ? ($product->revenue / $total_revenue) * 100 : 0;
                                            if ($percentage > 10) {
                                                $performanceClass = 'excellent';
                                                $performanceText = 'Excellent';
                                            } elseif ($percentage > 5) {
                                                $performanceClass = 'good';
                                                $performanceText = 'Good';
                                            } elseif ($percentage > 2) {
                                                $performanceClass = 'average';
                                                $performanceText = 'Average';
                                            } else {
                                                $performanceClass = 'poor';
                                                $performanceText = 'Low';
                                            }
                                        @endphp
                                        <span class="performance-indicator performance-{{ $performanceClass }}">
                                            {{ $performanceText }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5">TOTAL</th>
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
                                        <strong>{{ number_format($products->sum('order_count')) }}</strong>
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

    <!-- Pagination Info -->
    @if(count($products) > 0)
    <div class="pagination-info">
        <div>
            Showing <strong>{{ count($products) }}</strong> products
            @if(request('category_id') || request('search'))
            matching your filters
            @endif
        </div>
        <div>
            <a href="{{ route('admin.reports.revenue.by-category') }}" class="btn btn-outline-brand btn-sm">
                <i class="bi bi-grid-3x3-gap"></i> View by Category
            </a>
            <a href="{{ route('admin.reports.revenue.index') }}" class="btn btn-brand btn-sm ms-2">
                <i class="bi bi-arrow-left"></i> Back to Revenue
            </a>
        </div>
    </div>
    @endif

    <!-- Info Section -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-info-circle text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Performance Indicators</h6>
                <p class="text-muted small mb-0">
                    Products are ranked by total revenue contribution. Performance indicators show percentage of total revenue.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-funnel text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Smart Filtering</h6>
                <p class="text-muted small mb-0">
                    Filter by date range, category, or search for specific products to analyze performance in detail.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Revenue Analytics</h6>
                <p class="text-muted small mb-0">
                    All metrics calculated from completed, shipped, and processing orders for accurate revenue reporting.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Product Revenue Bar Chart
    const productChartCtx = document.getElementById('productRevenueChart');
    if (productChartCtx) {
        const products = @json($products->take(15));

        // Generate gradient colors
        const colors = products.map((_, index) => {
            const hue = (index * 360 / 15) % 360;
            return `hsl(${hue}, 65%, 50%)`;
        });

        new Chart(productChartCtx, {
            type: 'bar',
            data: {
                labels: products.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name),
                datasets: [{
                    label: 'Revenue',
                    data: products.map(p => p.revenue),
                    backgroundColor: '#5B914C',
                    borderColor: '#4a7a3d',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: '#6BA055'
                }]
            },
            options: {
                indexAxis: 'y',
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
                                const product = products[context.dataIndex];
                                return [
                                    'Revenue: {{ store_currency_symbol() }}' + context.parsed.x.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    'Units Sold: ' + product.units_sold.toLocaleString(),
                                    'Orders: ' + product.order_count.toLocaleString()
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ store_currency_symbol() }}' + value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false
                        }
                    }
                }
            }
        });
    }

    // Real-time search (optional - debounced)
    let searchTimeout;
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    document.getElementById('filterForm').submit();
                }
            }, 500);
        });
    }

    // Table sorting (if you want to add client-side sorting)
    // You can integrate DataTables.js here for advanced features
});
</script>
@endpush
