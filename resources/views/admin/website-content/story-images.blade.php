@extends('admin.layout')
@section('title', 'Our Story Images & Captions')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / OUR STORY</span><h1>Our Story Images & Captions</h1><p>Select up to 12 photos from the gallery and add the captions shown in the homepage story strip.</p></div></div>
<form class="admin-form" method="POST" action="{{ route('admin.website-content.story-images.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="field"><label for="our_story_eyebrow">SECTION LABEL</label><input id="our_story_eyebrow" name="our_story_eyebrow" maxlength="100" value="{{ old('our_story_eyebrow', $siteSettings['our_story_eyebrow'] ?? 'OUR STORY') }}"></div>
    <div class="form-grid"><div class="field full"><label for="our_story_title">SECTION TITLE *</label><input id="our_story_title" name="our_story_title" required value="{{ old('our_story_title', $siteSettings['our_story_title'] ?? 'The Serenovia story, in scenes.') }}"></div></div>
    @if($galleryImages->isEmpty())
        <div class="empty-state"><strong>Your photo gallery is empty.</strong>Add images in Photo gallery before choosing story photos.</div>
    @else
        <div class="story-image-manager">
            @foreach($galleryImages as $image)
                @php($saved = $storyItems->get($image->id, []))
                <label class="story-image-option">
                    <img src="{{ asset($image->path) }}" alt="{{ $image->alt_text ?: $image->title }}" loading="lazy">
                    <span class="story-image-option-title"><input type="checkbox" name="story_images[]" value="{{ $image->id }}" @checked(old('story_images') ? in_array((string)$image->id, old('story_images', []), true) : $storyItems->has($image->id))><b>{{ $image->title ?: 'Untitled photograph' }}</b></span>
                    <input type="text" name="story_captions[{{ $image->id }}]" value="{{ old('story_captions.'.$image->id, $saved['caption'] ?? '') }}" maxlength="180" placeholder="Caption shown with this photo">
                </label>
            @endforeach
        </div>
    @endif
    @error('story_images')<small class="field-error">{{ $message }}</small>@enderror
    <div class="form-actions"><button class="button" type="submit">Save story images </button></div>
</form>
@endsection
