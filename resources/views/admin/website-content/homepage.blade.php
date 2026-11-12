@extends('admin.layout')
@section('title', 'Homepage Buttons & Captions')
@section('content')
<div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT / HOMEPAGE</span><h1>Homepage Buttons & Captions</h1><p>Manage the words and calls to action used across the homepage sections.</p></div></div>
<form class="admin-form" method="POST" action="{{ route('admin.website-content.homepage.update', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf @method('PUT')
    <div class="form-grid">
        <div class="field"><label for="home_hero_eyebrow">HERO EYEBROW</label><input id="home_hero_eyebrow" name="home_hero_eyebrow" value="{{ old('home_hero_eyebrow', $siteSettings['home_hero_eyebrow'] ?? 'TANZANIA, AT YOUR OWN PACE') }}"></div>
        <div class="field"><label for="home_primary_button">PRIMARY BUTTON</label><input id="home_primary_button" name="home_primary_button" required value="{{ old('home_primary_button', $siteSettings['home_primary_button'] ?? 'Start your journey') }}"></div>
        <div class="field"><label for="home_secondary_button">SECONDARY BUTTON</label><input id="home_secondary_button" name="home_secondary_button" required value="{{ old('home_secondary_button', $siteSettings['home_secondary_button'] ?? 'Explore our safaris') }}"></div>
        <div class="field"><label for="home_intro_eyebrow">OUR STORY EYEBROW</label><input id="home_intro_eyebrow" name="home_intro_eyebrow" value="{{ old('home_intro_eyebrow', $siteSettings['home_intro_eyebrow'] ?? 'KARIBU, WELCOME') }}"></div>
        <div class="field full"><label for="home_intro_title">OUR STORY TITLE <small>First line is the heading; second line is emphasized.</small></label><textarea id="home_intro_title" name="home_intro_title" rows="2" required>{{ old('home_intro_title', $siteSettings['home_intro_title'] ?? "Made of wild places.\nMade for your kind of wonder.") }}</textarea></div>
        <div class="field full"><label for="home_intro_copy">OUR STORY INTRODUCTION</label><textarea id="home_intro_copy" name="home_intro_copy" rows="3">{{ old('home_intro_copy', $siteSettings['home_intro_copy'] ?? 'At Serenovia, we believe the best safari is the one that feels like yours. From the first morning on the savannah to the last light over the Indian Ocean, our local team brings Tanzania closer—with care, curiosity and a little bit of magic.') }}</textarea></div>
        @foreach([
            'journeys' => ['Featured journeys', 'GO WHERE THE STORY TAKES YOU', 'Choose your adventure.', 'Big skies, ancient trails and salt-kissed shores. One beautiful country, a thousand ways to feel it.'],
            'destinations' => ['Destinations', 'A COUNTRY THAT STAYS WITH YOU', 'Find your place.', 'Come for the wildlife. Stay for the way Tanzania makes you feel.'],
            'journal' => ['Journal', 'NOTES FROM TANZANIA', 'The Serenovia journal.', 'Small stories, local perspectives and inspiration for the places that stay with you.'],
            'faq' => ['FAQ', 'A FEW GOOD THINGS TO KNOW', 'Frequently asked questions.', 'Planning a trip should feel exciting. Here are a few answers to help you get started.'],
        ] as $key => $defaults)
            <div class="field"><label for="home_{{ $key }}_eyebrow">{{ strtoupper($defaults[0]) }} EYEBROW</label><input id="home_{{ $key }}_eyebrow" name="home_{{ $key }}_eyebrow" value="{{ old('home_'.$key.'_eyebrow', $siteSettings['home_'.$key.'_eyebrow'] ?? $defaults[1]) }}"></div>
            <div class="field"><label for="home_{{ $key }}_title">{{ strtoupper($defaults[0]) }} TITLE</label><input id="home_{{ $key }}_title" name="home_{{ $key }}_title" required value="{{ old('home_'.$key.'_title', $siteSettings['home_'.$key.'_title'] ?? $defaults[2]) }}"></div>
            <div class="field full"><label for="home_{{ $key }}_copy">{{ strtoupper($defaults[0]) }} CAPTION</label><textarea id="home_{{ $key }}_copy" name="home_{{ $key }}_copy" rows="2">{{ old('home_'.$key.'_copy', $siteSettings['home_'.$key.'_copy'] ?? $defaults[3]) }}</textarea></div>
        @endforeach
        <div class="field"><label for="home_plan_eyebrow">PLAN SECTION EYEBROW</label><input id="home_plan_eyebrow" name="home_plan_eyebrow" value="{{ old('home_plan_eyebrow', $siteSettings['home_plan_eyebrow'] ?? 'YOUR TANZANIA STORY STARTS HERE') }}"></div>
        <div class="field"><label for="home_plan_button">PLAN SECTION BUTTON</label><input id="home_plan_button" name="home_plan_button" required value="{{ old('home_plan_button', $siteSettings['home_plan_button'] ?? 'Plan your safari') }}"></div>
        <div class="field full"><label for="home_plan_title">PLAN SECTION TITLE</label><input id="home_plan_title" name="home_plan_title" required value="{{ old('home_plan_title', $siteSettings['home_plan_title'] ?? 'Let’s make it unforgettable.') }}"></div>
        <div class="field full"><label for="home_plan_copy">PLAN SECTION CAPTION</label><textarea id="home_plan_copy" name="home_plan_copy" rows="2">{{ old('home_plan_copy', $siteSettings['home_plan_copy'] ?? 'Tell us what you have been dreaming about. We will take it from there.') }}</textarea></div>
    </div>
    <div class="form-actions"><button class="button" type="submit">Save homepage content <span>↗</span></button></div>
</form>
@endsection
