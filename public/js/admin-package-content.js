document.addEventListener('DOMContentLoaded', () => {
    const list = document.querySelector('[data-package-faq-list]');
    const template = document.querySelector('[data-package-faq-template]');
    if (!list || !template) return;
    function renumber() {
        list.querySelectorAll('[data-package-faq]').forEach((row, index) => {
            for (const key of ['question', 'answer']) {
                const input = row.querySelector(`[data-faq-${key}]`);
                input.name = `package_details[faqs][${index}][${key}]`;
                input.id = `package-faq-${index}-${key}`;
                input.previousElementSibling.htmlFor = input.id;
            }
        });
    }
    list.addEventListener('click', event => {
        if (!event.target.closest('[data-remove-package-faq]')) return;
        event.target.closest('[data-package-faq]').remove();
        renumber();
    });
    document.querySelector('[data-add-package-faq]').addEventListener('click', () => {
        if (list.children.length >= 30) return;
        list.append(template.content.cloneNode(true));
        renumber();
        list.lastElementChild.querySelector('input').focus();
    });
    renumber();
});
