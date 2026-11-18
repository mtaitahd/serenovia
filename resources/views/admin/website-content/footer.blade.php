@extends('admin.layout')
@section('title', 'Footer')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / SITE-WIDE</span><h1>Footer</h1><p>Manage the footer description, location, link labels and headings.</p></div></div>
<form class="admin-form" method="POST" action="{{ route('admin.website-content.footer.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="form-grid">
        <div class="field full"><label for="footer_tagline">BRAND DESCRIPTION</label><textarea id="footer_tagline" name="footer_tagline" rows="3">{{ old('footer_tagline', $siteSettings['footer_tagline'] ?? 'Thoughtfully made journeys through Tanzania.') }}</textarea></div>
        <div class="field"><label for="footer_location">FOOTER LOCATION</label><input id="footer_location" name="footer_location" value="{{ old('footer_location', $siteSettings['footer_location'] ?? $siteSettings['address']) }}"></div>
        <div class="field"><label for="footer_bottom_note">BOTTOM STRAPLINE</label><input id="footer_bottom_note" name="footer_bottom_note" value="{{ old('footer_bottom_note', $siteSettings['footer_bottom_note'] ?? '') }}"></div>
        <div class="field"><label for="footer_explore_heading">EXPLORE COLUMN HEADING</label><input id="footer_explore_heading" name="footer_explore_heading" required value="{{ old('footer_explore_heading', $siteSettings['footer_explore_heading'] ?? 'Useful links') }}"></div>
        <div class="field"><label for="footer_contact_heading">CONTACT COLUMN HEADING</label><input id="footer_contact_heading" name="footer_contact_heading" required value="{{ old('footer_contact_heading', $siteSettings['footer_contact_heading'] ?? 'Get in touch') }}"></div>
        <div class="field full"><label for="footer_links_text">EXPLORE LINKS <small>One per line: label | URL</small></label><textarea id="footer_links_text" name="footer_links_text" rows="7" placeholder="Safari journeys | /safaris&#10;Places to stay | /accommodations">{{ old('footer_links_text', $footerLinksText) }}</textarea></div>
    </div>
    <div class="form-actions"><button class="button" type="submit">Save footer <span>↗</span></button></div>
</form>
@endsection
