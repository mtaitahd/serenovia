@extends('admin.layout')
@section('title', $destination->exists ? 'Edit destination' : 'New destination')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">PLACES TO EXPLORE</span><h1>{{ $destination->exists ? 'Edit destination' : 'Create a destination' }}</h1><p>Only published destinations appear on the public website.</p></div><a class="button secondary" href="{{ route('admin.destinations.index') }}">← All destinations</a></div>
    <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $destination->exists ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}">
        @csrf @if($destination->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="name">DESTINATION NAME *</label><input id="name" name="name" value="{{ old('name', $destination->name) }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="slug">PAGE ADDRESS</label><input id="slug" name="slug" value="{{ old('slug', $destination->slug) }}" placeholder="Leave blank to make from name">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="country">COUNTRY *</label><select id="country" name="country" required><option value="">Choose a country</option>@foreach($countries as $country)<option value="{{ $country->name }}" @selected(old('country', $destination->country ?: 'Tanzania')===$country->name)>{{ $country->name }}{{ $country->is_active ? '' : ' · inactive' }}</option>@endforeach</select>@error('country')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="status">VISIBILITY</label><select id="status" name="status"><option value="published" @selected(old('status', $destination->status ?: 'published') === 'published')>Published</option><option value="draft" @selected(old('status', $destination->status) === 'draft')>Draft</option></select></div>
            <div class="field"><label for="tagline">SHORT TAGLINE</label><input id="tagline" name="tagline" value="{{ old('tagline', $destination->tagline) }}"></div>
            <div class="field"><label for="sort_order">DISPLAY ORDER</label><input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $destination->sort_order ?? 0) }}"></div>
            <div class="field full"><label for="summary">SHORT INTRODUCTION</label><textarea id="summary" name="summary" rows="3">{{ old('summary', $destination->summary) }}</textarea></div>
            <div class="field full"><label for="description">DESTINATION STORY</label><textarea id="description" name="description" rows="7">{{ old('description', $destination->description) }}</textarea></div>
            <div class="field full"><label for="image">COVER PHOTO</label>@if($destination->image_path)<img class="image-current" src="{{ asset($destination->image_path) }}" alt="">@endif<input id="image" type="file" name="image" accept="image/*"><small>JPG, PNG or WebP. Maximum 8 MB.</small></div>
            <div class="field full"><label class="checkbox-row"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $destination->is_featured))> Feature on the home page</label></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save destination <span>↗</span></button><a class="button secondary" href="{{ route('admin.destinations.index') }}">Cancel</a></div>
    </form>
@endsection
