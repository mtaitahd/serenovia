@extends('admin.layout')
@section('title', $accommodation->exists ? 'Edit accommodation' : 'Add accommodation')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">STAY COLLECTION</span><h1>{{ $accommodation->exists ? 'Edit accommodation' : 'Add an accommodation' }}</h1><p>Record the stay once, then attach it to package days and comfort levels.</p></div><a class="button secondary" href="{{ route('admin.accommodations.index') }}">← Accommodations</a></div>
    <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $accommodation->exists ? route('admin.accommodations.update', $accommodation) : route('admin.accommodations.store') }}">
        @csrf @if($accommodation->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="name">PROPERTY NAME *</label><input id="name" name="name" required value="{{ old('name', $accommodation->name) }}">@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="slug">PAGE ADDRESS</label><input id="slug" name="slug" value="{{ old('slug', $accommodation->slug) }}" placeholder="Made from the name">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="destination_id">DESTINATION</label><select id="destination_id" name="destination_id"><option value="">Choose a destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected(old('destination_id', $accommodation->destination_id)==$destination->id)>{{ $destination->name }}</option>@endforeach</select></div>
            <div class="field"><label for="tier">COMFORT LEVEL *</label><select id="tier" name="tier" required>@foreach(['budget'=>'Budget','mid_range'=>'Mid-range','luxury'=>'Luxury'] as $key=>$label)<option value="{{ $key }}" @selected(old('tier', $accommodation->tier ?: 'mid_range')===$key)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="location">AREA / PARK</label><input id="location" name="location" value="{{ old('location', $accommodation->location) }}" placeholder="e.g. Serengeti National Park"></div>
            <div class="field"><label for="price_from">STARTING PRICE (OPTIONAL)</label><input id="price_from" name="price_from" type="number" min="0" step="0.01" value="{{ old('price_from', $accommodation->price_from) }}"></div>
            <div class="field"><label for="currency">CURRENCY</label><select id="currency" name="currency">@foreach(['USD','TZS','EUR','GBP'] as $currency)<option @selected(old('currency', $accommodation->currency ?: 'USD')===$currency)>{{ $currency }}</option>@endforeach</select></div>
            <div class="field"><label for="status">VISIBILITY</label><select id="status" name="status"><option value="published" @selected(old('status', $accommodation->status ?: 'published')==='published')>Published</option><option value="draft" @selected(old('status', $accommodation->status)==='draft')>Draft</option></select></div>
            <div class="field full"><label for="description">DESCRIPTION</label><textarea id="description" name="description" rows="5">{{ old('description', $accommodation->description) }}</textarea></div>
            <div class="field full"><label for="amenities_text">AMENITIES <small>One per line</small></label><textarea id="amenities_text" name="amenities_text" rows="4" placeholder="Private bathroom&#10;Wi-Fi&#10;Restaurant">{{ old('amenities_text', implode("\n", $accommodation->amenities ?? [])) }}</textarea></div>
            <div class="field full"><label for="image">PROPERTY COVER PHOTO</label>@if($accommodation->image_path)<div class="cover-preview"><img src="{{ asset($accommodation->image_path) }}" alt="Current accommodation"></div>@endif<input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">@error('image')<small class="field-error">{{ $message }}</small>@enderror<small>JPG, PNG or WebP · up to 8 MB.</small></div>
            <div class="field full">
                <label for="stay-gallery">PROPERTY GALLERY</label>
                <input type="hidden" name="gallery_edited" value="1">
                <select id="stay-gallery" name="gallery_photo_ids[]" multiple size="5">
                    @foreach($galleryImages as $photo)
                        <option value="{{ $photo->id }}" @selected(in_array($photo->id, old('gallery_photo_ids', old('gallery_edited') ? [] : ($accommodation->gallery_photo_ids ?? []))))>{{ $photo->title ?: $photo->alt_text ?: 'Photo '.$photo->id }}</option>
                    @endforeach
                </select>
                <small>Select up to five photos. Hold Ctrl or Cmd to select multiple photos.</small>
                @error('gallery_photo_ids')<small class="field-error">{{ $message }}</small>@enderror
                @if($errors->has('gallery_photo_ids.*'))<small class="field-error">{{ $errors->first('gallery_photo_ids.*') }}</small>@endif
            </div>
            <div class="field"><label for="sort_order">DISPLAY ORDER</label><input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $accommodation->sort_order ?? 0) }}"></div>
            <div class="field"><label class="checkbox-row"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $accommodation->is_featured))> Feature this stay</label></div>
        </div>
        <div class="builder-actions"><a class="button secondary" href="{{ route('admin.accommodations.index') }}">Cancel</a><button class="button" type="submit">{{ $accommodation->exists ? 'Save accommodation' : 'Add accommodation' }}</button></div>
    </form>
@endsection
