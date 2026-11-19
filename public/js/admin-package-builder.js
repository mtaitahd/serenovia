document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.package-builder');
    if (!form) return;

    const panels = [...form.querySelectorAll('[data-builder-step]')];
    const steps = [...form.querySelectorAll('[data-step-jump]')];
    const progress = document.getElementById('builder-progress-fill');
    const progressLabel = document.getElementById('builder-progress-label');
    const stepTitle = document.getElementById('builder-step-title');
    const titles = ['Package basics', 'Pricing & details', 'Story & route', 'Review & publish'];
    let currentStep = 1;

    function showStep(step) {
        currentStep = Math.max(1, Math.min(4, Number(step)));
        panels.forEach(panel => { panel.hidden = Number(panel.dataset.builderStep) !== currentStep; });
        steps.forEach(button => {
            const number = Number(button.dataset.stepJump);
            button.classList.toggle('is-current', number === currentStep);
            button.classList.toggle('is-complete', number < currentStep);
            if (number === currentStep) button.setAttribute('aria-current', 'step');
            else button.removeAttribute('aria-current');
        });
        const percent = currentStep * 25;
        progress.style.width = `${percent}%`;
        progressLabel.textContent = `${percent}% complete`;
        stepTitle.textContent = `Step ${currentStep} of 4 · ${titles[currentStep - 1]}`;
        if (currentStep === 4) updateReview();
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    form.querySelectorAll('.builder-next').forEach(button => button.addEventListener('click', () => {
        const panel = form.querySelector(`[data-builder-step="${currentStep}"]`);
        const invalid = [...panel.querySelectorAll('input,select,textarea')].find(input => !input.checkValidity());
        if (invalid) {
            revealDetails(invalid);
            invalid.reportValidity();
            invalid.focus();
            return;
        }
        showStep(button.dataset.nextStep);
    }));
    form.querySelectorAll('.builder-back').forEach(button => button.addEventListener('click', () => showStep(button.dataset.previousStep)));
    steps.forEach(button => button.addEventListener('click', () => showStep(button.dataset.stepJump)));

    function revealDetails(element) {
        for (let parent = element.parentElement; parent && parent !== form; parent = parent.parentElement) {
            if (parent.tagName === 'DETAILS') parent.open = true;
        }
    }
    form.addEventListener('invalid', event => {
        const panel = event.target.closest('[data-builder-step]');
        if (panel) showStep(panel.dataset.builderStep);
        revealDetails(event.target);
    }, true);
    const firstInvalid = form.querySelector('.field-error');
    if (firstInvalid) {
        revealDetails(firstInvalid);
        const failedPanel = firstInvalid.closest('[data-builder-step]');
        if (failedPanel) showStep(failedPanel.dataset.builderStep);
    }

    const dialog = document.getElementById('gallery-picker-dialog');
    const hiddenImageId = document.getElementById('gallery_image_id');
    const fileInput = document.getElementById('cover-upload');
    const preview = document.getElementById('cover-preview');
    let pendingImage = null;

    function paintPreview(url, alt) {
        preview.replaceChildren();
        if (!url) {
            const hint = document.createElement('span');
            hint.textContent = 'Choose a photo that captures this journey';
            preview.append(hint);
            return;
        }
        const image = document.createElement('img');
        image.src = url;
        image.alt = alt || 'Selected safari cover';
        preview.append(image);
        const reviewCover = document.getElementById('review-cover');
        reviewCover.replaceChildren(image.cloneNode());
    }

    document.querySelector('[data-open-gallery-picker]')?.addEventListener('click', () => {
        pendingImage = null;
        document.querySelectorAll('.gallery-picker-item').forEach(item => item.classList.toggle('is-selected', item.dataset.imageId === hiddenImageId.value));
        document.getElementById('gallery-picker-selection').textContent = hiddenImageId.value ? 'Current cover selected' : 'Choose one photo';
        document.getElementById('gallery-picker-confirm').disabled = !hiddenImageId.value;
        dialog.showModal();
    });
    document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog?.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });

    document.querySelectorAll('.gallery-picker-item').forEach(item => item.addEventListener('click', () => {
        pendingImage = item;
        document.querySelectorAll('.gallery-picker-item').forEach(card => card.classList.toggle('is-selected', card === item));
        document.getElementById('gallery-picker-selection').textContent = item.dataset.imageName;
        document.getElementById('gallery-picker-confirm').disabled = false;
    }));
    document.getElementById('gallery-picker-confirm')?.addEventListener('click', () => {
        if (!pendingImage) return;
        hiddenImageId.value = pendingImage.dataset.imageId;
        fileInput.value = '';
        paintPreview(pendingImage.dataset.imageUrl, pendingImage.dataset.imageName);
        dialog.close();
    });

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (!file) return;
        hiddenImageId.value = '';
        const reader = new FileReader();
        reader.onload = () => paintPreview(reader.result, file.name);
        reader.readAsDataURL(file);
    });

    function filterGallery() {
        const term = document.getElementById('gallery-picker-search').value.trim().toLocaleLowerCase();
        const category = document.getElementById('gallery-picker-category').value.toLocaleLowerCase();
        let shown = 0;
        document.querySelectorAll('.gallery-picker-item').forEach(item => {
            const matches = `${item.dataset.imageName} ${item.dataset.imageCategory}`.toLocaleLowerCase().includes(term)
                && (!category || item.dataset.imageCategory.toLocaleLowerCase() === category);
            item.hidden = !matches;
            if (matches) shown++;
        });
        const empty = document.getElementById('gallery-picker-empty');
        if (empty) empty.hidden = shown > 0;
    }
    document.getElementById('gallery-picker-search')?.addEventListener('input', filterGallery);
    document.getElementById('gallery-picker-category')?.addEventListener('change', filterGallery);

    function overviewText() {
        const html = window.tinymce?.get('description')?.getContent() || document.getElementById('description').value;
        const documentText = new DOMParser().parseFromString(html.replace(/<[^>]+>/g, ' '), 'text/html');
        return documentText.body.textContent.replace(/\s+/g, ' ').trim();
    }
    function updateReview() {
        document.getElementById('review-title').textContent = document.getElementById('title').value || 'Your safari package';
        document.getElementById('review-summary').textContent = document.getElementById('summary').value.trim() || overviewText().slice(0, 250) || 'Add the tour overview in Itinerary.';
        document.getElementById('review-duration').textContent = `${document.getElementById('duration_days').value || 1} days · ${document.getElementById('duration_nights').value || 0} nights`;
        const image = document.getElementById('cover-preview').querySelector('img');
        const reviewCover = document.getElementById('review-cover');
        if (image) reviewCover.replaceChildren(image.cloneNode());
        else reviewCover.replaceChildren();
    }
    form.querySelectorAll('#title,#summary,#description,#duration_days,#duration_nights').forEach(input => input.addEventListener('input', updateReview));

    const planList = document.getElementById('accommodation-plan-list');
    const planTemplate = document.getElementById('accommodation-plan-template');
    const dayCountInput = document.getElementById('duration_days');
    const itineraryList = document.getElementById('itinerary-day-list');
    const itineraryTemplate = document.getElementById('itinerary-day-template');
    const itineraryInput = document.getElementById('itinerary');
    let planRowIndex = planList?.querySelectorAll('.accommodation-plan-row').length || 0;

    function currentDayCount() {
        return Math.max(1, Math.min(365, Number(dayCountInput?.value) || 1));
    }

    function refreshPlanDayOptions(select, preferred) {
        const days = currentDayCount();
        let configuredDays = [];
        try { configuredDays = JSON.parse(select.dataset.dayOptions || '[]'); } catch { configuredDays = []; }
        const options = [...new Set(configuredDays.map(Number).filter(day => Number.isInteger(day) && day >= 1 && day <= days))].sort((a, b) => a - b);
        const selected = options.includes(Number(preferred || select.value)) ? Number(preferred || select.value) : (options[0] || '');
        select.replaceChildren();
        if (!options.length) select.add(new Option('Add package days in admin', '', true, true));
        options.forEach(day => {
            const option = new Option(`Day ${day}`, String(day), false, day === selected);
            select.add(option);
        });
    }

    function itineraryTextLines(source) {
        const scratch = document.createElement('div');
        scratch.innerHTML = source || '';
        scratch.querySelectorAll('br').forEach(node => node.replaceWith('\n'));
        scratch.querySelectorAll('p,h1,h2,h3,h4,h5,h6,li,div').forEach(node => node.append('\n'));
        return (scratch.textContent || '').replace(/\r/g, '').split('\n').map(line => line.trim()).filter(Boolean);
    }

    function readInitialItinerary() {
        try { const saved = JSON.parse(itineraryList?.dataset.initialDays || '[]'); if (Array.isArray(saved) && saved.length) return saved; } catch {}
        const lines = itineraryTextLines(itineraryList?.dataset.initialItinerary || '');
        const days = [];
        lines.forEach(line => {
            const match = line.match(/^day\s*(\d+)\s*(?:[:\-–—]\s*)?(.*)$/i);
            if (match) {
                const number = Math.max(1, Number(match[1]) || days.length + 1);
                while (days.length < number) days.push({ title: '', details: '' });
                days[number - 1].title = match[2].trim();
            } else if (days.length) {
                days[days.length - 1].details += `${days[days.length - 1].details ? '\n' : ''}${line}`;
            } else {
                days.push({ title: 'Itinerary notes', details: line });
            }
        });
        return days;
    }

    function itineraryCards() {
        return [...(itineraryList?.querySelectorAll('.itinerary-day-card') || [])];
    }

    function renumberItineraryDays() {
        itineraryCards().forEach((card, index) => {
            const number = index + 1;
            card.querySelectorAll('[data-day-field]').forEach(input => { input.id = "itinerary-day-" + number + "-" + input.dataset.dayField; if(input.previousElementSibling?.tagName === 'LABEL') input.previousElementSibling.htmlFor = input.id; input.name = `itinerary_days[${index}][${input.dataset.dayField}]${input.hasAttribute('data-day-array') ? '[]' : ''}`; });
            card.querySelectorAll('[data-remove-photo]').forEach(input => { input.name = `itinerary_days[${index}][remove_images][]`; });
            card.querySelector('[data-day-number]').textContent = `DAY ${String(number).padStart(2, '0')}`;
            card.querySelector('[data-day-title-label]').textContent = `DAY ${number} TITLE / LOCATION`;
            card.querySelector('[data-day-details-label]').textContent = `DAY ${number} ACTIVITIES & NOTES`;
        });
    }

    function addItineraryDay(day = {}) {
        if (!itineraryList || !itineraryTemplate) return;
        const fragment = itineraryTemplate.content.cloneNode(true);
        const card = fragment.querySelector('.itinerary-day-card');
        card.querySelector('[data-itinerary-title]').value = day.title || '';
        card.querySelector('[data-itinerary-details]').value = day.details || '';
        window.setupItineraryDayMedia?.(card, day, itineraryList);
        card.querySelector('[data-remove-itinerary-day]').addEventListener('click', () => {
            const cards = itineraryCards();
            if (cards.length <= 1) {
                cards[0]?.querySelector('[data-itinerary-title]').focus();
                return;
            }
            const removedIndex = cards.indexOf(card) + 1;
            card.remove();
            const count = itineraryCards().length;
            dayCountInput.value = count;
            dayCountInput.dispatchEvent(new Event('change', { bubbles: true }));
            planList?.querySelectorAll('[data-plan-day]').forEach(select => {
                const oldDay = Number(select.value) || 1;
                refreshPlanDayOptions(select, oldDay > removedIndex ? oldDay - 1 : Math.min(oldDay, count));
            });
            renumberItineraryDays();
            updateReview();
        });
        itineraryList.append(fragment);
        renumberItineraryDays();
    }

    function syncItineraryDayCount() {
        if (!itineraryList) return;
        const days = currentDayCount();
        while (itineraryCards().length < days) addItineraryDay();
        while (itineraryCards().length > days && itineraryCards().length > 1) itineraryCards().at(-1).remove();
        renumberItineraryDays();
    }

    function escapeHtml(value) {
        return value.replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character]);
    }

    function serializeItinerary() {
        if (!itineraryInput) return;
        itineraryInput.value = itineraryCards().map((card, index) => {
            const title = card.querySelector('[data-itinerary-title]').value.trim();
            const details = card.querySelector('[data-itinerary-details]').value.trim();
            if (!title && !details) return '';
            const heading = `<h3>Day ${index + 1}${title ? ` — ${escapeHtml(title)}` : ''}</h3>`;
            const body = details ? `<p>${escapeHtml(details).replace(/\n/g, '<br>')}</p>` : '';
            return `${heading}${body}`;
        }).filter(Boolean).join('\n');
    }

    const initialDays = readInitialItinerary();
    const initialCount = Math.max(currentDayCount(), initialDays.length, 1);
    dayCountInput.value = initialCount;
    for (let index = 0; index < initialCount; index++) addItineraryDay(initialDays[index] || {});
    document.getElementById('add-itinerary-day')?.addEventListener('click', () => {
        const count = itineraryCards().length + 1;
        addItineraryDay();
        dayCountInput.value = count;
        dayCountInput.dispatchEvent(new Event('change', { bubbles: true }));
        updateReview();
    });
    dayCountInput?.addEventListener('change', syncItineraryDayCount);
    form.addEventListener('submit', serializeItinerary);

    function syncAccommodationTier(row) {
        const tier = row.querySelector('[data-plan-tier]') || row.querySelector('select[name$="[tier]"]');
        const stay = row.querySelector('[data-plan-stay]') || row.querySelector('select[name$="[accommodation_id]"]');
        if (!tier || !stay) return;
        const selected = stay.value;
        [...stay.options].forEach(option => { option.hidden = Boolean(option.dataset.tier && option.dataset.tier !== tier.value); });
        if (selected && stay.selectedOptions[0]?.hidden) stay.value = '';
    }

    planList?.querySelectorAll('[data-plan-day]').forEach(select => refreshPlanDayOptions(select, select.value));
    planList?.querySelectorAll('.accommodation-plan-row').forEach(syncAccommodationTier);
    dayCountInput?.addEventListener('change', () => planList?.querySelectorAll('[data-plan-day]').forEach(select => refreshPlanDayOptions(select, select.value)));

    document.getElementById('add-accommodation-plan')?.addEventListener('click', () => {
        if (!planList || !planTemplate) return;
        const fragment = planTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.accommodation-plan-row');
        const day = row.querySelector('[data-plan-day]');
        const tier = row.querySelector('[data-plan-tier]');
        const stay = row.querySelector('[data-plan-stay]');
        day.name = `accommodation_plan[${planRowIndex}][day_number]`;
        tier.name = `accommodation_plan[${planRowIndex}][tier]`;
        stay.name = `accommodation_plan[${planRowIndex}][accommodation_id]`;
        refreshPlanDayOptions(day, 1);
        syncAccommodationTier(row);
        row.querySelector('[data-remove-plan-row]').addEventListener('click', () => row.remove());
        planList.append(fragment);
        planRowIndex++;
    });
    planList?.querySelectorAll('[data-remove-plan-row]').forEach(button => button.addEventListener('click', () => button.closest('.accommodation-plan-row')?.remove()));
    planList?.querySelectorAll('.accommodation-plan-row select[name$="[tier]"]').forEach(select => select.addEventListener('change', () => syncAccommodationTier(select.closest('.accommodation-plan-row'))));

    showStep(firstInvalid?.closest('[data-builder-step]')?.dataset.builderStep || 1);
});
