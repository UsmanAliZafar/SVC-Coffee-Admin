{{-- resources/views/admin/auth/forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - Coffee Admin</title>

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
        }

        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }

        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-brand {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
            padding: 12px;
            font-weight: 500;
        }

        .btn-brand:hover {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(75, 122, 63, 0.3);
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.2rem rgba(91, 145, 76, 0.25);
        }

        .coffee-icon {
            color: var(--brand-primary);
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .text-brand {
            color: var(--brand-primary) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-key coffee-icon"></i>
                    <h2 class="text-brand fw-bold mb-1">Forgot Password</h2>
                    <p class="text-muted">Enter your email to reset your password</p>
                </div>

                <!-- Flash Messages -->
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.email') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope"></i> Email Address
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

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-brand btn-lg">
                            <i class="bi bi-envelope"></i> Send Reset Link
                        </button>
                    </div>

                    <!-- Back to Login Link -->
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.login') }}" class="text-brand text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
