const blogTrack = document.querySelector('#home-blog-track');
const blogPages = document.querySelector('.home-blog-pages');

if (blogTrack && blogPages) {
    let pageOffsets = [];

    function updateCurrentPage() {
        const distance = offset => Math.abs(offset - blogTrack.scrollLeft);
        const closest = pageOffsets.reduce((best, offset, index) =>
            distance(offset) < distance(pageOffsets[best]) ? index : best, 0);
        Array.from(blogPages.children).forEach((button, index) => {
            button.setAttribute('aria-current', String(index === closest));
        });
    }

    function renderBlogPages() {
        const cards = Array.from(blogTrack.children);
        const visible = cards.filter(card => card.offsetLeft + card.offsetWidth <= blogTrack.clientWidth + 1).length || 1;
        const maxOffset = blogTrack.scrollWidth - blogTrack.clientWidth;
        pageOffsets = cards.filter((card, index) => index % visible === 0)
            .map(card => Math.min(card.offsetLeft, maxOffset));
        blogPages.replaceChildren(...pageOffsets.map((offset, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('aria-label', `Show blog page ${index + 1}`);
            button.setAttribute('aria-controls', blogTrack.id);
            button.addEventListener('click', () => blogTrack.scrollTo({
                left: offset,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            }));
            return button;
        }));
        blogPages.hidden = pageOffsets.length < 2;
        blogTrack.classList.toggle('has-pages', pageOffsets.length > 1);
        updateCurrentPage();
    }

    renderBlogPages();
    window.addEventListener('resize', renderBlogPages);
    blogTrack.addEventListener('scroll', updateCurrentPage, { passive: true });
}
