const heroVideo = document.querySelector('.home-hero-video');
const heroVideoToggle = document.querySelector('[data-hero-video-toggle]');
if (heroVideo) {
    heroVideo.muted = true;
    const sync = () => {
        if (heroVideoToggle) heroVideoToggle.textContent = heroVideo.paused ? 'Play homepage video' : 'Pause homepage video';
    };
    heroVideo.addEventListener('playing', sync);
    heroVideo.addEventListener('pause', sync);
    heroVideoToggle?.addEventListener('click', () => {
        if (heroVideo.paused) heroVideo.play().catch(sync);
        else heroVideo.pause();
    });
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const start = () => {
        if (motion.matches) heroVideo.pause();
        else heroVideo.play().catch(sync);
    };
    motion.addEventListener('change', start);
    start();
}