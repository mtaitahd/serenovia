(() => {
    const sidebar = document.querySelector('.admin-sidebar');
    const toggles = [...document.querySelectorAll('[data-sidebar-toggle]')];
    if (!sidebar || toggles.length === 0) return;

    const storageKey = 'serenovia-admin-sidebar-group';
    const isMobile = () => window.matchMedia('(max-width: 760px)').matches;
    let savedGroup = '';
    try { savedGroup = localStorage.getItem(storageKey) || ''; } catch (_) {}

    function setOpen(toggle, open, persist = false) {
        const group = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!group) return;
        toggle.setAttribute('aria-expanded', String(open));
        group.classList.toggle('is-open', open);
        group.setAttribute('aria-hidden', String(!open));
        if (persist) {
            try { localStorage.setItem(storageKey, open ? toggle.dataset.sidebarToggle : ''); } catch (_) {}
        }
    }

    const activeToggle = toggles.find(toggle => toggle.dataset.active === 'true');
    toggles.forEach(toggle => {
        const shouldOpen = activeToggle ? toggle === activeToggle : toggle.dataset.sidebarToggle === savedGroup;
        setOpen(toggle, shouldOpen);
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') !== 'true';
            toggles.forEach(other => setOpen(other, false));
            if (open) setOpen(toggle, true);
            setOpen(toggle, open, true);
            sidebar.classList.toggle('sidebar-expanded', isMobile() && open);
        });
    });

    if (isMobile() && toggles.some(toggle => toggle.getAttribute('aria-expanded') === 'true')) {
        sidebar.classList.add('sidebar-expanded');
    }
})();
