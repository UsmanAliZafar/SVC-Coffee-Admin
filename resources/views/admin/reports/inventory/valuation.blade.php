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
.variant-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }

    .warehouse-filter-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 15px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-left: 10px;
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
            <button type="button" class="btn btn-brand dropdown-toggle d-none" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="exportValuation('pdf')"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportValuation('excel')"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportValuation('csv')"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- ✅ UPDATED: Total Valuation Header -->
    <div class="valuation-header">
        <div class="total-value-display">
            <div class="currency-icon">
                {{ store_currency_symbol() }}
            </div>
            <div class="amount">
                {{ store_currency_symbol() }}{{ number_format($valuation['total_value'], 2) }}
            </div>
            <div class="label">
                Total Inventory Value
                @if(request('warehouse_id'))
                    @php
                        $selectedWarehouse = $warehouses->firstWhere('id', request('warehouse_id'));
                    @endphp
                    <span class="warehouse-filter-badge">
                        <i class="bi bi-building"></i> {{ $selectedWarehouse->name ?? 'Warehouse' }}
                    </span>
                @endif
            </div>
            <small class="d-block mt-2" style="opacity: 0.8;">
                As of {{ now()->format('F d, Y') }} •
                {{ number_format($valuation['total_items']) }} Items •
                {{ number_format($valuation['total_quantity']) }} Units
            </small>
        </div>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <strong>Valuation Method:</strong>
        This report uses the <strong>Current Price Method</strong> to calculate inventory value.
        Value = Stock Quantity × Current Unit Price
        <br><small class="mt-2 d-block">
            <i class="bi bi-layers"></i> Includes both simple products and product variants tracked separately.
        </small>
    </div>

    <!-- ✅ UPDATED: Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-boxes"></i>
            </div>
            <div class="value">{{ number_format($valuation['total_items']) }}</div>
            <div class="label">Items Tracked</div>
            <small class="text-muted d-block mt-1">Products + Variants</small>
        </div>
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-stack"></i>
            </div>
            <div class="value">{{ number_format($valuation['total_quantity']) }}</div>
            <div class="label">Total Units</div>
            <small class="text-muted d-block mt-1">In Stock</small>
        </div>
        <div class="summary-card">
            <div class="icon">
                {{ store_currency_symbol() }}
            </div>
            <div class="value">{{ store_currency_symbol() }}{{ number_format($valuation['total_cost'], 2) }}</div>
            <div class="label">Total Cost</div>
            <small class="text-muted d-block mt-1">Purchase Value</small>
        </div>
        <div class="summary-card">
            <div class="icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="value">{{ store_currency_symbol() }}{{ number_format($valuation['total_profit'], 2) }}</div>
            <div class="label">Potential Profit</div>
            <small class="text-muted d-block mt-1">{{ $valuation['profit_margin'] }}% Margin</small>
        </div>
    </div>

    <!-- ✅ UPDATED: Filter Section -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.inventory.valuation') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-building"></i> Warehouse
                    </label>
                    <select class="form-select" name="warehouse_id" onchange="this.form.submit()">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-grid-3x3-gap"></i> Category
                    </label>
                    <select class="form-select" name="category_id" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Product name or SKU..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-sort-down"></i> Sort
                    </label>
                    <select class="form-select" name="sort">
                        <option value="value_desc" {{ request('sort') == 'value_desc' ? 'selected' : '' }}>Value: High to Low</option>
                        <option value="value_asc" {{ request('sort') == 'value_asc' ? 'selected' : '' }}>Value: Low to High</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                        <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>

            @if(request()->hasAny(['warehouse_id', 'category_id', 'search']))
            <div class="mt-3">
                <a href="{{ route('admin.reports.inventory.valuation') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- ✅ NEW: Valuation Breakdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-piggy-bank text-success fs-1 mb-3"></i>
                    <h6 class="text-muted">Cost Value</h6>
                    <h3 class="text-success">{{ store_currency_symbol() }}{{ number_format($valuation['total_cost'], 2) }}</h3>
                    <small class="text-muted">What we paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-tag text-primary fs-1 mb-3"></i>
                    <h6 class="text-muted">Retail Value</h6>
                    <h3 class="text-primary">{{ store_currency_symbol() }}{{ number_format($valuation['total_value'], 2) }}</h3>
                    <small class="text-muted">What we can sell for</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-warning fs-1 mb-3"></i>
                    <h6 class="text-muted">Potential Profit</h6>
                    <h3 class="text-warning">{{ store_currency_symbol() }}{{ number_format($valuation['total_profit'], 2) }}</h3>
                    <small class="text-muted">{{ $valuation['profit_margin'] }}% margin</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ UPDATED: Detailed Product Valuation Table -->
    @if(count($valuation['items']) > 0)
    <div class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="bi bi-list-check text-brand"></i> Detailed Valuation
                    <span class="badge bg-brand ms-2">{{ number_format(count($valuation['items'])) }} Items</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table product-valuation-table mb-0">
                        <thead>
                            <tr>
                                <th width="35%">Product</th>
                                <th width="12%">SKU</th>
                                <th width="12%">Category</th>
                                <th width="8%" class="text-center">Stock</th>
                                <th width="10%" class="text-end">Unit Cost</th>
                                <th width="10%" class="text-end">Unit Price</th>
                                <th width="10%" class="text-end">Total Value</th>
                                <th width="8%" class="text-center">% Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($valuation['items'] as $item)
                            @php
                                $percentage = $valuation['total_value'] > 0
                                    ? ($item['total_value'] / $valuation['total_value']) * 100
                                    : 0;

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
                                    <div class="d-flex align-items-start">
                                        <div>
                                            <strong>{{ $item['name'] }}</strong>
                                            @if($item['type'] === 'variant')
                                            <span class="variant-badge">
                                                <i class="bi bi-layers"></i> {{ $item['variant_name'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><code class="small">{{ $item['sku'] }}</code></td>
                                <td>
                                    <span class="badge bg-light text-dark small">{{ $item['category'] }}</span>
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($item['quantity']) }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-muted">{{ store_currency_symbol() }}{{ number_format($item['unit_cost'], 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ store_currency_symbol() }}{{ number_format($item['unit_price'], 2) }}</strong>
                                </td>
                                <td class="text-end value-cell">
                                    {{ store_currency_symbol() }}{{ number_format($item['total_value'], 2) }}
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
                                <th class="text-center">{{ number_format($valuation['total_quantity']) }}</th>
                                <th class="text-end">—</th>
                                <th class="text-end">—</th>
                                <th class="text-end value-cell">
                                    {{ store_currency_symbol() }}{{ number_format($valuation['total_value'], 2) }}
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
        <h5>No Items Found</h5>
        <p class="mb-0">No products or variants match your current filters, or all items are out of stock.</p>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-calculator text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Current Price Method</h6>
                <p class="text-muted small mb-0">
                    Inventory valued using current unit prices for accurate financial reporting.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-layers text-info fs-3 mb-2"></i>
                <h6 class="fw-bold">Variant Support</h6>
                <p class="text-muted small mb-0">
                    Individual variants tracked separately with their own costs and prices.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-building text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Multi-Warehouse</h6>
                <p class="text-muted small mb-0">
                    View total valuation or filter by specific warehouse locations.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ✅ Export valuation
function exportValuation(format) {
    const params = new URLSearchParams({
        export: format,
        warehouse_id: '{{ request("warehouse_id") }}',
        category_id: '{{ request("category_id") }}',
        search: '{{ request("search") }}',
        sort: '{{ request("sort", "value_desc") }}'
    });

    window.open("{{ route('admin.reports.inventory.valuation') }}?" + params.toString(), '_blank');
}
</script>
@endpush
