document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-category-choice]').forEach(select => {
        const input = select.parentElement.querySelector('[data-new-category]');
        const toggle = () => {
            const adding = select.value === '__new__';
            input.hidden = !adding; input.disabled = !adding; input.required = adding;
            if (adding) input.focus();
        };
        select.addEventListener('change', toggle); toggle();
    });
    const search = document.getElementById('gallery-search');
    const categoryFilter = document.getElementById('gallery-filter');
    const cards = [...document.querySelectorAll('.gallery-manager-card')];
    const noResults = document.getElementById('gallery-no-results');

    document.querySelectorAll('[data-open-details]').forEach(button => button.addEventListener('click', () => {
        document.getElementById(button.dataset.openDetails)?.showModal();
    }));
    document.querySelectorAll('.gallery-details-dialog [data-close-details]').forEach(button => button.addEventListener('click', () => {
        button.closest('dialog')?.close();
    }));
    document.querySelectorAll('.gallery-details-dialog').forEach(dialog => dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    }));

    function filterCards() {
        const term = (search?.value || '').trim().toLocaleLowerCase();
        const category = (categoryFilter?.value || '').toLocaleLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const matches = card.dataset.title.includes(term) && (!category || card.dataset.category === category);
            card.hidden = !matches;
            if (matches) visible++;
        });
        if (noResults) noResults.hidden = visible > 0;
    }
    search?.addEventListener('input', filterCards);
    categoryFilter?.addEventListener('change', filterCards);

    const uploadDialog = document.getElementById('gallery-upload-dialog');
    const uploadForm = document.getElementById('gallery-upload-form');
    const fileInput = document.getElementById('gallery-files');
    const dropzone = document.getElementById('gallery-dropzone');
    const previews = document.getElementById('gallery-upload-previews');
    const count = document.getElementById('gallery-selection-count');
    const submit = document.getElementById('gallery-upload-submit');
    const clearButton = document.getElementById('gallery-clear-selection');
    const message = document.getElementById('gallery-upload-message');
    const progressWrap = document.getElementById('gallery-upload-progress');
    const progressBar = document.getElementById('gallery-upload-progress-bar');
    const progressLabel = document.getElementById('gallery-upload-progress-label');
    const quality = document.getElementById('gallery-quality');
    const qualityLabel = document.getElementById('gallery-quality-label');
    let selectedFiles = [];
    let previewUrls = [];
    let isUploading = false;
    let uploadedSuccessfully = false;

    function updateFiles(files) {
        const incoming = [...files];
        const available = Math.max(0, 20 - selectedFiles.length);
        selectedFiles.push(...incoming.slice(0, available));
        if (incoming.length > available) message.textContent = 'You can upload up to 20 photos at a time.';
        const transfer = new DataTransfer();
        selectedFiles.forEach(file => transfer.items.add(file));
        fileInput.files = transfer.files;
        renderPreviews();
    }

    function renderPreviews() {
        previewUrls.forEach(url => URL.revokeObjectURL(url));
        previewUrls = [];
        previews.replaceChildren();
        selectedFiles.forEach((file, index) => {
            const tile = document.createElement('article');
            tile.className = 'gallery-upload-preview';
            const image = document.createElement('img');
            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            image.src = url;
            image.alt = file.name;
            const name = document.createElement('span');
            name.textContent = file.name;
            const size = document.createElement('small');
            size.textContent = file.size >= 1048576 ? `${(file.size / 1048576).toFixed(1)} MB` : `${Math.ceil(file.size / 1024)} KB`;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'preview-remove';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `Remove ${file.name}`);
            remove.disabled = isUploading;
            remove.addEventListener('click', () => {
                if (isUploading) return;
                selectedFiles.splice(index, 1);
                const transfer = new DataTransfer();
                selectedFiles.forEach(item => transfer.items.add(item));
                fileInput.files = transfer.files;
                renderPreviews();
            });
            tile.append(image, remove, name, size);
            previews.append(tile);
        });
        count.textContent = selectedFiles.length ? `${selectedFiles.length} photo${selectedFiles.length === 1 ? '' : 's'} selected` : 'No photos selected';
        submit.disabled = isUploading || !selectedFiles.length;
        clearButton.hidden = !selectedFiles.length || isUploading;
    }

    document.querySelector('[data-open-upload]')?.addEventListener('click', () => uploadDialog.showModal());
    document.querySelectorAll('[data-close-gallery-upload]').forEach(button => button.addEventListener('click', () => {
        if (!isUploading) uploadDialog.close();
    }));
    uploadDialog?.addEventListener('click', event => { if (event.target === uploadDialog && !isUploading) uploadDialog.close(); });
    uploadDialog?.addEventListener('close', () => {
        if (uploadedSuccessfully) window.location.reload();
    });
    dropzone?.addEventListener('click', event => { if (event.target !== fileInput && !isUploading) fileInput.click(); });
    dropzone?.addEventListener('keydown', event => {
        if ((event.key === 'Enter' || event.key === ' ') && !isUploading) { event.preventDefault(); fileInput.click(); }
    });
    fileInput?.addEventListener('change', () => updateFiles(fileInput.files));
    clearButton?.addEventListener('click', () => { selectedFiles = []; fileInput.value = ''; renderPreviews(); });
    dropzone?.addEventListener('dragover', event => { event.preventDefault(); dropzone.classList.add('is-over'); });
    dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('is-over'));
    dropzone?.addEventListener('drop', event => {
        event.preventDefault();
        dropzone.classList.remove('is-over');
        if (!isUploading) updateFiles(event.dataTransfer.files);
    });
    quality?.addEventListener('input', () => { qualityLabel.textContent = `${quality.value}%`; });

    uploadForm?.addEventListener('submit', event => {
        event.preventDefault();
        if (isUploading || !selectedFiles.length) return;
        isUploading = true;
        message.textContent = '';
        progressWrap.hidden = false;
        progressBar.style.width = '0%';
        progressLabel.textContent = 'Preparing images…';
        submit.textContent = 'Uploading…';
        renderPreviews();
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadForm.action);
        xhr.timeout = 180000;
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.addEventListener('progress', event => {
            if (!event.lengthComputable) return;
            const percent = Math.round(event.loaded / event.total * 100);
            progressBar.style.width = `${percent}%`;
            progressLabel.textContent = percent < 100 ? `Uploading photos · ${percent}%` : 'Optimizing images…';
        });
        xhr.onload = () => {
            let result = {};
            try { result = JSON.parse(xhr.responseText); } catch { /* show the response below */ }
            if (xhr.status >= 200 && xhr.status < 300 && result.success) {
                uploadedSuccessfully = true;
                isUploading = false;
                progressBar.style.width = '100%';
                progressLabel.textContent = 'Upload and image optimization complete.';
                message.textContent = result.message;
                message.classList.add('is-success');
                submit.textContent = 'Done · close this window';
                submit.disabled = false;
                submit.onclick = () => uploadDialog.close();
                document.querySelectorAll('[data-close-gallery-upload]').forEach(button => button.disabled = false);
            } else {
                const errors = Object.values(result.errors || {}).flat().join(' ');
                message.textContent = errors || result.message || `Upload failed (${xhr.status}).`;
                isUploading = false;
                submit.textContent = 'Try upload again';
                renderPreviews();
            }
        };
        xhr.onerror = () => { message.textContent = 'Connection failed. Your selected photos are still here; please try again.'; isUploading = false; submit.textContent = 'Try upload again'; renderPreviews(); };
        xhr.ontimeout = () => { message.textContent = 'Upload timed out. Check the gallery before retrying.'; isUploading = false; submit.textContent = 'Try upload again'; renderPreviews(); };
        xhr.send(new FormData(uploadForm));
    });

    const compressDialog = document.getElementById('gallery-compress-dialog');
    const compressForm = document.getElementById('gallery-compress-form');
    const qualityField = document.getElementById('compression-quality-field');
    const sizeField = document.getElementById('compression-size-field');
    const compressStatus = document.getElementById('gallery-compress-status');
    document.querySelectorAll('.compress-trigger').forEach(button => button.addEventListener('click', () => {
        compressForm.action = button.dataset.compressAction;
        document.getElementById('gallery-compress-name').textContent = button.dataset.imageName;
        compressDialog.showModal();
    }));
    document.querySelectorAll('[data-close-compress]').forEach(button => button.addEventListener('click', () => compressDialog.close()));
    compressDialog?.addEventListener('click', event => { if (event.target === compressDialog) compressDialog.close(); });
    document.querySelectorAll('input[name="mode"]').forEach(radio => radio.addEventListener('change', () => {
        const bySize = document.querySelector('input[name="mode"]:checked').value === 'filesize';
        qualityField.hidden = bySize;
        sizeField.hidden = !bySize;
        compressStatus.textContent = bySize ? 'The image will be optimized toward the selected file size.' : 'The image will be encoded at this fixed quality.';
    }));
    compressForm?.addEventListener('submit', () => { compressStatus.textContent = 'Optimizing from the untouched original…'; });

    renderPreviews();
});
