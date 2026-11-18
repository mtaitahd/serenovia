@extends('admin.layout')
@section('title', 'Homepage · '.$definition['label'])
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-homepage.css') }}?v=1">@endpush
@push('scripts')<script src="{{ asset('js/admin-homepage.js') }}?v=1" defer></script>@endpush
@section('content')
<div class="page-heading hp-page-heading">
    <div><span class="eyebrow">WEBSITE / HOMEPAGE</span><h1>Make it your Serenovia.</h1><p>Edit the content you see on the homepage, one section at a time.</p></div>
    <a class="button button-light" href="{{ route('home') }}#{{ $definition['anchor'] }}" target="_blank" rel="noopener">View this section ↗</a>
</div>
<div class="hp-editor">
    <nav class="hp-section-nav" aria-label="Homepage sections">
        <span class="hp-nav-caption">ON YOUR HOMEPAGE</span>
        @foreach($sections as $key => $item)
            <a href="{{ route('admin.website-content.homepage', ['section' => $key]) }}" @class(['is-current' => $key === $section]) @if($key === $section) aria-current="page" @endif><small>{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</small>{{ $item['label'] }}<span>›</span></a>
        @endforeach
        <a href="{{ route('admin.website-content.story-images') }}" data-admin-popup>Our Story · photos & captions ↗</a>
        <a href="{{ route('admin.website-content.footer') }}" data-admin-popup>Footer text & links ↗</a>
        <a href="{{ route('admin.settings.edit') }}" data-admin-popup>Brand & contact details ↗</a>
    </nav>
    <div class="hp-editor-main">
        <div class="hp-section-heading"><span class="hp-status">LIVE CONTENT</span><h2>{{ $definition['label'] }}</h2><p>{{ $definition['description'] }}</p></div>
        @if($errors->any())<div class="notice error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="admin-form hp-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.website-content.homepage.update') }}">
            @csrf @method('PUT')<input type="hidden" name="section" value="{{ $section }}">
            <div class="form-grid">
            @foreach($definition['fields'] as $key => $field)
                @php $value = old('content.'.$key, $home[$key]); @endphp
                <div @class(['field', 'full' => in_array($field['type'], ['image','video','textarea','title','toggle'])])>
                    @if(in_array($field['type'], ['image','video']))
                        @php $media = $home[$key]; @endphp
                        <fieldset class="hp-media-field" data-media-field="{{ $key }}" data-media-kind="{{ $field['type'] }}">
                            <legend>{{ $field['label'] }}</legend>
                            <div class="hp-media-layout">
                                <div class="hp-media-preview">
                                    @if($field['type'] === 'video')
                                        <video data-media-preview controls preload="metadata" @if($media) src="{{ asset($media) }}" @endif poster="{{ asset($home['hero_image']) }}"></video>
                                    @else
                                        <img data-media-preview @if($media) src="{{ asset($media) }}" @else hidden @endif alt="{{ $field['label'] }}">
                                    @endif
                                    <span data-media-placeholder @if($media) hidden @endif>{{ $key === 'home_faq_image' ? '3D question mark is currently displayed' : 'No media selected' }}</span>
                                </div>
                                <div class="hp-media-actions">
                                    <span class="hp-media-state" data-media-state>Currently used on the website</span>
                                    @if($field['type'] === 'image')
                                        <input type="hidden" data-gallery-value name="gallery[{{ $key }}]" value="{{ old('gallery.'.$key) }}">
                                        <button class="button button-light" type="button" data-select-gallery>Select from gallery</button>
                                    @endif
                                    <label for="upload-{{ $key }}">{{ $field['type'] === 'video' ? 'Replace video' : 'Or upload an image' }}</label>
                                    <input id="upload-{{ $key }}" type="file" name="uploads[{{ $key }}]" accept="{{ $field['type'] === 'video' ? 'video/mp4,video/webm' : 'image/jpeg,image/png,image/webp' }}" data-media-upload>
                                    <small>{{ $field['type'] === 'video' ? 'MP4 or WebM, up to 50 MB. The hosting upload limit also applies ('.ini_get('upload_max_filesize').'). Update the credit below when replacing the video.' : 'JPG, PNG or WebP, up to 8 MB. The original gallery photo is preserved.' }}</small>
                                    <label class="hp-remove"><input type="checkbox" data-media-remove name="remove[{{ $key }}]" value="1" @checked(old('remove.'.$key))>{{ in_array($key, ['hero_image','site_logo']) ? 'Reset to original image' : 'Remove this media' }}</label>
                                    @if($media)<a href="{{ asset($media) }}" target="_blank" rel="noopener" class="hp-current-link">Open current {{ $field['type'] }} ↗</a>@endif
                                </div>
                            </div>
                            @error('uploads.'.$key)<small class="field-error">{{ $message }}</small>@enderror
                            @error('gallery.'.$key)<small class="field-error">{{ $message }}</small>@enderror
                        </fieldset>
                    @elseif($field['type'] === 'toggle')
                        <input type="hidden" name="content[{{ $key }}]" value="0">
                        <label class="hp-toggle"><input type="checkbox" name="content[{{ $key }}]" value="1" @checked((string)$value === '1')><span>{{ $field['label'] }}</span></label>
                    @else
                        <label for="home-field-{{ $key }}">{{ $field['label'] }}</label>
                        @if(in_array($field['type'], ['textarea','title']))
                            <textarea id="home-field-{{ $key }}" name="content[{{ $key }}]" rows="{{ $field['type'] === 'title' ? 2 : 4 }}" maxlength="{{ $field['type'] === 'title' ? 255 : 5000 }}" @required($field['type'] === 'title')>{{ $value }}</textarea>
                        @else
                            <input id="home-field-{{ $key }}" name="content[{{ $key }}]" type="{{ $field['type'] === 'url' ? 'url' : 'text' }}" maxlength="{{ in_array($field['type'], ['url','link']) ? 1000 : 500 }}" value="{{ $value }}" @required($field['type'] === 'link')>
                        @endif
                        @error('content.'.$key)<small class="field-error">{{ $message }}</small>@enderror
                    @endif
                </div>
            @endforeach
            </div>
            @if(isset($definition['collection']))
                @php $kind = $definition['collection']; @endphp
                <section class="hp-records" data-record-selector data-limit="{{ $definition['limit'] }}">
                    <div class="hp-records-heading"><h3>Content in this section</h3><a href="{{ route('admin.'.$kind.'.create') }}" data-admin-popup>Add {{ $kind === 'faqs' ? 'question' : 'new content' }} ↗</a></div>
                    <p>Edit a card to change its actual title, description or photo. Published changes also apply wherever that record is used.</p>
                    <div class="hp-selection-mode">
                        <label><input type="radio" name="selection_mode" value="automatic" @checked(old('selection_mode', $manualSelection ? 'manual' : 'automatic') === 'automatic')> Automatic selection</label>
                        <label><input type="radio" name="selection_mode" value="manual" @checked(old('selection_mode', $manualSelection ? 'manual' : 'automatic') === 'manual')> Choose and order content (up to {{ $definition['limit'] }})</label>
                    </div>
                    <p class="hp-record-feedback" data-record-feedback aria-live="polite"></p>
                    <div class="hp-record-list">
                    @foreach($availableRecords as $record)
                        @php
                            $title = $record->title ?? $record->name ?? $record->question;
                            $copy = $record->summary ?? $record->description ?? $record->excerpt ?? $record->answer ?? $record->tagline;
                            $selected = in_array($record->id, array_map('intval', old('record_ids', $shownRecords->pluck('id')->all())));
                        @endphp
                        <article class="hp-record" data-record>
                            @if($kind !== 'faqs')<img src="{{ asset(\App\Support\HomepageContent::image($record)) }}" alt="" loading="lazy">@else<span class="hp-question-icon">?</span>@endif
                            <div><label><input type="checkbox" name="record_ids[]" value="{{ $record->id }}" @checked($selected)><strong>{{ $title }}</strong></label><p>{{ Str::limit(strip_tags($copy ?? ''), 155) }}</p><a href="{{ route('admin.'.$kind.'.edit', $record->id) }}" data-admin-popup>Edit {{ $kind === 'faqs' ? 'question & answer' : 'content & image' }} ↗</a></div>
                            <div class="hp-order"><button type="button" data-record-up aria-label="Move {{ $title }} up">↑</button><button type="button" data-record-down aria-label="Move {{ $title }} down">↓</button></div>
                        </article>
                    @endforeach
                    </div>
                    @if($availableRecords->isEmpty())<p>No published content is available yet. Create and publish a record to show it here.</p>@endif
                </section>
            @endif
            <div class="hp-save-bar"><span>Changes appear on the homepage after saving.</span><button class="button" type="submit">Save this section <span>↗</span></button></div>
        </form>
    </div>
</div>
<dialog class="hp-gallery-dialog" aria-labelledby="hp-gallery-title">
    <div class="hp-dialog-heading"><div><span class="eyebrow">PHOTO LIBRARY</span><h2 id="hp-gallery-title">Choose a photograph</h2></div><button type="button" data-gallery-cancel aria-label="Close gallery">×</button></div>
    <div class="hp-gallery-filters"><input type="search" data-gallery-search placeholder="Search photographs" aria-label="Search photographs"><select data-gallery-category aria-label="Filter by category"><option value="">All categories</option>@foreach($galleryImages->pluck('category')->filter()->unique()->sort() as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></div>
    <div class="hp-gallery-grid">
        @foreach($galleryImages as $image)
            <button type="button" class="hp-gallery-photo" data-gallery-id="{{ $image->id }}" data-path="{{ asset($image->path) }}" data-title="{{ $image->title ?: $image->alt_text ?: 'Photo '.$image->id }}" data-category="{{ $image->category }}" aria-pressed="false">
                <img src="{{ asset($image->path) }}" alt="{{ $image->alt_text ?: $image->title }}" loading="lazy"><span class="hp-gallery-tick" aria-hidden="true">✓</span><strong>{{ $image->title ?: 'Photo '.$image->id }}</strong><small>{{ $image->category }}</small>
            </button>
        @endforeach
    </div>
    <p data-gallery-empty hidden>No photographs match this filter.</p>
    <div class="hp-dialog-footer"><a href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Manage photo gallery ↗</a><button type="button" class="button button-light" data-gallery-cancel>Cancel</button><button type="button" class="button" data-gallery-apply disabled>Use selected photograph</button></div>
</dialog>
@endsection
