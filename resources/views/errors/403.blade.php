{{-- resources/views/errors/403.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Coffee Admin</title>

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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            width: 100%;
            margin: 20px;
        }

        .error-number {
            font-size: 8rem;
            font-weight: 800;
            color: #dc3545;
            line-height: 1;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .lock-icon {
            font-size: 4rem;
            color: #dc3545;
            animation: shake 1s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .btn-brand {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
            padding: 12px 30px;
            font-weight: 500;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-brand:hover {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(75, 122, 63, 0.3);
        }

        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .text-danger-custom {
            color: #dc3545 !important;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .floating-elements i {
            position: absolute;
            color: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .floating-elements i:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .floating-elements i:nth-child(2) { top: 20%; right: 20%; animation-delay: 1s; }
        .floating-elements i:nth-child(3) { bottom: 20%; left: 20%; animation-delay: 2s; }
        .floating-elements i:nth-child(4) { bottom: 30%; right: 10%; animation-delay: 3s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        .permission-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <i class="bi bi-shield-x" style="font-size: 3rem;"></i>
        <i class="bi bi-lock" style="font-size: 2rem;"></i>
        <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem;"></i>
        <i class="bi bi-person-x" style="font-size: 2rem;"></i>
    </div>

    <div class="error-container">
        <div class="error-card p-5 text-center">
            <div class="mb-4">
                <i class="bi bi-shield-lock lock-icon"></i>
            </div>

            <h1 class="error-number">403</h1>

            <h2 class="text-danger-custom fw-bold mb-3">Access Denied</h2>

            <p class="text-muted mb-4 fs-5">
                You don't have permission to access this resource.
                Your current role doesn't include the required permissions for this action.
            </p>

            @auth('admin')
            <div class="permission-info mb-4 text-start">
                <h6 class="fw-bold text-warning mb-2">
                    <i class="bi bi-info-circle"></i> Your Current Access Level:
                </h6>
                <p class="mb-2">
                    <strong>Name:</strong> {{ auth('admin')->user()->name }}<br>
                    <strong>Email:</strong> {{ auth('admin')->user()->email }}<br>
                    <strong>Role(s):</strong> {{ auth('admin')->user()->roles->pluck('display_name')->join(', ') }}
                </p>
                <small class="text-muted">
                    If you believe this is an error, please contact your system administrator.
                </small>
            </div>
            @endauth

            <div class="row text-start mb-4">
                <div class="col-md-6">
                    <h5 class="text-danger-custom mb-3">
                        <i class="bi bi-exclamation-triangle"></i> What happened?
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Insufficient permissions</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Restricted access area</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Role limitations applied</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="text-danger-custom mb-3">
                        <i class="bi bi-question-circle"></i> What can you do?
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-arrow-right text-danger me-2"></i> Contact administrator</li>
                        <li class="mb-2"><i class="bi bi-arrow-right text-danger me-2"></i> Request access upgrade</li>
                        <li class="mb-2"><i class="bi bi-arrow-right text-danger me-2"></i> Use allowed features only</li>
                    </ul>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                @auth('admin')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-brand">
                    <i class="bi bi-speedometer2"></i> Back to Dashboard
                </a>
                @else
                <a href="{{ route('admin.login') }}" class="btn btn-brand">
                    <i class="bi bi-box-arrow-in-right"></i> Admin Login
                </a>
                @endauth

                <button onclick="history.back()" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-left"></i> Go Back
                </button>
            </div>

            <div class="mt-4 pt-3 border-top">
                <small class="text-muted">
                    Error Code: 403 | Time: {{ now()->format('Y-m-d H:i:s') }}
                    @auth('admin') | User: {{ auth('admin')->user()->email }} @endauth
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
