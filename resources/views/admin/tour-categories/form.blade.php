@extends('admin.layout')
@section('title', $category->exists ? 'Edit tour category' : 'Add tour category')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">TOUR CLASSIFICATION</span><h1>{{ $category->exists ? 'Edit category' : 'Add a category' }}</h1><p>Categories make related journeys easier to find in both listings.</p></div><a class="button secondary" href="{{ route('admin.tour-categories.index') }}">← Categories</a></div>
    <form class="admin-form" method="POST" action="{{ $category->exists ? route('admin.tour-categories.update', $category) : route('admin.tour-categories.store') }}">
        @csrf @if($category->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="name">CATEGORY NAME *</label><input id="name" name="name" required value="{{ old('name', $category->name) }}">@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="slug">PAGE ADDRESS</label><input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="Made from the category name">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field full"><label for="description">DESCRIPTION</label><textarea id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea></div>
            <div class="field"><label for="sort_order">DISPLAY ORDER</label><input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
            <div class="field"><label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))> Show this category in public filters</label></div>
        </div>
        <div class="builder-actions"><a class="button secondary" href="{{ route('admin.tour-categories.index') }}">Cancel</a><button class="button" type="submit">{{ $category->exists ? 'Save category' : 'Add category' }}</button></div>
    </form>
@endsection
