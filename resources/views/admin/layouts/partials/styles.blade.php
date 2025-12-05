{{-- resources/views/admin/layouts/partials/styles.blade.php --}}
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

<style>
    :root {
        --brand-primary: #5B914C;
        --brand-primary-rgb: 91, 145, 76;
        --brand-primary-dark: #4A7A3F;
        --brand-primary-light: #6BA055;
        --navbar-height: 60px;
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 80px;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .btn-brand {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
        color: white;
        transition: all 0.3s ease;
    }

    .btn-brand:hover {
        background-color: var(--brand-primary-dark);
        border-color: var(--brand-primary-dark);
        color: white;
        transform: translateY(-1px);
    }

    .bg-brand {
        background-color: var(--brand-primary) !important;
    }

    .text-brand {
        color: var(--brand-primary) !important;
    }

    .border-brand {
        border-color: var(--brand-primary) !important;
    }

    /* Navbar Styles */
    .navbar {
        height: var(--navbar-height);
        z-index: 1030;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .navbar-brand {
        font-weight: 600;
        font-size: 1.2rem;
    }

    /* Admin Wrapper */
    .admin-wrapper {
        display: flex;
        min-height: calc(100vh - var(--navbar-height));
    }

    /* Sidebar Styles */
    .sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transition: all 0.3s ease;
        z-index: 1020;
        overflow-y: auto;
        border-right: 2px solid var(--brand-primary);
        flex-shrink: 0;
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar .sidebar-content {
        padding: 1rem 0;
    }

    .sidebar .nav-link {
        color: #495057;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
        margin: 0.25rem 0.5rem;
        transition: all 0.3s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        text-decoration: none;
        position: relative;
    }

    .sidebar .nav-link:hover {
        background-color: var(--brand-primary);
        color: white;
        transform: translateX(5px);
    }

    .sidebar .nav-link.active {
        background-color: var(--brand-primary);
        color: white;
        font-weight: 600;
    }

    .sidebar .nav-link i {
        width: 20px;
        margin-right: 12px;
        text-align: center;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .sidebar .nav-text {
        transition: all 0.3s ease;
        opacity: 1;
        overflow: hidden;
    }

    .sidebar.collapsed .nav-text {
        opacity: 0;
        width: 0;
    }

    .sidebar.collapsed .nav-link {
        justify-content: center;
        padding: 0.75rem 0.5rem;
        margin: 0.25rem 0.25rem;
    }

    .sidebar.collapsed .nav-link i {
        margin-right: 0;
    }

    /* Tooltip for collapsed sidebar */
    .sidebar.collapsed .nav-link:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: var(--brand-primary);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        white-space: nowrap;
        z-index: 1000;
        margin-left: 0.5rem;
        font-size: 0.875rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    /* Section dividers */
    .sidebar .section-divider {
        margin: 1rem 0 0.5rem 0;
        padding: 0;
    }

    .sidebar .section-divider hr {
        margin: 0.5rem 1rem;
        border-color: #dee2e6;
    }

    .sidebar .section-title {
        color: #6c757d;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0 1rem;
        margin-bottom: 0.5rem;
    }

    .sidebar.collapsed .section-title {
        opacity: 0;
        height: 0;
        padding: 0;
        margin: 0;
        overflow: hidden;
    }

    /* Main Content */
    .main-content {
        flex: 1;
        transition: all 0.3s ease;
        min-height: calc(100vh - var(--navbar-height));
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 1.5rem;
        max-width: 100%;
    }

    /* Sidebar Toggle Button */
    .sidebar-toggle {
        position: absolute;
        top: 1rem;
        right: -18px;
        z-index: 1021;
        background: var(--brand-primary);
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        cursor: pointer;
    }

    .sidebar-toggle:hover {
        background: var(--brand-primary-dark);
        transform: scale(1.1);
    }

    .sidebar-toggle i {
        font-size: 1rem;
    }

    /* Card Styles */
    .card-stats {
        border-left: 4px solid var(--brand-primary);
        transition: all 0.3s ease;
    }

    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* Alert Styles */
    .alert-auto-dismiss {
        transition: opacity 0.15s linear;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .admin-wrapper {
            position: relative;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1040;
            transform: translateX(-100%);
            width: var(--sidebar-width) !important;
            padding-top: var(--navbar-height);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-toggle {
            display: none;
        }

        .main-content {
            width: 100%;
            margin-left: 0;
        }

        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-overlay.show {
            opacity: 1;
            visibility: visible;
        }
    }

    @media (max-width: 576px) {
        .content-wrapper {
            padding: 1rem;
        }

        .navbar-brand {
            font-size: 1.1rem;
        }
    }

    /* Custom scrollbar for sidebar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--brand-primary);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--brand-primary-dark);
    }

    /* Notification badge animation */
    /* .badge {
        animation: pulse 2s infinite;
    } */

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>
<style>
    /* Admin Users Dropdown Navigation Styles */
    .nav-item.dropdown .dropdown-menu {
        min-width: 280px;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 0;
        margin-top: 0.5rem;
        background: #ffffff;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
    }

    .nav-item.dropdown .dropdown-item {
        padding: 0.75rem 1.25rem;
        border: none;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-item.dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #5B914C;
        transform: translateX(2px);
    }

    .nav-item.dropdown .dropdown-item.active {
        background-color: #5B914C;
        color: white;
    }

    .nav-item.dropdown .dropdown-item.active:hover {
        background-color: #4a7a3f;
        color: white;
        transform: translateX(0);
    }

    .nav-item.dropdown .dropdown-item i {
        width: 20px;
        text-align: center;
        opacity: 0.8;
    }

    .nav-item.dropdown .dropdown-item:hover i {
        opacity: 1;
    }

    .nav-item.dropdown .dropdown-item small {
        font-size: 0.75rem;
        margin-top: 2px;
    }

    .nav-item.dropdown .dropdown-header {
        color: #6c757d;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem 1.25rem 0.25rem;
        margin-bottom: 0.25rem;
    }

    .nav-item.dropdown .dropdown-divider {
        margin: 0.5rem 0;
        border-color: #e9ecef;
    }

    /* Navigation Link Active State */
    .nav-link.dropdown-toggle.active {
        background-color: #5B914C;
        color: white !important;
        border-radius: 6px;
    }

    .nav-link.dropdown-toggle.active::after {
        color: white;
    }

    /* Dropdown Arrow Styling */
    .nav-link.dropdown-toggle::after {
        transition: transform 0.3s ease;
    }

    .nav-link.dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(180deg);
    }

    /* Hover Effects for Dropdown Toggle */
    .nav-link.dropdown-toggle:hover {
        background-color: rgba(91, 145, 76, 0.1);
        border-radius: 6px;
        color: #222222;
    }

    /* Animation for dropdown appearance */
    @keyframes dropdownSlideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu.show {
        animation: dropdownSlideIn 0.3s ease-out;
    }

    /* Custom scrollbar for long dropdown menus */
    .nav-item.dropdown .dropdown-menu {
        max-height: 400px;
        overflow-y: auto;
    }

    .nav-item.dropdown .dropdown-menu::-webkit-scrollbar {
        width: 6px;
    }

    .nav-item.dropdown .dropdown-menu::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .nav-item.dropdown .dropdown-menu::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .nav-item.dropdown .dropdown-menu::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }

    /* Badge for notification counts (future enhancement) */
    .nav-item.dropdown .dropdown-item .badge {
        float: right;
        margin-top: 2px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .nav-item.dropdown .dropdown-menu {
            min-width: 250px;
            position: static !important;
            transform: none !important;
            box-shadow: none;
            border: 1px solid #e9ecef;
            margin: 0.5rem 0;
        }

        .nav-item.dropdown .dropdown-item {
            padding: 0.5rem 1rem;
        }
    }
</style>
{{-- Datatable comme Css --}}
<style>
    div.dataTables_processing {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        margin: 0 !important;
        padding: 30px 50px !important;
        background: rgba(255, 255, 255, 0.98) !important;
        border: none !important;
        border-radius: 15px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        z-index: 9999 !important;
        backdrop-filter: blur(10px);
        animation: fadeInScale 0.3s ease;
    }

    .datatable-loading-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .bars-loader {
        display: flex;
        gap: 6px;
        align-items: center;
        height: 50px;
    }

    .bars-loader span {
        width: 8px;
        height: 100%;
        background: #5B914C;
        border-radius: 4px;
        animation: barGrow 1.2s ease-in-out infinite;
    }

    .bars-loader span:nth-child(1) { animation-delay: 0s; }
    .bars-loader span:nth-child(2) { animation-delay: 0.1s; }
    .bars-loader span:nth-child(3) { animation-delay: 0.2s; }
    .bars-loader span:nth-child(4) { animation-delay: 0.3s; }
    .bars-loader span:nth-child(5) { animation-delay: 0.4s; }

    .datatable-loading-text {
        color: #5B914C;
        font-weight: 600;
        font-size: 1rem;
    }

    @keyframes barGrow {
        0%, 100% {
            height: 30%;
        }
        50% {
            height: 100%;
        }
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }
</style>
{{--  --}}
@stack('styles')
