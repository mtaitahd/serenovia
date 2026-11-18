const heroVideo = document.querySelector('.home-hero-video');
const heroVideoControls = document.querySelector('.home-hero-video-controls');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

if (heroVideo && heroVideoControls) {
    const button = heroVideoControls.querySelector('button');

    heroVideo.addEventListener('playing', () => {
        heroVideo.classList.add('is-ready');
        heroVideoControls.hidden = false;
        button.textContent = 'Pause video';
    });
    heroVideo.addEventListener('pause', () => { button.textContent = 'Play video'; });
    heroVideo.addEventListener('error', () => {
        heroVideo.classList.remove('is-ready');
        heroVideoControls.hidden = true;
    });
    button.addEventListener('click', () => {
        if (heroVideo.paused) heroVideo.play().catch(() => {});
        else heroVideo.pause();
    });
    reduceMotion.addEventListener('change', () => {
        if (reduceMotion.matches) heroVideo.pause();
    });

    if (!reduceMotion.matches && !navigator.connection?.saveData) {
        heroVideo.muted = true;
        heroVideo.src = heroVideo.dataset.src;
        heroVideo.play().catch(() => {});
    }
}
