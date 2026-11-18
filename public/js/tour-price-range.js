document.querySelectorAll('[data-price-filter]').forEach(filter => {
    const bounds = JSON.parse(filter.dataset.bounds);
    const slider = filter.querySelector('.catalogue-price-slider');
    const currency = filter.querySelector('[name=price_currency]');
    const fields = [...filter.querySelectorAll('[data-range-field]')];
    const thumbs = [...slider.querySelectorAll('input')];
    if (fields.length !== 2) return;
    const unit = () => currency?.value || filter.dataset.unit;

    function sync() {
        const { min, max } = bounds[unit()];
        slider.hidden = false;
        slider.classList.toggle('is-unavailable', max <= min);
        const values = fields.map((field, index) => field.value === '' ? [min, max][index] : Math.min(max, Math.max(min, Number(field.value)))).sort((a, b) => a - b);
        thumbs.forEach((thumb, index) => {
            thumb.min = min;
            thumb.max = max;
            thumb.value = values[index];
            thumb.disabled = max <= min;
            thumb.setAttribute('aria-valuetext', values[index] + ' ' + unit());
            fields[index].placeholder = [min, max][index];
            slider.style.setProperty(index ? '--price-end' : '--price-start', ((values[index] - min) / (max - min || 1) * 100) + '%');
        });
        slider.classList.toggle('min-on-top', values[0] === max);
    }

    thumbs.forEach((thumb, index) => thumb.addEventListener('input', () => {
        const other = Number(thumbs[1 - index].value);
        fields[index].value = index ? Math.max(other, Number(thumb.value)) : Math.min(other, Number(thumb.value));
        sync();
    }));
    fields.forEach(field => field.addEventListener('input', sync));
    currency?.addEventListener('change', () => {
        fields.forEach(field => { field.value = ''; });
        sync();
    });
    sync();
});
