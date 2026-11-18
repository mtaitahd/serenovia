<dialog class="admin-dialog gallery-picker-dialog day-gallery-dialog" id="day-gallery-dialog" aria-labelledby="day-gallery-title">
    <div class="dialog-heading"><div><span class="eyebrow">YOUR PHOTO LIBRARY</span><h2 id="day-gallery-title">Choose photographs</h2><p>Select up to five photos for this day.</p></div><button type="button" class="dialog-close" data-day-gallery-cancel aria-label="Close">×</button></div>
    <div class="gallery-picker-tools"><input type="search" data-day-gallery-search placeholder="Search photographs" aria-label="Search photographs"><select data-day-gallery-category aria-label="Filter by collection"><option value="">All collections</option></select><a class="button secondary" href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Manage gallery ↗</a></div>
    <p data-day-gallery-status role="status" aria-live="polite"></p>
    <div class="gallery-picker-grid" data-day-gallery-grid></div>
    <p data-day-gallery-empty hidden>No photos match this search. Add photos in Gallery, then save your package and refresh to load them.</p>
    <div class="dialog-footer"><button type="button" class="button secondary" data-day-gallery-cancel>Cancel</button><button type="button" class="button" data-day-gallery-confirm>Use selected photos</button></div>
</dialog>
