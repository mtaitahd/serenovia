document.querySelectorAll('[data-home-carousel]').forEach(carousel => {
    const controls = carousel.querySelector('.home-stays-controls');
    if (!controls) return;

    const slides = carousel.querySelectorAll('[data-stay-slide]');
    const buttons = controls.querySelectorAll('button');

    buttons.forEach((button, selectedIndex) => {
        button.addEventListener('click', () => {
            slides.forEach((slide, index) => {
                const selected = index === selectedIndex;
                slide.hidden = !selected;
                buttons[index].setAttribute('aria-pressed', String(selected));
            });
        });
    });

    controls.hidden = false;
});
