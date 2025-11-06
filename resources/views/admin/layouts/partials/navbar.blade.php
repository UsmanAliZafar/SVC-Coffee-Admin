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
                <li class="nav-item dropdown me-3 mt-3">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        @if(unread_notifications_count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                {{ unread_notifications_count() }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 350px; max-height: 500px; overflow-y: auto;">
                        <li>
                            <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-bell"></i> Notifications</span>
                                @if(unread_notifications_count() > 0)
                                    <span class="badge bg-primary">{{ unread_notifications_count() }}</span>
                                @endif
                            </h6>
                        </li>

                        @forelse(recent_notifications(5) as $notification)
                            <li>
                                <a class="dropdown-item py-2 {{ $notification->isUnread() ? 'bg-light' : '' }}"
                                href="{{ $notification->action_url ?? route('admin.notifications.index') }}"
                                data-notification-id="{{ $notification->id }}">
                                    <div class="d-flex align-items-start">
                                        <i class="{{ $notification->icon }} text-{{ $notification->color }} me-3 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $notification->title }}</div>
                                            <small class="text-muted d-block">
                                                {{ Str::limit($notification->message, 50) }}
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> {{ $notification->getTimeAgo() }}
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li>
                                <div class="dropdown-item text-center py-4 text-muted">
                                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                    No new notifications
                                </div>
                            </li>
                        @endforelse

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center text-primary fw-semibold"
                            href="{{ route('admin.notifications.index') }}">
                                <i class="bi bi-arrow-right-circle"></i> View all notifications
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- End Notifications -->

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
