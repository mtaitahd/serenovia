@extends('admin.layout')
@section('title', 'Website settings')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">YOUR BRAND DETAILS</span><h1>Website settings</h1><p>These details are shown across the public Serenovia website.</p></div></div>
    <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="field"><label for="brand_name">BUSINESS NAME *</label><input id="brand_name" name="brand_name" value="{{ old('brand_name', $settings['brand_name']) }}" required></div>
            <div class="field"><label for="tagline">TAGLINE</label><input id="tagline" name="tagline" value="{{ old('tagline', $settings['tagline']) }}"></div>
            <div class="field full"><label for="hero_title">HOME PAGE HEADLINE *</label><input id="hero_title" name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}" required></div>
            <div class="field full"><label for="hero_subtitle">HOME PAGE INTRODUCTION</label><textarea id="hero_subtitle" name="hero_subtitle" rows="3">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea></div>
            <div class="field full"><label for="hero_image">HOME PAGE COVER PHOTO</label>@if($settings['hero_image'] ?? false)<img class="image-current" src="{{ asset($settings['hero_image']) }}" alt="">@endif<input id="hero_image" type="file" name="hero_image" accept="image/*"><small>Upload a new JPG, PNG or WebP image (up to 8 MB) to replace the current cover photo.</small></div>
            <div class="field"><label for="email">PUBLIC EMAIL *</label><input id="email" type="email" name="email" value="{{ old('email', $settings['email']) }}" required></div>
            <div class="field"><label for="phone">PUBLIC PHONE *</label><input id="phone" name="phone" value="{{ old('phone', $settings['phone']) }}" required></div>
            <div class="field"><label for="whatsapp">WHATSAPP NUMBER *</label><input id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp']) }}" required><small>Use country code and digits, without spaces or plus sign.</small></div>
            <div class="field"><label for="address">BUSINESS ADDRESS</label><input id="address" name="address" value="{{ old('address', $settings['address']) }}"></div>
            <div class="field"><label for="instagram">INSTAGRAM PROFILE URL</label><input id="instagram" type="url" name="instagram" value="{{ old('instagram', $settings['instagram']) }}" placeholder="https://instagram.com/..."></div>
            <div class="field"><label for="facebook">FACEBOOK PAGE URL</label><input id="facebook" type="url" name="facebook" value="{{ old('facebook', $settings['facebook']) }}" placeholder="https://facebook.com/..."></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save website details </button></div>
    </form>
@endsection
