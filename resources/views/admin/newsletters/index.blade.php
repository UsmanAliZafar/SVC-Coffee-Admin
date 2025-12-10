@extends('admin.layouts.app')

@section('title', 'Manage Newsletter Subscribers')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-card .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #5B914C;
    }
    .btn-filter {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-filter:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }
    .newsletter-stats {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-item .stat-value {
        font-size: 2rem;
        font-weight: bold;
    }
    .stat-item .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .subscriber-details {
        line-height: 1.6;
    }
    .subscriber-email {
        margin-bottom: 3px;
    }
    .subscriber-name {
        font-size: 0.85em;
        margin-top: 3px;
    }
    .subscriber-link:hover {
        color: #5B914C !important;
        text-decoration: underline !important;
    }
    .created-at-container,
    .subscribed-at-container,
    .unsubscribed-at-container {
        line-height: 1.5;
        font-size: 0.9em;
    }
    .form-check-input:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-envelope-at"></i> Newsletter Subscribers</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Newsletter</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('newsletters.create'))
            <a href="{{ route('admin.newsletters.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Add Subscriber
            </a>
            @endif
            @if(auth('admin')->user()->hasPermission('newsletters.read'))
            <a href="{{ route('admin.newsletters.export') }}" class="btn btn-outline-success" id="exportBtn">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
            @endif
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="newsletter-stats">
        <div class="row">
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="totalSubscribers">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Subscribers</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value text-success" id="subscribedCount">{{ $stats['subscribed'] }}</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value text-danger" id="unsubscribedCount">{{ $stats['unsubscribed'] }}</div>
                <div class="stat-label">Unsubscribed</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="todayCount">{{ $stats['today'] }}</div>
                <div class="stat-label">Today</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="weekCount">{{ $stats['this_week'] }}</div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="col-md-2 stat-item">
                <div class="stat-value" id="monthCount">{{ $stats['this_month'] }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control"
                       placeholder="Search by email or name...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="subscribed">Subscribed</option>
                    <option value="unsubscribed">Unsubscribed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" id="dateFrom" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" id="dateTo" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Bulk Actions --}}
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span><strong id="selectedCount">0</strong> subscriber(s) selected</span>
                    <div>
                        @if(auth('admin')->user()->hasPermission('newsletters.update'))
                        <button type="button" class="btn btn-sm btn-success" id="bulkSubscribe">
                            <i class="bi bi-check-circle"></i> Subscribe
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="bulkUnsubscribe">
                            <i class="bi bi-x-circle"></i> Unsubscribe
                        </button>
                        @endif
                        @if(auth('admin')->user()->hasPermission('newsletters.read'))
                        <button type="button" class="btn btn-sm btn-primary" id="bulkExport">
                            <i class="bi bi-download"></i> Export Selected
                        </button>
                        @endif
                        @if(auth('admin')->user()->hasPermission('newsletters.delete'))
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDelete">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">
                            <i class="bi bi-x"></i> Deselect All
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table id="newslettersTable" class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            @if(auth('admin')->user()->hasPermission('newsletters.delete'))
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            @endif
                            <th>Subscriber Info</th>
                            <th width="120">Status</th>
                            <th width="150">Subscribed At</th>
                            <th width="150">Unsubscribed At</th>
                            <th width="150">Created At</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedNewsletters = [];

    // Initialize DataTable
    const table = $('#newslettersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.newsletters.data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d.search = $('#searchFilter').val();
            }
        },
        columns: [
            @if(auth('admin')->user()->hasPermission('newsletters.delete'))
            { data: 'checkbox', orderable: false, searchable: false },
            @endif
            { data: 'subscriber_info', orderable: false },
            { data: 'status_badge', orderable: false },
            { data: 'subscribed_at_formatted', orderable: false },
            { data: 'unsubscribed_at_formatted', orderable: false },
            { data: 'created_at_formatted' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']], // Sort by created_at by default
        pageLength: 25,
        responsive: true,
        language: {
            processing: `
                <div class="datatable-loading-container">
                    <div class="bars-loader">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="datatable-loading-text">Loading Subscribers...</div>
                </div>
            `
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Filter change events
    $('#statusFilter, #dateFrom, #dateTo').on('change', function() {
        table.draw();
    });

    // Search with delay
    let searchDelay;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#searchFilter').val('');
        $('#statusFilter').val('');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.draw();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.newsletter-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    $(document).on('change', '.newsletter-checkbox', function() {
        updateBulkActions();
        const totalCheckboxes = $('.newsletter-checkbox').length;
        const checkedCheckboxes = $('.newsletter-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        selectedNewsletters = [];
        $('.newsletter-checkbox:checked').each(function() {
            selectedNewsletters.push($(this).val());
        });

        $('#selectedCount').text(selectedNewsletters.length);

        if (selectedNewsletters.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Deselect all
    $('#deselectAll').on('click', function() {
        $('.newsletter-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    });

    // Bulk Subscribe
    $('#bulkSubscribe').on('click', function() {
        if (selectedNewsletters.length === 0) {
            showNotification('Please select at least one subscriber', 'warning');
            return;
        }

        Swal.fire({
            title: `Subscribe ${selectedNewsletters.length} subscriber(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, subscribe them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.newsletters.bulk-status") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        newsletter_ids: selectedNewsletters,
                        is_subscribed: true
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
                        refreshStats();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Bulk Unsubscribe
    $('#bulkUnsubscribe').on('click', function() {
        if (selectedNewsletters.length === 0) {
            showNotification('Please select at least one subscriber', 'warning');
            return;
        }

        Swal.fire({
            title: `Unsubscribe ${selectedNewsletters.length} subscriber(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unsubscribe them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.newsletters.bulk-status") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        newsletter_ids: selectedNewsletters,
                        is_subscribed: false
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
                        refreshStats();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Bulk Delete
    $('#bulkDelete').on('click', function() {
        if (selectedNewsletters.length === 0) {
            showNotification('Please select at least one subscriber', 'warning');
            return;
        }

        Swal.fire({
            title: `Delete ${selectedNewsletters.length} subscriber(s)?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.newsletters.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        newsletter_ids: selectedNewsletters
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
                        refreshStats();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Subscribe Newsletter
    $(document).on('click', '.subscribe-newsletter', function() {
        const id = $(this).data('id');
        const email = $(this).data('email');

        Swal.fire({
            title: `Subscribe "${email}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, subscribe!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/newsletters/${id}/subscribe`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        refreshStats();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Unsubscribe Newsletter
    $(document).on('click', '.unsubscribe-newsletter', function() {
        const id = $(this).data('id');
        const email = $(this).data('email');

        Swal.fire({
            title: `Unsubscribe "${email}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unsubscribe!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/newsletters/${id}/unsubscribe`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        refreshStats();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Delete Newsletter
    $(document).on('click', '.delete-newsletter', function() {
        const id = $(this).data('id');
        const email = $(this).data('email');

        Swal.fire({
            title: `Delete "${email}"?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/newsletters/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        refreshStats();
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.message || 'An error occurred';
                        showNotification(errorMessage, 'error');
                    }
                });
            }
        });
    });

    // Export functionality
    $('#exportBtn').on('click', function(e) {
        e.preventDefault();

        let url = '{{ route("admin.newsletters.export") }}';
        const status = $('#statusFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.location.href = url;
        showNotification('Exporting subscribers...', 'info');
    });

    // Refresh statistics
    function refreshStats() {
        $.ajax({
            url: '{{ route("admin.newsletters.statistics") }}',
            method: 'GET',
            success: function(stats) {
                $('#totalSubscribers').text(stats.total);
                $('#subscribedCount').text(stats.subscribed);
                $('#unsubscribedCount').text(stats.unsubscribed);
                $('#todayCount').text(stats.today);
                $('#weekCount').text(stats.this_week);
                $('#monthCount').text(stats.this_month);
            }
        });
    }

    // Notification Helper
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3"
                 role="alert" style="z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        $('body').append(notification);

        setTimeout(function() {
            notification.alert('close');
        }, 5000);
    }
});
</script>
@endpush
