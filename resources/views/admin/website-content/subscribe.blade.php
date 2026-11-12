@extends('admin.layout')
@section('title', 'Subscribe Section')
@section('content')
@php
    $defaultVideos = [
        ['title' => 'A Serengeti morning', 'thumbnail' => '', 'views' => '', 'when' => '', 'url' => ''],
    ];
    $videos = old('subscribe_videos', json_decode($siteSettings['subscribe_videos'] ?? '[]', true) ?: $defaultVideos);
@endphp
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / HOMEPAGE</span><h1>Subscribe Section</h1><p>Manage the video channel invitation and the video cards displayed on the homepage.</p></div></div>
<form class="admin-form" method="POST" action="{{ route('admin.website-content.subscribe.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="form-grid">
        <div class="field full"><label class="checkbox-row"><input type="checkbox" name="subscribe_enabled" value="1" @checked(old('subscribe_enabled', $siteSettings['subscribe_enabled'] ?? '0') === '1')> Show the Subscribe section on the homepage</label></div>
        <div class="field full"><label for="subscribe_heading">SECTION HEADING *</label><input id="subscribe_heading" name="subscribe_heading" required value="{{ old('subscribe_heading', $siteSettings['subscribe_heading'] ?? 'Follow the Serenovia journey') }}"></div>
        <div class="field full"><label for="subscribe_youtube_url">YOUTUBE CHANNEL LINK</label><input id="subscribe_youtube_url" name="subscribe_youtube_url" type="url" value="{{ old('subscribe_youtube_url', $siteSettings['subscribe_youtube_url'] ?? '') }}" placeholder="https://youtube.com/@..."></div>
    </div>
    <div class="website-video-manager" id="subscribe-videos-list">
        @foreach($videos as $index => $video)
            <article class="website-video-row">
                <div class="website-video-row-head"><b>Video {{ $loop->iteration }}</b><button class="button button-light" type="button" data-remove-video>Remove</button></div>
                <div class="form-grid">
                    <div class="field"><label>TITLE</label><input name="subscribe_videos[{{ $index }}][title]" value="{{ $video['title'] ?? '' }}" maxlength="255"></div>
                    <div class="field"><label>THUMBNAIL URL</label><input name="subscribe_videos[{{ $index }}][thumbnail]" type="url" value="{{ $video['thumbnail'] ?? '' }}" placeholder="https://..."></div>
                    <div class="field"><label>VIEWS TEXT</label><input name="subscribe_videos[{{ $index }}][views]" value="{{ $video['views'] ?? '' }}" placeholder="e.g. 12K views"></div>
                    <div class="field"><label>POSTED WHEN</label><input name="subscribe_videos[{{ $index }}][when]" value="{{ $video['when'] ?? '' }}" placeholder="e.g. 2 weeks ago"></div>
                    <div class="field full"><label>VIDEO URL</label><input name="subscribe_videos[{{ $index }}][url]" type="url" value="{{ $video['url'] ?? '' }}" placeholder="https://youtube.com/watch?v=..."></div>
                </div>
            </article>
        @endforeach
    </div>
    <button class="button button-light" type="button" id="add-subscribe-video">+ Add video</button>
    <p class="form-hint">The first 3 videos are shown on the public homepage.</p>
    <div class="form-actions"><button class="button" type="submit">Save subscribe section <span>↗</span></button></div>
</form>
@push('scripts')
<script>
(() => {
    const list = document.getElementById('subscribe-videos-list');
    const add = document.getElementById('add-subscribe-video');
    if (!list || !add) return;
    let next = {{ count($videos) }};
    add.addEventListener('click', () => {
        if (list.querySelectorAll('.website-video-row').length >= 12) return;
        const i = next++;
        const row = document.createElement('article');
        row.className = 'website-video-row';
        row.innerHTML = `<div class="website-video-row-head"><b>New video</b><button class="button button-light" type="button" data-remove-video>Remove</button></div><div class="form-grid"><div class="field"><label>TITLE</label><input name="subscribe_videos[${i}][title]" maxlength="255"></div><div class="field"><label>THUMBNAIL URL</label><input name="subscribe_videos[${i}][thumbnail]" type="url" placeholder="https://..."></div><div class="field"><label>VIEWS TEXT</label><input name="subscribe_videos[${i}][views]" placeholder="e.g. 12K views"></div><div class="field"><label>POSTED WHEN</label><input name="subscribe_videos[${i}][when]" placeholder="e.g. 2 weeks ago"></div><div class="field full"><label>VIDEO URL</label><input name="subscribe_videos[${i}][url]" type="url" placeholder="https://youtube.com/watch?v=..."></div></div>`;
        list.appendChild(row);
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-video]');
        if (button) button.closest('.website-video-row').remove();
    });
})();
</script>
@endpush
@endsection
