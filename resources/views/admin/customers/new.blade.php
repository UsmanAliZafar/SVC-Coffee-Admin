@extends('admin.layouts.app')

@section('title', 'New Customers')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">New Customers</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">New Customers</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> All Customers
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- New Today Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #5B914C;">
                                New Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['new_today'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New This Week Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-info">
                                New This Week
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['new_this_week'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New This Month Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-primary">
                                New This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['new_this_month'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last 30 Days Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-warning">
                                Last 30 Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_new'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="newCustomerTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today"
                            type="button" role="tab" aria-controls="today" aria-selected="true">
                        <i class="fas fa-clock me-1"></i> Today
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="week-tab" data-bs-toggle="tab" data-bs-target="#week"
                            type="button" role="tab" aria-controls="week" aria-selected="false">
                        <i class="fas fa-calendar-week me-1"></i> This Week
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="month-tab" data-bs-toggle="tab" data-bs-target="#month"
                            type="button" role="tab" aria-controls="month" aria-selected="false">
                        <i class="fas fa-calendar-alt me-1"></i> This Month
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                            type="button" role="tab" aria-controls="all" aria-selected="false">
                        <i class="fas fa-users me-1"></i> Last 30 Days
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="newCustomerTabsContent">
                <!-- Today Tab -->
                <div class="tab-pane fade show active" id="today" role="tabpanel" aria-labelledby="today-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="todayTable">
                            <thead style="background-color: #5B914C; color: white;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- This Week Tab -->
                <div class="tab-pane fade" id="week" role="tabpanel" aria-labelledby="week-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="weekTable">
                            <thead style="background-color: #5B914C; color: white;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- This Month Tab -->
                <div class="tab-pane fade" id="month" role="tabpanel" aria-labelledby="month-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="monthTable">
                            <thead style="background-color: #5B914C; color: white;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Last 30 Days Tab -->
                <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="allTable">
                            <thead style="background-color: #5B914C; color: white;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .border-left-success {
        border-left: 0.25rem solid #5B914C !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
    }

    .nav-tabs .nav-link:hover {
        border-bottom: 3px solid #5B914C;
        color: #5B914C;
    }

    .nav-tabs .nav-link.active {
        color: #5B914C;
        background-color: transparent;
        border-bottom: 3px solid #5B914C;
        font-weight: 600;
    }

    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.875em;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
    }

    .breadcrumb-item a {
        color: #5B914C;
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        color: #4a7a3d;
        text-decoration: underline;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #5B914C !important;
        border-color: #5B914C !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #4a7a3d !important;
        border-color: #4a7a3d !important;
        color: white !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables for each tab
        var tables = {
            today: null,
            week: null,
            month: null,
            all: null
        };

        // Common DataTable configuration
        function getDataTableConfig(period) {
            return {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.customers.new-data') }}",
                    data: function(d) {
                        d.period = period;
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            return `<div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="fas fa-user-circle fa-2x" style="color: #5B914C;"></i>
                                        </div>
                                        <div>
                                            <strong>${data}</strong>
                                        </div>
                                    </div>`;
                        }
                    },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            var badgeClass = '';
                            var statusText = '';

                            switch(data) {
                                case 'active':
                                    badgeClass = 'bg-success';
                                    statusText = 'Active';
                                    break;
                                case 'inactive':
                                    badgeClass = 'bg-secondary';
                                    statusText = 'Inactive';
                                    break;
                                case 'blocked':
                                    badgeClass = 'bg-danger';
                                    statusText = 'Blocked';
                                    break;
                                default:
                                    badgeClass = 'bg-secondary';
                                    statusText = data;
                            }

                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return moment(data).format('MMM DD, YYYY h:mm A');
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.customers.index') }}/${row.id}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.index') }}/${row.id}/edit"
                                       class="btn btn-sm text-white" style="background-color: #5B914C;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[5, 'desc']], // Sort by created_at descending
                pageLength: 25,
                language: {
                    emptyTable: "No new customers found for this period"
                }
            };
        }

        // Initialize Today table on page load
        tables.today = $('#todayTable').DataTable(getDataTableConfig('today'));

        // Initialize other tables when tabs are shown
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr('data-bs-target');
            var period = target.substring(1); // Remove # from target

            if (period !== 'today' && !tables[period]) {
                tables[period] = $(`#${period}Table`).DataTable(getDataTableConfig(period));
            }
        });

        // Refresh statistics every 60 seconds
        setInterval(function() {
            location.reload();
        }, 60000);
    });
</script>
@endpush
@endsection
