@extends('admin.layouts.app')

@section('title', 'View Newsletter Subscriber')

@push('styles')
<style>
    .detail-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
    }
    .detail-section-title {
        color: #5B914C;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .detail-row {
        padding: 15px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    .detail-value {
        color: #212529;
        font-size: 1rem;
    }
    .subscriber-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        font-size: 2.5rem;
        font-weight: bold;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 4px 15px rgba(91, 145, 76, 0.3);
    }
    .status-badge-large {
        font-size: 1.1rem;
        padding: 8px 20px;
    }
    .btn-action {
        min-width: 150px;
    }
    .btn-subscribe {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-subscribe:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
        color: white;
    }
    .info-box {
        background-color: #e7f3e3;
        border-left: 4px solid #5B914C;
        padding: 15px;
        border-radius: 5px;
    }
    .info-box i {
        color: #5B914C;
    }
    .timeline-item {
        padding-left: 30px;
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 30px;
        bottom: 0;
        width: 2px;
        background-color: #dee2e6;
    }
    .timeline-item:last-child::before {
        display: none;
    }
    .timeline-icon {
        position: absolute;
        left: 0;
        top: 5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #5B914C;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
    .copy-btn {
        cursor: pointer;
        color: #6c757d;
        transition: color 0.2s;
    }
    .copy-btn:hover {
        color: #5B914C;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-person-circle"></i> Subscriber Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.newsletters.index') }}">Newsletter</a></li>
                    <li class="breadcrumb-item active">View Subscriber</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('newsletters.update'))
            <a href="{{ route('admin.newsletters.edit', $newsletter->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Edit Subscriber
            </a>
            @endif
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Main Details Column --}}
        <div class="col-lg-8">
            {{-- Subscriber Information Card --}}
            <div class="card detail-card mb-4">
                <div class="card-body p-4">
                    <h5 class="detail-section-title">
                        <i class="bi bi-person-badge me-2"></i>Subscriber Information
                    </h5>

                    {{-- Avatar --}}
                    <div class="text-center mb-4">
                        <div class="subscriber-avatar">
                            {{ strtoupper(substr($newsletter->email, 0, 1)) }}
                        </div>
                        <h4 class="mb-1">{{ $newsletter->name ?? 'Anonymous Subscriber' }}</h4>
                        <p class="text-muted mb-2">
                            <i class="bi bi-envelope me-1"></i>
                            {{ $newsletter->email }}
                            <i class="bi bi-clipboard copy-btn ms-2"
                               onclick="copyToClipboard('{{ $newsletter->email }}')"
                               title="Copy email"
                               data-bs-toggle="tooltip"></i>
                        </p>
                        @if($newsletter->is_subscribed)
                            <span class="badge status-badge-large bg-success">
                                <i class="bi bi-check-circle me-1"></i>Active Subscriber
                            </span>
                        @else
                            <span class="badge status-badge-large bg-danger">
                                <i class="bi bi-x-circle me-1"></i>Unsubscribed
                            </span>
                        @endif
                    </div>

                    <hr class="my-4">

                    {{-- Details Grid --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-hash me-1"></i>Subscriber ID
                                </div>
                                <div class="detail-value">
                                    <code>{{ $newsletter->id }}</code>
                                    <i class="bi bi-clipboard copy-btn ms-2"
                                       onclick="copyToClipboard('{{ $newsletter->id }}')"
                                       title="Copy ID"
                                       data-bs-toggle="tooltip"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-person me-1"></i>Full Name
                                </div>
                                <div class="detail-value">
                                    {{ $newsletter->name ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-envelope me-1"></i>Email Address
                                </div>
                                <div class="detail-value">
                                    <a href="mailto:{{ $newsletter->email }}">{{ $newsletter->email }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-toggle-on me-1"></i>Subscription Status
                                </div>
                                <div class="detail-value">
                                    @if($newsletter->is_subscribed)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Subscribed
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Unsubscribed
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subscription Timeline Card --}}
            <div class="card detail-card mb-4">
                <div class="card-body p-4">
                    <h5 class="detail-section-title">
                        <i class="bi bi-clock-history me-2"></i>Subscription Timeline
                    </h5>

                    <div class="timeline">
                        {{-- Created --}}
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="bi bi-plus"></i>
                            </div>
                            <div>
                                <strong>Subscriber Added</strong>
                                <p class="text-muted mb-0">
                                    {{ $newsletter->created_at->format('F d, Y \a\t h:i A') }}
                                    <small>({{ $newsletter->created_at->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>

                        {{-- Subscribed --}}
                        @if($newsletter->subscribed_at)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success">
                                <i class="bi bi-check"></i>
                            </div>
                            <div>
                                <strong>Subscribed</strong>
                                <p class="text-muted mb-0">
                                    {{ $newsletter->subscribed_at->format('F d, Y \a\t h:i A') }}
                                    <small>({{ $newsletter->subscribed_at->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Unsubscribed --}}
                        @if($newsletter->unsubscribed_at)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-danger">
                                <i class="bi bi-x"></i>
                            </div>
                            <div>
                                <strong>Unsubscribed</strong>
                                <p class="text-muted mb-0">
                                    {{ $newsletter->unsubscribed_at->format('F d, Y \a\t h:i A') }}
                                    <small>({{ $newsletter->unsubscribed_at->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Updated --}}
                        @if($newsletter->updated_at && !$newsletter->updated_at->eq($newsletter->created_at))
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="bi bi-pencil"></i>
                            </div>
                            <div>
                                <strong>Last Updated</strong>
                                <p class="text-muted mb-0">
                                    {{ $newsletter->updated_at->format('F d, Y \a\t h:i A') }}
                                    <small>({{ $newsletter->updated_at->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Additional Details Card --}}
            <div class="card detail-card">
                <div class="card-body p-4">
                    <h5 class="detail-section-title">
                        <i class="bi bi-info-circle me-2"></i>Additional Details
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-calendar-check me-1"></i>Subscribed Date
                                </div>
                                <div class="detail-value">
                                    @if($newsletter->subscribed_at)
                                        {{ $newsletter->subscribed_at->format('F d, Y \a\t h:i A') }}
                                    @else
                                        <span class="text-muted">Never subscribed</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-calendar-x me-1"></i>Unsubscribed Date
                                </div>
                                <div class="detail-value">
                                    @if($newsletter->unsubscribed_at)
                                        {{ $newsletter->unsubscribed_at->format('F d, Y \a\t h:i A') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-calendar-plus me-1"></i>Created At
                                </div>
                                <div class="detail-value">
                                    {{ $newsletter->created_at->format('F d, Y \a\t h:i A') }}
                                    <br>
                                    <small class="text-muted">{{ $newsletter->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-calendar-event me-1"></i>Last Updated
                                </div>
                                <div class="detail-value">
                                    @if($newsletter->updated_at && !$newsletter->updated_at->eq($newsletter->created_at))
                                        {{ $newsletter->updated_at->format('F d, Y \a\t h:i A') }}
                                        <br>
                                        <small class="text-muted">{{ $newsletter->updated_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Never updated</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="bi bi-clock me-1"></i>Subscription Duration
                                </div>
                                <div class="detail-value">
                                    @if($newsletter->subscribed_at)
                                        @if($newsletter->is_subscribed)
                                            <span class="badge bg-success">
                                                Active for {{ $newsletter->subscribed_at->diffForHumans(null, true) }}
                                            </span>
                                        @else
                                            @if($newsletter->unsubscribed_at)
                                                <span class="badge bg-secondary">
                                                    Was subscribed for {{ $newsletter->subscribed_at->diffForHumans($newsletter->unsubscribed_at, true) }}
                                                </span>
                                            @else
                                                <span class="text-muted">Duration unknown</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted">Never subscribed</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions & Info Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Actions Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-charge me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    @if(auth('admin')->user()->hasPermission('newsletters.update'))
                        @if($newsletter->is_subscribed)
                            <button type="button" class="btn btn-warning btn-action w-100 mb-2" id="unsubscribeBtn">
                                <i class="bi bi-x-circle me-2"></i>Unsubscribe
                            </button>
                        @else
                            <button type="button" class="btn btn-subscribe btn-action w-100 mb-2" id="subscribeBtn">
                                <i class="bi bi-check-circle me-2"></i>Subscribe
                            </button>
                        @endif
                        <a href="{{ route('admin.newsletters.edit', $newsletter->id) }}" class="btn btn-primary btn-action w-100 mb-2">
                            <i class="bi bi-pencil me-2"></i>Edit Details
                        </a>
                    @endif

                    <a href="mailto:{{ $newsletter->email }}" class="btn btn-outline-secondary btn-action w-100 mb-2">
                        <i class="bi bi-envelope me-2"></i>Send Email
                    </a>

                    @if(auth('admin')->user()->hasPermission('newsletters.delete'))
                        <hr>
                        <button type="button" class="btn btn-outline-danger btn-action w-100" id="deleteBtn">
                            <i class="bi bi-trash me-2"></i>Delete Subscriber
                        </button>
                    @endif
                </div>
            </div>

            {{-- Status Information Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Status Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Current Status:</span>
                            @if($newsletter->is_subscribed)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Member Since:</span>
                            <strong>{{ $newsletter->created_at->format('M d, Y') }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Total Days:</span>
                            <strong>{{ $newsletter->created_at->diffInDays(now()) }} days</strong>
                        </div>
                    </div>

                    @if($newsletter->is_subscribed && $newsletter->subscribed_at)
                    <div class="info-box">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <small>Subscribed {{ $newsletter->subscribed_at->diffForHumans() }}</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- System Information Card --}}
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-gear me-2"></i>System Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Record ID:</span>
                            <code class="small">{{ $newsletter->id }}</code>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created:</span>
                            <span>{{ $newsletter->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Last Modified:</span>
                            <span>
                                @if($newsletter->updated_at && !$newsletter->updated_at->eq($newsletter->created_at))
                                    {{ $newsletter->updated_at->format('M d, Y') }}
                                @else
                                    Never
                                @endif
                            </span>
                        </div>
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
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Subscribe button
    $('#subscribeBtn').on('click', function() {
        Swal.fire({
            title: 'Subscribe this user?',
            text: 'This will activate their newsletter subscription',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5B914C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, subscribe!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.newsletters.subscribe", $newsletter->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Subscribed!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while subscribing',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Unsubscribe button
    $('#unsubscribeBtn').on('click', function() {
        Swal.fire({
            title: 'Unsubscribe this user?',
            text: 'They will no longer receive newsletters',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unsubscribe!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.newsletters.unsubscribe", $newsletter->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Unsubscribed!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while unsubscribing',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Delete button
    $('#deleteBtn').on('click', function() {
        Swal.fire({
            title: 'Delete this subscriber?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.newsletters.destroy", $newsletter->id) }}',
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '{{ route("admin.newsletters.index") }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });
});

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show tooltip feedback
        const tooltip = new bootstrap.Tooltip(event.target, {
            title: 'Copied!',
            trigger: 'manual'
        });
        tooltip.show();
        setTimeout(() => {
            tooltip.dispose();
        }, 1500);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endpush
