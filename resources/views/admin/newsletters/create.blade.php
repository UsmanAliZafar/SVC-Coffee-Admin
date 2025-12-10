@extends('admin.layouts.app')

@section('title', 'Add Newsletter Subscriber')

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
    .btn-submit {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
        padding: 10px 30px;
        font-weight: 600;
    }
    .btn-submit:hover {
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-person-plus-fill"></i> Add Newsletter Subscriber</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.newsletters.index') }}">Newsletter</a></li>
                    <li class="breadcrumb-item active">Add Subscriber</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="info-box">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Note:</strong> Add a new subscriber to your newsletter mailing list. You can manually add subscribers or import them in bulk.
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.newsletters.store') }}" method="POST" id="createNewsletterForm">
                        @csrf

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
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Enter subscriber email"
                                       required
                                       autofocus>
                                <div class="form-text">
                                    <i class="bi bi-envelope me-1"></i>A valid email address is required
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Name --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Full Name
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Enter subscriber name (optional)"
                                       maxlength="255">
                                <div class="form-text">
                                    <i class="bi bi-person me-1"></i>Optional: Subscriber's full name for personalization
                                </div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Subscription Status Section --}}
                        <div class="mb-4">
                            <h5 class="form-section-title">
                                <i class="bi bi-check-circle me-2"></i>Subscription Status
                            </h5>

                            {{-- Is Subscribed --}}
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="is_subscribed"
                                       name="is_subscribed"
                                       value="1"
                                       {{ old('is_subscribed', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_subscribed">
                                    <strong>Active Subscription</strong>
                                </label>
                            </div>
                            <div class="form-text ms-5 mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Enable this if the subscriber should receive newsletters immediately
                            </div>
                        </div>

                        {{-- Additional Information --}}
                        <div class="alert alert-light border">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb me-2"></i>Additional Information
                            </h6>
                            <ul class="mb-0 small">
                                <li>Subscribers will be added with the current timestamp</li>
                                <li>If subscription is active, the "Subscribed At" date will be set automatically</li>
                                <li>You can change the subscription status anytime from the subscriber list</li>
                                <li>Duplicate email addresses are not allowed</li>
                            </ul>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                                </button>
                                <button type="submit" class="btn btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>Add Subscriber
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar Tips --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h6 class="mb-0">
                        <i class="bi bi-question-circle me-2"></i>Quick Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="bi bi-envelope-check me-2"></i>Email Validation
                        </h6>
                        <p class="small text-muted mb-0">
                            Ensure the email address is valid and not already in the system. Duplicate emails will be rejected.
                        </p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="bi bi-toggle-on me-2"></i>Active Status
                        </h6>
                        <p class="small text-muted mb-0">
                            Keep the subscription toggle ON if you want the subscriber to receive newsletters immediately.
                        </p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="bi bi-person-badge me-2"></i>Name Field
                        </h6>
                        <p class="small text-muted mb-0">
                            Adding a name helps personalize emails but is completely optional.
                        </p>
                    </div>
                    <hr>
                    <div>
                        <h6 class="text-success">
                            <i class="bi bi-shield-check me-2"></i>Privacy Notice
                        </h6>
                        <p class="small text-muted mb-0">
                            Make sure you have consent to add subscribers to your mailing list according to privacy regulations.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Statistics Card --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Current Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Subscribers:</span>
                        <strong id="totalStat">Loading...</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active:</span>
                        <strong class="text-success" id="activeStat">Loading...</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Unsubscribed:</span>
                        <strong class="text-danger" id="unsubscribedStat">Loading...</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load statistics
    loadStatistics();

    // Form validation
    $('#createNewsletterForm').on('submit', function(e) {
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
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
    });

    // Email input validation feedback
    $('#email').on('blur', function() {
        const email = $(this).val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Please enter a valid email address</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Reset form
    $('button[type="reset"]').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Reset Form?',
            text: 'All entered data will be cleared',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reset it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('createNewsletterForm').reset();
                $('#is_subscribed').prop('checked', true);
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                showNotification('Form has been reset', 'info');
            }
        });
    });

    // Load statistics from API
    function loadStatistics() {
        $.ajax({
            url: '{{ route("admin.newsletters.statistics") }}',
            method: 'GET',
            success: function(stats) {
                $('#totalStat').text(stats.total || 0);
                $('#activeStat').text(stats.subscribed || 0);
                $('#unsubscribedStat').text(stats.unsubscribed || 0);
            },
            error: function() {
                $('#totalStat').text('N/A');
                $('#activeStat').text('N/A');
                $('#unsubscribedStat').text('N/A');
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
