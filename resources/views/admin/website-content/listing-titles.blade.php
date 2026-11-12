@extends('admin.layout')
@section('title', 'Listing Titles')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / LISTINGS</span><h1>Listing Titles</h1><p>Edit the headings and introductions shown above public listing pages.</p></div></div>
<form class="admin-form" method="POST" action="{{ route('admin.website-content.listing-titles.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="form-grid">
        @foreach([
            'safaris' => ['Safari journeys', 'Safari journeys.', 'Explore the journeys currently being shaped by Serenovia’s local team.'],
            'destinations' => ['Destinations', 'Find your place.', 'Explore the landscapes and communities that make Tanzania feel like nowhere else.'],
            'journal' => ['Journal', 'The Serenovia journal.', 'Small stories, local perspectives and inspiration for the places that stay with you.'],
        ] as $key => $defaults)
            <div class="field full"><label>{{ strtoupper($defaults[0]) }} LISTING</label></div>
            <div class="field full"><label for="{{ $key }}_listing_eyebrow">EYEBROW</label><input id="{{ $key }}_listing_eyebrow" name="{{ $key }}_listing_eyebrow" value="{{ old($key.'_listing_eyebrow', $siteSettings[$key.'_listing_eyebrow'] ?? ['safaris' => 'MADE FOR YOUR KIND OF WONDER', 'destinations' => 'A COUNTRY THAT STAYS WITH YOU', 'journal' => 'NOTES FROM TANZANIA'][$key]) }}"></div>
            <div class="field"><label for="{{ $key }}_listing_title">PAGE TITLE</label><input id="{{ $key }}_listing_title" name="{{ $key }}_listing_title" required value="{{ old($key.'_listing_title', $siteSettings[$key.'_listing_title'] ?? $defaults[1]) }}"></div>
            <div class="field full"><label for="{{ $key }}_listing_intro">INTRODUCTION</label><textarea id="{{ $key }}_listing_intro" name="{{ $key }}_listing_intro" rows="2">{{ old($key.'_listing_intro', $siteSettings[$key.'_listing_intro'] ?? $defaults[2]) }}</textarea></div>
        @endforeach
    </div>
    <div class="form-actions"><button class="button" type="submit">Save listing titles <span>↗</span></button></div>
</form>
@endsection
