// Recover unfinished package edits in this browser without publishing changes.
document.addEventListener('DOMContentLoaded', async () => {
    const form = document.querySelector('.package-builder');
    if (!form) return;
    const status = form.querySelector('[data-recovery-status]');
    const key = `${form.dataset.recoveryUser}:${new URL(form.action).pathname}`;
    const version = form.dataset.recoveryVersion;
    let db, ready = false, lastSignature = '', saving = Promise.resolve();
    const open = () => new Promise((resolve, reject) => {
        const request = indexedDB.open('serenovia-package-recovery', 1);
        request.onupgradeneeded = () => request.result.createObjectStore('forms');
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
    const read = () => new Promise((resolve, reject) => {
        const request = db.transaction('forms').objectStore('forms').get(key);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
    const write = value => new Promise((resolve, reject) => {
        const tx = db.transaction('forms', 'readwrite');
        tx.objectStore('forms').put(value, key);
        tx.oncomplete = resolve;
        tx.onerror = tx.onabort = () => reject(tx.error || new Error('Recovery storage unavailable'));
    });
    function snapshot() {
        window.tinymce?.editors?.filter(editor => editor.initialized).forEach(editor => editor.save());
        const planIndices = new Map([...form.querySelectorAll('.accommodation-plan-row')].map((row,index) => [row.querySelector('[name]')?.name.match(/accommodation_plan\[(\d+)\]/)?.[1], index]));
        const entries = [...new FormData(form)].map(([name,value]) => [name.replace(/accommodation_plan\[(\d+)\]/, (match,index) => `accommodation_plan[${planIndices.get(index) ?? index}]`), value]).filter(([name, value]) => !['_token', '_method'].includes(name) && !(value instanceof File && !value.name));
        const days = [...form.querySelectorAll('.itinerary-day-card')].map(card => {
            const day = {};
            card.querySelectorAll('[data-day-field]').forEach(input => {
                if (input.type === 'file') return;
                day[input.dataset.dayField] = input.multiple ? [...input.selectedOptions].map(o => o.value) : input.value;
            });
            return day;
        });
        return {entries, days, cover: document.querySelector('#cover-preview img')?.src || '',
            faqCount: form.querySelectorAll('[data-package-faq]').length,
            planCount: form.querySelectorAll('.accommodation-plan-row').length,
            version, time: Date.now()};
    }
    function signature(value) {
        return JSON.stringify([value.entries.map(([k,v]) => [k,v instanceof File ? [v.name,v.size,v.lastModified] : v]),value.days,value.cover,value.faqCount,value.planCount]);
    }
    async function save() {
        if (!ready) return;
        const value = snapshot(), next = signature(value);
        if (next === lastSignature) return saving;
        saving = saving.catch(() => {}).then(() => write(value));
        try {
            await saving;
            lastSignature = next;
            status.textContent = 'Changes backed up in this browser. Use Save package to save to the website.';
        } catch (error) {
            status.textContent = 'Recovery unavailable. Save package before leaving this page.';
            throw error;
        }
    }
    window.savePackageRecovery = () => ready ? save() : Promise.reject(new Error('Form recovery is not available yet'));
    form.inert = true;
    try {
        const saved = await Promise.race([
            (async () => { db = await open(); return read(); })(),
            new Promise((_, reject) => setTimeout(() => reject(new Error('Recovery storage timed out')), 2500)),
        ]);
        let savedAt = 0;
        try { savedAt = Number(localStorage.getItem('package-saved:' + key)) || 0; } catch {}
        if (saved && saved.version === version && saved.time > savedAt && !form.querySelector('.field-error')) {
            form.dispatchEvent(new CustomEvent('restore-package-days', {detail: saved.days}));
            const faqList = form.querySelector('[data-package-faq-list]');
            if (faqList) { faqList.replaceChildren(); for (let i=0;i<saved.faqCount;i++) form.querySelector('[data-add-package-faq]')?.click(); }
            const plans = form.querySelector('#accommodation-plan-list');
            if (plans) {
                plans.replaceChildren();
                for (let i=0;i<saved.planCount;i++) form.querySelector('#add-accommodation-plan')?.click();
                plans.querySelectorAll('.accommodation-plan-row').forEach((row,i) => row.querySelectorAll('[name]').forEach(input => { input.name = input.name.replace(/accommodation_plan\[\d+\]/, `accommodation_plan[${i}]`); }));
            }
            const values = new Map();
            saved.entries.forEach(([name,value]) => { if(!values.has(name)) values.set(name,[]); values.get(name).push(value); });
            [...form.elements].forEach(input => {
                if (!input.name || ['_token','_method'].includes(input.name)) return;
                const items = values.get(input.name) || [];
                if (input.type === 'file') {
                    const transfer = new DataTransfer(); items.filter(v=>v instanceof File).forEach(file=>transfer.items.add(file)); input.files=transfer.files;
                } else if (['checkbox','radio'].includes(input.type)) input.checked = items.includes(input.value);
                else if (input.multiple) [...input.options].forEach(option=>{option.selected=items.includes(option.value)});
                else if (items.length) input.value=items.at(-1);
            });
            form.querySelectorAll('.tinymce-editor').forEach(input => {
                const content = input.value;
                const update = editor => editor.setContent(content);
                const editor = window.tinymce?.get(input.id);
                if (editor?.initialized) update(editor);
                else if (editor) editor.on('init',()=>update(editor));
            });
            const coverFile = form.querySelector('#cover-upload')?.files?.[0];
            const cover = coverFile ? URL.createObjectURL(coverFile) : saved.cover;
            form.dispatchEvent(new CustomEvent('restore-package-cover', {detail:cover}));
            status.textContent = 'Your unfinished changes have been restored. Review them, then Save package.';
        } else status.textContent = 'Form recovery ready. Unfinished changes are backed up in this browser.';
        lastSignature = signature(snapshot());
        ready = true;
    } catch {
        status.textContent = 'Recovery unavailable in this browser. Save package before leaving.';
    } finally { form.inert = false; }
    // Polling also catches gallery selections, map pins and rich editor changes.
    const timer = setInterval(()=>save().catch(()=>{}), 1000);
    form.addEventListener('change',()=>save().catch(()=>{}));
    let submitting = false;
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        // Recovery must never block the native save request, even if browser
        // storage is slow, full or unavailable. Existing backups remain intact.
        submitting = true;
        save().catch(() => {});
        queueMicrotask(() => {
            submitting = !event.defaultPrevented;
            if (submitting) {
                const feedback = form.querySelector('[data-package-save-status]');
                if (feedback) feedback.textContent = 'Saving package…';
            }
        });
    });
    window.addEventListener('pageshow', () => { submitting = false; });
    window.addEventListener('pagehide',()=>{ clearInterval(timer); save().catch(()=>{}); });
    window.addEventListener('beforeunload',event=>{
        if (!submitting && (!ready || signature(snapshot()) !== lastSignature)) { event.preventDefault(); event.returnValue=''; }
    });
});
