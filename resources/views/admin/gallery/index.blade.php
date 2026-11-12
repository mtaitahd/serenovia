@extends('admin.layout')
@php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
@section('title', 'Photo gallery')
@section('content')
    <div class="page-heading gallery-page-heading"><div><span class="eyebrow">YOUR IMAGE COLLECTION</span><h1>Photo gallery</h1><p>Upload, compress and organize the imagery used across your Serenovia website.</p></div><button class="button" type="button" data-open-upload>＋ Add photos</button></div>

    <section class="gallery-manager-tools">
        <div><span class="eyebrow">LIBRARY</span><strong>{{ $images->total() }} photographs</strong></div>
        <label class="gallery-search"><span>⌕</span><input id="gallery-search" type="search" placeholder="Search titles or alt text"></label>
        <select id="gallery-filter"><option value="">Every collection</option>@foreach(['Safari','Wildlife','Culture','Kilimanjaro','Zanzibar','Other'] as $category)<option value="{{ strtolower($category) }}">{{ $category }}</option>@endforeach</select>
    </section>

    @if($images->isEmpty())
        <section class="panel"><div class="empty-state"><strong>Your photo library is ready.</strong>Upload the first images you want to use across the website.</div></section>
    @else
        <div class="gallery-manager-grid" id="gallery-manager-grid">
            @foreach($images as $image)
                <article class="gallery-manager-card" data-title="{{ strtolower(($image->title ?? '').' '.($image->alt_text ?? '')) }}" data-category="{{ strtolower($image->category) }}">
                    <div class="gallery-manager-photo"><img src="{{ asset($image->path) }}" alt="{{ $image->alt_text }}" loading="lazy"><span>{{ $image->category }}</span></div>
                    <div class="gallery-manager-body">
                        <div class="gallery-image-title"><strong>{{ $image->title ?: 'Untitled photograph' }}</strong><small>{{ $image->alt_text ?: 'Add a description for accessibility' }}</small></div>
                        <div class="image-size-line"><span>Original <b>{{ $image->source_bytes ? ($image->source_bytes >= 1048576 ? number_format($image->source_bytes / 1048576, 2).' MB' : number_format($image->source_bytes / 1024, 1).' KB') : '—' }}</b></span><span>WebP <b>{{ $image->display_bytes ? ($image->display_bytes >= 1048576 ? number_format($image->display_bytes / 1048576, 2).' MB' : number_format($image->display_bytes / 1024, 1).' KB') : '—' }}</b></span></div>
                        <button class="button secondary small" type="button" data-open-details="gallery-details-{{ $image->id }}">Edit photo details</button>
                        <dialog class="admin-dialog gallery-details-dialog" id="gallery-details-{{ $image->id }}" aria-label="Edit photo details">
                        <form class="gallery-details-form" method="POST" action="{{ route('admin.gallery.update', $image) }}">
                            @csrf @method('PUT')
                            <div class="dialog-heading"><div><span class="eyebrow">PHOTO LIBRARY</span><h2>Edit photo details</h2><p>Update the title, accessible description and collection.</p></div><button class="dialog-close" type="button" data-close-details aria-label="Close">×</button></div>
                            <label><span>IMAGE TITLE</span><input name="title" value="{{ $image->title }}" placeholder="Image title"></label>
                            <label><span>ALT TEXT</span><input name="alt_text" value="{{ $image->alt_text }}" placeholder="Describe the scene"></label>
                            <label><span>COLLECTION</span><select name="category">@foreach(['Safari','Wildlife','Culture','Kilimanjaro','Zanzibar','Other'] as $category)<option @selected($image->category === $category)>{{ $category }}</option>@endforeach</select></label>
                            <div class="dialog-footer"><span>Changes appear on the website anywhere this image is used.</span><div><button class="button secondary" type="button" data-close-details>Cancel</button><button class="button" type="submit">Save details</button></div></div>
                        </form>
                        </dialog>
                        <div class="gallery-card-actions">
                            <button class="text-button compress-trigger" type="button" data-compress-action="{{ route('admin.gallery.compress', $image) }}" data-image-name="{{ $image->title ?: 'Untitled photograph' }}">Compress image</button>
                            <form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" onsubmit="return confirm('Remove this photo from the gallery?')">@csrf @method('DELETE')<button class="text-button" type="submit">Remove</button></form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="gallery-no-results" id="gallery-no-results" hidden>No photos match these filters.</div>
        <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $images])</div>
    @endif

    <dialog class="admin-dialog gallery-upload-dialog" id="gallery-upload-dialog" aria-labelledby="gallery-upload-title">
        <form id="gallery-upload-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.gallery.store') }}">
            @csrf
            <div class="dialog-heading"><div><span class="eyebrow">GALLERY MANAGER</span><h2 id="gallery-upload-title">Add photographs</h2><p>Upload one or many files. We keep the original safe and optimize a color-faithful WebP copy for the website.</p></div><button type="button" class="dialog-close" data-close-gallery-upload aria-label="Close">×</button></div>
            <div class="gallery-upload-body">
                <div class="upload-dropzone" id="gallery-dropzone" role="button" tabindex="0"><span class="upload-icon">↑</span><strong>Drop photos here</strong><span>or browse your computer · JPG, PNG or WebP · up to 8 MB each</span><input id="gallery-files" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></div>
                <div class="upload-selection-bar"><span id="gallery-selection-count">No photos selected</span><button type="button" class="text-button" id="gallery-clear-selection" hidden>Clear selection</button></div>
                <div class="gallery-upload-previews" id="gallery-upload-previews"></div>
                <div class="gallery-upload-settings">
                    <label class="field"><span class="field-label">COLLECTION</span><select name="category">@foreach(['Safari','Wildlife','Culture','Kilimanjaro','Zanzibar','Other'] as $category)<option>{{ $category }}</option>@endforeach</select></label>
                    <label class="field quality-control"><span class="field-label">WEBP IMAGE QUALITY <b id="gallery-quality-label">84%</b></span><input type="range" name="quality" id="gallery-quality" min="55" max="95" value="84"><small>Higher quality keeps more image detail; the original is always retained.</small></label>
                </div>
                <div class="gallery-upload-progress" id="gallery-upload-progress" hidden><div><span id="gallery-upload-progress-bar"></span></div><small id="gallery-upload-progress-label">Preparing upload…</small></div>
                <p class="gallery-upload-message" id="gallery-upload-message" role="status" aria-live="polite"></p>
            </div>
            <div class="dialog-footer"><span>Images stay in full color; only file size and oversized dimensions are reduced.</span><div><button class="button secondary" type="button" data-close-gallery-upload>Cancel</button><button class="button" id="gallery-upload-submit" type="submit" disabled>Upload photos</button></div></div>
        </form>
    </dialog>

    <dialog class="admin-dialog gallery-compress-dialog" id="gallery-compress-dialog" aria-labelledby="gallery-compress-title">
        <form id="gallery-compress-form" method="POST" action="">
            @csrf
            <div class="dialog-heading"><div><span class="eyebrow">IMAGE OPTIMIZATION</span><h2 id="gallery-compress-title">Compress image</h2><p id="gallery-compress-name"></p></div><button type="button" class="dialog-close" data-close-compress aria-label="Close">×</button></div>
            <div class="compression-options">
                <label class="compression-option"><input type="radio" name="mode" value="quality" checked><span><b>Set quality</b><small>Use a fixed quality while keeping the image crisp.</small></span></label>
                <label class="compression-option"><input type="radio" name="mode" value="filesize"><span><b>Target file size</b><small>Lower quality gradually until the target is reached.</small></span></label>
                <label class="field" id="compression-quality-field"><span class="field-label">QUALITY</span><input type="number" name="quality_value" min="25" max="95" value="80"><small>25–95% · start near 80% for a balanced result.</small></label>
                <label class="field" id="compression-size-field" hidden><span class="field-label">TARGET SIZE</span><input type="number" name="filesize_value" min="10" max="10240" value="350"><small>Target size in KB. If the target is too small, the best readable version is kept.</small></label>
                <p class="compression-preserve-note">Your original is kept unchanged. The photo stays in full color.</p>
                <p class="gallery-upload-message" id="gallery-compress-message" role="status"></p>
            </div>
            <div class="dialog-footer"><span id="gallery-compress-status">Choose a compression setting.</span><div><button class="button secondary" type="button" data-close-compress>Cancel</button><button class="button" type="submit" id="gallery-compress-submit">Compress photo</button></div></div>
        </form>
    </dialog>
@endsection
@push('scripts')
<script src="{{ $publicBasePath }}/js/admin-gallery.js" defer></script>
@endpush

