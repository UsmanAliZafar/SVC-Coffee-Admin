{{-- resources/views/admin/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login -SVC Coffee Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --brand-primary: #5B914C;
            --brand-primary-rgb: 91, 145, 76;
            --brand-primary-dark: #4A7A3F;
            --brand-primary-light: #6BA055;
        }

        body {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 600px; /* Increased from 400px */
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .login-card {
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 3.5rem 3rem; /* Increased padding */
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .btn-brand {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
            padding: 16px 24px; /* Increased padding */
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 14px;
            transition: all 0.3s ease;
        }

        .btn-brand:hover {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(75, 122, 63, 0.3);
        }

        .form-control {
            border-radius: 14px;
            padding: 16px 20px; /* Increased padding */
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 1rem;
            height: auto;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.25rem rgba(91, 145, 76, 0.25);
        }

        .coffee-icon {
            color: var(--brand-primary);
            font-size: 4rem; /* Increased icon size */
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 8px rgba(91, 145, 76, 0.3);
        }

        .alert {
            border-radius: 14px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 2rem;
        }

        .form-check-input:checked {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
        }

        .text-brand {
            color: var(--brand-primary) !important;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #495057;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 2rem; /* Increased spacing between form groups */
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--brand-primary);
        }

        .header-section {
            text-align: center;
            margin-bottom: 3rem; /* Increased margin */
        }

        .header-section h2 {
            font-size: 2.5rem; /* Increased font size */
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-section p {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .default-credentials {
            margin-top: 2.5rem;
            padding: 1.5rem;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border-radius: 16px;
            border-left: 4px solid var(--brand-primary);
        }

        .form-check {
            margin-bottom: 2rem;
            padding-left: 1.5rem;
        }

        .form-check-label {
            font-weight: 500;
            cursor: pointer;
        }

        .forgot-password-link {
            margin-top: 1.5rem;
        }

        .forgot-password-link a {
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-password-link a:hover {
            text-decoration: underline;
            color: var(--brand-primary-dark) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-container {
                max-width: 500px;
                padding: 15px;
            }

            .login-card {
                padding: 2.5rem 2rem;
            }

            .header-section h2 {
                font-size: 2rem;
            }

            .coffee-icon {
                font-size: 3.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                padding: 10px;
            }

            .login-card {
                padding: 2rem 1.5rem;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="login-container">
            <div class="login-card">
                <div class="header-section">
                    <i class="bi bi-cup-hot coffee-icon"></i>
                    <h2 class="text-brand fw-bold mb-1">SVC Coffee Admin</h2>
                    <p class="text-muted">Sign in to your admin account</p>
                </div>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <div class="position-relative">
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                                style="padding-right: 50px;"
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword()"
                            >
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me for 30 days
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-brand btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard
                        </button>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-center forgot-password-link">
                        <a href="{{ route('admin.forgot-password') }}" class="text-brand">
                            <i class="bi bi-question-circle me-1"></i>Forgot your password?
                        </a>
                    </div>
                </form>

                <!-- Default Login Credentials (Remove in production) -->
                {{-- <div class="default-credentials">
                    <h6 class="text-brand fw-bold mb-3">
                        <i class="bi bi-info-circle me-2"></i>Default Login Credentials
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <strong class="text-primary">Super Admin:</strong><br>
                            <small class="text-muted">admin@coffee.com<br>password123</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong class="text-info">Manager:</strong><br>
                            <small class="text-muted">manager@coffee.com<br>password123</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong class="text-success">Staff:</strong><br>
                            <small class="text-muted">staff@coffee.com<br>password123</small>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Form validation feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Signing In...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
