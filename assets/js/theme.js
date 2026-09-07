(function () {
    var root = document.documentElement;
    var stored = localStorage.getItem('theme');
    var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (stored === 'dark' || (!stored && prefersDark)) {
        root.classList.add('dark');
    }
    var btn = document.getElementById('themeToggle');
    if (!btn) return;
    var icon = document.getElementById('themeToggleIcon');
    if (!icon) {
        icon = btn.querySelector('i');
    }
    var refresh = function () {
        var dark = root.classList.contains('dark');
        if (icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
        btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
    };
    refresh();
    btn.addEventListener('click', function () {
        root.classList.toggle('dark');
        localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
        refresh();
    });
})();