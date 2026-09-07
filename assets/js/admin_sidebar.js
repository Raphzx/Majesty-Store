(function() {
    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); }
        else { document.addEventListener('DOMContentLoaded', fn); }
    }

    ready(function() {
        var burger = document.getElementById('adminBurger');
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var closeBtn = document.getElementById('sidebarClose');
        if (!sidebar || !burger) return;

        function openSidebar() {
            sidebar.classList.add('open');
            if (overlay) overlay.classList.add('show');
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }

        burger.addEventListener('click', function(e) {
            e.stopPropagation();
            openSidebar();
        });
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        var navLinks = sidebar.querySelectorAll('a');
        navLinks.forEach(function(a) {
            a.addEventListener('click', closeSidebar);
        });
    });
})();
