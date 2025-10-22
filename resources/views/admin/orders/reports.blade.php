@extends('admin.layouts.app')

@section('title', 'Orders Reports & Analytics')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Orders Reports & Analytics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-outline-success" id="exportReportBtn">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </button>
            <button class="btn btn-outline-primary" id="printReportBtn">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    {{-- Date Range Selector --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="dateRangeForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select class="form-select" id="dateRangePreset">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_7_days">Last 7 Days</option>
                        <option value="last_30_days" selected>Last 30 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-2" id="customDateFrom" style="display: none;">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom">
                </div>
                <div class="col-md-2" id="customDateTo" style="display: none;">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" id="applyDateRange">
                        <i class="bi bi-arrow-clockwise"></i> Update Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Orders</h6>
                            <h3 class="mb-0" id="totalOrders">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                            <small class="text-muted" id="ordersChange">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Revenue</h6>
                            <h3 class="mb-0" id="totalRevenue">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                            <small class="text-muted" id="revenueChange">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="bi bi-graph-up fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Average Order Value</h6>
                            <h3 class="mb-0" id="avgOrderValue">${{ number_format($stats['average_order_value'] ?? 0, 2) }}</h3>
                            <small class="text-muted" id="avgChange">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Orders</h6>
                            <h3 class="mb-0" id="pendingOrders">{{ number_format($stats['pending_orders'] ?? 0) }}</h3>
                            <small class="text-muted">Requires attention</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-graph-up text-primary"></i> Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-pie-chart text-success"></i> Order Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Stats Row --}}
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-bar-chart text-info"></i> Revenue by Source</h5>
                </div>
                <div class="card-body">
                    <canvas id="sourceChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-cash-stack text-warning"></i> Payment Methods</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Status Breakdown --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-list-check text-primary"></i> Order Status Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Pending</h6>
                                    <h4 class="mb-0 text-warning" id="statusPending">{{ number_format($stats['pending_orders'] ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-clock-history fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Processing</h6>
                                    <h4 class="mb-0 text-primary" id="statusProcessing">{{ number_format($stats['processing_orders'] ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-gear fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Shipped</h6>
                                    <h4 class="mb-0 text-info" id="statusShipped">{{ number_format($stats['shipped_orders'] ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-truck fs-2 text-info"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Delivered</h6>
                                    <h4 class="mb-0 text-success" id="statusDelivered">{{ number_format($stats['delivered_orders'] ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-check-circle fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Cancelled</h6>
                                    <h4 class="mb-0 text-danger" id="statusCancelled">{{ number_format($stats['cancelled_orders'] ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-x-circle fs-2 text-danger"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Refunded</h6>
                                    <h4 class="mb-0 text-dark" id="statusRefunded">{{ number_format($stats['refunded_orders'] ?? 0) }}</h4>
                                </div>
                                <i class="bi bi-arrow-counterclockwise fs-2 text-dark"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Completion Rate</h6>
                                    <h4 class="mb-0 text-success" id="completionRate">0%</h4>
                                </div>
                                <i class="bi bi-percent fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <h6 class="text-muted mb-1">Cancellation Rate</h6>
                                    <h4 class="mb-0 text-danger" id="cancellationRate">0%</h4>
                                </div>
                                <i class="bi bi-graph-down fs-2 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-star text-warning"></i> Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsTable">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-people text-primary"></i> Top Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody id="topCustomersTable">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Metrics --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-speedometer text-success"></i> Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="border-start border-4 border-primary ps-3">
                                <h6 class="text-muted mb-1">Average Processing Time</h6>
                                <h4 class="mb-0" id="avgProcessingTime">-</h4>
                                <small class="text-muted">From order to shipped</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border-start border-4 border-success ps-3">
                                <h6 class="text-muted mb-1">Average Delivery Time</h6>
                                <h4 class="mb-0" id="avgDeliveryTime">-</h4>
                                <small class="text-muted">From shipped to delivered</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border-start border-4 border-info ps-3">
                                <h6 class="text-muted mb-1">Customer Satisfaction</h6>
                                <h4 class="mb-0" id="customerSatisfaction">-</h4>
                                <small class="text-muted">Based on completed orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    @media print {
        .btn, nav, .card-header button {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    let salesChart, statusChart, sourceChart, paymentChart;

    // Handle date range preset
    $('#dateRangePreset').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customDateFrom, #customDateTo').show();
        } else {
            $('#customDateFrom, #customDateTo').hide();
            applyDateRange();
        }
    });

    // Apply date range
    $('#applyDateRange').on('click', applyDateRange);

    function applyDateRange() {
        const preset = $('#dateRangePreset').val();
        let dateFrom, dateTo;

        if (preset === 'custom') {
            dateFrom = $('#dateFrom').val();
            dateTo = $('#dateTo').val();
        } else {
            // Calculate dates based on preset
            const today = new Date();
            dateTo = today.toISOString().split('T')[0];

            switch(preset) {
                case 'today':
                    dateFrom = dateTo;
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    dateFrom = yesterday.toISOString().split('T')[0];
                    dateTo = dateFrom;
                    break;
                case 'last_7_days':
                    const week = new Date(today);
                    week.setDate(week.getDate() - 7);
                    dateFrom = week.toISOString().split('T')[0];
                    break;
                case 'last_30_days':
                    const month = new Date(today);
                    month.setDate(month.getDate() - 30);
                    dateFrom = month.toISOString().split('T')[0];
                    break;
                case 'this_month':
                    dateFrom = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    break;
                case 'last_month':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    dateFrom = lastMonth.toISOString().split('T')[0];
                    const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                    dateTo = lastMonthEnd.toISOString().split('T')[0];
                    break;
                case 'this_year':
                    dateFrom = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                    break;
            }
        }

        loadReportData(dateFrom, dateTo);
    }

    function loadReportData(dateFrom, dateTo) {
        $.ajax({
            url: '/admin/orders/reports/data',
            data: { date_from: dateFrom, date_to: dateTo },
            success: function(data) {
                updateMetrics(data);
                updateCharts(data);
                updateTables(data);
                updatePerformanceMetrics(data);
            },
            error: function() {
                Swal.fire('Error', 'Failed to load report data', 'error');
            }
        });
    }

    function updateMetrics(data) {
        $('#totalOrders').text(number_format(data.total_orders || 0));
        $('#totalRevenue').text('$' + number_format(data.total_revenue || 0, 2));
        $('#avgOrderValue').text('$' + number_format(data.avg_order_value || 0, 2));
        $('#pendingOrders').text(number_format(data.pending_orders || 0));

        // Status counts
        $('#statusPending').text(number_format(data.status_breakdown?.pending || 0));
        $('#statusProcessing').text(number_format(data.status_breakdown?.processing || 0));
        $('#statusShipped').text(number_format(data.status_breakdown?.shipped || 0));
        $('#statusDelivered').text(number_format(data.status_breakdown?.delivered || 0));
        $('#statusCancelled').text(number_format(data.status_breakdown?.cancelled || 0));
        $('#statusRefunded').text(number_format(data.status_breakdown?.refunded || 0));

        // Rates
        const completionRate = data.total_orders > 0 ?
            ((data.status_breakdown?.delivered || 0) / data.total_orders * 100).toFixed(1) : 0;
        const cancellationRate = data.total_orders > 0 ?
            ((data.status_breakdown?.cancelled || 0) / data.total_orders * 100).toFixed(1) : 0;

        $('#completionRate').text(completionRate + '%');
        $('#cancellationRate').text(cancellationRate + '%');
    }

    function updateCharts(data) {
        // Sales Chart
        if (salesChart) salesChart.destroy();
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: data.sales_trend?.labels || [],
                datasets: [{
                    label: 'Revenue',
                    data: data.sales_trend?.revenue || [],
                    borderColor: '#5B914C',
                    backgroundColor: 'rgba(91, 145, 76, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: true }
                }
            }
        });

        // Status Chart
        if (statusChart) statusChart.destroy();
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: [
                        data.status_breakdown?.pending || 0,
                        data.status_breakdown?.processing || 0,
                        data.status_breakdown?.shipped || 0,
                        data.status_breakdown?.delivered || 0,
                        data.status_breakdown?.cancelled || 0
                    ],
                    backgroundColor: ['#ffc107', '#0d6efd', '#0dcaf0', '#198754', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Source Chart
        if (sourceChart) sourceChart.destroy();
        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        sourceChart = new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: data.revenue_by_source?.labels || [],
                datasets: [{
                    label: 'Revenue',
                    data: data.revenue_by_source?.data || [],
                    backgroundColor: '#5B914C'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Payment Chart
        if (paymentChart) paymentChart.destroy();
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        paymentChart = new Chart(paymentCtx, {
            type: 'bar',
            data: {
                labels: data.payment_methods?.labels || [],
                datasets: [{
                    label: 'Orders',
                    data: data.payment_methods?.data || [],
                    backgroundColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y'
            }
        });
    }

    function updateTables(data) {
        // Top Products
        let productsHtml = '';
        if (data.top_products && data.top_products.length > 0) {
            data.top_products.forEach((product, index) => {
                productsHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${product.name}</td>
                        <td>${product.quantity}</td>
                        <td><strong>$${number_format(product.revenue, 2)}</strong></td>
                    </tr>
                `;
            });
        } else {
            productsHtml = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
        }
        $('#topProductsTable').html(productsHtml);

        // Top Customers
        let customersHtml = '';
        if (data.top_customers && data.top_customers.length > 0) {
            data.top_customers.forEach((customer, index) => {
                customersHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${customer.name}</td>
                        <td>${customer.orders}</td>
                        <td><strong>$${number_format(customer.total_spent, 2)}</strong></td>
                    </tr>
                `;
            });
        } else {
            customersHtml = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
        }
        $('#topCustomersTable').html(customersHtml);
    }

    function updatePerformanceMetrics(data) {
        $('#avgProcessingTime').text(data.avg_processing_time || '-');
        $('#avgDeliveryTime').text(data.avg_delivery_time || '-');
        $('#customerSatisfaction').text(data.customer_satisfaction || '-');
    }

    function number_format(number, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }

    // Export to Excel
    $('#exportReportBtn').on('click', function() {
        Swal.fire('Coming Soon', 'Export to Excel functionality will be implemented', 'info');
    });

    // Print Report
    $('#printReportBtn').on('click', function() {
        window.print();
    });

    // Load initial data
    applyDateRange();
});
</script>
@endpush
