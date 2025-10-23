@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Profile</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Profile</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <div class="avatar-circle">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $admin->name }}</h5>
                    <p class="text-muted mb-2">{{ '@' . $admin->username }}</p>
                    <p class="small text-muted mb-3">{{ $admin->email }}</p>

                    <div class="border-top pt-3">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <div class="small text-muted">Roles</div>
                                <strong>{{ $admin->roles->count() }}</strong>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Status</div>
                                <strong class="text-success">Active</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        @foreach($admin->roles as $role)
                        <span class="badge bg-primary me-1 mb-1">{{ $role->display_name }}</span>
                        @endforeach
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">
                            <i class="bi bi-calendar3"></i>
                            Joined {{ $admin->created_at->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile & Password Forms -->
        <div class="col-lg-8">
            <!-- Update Profile Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge me-2"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Full Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $admin->name) }}"
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('username') is-invalid @enderror"
                                       id="username"
                                       name="username"
                                       value="{{ old('username', $admin->username) }}"
                                       required>
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email (Read-only) -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       value="{{ $admin->email }}"
                                       disabled
                                       readonly>
                                <div class="form-text">Email cannot be changed</div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $admin->phone) }}"
                                       placeholder="+1234567890">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('password_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('password_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('password_error') && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Error updating password:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.profile.update-password') }}" method="POST" id="passwordForm">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       id="current_password"
                                       name="current_password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="bi bi-eye" id="current_password_icon"></i>
                                </button>
                                @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('new_password') is-invalid @enderror"
                                       id="new_password"
                                       name="new_password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="bi bi-eye" id="new_password_icon"></i>
                                </button>
                                @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimum 8 characters, must be different from current password</div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="new_password_confirmation"
                                       name="new_password_confirmation"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                    <i class="bi bi-eye" id="new_password_confirmation_icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetPasswordForm()">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-shield-check me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar .avatar-circle {
    width: 120px;
    height: 120px;
    margin: 0 auto;
    border-radius: 50%;
    background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 60px;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-bottom: 2px solid #f0f0f0;
    padding: 1.25rem;
    border-radius: 12px 12px 0 0 !important;
}

.btn-primary {
    background-color: #5B914C;
    border-color: #5B914C;
}

.btn-primary:hover {
    background-color: #4a7a3d;
    border-color: #4a7a3d;
}

.badge.bg-primary {
    background-color: #5B914C !important;
}

.form-control:focus {
    border-color: #5B914C;
    box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
}

.alert {
    border-radius: 8px;
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function resetPasswordForm() {
    document.getElementById('passwordForm').reset();

    // Reset all password fields to password type
    ['current_password', 'new_password', 'new_password_confirmation'].forEach(id => {
        const input = document.getElementById(id);
        const icon = document.getElementById(id + '_icon');
        if (input.type === 'text') {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>
@endsection
