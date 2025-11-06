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
            <strong>How it works:</strong> In-app notifications are always created. You can optionally enable email notifications for specific types below.
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
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Notification Type</th>
                                    <th style="width: 20%" class="text-center">In-App</th>
                                    <th style="width: 20%" class="text-center">Email</th>
                                    <th style="width: 20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settings as $setting)
                                    <tr data-notification-type="{{ $setting->notification_type }}">
                                        <td>
                                            <div>
                                                <strong>{{ $setting->getLabel() }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $setting->getDescription() }}</small>
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
                                        <td colspan="4" class="bg-light">
                                            <div class="p-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Custom Email Address</label>
                                                        <input type="email"
                                                               class="form-control form-control-sm"
                                                               name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][email_address]"
                                                               value="{{ $setting->email_address }}"
                                                               placeholder="Leave empty to use default">
                                                        <small class="text-muted">Default: {{ auth('admin')->user()->email }}</small>
                                                    </div>

                                                    @if(in_array($setting->notification_type, ['stock_low', 'stock_critical', 'stock_out']))
                                                        <div class="col-md-6">
                                                            <label class="form-label">Stock Threshold</label>
                                                            <input type="number"
                                                                   class="form-control form-control-sm"
                                                                   name="settings[{{ $loop->parent->index }}_{{ $loop->index }}][threshold_value]"
                                                                   value="{{ $setting->threshold_value }}"
                                                                   placeholder="Custom threshold"
                                                                   min="0">
                                                            <small class="text-muted">Override default product threshold</small>
                                                        </div>
                                                    @endif
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
            }
        });
    });

    // Reset to defaults
    $('#resetDefaultsBtn').on('click', function() {
        if (!confirm('Are you sure you want to reset all settings to default values?')) {
            return;
        }

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

    // Toggle all in category
    $('.card').each(function() {
        const card = $(this);
        const header = card.find('.card-header');

        // Add toggle all buttons
        header.append(`
            <div class="float-end">
                <small class="me-3">
                    <a href="#" class="text-white toggle-all-app">Toggle All In-App</a> |
                    <a href="#" class="text-white toggle-all-email">Toggle All Email</a>
                </small>
            </div>
        `);
    });

    // Toggle all in-app
    $('.toggle-all-app').on('click', function(e) {
        e.preventDefault();
        const card = $(this).closest('.card');
        const switches = card.find('.in-app-toggle');
        const allChecked = switches.filter(':checked').length === switches.length;
        switches.prop('checked', !allChecked);
    });

    // Toggle all email
    $('.toggle-all-email').on('click', function(e) {
        e.preventDefault();
        const card = $(this).closest('.card');
        const switches = card.find('.email-toggle');
        const allChecked = switches.filter(':checked').length === switches.length;
        switches.prop('checked', !allChecked);
    });
});
</script>
@endpush
@endsection
