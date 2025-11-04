{{-- resources/views/admin/reports/inventory/valuation.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Inventory Valuation - Coffee Admin')

@push('styles')
<style>
    .valuation-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .valuation-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 20s infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(30px, 30px) rotate(10deg); }
    }

    .total-value-display {
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .total-value-display .currency-icon {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 10px;
    }

    .total-value-display .amount {
        font-size: 4rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        margin: 20px 0;
    }

    .total-value-display .label {
        font-size: 1.2rem;
        opacity: 0.9;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .valuation-method-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #5B914C;
        margin-bottom: 20px;
    }

    .valuation-method-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.2);
    }

    .method-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .method-name {
        font-size: 1.3rem;
        font-weight: 700;
        color: #212529;
    }

    .method-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
    }

    .method-description {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .breakdown-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .breakdown-item {
        text-align: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .breakdown-item .value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #212529;
        display: block;
    }

    .breakdown-item .label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-top: 5px;
    }

    .product-valuation-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .product-valuation-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .product-valuation-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
        white-space: nowrap;
    }

    .product-valuation-table td {
        padding: 15px;
        vertical-align: middle;
    }

    .product-valuation-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .product-valuation-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .value-cell {
        font-weight: 600;
        color: #5B914C;
    }

    .percentage-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .percentage-high {
        background: #d4edda;
        color: #155724;
    }

    .percentage-medium {
        background: #d1ecf1;
        color: #0c5460;
    }

    .percentage-low {
        background: #f8d7da;
        color: #721c24;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .chart-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .chart-container {
        height: 400px;
        position: relative;
    }

    .comparison-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .comparison-bar {
        margin-bottom: 20px;
    }

    .comparison-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .comparison-progress {
        height: 30px;
        background: #e9ecef;
        border-radius: 15px;
        overflow: hidden;
        position: relative;
    }

    .comparison-fill {
        height: 100%;
        background: linear-gradient(90deg, #5B914C 0%, #6BA055 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        transition: width 1s ease;
    }

    .category-valuation {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        border-left: 4px solid #5B914C;
    }

    .category-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 15px;
    }

    .category-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
        flex-grow: 1;
    }

    .category-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #5B914C;
    }

    .product-thumbnail {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .info-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 4px solid #2196f3;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box i {
        font-size: 1.5rem;
        color: #1976d2;
        margin-right: 10px;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        text-align: center;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.15);
    }

    .summary-card .icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 15px;
        background: linear-gradient(135deg, #5B914C 0%, #6BA055 100%);
        color: white;
    }

    .summary-card .value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .summary-card .label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    @media print {
        .no-print { display: none !important; }
        .product-valuation-table { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .total-value-display .amount { font-size: 2.5rem; }
        .breakdown-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="inventory-valuation-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-cash-stack text-brand"></i> Inventory Valuation
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Valuation</li>
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

    <!-- Total Valuation Header -->
    <div class="valuation-header">
        <div class="total-value-display">
            <div class="currency-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="amount">
                {{ store_currency_symbol() }}{{ number_format($total_valuation, 2) }}
            </div>
            <div class="label">Total Inventory Value</div>
            <small class="d-block mt-2" style="opacity: 0.8;">
                As of {{ now()->format('F d, Y') }}
            </small>
        </div>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <strong>Valuation Method:</strong>
        This report uses the <strong>Average Cost Method</strong> to calculate inventory value.
        Value = Current Stock Quantity × Average Unit Cost
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-boxes"></i>
            </div>
            <div class="value">{{ number_format($total_products) }}</div>
            <div class="label">Products Tracked</div>
        </div>
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-stack"></i>
            </div>
            <div class="value">{{ number_format($total_units) }}</div>
            <div class="label">Total Units</div>
        </div>
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="value">{{ store_currency_symbol() }}{{ number_format($average_unit_value, 2) }}</div>
            <div class="label">Avg Unit Value</div>
        </div>
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="value">{{ number_format($categories_count) }}</div>
            <div class="label">Categories</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.inventory.valuation') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search"></i> Search Product
                    </label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Product name or SKU..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-sort-down"></i> Sort By
                    </label>
                    <select class="form-select" name="sort">
                        <option value="value_desc" {{ request('sort') == 'value_desc' ? 'selected' : '' }}>Value: High to Low</option>
                        <option value="value_asc" {{ request('sort') == 'value_asc' ? 'selected' : '' }}>Value: Low to High</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                        <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Valuation by Category -->
    @if(count($category_valuations) > 0)
    <div class="chart-section">
        <h5 class="mb-4">
            <i class="bi bi-pie-chart text-brand"></i> Valuation by Category
        </h5>
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <canvas id="categoryValuationChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                @foreach($category_valuations as $index => $cat_val)
                <div class="category-valuation" style="border-left-color: {{ ['#5B914C', '#0dcaf0', '#0d6efd', '#ffc107', '#dc3545'][$index % 5] }};">
                    <div class="category-header">
                        <div class="category-name">
                            <i class="bi bi-folder"></i> {{ $cat_val['category_name'] }}
                        </div>
                        <div class="category-value">
                            {{ store_currency_symbol() }}{{ number_format($cat_val['total_value'], 2) }}
                        </div>
                    </div>
                    <div class="breakdown-grid">
                        <div class="breakdown-item">
                            <span class="value">{{ number_format($cat_val['product_count']) }}</span>
                            <span class="label">Products</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="value">{{ number_format($cat_val['total_units']) }}</span>
                            <span class="label">Units</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="value">{{ $total_valuation > 0 ? number_format(($cat_val['total_value'] / $total_valuation) * 100, 1) : 0 }}%</span>
                            <span class="label">% of Total</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Product Valuation Table -->
    @if($products->count() > 0)
    <div class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-brand"></i> Detailed Product Valuation
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table product-valuation-table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th class="text-center">Stock Qty</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total Value</th>
                                <th class="text-center">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            @php
                                $product_value = $product->stock_quantity * $product->price;
                                $percentage = $total_valuation > 0 ? ($product_value / $total_valuation) * 100 : 0;

                                if ($percentage > 5) {
                                    $badge_class = 'percentage-high';
                                } elseif ($percentage > 2) {
                                    $badge_class = 'percentage-medium';
                                } else {
                                    $badge_class = 'percentage-low';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}"
                                             alt="{{ $product->name }}"
                                             class="product-thumbnail me-3"
                                             onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                                        @else
                                        <div class="product-thumbnail bg-light d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-box text-muted"></i>
                                        </div>
                                        @endif
                                        <strong>{{ Str::limit($product->name, 40) }}</strong>
                                    </div>
                                </td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>
                                    @if($product->category)
                                    <span class="badge bg-light text-dark">{{ $product->category->title }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($product->stock_quantity) }}</strong>
                                </td>
                                <td class="text-end">
                                    {{ store_currency_symbol() }}{{ number_format($product->price, 2) }}
                                </td>
                                <td class="text-end value-cell">
                                    {{ store_currency_symbol() }}{{ number_format($product_value, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="percentage-badge {{ $badge_class }}">
                                        {{ number_format($percentage, 2) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3">TOTAL</th>
                                <th class="text-center">{{ number_format($total_units) }}</th>
                                <th class="text-end">—</th>
                                <th class="text-end value-cell">
                                    {{ store_currency_symbol() }}{{ number_format($total_valuation, 2) }}
                                </th>
                                <th class="text-center">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-inbox fs-1 mb-3"></i>
        <h5>No Products Found</h5>
        <p class="mb-0">No products match your current filters.</p>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-calculator text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Average Cost Method</h6>
                <p class="text-muted small mb-0">
                    Inventory valued using average unit cost for accurate financial reporting.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-graph-up text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Real-Time Tracking</h6>
                <p class="text-muted small mb-0">
                    Valuation updates automatically as stock levels and costs change.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-shield-check text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Financial Accuracy</h6>
                <p class="text-muted small mb-0">
                    Accurate inventory valuation for balance sheet and financial statements.
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
    // Category Valuation Pie Chart
    const chartCtx = document.getElementById('categoryValuationChart');
    if (chartCtx) {
        const categoryData = @json($category_valuations);

        const colors = ['#5B914C', '#0dcaf0', '#0d6efd', '#ffc107', '#dc3545', '#6c757d', '#20c997'];

        new Chart(chartCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(c => c.category_name),
                datasets: [{
                    data: categoryData.map(c => c.total_value),
                    backgroundColor: colors.slice(0, categoryData.length),
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    label,
                                    'Value: {{ store_currency_symbol() }}' + value.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    'Share: ' + percentage + '%'
                                ];
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
