@extends('admin.layout')
@section('title', $country->exists ? 'Edit country' : 'Add country')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">TOUR CATALOGUE</span><h1>{{ $country->exists ? 'Edit country' : 'Add a country' }}</h1><p>Active countries appear in the safari listing in the order set here.</p></div><a class="button secondary" href="{{ route('admin.countries.index') }}">← All countries</a></div>
    <form class="admin-form" method="POST" action="{{ $country->exists ? route('admin.countries.update', $country) : route('admin.countries.store') }}">
        @csrf @if($country->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="name">COUNTRY NAME *</label><input id="name" name="name" value="{{ old('name', $country->name) }}" required maxlength="120">@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="slug">FILTER ADDRESS</label><input id="slug" name="slug" value="{{ old('slug', $country->slug) }}" placeholder="Made from country name">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="sort_order">LIST ORDER *</label><input id="sort_order" type="number" name="sort_order" min="0" max="10000" value="{{ old('sort_order', $country->sort_order ?? 0) }}" required><small>Lower numbers appear first on the safari listing.</small>@error('sort_order')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $country->exists ? $country->is_active : true))> Show in safari country list</label></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">{{ $country->exists ? 'Save country' : 'Add country' }} </button><a class="button secondary" href="{{ route('admin.countries.index') }}">Cancel</a></div>
    </form>
@endsection
