// ============================================
// SPA (Single Page Application) Implementation
// Add this to your main admin layout
// ============================================

$(document).ready(function() {

    // Configuration
    const contentContainer = '#main-content'; // Your main content container ID
    const loadingHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading content...</p>
        </div>
    `;

    // Function to load page content via AJAX
    function loadPage(url, pushState = true) {
        // Show loading indicator
        $(contentContainer).html(loadingHTML);

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Make AJAX request
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SPA-Request': 'true' // Custom header to identify SPA requests
            },
            success: function(response) {
                // Check if response is full HTML or partial content
                let content;

                if (typeof response === 'string' && response.includes('<!DOCTYPE') || response.includes('<html')) {
                    // Extract content from full page
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(response, 'text/html');
                    content = doc.querySelector(contentContainer)?.innerHTML || response;

                    // Update page title
                    const title = doc.querySelector('title')?.textContent;
                    if (title) {
                        document.title = title;
                    }
                } else {
                    content = response;
                }

                // Update content
                $(contentContainer).html(content);

                // Update browser history
                if (pushState) {
                    window.history.pushState({ path: url }, '', url);
                }

                // Update active menu item
                updateActiveMenu(url);

                // Reinitialize plugins for new content
                reinitializePlugins();

                // Execute inline scripts
                executeScripts(content);
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Failed to load page';

                if (xhr.status === 404) {
                    errorMessage = 'Page not found (404)';
                } else if (xhr.status === 403) {
                    errorMessage = 'Access denied (403)';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error (500)';
                }

                $(contentContainer).html(`
                    <div class="alert alert-danger text-center py-5">
                        <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">${errorMessage}</h4>
                        <p>${error}</p>
                        <button class="btn btn-primary mt-3" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Reload Page
                        </button>
                    </div>
                `);
            }
        });
    }

    // Handle navigation link clicks
    $(document).on('click', 'a.spa-link, .nav-link, .submenu a, .breadcrumb a', function(e) {
        const href = $(this).attr('href');

        // Skip if:
        // - href is empty, #, javascript:void(0), or external
        // - has data-no-spa attribute
        // - is a logout link
        // - opens in new tab (target="_blank")
        if (!href ||
            href === '#' ||
            href === 'javascript:void(0)' ||
            href.startsWith('http') && !href.includes(window.location.host) ||
            $(this).attr('data-no-spa') === 'true' ||
            $(this).attr('target') === '_blank' ||
            href.includes('logout')) {
            return;
        }

        e.preventDefault();
        loadPage(href);
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.path) {
            loadPage(e.state.path, false);
        } else {
            loadPage(window.location.href, false);
        }
    });

    // Update active menu items
    function updateActiveMenu(url) {
        // Remove all active classes
        $('.nav-link, .submenu a').removeClass('active');

        // Add active class to current link
        $('a[href="' + url + '"]').addClass('active');

        // Open parent dropdown if exists
        $('a[href="' + url + '"]').closest('.has-dropdown').addClass('active open')
            .find('.submenu').slideDown(300);
    }

    // Reinitialize plugins after content load
    function reinitializePlugins() {
        // Reinitialize DataTables
        if (typeof $.fn.DataTable !== 'undefined') {
            if ($.fn.DataTable.isDataTable('#categoriesTable')) {
                $('#categoriesTable').DataTable().destroy();
            }
            // The table will be reinitialized by the page's own script
        }

        // Reinitialize Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2();
        }

        // Reinitialize Tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Reinitialize any custom plugins you use
        // Add more here as needed
    }

    // Execute inline scripts from loaded content
    function executeScripts(content) {
        const scripts = $(content).filter('script').add($(content).find('script'));

        scripts.each(function() {
            if (this.src) {
                // External script
                const script = document.createElement('script');
                script.src = this.src;
                document.body.appendChild(script);
            } else {
                // Inline script
                try {
                    eval(this.textContent || this.innerHTML);
                } catch (e) {
                    console.error('Error executing script:', e);
                }
            }
        });
    }

    // Handle form submissions via AJAX
    $(document).on('submit', 'form.spa-form', function(e) {
        e.preventDefault();

        const form = $(this);
        const formData = new FormData(this);
        const url = form.attr('action');
        const method = form.attr('method') || 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.redirect) {
                    loadPage(response.redirect);
                } else if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500
                    });
                }
            },
            error: function(xhr) {
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    // Display errors on form
                    $.each(errors, function(key, messages) {
                        const input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });
                }
            }
        });
    });

    // Optional: Add loading progress bar at top
    function addProgressBar() {
        const progressBar = $('<div id="spa-progress-bar" style="position: fixed; top: 0; left: 0; width: 0; height: 3px; background: #5B914C; z-index: 9999; transition: width 0.3s;"></div>');
        $('body').append(progressBar);
    }

    function updateProgressBar(percent) {
        $('#spa-progress-bar').css('width', percent + '%');
        if (percent === 100) {
            setTimeout(() => $('#spa-progress-bar').css('width', '0'), 500);
        }
    }

    // Initialize progress bar
    addProgressBar();

    // Update progress on AJAX events
    $(document).ajaxStart(function() {
        updateProgressBar(30);
    }).ajaxComplete(function() {
        updateProgressBar(100);
    });

    // Initial state
    window.history.replaceState({ path: window.location.href }, '', window.location.href);
});
