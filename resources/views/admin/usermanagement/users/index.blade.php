@extends('admin.layouts.app')

@section('title', 'Admin Users Management')

@section('page_title', 'Admin Users')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Admin Users</li>
        </ol>
    </nav>
@endsection

@push('styles')
<style>
    .avatar-sm {
        flex-shrink: 0;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .status-toggle {
        cursor: pointer;
    }

    .form-switch .form-check-input {
        width: 2.5rem;
        height: 1.25rem;
    }

    .card.bg-primary {
        background-color: #5B914C !important;
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
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-gear me-2"></i>Admin Users Management
                    </h5>
                    @if(auth('admin')->user()->hasPermission('admin_users.create'))
                    <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Add New User
                    </a>
                    @endif
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Role</label>
                            <select class="form-select" id="roleFilter">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Filter by Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
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
                        <table class="table table-hover table-striped" id="adminUsersTable" width="100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">User Info</th>
                                    <th width="20%">Roles</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Created</th>
                                    <th width="20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTable will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0" id="totalUsers">0</h3>
                                    <p class="mb-0">Total Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person-gear" style="font-size: 2rem;"></i>
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
                                    <h3 class="mb-0" id="activeUsers">0</h3>
                                    <p class="mb-0">Active Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person-check" style="font-size: 2rem;"></i>
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
                                    <h3 class="mb-0" id="inactiveUsers">0</h3>
                                    <p class="mb-0">Inactive Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person-x" style="font-size: 2rem;"></i>
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
                                    <h3 class="mb-0" id="totalRoles">0</h3>
                                    <p class="mb-0">Available Roles</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
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
                <p>Are you sure you want to delete the admin user <strong id="userName"></strong>?</p>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete User</button>
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
    const table = $('#adminUsersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.users.data') }}",
            type: 'GET',
            data: function(d) {
                d.role_filter = $('#roleFilter').val();
                d.status_filter = $('#statusFilter').val();
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
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const initials = row.name.substring(0, 2).toUpperCase();
                    let html = `
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px;">
                                    ${initials}
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">${row.name}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-envelope me-1"></i>${row.email}<br>
                                    <i class="bi bi-person me-1"></i>${row.username}
                    `;
                    if (row.phone) {
                        html += `<br><i class="bi bi-phone me-1"></i>${row.phone}`;
                    }
                    html += `
                                </small>
                            </div>
                        </div>
                    `;
                    return html;
                },
                width: '25%'
            },
            {
                data: 'roles',
                name: 'roles',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let html = '';
                    if (row.roles && row.roles.length > 0) {
                        row.roles.forEach(role => {
                            html += `<span class="badge bg-info text-dark me-1">${role.display_name}</span>`;
                        });
                    } else {
                        html = '<span class="text-muted">No roles assigned</span>';
                    }
                    return html;
                },
                width: '20%'
            },
            {
                data: 'is_active',
                name: 'is_active',
                render: function(data, type, row) {
                    const isChecked = row.is_active ? 'checked' : '';
                    const badgeClass = row.is_active ? 'bg-success' : 'bg-secondary';
                    const badgeText = row.is_active ? 'Active' : 'Inactive';

                    if (row.can_edit) {
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle"
                                       type="checkbox"
                                       data-user-id="${row.id}"
                                       ${isChecked}>
                                <label class="form-check-label">
                                    <span class="badge ${badgeClass}">${badgeText}</span>
                                </label>
                            </div>
                        `;
                    } else {
                        return `<span class="badge ${badgeClass}">${badgeText}</span>`;
                    }
                },
                width: '15%'
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
                    if (row.can_view) {
                        html += `
                            <a href="/admin/users/${row.id}"
                               class="btn btn-sm btn-outline-info"
                               data-bs-toggle="tooltip"
                               title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        `;
                    }

                    // Edit button
                    if (row.can_edit) {
                        html += `
                            <a href="/admin/users/${row.id}/edit"
                               class="btn btn-sm btn-outline-warning"
                               data-bs-toggle="tooltip"
                               title="Edit User">
                                <i class="bi bi-pencil"></i>
                            </a>
                        `;
                    }

                    // Delete button
                    if (row.can_delete) {
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-user"
                                    data-user-id="${row.id}"
                                    data-user-name="${row.name}"
                                    data-bs-toggle="tooltip"
                                    title="Delete User">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }

                    html += `</div>`;
                    return html;
                },
                width: '20%'
            }
        ],
        order: [[4, 'desc']], // Order by created_at desc
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
                    columns: [1, 2, 3, 4]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
                className: 'btn btn-outline-danger btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i>Print',
                className: 'btn btn-outline-secondary btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                }
            }
        ],
        language: {
            processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: '<div class="text-center py-4"><i class="bi bi-person-x" style="font-size: 2rem;"></i><p class="mt-2 text-muted">No admin users found</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="bi bi-search" style="font-size: 2rem;"></i><p class="mt-2 text-muted">No matching users found</p></div>',
            lengthMenu: 'Show _MENU_ entries',
            search: 'Search users:',
            info: 'Showing _START_ to _END_ of _TOTAL_ users',
            infoEmpty: 'Showing 0 to 0 of 0 users',
            infoFiltered: '(filtered from _MAX_ total users)',
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
    $('#roleFilter, #statusFilter').on('change', function() {
        table.draw();
    });

    // Refresh button
    $('#refreshTable').on('click', function() {
        table.ajax.reload();
        loadStats();
        showAlert('success', 'Data refreshed successfully');
    });

    // Status toggle functionality
    $(document).on('change', '.status-toggle', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked');
        const toggle = $(this);
        const badge = toggle.siblings('label').find('.badge');

        $.ajax({
            url: `/admin/users/${userId}/toggle-status`,
            method: 'POST',
            beforeSend: function() {
                toggle.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    badge.removeClass('bg-success bg-secondary')
                         .addClass(response.status ? 'bg-success' : 'bg-secondary')
                         .text(response.status ? 'Active' : 'Inactive');

                    showAlert('success', response.message);
                    loadStats(); // Refresh stats
                } else {
                    // Revert checkbox state
                    toggle.prop('checked', !isChecked);
                    showAlert('danger', response.message || 'Failed to update status');
                }
            },
            error: function(xhr) {
                // Revert checkbox state
                toggle.prop('checked', !isChecked);

                let errorMessage = 'Failed to update status';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                showAlert('danger', errorMessage);
            },
            complete: function() {
                toggle.prop('disabled', false);
            }
        });
    });

    // Delete user functionality
    let userToDelete = null;

    $(document).on('click', '.delete-user', function() {
        userToDelete = $(this).data('user-id');
        const userName = $(this).data('user-name');

        $('#userName').text(userName);
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (userToDelete) {
            showLoadingOverlay();

            $.ajax({
                url: `/admin/users/${userToDelete}`,
                method: 'DELETE',
                success: function(response) {
                    hideLoadingOverlay();
                    $('#deleteModal').modal('hide');

                    if (response.success) {
                        showAlert('success', response.message);
                        table.ajax.reload();
                        loadStats();
                    } else {
                        showAlert('danger', response.message || 'Error deleting user');
                    }
                },
                error: function(xhr) {
                    hideLoadingOverlay();
                    $('#deleteModal').modal('hide');

                    let message = 'Error deleting user';
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
            url: "{{ route('admin.users.stats') }}",
            method: 'GET',
            success: function(response) {
                $('#totalUsers').text(response.total_users);
                $('#activeUsers').text(response.active_users);
                $('#inactiveUsers').text(response.inactive_users);
                $('#totalRoles').text(response.total_roles);
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
