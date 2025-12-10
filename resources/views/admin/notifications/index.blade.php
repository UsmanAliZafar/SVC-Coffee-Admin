@extends('admin.layouts.app')

@section('title', 'Notifications')
@push('styles')
<!-- Add Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
.notification-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item.bg-light {
    border-left-color: #0d6efd;
}

.notification-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 50%;
}
</style>
@endpush
@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Notifications</h1>
            <p class="text-muted mb-0">Manage your notifications and alerts</p>
        </div>
        <div>
            <a href="{{ route('admin.notifications.settings') }}" class="btn btn-outline-primary">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-bell fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Unread</p>
                            <h3 class="mb-0 text-warning">{{ $stats['unread'] }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-envelope-open fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Read</p>
                            <h3 class="mb-0 text-success">{{ $stats['read'] }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Urgent</p>
                            <h3 class="mb-0 text-danger">{{ $stats['urgent'] }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="type" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $category)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $category['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            <hr class="my-3">

            <!-- Bulk Actions -->
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success" id="markAllReadBtn">
                    <i class="bi bi-check-all"></i> Mark All as Read
                </button>
                <button type="button" class="btn btn-sm btn-warning" id="clearReadBtn">
                    <i class="bi bi-trash"></i> Clear Read
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="clearAllBtn">
                    <i class="bi bi-trash3"></i> Clear All
                </button>
            </div>
        </div>
    </div>
    <!-- Notifications List -->
    <div class="card">
        <div class="card-body">
            <table id="notificationsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th width="50"></th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Time</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};
</script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#notificationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.notifications.data") }}',
            data: function(d) {
                d.type = $('select[name="type"]').val();
                d.status = $('select[name="status"]').val();
                d.priority = $('select[name="priority"]').val();
            }
        },
        columns: [
            {
                data: 'icon',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<div class="notification-icon text-${row.color}">
                        <i class="${data} fs-4"></i>
                    </div>`;
                }
            },
            {
                data: 'title',
                render: function(data, type, row) {
                    let badge = row.is_read ? '' : '<span class="badge bg-primary badge-sm ms-2">New</span>';
                    return data + badge;
                }
            },
            {
                data: 'message',
                render: function(data) {
                    return data.length > 100 ? data.substring(0, 100) + '...' : data;
                }
            },
            {
                data: 'badge_html',
                orderable: false
            },
            {
                data: 'priority_badge',
                orderable: false
            },
            {
                data: 'time_ago',
                orderable: false
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [[5, 'desc']],
        pageLength: 20,
        language: {
            emptyTable: '<div class="text-center py-4"><i class="bi bi-bell-slash fs-1 text-muted"></i><p class="text-muted mt-3">No notifications found</p></div>'
        },
        drawCallback: function() {
            attachEventHandlers();
        }
    });

    // Filter form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Clear filters
    $('.btn-outline-secondary').on('click', function(e) {
        e.preventDefault();
        $('select[name="type"]').val('');
        $('select[name="status"]').val('');
        $('select[name="priority"]').val('');
        table.ajax.reload();
    });

    // Attach event handlers to dynamically loaded content
    function attachEventHandlers() {
        // Mark as read
        $('.mark-read-btn').off('click').on('click', function() {
            const btn = $(this);
            const notificationId = btn.data('id');

            $.ajax({
                url: `/admin/notifications/${notificationId}/mark-as-read`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        table.ajax.reload(null, false);
                        updateUnreadCount();
                    }
                },
                error: function() {
                    toastr.error('Failed to mark as read');
                }
            });
        });

        // Delete notification
        $('.delete-notification-btn').off('click').on('click', function() {
            const btn = $(this);
            const notificationId = btn.data('id');

            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }

            $.ajax({
                url: `/admin/notifications/${notificationId}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        table.ajax.reload(null, false);
                        updateUnreadCount();
                    }
                },
                error: function() {
                    toastr.error('Failed to delete notification');
                }
            });
        });
    }

    // Mark all as read
    $('#markAllReadBtn').on('click', function() {
        // Show confirmation dialog instead of alert
        if (!confirm('Are you sure you want to mark all notifications as read?')) {
            return;
        }

        // Show loading state
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: '{{ route("admin.notifications.mark-all-as-read") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);

                    // Option 1: Reload page after toastr (recommended)
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500); // Wait 1.5 seconds to show toastr
                }
            },
            error: function() {
                toastr.error('Failed to mark all as read');
                // Re-enable button on error
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Clear read notifications
    $('#clearReadBtn').on('click', function() {
        if (!confirm('Are you sure you want to clear all read notifications?')) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.notifications.clear-read") }}',
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    table.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Failed to clear notifications');
            }
        });
    });

    // Clear all notifications
    $('#clearAllBtn').on('click', function() {
        if (!confirm('Are you sure you want to clear ALL notifications? This cannot be undone.')) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.notifications.clear-all") }}',
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    table.ajax.reload();
                }
            },
            error: function() {
                toastr.error('Failed to clear notifications');
            }
        });
    });

    // Update unread count
    function updateUnreadCount() {
        $.get('{{ route("admin.notifications.unread") }}', function(response) {
            if (response.success) {
                $('.notification-badge').text(response.unread_count);
                if (response.unread_count === 0) {
                    $('.notification-badge').hide();
                } else {
                    $('.notification-badge').show();
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.notification-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item.bg-light {
    border-left-color: #0d6efd;
}

.notification-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 50%;
}
</style>
@endpush
@endsection
