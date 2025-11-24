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
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                    href="{{ route('admin.products.index') }}"
                    data-tooltip="Products">
                        <i class="bi bi-box"></i>
                        <span class="nav-text">Products</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('products.create'))
                        <li><a href="{{ route('admin.products.create') }}" class="{{ request()->routeIs('admin.products.create') ? 'active' : '' }}"><i class="bi bi-plus-square"></i> Add Product</a></li>
                        @endif
                        @if(auth('admin')->user()->hasPermission('products.read'))
                        <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.index') ? 'active' : '' }}"><i class="bi bi-list-ul"></i> All Products</a></li>
                        <li><a href="{{ route('admin.products.low-stock') }}" class="{{ request()->routeIs('admin.products.low-stock') ? 'active' : '' }}"><i class="bi bi-exclamation-triangle text-warning"></i> Low Stock</a></li>
                        <li><a href="{{ route('admin.products.inactive') }}" class="{{ request()->routeIs('admin.products.inactive') ? 'active' : '' }}"><i class="bi bi-eye-slash text-danger"></i> Discontinued</a></li>
                        <li><a href="{{ route('admin.products.featured') }}" class="{{ request()->routeIs('admin.products.featured') ? 'active' : '' }}"><i class="bi bi-star text-success"></i> Featured</a></li>
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
                        {{-- <li>
                            <a href="{{ route('admin.categories.tree') }}"
                            class="{{ request()->routeIs('admin.categories.tree') ? 'active' : '' }}">
                                <i class="bi bi-diagram-3"></i> Category Tree
                            </a>
                        </li> --}}
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
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.inventory.*', 'admin.warehouses.*', 'admin.stock-alerts.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.inventory.*', 'admin.warehouses.*', 'admin.stock-alerts.*') ? 'active' : '' }}"
                    href="{{ route('admin.inventory.index') }}"
                    data-tooltip="Inventory">
                        <i class="bi bi-boxes"></i>
                        <span class="nav-text">Inventory</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        {{-- Main Inventory Overview --}}
                        @if(auth('admin')->user()->hasPermission('inventory.read'))
                        <li>
                            <a href="{{ route('admin.inventory.index') }}"
                            class="{{ request()->routeIs('admin.inventory.index') ? 'active' : '' }}">
                                <i class="bi bi-grid"></i> Overview
                            </a>
                        </li>
                        @endif

                        {{-- Adjust Stock --}}
                        @if(auth('admin')->user()->hasPermission('inventory.update'))
                        <li>
                            <a href="{{ route('admin.inventory.adjust') }}"
                            class="{{ request()->routeIs('admin.inventory.adjust') ? 'active' : '' }}">
                                <i class="bi bi-plus-slash-minus"></i> Adjust Stock
                            </a>
                        </li>
                        @endif

                        {{-- Movement History --}}
                        @if(auth('admin')->user()->hasPermission('inventory.read'))
                        <li>
                            <a href="{{ route('admin.inventory.movement') }}"
                            class="{{ request()->routeIs('admin.inventory.movement') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i> Movement
                            </a>
                        </li>

                        {{-- Low Stock --}}
                        <li>
                            <a href="{{ route('admin.inventory.low-stock') }}"
                            class="{{ request()->routeIs('admin.inventory.low-stock') ? 'active' : '' }}">
                                <i class="bi bi-exclamation-triangle text-warning"></i> Low Stock
                                <span class="badge bg-warning text-dark ms-auto" id="lowStockBadge" style="display: none;">0</span>
                            </a>
                        </li>

                        {{-- Out of Stock --}}
                        <li>
                            <a href="{{ route('admin.inventory.out-of-stock') }}"
                            class="{{ request()->routeIs('admin.inventory.out-of-stock') ? 'active' : '' }}">
                                <i class="bi bi-x-octagon text-danger"></i> Out of Stock
                                <span class="badge bg-danger text-white ms-auto" id="outOfStockBadge" style="display: none;">0</span>
                            </a>
                        </li>

                        {{-- Stock Alerts --}}
                        {{-- <li>
                            <a href="{{ route('admin.stock-alerts.index') }}"
                            class="{{ request()->routeIs('admin.stock-alerts.*') ? 'active' : '' }}">
                                <i class="bi bi-bell"></i> All Alerts
                                <span class="badge bg-danger text-white ms-auto" id="totalAlertsBadge" style="display: none;">0</span>
                            </a>
                        </li> --}}

                        {{-- Warehouses --}}
                        <li>
                            <a href="{{ route('admin.warehouses.index') }}"
                            class="{{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                                <i class="bi bi-building"></i> Warehouses
                            </a>
                        </li>

                        {{-- Warehouse Sync --}}
                        {{-- <li>
                            <a href="{{ route('admin.inventory.warehouse-sync') }}"
                            class="{{ request()->routeIs('admin.inventory.warehouse-sync', 'admin.inventory.sync-settings') ? 'active' : '' }}">
                                <i class="bi bi-arrow-repeat"></i> Warehouse Sync
                            </a>
                        </li> --}}

                        {{-- Bulk Update --}}
                        {{-- @if(auth('admin')->user()->hasPermission('inventory.update'))
                        <li>
                            <a href="{{ route('admin.inventory.bulk-update') }}"
                            class="{{ request()->routeIs('admin.inventory.bulk-update') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Bulk Update
                            </a>
                        </li>
                        @endif --}}

                        {{-- Reports --}}
                        {{-- <li>
                            <a href="{{ route('admin.inventory.reports') }}"
                            class="{{ request()->routeIs('admin.inventory.reports') ? 'active' : '' }}">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li> --}}
                        @endif
                    </ul>
                </li>
            @endif
            {{-- End Inventory Management --}}

            {{-- Orders Management --}}
            @if(auth('admin')->user()->hasPermission('orders.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.orders.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                    href="{{ route('admin.orders.index') }}"
                    data-tooltip="Orders">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text">Orders</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('orders.read'))
                        <li>
                            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                                <i class="bi bi-list-ul"></i> All Orders
                            </a>
                        </li>
                        @endif

                        @if(auth('admin')->user()->hasPermission('orders.create'))
                        <li>
                            <a href="{{ route('admin.orders.create') }}" class="{{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                                <i class="bi bi-plus-square"></i> Create Order
                            </a>
                        </li>
                        @endif

                        @if(auth('admin')->user()->hasPermission('orders.read'))
                        <li class="divider"></li>

                        <li>
                            <a href="{{ route('admin.orders.pending') }}" class="{{ request()->routeIs('admin.orders.pending') ? 'active' : '' }}">
                                <i class="bi bi-clock text-warning"></i> Pending
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.processing') }}" class="{{ request()->routeIs('admin.orders.processing') ? 'active' : '' }}">
                                <i class="bi bi-hourglass-split text-info"></i> Processing
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.shipped') }}" class="{{ request()->routeIs('admin.orders.shipped') ? 'active' : '' }}">
                                <i class="bi bi-truck text-primary"></i> Shipped
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.delivered') }}" class="{{ request()->routeIs('admin.orders.delivered') ? 'active' : '' }}">
                                <i class="bi bi-check-circle text-success"></i> Delivered
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.cancelled') }}" class="{{ request()->routeIs('admin.orders.cancelled') ? 'active' : '' }}">
                                <i class="bi bi-x-circle text-danger"></i> Cancelled
                            </a>
                        </li>

                        <li class="divider"></li>

                        <li>
                            <a href="{{ route('admin.orders.today') }}" class="{{ request()->routeIs('admin.orders.today') ? 'active' : '' }}">
                                <i class="bi bi-calendar-day text-secondary"></i> Today's Orders
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.invoices') }}" class="{{ request()->routeIs('admin.orders.invoices') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text text-info"></i> Invoices
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.shipping') }}" class="{{ request()->routeIs('admin.orders.shipping') ? 'active' : '' }}">
                                <i class="bi bi-box-seam text-primary"></i> Shipping
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.refunds') }}" class="{{ request()->routeIs('admin.orders.refunds') ? 'active' : '' }}">
                                <i class="bi bi-arrow-counterclockwise text-warning"></i> Refunds
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.with-notes') }}" class="{{ request()->routeIs('admin.orders.with-notes') ? 'active' : '' }}">
                                <i class="bi bi-sticky text-muted"></i> With Notes
                            </a>
                        </li>

                        <li class="divider"></li>

                        <li>
                            <a href="{{ route('admin.orders.reports') }}" class="{{ request()->routeIs('admin.orders.reports') ? 'active' : '' }}">
                                <i class="bi bi-graph-up text-success"></i> Reports
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
            @endif
            {{-- Orders Management --}}

            {{-- Customers Management --}}
            @if(auth('admin')->user()->hasPermission('customers.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.customers.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                    href="{{ route('admin.customers.index') }}"
                    data-tooltip="Customers">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">Customers</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        {{-- All Customers --}}
                        <li>
                            <a href="{{ route('admin.customers.index') }}"
                            class="{{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
                                <i class="bi bi-people"></i> All Customers
                            </a>
                        </li>

                        {{-- Add Customer --}}
                        @if(auth('admin')->user()->hasPermission('customers.create'))
                        <li>
                            <a href="{{ route('admin.customers.create') }}"
                            class="{{ request()->routeIs('admin.customers.create') ? 'active' : '' }}">
                                <i class="bi bi-person-plus"></i> Add Customer
                            </a>
                        </li>
                        @endif

                        <li class="submenu-divider"></li>

                        {{-- Customer Segments --}}

                        <li>
                            <a href="{{ route('admin.customers.returning') }}"
                            class="{{ request()->routeIs('admin.customers.returning') ? 'active' : '' }}">
                                <i class="bi bi-arrow-repeat text-primary"></i> Returning
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.customers.top') }}"
                            class="{{ request()->routeIs('admin.customers.top') ? 'active' : '' }}">
                                <i class="bi bi-trophy text-warning"></i> Top Customers
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.customers.active') }}"
                            class="{{ request()->routeIs('admin.customers.active') ? 'active' : '' }}">
                                <i class="bi bi-check-circle text-success"></i> Active
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.customers.inactive') }}"
                            class="{{ request()->routeIs('admin.customers.inactive') ? 'active' : '' }}">
                                <i class="bi bi-pause-circle text-secondary"></i> Inactive
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.customers.blocked') }}"
                            class="{{ request()->routeIs('admin.customers.blocked') ? 'active' : '' }}">
                                <i class="bi bi-lock text-danger"></i> Blocked
                            </a>
                        </li>

                        <li class="submenu-divider"></li>
                        {{-- Reports --}}
                        <li>
                            <a href="{{ route('admin.customers.reports') }}"
                            class="{{ request()->routeIs('admin.customers.reports') ? 'active' : '' }}">
                                <i class="bi bi-bar-chart"></i> Reports & Analytics
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            {{-- End Customers Management --}}

            {{-- Coupons Management --}}
            @if(auth('admin')->user()->hasPermission('coupons.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.coupons.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"
                    href="{{ route('admin.coupons.index') }}"
                    data-tooltip="Coupons">
                        <i class="bi bi-ticket-perforated"></i>
                        <span class="nav-text">Coupons</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        {{-- All Coupons --}}
                        <li>
                            <a href="{{ route('admin.coupons.index') }}"
                            class="{{ request()->routeIs('admin.coupons.index') ? 'active' : '' }}">
                                <i class="bi bi-ticket-perforated"></i> All Coupons
                            </a>
                        </li>

                        {{-- Create Coupon --}}
                        @if(auth('admin')->user()->hasPermission('coupons.create'))
                        <li>
                            <a href="{{ route('admin.coupons.create') }}"
                            class="{{ request()->routeIs('admin.coupons.create') ? 'active' : '' }}">
                                <i class="bi bi-plus-circle"></i> Create Coupon
                            </a>
                        </li>
                        @endif

                        <li class="submenu-divider"></li>

                        {{-- Coupon Types --}}
                        <li>
                            <a href="{{ route('admin.coupons.index', ['discount_type' => 'percentage']) }}"
                            class="{{ request()->get('discount_type') === 'percentage' ? 'active' : '' }}">
                                <i class="bi bi-percent text-primary"></i> Percentage Discounts
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.coupons.index', ['discount_type' => 'fixed_amount']) }}"
                            class="{{ request()->get('discount_type') === 'fixed_amount' ? 'active' : '' }}">
                                <i class="bi bi-currency-dollar text-success"></i> Fixed Amount
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.coupons.index', ['discount_type' => 'free_shipping']) }}"
                            class="{{ request()->get('discount_type') === 'free_shipping' ? 'active' : '' }}">
                                <i class="bi bi-truck text-info"></i> Free Shipping
                            </a>
                        </li>

                        {{-- <li>
                            <a href="{{ route('admin.coupons.index', ['discount_type' => 'buy_x_get_y']) }}"
                            class="{{ request()->get('discount_type') === 'buy_x_get_y' ? 'active' : '' }}">
                                <i class="bi bi-gift text-warning"></i> Buy X Get Y
                            </a>
                        </li> --}}

                        <li class="submenu-divider"></li>

                        {{-- Coupon Status --}}
                        <li>
                            <a href="{{ route('admin.coupons.index', ['status' => 'active']) }}"
                            class="{{ request()->get('status') === 'active' ? 'active' : '' }}">
                                <i class="bi bi-check-circle text-success"></i> Active Coupons
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.coupons.index', ['status' => 'inactive']) }}"
                            class="{{ request()->get('status') === 'inactive' ? 'active' : '' }}">
                                <i class="bi bi-x-circle text-secondary"></i> Inactive
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.coupons.index', ['status' => 'expired']) }}"
                            class="{{ request()->get('status') === 'expired' ? 'active' : '' }}">
                                <i class="bi bi-calendar-x text-danger"></i> Expired
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.coupons.index', ['status' => 'scheduled']) }}"
                            class="{{ request()->get('status') === 'scheduled' ? 'active' : '' }}">
                                <i class="bi bi-clock text-info"></i> Scheduled
                            </a>
                        </li>

                        <li class="submenu-divider"></li>

                        {{-- Featured Coupons --}}
                        <li>
                            <a href="{{ route('admin.coupons.index', ['is_featured' => '1']) }}"
                            class="{{ request()->get('is_featured') === '1' ? 'active' : '' }}">
                                <i class="bi bi-star-fill text-warning"></i> Featured Coupons
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- End Coupons Management --}}
            <li class="section-divider">
                <span class="section-title">Reports</span>
            </li>
           {{-- Reports & Analytics --}}
            @if(auth('admin')->user()->hasPermission('reports.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.reports.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                    href="{{ route('admin.reports.index') }}" data-tooltip="Reports">
                        <i class="bi bi-graph-up"></i>
                        <span class="nav-text">Reports</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('reports.read'))
                        <li><a href="{{ route('admin.reports.sales.monthly') }}" class="{{ request()->routeIs('admin.reports.sales.monthly') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> Sales Report</a></li>
                        <li><a href="{{ route('admin.reports.revenue.index') }}" class="{{ request()->routeIs('admin.reports.revenue.index') ? 'active' : '' }}"><i class="bi bi-currency-dollar"></i> Revenue</a></li>
                        <li><a href="{{ route('admin.reports.products.top-selling') }}" class="{{ request()->routeIs('admin.reports.products.top-selling') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Products</a></li>
                        <li><a href="{{ route('admin.reports.customers.index') }}" class="{{ request()->routeIs('admin.reports.customers.index') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> Customers</a></li>
                        <li><a href="{{ route('admin.reports.inventory.index') }}" class="{{ request()->routeIs('admin.reports.inventory.index') ? 'active' : '' }}"><i class="bi bi-boxes"></i> Inventory</a></li>
                        @endif
                    </ul>
                </li>
            @endif
            {{-- Reports & Analytics --}}

             {{-- Divider --}}
            <li class="section-divider">
                <span class="section-title">System</span>
            </li>
            {{-- Contact Us Management --}}
            @if(auth('admin')->user()->hasPermission('contact_us.read'))
                <li class="nav-item {{ request()->routeIs('admin.contact-us.*') ? 'active' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.contact-us.*') ? 'active' : '' }}"
                    href="{{ route('admin.contact-us.index') }}"
                    data-tooltip="Contact Us">
                        <i class="bi bi-envelope-fill"></i>
                        <span class="nav-text">Contact & Queries</span>
                        @php
                            $unreadCount = \App\Models\ContactUs::whereNull('read_at')->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger rounded-pill ms-2">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
            @endif
            {{-- End Contact Us Management --}}

            {{-- Notifications Management --}}
            @if(auth('admin')->user()->hasPermission('notifications.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.notifications.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}"
                    href="{{ route('admin.notifications.index') }}"
                    data-tooltip="Content">
                        <i class="bi bi-file-text"></i>
                        <span class="nav-text">Notifications
                            @if((unread_notifications_count() ?? 0) > 0)
                                <span class="badge bg-danger ms-auto">{{ unread_notifications_count() }}</span>
                            @endif
                        </span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('notifications.read'))
                            <li class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                                <a class="nav-link {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                                    <i class="bi bi-bell"></i>
                                    <span class="nav-text">Notifications</span>
                                    @if((unread_notifications_count() ?? 0) > 0)
                                        <span class="badge bg-danger ms-auto">{{ unread_notifications_count() }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                        @if(auth('admin')->user()->hasPermission('notifications.update'))
                        <li>
                            <a href="{{ route('admin.notifications.settings') }}" class="{{ request()->routeIs('admin.notifications.settings') ? 'active' : '' }}">
                                <i class="bi bi-plus-square"></i> Notifications Settings
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
            @endif
            {{-- Notifications Management --}}

            {{-- Content Management --}}
            @if(auth('admin')->user()->hasPermission('content.read'))
                <li class="nav-item has-dropdown {{ request()->routeIs('admin.pages.*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"
                    href="{{ route('admin.pages.index') }}"
                    data-tooltip="Content">
                        <i class="bi bi-file-text"></i>
                        <span class="nav-text">Content</span>
                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(auth('admin')->user()->hasPermission('content.read'))
                        <li>
                            <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.index') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark"></i> Pages
                            </a>
                        </li>
                        @if(auth('admin')->user()->hasPermission('content.create'))
                        <li>
                            <a href="{{ route('admin.pages.create') }}" class="{{ request()->routeIs('admin.pages.create') ? 'active' : '' }}">
                                <i class="bi bi-plus-square"></i> Add New Page
                            </a>
                        </li>
                        @endif
                        {{-- Uncomment when ready --}}
                        {{-- <li><a href=""><i class="bi bi-image"></i> Banners</a></li> --}}
                        {{-- <li><a href=""><i class="bi bi-envelope-paper"></i> Email Templates</a></li> --}}
                        @endif
                    </ul>
                </li>
            @endif
            {{-- Content Management --}}

            {{-- Admin Users Management --}}
            @if(auth('admin')->user()->hasPermission('admin_users.read') || auth('admin')->user()->hasPermission('roles.read') || auth('admin')->user()->hasRole('super_admin'))
            <li class="nav-item has-dropdown {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.profile.*')  ? 'open' : '' }}">
                <a class="nav-link {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
                   href="{{ route('admin.users.index') }}"
                   data-tooltip="Admin Users">
                    <i class="bi bi-person-gear"></i>
                    <span class="nav-text">Admin Users</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </a>
                <ul class="submenu">
                    @if(auth('admin')->user()->hasPermission('admin_users.create'))
                    <li><a href="{{ route('admin.users.create') }}" class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}"><i class="bi bi-person-plus"></i> Add User</a></li>
                    @endif
                    @if(auth('admin')->user()->hasPermission('roles.read'))
                    <li><a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.index') ? 'active' : '' }}"><i class="bi bi-shield-check"></i> Roles</a></li>
                    @endif
                    @if(auth('admin')->user()->hasPermission('roles.create'))
                    <li><a href="{{ route('admin.roles.create') }}" class="{{ request()->routeIs('admin.roles.create') ? 'active' : '' }}"><i class="bi bi-shield-plus"></i> Add Role</a></li>
                    @endif
                    @if(auth('admin')->user()->hasRole('super_admin'))
                    <li><a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}"><i class="bi bi-key"></i> Permissions</a></li>
                    @endif
                    <li><a href="{{ route('admin.profile.edit') }}" class="{{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}"><i class="bi bi-person-circle"></i> My Profile</a></li>
                </ul>
            </li>
            @endif

            @if(auth('admin')->user()->hasPermission('settings.read'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                    href="{{ route('admin.settings.index') }}" data-tooltip="Settings">
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
    .sidebar .submenu a.active {
        background: rgba(91, 145, 76, 0.3);
        color: #4b4040;
        padding-left: 50px;
        font-weight: 700;
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

    });
</script>
