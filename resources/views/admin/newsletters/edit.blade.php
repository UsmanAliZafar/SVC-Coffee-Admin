@extends('admin.layouts.app')

@section('title', 'Edit Newsletter Subscriber')

@push('styles')
<style>
    .form-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
    }
    .form-section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    .form-label .required {
        color: #dc3545;
        margin-left: 3px;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: #5B914C;
        box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
    }
    .btn-update {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        padding: 10px 30px;
        font-weight: 600;
    }
    .btn-update:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }
    .form-text {
        color: #6c757d;
        font-size: 0.875rem;
    }
    .form-check-input:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }
    .info-box {
        background-color: #e7f3e3;
        border-left: 4px solid #5B914C;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .info-box i {
        color: #5B914C;
    }
    .subscriber-preview {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 20px;
    }
    .subscriber-avatar-edit {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.3);
        color: white;
        font-size: 2rem;
        font-weight: bold;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border: 3px solid white;
    }
    .status-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .status-indicator.active {
        background-color: #28a745;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
    }
    .status-indicator.inactive {
        background-color: #dc3545;
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
    }
    .timeline-compact {
        font-size: 0.875rem;
    }
    .timeline-compact .timeline-item {
        padding: 8px 0;
        border-left: 2px solid #dee2e6;
        padding-left: 15px;
        margin-left: 5px;
    }
    .timeline-compact .timeline-item:last-child {
        border-left: 2px solid transparent;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil-square"></i> Edit Newsletter Subscriber</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.newsletters.index') }}">Newsletter</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.newsletters.show', $newsletter->id) }}">View Subscriber</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.newsletters.show', $newsletter->id) }}" class="btn btn-outline-info">
                <i class="bi bi-eye me-2"></i>View Details
            </a>
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="info-box">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Editing Subscriber:</strong> You are modifying the information for <strong>{{ $newsletter->email }}</strong>
    </div>

    {{-- Form Row --}}
    <div class="row">
        {{-- Form Column --}}
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.newsletters.update', $newsletter->id) }}" method="POST" id="editNewsletterForm">
                        @csrf
                        @method('PUT')

                        {{-- Subscriber Information Section --}}
                        <div class="mb-4">
                            <h5 class="form-section-title">
                                <i class="bi bi-person-circle me-2"></i>Subscriber Information
                            </h5>

                            {{-- Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email Address<span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $newsletter->email) }}"
                                           placeholder="Enter subscriber email"
                                           required>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-shield-check me-1"></i>Changing the email will update the subscriber's primary contact
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Name --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Full Name
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $newsletter->name) }}"
                                           placeholder="Enter subscriber name (optional)"
                                           maxlength="255">
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-chat-left-quote me-1"></i>Used for email personalization (e.g., "Hi John")
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Subscription Status Section --}}
                        <div class="mb-4">
                            <h5 class="form-section-title">
                                <i class="bi bi-toggle-on me-2"></i>Subscription Status
                            </h5>

                            <div class="card bg-light border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       role="switch"
                                                       id="is_subscribed"
                                                       name="is_subscribed"
                                                       value="1"
                                                       {{ old('is_subscribed', $newsletter->is_subscribed) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_subscribed">
                                                    <strong>Active Subscription</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted ms-5">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Toggle to activate or deactivate newsletter subscription
                                            </small>
                                        </div>
                                        <div>
                                            <span class="status-indicator" id="statusIndicator"></span>
                                            <span id="statusText" class="fw-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Current Status Information --}}
                        <div class="alert alert-info border-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-clock-history me-2"></i>Current Status Information
                            </h6>
                            <div class="row small">
                                <div class="col-md-6 mb-2">
                                    <strong>Status:</strong>
                                    @if($newsletter->is_subscribed)
                                        <span class="badge bg-success">Subscribed</span>
                                    @else
                                        <span class="badge bg-danger">Unsubscribed</span>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Member Since:</strong> {{ $newsletter->created_at->format('M d, Y') }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Subscribed At:</strong>
                                    {{ $newsletter->subscribed_at ? $newsletter->subscribed_at->format('M d, Y h:i A') : 'Never' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Unsubscribed At:</strong>
                                    {{ $newsletter->unsubscribed_at ? $newsletter->unsubscribed_at->format('M d, Y h:i A') : '—' }}
                                </div>
                            </div>
                        </div>

                        {{-- Warning Message --}}
                        <div class="alert alert-warning border-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Any changes to the subscription status will be logged with the current timestamp.
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('admin.newsletters.show', $newsletter->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-warning me-2" id="resetBtn">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Changes
                                </button>
                                <button type="submit" class="btn btn-update">
                                    <i class="bi bi-check-circle me-2"></i>Update Subscriber
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar Column --}}
        <div class="col-lg-4">
            {{-- Subscriber Preview Card --}}
            <div class="subscriber-preview">
                <div class="subscriber-avatar-edit" id="avatarPreview">
                    {{ strtoupper(substr($newsletter->email, 0, 1)) }}
                </div>
                <h5 id="namePreview" class="mb-1">{{ $newsletter->name ?? 'Anonymous Subscriber' }}</h5>
                <p id="emailPreview" class="mb-0 opacity-75">{{ $newsletter->email }}</p>
            </div>

            {{-- Quick Info Card --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Subscriber Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Subscriber ID</small>
                        <div><code>{{ $newsletter->id }}</code></div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Created At</small>
                        <div>{{ $newsletter->created_at->format('M d, Y') }}</div>
                        <small class="text-muted">{{ $newsletter->created_at->diffForHumans() }}</small>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Last Updated</small>
                        <div>
                            @if($newsletter->updated_at && !$newsletter->updated_at->eq($newsletter->created_at))
                                {{ $newsletter->updated_at->format('M d, Y') }}
                                <br><small class="text-muted">{{ $newsletter->updated_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never updated</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div>
                        <small class="text-muted">Total Days</small>
                        <div><strong>{{ $newsletter->created_at->diffInDays(now()) }}</strong> days</div>
                    </div>
                </div>
            </div>

            {{-- Activity Timeline Card --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline-compact">
                        @if($newsletter->updated_at && !$newsletter->updated_at->eq($newsletter->created_at))
                        <div class="timeline-item">
                            <i class="bi bi-pencil text-primary"></i>
                            <strong>Updated</strong>
                            <div class="text-muted small">{{ $newsletter->updated_at->diffForHumans() }}</div>
                        </div>
                        @endif

                        @if($newsletter->unsubscribed_at)
                        <div class="timeline-item">
                            <i class="bi bi-x-circle text-danger"></i>
                            <strong>Unsubscribed</strong>
                            <div class="text-muted small">{{ $newsletter->unsubscribed_at->diffForHumans() }}</div>
                        </div>
                        @endif

                        @if($newsletter->subscribed_at)
                        <div class="timeline-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <strong>Subscribed</strong>
                            <div class="text-muted small">{{ $newsletter->subscribed_at->diffForHumans() }}</div>
                        </div>
                        @endif

                        <div class="timeline-item">
                            <i class="bi bi-plus-circle text-info"></i>
                            <strong>Created</strong>
                            <div class="text-muted small">{{ $newsletter->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-question-circle me-2"></i>Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        <strong>Tip:</strong> Changes take effect immediately after saving.
                    </p>
                    <p class="small mb-0">
                        <i class="bi bi-shield-check text-success me-2"></i>
                        <strong>Security:</strong> All changes are logged and tracked.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Original values for reset
    const originalEmail = '{{ old('email', $newsletter->email) }}';
    const originalName = '{{ old('name', $newsletter->name) }}';
    const originalSubscribed = {{ old('is_subscribed', $newsletter->is_subscribed) ? 'true' : 'false' }};

    // Update status indicator
    updateStatusIndicator();

    // Email input - update preview
    $('#email').on('input', function() {
        const email = $(this).val().trim();
        if (email) {
            $('#emailPreview').text(email);
            const firstLetter = email.charAt(0).toUpperCase();
            $('#avatarPreview').text(firstLetter);
        }
    });

    // Name input - update preview
    $('#name').on('input', function() {
        const name = $(this).val().trim();
        if (name) {
            $('#namePreview').text(name);
        } else {
            $('#namePreview').text('Anonymous Subscriber');
        }
    });

    // Subscription status toggle
    $('#is_subscribed').on('change', function() {
        updateStatusIndicator();
    });

    // Update status indicator function
    function updateStatusIndicator() {
        const isSubscribed = $('#is_subscribed').is(':checked');
        const indicator = $('#statusIndicator');
        const text = $('#statusText');

        if (isSubscribed) {
            indicator.removeClass('inactive').addClass('active');
            text.text('Active').removeClass('text-danger').addClass('text-success');
        } else {
            indicator.removeClass('active').addClass('inactive');
            text.text('Inactive').removeClass('text-success').addClass('text-danger');
        }
    }

    // Reset button
    $('#resetBtn').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Reset Changes?',
            text: 'This will restore all fields to their original values',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#email').val(originalEmail);
                $('#name').val(originalName);
                $('#is_subscribed').prop('checked', originalSubscribed);

                // Update previews
                $('#emailPreview').text(originalEmail);
                $('#namePreview').text(originalName || 'Anonymous Subscriber');
                $('#avatarPreview').text(originalEmail.charAt(0).toUpperCase());

                updateStatusIndicator();

                // Remove validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                showNotification('Form has been reset to original values', 'info');
            }
        });
    });

    // Form validation
    $('#editNewsletterForm').on('submit', function(e) {
        const email = $('#email').val().trim();

        if (!email) {
            e.preventDefault();
            showNotification('Please enter an email address', 'warning');
            $('#email').focus();
            return false;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showNotification('Please enter a valid email address', 'warning');
            $('#email').focus();
            return false;
        }

        // Show loading state
        $(this).find('button[type="submit"]')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
    });

    // Email validation on blur
    $('#email').on('blur', function() {
        const email = $(this).val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback d-block">Please enter a valid email address</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
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
