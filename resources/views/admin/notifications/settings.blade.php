@extends('admin.layouts.app')

@section('title', 'Notification Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
.form-check-input {
    width: 3em;
    height: 1.5em;
}

.table td {
    vertical-align: middle;
}

.advanced-btn {
    font-size: 0.875rem;
}

.card-header h5 {
    margin-bottom: 0;
}

.config-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    margin-left: 0.5rem;
}

.notification-type-cell {
    position: relative;
}

.config-indicators {
    display: flex;
    gap: 0.25rem;
    margin-top: 0.25rem;
}

.indicator-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    white-space: nowrap;
}

.default-email-indicator {
    background-color: #e7f3ff;
    color: #004085;
    border: 1px solid #b8daff;
}

.customer-email-indicator {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.priority-indicator {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.priority-urgent {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.priority-high {
    background-color: #fff3cd;
    color: #856404;
}

.priority-normal {
    background-color: #d1ecf1;
    color: #0c5460;
}

.priority-low {
    background-color: #e2e3e5;
    color: #383d41;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Notification Settings</h1>
            <p class="text-muted mb-0">Configure your notification preferences</p>
        </div>
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Notifications
            </a>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-info-circle fs-4 me-3"></i>
        <div>
            <strong>How it works:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>In-App Toggle:</strong> Enable/disable in-app notifications (shown in admin panel)</li>
                <li><strong>Email Toggle:</strong> Enable/disable email notifications to your inbox</li>
                <li><strong>Config Indicators:</strong> Show default settings and customer notification status</li>
            </ul>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Badge Legend</h6>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                <span class="indicator-badge default-email-indicator">
                    <i class="bi bi-envelope"></i> Default Email: On
                </span>
                <span class="indicator-badge customer-email-indicator">
                    <i class="bi bi-person-check"></i> Customer Email: On
                </span>
                <span class="indicator-badge priority-urgent">
                    <i class="bi bi-exclamation-triangle"></i> Urgent
                </span>
                <span class="indicator-badge priority-high">
                    <i class="bi bi-exclamation-circle"></i> High
                </span>
                <span class="indicator-badge priority-normal">
                    <i class="bi bi-info-circle"></i> Normal
                </span>
                <span class="indicator-badge priority-low">
                    <i class="bi bi-dash-circle"></i> Low
                </span>
            </div>
        </div>
    </div>

    <form id="notificationSettingsForm">
        @csrf

        @foreach($groupedSettings as $categoryKey => $settings)
            @php
                $category = $categories[$categoryKey] ?? ['label' => ucfirst($categoryKey), 'icon' => 'bi-bell', 'color' => 'secondary'];
            @endphp

            <div class="card mb-4">
                <div class="card-header bg-{{ $category['color'] }} text-white">
                    <h5 class="mb-0">
                        <i class="{{ $category['icon'] }} me-2"></i>
                        {{ $category['label'] }}
                    </h5>
                    <div class="float-end" style="margin-top: -1.5rem;">
                        <small>
                            <a href="#" class="text-white text-decoration-none toggle-all-app" data-category="{{ $categoryKey }}">
                                <i class="bi bi-toggle-on"></i> Toggle All In-App
                            </a>
                            <span class="mx-2">|</span>
                            <a href="#" class="text-white text-decoration-none toggle-all-email" data-category="{{ $categoryKey }}">
                                <i class="bi bi-envelope"></i> Toggle All Email
                            </a>
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 35%">Notification Type</th>
                                    <th style="width: 15%" class="text-center">In-App</th>
                                    <th style="width: 15%" class="text-center">Email</th>
                                    <th style="width: 20%">Config Info</th>
                                    <th style="width: 15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settings as $setting)
                                    @php
                                        $config = $setting->getConfig();
                                        $defaultEmail = $config['default_email'] ?? false;
                                        $sendToCustomer = $config['send_to_customer'] ?? false;
                                        $priority = $config['priority'] ?? 'normal';
                                    @endphp
                                    <tr data-notification-type="{{ $setting->notification_type }}" data-category="{{ $categoryKey }}">
                                        <td class="notification-type-cell">
                                            <div>
                                                <strong>{{ $setting->getLabel() }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $setting->getDescription() }}</small>
                                                <div class="config-indicators">
                                                    @if($defaultEmail)
                                                        <span class="indicator-badge default-email-indicator" title="Default email notification is enabled in config">
                                                            <i class="bi bi-envelope"></i> Default Email
                                                        </span>
                                                    @endif
                                                    @if($sendToCustomer)
                                                        <span class="indicator-badge customer-email-indicator" title="This notification is sent to customers">
                                                            <i class="bi bi-person-check"></i> Customer Email
                                                        </span>
                                                    @endif
                                                    <span class="indicator-badge priority-{{ $priority }}" title="Priority: {{ ucfirst($priority) }}">
                                                        @if($priority === 'urgent')
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                        @elseif($priority === 'high')
                                                            <i class="bi bi-exclamation-circle"></i>
                                                        @elseif($priority === 'normal')
                                                            <i class="bi bi-info-circle"></i>
                                                        @else
                                                            <i class="bi bi-dash-circle"></i>
                                                        @endif
                                                        {{ ucfirst($priority) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input in-app-toggle"
                                                       type="checkbox"
                                                       name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][is_enabled]"
                                                       value="1"
                                                       {{ $setting->is_enabled ? 'checked' : '' }}
                                                       style="cursor: pointer;">
                                                <input type="hidden"
                                                       name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][notification_type]"
                                                       value="{{ $setting->notification_type }}">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input email-toggle"
                                                       type="checkbox"
                                                       name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][send_email]"
                                                       value="1"
                                                       {{ $setting->send_email ? 'checked' : '' }}
                                                       style="cursor: pointer;">
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <div><strong>Default Email:</strong> {{ $defaultEmail ? 'Yes' : 'No' }}</div>
                                                <div><strong>Customer Email:</strong> {{ $sendToCustomer ? 'Yes' : 'No' }}</div>
                                                <div><strong>Priority:</strong> {{ ucfirst($priority) }}</div>
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary advanced-btn"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#advanced-{{ $setting->notification_type }}">
                                                <i class="bi bi-gear"></i> Advanced
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Advanced Settings Row -->
                                    <tr class="collapse" id="advanced-{{ $setting->notification_type }}">
                                        <td colspan="5" class="bg-light">
                                            <div class="p-3">
                                                <h6 class="mb-3">
                                                    <i class="bi bi-gear"></i> Advanced Settings for {{ $setting->getLabel() }}
                                                </h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <i class="bi bi-envelope"></i> Custom Email Address
                                                        </label>
                                                        <input type="email"
                                                               class="form-control form-control-sm"
                                                               name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][email_address]"
                                                               value="{{ $setting->email_address }}"
                                                               placeholder="Leave empty to use default">
                                                        <small class="text-muted">
                                                            <i class="bi bi-info-circle"></i> Default: {{ auth('admin')->user()->email }}
                                                        </small>
                                                    </div>

                                                    @if(in_array($setting->notification_type, ['stock_low', 'stock_critical', 'stock_out']))
                                                        <div class="col-md-6">
                                                            <label class="form-label">
                                                                <i class="bi bi-box-seam"></i> Stock Threshold
                                                            </label>
                                                            <input type="number"
                                                                   class="form-control form-control-sm"
                                                                   name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][threshold_value]"
                                                                   value="{{ $setting->threshold_value }}"
                                                                   placeholder="Custom threshold"
                                                                   min="0">
                                                            <small class="text-muted">
                                                                <i class="bi bi-info-circle"></i> Override default product threshold
                                                            </small>
                                                        </div>
                                                    @endif

                                                    <div class="col-12">
                                                        <div class="alert alert-info mb-0">
                                                            <strong><i class="bi bi-info-circle"></i> Config Information:</strong>
                                                            <ul class="mb-0 mt-2">
                                                                <li><strong>Category:</strong> {{ $config['category'] ?? 'N/A' }}</li>
                                                                <li><strong>Icon:</strong> <i class="{{ $config['icon'] ?? 'bi-bell' }}"></i> {{ $config['icon'] ?? 'N/A' }}</li>
                                                                <li><strong>Color:</strong> <span class="badge bg-{{ $config['color'] ?? 'secondary' }}">{{ $config['color'] ?? 'N/A' }}</span></li>
                                                                <li><strong>Default Email Enabled:</strong> {{ $defaultEmail ? '✅ Yes' : '❌ No' }}</li>
                                                                <li><strong>Send to Customer:</strong> {{ $sendToCustomer ? '✅ Yes (Customer will receive this notification)' : '❌ No (Admin only)' }}</li>
                                                                <li><strong>Priority Level:</strong> {{ ucfirst($priority) }}</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-warning" id="resetDefaultsBtn">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset to Defaults
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if(config('app.debug'))
        <!-- Test Notification Button (Only in Debug Mode) -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-bug"></i> Developer Tools</h6>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-info" id="testNotificationBtn">
                    <i class="bi bi-send"></i> Send Test Notification
                </button>
                <small class="text-muted ms-2">This will send a test "Order Created" notification</small>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
