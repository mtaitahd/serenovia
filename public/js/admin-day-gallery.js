document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('day-gallery-dialog');
    if (!dialog) return;
    const grid = dialog.querySelector('[data-day-gallery-grid]');
    const search = dialog.querySelector('[data-day-gallery-search]');
    const category = dialog.querySelector('[data-day-gallery-category]');
    const status = dialog.querySelector('[data-day-gallery-status]');
    let activeSelect, activeCard, pending = new Set(), capacity = 5, savedCount = 0;
    function remainingSaved(card) {
        return [...card.querySelectorAll('[data-remove-photo]')].filter(input => !input.checked).length;
    }
    function paintCard(card) {
        for (const key of ['gallery_ids', 'transport_gallery_ids']) {
            const select = card.querySelector(`[data-day-field="${key}"]`);
            const preview = card.querySelector(key === 'gallery_ids' ? '[data-day-upload-previews]' : '[data-transport-previews]');
            if (!select || !preview) continue;
            preview.replaceChildren();
            [...select.selectedOptions].forEach(option => {
                const button = document.createElement('button'); button.type = 'button'; button.className = 'day-gallery-preview';
                button.setAttribute('aria-label', `Remove ${option.textContent}`);
                const img = document.createElement('img'); img.src = option.dataset.photoUrl; img.alt = option.textContent;
                const mark = document.createElement('span'); mark.textContent = '×'; button.append(img, mark);
                button.addEventListener('click', () => { option.selected = false; select.dispatchEvent(new Event('change', {bubbles:true})); });
                preview.append(button);
            });
            const total = select.selectedOptions.length + (key === 'gallery_ids' ? remainingSaved(card) : 0);
            card.querySelector(`[data-gallery-count="${key}"]`).textContent = `${total} of 5 photos selected`;
        }
    }
    function filter() {
        let shown = 0;
        for (const item of grid.children) {
            item.hidden = !(item.dataset.title.includes(search.value.trim().toLowerCase()) && (!category.value || item.dataset.category === category.value));
            if (!item.hidden) shown++;
        }
        dialog.querySelector('[data-day-gallery-empty]').hidden = shown > 0;
    }
    function paintSelection(message = '') {
        for (const item of grid.children) {
            const selected = pending.has(item.dataset.id);
            item.classList.toggle('is-selected', selected); item.setAttribute('aria-pressed', String(selected));
        }
        status.textContent = message || `${pending.size + savedCount} of 5 photos selected${savedCount ? ` (${savedCount} already saved)` : ''}`;
    }
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-open-day-gallery]');
        if (!trigger) return;
        activeCard = trigger.closest('.itinerary-day-card');
        activeSelect = activeCard.querySelector(`[data-day-field="${trigger.dataset.openDayGallery}"]`);
        pending = new Set([...activeSelect.selectedOptions].map(option => option.value));
        savedCount = trigger.dataset.openDayGallery === 'gallery_ids' ? remainingSaved(activeCard) : 0;
        capacity = Math.max(0, 5 - savedCount);
        dialog.querySelector('h2').textContent = trigger.dataset.openDayGallery === 'gallery_ids' ? (activeCard.id === 'stay-photo-picker' ? 'Choose stay photographs' : 'Choose day photographs') : 'Choose transport photographs';
        category.replaceChildren(new Option('All collections', '')); search.value = ''; grid.replaceChildren();
        const categories = [...new Set([...activeSelect.options].map(option => option.dataset.category).filter(Boolean))].sort();
        categories.forEach(value => category.add(new Option(value,value)));
        if (trigger.dataset.openDayGallery === 'transport_gallery_ids') category.value = categories.find(value => /transport|vehicle|car/i.test(value)) || '';
        if (activeCard.id === 'stay-photo-picker') category.value = categories.includes('Accommodation') ? 'Accommodation' : '';
        for (const option of activeSelect.options) {
            const button = document.createElement('button'); button.type = 'button'; button.className = 'gallery-picker-item';
            button.dataset.id = option.value; button.dataset.title = option.textContent.toLowerCase(); button.dataset.category = option.dataset.category || '';
            const img = document.createElement('img'); img.src = option.dataset.photoUrl; img.alt = ''; img.loading = 'lazy';
            const label = document.createElement('span'); label.textContent = option.textContent;
            const tick = document.createElement('b'); tick.className = 'day-gallery-tick'; tick.textContent = '✓'; tick.setAttribute('aria-hidden', 'true');
            button.append(img, label, tick);
            button.addEventListener('click', () => {
                if (pending.has(option.value)) pending.delete(option.value);
                else if (pending.size < capacity) pending.add(option.value);
                else { paintSelection('Maximum five photos. Remove a selected or saved photo first.'); return; }
                paintSelection();
            });
            grid.append(button);
        }
        dialog.querySelector('.dialog-heading p').textContent = 'Select up to five photographs.';
        paintSelection(); filter(); dialog.showModal();
    });
    dialog.querySelectorAll('[data-day-gallery-cancel]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    dialog.querySelector('[data-day-gallery-confirm]').addEventListener('click', () => {
        for (const option of activeSelect.options) option.selected = pending.has(option.value);
        activeSelect.dispatchEvent(new Event('change', {bubbles:true})); dialog.close();
    });
    search.addEventListener('input', filter); category.addEventListener('change', filter);
    document.addEventListener('change', event => {
        const card = event.target.closest('.itinerary-day-card');
        if (card && (event.target.matches('[data-day-field="gallery_ids"], [data-day-field="transport_gallery_ids"], [data-remove-photo]'))) paintCard(card);
    });
    document.querySelectorAll('#stay-photo-picker').forEach(paintCard);
    const list = document.getElementById('itinerary-day-list');
    if (list) {
        new MutationObserver(() => list.querySelectorAll('.itinerary-day-card').forEach(paintCard)).observe(list, {childList:true});
        list.querySelectorAll('.itinerary-day-card').forEach(paintCard);
    }
});
