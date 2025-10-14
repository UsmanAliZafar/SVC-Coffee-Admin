{{-- resources/views/admin/layouts/partials/scripts.blade.php --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
{{--  --}}
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
    // Sidebar Toggle Function
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleIcon = document.getElementById('toggle-icon');

        sidebar.classList.toggle('collapsed');

        // Change icon
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.classList.remove('bi-chevron-left');
            toggleIcon.classList.add('bi-chevron-right');
        } else {
            toggleIcon.classList.remove('bi-chevron-right');
            toggleIcon.classList.add('bi-chevron-left');
        }

        // Save preference to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    // Mobile Sidebar Toggle
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');

        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Restore sidebar state from localStorage
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            toggleSidebar();
        }

        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert-auto-dismiss');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        });

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.querySelector('.navbar-toggler');
            const overlay = document.getElementById('mobile-overlay');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggleButton.contains(event.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');

        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
</script>
<script>
    /**
 * Admin Users Dropdown Navigation Enhancement
 * Adds interactive features and smooth animations
 */
$(document).ready(function() {

    // Initialize dropdown with enhanced features
    initAdminUsersDropdown();

    function initAdminUsersDropdown() {
        const dropdown = $('#adminUsersDropdown');
        const dropdownMenu = dropdown.next('.dropdown-menu');

        // Add hover effect for better UX (optional)
        if (window.innerWidth > 768) {
            dropdown.parent().hover(
                function() {
                    // Mouse enter
                    $(this).find('.dropdown-toggle').addClass('hover-state');
                },
                function() {
                    // Mouse leave
                    $(this).find('.dropdown-toggle').removeClass('hover-state');
                }
            );
        }

        // Auto-close dropdown when clicking outside (enhanced)
        $(document).on('click', function(e) {
            if (!dropdown.parent().is(e.target) && dropdown.parent().has(e.target).length === 0) {
                dropdownMenu.removeClass('show');
                dropdown.attr('aria-expanded', 'false');
            }
        });

        // Keyboard navigation support
        dropdown.on('keydown', function(e) {
            const items = dropdownMenu.find('.dropdown-item:visible');
            let currentIndex = items.index(items.filter(':focus'));

            switch(e.keyCode) {
                case 40: // Arrow Down
                    e.preventDefault();
                    if (currentIndex < items.length - 1) {
                        items.eq(currentIndex + 1).focus();
                    } else {
                        items.first().focus();
                    }
                    break;
                case 38: // Arrow Up
                    e.preventDefault();
                    if (currentIndex > 0) {
                        items.eq(currentIndex - 1).focus();
                    } else {
                        items.last().focus();
                    }
                    break;
                case 27: // Escape
                    e.preventDefault();
                    dropdownMenu.removeClass('show');
                    dropdown.attr('aria-expanded', 'false').focus();
                    break;
                case 13: // Enter
                    if (currentIndex >= 0) {
                        items.eq(currentIndex)[0].click();
                    }
                    break;
            }
        });

        // Add click tracking for analytics (optional)
        dropdownMenu.find('.dropdown-item').on('click', function() {
            const itemText = $(this).find('span').first().text();
            console.log('Admin dropdown navigation:', itemText);

            // You can add analytics tracking here
            // gtag('event', 'navigation_click', {
            //     'category': 'admin_users_dropdown',
            //     'label': itemText
            // });
        });

        // Add loading states for dropdown items (future enhancement)
        dropdownMenu.find('.dropdown-item[href]').on('click', function() {
            const link = $(this);
            const icon = link.find('i').first();
            const originalIcon = icon.attr('class');

            // Show loading state
            icon.attr('class', 'bi bi-hourglass-split me-2');
            link.addClass('loading');

            // Reset after a short delay (in case of page navigation issues)
            setTimeout(function() {
                icon.attr('class', originalIcon);
                link.removeClass('loading');
            }, 3000);
        });
    }

    // Add notification badges (future enhancement)
    function updateDropdownNotifications() {
        // Example: Add badge for inactive users count
        $.get('/admin/api/notifications/counts', function(data) {
            if (data.inactive_users > 0) {
                const inactiveUsersLink = $('a[href*="status=0"]');
                if (inactiveUsersLink.length && !inactiveUsersLink.find('.badge').length) {
                    inactiveUsersLink.append(`<span class="badge bg-warning text-dark ms-2">${data.inactive_users}</span>`);
                }
            }
        }).fail(function() {
            // Handle error silently or show user-friendly message
            console.log('Could not load notification counts');
        });
    }

    // Call notification update (uncomment when backend is ready)
    // updateDropdownNotifications();

    // Refresh notifications every 5 minutes (optional)
    // setInterval(updateDropdownNotifications, 300000);
});

// CSS for additional states
const additionalStyles = `
<style>
    .nav-link.dropdown-toggle.hover-state {
        background-color: rgba(91, 145, 76, 0.05);
        color: #5B914C;
    }

    .dropdown-item.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .dropdown-item.loading i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Focus styles for keyboard navigation */
    .dropdown-item:focus {
        outline: 2px solid #5B914C;
        outline-offset: -2px;
        background-color: #f8f9fa;
    }

    .dropdown-item.active:focus {
        outline-color: white;
    }
</style>
`;

// Inject additional styles
if (!document.querySelector('#admin-dropdown-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'admin-dropdown-styles';
    styleElement.innerHTML = additionalStyles;
    document.head.appendChild(styleElement);
}
</script>
