{{-- resources/views/admin/reports/customers/new-vs-returning.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'New vs Returning Customers - Coffee Admin')

@push('styles')
<style>
    .comparison-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .comparison-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 15s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.1); opacity: 0.5; }
    }

    .split-metric {
        position: relative;
        z-index: 1;
    }

    .metric-side {
        text-align: center;
        padding: 25px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .metric-side:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-5px);
    }

    .metric-side.new {
        border-left: 4px solid #17a2b8;
    }

    .metric-side.returning {
        border-left: 4px solid #28a745;
    }

    .metric-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 15px;
        background: rgba(255, 255, 255, 0.2);
    }

    .metric-value {
        font-size: 3rem;
        font-weight: bold;
        margin: 15px 0;
    }

    .metric-label {
        font-size: 1rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .metric-subtext {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-top: 10px;
    }

    .vs-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
    }

    .vs-badge {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: white;
        color: #5B914C;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .comparison-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .stat-comparison {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 20px;
        align-items: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .stat-left, .stat-right {
        text-align: center;
    }

    .stat-left {
        text-align: right;
    }

    .stat-right {
        text-align: left;
    }

    .stat-value-large {
        font-size: 2rem;
        font-weight: bold;
        display: block;
    }

    .stat-value-large.new {
        color: #17a2b8;
    }

    .stat-value-large.returning {
        color: #28a745;
    }

    .stat-label-small {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 5px;
    }

    .stat-divider {
        width: 2px;
        height: 60px;
        background: #dee2e6;
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

    .trend-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .trend-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .trend-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #212529;
    }

    .trend-change {
        font-size: 1.2rem;
        font-weight: bold;
        padding: 5px 15px;
        border-radius: 20px;
    }

    .trend-change.positive {
        background: #d4edda;
        color: #155724;
    }

    .trend-change.negative {
        background: #f8d7da;
        color: #721c24;
    }

    .progress-comparison {
        position: relative;
        height: 40px;
        background: #e9ecef;
        border-radius: 20px;
        overflow: hidden;
        margin: 20px 0;
    }

    .progress-bar-split {
        height: 100%;
        display: flex;
    }

    .progress-new {
        background: linear-gradient(90deg, #17a2b8 0%, #138496 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        transition: flex 1s ease;
    }

    .progress-returning {
        background: linear-gradient(90deg, #28a745 0%, #218838 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        transition: flex 1s ease;
    }

    .cohort-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .cohort-table thead {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
    }

    .cohort-table th {
        font-weight: 600;
        padding: 15px;
        border: none;
        text-align: center;
    }

    .cohort-table td {
        padding: 15px;
        vertical-align: middle;
        text-align: center;
    }

    .cohort-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .cohort-table tbody tr:hover {
        background-color: #f8fdf6;
    }

    .retention-cell {
        font-weight: 600;
        border-radius: 8px;
        padding: 8px;
    }

    .retention-high {
        background: #d4edda;
        color: #155724;
    }

    .retention-medium {
        background: #fff3cd;
        color: #856404;
    }

    .retention-low {
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

    .info-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 4px solid #2196f3;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 20px;
        margin-bottom: 10px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 8px;
    }

    .legend-color.new {
        background: #17a2b8;
    }

    .legend-color.returning {
        background: #28a745;
    }

    @media print {
        .no-print { display: none !important; }
    }

    @media (max-width: 768px) {
        .stat-comparison {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .stat-divider {
            width: 100%;
            height: 2px;
        }
        .stat-left, .stat-right {
            text-align: center !important;
        }
    }
</style>
@endpush

@section('content')
<div class="new-vs-returning-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-diagram-3 text-brand"></i> New vs Returning Customers
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">New vs Returning</li>
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
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf"></i> Export to PDF</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-excel"></i> Export to Excel</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-file-csv"></i> Export to CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Comparison Header -->
    <div class="comparison-header">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="metric-side new">
                    <div class="metric-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="metric-value">{{ number_format($new_customers_count) }}</div>
                    <div class="metric-label">New Customers</div>
                    <div class="metric-subtext">{{ ($new_orders_count + $returning_orders_count) > 0 ? number_format($new_percentage, 1) : 0 }}% of total orders</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="vs-divider">
                    <div class="vs-badge">VS</div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="metric-side returning">
                    <div class="metric-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="metric-value">{{ number_format($returning_customers_count) }}</div>
                    <div class="metric-label">Returning Customers</div>
                    <div class="metric-subtext">{{ ($new_orders_count + $returning_orders_count) > 0 ? number_format($returning_percentage, 1) : 0 }}% of total orders</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section no-print">
        <form method="GET" action="{{ route('admin.reports.customers.new-vs-returning') }}">
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
                        <i class="bi bi-funnel"></i> Period
                    </label>
                    <select class="form-select" name="period" onchange="this.form.submit()">
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        <option value="7days" {{ request('period') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ request('period') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90days" {{ request('period') == '90days' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i> Apply Filter
                    </button>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i>
                Showing data from <strong>{{ $start_date->format('M d, Y') }}</strong>
                to <strong>{{ $end_date->format('M d, Y') }}</strong>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <strong>Definition:</strong>
        A "New Customer" is making their first purchase. A "Returning Customer" has made 2+ purchases.
        This helps track customer acquisition vs retention effectiveness.
    </div>

    <!-- Visual Distribution -->
    <div class="comparison-card">
        <h5 class="mb-4">
            <i class="bi bi-pie-chart text-brand"></i> Customer Distribution
        </h5>
        <div class="progress-comparison">
            <div class="progress-bar-split">
                <div class="progress-new" style="flex: {{ $new_percentage }}">
                    {{ number_format($new_percentage, 1) }}% New
                </div>
                <div class="progress-returning" style="flex: {{ $returning_percentage }}">
                    {{ number_format($returning_percentage, 1) }}% Returning
                </div>
            </div>
        </div>
        <div class="text-center">
            <div class="legend-item">
                <div class="legend-color new"></div>
                <span>New Customers (First Order)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color returning"></div>
                <span>Returning Customers (2+ Orders)</span>
            </div>
        </div>
    </div>

    <!-- Key Metrics Comparison -->
    <div class="comparison-card">
        <h5 class="mb-4">
            <i class="bi bi-graph-up text-brand"></i> Performance Comparison
        </h5>

        <!-- Revenue Comparison -->
        <div class="stat-comparison">
            <div class="stat-left">
                <span class="stat-value-large new">{{ store_currency_symbol() }}{{ number_format($new_revenue, 2) }}</span>
                <div class="stat-label-small">New Customer Revenue</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-right">
                <span class="stat-value-large returning">{{ store_currency_symbol() }}{{ number_format($returning_revenue, 2) }}</span>
                <div class="stat-label-small">Returning Customer Revenue</div>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="stat-comparison">
            <div class="stat-left">
                <span class="stat-value-large new">{{ store_currency_symbol() }}{{ number_format($new_aov, 2) }}</span>
                <div class="stat-label-small">Avg Order Value (New)</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-right">
                <span class="stat-value-large returning">{{ store_currency_symbol() }}{{ number_format($returning_aov, 2) }}</span>
                <div class="stat-label-small">Avg Order Value (Returning)</div>
            </div>
        </div>

        <!-- Orders Count -->
        <div class="stat-comparison">
            <div class="stat-left">
                <span class="stat-value-large new">{{ number_format($new_orders_count) }}</span>
                <div class="stat-label-small">Orders from New Customers</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-right">
                <span class="stat-value-large returning">{{ number_format($returning_orders_count) }}</span>
                <div class="stat-label-small">Orders from Returning</div>
            </div>
        </div>
    </div>

    <!-- Trend Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-graph-up text-brand"></i> Customer Trend
                </h5>
                <div class="chart-container">
                    <canvas id="customerTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="chart-section">
                <h5 class="mb-4">
                    <i class="bi bi-cash-stack text-brand"></i> Revenue Trend
                </h5>
                <div class="chart-container">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    @if(count($monthly_breakdown) > 0)
    <div class="comparison-card">
        <h5 class="mb-4">
            <i class="bi bi-calendar-month text-brand"></i> Monthly Breakdown
        </h5>
        <div class="table-responsive">
            <table class="table cohort-table mb-0">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>New Customers</th>
                        <th>Returning Customers</th>
                        <th>New Revenue</th>
                        <th>Returning Revenue</th>
                        <th>Retention Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthly_breakdown as $month)
                    @php
                        $total_customers = $month['new_customers'] + $month['returning_customers'];
                        $retention_rate = $total_customers > 0 ? ($month['returning_customers'] / $total_customers) * 100 : 0;

                        if ($retention_rate >= 50) {
                            $retention_class = 'retention-high';
                        } elseif ($retention_rate >= 30) {
                            $retention_class = 'retention-medium';
                        } else {
                            $retention_class = 'retention-low';
                        }
                    @endphp
                    <tr>
                        <td><strong>{{ $month['month'] }}</strong></td>
                        <td><span class="badge bg-info">{{ number_format($month['new_customers']) }}</span></td>
                        <td><span class="badge bg-success">{{ number_format($month['returning_customers']) }}</span></td>
                        <td>{{ store_currency_symbol() }}{{ number_format($month['new_revenue'], 2) }}</td>
                        <td>{{ store_currency_symbol() }}{{ number_format($month['returning_revenue'], 2) }}</td>
                        <td>
                            <span class="retention-cell {{ $retention_class }}">
                                {{ number_format($retention_rate, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Insights -->
    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="trend-card">
                <div class="trend-header">
                    <div class="trend-title">
                        <i class="bi bi-trophy text-warning"></i> Customer Acquisition
                    </div>
                    <div class="trend-change {{ $new_customers_count > $returning_customers_count ? 'positive' : 'negative' }}">
                        {{ $new_customers_count > $returning_customers_count ? '+' : '' }}{{ number_format(abs($new_customers_count - $returning_customers_count)) }}
                    </div>
                </div>
                <p class="mb-0">
                    @if($new_customers_count > $returning_customers_count)
                    <i class="bi bi-arrow-up-circle text-success"></i>
                    Great! You're acquiring more new customers than returning ones. Focus on retention strategies.
                    @else
                    <i class="bi bi-check-circle text-success"></i>
                    Excellent retention! More customers are returning. This indicates strong customer satisfaction.
                    @endif
                </p>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="trend-card">
                <div class="trend-header">
                    <div class="trend-title">
                        <i class="bi bi-graph-up-arrow text-success"></i> Revenue Impact
                    </div>
                    <div class="trend-change {{ $returning_revenue > $new_revenue ? 'positive' : 'negative' }}">
                        {{ ($new_revenue + $returning_revenue) > 0 ? number_format((($returning_revenue / ($new_revenue + $returning_revenue)) * 100), 1) : 0 }}%
                    </div>
                </div>
                <p class="mb-0">
                    @if($returning_revenue > $new_revenue)
                    <i class="bi bi-star text-warning"></i>
                    Returning customers generate more revenue - a sign of strong customer loyalty and repeat business.
                    @else
                    <i class="bi bi-lightbulb text-info"></i>
                    New customers drive more revenue. Consider implementing loyalty programs to increase repeat purchases.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="alert alert-light border">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-graph-up text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Customer Acquisition</h6>
                <p class="text-muted small mb-0">
                    Track effectiveness of marketing campaigns in bringing new customers.
                </p>
            </div>
            <div class="col-md-4 text-center border-start border-end">
                <i class="bi bi-arrow-repeat text-success fs-3 mb-2"></i>
                <h6 class="fw-bold">Customer Retention</h6>
                <p class="text-muted small mb-0">
                    Measure success in converting first-time buyers into repeat customers.
                </p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-cash-stack text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold">Revenue Balance</h6>
                <p class="text-muted small mb-0">
                    Understand revenue split between acquisition and retention strategies.
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
    // Customer Trend Chart
    const trendCtx = document.getElementById('customerTrendChart');
    if (trendCtx) {
        const trendData = @json($trend_data ?? []);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'New Customers',
                        data: trendData.map(d => d.new_customers),
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Returning Customers',
                        data: trendData.map(d => d.returning_customers),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
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
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        const trendData = @json($trend_data ?? []);

        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'New Customer Revenue',
                        data: trendData.map(d => d.new_revenue),
                        backgroundColor: '#17a2b8',
                        borderRadius: 8
                    },
                    {
                        label: 'Returning Customer Revenue',
                        data: trendData.map(d => d.returning_revenue),
                        backgroundColor: '#28a745',
                        borderRadius: 8
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
                                return context.dataset.label + ': {{ store_currency_symbol() }}' + context.parsed.y.toLocaleString('en-US', {
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

    // Animate progress bars
    setTimeout(() => {
        document.querySelectorAll('.progress-new, .progress-returning').forEach(bar => {
            const flex = bar.style.flex;
            bar.style.flex = '0';
            setTimeout(() => {
                bar.style.flex = flex;
            }, 100);
        });
    }, 200);
});
</script>
@endpush
