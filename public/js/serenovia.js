(() => {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Open navigation' : 'Close navigation');
            nav.classList.toggle('is-open', !isOpen);
        });

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open navigation');
                nav.classList.remove('is-open');
            });
        });
    }

    const revealTargets = document.querySelectorAll(
        '.intro-label, .intro-copy, .journeys .section-heading, .journey-card, .destination-title, .destination-list a, .quote-content, .plan > *'
    );

    revealTargets.forEach((target, index) => {
        target.classList.add('reveal');
        if (target.matches('.journey-card, .destination-list a')) {
            target.style.transitionDelay = `${(index % 4) * 75}ms`;
        }
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealTargets.forEach((target) => observer.observe(target));
    } else {
        revealTargets.forEach((target) => target.classList.add('is-visible'));
    }
})();