</script>
<script>
$(document).ready(function() {
    // Save settings
    $('#notificationSettingsForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '{{ route("admin.notifications.settings.update") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to save settings');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Reset to defaults
    $('#resetDefaultsBtn').on('click', function() {
        if (!confirm('Are you sure you want to reset all settings to default values?\n\nThis will:\n- Enable/disable notifications based on config defaults\n- Clear custom email addresses\n- Reset all thresholds')) {
            return;
        }

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Resetting...');

        $.ajax({
            url: '{{ route("admin.notifications.settings.reset") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function() {
                toastr.error('Failed to reset settings');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Test notification (debug only)
    $('#testNotificationBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');

        $.ajax({
            url: '{{ route("admin.notifications.test") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to send test notification');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-send"></i> Send Test Notification');
            }
        });
    });

    // Toggle all in-app for category
    $('.toggle-all-app').on('click', function(e) {
        e.preventDefault();
        const category = $(this).data('category');
        const switches = $(`tr[data-category="${category}"] .in-app-toggle`);
        const allChecked = switches.filter(':checked').length === switches.length;
        switches.prop('checked', !allChecked);
    });

    // Toggle all email for category
    $('.toggle-all-email').on('click', function(e) {
        e.preventDefault();
        const category = $(this).data('category');
        const switches = $(`tr[data-category="${category}"] .email-toggle`);
        const allChecked = switches.filter(':checked').length === switches.length;
        switches.prop('checked', !allChecked);
    });

    // Add tooltips
    $('[title]').tooltip();
});
</script>
@endpush
@endsection
