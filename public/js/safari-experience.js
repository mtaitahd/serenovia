(() => {
    const viewer = document.querySelector('.photo-viewer');
    let photos = [], current = 0, trigger;
    const showPhoto = (index) => {
        current = (index + photos.length) % photos.length;
        const photo = photos[current];
        const image = viewer.querySelector('img');
        image.src = photo.href;
        image.alt = photo.dataset.caption || photo.querySelector('img')?.alt || '';
        viewer.querySelector('figcaption').textContent = image.alt;
        viewer.querySelector('.photo-count').textContent = `${current + 1} / ${photos.length}`;
        viewer.querySelectorAll('.photo-prev, .photo-next').forEach(button => { button.hidden = photos.length < 2; });
    };
    if (viewer && typeof viewer.showModal === 'function') {
        document.querySelectorAll('[data-photo]').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                trigger = link;
                photos = [...document.querySelectorAll('[data-photo]')].filter(item => item.dataset.photoGroup === link.dataset.photoGroup);
                showPhoto(photos.indexOf(link));
                viewer.showModal();
                document.body.classList.add('photo-open');
            });
        });
        viewer.querySelector('.photo-close').addEventListener('click', () => viewer.close());
        viewer.querySelector('.photo-prev').addEventListener('click', () => showPhoto(current - 1));
        viewer.querySelector('.photo-next').addEventListener('click', () => showPhoto(current + 1));
        viewer.addEventListener('keydown', event => {
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                showPhoto(current + (event.key === 'ArrowLeft' ? -1 : 1));
            }
        });
        viewer.addEventListener('click', event => { if (event.target === viewer) viewer.close(); });
        viewer.addEventListener('close', () => { document.body.classList.remove('photo-open'); trigger?.focus(); });
    }
    const days = [...document.querySelectorAll('.itinerary-day')];
    const expand = document.querySelector('.expand-itinerary');
    if (expand && days.length) {
        expand.hidden = false;
        const updateLabel = () => { expand.textContent = days.every(day => day.open) ? 'Collapse all −' : 'Expand all +'; };
        expand.addEventListener('click', () => {
            const open = !days.every(day => day.open);
            days.forEach(day => { day.open = open; });
            updateLabel();
        });
        days.forEach(day => day.addEventListener('toggle', updateLabel));
        updateLabel();
    }
    document.querySelectorAll('.faq-item').forEach(item => {
        item.addEventListener('toggle', () => {
            if (item.open) item.parentElement.querySelectorAll('.faq-item').forEach(other => { if (other !== item) other.open = false; });
        });
    });
    const navLinks = [...document.querySelectorAll('.journey-navigation a')];
    if (navLinks.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) navLinks.forEach(link => link.classList.toggle('is-active', link.hash === `#${entry.target.id}`));
            });
        }, {rootMargin: '-15% 0px -55% 0px'});
        navLinks.forEach(link => { const section = document.querySelector(link.hash); if (section) observer.observe(section); });
    }
})();

// Independent controls for each journal carousel.
document.querySelectorAll('.article-discovery').forEach(section => {
    const track = section.querySelector('.article-discovery-track');
    const controls = section.querySelector('.article-slider-controls');
    const previous = section.querySelector('[data-article-prev]');
    const next = section.querySelector('[data-article-next]');
    controls.hidden = false;
    const update = () => {
        previous.disabled = track.scrollLeft <= 2;
        next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
    };
    const advance = direction => {
        const card = track.querySelector('.discovery-card');
        const gap = parseFloat(getComputedStyle(track).gap) || 0;
        track.scrollBy({left: direction * (card.getBoundingClientRect().width + gap), behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
    };
    previous.addEventListener('click', () => advance(-1));
    next.addEventListener('click', () => advance(1));
    track.addEventListener('scroll', update, {passive:true});
    track.addEventListener('keydown', event => {
        if (event.target === track && ['ArrowLeft','ArrowRight'].includes(event.key)) {
            event.preventDefault(); advance(event.key === 'ArrowRight' ? 1 : -1);
        }
    });
    window.addEventListener('resize', update);
    update();
});
