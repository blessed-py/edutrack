(function () {
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    var toggle = document.getElementById('sidebar-toggle');
    if (!sidebar || !toggle) return;

    function close() {
        sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
    }

    function open() {
        sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('open');
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? close() : open();
    });

    if (backdrop) backdrop.addEventListener('click', close);
    sidebar.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', close);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) close();
    });
})();
