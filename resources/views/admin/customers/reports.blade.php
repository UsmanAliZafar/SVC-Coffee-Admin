@extends('admin.layouts.app')

@section('title', 'Customer Reports & Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .page-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.5rem;
    }
    .chart-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    .chart-container {
        position: relative;
        height: 350px;
        padding: 1rem;
    }
    .metric-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
        height: 100%;
    }
    .metric-box:hover {
        border-color: #5B914C;
        background: rgba(91, 145, 76, 0.05);
        transform: translateY(-2px);
    }
    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #5B914C;
        margin: 0.5rem 0;
    }
    .metric-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .trend-up {
        color: #28a745;
    }
    .trend-down {
        color: #dc3545;
    }
    .segment-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s;
        height: 100%;
    }
    .segment-card:hover {
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.2);
        transform: translateY(-3px);
    }
    .segment-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    .progress-thin {
        height: 8px;
        border-radius: 4px;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 2px solid #e9ecef;
    }
    .export-btn-group .btn {
        border-radius: 8px;
    }
    .info-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .kpi-card {
        background: linear-gradient(135deg, rgba(91, 145, 76, 0.05) 0%, rgba(74, 122, 61, 0.05) 100%);
        border-left: 4px solid #5B914C;
        border-radius: 8px;
        padding: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-graph-up-arrow text-primary"></i> Customer Reports & Analytics
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.customers.index') }}">Customers</a>
                    </li>
                    <li class="breadcrumb-item active">Reports & Analytics</li>
                </ol>
            </nav>
        </div>
        <div class="export-btn-group d-flex gap-2">
            <button class="btn btn-success" id="exportPdfBtn">
                <i class="bi bi-file-pdf-fill"></i> Export PDF
            </button>
            <button class="btn btn-outline-success" id="exportCsvBtn">
                <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
            </button>
            <button class="btn btn-outline-primary" id="refreshDataBtn">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-range"></i> Date Range
                </label>
                <input type="text" class="form-control" id="dateRangePicker" placeholder="Select date range">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-speedometer2"></i> Quick Ranges
                </label>
                <select class="form-select" id="quickRange">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month" selected>This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="all_time">All Time</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-funnel"></i> Customer Type
                </label>
                <select class="form-select" id="filterCustomerType">
                    <option value="">All Types</option>
                    <option value="individual">Individual</option>
                    <option value="business">Business</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="vip">VIP</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label invisible">Action</label>
                <button class="btn btn-primary w-100" id="applyFiltersBtn">
                    <i class="bi bi-search"></i> Apply
                </button>
            </div>
        </div>
    </div>

    {{-- Key Performance Indicators --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Total Customers</div>
                            <div class="stat-value" id="totalCustomers">{{ number_format($stats['total_customers']) }}</div>
                            <small class="text-muted" id="totalCustomersTrend"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Active Customers</div>
                            <div class="stat-value" id="activeCustomers">{{ number_format($stats['active_customers']) }}</div>
                            <small class="text-muted">
                                {{ $stats['total_customers'] > 0 ? number_format(($stats['active_customers'] / $stats['total_customers']) * 100, 1) : 0 }}% of total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">New This Month</div>
                            <div class="stat-value" id="newCustomers">{{ number_format($stats['new_this_month']) }}</div>
                            <small class="text-muted" id="newCustomersTrend"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-value" id="totalRevenue">{{ store_currency_symbol() }}{{ number_format($stats['total_lifetime_value'], 2) }}</div>
                            <small class="text-muted">Lifetime value</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Metrics Row --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <i class="bi bi-graph-up text-success fs-2 mb-2"></i>
                <div class="metric-label">Avg Customer Value</div>
                <div class="metric-value" id="avgCustomerValue">{{ store_currency_symbol() }}{{ number_format($stats['avg_customer_value'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <i class="bi bi-arrow-repeat text-primary fs-2 mb-2"></i>
                <div class="metric-label">Returning Rate</div>
                <div class="metric-value" id="returningRate">
                    @php
                        $returningCount = \App\Models\Customer::returning()->count();
                        $returningRate = $stats['total_customers'] > 0 ? ($returningCount / $stats['total_customers'] * 100) : 0;
                    @endphp
                    {{ number_format($returningRate, 1) }}%
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <i class="bi bi-trophy text-warning fs-2 mb-2"></i>
                <div class="metric-label">High Value</div>
                <div class="metric-value" id="highValueCount">
                    @php
                        $highValueCount = \App\Models\Customer::highValue(1000)->count();
                    @endphp
                    {{ number_format($highValueCount) }}
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box">
                <i class="bi bi-exclamation-triangle text-danger fs-2 mb-2"></i>
                <div class="metric-label">At Risk</div>
                <div class="metric-value" id="atRiskCount">
                    @php
                        $atRiskCount = \App\Models\Customer::atRisk(90)->count();
                    @endphp
                    {{ number_format($atRiskCount) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Customer Growth Chart --}}
        <div class="col-lg-8 mb-4">
            <div class="card chart-card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-graph-up text-primary"></i> Customer Growth Trend
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="customerGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer Segments Pie --}}
        <div class="col-lg-4 mb-4">
            <div class="card chart-card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pie-chart text-success"></i> Customer Segments
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="segmentsPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Type Distribution --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="segment-card">
                <div class="segment-icon">👤</div>
                <h6 class="fw-bold">Individual</h6>
                <h3 class="text-primary mb-2" id="individualCount">
                    @php
                        $individualCount = \App\Models\Customer::individual()->count();
                    @endphp
                    {{ number_format($individualCount) }}
                </h3>
                <div class="progress progress-thin mb-2">
                    <div class="progress-bar bg-primary" role="progressbar"
                         style="width: {{ $stats['total_customers'] > 0 ? ($individualCount / $stats['total_customers'] * 100) : 0 }}%"></div>
                </div>
                <small class="text-muted">
                    {{ $stats['total_customers'] > 0 ? number_format(($individualCount / $stats['total_customers'] * 100), 1) : 0 }}% of total
                </small>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="segment-card">
                <div class="segment-icon">🏢</div>
                <h6 class="fw-bold">Business</h6>
                <h3 class="text-info mb-2" id="businessCount">
                    @php
                        $businessCount = \App\Models\Customer::business()->count();
                    @endphp
                    {{ number_format($businessCount) }}
                </h3>
                <div class="progress progress-thin mb-2">
                    <div class="progress-bar bg-info" role="progressbar"
                         style="width: {{ $stats['total_customers'] > 0 ? ($businessCount / $stats['total_customers'] * 100) : 0 }}%"></div>
                </div>
                <small class="text-muted">
                    {{ $stats['total_customers'] > 0 ? number_format(($businessCount / $stats['total_customers'] * 100), 1) : 0 }}% of total
                </small>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="segment-card">
                <div class="segment-icon">📦</div>
                <h6 class="fw-bold">Wholesale</h6>
                <h3 class="text-warning mb-2" id="wholesaleCount">
                    @php
                        $wholesaleCount = \App\Models\Customer::wholesale()->count();
                    @endphp
                    {{ number_format($wholesaleCount) }}
                </h3>
                <div class="progress progress-thin mb-2">
                    <div class="progress-bar bg-warning" role="progressbar"
                         style="width: {{ $stats['total_customers'] > 0 ? ($wholesaleCount / $stats['total_customers'] * 100) : 0 }}%"></div>
                </div>
                <small class="text-muted">
                    {{ $stats['total_customers'] > 0 ? number_format(($wholesaleCount / $stats['total_customers'] * 100), 1) : 0 }}% of total
                </small>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="segment-card">
                <div class="segment-icon">⭐</div>
                <h6 class="fw-bold">VIP</h6>
                <h3 class="text-success mb-2" id="vipCount">
                    @php
                        $vipCount = \App\Models\Customer::vip()->count();
                    @endphp
                    {{ number_format($vipCount) }}
                </h3>
                <div class="progress progress-thin mb-2">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ $stats['total_customers'] > 0 ? ($vipCount / $stats['total_customers'] * 100) : 0 }}%"></div>
                </div>
                <small class="text-muted">
                    {{ $stats['total_customers'] > 0 ? number_format(($vipCount / $stats['total_customers'] * 100), 1) : 0 }}% of total
                </small>
            </div>
        </div>
    </div>

    {{-- Status Distribution & Key Insights --}}
    <div class="row mb-4">
        {{-- Status Distribution --}}
        <div class="col-lg-6 mb-4">
            <div class="card chart-card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-circle-fill text-info"></i> Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Insights --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-lightbulb text-warning"></i> Key Insights
                    </h5>
                </div>
                <div class="card-body">
                    <div class="kpi-card mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Customer Acquisition</h6>
                                <p class="mb-0 text-muted small">
                                    You've gained <strong>{{ number_format($stats['new_this_month']) }}</strong> new customers this month.
                                </p>
                            </div>
                            <i class="bi bi-graph-up-arrow text-success fs-2"></i>
                        </div>
                    </div>

                    <div class="kpi-card mb-3" style="border-left-color: #0dcaf0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Revenue Performance</h6>
                                <p class="mb-0 text-muted small">
                                    Average customer value is <strong>{{ store_currency_symbol() }}{{ number_format($stats['avg_customer_value'], 2) }}</strong>.
                                </p>
                            </div>
                            <i class="bi bi-currency-dollar text-info fs-2"></i>
                        </div>
                    </div>

                    <div class="kpi-card mb-3" style="border-left-color: #ffc107;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Retention Rate</h6>
                                <p class="mb-0 text-muted small">
                                    <strong>{{ number_format($returningRate, 1) }}%</strong> of customers have made repeat purchases.
                                </p>
                            </div>
                            <i class="bi bi-arrow-repeat text-warning fs-2"></i>
                        </div>
                    </div>

                    <div class="kpi-card" style="border-left-color: #dc3545;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">At-Risk Customers</h6>
                                <p class="mb-0 text-muted small">
                                    <strong>{{ number_format($atRiskCount) }}</strong> customers haven't ordered in 90+ days.
                                </p>
                            </div>
                            <i class="bi bi-exclamation-triangle text-danger fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue by Customer Segment --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-bar-chart text-primary"></i> Revenue by Customer Segment
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="revenueSegmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#dateRangePicker').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        }
    });

    // Quick range selector
    $('#quickRange').on('change', function() {
        const range = $(this).val();
        let startDate, endDate;

        switch(range) {
            case 'today':
                startDate = moment();
                endDate = moment();
                break;
            case 'yesterday':
                startDate = moment().subtract(1, 'days');
                endDate = moment().subtract(1, 'days');
                break;
            case 'this_week':
                startDate = moment().startOf('week');
                endDate = moment().endOf('week');
                break;
            case 'last_week':
                startDate = moment().subtract(1, 'week').startOf('week');
                endDate = moment().subtract(1, 'week').endOf('week');
                break;
            case 'this_month':
                startDate = moment().startOf('month');
                endDate = moment().endOf('month');
                break;
            case 'last_month':
                startDate = moment().subtract(1, 'month').startOf('month');
                endDate = moment().subtract(1, 'month').endOf('month');
                break;
            case 'this_year':
                startDate = moment().startOf('year');
                endDate = moment().endOf('year');
                break;
            case 'all_time':
                startDate = moment().subtract(10, 'years');
                endDate = moment();
                break;
        }

        if (startDate && endDate) {
            $('#dateRangePicker').data('daterangepicker').setStartDate(startDate);
            $('#dateRangePicker').data('daterangepicker').setEndDate(endDate);
        }
    });

    // Chart.js default configuration
    Chart.defaults.font.family = 'Inter, system-ui, -apple-system, sans-serif';
    Chart.defaults.color = '#6c757d';

    // Customer Growth Chart
    const growthCtx = document.getElementById('customerGrowthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'New Customers',
                data: [],
                borderColor: '#5B914C',
                backgroundColor: 'rgba(91, 145, 76, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Segments Pie Chart
    const segmentsCtx = document.getElementById('segmentsPieChart').getContext('2d');
    const segmentsChart = new Chart(segmentsCtx, {
        type: 'doughnut',
        data: {
            labels: ['New', 'One-time', 'Regular', 'Loyal', 'High Value', 'At Risk'],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#17a2b8',
                    '#6c757d',
                    '#5B914C',
                    '#0d6efd',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Active', 'Inactive', 'Blocked'],
            datasets: [{
                label: 'Customers',
                data: [
                    {{ $stats['active_customers'] }},
                    {{ \App\Models\Customer::inactive()->count() }},
                    {{ \App\Models\Customer::blocked()->count() }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#6c757d',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Revenue by Segment Chart
    const revenueSegmentCtx = document.getElementById('revenueSegmentChart').getContext('2d');
    const revenueSegmentChart = new Chart(revenueSegmentCtx, {
        type: 'bar',
        data: {
            labels: ['Individual', 'Business', 'Wholesale', 'VIP'],
            datasets: [{
                label: 'Revenue',
                data: [],
                backgroundColor: '#5B914C'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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

    // Load report data
    function loadReportData() {
        const dateRange = $('#dateRangePicker').data('daterangepicker');
        const dateFrom = dateRange.startDate.format('YYYY-MM-DD');
        const dateTo = dateRange.endDate.format('YYYY-MM-DD');
        const customerType = $('#filterCustomerType').val();

        $.ajax({
            url: '{{ route("admin.customers.reports.data") }}',
            data: {
                date_from: dateFrom,
                date_to: dateTo,
                customer_type: customerType
            },
            success: function(response) {
                if (response.success) {
                    // Update KPIs
                    $('#totalCustomers').text(response.total_customers.toLocaleString());
                    $('#activeCustomers').text(response.active_customers.toLocaleString());
                    $('#newCustomers').text(response.new_customers.toLocaleString());
                    $('#totalRevenue').text('{{ store_currency_symbol() }}' + parseFloat(response.total_revenue).toFixed(2));

                    // Update charts
                    growthChart.data.labels = response.growth_trend.labels;
                    growthChart.data.datasets[0].data = response.growth_trend.data;
                    growthChart.update();

                    segmentsChart.data.datasets[0].data = Object.values(response.segments);
                    segmentsChart.update();

                    if (response.revenue_by_type) {
                        revenueSegmentChart.data.datasets[0].data = Object.values(response.revenue_by_type);
                        revenueSegmentChart.update();
                    }
                }
            }
        });
    }

    // Apply filters
    $('#applyFiltersBtn').on('click', function() {
        loadReportData();
    });

    // Refresh data
    $('#refreshDataBtn').on('click', function() {
        const $btn = $(this);
        const $icon = $btn.find('i');

        $icon.addClass('fa-spin');
        loadReportData();

        setTimeout(() => {
            $icon.removeClass('fa-spin');
            Swal.fire({
                icon: 'success',
                title: 'Refreshed!',
                text: 'Report data has been updated',
                timer: 1500,
                showConfirmButton: false
            });
        }, 1000);
    });

    // Export CSV
    $('#exportCsvBtn').on('click', function() {
        const dateRange = $('#dateRangePicker').data('daterangepicker');
        const params = {
            date_from: dateRange.startDate.format('YYYY-MM-DD'),
            date_to: dateRange.endDate.format('YYYY-MM-DD'),
            customer_type: $('#filterCustomerType').val()
        };

        window.location.href = '{{ route("admin.customers.export") }}?' + $.param(params);
    });

    // Export PDF
    $('#exportPdfBtn').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'PDF Export',
            text: 'PDF export feature coming soon. This will generate a comprehensive report with all charts and data.',
            confirmButtonColor: '#5B914C'
        });
    });

    // Initial load
    loadReportData();
});
</script>
@endpush
