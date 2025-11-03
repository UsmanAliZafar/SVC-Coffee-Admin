{{-- resources/views/admin/reports/inventory/movements.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Inventory Movements - Coffee Admin')

@push('styles')
<style>
    .movement-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .movement-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .summary-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
    }

    .summary-card .icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 15px;
    }

    .summary-card .value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .summary-card .label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .timeline-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .movement-timeline {
        position: relative;
        padding-left: 40px;
        margin-top: 30px;
    }

    .movement-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #5B914C 0%, #e9ecef 100%);
    }

    .movement-item {
        position: relative;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .movement-item:hover {
        transform: translateX(5px);
    }

    .movement-dot {
        position: absolute;
        left: -30px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 0 0 3px;
        z-index: 2;
    }

    .movement-dot.in {
        background: #28a745;
        box-shadow: 0 0 0 3px #28a745;
    }

    .movement-dot.out {
        background: #dc3545;
        box-shadow: 0 0 0 3px #dc3545;
    }

    .movement-dot.adjustment {
        background: #ffc107;
        box-shadow: 0 0 0 3px #ffc107;
    }

    .movement-card {
        background: white;
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .movement-card:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.15);
    }

    .movement-card.in {
        border-left: 4px solid #28a745;
        background: linear-gradient(90deg, #f0fff4 0%, white 10%);
    }

    .movement-card.out {
        border-left: 4px solid #dc3545;
        background: linear-gradient(90deg, #fff5f5 0%, white 10%);
    }

    .movement-card.adjustment {
        border-left: 4px solid #ffc107;
        background: linear-gradient(90deg, #fffef5 0%, white 10%);
    }

    .movement-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .movement-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .movement-type-badge.in {
        background: #d4edda;
        color: #155724;
    }

    .movement-type-badge.out {
        background: #f8d7da;
        color: #721c24;
    }

    .movement-type-badge.adjustment {
        background: #fff3cd;
        color: #856404;
    }

    .movement-quantity {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .movement-quantity.positive {
        color: #28a745;
    }

    .movement-quantity.negative {
        color: #dc3545;
    }

    .movement-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .detail-item {
        text-align: center;
    }

    .detail-value {
        font-weight: bold;
        color: #212529;
        display: block;
        margin-bottom: 3px;
    }

    .detail-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
    }

    .product-info-inline {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .product-thumb {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .product-details {
        flex-grow: 1;
    }

    .product-name-inline {
        font-weight: 700;
        color: #212529;
        margin-bottom: 3px;
    }

    .product-sku-inline {
        font-size: 0.875rem;
        color: #6c757d;
        font-family: 'Courier New', monospace;
    }

    .filter-panel {
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
        height: 350px;
        position: relative;
    }

    .movement-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #5B914C;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(91, 145, 76, 0.15);
    }

    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-card.in .stat-icon {
        background: #d4edda;
        color: #155724;
    }

    .stat-card.out .stat-icon {
        background: #f8d7da;
        color: #721c24;
    }

    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #5B914C;
        margin: 10px 0;
    }

    .stat-card .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .date-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #f8f9fa;
        border-radius: 15px;
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
    }

    .reference-link {
        color: #5B914C;
        text-decoration: none;
        font-weight: 600;
    }

    .reference-link:hover {
        text-decoration: underline;
    }

    .notes-box {
        background: #f8f9fa;
        border-left: 3px solid #5B914C;
        padding: 12px 15px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 0.875rem;
        color: #495057;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    @media print {
        .no-print { display: none !important; }
        .movement-item { break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .movement-timeline { padding-left: 30px; }
        .movement-details { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="inventory-movements-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-arrow-left-right text-brand"></i> Inventory Movements
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Movements</li>
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

    <!-- Movement Header Summary -->
    <div class="movement-header">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="value">{{ number_format($total_movements) }}</div>
                    <div class="label">Total Movements</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="value">{{ number_format($total_in) }}</div>
                    <div class="label">Stock In</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                    <div class="value">{{ number_format($total_out) }}</div>
                    <div class="label">Stock Out</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="summary-card">
                    <div class="icon">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="value">{{ number_format($net_change) }}</div>
                    <div class="label">Net Change</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movement Statistics -->
    <div class="movement-stats-grid">
        <div class="stat-card in">
            <div class="stat-icon">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div class="stat-value">{{ number_format($stock_in_quantity) }}</div>
            <div class="stat-label">Units Added</div>
        </div>
        <div class="stat-card out">
            <div class="stat-icon">
                <i class="bi bi-dash-circle"></i>
            </div>
            <div class="stat-value">{{ number_format($stock_out_quantity) }}</div>
            <div class="stat-label">Units Removed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3cd; color: #856404;">
                <i class="bi bi-wrench"></i>
            </div>
            <div class="stat-value">{{ number_format($adjustments_count) }}</div>
            <div class="stat-label">Adjustments Made</div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel no-print">
        <form method="GET" action="{{ route('admin.reports.inventory.movement') }}">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> Start Date
                    </label>
                    <input type="date" class="form-control" name="start_date"
                           value="{{ $start_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-range"></i> End Date
                    </label>
                    <input type="date" class="form-control" name="end_date"
                           value="{{ $end_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-tag"></i> Category
                    </label>
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        @if(isset($movement_categories))
                        @foreach($movement_categories as $key => $label)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-building"></i> Warehouse
                    </label>
                    <select class="form-select" name="warehouse_id">
                        <option value="">All Warehouses</option>
                        @if(isset($warehouses))
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search"></i> Product
                    </label>
                    <input type="text" class="form-control" name="product"
                           placeholder="Name or SKU..."
                           value="{{ request('product') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing movements from <strong>{{ $start_date->format('M d, Y') }}</strong>
                to <strong>{{ $end_date->format('M d, Y') }}</strong>
                @if(request('category'))
                | Category: <strong class="text-capitalize">{{ request('category') }}</strong>
                @endif
                @if(request('warehouse_id') && isset($warehouses))
                @php
                    $selectedWarehouse = $warehouses->firstWhere('id', request('warehouse_id'));
                @endphp
                | Warehouse: <strong>{{ $selectedWarehouse->name ?? 'Unknown' }}</strong>
                @endif
                @if(request('product'))
                | Search: <strong>"{{ request('product') }}"</strong>
                @endif
            </div>
        </form>
    </div>

    <!-- Movement Trends Chart -->
    @if($movements->count() > 0)
    <div class="chart-section">
        <h5 class="mb-4">
            <i class="bi bi-graph-up text-brand"></i> Movement Trends
        </h5>
        <div class="chart-container">
            <canvas id="movementTrendsChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Movements Timeline -->
    @if($movements->count() > 0)
    <div class="timeline-container">
        <h5 class="mb-4">
            <i class="bi bi-clock-history text-brand"></i> Movement History
            <span class="badge bg-brand ms-2">{{ $movements->count() }} Records</span>
        </h5>

        <div class="movement-timeline">
            @foreach($movements as $movement)
            @php
                // Use model methods to determine type
                $movementType = $movement->isIncrease() ? 'in' : ($movement->isDecrease() ? 'out' : 'adjustment');
                $quantityClass = $movement->quantity >= 0 ? 'positive' : 'negative';
            @endphp
            <div class="movement-item">
                <div class="movement-dot {{ $movementType }}"></div>

                <div class="movement-card {{ $movementType }}">
                    <!-- Movement Header -->
                    <div class="movement-header-row">
                        <div>
                            {!! $movement->getTypeBadge() !!}
                            <span class="date-badge ms-2">
                                <i class="bi bi-calendar"></i> {{ $movement->created_at->format('M d, Y H:i') }}
                            </span>
                        </div>
                        <div class="movement-quantity {{ $quantityClass }}">
                            {{ $movement->getFormattedQuantity() }}
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info-inline">
                        @if($movement->product && $movement->product->main_image)
                        <img src="{{ asset('storage/' . $movement->product->main_image) }}"
                             alt="{{ $movement->product->name }}"
                             class="product-thumb"
                             onerror="this.src='{{ asset('images/placeholders/not_availble.jpg') }}'">
                        @else
                        <div class="product-thumb bg-light d-flex align-items-center justify-content-center">
                            <i class="bi {{ $movement->getTypeIcon() }} text-muted"></i>
                        </div>
                        @endif

                        <div class="product-details">
                            <div class="product-name-inline">{{ $movement->product->name ?? 'Unknown Product' }}</div>
                            <div class="product-sku-inline">SKU: {{ $movement->product->sku ?? 'N/A' }}</div>
                            @if($movement->warehouse)
                            <small class="text-muted">
                                <i class="bi bi-building"></i> {{ $movement->warehouse->name }}
                            </small>
                            @endif
                        </div>
                    </div>

                    <!-- Movement Details -->
                    <div class="movement-details">
                        <div class="detail-item">
                            <span class="detail-value">{{ number_format($movement->previous_quantity) }}</span>
                            <span class="detail-label">Before</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-value">{{ number_format($movement->new_quantity) }}</span>
                            <span class="detail-label">After</span>
                        </div>
                        @if($movement->reference_type && $movement->reference_id)
                        <div class="detail-item">
                            <span class="detail-value">
                                <a href="#" class="reference-link">
                                    #{{ $movement->reference_id }}
                                </a>
                            </span>
                            <span class="detail-label">{{ ucfirst($movement->reference_type) }}</span>
                        </div>
                        @endif
                        @if($movement->creator)
                        <div class="detail-item">
                            <span class="detail-value">{{ $movement->creator->name }}</span>
                            <span class="detail-label">By User</span>
                        </div>
                        @endif
                    </div>

                    <!-- Transfer Information -->
                    @if($movement->type == 'transfer' && $movement->fromWarehouse && $movement->toWarehouse)
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <small>
                            <i class="bi bi-arrow-left-right"></i>
                            <strong>Transfer:</strong>
                            {{ $movement->fromWarehouse->name }}
                            <i class="bi bi-arrow-right"></i>
                            {{ $movement->toWarehouse->name }}
                        </small>
                    </div>
                    @endif

                    <!-- Reason -->
                    @if($movement->reason)
                    <div class="alert alert-secondary mt-3 mb-0 py-2">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            <strong>Reason:</strong> {{ $movement->reason }}
                        </small>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($movement->notes)
                    <div class="notes-box">
                        <i class="bi bi-sticky"></i> <strong>Notes:</strong> {{ $movement->notes }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h5>No Movements Found</h5>
        <p class="text-muted">No inventory movements recorded for the selected filters.</p>
    </div>
    @endif

    <!-- Info Footer -->
    <div class="alert alert-light border mt-4">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-clock-history text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Complete History</h6>
                <p class="text-muted small mb-0">
                    Every stock movement is tracked with timestamp, user, and reason.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-shield-check text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Audit Trail</h6>
                <p class="text-muted small mb-0">
                    Maintain compliance with complete inventory audit trails.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up-arrow text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Trend Analysis</h6>
                <p class="text-muted small mb-0">
                    Identify patterns in stock movements to optimize inventory.
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
    const chartCtx = document.getElementById('movementTrendsChart');
    if (chartCtx) {
        // Sample data - would be populated from backend
        const trendData = @json($movement_trends ?? []);

        new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'Stock In',
                        data: trendData.map(d => d.stock_in),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Stock Out',
                        data: trendData.map(d => d.stock_out),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Net Change',
                        data: trendData.map(d => d.net_change),
                        borderColor: '#5B914C',
                        backgroundColor: 'rgba(91, 145, 76, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        borderDash: [5, 5]
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
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' units';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' units';
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
