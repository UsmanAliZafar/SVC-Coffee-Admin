@extends('admin.layouts.app')

@section('title', 'Edit Admin User')

@section('page_title', 'Edit Admin User')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin Users</a></li>
            <li class="breadcrumb-item active">Edit User</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #5B914C; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil me-2"></i>Edit Admin User: {{ $user->name }}
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" id="editUserForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-person me-2"></i>Personal Information
                                </h6>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           placeholder="Enter full name"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           placeholder="Enter email address"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('username') is-invalid @enderror"
                                           id="username"
                                           name="username"
                                           value="{{ old('username', $user->username) }}"
                                           placeholder="Enter username"
                                           required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="Enter phone number">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- User Info -->
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-muted mb-2">Account Information</h6>
                                        <small class="text-muted">
                                            <strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i A') }}<br>
                                            <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i A') }}<br>
                                            @if($user->last_login_at)
                                                <strong>Last Login:</strong> {{ $user->last_login_at->format('M d, Y H:i A') }}
                                            @else
                                                <strong>Last Login:</strong> Never logged in
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Security & Access -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-shield-lock me-2"></i>Security & Access
                                </h6>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="password"
                                               name="password"
                                               placeholder="Leave blank to keep current password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Minimum 8 characters required</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           placeholder="Confirm new password">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Assign Roles <span class="text-danger">*</span></label>
                                    <div class="border rounded p-3 @error('roles') border-danger @enderror" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="roles[]"
                                                   value="{{ $role->id }}"
                                                   id="role_{{ $role->id }}"
                                                   {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                <strong>{{ $role->display_name }}</strong>
                                                @if($role->description)
                                                    <br><small class="text-muted">{{ $role->description }}</small>
                                                @endif
                                            </label>
                                        </div>
                                        @if(!$loop->last)<hr class="my-2">@endif
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="is_active"
                                               id="is_active"
                                               value="1"
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Account Active
                                            <small class="text-muted d-block">Enable this account for login</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>Back to Users
                                        </a>
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info ms-2">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Changes
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>Update User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-check-input:checked {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .form-check-input:focus {
        border-color: #5B914C;
        box-shadow: 0 0 0 0.25rem rgba(91, 145, 76, 0.25);
    }

    .btn-success {
        background-color: #5B914C;
        border-color: #5B914C;
    }

    .btn-success:hover {
        background-color: #4a7a3f;
        border-color: #4a7a3f;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Form validation
    $('#editUserForm').submit(function(e) {
        let isValid = true;

        // Check if at least one role is selected
        if ($('input[name="roles[]"]:checked').length === 0) {
            e.preventDefault();
            isValid = false;

            showAlert('error', 'Please select at least one role for the user.');

            // Scroll to roles section
            $('html, body').animate({
                scrollTop: $('label:contains("Assign Roles")').offset().top - 100
            }, 500);
        }

        // Password confirmation check (only if password is entered)
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();

        if (password !== '' && password !== confirmPassword) {
            e.preventDefault();
            isValid = false;

            $('#password_confirmation').addClass('is-invalid');
            if ($('#password_confirmation').next('.invalid-feedback').length === 0) {
                $('#password_confirmation').after('<div class="invalid-feedback">Passwords do not match.</div>');
            }

            showAlert('error', 'Password confirmation does not match.');
        } else {
            $('#password_confirmation').removeClass('is-invalid');
            $('#password_confirmation').next('.invalid-feedback').remove();
        }

        return isValid;
    });

    // Clear password confirmation when password is cleared
    $('#password').on('input', function() {
        if ($(this).val() === '') {
            $('#password_confirmation').val('');
        }
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertIcon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi bi-${alertIcon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert alert at the top of the card body
        $('.card-body').prepend(alertHtml);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
