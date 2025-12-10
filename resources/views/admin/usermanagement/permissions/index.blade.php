@extends('admin.layouts.app')

@section('title', 'Permissions Management')

@section('page_title', 'System Permissions')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item active">Permissions</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Warning Alert for Super Admin Only -->
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <strong>Super Administrator Access Only</strong> -
                    Permission management is restricted to super administrators as it affects system security and functionality.
                </div>
            </div>

             <!-- Quick Stats Cards -->
            <div class="row mt-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0" id="totalPermissions">0</h3>
                                    <p class="mb-0">Total Permissions</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-key" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0" id="totalModules">0</h3>
                                    <p class="mb-0">Modules</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-folder" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0" id="totalActions">0</h3>
                                    <p class="mb-0">Actions</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-lightning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0" id="totalRoleAssignments">0</h3>
                                    <p class="mb-0">Role Assignments</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-key me-2"></i>System Permissions Management
                    </h5>
                    <div>
                        <a href="{{ route('admin.permissions.create') }}" class="btn btn-light btn-sm me-2">
                            <i class="bi bi-plus-circle me-1"></i>Add Permission
                        </a>
                        <a href="{{ route('admin.permissions.bulk-create') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-plus-square me-1"></i>Bulk Create
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Module</label>
                            <select class="form-select" id="moduleFilter">
                                <option value="">All Modules</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module }}">
                                        {{ ucwords(str_replace('_', ' ', $module)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Filter by Action</label>
                            <select class="form-select" id="actionFilter">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}">
                                        {{ ucwords(str_replace('_', ' ', $action)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Actions</label>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-success" id="refreshTable">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="permissionsTable" width="100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Permission Details</th>
                                    <th width="15%">Module</th>
                                    <th width="15%">Action</th>
                                    <th width="10%">Roles Count</th>
                                    <th width="15%">Created</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTable will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the permission <strong id="permissionName"></strong>?</p>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone and may affect system functionality.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Permission</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .badge.fs-6 {
        font-size: 0.875rem !important;
    }

    .btn-outline-success:hover {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .card.bg-primary {
        background-color: #5B914C !important;
    }

    .alert-warning {
        border-color: #ffc107;
        background-color: #fff3cd;
    }

    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
    }

    /* DataTable custom styles */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 0.75rem;
    }

    .dt-buttons .btn {
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
    }
</style>
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    // Initialize CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable
    const table = $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.permissions.data') }}",
            type: 'GET',
            data: function(d) {
                d.module_filter = $('#moduleFilter').val();
                d.action_filter = $('#actionFilter').val();
            },
            error: function(xhr, error, thrown) {
                console.log('Ajax error:', error);
                showAlert('danger', 'Error loading data: ' + thrown);
            }
        },
        columns: [
            {
                data: 'index',
                name: 'index',
                orderable: false,
                searchable: false,
                width: '5%'
            },
            {
                data: 'display_name',
                name: 'display_name',
                render: function(data, type, row) {
                    let html = `
                        <div>
                            <h6 class="mb-1">${row.display_name}</h6>
                            <small class="text-muted">
                                <i class="bi bi-code-slash me-1"></i>${row.name}
                            </small>
                    `;
                    if (row.description) {
                        html += `<br><small class="text-muted">${row.description}</small>`;
                    }
                    html += `</div>`;
                    return html;
                },
                width: '25%'
            },
            {
                data: 'module',
                name: 'module',
                render: function(data, type, row) {
                    return `<span class="badge bg-primary">${data.replace(/_/g, ' ').replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase())}</span>`;
                },
                width: '15%'
            },
            {
                data: 'action',
                name: 'action',
                render: function(data, type, row) {
                    return `<span class="badge bg-secondary">${data.replace(/_/g, ' ').replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase())}</span>`;
                },
                width: '15%'
            },
            {
                data: 'roles_count',
                name: 'roles_count',
                render: function(data, type, row) {
                    const roleText = data === 1 ? 'Role' : 'Roles';
                    return `<span class="badge bg-info fs-6">${data} ${roleText}</span>`;
                },
                width: '10%'
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    const date = new Date(data);
                    const dateStr = date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                    const timeStr = date.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    return `
                        <small>
                            ${dateStr}<br>
                            <span class="text-muted">${timeStr}</span>
                        </small>
                    `;
                },
                width: '15%'
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let html = `<div class="btn-group" role="group">`;

                    // View button
                    html += `
                        <a href="/admin/permissions/${row.id}"
                           class="btn btn-sm btn-outline-info"
                           data-bs-toggle="tooltip"
                           title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    `;

                    // Edit button
                    html += `
                        <a href="/admin/permissions/${row.id}/edit"
                           class="btn btn-sm btn-outline-warning"
                           data-bs-toggle="tooltip"
                           title="Edit Permission">
                            <i class="bi bi-pencil"></i>
                        </a>
                    `;

                    // Delete button (only if not in use)
                    if (row.can_delete) {
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-permission"
                                    data-permission-id="${row.id}"
                                    data-permission-name="${row.display_name}"
                                    data-bs-toggle="tooltip"
                                    title="Delete Permission">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    } else {
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="tooltip"
                                    title="Cannot delete: In use by roles"
                                    disabled>
                                <i class="bi bi-lock"></i>
                            </button>
                        `;
                    }

                    html += `</div>`;
                    return html;
                },
                width: '15%'
            }
        ],
        order: [[2, 'asc'], [3, 'asc']], // Order by module, then action
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"B>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
                className: 'btn btn-outline-success btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5] // Exclude # and Actions columns
                }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
                className: 'btn btn-outline-danger btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i>Print',
                className: 'btn btn-outline-secondary btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            }
        ],
        language: {
            processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: '<div class="text-center py-4"><i class="bi bi-key" style="font-size: 2rem;"></i><p class="mt-2 text-muted">No permissions found</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="bi bi-search" style="font-size: 2rem;"></i><p class="mt-2 text-muted">No matching permissions found</p></div>',
            lengthMenu: 'Show _MENU_ entries',
            search: 'Search permissions:',
            info: 'Showing _START_ to _END_ of _TOTAL_ permissions',
            infoEmpty: 'Showing 0 to 0 of 0 permissions',
            infoFiltered: '(filtered from _MAX_ total permissions)',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        drawCallback: function(settings) {
            // Initialize tooltips after each draw
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Filter functionality
    $('#moduleFilter, #actionFilter').on('change', function() {
        table.draw();
    });

    // Refresh button
    $('#refreshTable').on('click', function() {
        table.ajax.reload();
        loadStats();
        showAlert('success', 'Data refreshed successfully');
    });

    // Delete permission functionality
    let permissionToDelete = null;

    $(document).on('click', '.delete-permission', function() {
        permissionToDelete = $(this).data('permission-id');
        const permissionName = $(this).data('permission-name');

        $('#permissionName').text(permissionName);
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (permissionToDelete) {
            showLoadingOverlay();

            $.ajax({
                url: `/admin/permissions/${permissionToDelete}`,
                method: 'DELETE',
                success: function(response) {
                    hideLoadingOverlay();
                    $('#deleteModal').modal('hide');

                    if (response.success) {
                        showAlert('success', response.message);
                        table.ajax.reload();
                        loadStats();
                    } else {
                        showAlert('danger', response.message || 'Error deleting permission');
                    }
                },
                error: function(xhr) {
                    hideLoadingOverlay();
                    $('#deleteModal').modal('hide');

                    let message = 'Error deleting permission';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    showAlert('danger', message);
                }
            });
        }
    });

    // Load statistics
    function loadStats() {
        $.ajax({
            url: "{{ route('admin.permissions.stats') }}",
            method: 'GET',
            success: function(response) {
                $('#totalPermissions').text(response.total_permissions);
                $('#totalModules').text(response.total_modules);
                $('#totalActions').text(response.total_actions);
                $('#totalRoleAssignments').text(response.total_role_assignments);
            },
            error: function() {
                console.log('Error loading statistics');
            }
        });
    }

    // Helper functions
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertIcon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi bi-${alertIcon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').remove();

        // Insert alert at the top of the card body
        $('.card-body').prepend(alertHtml);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    function showLoadingOverlay() {
        $('#loadingOverlay').show();
    }

    function showLoadingOverlay() {
        $('#loadingOverlay').show();
    }

    function hideLoadingOverlay() {
        $('#loadingOverlay').hide();
    }

    // Initial load
    loadStats();

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
