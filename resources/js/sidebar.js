document.querySelectorAll('[data-sidebar-link]').forEach((link) => {
    link.addEventListener('click', () => {
        const sidebar = link.closest('.offcanvas');

        if (sidebar?.classList.contains('show') && window.bootstrap) {
            window.bootstrap.Offcanvas.getOrCreateInstance(sidebar).hide();
        }
    });
});
