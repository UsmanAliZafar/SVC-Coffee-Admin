@extends('admin.layouts.app')

@section('title', 'Manage Pages')

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
    .page-stats {
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
    .page-details {
        line-height: 1.6;
    }
    .page-title {
        margin-bottom: 3px;
    }
    .page-slug {
        margin-bottom: 3px;
    }
    .page-excerpt {
        font-size: 0.85em;
        margin-top: 3px;
    }
    .page-link:hover {
        color: #5B914C !important;
        text-decoration: underline !important;
    }
    .created-at-container,
    .updated-at-container {
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
            <h2 class="mb-1"><i class="bi bi-file-earmark-text"></i> Pages Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Pages</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('content.create'))
            <a href="{{ route('admin.pages.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Create New Page
            </a>
            @endif
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="page-stats">
        <div class="row">
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalPages">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Pages</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value text-success" id="publishedPages">{{ $stats['published'] }}</div>
                <div class="stat-label">Published</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value text-warning" id="draftPages">{{ $stats['draft'] }}</div>
                <div class="stat-label">Drafts</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="archivedPages">{{ $stats['archived'] }}</div>
                <div class="stat-label">Archived</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control"
                       placeholder="Search by title, slug, content...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Visibility</label>
                <select id="visibilityFilter" class="form-select">
                    <option value="">All</option>
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hierarchy</label>
                <select id="parentFilter" class="form-select">
                    <option value="">All Pages</option>
                    <option value="parent">Parent Only</option>
                    <option value="child">Child Only</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Navigation</label>
                <select id="navigationFilter" class="form-select">
                    <option value="">All</option>
                    <option value="header">In Header</option>
                    <option value="footer">In Footer</option>
                    <option value="none">None</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-2">
                <label class="form-label">Parent Page</label>
                <select id="parentIdFilter" class="form-select">
                    <option value="">All Parents</option>
                    @foreach($parentPages as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Template</label>
                <select id="templateFilter" class="form-select">
                    <option value="">All Templates</option>
                    @foreach($templates as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
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
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Bulk Actions --}}
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span><strong id="selectedCount">0</strong> page(s) selected</span>
                    <div>
                        @if(auth('admin')->user()->hasPermission('content.update'))
                        <button type="button" class="btn btn-sm btn-primary" id="bulkUpdateStatus">
                            <i class="bi bi-arrow-repeat"></i> Update Status
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="bulkPublish">
                            <i class="bi bi-check-circle"></i> Publish
                        </button>
                        @endif
                        @if(auth('admin')->user()->hasPermission('content.delete'))
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
                <table id="pagesTable" class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            @if(auth('admin')->user()->hasPermission('content.delete'))
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            @endif
                            <th>Page Info</th>
                            <th width="150">Parent/Children</th>
                            <th width="100">Status</th>
                            <th width="100">Visibility</th>
                            <th width="120">Navigation</th>
                            <th width="120">Template</th>
                            <th width="150">Menu Label</th>
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

{{-- Bulk Update Status Modal --}}
<div class="modal fade" id="bulkUpdateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Bulk Update Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Status</label>
                    <select class="form-select" id="bulkStatusSelect" required>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <span id="bulkStatusCount">0</span> page(s) will be updated
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="executeBulkStatus">Update Status</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedPages = [];

    // Initialize DataTable
    const table = $('#pagesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.pages.get-data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.visibility = $('#visibilityFilter').val();
                d.parent_filter = $('#parentFilter').val();
                d.parent_id = $('#parentIdFilter').val();
                d.template = $('#templateFilter').val();
                d.navigation = $('#navigationFilter').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d.search = $('#searchFilter').val();
            }
        },
        columns: [
            @if(auth('admin')->user()->hasPermission('content.delete'))
            { data: 'checkbox', orderable: false, searchable: false },
            @endif
            { data: 'page_info', orderable: false },
            { data: 'parent_badge', orderable: false },
            { data: 'status_badge', orderable: false },
            { data: 'visibility_badge', orderable: false },
            { data: 'navigation_badges', orderable: false },
            { data: 'template_badge', orderable: false },
            { data: 'menu_label', orderable: false },
            { data: 'created_at_formatted' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
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
                    <div class="datatable-loading-text">Loading Pages...</div>
                </div>
            `
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Filter change events
    $('#statusFilter, #visibilityFilter, #parentFilter, #parentIdFilter, #templateFilter, #navigationFilter, #dateFrom, #dateTo').on('change', function() {
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
        $('#visibilityFilter').val('');
        $('#parentFilter').val('');
        $('#parentIdFilter').val('');
        $('#templateFilter').val('');
        $('#navigationFilter').val('');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.draw();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.page-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    $(document).on('change', '.page-checkbox', function() {
        updateBulkActions();
        const totalCheckboxes = $('.page-checkbox').length;
        const checkedCheckboxes = $('.page-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        selectedPages = [];
        $('.page-checkbox:checked').each(function() {
            selectedPages.push($(this).val());
        });

        $('#selectedCount').text(selectedPages.length);
        $('#bulkStatusCount').text(selectedPages.length);

        if (selectedPages.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Deselect all
    $('#deselectAll').on('click', function() {
        $('.page-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    });

    // Bulk Update Status
    $('#bulkUpdateStatus').on('click', function() {
        if (selectedPages.length === 0) {
            showNotification('Please select at least one page', 'warning');
            return;
        }
        $('#bulkUpdateStatusModal').modal('show');
    });

    $('#executeBulkStatus').on('click', function() {
        const status = $('#bulkStatusSelect').val();

        if (!status) {
            showNotification('Please select a status', 'warning');
            return;
        }

        Swal.fire({
            title: `Update ${selectedPages.length} page(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, update them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.pages.bulk-status") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        page_ids: selectedPages,
                        status: status
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        $('#bulkUpdateStatusModal').modal('hide');
                        table.draw();
                        $('#deselectAll').click();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Bulk Publish
    $('#bulkPublish').on('click', function() {
        if (selectedPages.length === 0) {
            showNotification('Please select at least one page', 'warning');
            return;
        }

        Swal.fire({
            title: `Publish ${selectedPages.length} page(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, publish them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.pages.bulk-status") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        page_ids: selectedPages,
                        status: 'published'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
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
        if (selectedPages.length === 0) {
            showNotification('Please select at least one page', 'warning');
            return;
        }

        Swal.fire({
            title: `Delete ${selectedPages.length} page(s)?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.pages.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        page_ids: selectedPages
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                        $('#deselectAll').click();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Publish Page
    $(document).on('click', '.publish-page', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');

        Swal.fire({
            title: `Publish "${title}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, publish it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/pages/${id}/publish`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Unpublish Page
    $(document).on('click', '.unpublish-page', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');

        Swal.fire({
            title: `Unpublish "${title}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, set to draft!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/pages/${id}/unpublish`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Archive Page
    $(document).on('click', '.archive-page', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');

        Swal.fire({
            title: `Archive "${title}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/pages/${id}/archive`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Duplicate Page
    $(document).on('click', '.duplicate-page', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');

        Swal.fire({
            title: `Duplicate "${title}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, duplicate it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/pages/${id}/duplicate`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();

                        // Optionally redirect to edit the duplicate
                        if (response.redirect_url) {
                            setTimeout(function() {
                                window.location.href = response.redirect_url;
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        showNotification('An error occurred', 'error');
                    }
                });
            }
        });
    });

    // Delete Page
    $(document).on('click', '.delete-page', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');

        Swal.fire({
            title: `Delete "${title}"?`,
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/pages/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.draw();
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.message || 'An error occurred';
                        showNotification(errorMessage, 'error');
                    }
                });
            }
        });
    });

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
