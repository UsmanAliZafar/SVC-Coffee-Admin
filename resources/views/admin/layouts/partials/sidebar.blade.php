{{-- resources/views/admin/layouts/partials/sidebar.blade.php --}}
<nav class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            {{-- Products Management --}}
            @if(auth('admin')->user()->hasPermission('products.read'))
                <li class="nav-item has-dropdown">
                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                       href="{{ route('admin.products.index') }}"
                       data-tooltip="Products">
                        <i class="bi bi-box"></i>
                        <span class="nav-text">Products</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('products.create'))
                        <li><a href="#"><i class="bi bi-plus-square"></i> Add Product</a></li>
                        @endif
                        @if(auth('admin')->user()->hasPermission('products.read'))
                        <li><a href="#"><i class="bi bi-exclamation-triangle text-warning"></i> Low Stock</a></li>
                        <li><a href="#"><i class="bi bi-eye-slash text-danger"></i> Inactive</a></li>
                        <li><a href="#"><i class="bi bi-star text-success"></i> Featured</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Categories Management --}}
            @if(auth('admin')->user()->hasPermission('categories.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.categories.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                    href="{{ route('admin.categories.index') }}"
                    data-tooltip="Categories">
                        <i class="bi bi-tags"></i>
                        <span class="nav-text">Categories</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('categories.read'))
                        <li>
                            <a href="{{ route('admin.categories.index') }}"
                            class="{{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
                                <i class="bi bi-list-ul"></i> All Categories
                            </a>
                        </li>
                        @endif

                        @if(auth('admin')->user()->hasPermission('categories.create'))
                        <li>
                            <a href="{{ route('admin.categories.create') }}"
                            class="{{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">
                                <i class="bi bi-tag-fill"></i> Add Category
                            </a>
                        </li>
                        @endif

                        @if(auth('admin')->user()->hasPermission('categories.read'))
                        <li>
                            <a href="{{ route('admin.categories.tree') }}"
                            class="{{ request()->routeIs('admin.categories.tree') ? 'active' : '' }}">
                                <i class="bi bi-diagram-3"></i> Category Tree
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.empty') }}"
                            class="{{ request()->routeIs('admin.categories.empty') ? 'active' : '' }}">
                                <i class="bi bi-folder-x text-muted"></i> Empty Categories
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
            @endif
            {{-- Categories Management --}}

            {{-- Inventory Management --}}
            @if(auth('admin')->user()->hasPermission('inventory.read'))
                <li class="nav-item has-dropdown">
                    <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
                       href="#"
                       data-tooltip="Inventory">
                        <i class="bi bi-boxes"></i>
                        <span class="nav-text">Inventory</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('inventory.update'))
                        <li><a href="#"><i class="bi bi-plus-slash-minus"></i> Adjust Stock</a></li>
                        @endif
                        @if(auth('admin')->user()->hasPermission('inventory.read'))
                        <li><a href="#"><i class="bi bi-arrow-left-right"></i> Movement</a></li>
                        <li><a href="#"><i class="bi bi-arrow-repeat"></i> Warehouse Sync</a></li>
                        <li><a href="#"><i class="bi bi-exclamation-triangle text-warning"></i> Low Stock</a></li>
                        <li><a href="#"><i class="bi bi-x-circle text-danger"></i> Out of Stock</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Orders Management --}}
            @if(auth('admin')->user()->hasPermission('orders.read'))
                <li class="nav-item has-dropdown">
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                       href="#"
                       data-tooltip="Orders">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text">Orders</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('orders.create'))
                        <li><a href="#"><i class="bi bi-plus-square"></i> Create Order</a></li>
                        @endif
                        @if(auth('admin')->user()->hasPermission('orders.read'))
                        <li><a href="#"><i class="bi bi-clock text-warning"></i> Pending</a></li>
                        <li><a href="#"><i class="bi bi-hourglass-split text-info"></i> Processing</a></li>
                        <li><a href="#"><i class="bi bi-truck text-primary"></i> Shipped</a></li>
                        <li><a href="#"><i class="bi bi-file-earmark-text"></i> Invoices</a></li>
                        <li><a href="#"><i class="bi bi-arrow-counterclockwise"></i> Refunds</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Customers Management --}}
            @if(auth('admin')->user()->hasPermission('customers.read'))
                <li class="nav-item has-dropdown">
                    <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                       href="#"
                       data-tooltip="Customers">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">Customers</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('customers.create'))
                        <li><a href="#"><i class="bi bi-person-plus"></i> Add Customer</a></li>
                        @endif
                        @if(auth('admin')->user()->hasPermission('customers.read'))
                        <li><a href="#"><i class="bi bi-collection"></i> Groups</a></li>
                        <li><a href="#"><i class="bi bi-person-check text-success"></i> New</a></li>
                        <li><a href="#"><i class="bi bi-trophy text-warning"></i> Top Customers</a></li>
                        <li><a href="#"><i class="bi bi-envelope"></i> Email Templates</a></li>
                        <li><a href="#"><i class="bi bi-newspaper"></i> Newsletter</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Coffee Categories Section --}}
            <li class="nav-item section-divider">
                <span class="section-title">Top Categories</span>
            </li>

            {{-- <li class="nav-item">
                <span class="nav-text">Some Categories will be here</span>
            </li> --}}
            {{-- System Section --}}
            <li class="nav-item section-divider">
                <span class="section-title">System</span>
            </li>

            @if(auth('admin')->user()->hasPermission('reports.read'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                   href="#" data-tooltip="Reports">
                    <i class="bi bi-graph-up"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            @endif

            {{-- Admin Users Management --}}
            @if(auth('admin')->user()->hasPermission('admin_users.read') || auth('admin')->user()->hasPermission('roles.read') || auth('admin')->user()->hasRole('super_admin'))
            <li class="nav-item has-dropdown">
                <a class="nav-link {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
                   href="{{ route('admin.users.index') }}"
                   data-tooltip="Admin Users">
                    <i class="bi bi-person-gear"></i>
                    <span class="nav-text">Admin Users</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </a>
                <ul class="submenu">
                    @if(auth('admin')->user()->hasPermission('admin_users.create'))
                    <li><a href="{{ route('admin.users.create') }}"><i class="bi bi-person-plus"></i> Add User</a></li>
                    @endif
                    @if(auth('admin')->user()->hasPermission('roles.read'))
                    <li><a href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-check"></i> Roles</a></li>
                    @endif
                    @if(auth('admin')->user()->hasPermission('roles.create'))
                    <li><a href="{{ route('admin.roles.create') }}"><i class="bi bi-shield-plus"></i> Add Role</a></li>
                    @endif
                    @if(auth('admin')->user()->hasRole('super_admin'))
                    <li><a href="{{ route('admin.permissions.index') }}"><i class="bi bi-key"></i> Permissions</a></li>
                    @endif
                    <li><a href=""><i class="bi bi-person-circle"></i> My Profile</a></li>
                </ul>
            </li>
            @endif

            @if(auth('admin')->user()->hasPermission('settings.read'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                   href="#" data-tooltip="Settings">
                    <i class="bi bi-sliders"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

    {{-- Sidebar Toggle Button --}}
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="bi bi-chevron-left" id="toggle-icon"></i>
    </button>
</nav>

{{-- Mobile Overlay --}}
<div class="mobile-overlay" id="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<style>
/* Compact Sidebar Styles */
.sidebar .submenu {
    display: none;
    list-style: none;
    padding: 0;
    margin: 0;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.sidebar .submenu li {
    margin: 0;
}

.sidebar .submenu a {
    display: flex;
    align-items: center;
    padding: 8px 15px 8px 45px;
    color: rgb(16 16 16 / 80%);
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}

.sidebar .submenu a:hover {
    background: rgba(91, 145, 76, 0.3);
    color: #fff;
    padding-left: 50px;
}

.sidebar .submenu a i {
    margin-right: 8px;
    font-size: 14px;
    width: 16px;
}

.sidebar .has-dropdown > .nav-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.sidebar .dropdown-arrow {
    font-size: 12px;
    transition: transform 0.3s;
    margin-left: auto;
}

.sidebar .has-dropdown.open > .nav-link .dropdown-arrow {
    transform: rotate(180deg);
}

.sidebar .has-dropdown.open .submenu {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 500px;
    }
}

.sidebar .nav-item {
    margin-bottom: 4px;
}

.sidebar .nav-link {
    padding: 10px 15px;
    border-radius: 8px;
    transition: all 0.2s;
}

.sidebar .section-divider {
    margin: 15px 0 10px;
    padding: 0;
}

.sidebar .section-title {
    display: block;
    padding: 5px 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: rgba(40 37 37 / 50%);
    letter-spacing: 1px;
}
</style>

<script>
// Compact Sidebar Dropdown Toggle
document.addEventListener('DOMContentLoaded', function() {
    const dropdownItems = document.querySelectorAll('.sidebar .has-dropdown');

    dropdownItems.forEach(item => {
        const link = item.querySelector('.nav-link');

        link.addEventListener('click', function(e) {
            // If clicking the main link, navigate to it
            if (e.target === link || e.target.classList.contains('nav-text') || e.target.parentElement === link) {
                // Only prevent default if clicking the arrow
                const clickedArrow = e.target.classList.contains('dropdown-arrow') ||
                                   e.target.classList.contains('bi-chevron-down');

                if (clickedArrow) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Close other dropdowns
                    dropdownItems.forEach(other => {
                        if (other !== item) {
                            other.classList.remove('open');
                        }
                    });

                    // Toggle current dropdown
                    item.classList.toggle('open');
                }
            }
        });

        // Add click handler for the arrow icon specifically
        const arrow = link.querySelector('.dropdown-arrow');
        if (arrow) {
            arrow.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close other dropdowns
                dropdownItems.forEach(other => {
                    if (other !== item) {
                        other.classList.remove('open');
                    }
                });

                // Toggle current dropdown
                item.classList.toggle('open');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.sidebar')) {
            dropdownItems.forEach(item => {
                item.classList.remove('open');
            });
        }
    });
});
</script>
