{{-- resources/views/admin/layouts/partials/navbar.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-brand">
    <div class="container-fluid">
        {{-- Dynamic Brand with Logo --}}
        <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
            {{-- Store Logo --}}
            <img src="{{ store_logo() }}"
                 alt="{{ store_name() }}"
                 class="navbar-brand-logo me-2"
                 style="height: 40px; width: auto; object-fit: contain;">

            {{-- Store Name --}}
            <span class="d-none d-md-inline">{{ store_name() }} Admin</span>
            <span class="d-inline d-md-none">{{ Str::limit(store_name(), 15) }}</span>
        </a>

        <!-- Mobile menu button -->
        <button class="navbar-toggler d-md-none" type="button" onclick="toggleMobileSidebar()">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown me-3 mt-3">
                    <a class="nav-link position-relative"
                       href="#"
                       id="notificationDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       title="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        @if(unread_notifications_count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                {{ unread_notifications_count() > 99 ? '99+' : unread_notifications_count() }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                        style="min-width: 350px; max-height: 500px; overflow-y: auto;">
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
                                   data-notification-id="{{ $notification->id }}"
                                   onclick="markNotificationAsRead('{{ $notification->id }}')">
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

                <!-- Quick Links -->
                <li class="nav-item dropdown me-3 mt-3">
                    <a class="nav-link"
                       href="#"
                       id="quickLinksDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       title="Quick Links">
                        <i class="bi bi-grid-3x3-gap fs-5"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
                        <li><h6 class="dropdown-header"><i class="bi bi-lightning"></i> Quick Actions</h6></li>
                        <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">
                            <i class="bi bi-cart me-2"></i> Orders
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">
                            <i class="bi bi-box me-2"></i> Products
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.customers.index') }}">
                            <i class="bi bi-people me-2"></i> Customers
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a></li>
                    </ul>
                </li>

                <!-- Store Info -->
                <li class="nav-item dropdown me-3 mt-3">
                    <a class="nav-link"
                       href="#"
                       id="storeInfoDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       title="Store Information">
                        <i class="bi bi-info-circle fs-5"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 280px;">
                        <li><h6 class="dropdown-header"><i class="bi bi-shop"></i> Store Info</h6></li>
                        <li class="px-3 py-2">
                            <div class="small">
                                <strong><i class="bi bi-building me-2"></i>Name:</strong><br>
                                <span class="text-muted">{{ store_name() }}</span>
                            </div>
                        </li>
                        <li class="px-3 py-2">
                            <div class="small">
                                <strong><i class="bi bi-envelope me-2"></i>Email:</strong><br>
                                <span class="text-muted">{{ store_email() }}</span>
                            </div>
                        </li>
                        @if(store_phone())
                        <li class="px-3 py-2">
                            <div class="small">
                                <strong><i class="bi bi-telephone me-2"></i>Phone:</strong><br>
                                <span class="text-muted">{{ store_phone() }}</span>
                            </div>
                        </li>
                        @endif
                        <li class="px-3 py-2">
                            <div class="small">
                                <strong><i class="bi bi-currency-exchange me-2"></i>Currency:</strong><br>
                                <span class="text-muted">{{ store_currency() }} ({{ store_currency_symbol() }})</span>
                            </div>
                        </li>
                        <li class="px-3 py-2">
                            <div class="small">
                                <strong><i class="bi bi-clock me-2"></i>Timezone:</strong><br>
                                <span class="text-muted">{{ store_timezone() }}</span>
                            </div>
                        </li>
                        @if(is_store_open_today())
                        <li class="px-3 py-2">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Store Open Today
                            </span>
                        </li>
                        @else
                        <li class="px-3 py-2">
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Store Closed Today
                            </span>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center"
                       href="#"
                       id="navbarDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <div class="text-start">
                            <div>{{ auth('admin')->user()->name }}</div>
                            <small class="opacity-75">{{ auth('admin')->user()->roles->first()->display_name ?? 'Admin' }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                            <i class="bi bi-person me-2"></i> Profile
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a></li>
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

{{-- Notification Mark as Read Script --}}
@push('scripts')
<script>
function markNotificationAsRead(notificationId) {
    fetch(`/admin/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).catch(err => console.error('Error marking notification as read:', err));
}
</script>
@endpush
