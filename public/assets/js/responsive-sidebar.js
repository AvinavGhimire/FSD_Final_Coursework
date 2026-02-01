/**
 * GenzFitness - Responsive Sidebar Handler
 * Handles mobile hamburger menu and sidebar interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // Create hamburger menu button for mobile
    const navbar = document.querySelector('.navbar .container-fluid');
    if (navbar && window.innerWidth < 768) {
        const hamburger = document.createElement('button');
        hamburger.className = 'hamburger-menu';
        hamburger.innerHTML = '<i class="fas fa-bars"></i>';
        hamburger.setAttribute('aria-label', 'Toggle navigation');

        // Insert at the beginning of navbar
        navbar.insertBefore(hamburger, navbar.firstChild);

        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);

        // Toggle sidebar
        hamburger.addEventListener('click', function () {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');

            // Prevent body scroll when sidebar is open
            if (sidebar.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Close sidebar when clicking backdrop
        backdrop.addEventListener('click', function () {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        });

        // Close sidebar when clicking a nav link
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function () {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            });
        });
    }

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            // Reload page on significant resize to reinitialize layout
            if ((window.innerWidth >= 768 && document.querySelector('.hamburger-menu')) ||
                (window.innerWidth < 768 && !document.querySelector('.hamburger-menu'))) {
                location.reload();
            }
        }, 250);
    });

    // Add smooth transitions to sidebar on desktop
    if (window.innerWidth >= 1024) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.transition = 'width 0.3s ease';
        }
    }
});
