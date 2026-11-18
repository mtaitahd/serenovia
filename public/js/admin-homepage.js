(() => {
    const dialog = document.querySelector('.hp-gallery-dialog');
    if (!dialog) return;
    let activeField = null;
    let pending = null;
    const photos = [...dialog.querySelectorAll('[data-gallery-id]')];
    const apply = dialog.querySelector('[data-gallery-apply]');
    const search = dialog.querySelector('[data-gallery-search]');
    const category = dialog.querySelector('[data-gallery-category]');
    const filter = () => {
        const term = search.value.trim().toLowerCase();
        let visible = 0;
        photos.forEach(photo => {
            photo.hidden = !(photo.dataset.title + ' ' + photo.dataset.category).toLowerCase().includes(term) || (category.value !== '' && photo.dataset.category !== category.value);
            if (!photo.hidden) visible++;
        });
        dialog.querySelector('[data-gallery-empty]').hidden = visible > 0;
    };
    [search, category].forEach(control => control.addEventListener('input', filter));
    document.querySelectorAll('[data-select-gallery]').forEach(button => button.addEventListener('click', () => {
        activeField = button.closest('[data-media-field]');
        pending = photos.find(photo => photo.dataset.galleryId === activeField.querySelector('[data-gallery-value]').value) || null;
        photos.forEach(photo => photo.setAttribute('aria-pressed', String(photo === pending)));
        apply.disabled = !pending;
        search.value = ''; category.value = ''; filter();
        dialog.showModal();
        search.focus();
    }));
    photos.forEach(photo => photo.addEventListener('click', () => {
        pending = photo;
        photos.forEach(item => item.setAttribute('aria-pressed', String(item === photo)));
        apply.disabled = false;
    }));
    dialog.querySelectorAll('[data-gallery-cancel]').forEach(button => button.addEventListener('click', () => dialog.close()));
    apply.addEventListener('click', () => {
        if (!pending || !activeField) return;
        activeField.querySelector('[data-gallery-value]').value = pending.dataset.galleryId;
        activeField.querySelector('[data-media-upload]').value = '';
        activeField.querySelector('[data-media-remove]').checked = false;
        const image = activeField.querySelector('[data-media-preview]');
        image.src = pending.dataset.path; image.hidden = false;
        activeField.querySelector('[data-media-placeholder]').hidden = true;
        activeField.querySelector('[data-media-state]').textContent = 'Selected: ' + pending.dataset.title + ' · save to publish';
        dialog.close();
    });
    document.querySelectorAll('[data-media-upload]').forEach(input => input.addEventListener('change', () => {
        const file = input.files[0]; if (!file) return;
        const field = input.closest('[data-media-field]');
        const preview = field.querySelector('[data-media-preview]');
        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        preview.dataset.objectUrl = URL.createObjectURL(file);
        preview.src = preview.dataset.objectUrl; preview.hidden = false;
        field.querySelector('[data-media-placeholder]').hidden = true;
        field.querySelector('[data-media-remove]').checked = false;
        const gallery = field.querySelector('[data-gallery-value]'); if (gallery) gallery.value = '';
        field.querySelector('[data-media-state]').textContent = file.name + ' · save to publish';
    }));
    document.querySelectorAll('[data-media-remove]').forEach(input => input.addEventListener('change', () => {
        if (!input.checked) return;
        const field = input.closest('[data-media-field]');
        field.querySelector('[data-media-upload]').value = '';
        const gallery = field.querySelector('[data-gallery-value]'); if (gallery) gallery.value = '';
        field.querySelector('[data-media-state]').textContent = 'Media will be removed or reset after saving';
    }));
    document.querySelectorAll('[data-record-selector]').forEach(section => {
        const feedback = section.querySelector('[data-record-feedback]');
        const selected = () => [...section.querySelectorAll('input[type=checkbox]:checked')];
        const update = () => {
            const manual = section.querySelector('[value=manual]').checked;
            section.querySelectorAll('[data-record] input,[data-record-up],[data-record-down]').forEach(input => input.disabled = !manual);
            feedback.textContent = manual ? selected().length + ' of ' + section.dataset.limit + ' selected. Use arrows to change display order.' : 'The website automatically selects published content in its current order.';
        };
        section.addEventListener('change', event => {
            if (event.target.type === 'checkbox' && selected().length > Number(section.dataset.limit)) {
                event.target.checked = false;
                feedback.textContent = 'Select no more than ' + section.dataset.limit + ' items.';
                return;
            }
            update();
        });
        section.addEventListener('click', event => {
            const up = event.target.closest('[data-record-up]'); const down = event.target.closest('[data-record-down]');
            if (!up && !down) return;
            const row = (up || down).closest('[data-record]');
            if (up && row.previousElementSibling) row.previousElementSibling.before(row);
            if (down && row.nextElementSibling) row.nextElementSibling.after(row);
        });
        update();
    });
    // Restore a gallery choice after server-side validation without losing text edits.
    document.querySelectorAll('[data-gallery-value]').forEach(input => {
        const selected = photos.find(photo => photo.dataset.galleryId === input.value);
        if (!selected) return;
        const field = input.closest('[data-media-field]');
        const preview = field.querySelector('[data-media-preview]');
        preview.src = selected.dataset.path; preview.hidden = false;
        field.querySelector('[data-media-placeholder]').hidden = true;
        field.querySelector('[data-media-state]').textContent = 'Selected: ' + selected.dataset.title + ' · save to publish';
    });
})();
