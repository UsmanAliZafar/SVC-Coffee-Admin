@extends('admin.layouts.app')

@section('title', 'Inventory Alerts')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-bell-fill text-danger"></i>
                Inventory Alerts
            </h1>
            <p class="text-muted mb-0">Monitor stock levels and take action on critical alerts</p>
        </div>

        <div>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('admin.reports.inventory.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    {{-- Alert Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Critical Alerts</h6>
                            <h3 class="mb-0 text-danger">{{ $critical_count }}</h3>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-x-circle-fill" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Stock</h6>
                            <h3 class="mb-0 text-warning">{{ $low_stock_count }}</h3>
                            <small class="text-muted">Below Threshold</small>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Overstock</h6>
                            <h3 class="mb-0 text-info">{{ $overstock_count }}</h3>
                            <small class="text-muted">Excess Inventory</small>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-info-circle-fill" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Value at Risk</h6>
                            <h3 class="mb-0">{{ store_currency_symbol() }}{{ number_format($value_at_risk, 0) }}</h3>
                            <small class="text-muted">Potential Loss</small>
                        </div>
                        <div class="text-secondary">
                            <i class="bi bi-cash-stack" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Trend Chart --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Alert Trends (Last 30 Days)</h5>
        </div>
        <div class="card-body">
            <canvas id="alertTrendChart" height="80"></canvas>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.inventory.alerts') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Alert Type</label>
                        <select name="alert_type" class="form-select">
                            <option value="all" {{ request('alert_type') == 'all' ? 'selected' : '' }}>All Alerts</option>
                            <option value="critical" {{ request('alert_type') == 'critical' ? 'selected' : '' }}>Critical Only</option>
                            <option value="low_stock" {{ request('alert_type') == 'low_stock' ? 'selected' : '' }}>Low Stock Only</option>
                            <option value="overstock" {{ request('alert_type') == 'overstock' ? 'selected' : '' }}>Overstock Only</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Search Product</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or SKU" value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="severity_desc" {{ request('sort') == 'severity_desc' ? 'selected' : '' }}>Severity (High to Low)</option>
                            <option value="severity_asc" {{ request('sort') == 'severity_asc' ? 'selected' : '' }}>Severity (Low to High)</option>
                            <option value="stock_asc" {{ request('sort') == 'stock_asc' ? 'selected' : '' }}>Stock (Low to High)</option>
                            <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock (High to Low)</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.reports.inventory.alerts') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Alerts Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Active Alerts ({{ $total_alerts }})</h5>

            @if($total_alerts > 0)
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-danger" onclick="bulkAction('resolve')">
                    <i class="bi bi-check-circle"></i> Resolve Selected
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="bulkAction('reorder')">
                    <i class="bi bi-cart-plus"></i> Create Reorder
                </button>
            </div>
            @endif
        </div>
        <div class="card-body p-0">
            @if($alerts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th width="60">Severity</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Alert</th>
                            <th>Value</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input alert-checkbox"
                                       value="{{ $alert['product']->id }}">
                            </td>
                            <td>
                                <span class="badge bg-{{ $alert['color'] }} rounded-pill">
                                    <i class="{{ $alert['icon'] }}"></i>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($alert['product']->main_image)
                                    <img src="{{ $alert['product']->getMainImageUrl() }}"
                                         alt="{{ $alert['product']->name }}"
                                         class="rounded me-2"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.products.edit', $alert['product']->id) }}"
                                           class="text-decoration-none fw-medium">
                                            {{ $alert['product']->name }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code>{{ $alert['product']->sku }}</code>
                            </td>
                            <td>
                                @if($alert['product']->category)
                                <span class="badge bg-light text-dark">
                                    {{ $alert['product']->category->title }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $alert['type'] === 'critical' ? 'bg-danger' : ($alert['type'] === 'low_stock' ? 'bg-warning text-dark' : 'bg-info') }}">
                                    {{ number_format($alert['product']->stock_quantity) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">{{ number_format($alert['product']->low_stock_threshold) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="{{ $alert['icon'] }} text-{{ $alert['color'] }} me-2"></i>
                                    <span>{{ $alert['message'] }}</span>
                                </div>
                                @if(isset($alert['percentage']) && $alert['percentage'] < 50)
                                <small class="text-danger d-block">
                                    <i class="bi bi-arrow-down"></i> {{ number_format($alert['percentage'], 1) }}% of threshold
                                </small>
                                @endif
                            </td>
                            <td>
                                {{ store_currency_symbol() }}{{ number_format($alert['product']->price * $alert['product']->stock_quantity, 2) }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.products.edit', $alert['product']->id) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit Product">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-success"
                                            onclick="adjustStock('{{ $alert['product']->id }}')"
                                            title="Adjust Stock">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-info"
                                            onclick="createReorder('{{ $alert['product']->id }}')"
                                            title="Create Reorder">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No Active Alerts</h4>
                <p class="text-muted">All inventory levels are healthy!</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Chart Script --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Alert Trend Chart
const ctx = document.getElementById('alertTrendChart').getContext('2d');
const alertTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($alert_history, 'date')) !!},
        datasets: [
            {
                label: 'Critical Alerts',
                data: {!! json_encode(array_column($alert_history, 'critical')) !!},
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            },
            {
                label: 'Low Stock Alerts',
                data: {!! json_encode(array_column($alert_history, 'low_stock')) !!},
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.alert-checkbox').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Bulk actions
function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.alert-checkbox:checked'))
        .map(cb => cb.value);

    if (selected.length === 0) {
        alert('Please select at least one product');
        return;
    }

    if (confirm(`${action === 'resolve' ? 'Resolve' : 'Create reorder for'} ${selected.length} selected items?`)) {
        console.log(`Bulk ${action}:`, selected);
        // Implement your bulk action logic here
    }
}

// Adjust stock
function adjustStock(productId) {
    const quantity = prompt('Enter quantity to add:');
    if (quantity && !isNaN(quantity) && parseInt(quantity) > 0) {
        console.log(`Adjust stock for product ${productId}: +${quantity}`);
        // Implement stock adjustment logic
    }
}

// Create reorder
function createReorder(productId) {
    const quantity = prompt('Enter reorder quantity:');
    if (quantity && !isNaN(quantity) && parseInt(quantity) > 0) {
        console.log(`Create reorder for product ${productId}: ${quantity} units`);
        // Implement reorder creation logic
    }
}
</script>
@endpush
@endsection
