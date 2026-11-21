document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('admin-form-popup');
    const frame = document.getElementById('admin-form-popup-frame');
    if (!dialog || !frame) return;

    const popupPath = /^\/admin\/(?:tours|destinations|countries|articles|faqs|tour-categories|accommodations)\/(?:create|\d+\/edit)\/?$|^\/admin\/(?:settings|inquiries\/\d+)\/?$/;
    let expectedIndexPath = null;

    function adminRoute(pathname) {
        const position = pathname.lastIndexOf('/admin/');
        if (position >= 0) return { prefix: pathname.slice(0, position), path: pathname.slice(position) };
        if (pathname === '/admin' || pathname.startsWith('/admin/')) return { prefix: '', path: pathname };
        return null;
    }

    function openPopup(url, label) {
        const target = new URL(url, window.location.origin);
        target.searchParams.set('popup', '1');
        const admin = adminRoute(target.pathname);
        const resource = admin?.path.match(/^\/admin\/(tours|destinations|countries|articles|faqs|tour-categories|accommodations)\//);
        const inquiry = admin?.path.match(/^\/admin\/inquiries\/\d+\/?$/);
        expectedIndexPath = resource ? `${admin.prefix}/admin/${resource[1]}` : inquiry ? `${admin.prefix}/admin/inquiries` : null;
        frame.title = label || 'Admin form';
        frame.src = target.toString();
        dialog.showModal();
        document.body.classList.add('has-admin-popup');
    }

    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.target && link.target !== '_self') return;
        const url = new URL(link.href, window.location.href);
        const admin = adminRoute(url.pathname);
        if (url.origin !== window.location.origin || !admin || (!link.hasAttribute('data-admin-popup') && !popupPath.test(admin.path))) return;
        event.preventDefault();
        openPopup(url, link.textContent.trim());
    }, true);

    async function closeForm() {
        try { await frame.contentWindow.savePackageRecovery?.(); }
        catch { if (!confirm('The latest changes could not be backed up. Close without saving?')) return; }
        dialog.close();
    }
    document.querySelectorAll('[data-close-admin-popup]').forEach(button => button.addEventListener('click', closeForm));
    dialog.addEventListener('click', event => { if (event.target === dialog) closeForm(); });
    dialog.addEventListener('cancel', event => { event.preventDefault(); closeForm(); });
    dialog.addEventListener('close', () => {
        frame.src = 'about:blank';
        expectedIndexPath = null;
        document.body.classList.remove('has-admin-popup');
    });
    frame.addEventListener('load', () => {
        if (!expectedIndexPath) return;
        try {
            const loadedPath = adminRoute(new URL(frame.contentWindow.location.href).pathname);
            if (loadedPath && `${loadedPath.prefix}${loadedPath.path}`.replace(/\/$/, '') === expectedIndexPath.replace(/\/$/, '')) {
                dialog.close();
                window.location.reload();
            }
        } catch { /* A cross-origin response is not part of the admin popup flow. */ }
    });

    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin || event.data?.type !== 'admin-form-saved') return;
        dialog.close();
        window.location.reload();
    });
});
