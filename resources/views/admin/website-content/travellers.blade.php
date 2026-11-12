@extends('admin.layout')
@section('title', 'Traveller Stories')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / HOMEPAGE</span><h1>Traveller Stories</h1><p>Manage the homepage location map and traveller review links.</p></div></div>
<form class="admin-form" method="POST" action="{{ route('admin.website-content.travellers.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="form-grid">
        <div class="field full"><label class="checkbox-row"><input type="checkbox" name="traveller_enabled" value="1" @checked(old('traveller_enabled', $siteSettings['traveller_enabled'] ?? '1') === '1')> Show Traveller Stories on the homepage</label></div>
        <div class="field"><label for="traveller_badge">SECTION BADGE</label><input id="traveller_badge" name="traveller_badge" value="{{ old('traveller_badge', $siteSettings['traveller_badge'] ?? 'TRAVELLER STORIES') }}"></div>
        <div class="field"><label for="traveller_title">SECTION TITLE *</label><input id="traveller_title" name="traveller_title" required value="{{ old('traveller_title', $siteSettings['traveller_title'] ?? 'Stories from the journey.') }}"></div>
        <div class="field full"><label for="traveller_map_heading">MAP HEADING</label><input id="traveller_map_heading" name="traveller_map_heading" value="{{ old('traveller_map_heading', $siteSettings['traveller_map_heading'] ?? 'Where We Are') }}"></div>
        <div class="field full"><label for="traveller_map_embed">GOOGLE MAPS EMBED URL</label><input id="traveller_map_embed" name="traveller_map_embed" type="url" value="{{ old('traveller_map_embed', $siteSettings['traveller_map_embed'] ?? '') }}" placeholder="https://www.google.com/maps/embed?..."><small>Paste the secure iframe URL from Google Maps. Only HTTPS Google Maps embed links are displayed.</small>@error('traveller_map_embed')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="field full"><label for="traveller_tripadvisor_heading">REVIEW PANEL HEADING</label><input id="traveller_tripadvisor_heading" name="traveller_tripadvisor_heading" value="{{ old('traveller_tripadvisor_heading', $siteSettings['traveller_tripadvisor_heading'] ?? 'Loved by travellers') }}"></div>
        <div class="field full"><label for="traveller_tripadvisor_text">REVIEW PANEL DESCRIPTION</label><textarea id="traveller_tripadvisor_text" name="traveller_tripadvisor_text" rows="3">{{ old('traveller_tripadvisor_text', $siteSettings['traveller_tripadvisor_text'] ?? 'Read what guests say about their Serenovia safari experience.') }}</textarea></div>
        <div class="field"><label for="traveller_tripadvisor_link">REVIEW PAGE URL</label><input id="traveller_tripadvisor_link" name="traveller_tripadvisor_link" type="url" value="{{ old('traveller_tripadvisor_link', $siteSettings['traveller_tripadvisor_link'] ?? '') }}" placeholder="https://www.tripadvisor.com/..."></div>
        <div class="field"><label for="traveller_tripadvisor_button_text">REVIEW BUTTON TEXT</label><input id="traveller_tripadvisor_button_text" name="traveller_tripadvisor_button_text" value="{{ old('traveller_tripadvisor_button_text', $siteSettings['traveller_tripadvisor_button_text'] ?? 'Read traveller reviews') }}"></div>
        <div class="field"><label for="traveller_review_link">LEAVE A REVIEW URL</label><input id="traveller_review_link" name="traveller_review_link" type="url" value="{{ old('traveller_review_link', $siteSettings['traveller_review_link'] ?? '') }}" placeholder="https://..."></div>
        <div class="field"><label for="traveller_review_link_text">LEAVE A REVIEW BUTTON</label><input id="traveller_review_link_text" name="traveller_review_link_text" value="{{ old('traveller_review_link_text', $siteSettings['traveller_review_link_text'] ?? 'Leave a review') }}"></div>
    </div>
    <div class="form-actions"><button class="button" type="submit">Save traveller story <span>↗</span></button></div>
</form>
@endsection
