@extends('admin.layouts.app')

@section('title', 'Blocked Customers')

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
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
    .danger-banner {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 35, 51, 0.1) 100%);
        border-left: 4px solid #dc3545;
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
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
    }
    .filter-chip.active {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
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
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        flex-shrink: 0;
        position: relative;
    }
    .avatar-circle::after {
        content: '🚫';
        position: absolute;
        bottom: -2px;
        right: -2px;
        font-size: 0.6rem;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(220, 53, 69, 0.05);
    }
    .blocked-overlay {
        position: relative;
    }
    .blocked-overlay::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(220, 53, 69, 0.02) 10px,
            rgba(220, 53, 69, 0.02) 20px
        );
        pointer-events: none;
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #dc3545;
        display: inline-block;
        margin-right: 5px;
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        50% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
    }
    .action-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s;
        cursor: pointer;
        text-align: center;
        background: white;
    }
    .action-card:hover {
        border-color: #dc3545;
        background: rgba(220, 53, 69, 0.05);
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
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
    .risk-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .security-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="bi bi-lock-fill text-danger"></i> Blocked Customers
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
                    <li class="breadcrumb-item active">Blocked Customers</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning text-white" id="bulkUnblockBtn">
                <i class="bi bi-unlock-fill"></i> Bulk Unblock
            </button>
            <button class="btn btn-outline-danger" id="exportBtn">
                <i class="bi bi-file-earmark-excel-fill"></i> Export
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> All Customers
            </a>
        </div>
    </div>

    {{-- Danger Banner --}}
    <div class="danger-banner">
        <div class="d-flex align-items-center">
            <i class="bi bi-shield-x text-danger fs-3 me-3"></i>
            <div>
                <h6 class="mb-1 fw-bold text-danger">Security Alert - Access Restricted</h6>
                <p class="mb-0 text-muted">
                    These customers have <strong>CUSTOMER_BLOCKED</strong> status and are <strong>completely blocked from accessing their accounts</strong>. This status is typically used for security concerns, fraud prevention, or policy violations.
                </p>
            </div>
        </div>
    </div>

    {{-- Warning Banner --}}
    <div class="warning-banner">
        <div class="d-flex align-items-center">
            <span class="status-indicator"></span>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold">Before Unblocking</h6>
                <p class="mb-0 text-muted">
                    Review the reason for blocking before restoring access. Consider contacting the customer first to resolve any outstanding issues. Document all unblock actions for security audit trails.
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
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Blocked Customers</div>
                            <div class="stat-value">{{ number_format($stats['total_blocked']) }}</div>
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
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stat-label">Security Level</div>
                            <div class="stat-value" style="font-size: 1.5rem;">
                                @if($stats['total_blocked'] == 0)
                                    <span class="text-success">Safe</span>
                                @elseif($stats['total_blocked'] < 10)
                                    <span class="text-warning">Low</span>
                                @else
                                    <span class="text-danger">High</span>
                                @endif
                            </div>
                            <small class="text-muted">Risk assessment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-info fs-1 mb-2"></i>
                    <div class="stat-value" style="font-size: 1.5rem;" id="recentBlocksCount">-</div>
                    <div class="stat-label">Recent Blocks</div>
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-shield-check text-success fs-1 mb-2"></i>
                    <div class="stat-value" style="font-size: 1.5rem;" id="reviewedCount">-</div>
                    <div class="stat-label">Reviewed Cases</div>
                    <small class="text-muted">Ready for action</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Row --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="action-card" id="unblockSelected">
                <i class="bi bi-unlock-fill text-warning"></i>
                <h6 class="fw-bold mb-2">Unblock Selected</h6>
                <p class="text-muted small mb-0">Restore account access</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="action-card" id="reviewCases">
                <i class="bi bi-clipboard-check text-primary"></i>
                <h6 class="fw-bold mb-2">Review Cases</h6>
                <p class="text-muted small mb-0">Check blocking reasons</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="action-card" id="contactCustomers">
                <i class="bi bi-envelope-fill text-info"></i>
                <h6 class="fw-bold mb-2">Contact Customers</h6>
                <p class="text-muted small mb-0">Send notification</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="action-card" id="securityReport">
                <i class="bi bi-file-earmark-lock text-danger"></i>
                <h6 class="fw-bold mb-2">Security Report</h6>
                <p class="text-muted small mb-0">View analytics</p>
            </div>
        </div>
    </div>

    {{-- Security Insights --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <div class="security-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="text-muted mb-1">Fraud Suspects</h6>
                <h4 class="mb-0 fw-bold text-danger" id="fraudCount">-</h4>
                <small class="text-muted">Potential fraud</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <div class="security-icon">
                    <i class="bi bi-file-earmark-x"></i>
                </div>
                <h6 class="text-muted mb-1">Policy Violations</h6>
                <h4 class="mb-0 fw-bold text-warning" id="policyCount">-</h4>
                <small class="text-muted">Terms breaches</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <div class="security-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h6 class="text-muted mb-1">Pending Orders</h6>
                <h4 class="mb-0 fw-bold text-info" id="pendingOrdersCount">-</h4>
                <small class="text-muted">Blocked accounts</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="metric-box h-100">
                <div class="security-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h6 class="text-muted mb-1">Avg Block Time</h6>
                <h4 class="mb-0 fw-bold" id="avgBlockTime">-</h4>
                <small class="text-muted">Days blocked</small>
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
                <span class="filter-chip blocked-filter active" data-filter="all">
                    <i class="bi bi-lock"></i> All Blocked
                </span>
                <span class="filter-chip blocked-filter" data-filter="recent">
                    <i class="bi bi-clock-history"></i> Recently Blocked
                </span>
                <span class="filter-chip blocked-filter" data-filter="with_orders">
                    <i class="bi bi-cart-check"></i> With Orders
                </span>
                <span class="filter-chip blocked-filter" data-filter="high_value">
                    <i class="bi bi-trophy"></i> High Value
                </span>
                <span class="filter-chip blocked-filter" data-filter="long_term">
                    <i class="bi bi-calendar-x"></i> Long-term Block
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
                    <i class="bi bi-cart"></i> Order History
                </label>
                <select class="form-select form-select-sm" id="filterOrderHistory">
                    <option value="">Any Orders</option>
                    <option value="none">No Orders</option>
                    <option value="1-5">1-5 Orders</option>
                    <option value="6+">6+ Orders</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">
                    <i class="bi bi-calendar-x"></i> Blocked Since
                </label>
                <select class="form-select form-select-sm" id="filterBlockedSince">
                    <option value="">Any Time</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="180">6+ months</option>
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
    <div class="card border-0 shadow-sm blocked-overlay">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-shield-lock text-danger"></i> Blocked Customers List
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-danger badge-lg" id="customerCount">
                        Loading...
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <strong>Security Notice:</strong> All actions on blocked accounts are logged for security auditing. Ensure proper authorization before unblocking.
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="blockedCustomersTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th><i class="bi bi-person"></i> Customer</th>
                            <th class="text-center"><i class="bi bi-tag"></i> Type</th>
                            <th class="text-center"><i class="bi bi-cart"></i> Orders</th>
                            <th class="text-end"><i class="bi bi-currency-dollar"></i> Total Spent</th>
                            <th><i class="bi bi-diagram-3"></i> Segment</th>
                            <th><i class="bi bi-calendar-x"></i> Blocked Since</th>
                            <th><i class="bi bi-clock"></i> Duration</th>
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

{{-- Unblock Confirmation Modal --}}
<div class="modal fade" id="unblockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-unlock-fill"></i> Unblock Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    This will restore full account access for the selected customer(s).
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Unblocking</label>
                    <textarea class="form-control" id="unblockReason" rows="3" placeholder="Document why this account is being unblocked..."></textarea>
                    <small class="text-muted">This will be logged for security audit</small>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="notifyCustomer">
                    <label class="form-check-label" for="notifyCustomer">
                        Send notification email to customer
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning text-white" id="confirmUnblock">
                    <i class="bi bi-unlock-fill"></i> Confirm Unblock
                </button>
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
        if ($.fn.DataTable.isDataTable('#blockedCustomersTable')) {
            $('#blockedCustomersTable').DataTable().destroy();
        }

        table = $('#blockedCustomersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.customers.data") }}',
                data: function(d) {
                    d.status = 'CUSTOMER_BLOCKED';
                    d.customer_type = $('#filterCustomerType').val();
                    d.order_history = $('#filterOrderHistory').val();
                    d.blocked_days = $('#filterBlockedSince').val();
                    d.search_query = $('#searchInput').val();

                    // Quick filters
                    const activeFilter = $('.blocked-filter.active').data('filter');
                    if (activeFilter === 'recent') {
                        d.blocked_days = 30;
                    } else if (activeFilter === 'with_orders') {
                        d.has_orders = 'true';
                    } else if (activeFilter === 'high_value') {
                        d.high_value = 'true';
                    } else if (activeFilter === 'long_term') {
                        d.blocked_days_min = 90;
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
                            return '<span class="badge bg-secondary">0</span>';
                        }
                        return `<span class="badge bg-danger">${data}</span>`;
                    }
                },
                {
                    data: 'total_spent',
                    name: 'total_spent',
                    className: 'text-end',
                    render: function(data) {
                        return `<strong class="text-danger">{{ store_currency_symbol() }}${parseFloat(data).toFixed(2)}</strong>`;
                    }
                },
                { data: 'segment', orderable: false, searchable: false, className: 'text-center' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: 'updated_at',
                    name: 'blocked_duration',
                    render: function(data) {
                        const blockedDate = new Date(data);
                        const now = new Date();
                        const days = Math.floor((now - blockedDate) / (1000 * 60 * 60 * 24));

                        let badgeClass = 'bg-warning';
                        if (days > 90) badgeClass = 'bg-danger';
                        else if (days > 30) badgeClass = 'bg-warning';
                        else badgeClass = 'bg-info';

                        return `<span class="badge ${badgeClass}">${days} days</span>`;
                    }
                },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[6, 'desc']], // Order by blocked date (newest first)
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No blocked customers found",
                zeroRecords: "No matching blocked customers found",
                info: "Showing _START_ to _END_ of _TOTAL_ blocked customers"
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
                status: 'CUSTOMER_BLOCKED',
                get_security_insights: true
            },
            success: function(response) {
                if (response.insights) {
                    $('#recentBlocksCount').text(response.insights.recent_blocks || '0');
                    $('#reviewedCount').text(response.insights.reviewed || '0');
                    $('#fraudCount').text(response.insights.fraud_suspects || '0');
                    $('#policyCount').text(response.insights.policy_violations || '0');
                    $('#pendingOrdersCount').text(response.insights.pending_orders || '0');
                    $('#avgBlockTime').text((response.insights.avg_block_days || '0') + ' days');
                }
            }
        });
    }

    // Quick filters
    $('.blocked-filter').on('click', function() {
        $('.blocked-filter').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    // Advanced filters
    $('#filterCustomerType, #filterOrderHistory, #filterBlockedSince').on('change', function() {
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
        $('.blocked-filter').removeClass('active');
        $('.blocked-filter:first').addClass('active');
        $('#filterCustomerType, #filterOrderHistory, #filterBlockedSince, #searchInput').val('');
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

    // Bulk unblock
    $('#bulkUnblockBtn, #unblockSelected').on('click', function() {
        if (selectedCustomers.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select customers to unblock',
                confirmButtonColor: '#5B914C'
            });
            return;
        }

        $('#unblockModal').modal('show');
    });

    // Confirm unblock
    $('#confirmUnblock').on('click', function() {
        const reason = $('#unblockReason').val();
        const notify = $('#notifyCustomer').prop('checked');

        if (!reason.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Reason Required',
                text: 'Please provide a reason for unblocking',
                confirmButtonColor: '#5B914C'
            });
            return;
        }

        $('#unblockModal').modal('hide');

        $.ajax({
            url: '{{ route("admin.customers.bulk-action") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: 'activate',
                customer_ids: selectedCustomers,
                reason: reason,
                notify: notify
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Unblocked!',
                        text: response.message,
                        confirmButtonColor: '#5B914C'
                    });
                    selectedCustomers = [];
                    $('#selectAll').prop('checked', false);
                    $('#unblockReason').val('');
                    $('#notifyCustomer').prop('checked', false);
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to unblock customers',
                    confirmButtonColor: '#5B914C'
                });
            }
        });
    });

    // Action cards
    $('#reviewCases').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Case Review System',
            text: 'Detailed case management system coming soon. Review blocking reasons, notes, and history.',
            confirmButtonColor: '#5B914C'
        });
    });

    $('#contactCustomers').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Customer Communication',
            text: 'Email notification system coming soon. Contact blocked customers about their account status.',
            confirmButtonColor: '#5B914C'
        });
    });

    $('#securityReport').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Security Analytics',
            text: 'Comprehensive security dashboard coming soon. View trends, patterns, and risk analysis.',
            confirmButtonColor: '#5B914C'
        });
    });

    // Export
    $('#exportBtn').on('click', function() {
        const params = {
            status: 'CUSTOMER_BLOCKED',
            customer_type: $('#filterCustomerType').val(),
            order_history: $('#filterOrderHistory').val(),
            blocked_days: $('#filterBlockedSince').val()
        };

        window.location.href = '{{ route("admin.customers.export") }}?' + $.param(params);

        Swal.fire({
            icon: 'info',
            title: 'Exporting...',
            text: 'Security report will download shortly',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Initial insights load
    updateInsights();
});
</script>
@endpush
