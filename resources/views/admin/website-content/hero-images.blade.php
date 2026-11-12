@extends('admin.layout')
@section('title', 'Hero Images')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / HERO IMAGES</span><h1>Hero images</h1><p>Choose a gallery photo or upload a cover for each page. A new upload takes priority over a gallery selection.</p></div></div>
<form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.website-content.hero-images.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
<div class="form-grid">
@foreach($heroPages as $key => $label)
    @php($currentImage = $siteSettings[$key === 'home' ? 'hero_image' : $key.'_hero_image'] ?? ($galleryImages->first()?->path ?? ($siteSettings['hero_image'] ?? '')))
    <div class="field full" style="padding:20px 0;border-bottom:1px solid var(--line)"><h2>{{ $label }}</h2>
        @if($currentImage)<img class="image-current" src="{{ asset($currentImage) }}" alt="{{ $label }} cover" style="width:100%;max-width:440px;height:180px;object-fit:cover">@endif
        <label for="hero_gallery_{{ $key }}">CHOOSE FROM GALLERY</label><select id="hero_gallery_{{ $key }}" name="hero_gallery[{{ $key }}]"><option value="">Keep current image</option>@foreach($galleryImages as $image)<option value="{{ $image->id }}" @selected((string)old('hero_gallery.'.$key) === (string)$image->id)>{{ $image->title ?: $image->alt_text ?: 'Photo '.$image->id }}</option>@endforeach</select>
        <label for="hero_upload_{{ $key }}">OR UPLOAD A NEW PHOTO</label><input id="hero_upload_{{ $key }}" type="file" name="hero_uploads[{{ $key }}]" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, up to 8 MB. Wide landscape photos work best. Leave both fields empty to keep the current cover.</small>
        @error('hero_gallery.'.$key)<small class="field-error">{{ $message }}</small>@enderror @error('hero_uploads.'.$key)<small class="field-error">{{ $message }}</small>@enderror
    </div>
@endforeach
</div><p>Individual safari, destination and article covers are managed in their own edit forms.</p><div class="form-actions"><button type="submit" class="button">Save hero images <span>↗</span></button></div>
</form>
@endsection
