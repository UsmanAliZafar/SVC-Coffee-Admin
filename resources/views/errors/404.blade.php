{{-- resources/views/errors/404.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Coffee Admin</title>

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
            color: var(--brand-primary);
            line-height: 1;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .coffee-cup {
            font-size: 4rem;
            color: var(--brand-primary);
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
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

        .btn-outline-brand {
            color: var(--brand-primary);
            border-color: var(--brand-primary);
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-brand:hover {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
        }

        .text-brand {
            color: var(--brand-primary) !important;
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
    </style>
</head>
<body>
    <div class="floating-elements">
        <i class="bi bi-cup-hot" style="font-size: 3rem;"></i>
        <i class="bi bi-gear" style="font-size: 2rem;"></i>
        <i class="bi bi-box" style="font-size: 2.5rem;"></i>
        <i class="bi bi-receipt" style="font-size: 2rem;"></i>
    </div>

    <div class="error-container">
        <div class="error-card p-5 text-center">
            <div class="mb-4">
                <i class="bi bi-cup-hot coffee-cup"></i>
            </div>

            <h1 class="error-number">404</h1>

            <h2 class="text-brand fw-bold mb-3">Oops! Page Not Found</h2>

            <p class="text-muted mb-4 fs-5">
                Looks like this page took a coffee break and never came back!
                The page you're looking for doesn't exist or may have been moved.
            </p>

            <div class="row text-start mb-4">
                <div class="col-md-6">
                    <h5 class="text-brand mb-3">
                        <i class="bi bi-lightbulb"></i> What you can do:
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-arrow-right text-brand me-2"></i> Check the URL spelling</li>
                        <li class="mb-2"><i class="bi bi-arrow-right text-brand me-2"></i> Go back to the homepage</li>
                        <li class="mb-2"><i class="bi bi-arrow-right text-brand me-2"></i> Try using the navigation menu</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="text-brand mb-3">
                        <i class="bi bi-question-circle"></i> Need help?
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-arrow-right text-brand me-2"></i> Contact system administrator</li>
                        <li class="mb-2"><i class="bi bi-arrow-right text-brand me-2"></i> Check your permissions</li>
                        <li class="mb-2"><i class="bi bi-arrow-right text-brand me-2"></i> Report this issue</li>
                    </ul>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ url('/') }}" class="btn btn-brand">
                    <i class="bi bi-house"></i> Go Home
                </a>

                @auth('admin')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-brand">
                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                </a>
                @else
                <a href="{{ route('admin.login') }}" class="btn btn-outline-brand">
                    <i class="bi bi-box-arrow-in-right"></i> Admin Login
                </a>
                @endauth

                <button onclick="history.back()" class="btn btn-outline-brand">
                    <i class="bi bi-arrow-left"></i> Go Back
                </button>
            </div>

            <div class="mt-4 pt-3 border-top">
                <small class="text-muted">
                    Error Code: 404 | Time: {{ now()->format('Y-m-d H:i:s') }}
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
