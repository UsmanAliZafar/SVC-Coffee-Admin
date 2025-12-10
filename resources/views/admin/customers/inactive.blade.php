@extends('admin.layouts.app')

@section('title', 'Inactive Customers')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .page-header {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
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
    .info-banner {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.1) 0%, rgba(90, 98, 104, 0.1) 100%);
        border-left: 4px solid #6c757d;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .warning-banner {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 171, 0, 0.1) 100%);
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 2px solid #e9ecef;
    }
    .filter-chip {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.875rem;
        margin-right: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
        background: #fff;
    }
    .filter-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.2);
    }
    .filter-chip.active {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        flex-shrink: 0;
        opacity: 0.7;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(108, 117, 125, 0.05);
    }
    .reactivation-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #6c757d;
        display: inline-block;
        margin-right: 5px;
    }
    .action-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s;
        cursor: pointer;
        text-align: center;
    }
    .action-card:hover {
        border-color: #5B914C;
        background: rgba(91, 145, 76, 0.05);
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .action-card i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    .metric-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    .inactive-overlay {
        position: relative;
    }
    .inactive-overlay::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(108, 117, 125, 0.05);
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-pause-circle-fill text-secondary"></i> Inactive Customers
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
                    <li class="breadcrumb-item active">Inactive Customers</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" id="bulkActivateBtn">
                <i class="bi bi-check-circle-fill"></i> Bulk Activate
            </button>
            <button class="btn btn-outline-success" id="exportBtn">
                <i class="bi bi-file-earmark-excel-fill"></i> Export
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Warning Banner --}}
    <div class="warning-banner">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-3 me-3"></i>
            <div>
                <h6 class="mb-1 fw-bold">Attention Required</h6>
                <p class="mb-0 text-muted">
                    These customers have <strong>CUSTOMER_INACTIVE</strong> status and <strong>cannot access their accounts</strong>. Consider reactivating valuable customers or running a win-back campaign.
                </p>
            </div>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="info-banner">
        <div class="d-flex align-items-center">
            <span class="status-indicator"></span>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold">About Inactive Customers</h6>
                <p class="mb-0 text-muted">
                    Inactive status is typically set when customers request to pause their account, haven't engaged for a long time, or as an administrative action. They can be reactivated at any time.
                </p>
            </div>
        </div>
    </div>

    {{-- Statistics Dashboard --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Inactive Customers</div>
                            <div class="stat-value">{{ number_format($stats['total_inactive']) }}</div>
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
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Inactive Rate</div>
                            <div class="stat-value">
                                @php
                                    $totalCustomers = \App\Models\Customer::count();
                                    $inactiveRate = $totalCustomers > 0 ? ($stats['total_inactive'] / $totalCustomers * 100) : 0;
                                @endphp
                                {{ number_format($inactiveRate, 1) }}%
                            </div>
                            <small class="text-muted">Of all customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    {{ store_currency_symbol() }}
                    <div class="stat-value" style="font-size: 1.5rem;" id="potentialRevenue">-</div>
                    <div class="stat-label">Potential Revenue</div>
                    <small class="text-muted">If reactivated</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-counterclockwise text-info fs-1 mb-2"></i>
                    <div class="stat-value" style="font-size: 1.5rem;" id="reactivationCandidates">-</div>
                    <div class="stat-label">Reactivation Targets</div>
                    <small class="text-muted">High value inactive</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Reactivation Actions --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="action-card" id="sendWinbackEmail">
                <i class="bi bi-envelope-heart text-primary"></i>
                <h6 class="fw-bold mb-2">Win-back Email</h6>
                <p class="text-muted small mb-0">Send re-engagement campaign</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="action-card" id="offerDiscount">
                <i class="bi bi-tag-fill text-success"></i>
                <h6 class="fw-bold mb-2">Special Offer</h6>
                <p class="text-muted small mb-0">Create discount code</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="action-card" id="bulkActivateAction">
                <i class="bi bi-check-circle-fill text-warning"></i>
                <h6 class="fw-bold mb-2">Bulk Activate</h6>
                <p class="text-muted small mb-0">Reactivate selected</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="action-card" id="analyzeInactive">
                <i class="bi bi-graph-up-arrow text-info"></i>
                <h6 class="fw-bold mb-2">Analyze Trends</h6>
                <p class="text-muted small mb-0">View insights</p>
            </div>
        </div>
    </div>

    {{-- Insights Row --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-cart-x text-danger fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">Never Ordered</h6>
                <h4 class="mb-0 fw-bold" id="neverOrderedCount">-</h4>
                <small class="text-muted">No purchase history</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-star text-warning fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">Previous Buyers</h6>
                <h4 class="mb-0 fw-bold" id="previousBuyersCount">-</h4>
                <small class="text-muted">Had orders before</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-trophy text-success fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">High Value</h6>
                <h4 class="mb-0 fw-bold" id="highValueInactiveCount">-</h4>
                <small class="text-muted">{{ store_currency_symbol() }}1000+ spent</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <i class="bi bi-clock-history text-info fs-2 mb-2 d-block"></i>
                <h6 class="text-muted mb-1">Recent Inactive</h6>
                <h4 class="mb-0 fw-bold" id="recentInactiveCount">-</h4>
                <small class="text-muted">Last 30 days</small>
            </div>
        </div>
    </div>

    {{-- Quick Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <span class="text-muted me-3 fw-semibold">
                    <i class="bi bi-funnel"></i> Quick Filters:
                </span>
                <span class="filter-chip inactive-filter active" data-filter="all">
                    <i class="bi bi-people"></i> All Inactive
                </span>
                <span class="filter-chip inactive-filter" data-filter="with_orders">
                    <i class="bi bi-cart-check"></i> Previous Buyers
                </span>
                <span class="filter-chip inactive-filter" data-filter="never_ordered">
                    <i class="bi bi-cart-x"></i> Never Ordered
                </span>
                <span class="filter-chip inactive-filter" data-filter="high_value">
                    <i class="bi bi-trophy"></i> High Value
                </span>
                <span class="filter-chip inactive-filter" data-filter="recent">
                    <i class="bi bi-clock-history"></i> Recently Inactive
                </span>
                <div class="ms-auto">
                    <button class="btn btn-sm btn-outline-danger" id="clearFiltersBtn">
                        <i class="bi bi-x-circle-fill"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Filters --}}
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-tag"></i> Customer Type
                </label>
                <select class="form-select form-select-sm" id="filterCustomerType">
                    <option value="">All Types</option>
                    <option value="individual">Individual</option>
                    <option value="business">Business</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="vip">VIP</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-currency-dollar"></i> Previous Spending
                </label>
                <select class="form-select form-select-sm" id="filterSpending">
                    <option value="">Any Amount</option>
                    <option value="0">No Spending</option>
                    <option value="1-500">{{ store_currency_symbol() }}1 - {{ store_currency_symbol() }}500</option>
                    <option value="501-1000">{{ store_currency_symbol() }}501 - {{ store_currency_symbol() }}1,000</option>
                    <option value="1001+">{{ store_currency_symbol() }}1,001+</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-calendar-x"></i> Inactive Since
                </label>
                <select class="form-select form-select-sm" id="filterInactiveSince">
                    <option value="">Any Time</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="180">Last 6 months</option>
                    <option value="365">Last year</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-search"></i> Search
                </label>
                <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Name, email, phone...">
            </div>
        </div>
    </div>

    {{-- Main Data Table --}}
    <div class="card border-0 shadow-sm inactive-overlay">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-table text-secondary"></i> Inactive Customers List
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-secondary badge-lg" id="customerCount">
                        Loading...
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="inactiveCustomersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th><i class="bi bi-person"></i> Customer</th>
                            <th class="text-center"><i class="bi bi-tag"></i> Type</th>
                            <th class="text-center"><i class="bi bi-cart"></i> Orders</th>
                            <th class="text-end"><i class="bi bi-currency-dollar"></i> Total Spent</th>
                            <th><i class="bi bi-trophy"></i> Segment</th>
                            <th><i class="bi bi-clock-history"></i> Last Order</th>
                            <th><i class="bi bi-calendar-x"></i> Inactive Since</th>
                            <th class="text-center" width="150"><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data loaded via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let table;
    let selectedCustomers = [];

    // Initialize DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#inactiveCustomersTable')) {
            $('#inactiveCustomersTable').DataTable().destroy();
        }

        table = $('#inactiveCustomersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.customers.data") }}',
                data: function(d) {
                    d.status = 'CUSTOMER_INACTIVE';
                    d.customer_type = $('#filterCustomerType').val();
                    d.spending_range = $('#filterSpending').val();
                    d.inactive_days = $('#filterInactiveSince').val();
                    d.search_query = $('#searchInput').val();

                    // Quick filters
                    const activeFilter = $('.inactive-filter.active').data('filter');
                    if (activeFilter === 'with_orders') {
                        d.has_orders = 'true';
                    } else if (activeFilter === 'never_ordered') {
                        d.no_orders = 'true';
                    } else if (activeFilter === 'high_value') {
                        d.high_value = 'true';
                    } else if (activeFilter === 'recent') {
                        d.inactive_days = 30;
                    }
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                { data: 'customer_info', name: 'first_name' },
                { data: 'customer_type', name: 'customer_type', className: 'text-center' },
                {
                    data: 'total_orders',
                    name: 'total_orders',
                    className: 'text-center',
                    render: function(data) {
                        if (data === 0) {
                            return '<span class="badge bg-danger">Never ordered</span>';
                        }
                        return `<span class="badge bg-secondary">${data}</span>`;
                    }
                },
                {
                    data: 'total_spent',
                    name: 'total_spent',
                    className: 'text-end',
                    render: function(data) {
                        return `<strong class="text-muted">{{ store_currency_symbol() }}${parseFloat(data).toFixed(2)}</strong>`;
                    }
                },
                { data: 'segment', orderable: false, searchable: false, className: 'text-center' },
                {
                    data: 'last_order',
                    name: 'last_order_at',
                    render: function(data) {
                        if (data && data !== '<span class="text-muted">Never</span>') {
                            return data;
                        }
                        return '<span class="text-muted">Never ordered</span>';
                    }
                },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[7, 'desc']], // Order by inactive since (newest first)
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No inactive customers found",
                zeroRecords: "No matching inactive customers found",
                info: "Showing _START_ to _END_ of _TOTAL_ inactive customers"
            },
            drawCallback: function(settings) {
                const info = table.page.info();
                $('#customerCount').text(info.recordsDisplay + ' Customers');

                // Update insights
                updateInsights();

                // Update checkboxes
                $('.customer-checkbox').each(function() {
                    if (selectedCustomers.includes($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
            }
        });
    }

    initDataTable();

    // Update insights
    function updateInsights() {
        $.ajax({
            url: '{{ route("admin.customers.data") }}',
            data: {
                status: 'CUSTOMER_INACTIVE',
                get_insights: true
            },
            success: function(response) {
                if (response.insights) {
                    $('#neverOrderedCount').text(response.insights.never_ordered || '-');
                    $('#previousBuyersCount').text(response.insights.previous_buyers || '-');
                    $('#highValueInactiveCount').text(response.insights.high_value || '-');
                    $('#recentInactiveCount').text(response.insights.recent_inactive || '-');
                    $('#potentialRevenue').text('{{ store_currency_symbol() }}' + (response.insights.potential_revenue || '0.00'));
                    $('#reactivationCandidates').text(response.insights.reactivation_candidates || '-');
                }
            }
        });
    }

    // Quick filters
    $('.inactive-filter').on('click', function() {
        $('.inactive-filter').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    // Advanced filters
    $('#filterCustomerType, #filterSpending, #filterInactiveSince').on('change', function() {
        table.ajax.reload();
    });

    // Search with debounce
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.ajax.reload();
        }, 500);
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('.inactive-filter').removeClass('active');
        $('.inactive-filter:first').addClass('active');
        $('#filterCustomerType, #filterSpending, #filterInactiveSince, #searchInput').val('');
        table.ajax.reload();
    });

    // Select all
    $('#selectAll').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.customer-checkbox:visible').each(function() {
            $(this).prop('checked', isChecked);
            const customerId = $(this).val();
            if (isChecked) {
                if (!selectedCustomers.includes(customerId)) {
                    selectedCustomers.push(customerId);
                }
            } else {
                selectedCustomers = selectedCustomers.filter(id => id !== customerId);
            }
        });
    });

    // Individual checkbox
    $(document).on('change', '.customer-checkbox', function() {
        const customerId = $(this).val();
        if ($(this).prop('checked')) {
            if (!selectedCustomers.includes(customerId)) {
                selectedCustomers.push(customerId);
            }
        } else {
            selectedCustomers = selectedCustomers.filter(id => id !== customerId);
            $('#selectAll').prop('checked', false);
        }
    });

    // Bulk activate
    $('#bulkActivateBtn, #bulkActivateAction').on('click', function() {
        if (selectedCustomers.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select customers to activate',
                confirmButtonColor: '#5B914C'
            });
            return;
        }

        Swal.fire({
            title: 'Activate Selected Customers?',
            html: `This will reactivate <strong>${selectedCustomers.length}</strong> selected customer(s) and restore their account access.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle"></i> Yes, activate them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkActivate();
            }
        });
    });

    function performBulkActivate() {
        $.ajax({
            url: '{{ route("admin.customers.bulk-action") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: 'activate',
                customer_ids: selectedCustomers
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Activated!',
                        text: response.message,
                        confirmButtonColor: '#5B914C'
                    });
                    selectedCustomers = [];
                    $('#selectAll').prop('checked', false);
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to activate customers',
                    confirmButtonColor: '#5B914C'
                });
            }
        });
    }

    // Action cards
    $('#sendWinbackEmail').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Win-back Campaign',
            text: 'Email campaign feature coming soon. This will send personalized re-engagement emails to inactive customers.',
            confirmButtonColor: '#5B914C'
        });
    });

    $('#offerDiscount').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Special Offer',
            text: 'Discount code generator coming soon. Create exclusive offers for inactive customers.',
            confirmButtonColor: '#5B914C'
        });
    });

    $('#analyzeInactive').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Analytics Dashboard',
            text: 'Detailed analytics coming soon. View trends, patterns, and insights about inactive customers.',
            confirmButtonColor: '#5B914C'
        });
    });

    // Export
    $('#exportBtn').on('click', function() {
        const params = {
            status: 'CUSTOMER_INACTIVE',
            customer_type: $('#filterCustomerType').val(),
            spending_range: $('#filterSpending').val(),
            inactive_days: $('#filterInactiveSince').val()
        };

        window.location.href = '{{ route("admin.customers.export") }}?' + $.param(params);

        Swal.fire({
            icon: 'info',
            title: 'Exporting...',
            text: 'Your CSV file will download shortly',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Initial insights load
    updateInsights();
});
</script>
@endpush
