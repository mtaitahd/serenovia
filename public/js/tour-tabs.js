(() => {
    const links = [...document.querySelectorAll('[data-tour-tab]')];
    const panels = [...document.querySelectorAll('[data-tour-panel]')];
    if (!links.length) return;
    const show = name => {
        if (!links.some(link => link.dataset.tourTab === name)) name = 'overview';
        panels.forEach(panel => { panel.hidden = panel.dataset.tourPanel !== name; });
        links.forEach(link => {
            if (link.dataset.tourTab === name) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
        window.dispatchEvent(new Event('resize'));
    };
    links.forEach(link => link.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault(); show(link.dataset.tourTab); history.pushState(null, '', link.href);
    }));
    window.addEventListener('popstate', () => show(new URL(location.href).searchParams.get('section') || 'overview'));
})();
