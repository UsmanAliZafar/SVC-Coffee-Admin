{{-- resources/views/admin/layouts/partials/navbar.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-brand">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-cup-hot"></i> SVC-Coffee Admin
        </a>

        <!-- Mobile menu button -->
        <button class="navbar-toggler d-md-none" type="button" onclick="toggleMobileSidebar()">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li><h6 class="dropdown-header"><i class="bi bi-bell"></i> Notifications</h6></li>
                        <li><a class="dropdown-item py-2" href="#">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle text-warning me-3"></i>
                                <div>
                                    <div class="fw-semibold">Low stock alert</div>
                                    <small class="text-muted">Coffee beans running low</small>
                                </div>
                            </div>
                        </a></li>
                        <li><a class="dropdown-item py-2" href="#">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cart text-success me-3"></i>
                                <div>
                                    <div class="fw-semibold">New order received</div>
                                    <small class="text-muted">Order #1234 - $125.50</small>
                                </div>
                            </div>
                        </a></li>
                        <li><a class="dropdown-item py-2" href="#">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person text-info me-3"></i>
                                <div>
                                    <div class="fw-semibold">New customer registered</div>
                                    <small class="text-muted">Welcome new customer!</small>
                                </div>
                            </div>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center text-brand fw-semibold" href="#">View all notifications</a></li>
                    </ul>
                </li>

                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <div class="text-start">
                            <div>{{ auth('admin')->user()->name }}</div>
                            <small class="opacity-75">{{ auth('admin')->user()->roles->first()->display_name ?? 'Admin' }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-question-circle me-2"></i> Help</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
