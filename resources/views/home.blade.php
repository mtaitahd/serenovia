@extends('layouts.site')
@section('title', $siteSettings['brand_name'].' — '.$siteSettings['tagline'])
@section('description', $home['hero_subtitle'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hero-trip-finder.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/home-featured-tours.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/home-intro.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/home-social.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home-faq.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/home-stays.css') }}?v=2">
@endpush
@push('scripts')
    <script src="{{ asset('js/home-hero-video.js') }}?v=2" defer></script>
    <script src="{{ asset('js/home-stays.js') }}?v=2" defer></script>
@endpush
@section('content')
    @php
        $homeLink = fn ($key) => \App\Support\HomepageContent::link($home[$key]);
        $homeRecordImage = fn ($record) => asset(\App\Support\HomepageContent::image($record));
    @endphp
    <section class="hero hero-with-finder" aria-labelledby="hero-title">
        @if($home['home_hero_video_enabled'] === '1' && $home['home_hero_video'])
            <video class="home-hero-video" src="{{ asset($home['home_hero_video']) }}" autoplay muted loop playsinline preload="metadata" aria-hidden="true"></video>
        @else
            <div class="hero-image" style="background-image:url('{{ asset($home['hero_image']) }}')"></div>
        @endif
        <div class="hero-shade"></div>
        <div class="hero-finder-layout section-wrap">
            <div class="hero-content">
                <div class="eyebrow light"><span class="eyebrow-line"></span>{{ $home['home_hero_eyebrow'] }}</div>
                <h1 id="hero-title">{{ $home['hero_title'] }}</h1><p>{{ $home['hero_subtitle'] }}</p>
                <div class="hero-actions"><a href="{{ $homeLink('home_primary_url') }}" class="button button-coral">{{ $home['home_primary_button'] }} <span>↗</span></a><a href="{{ $homeLink('home_secondary_url') }}" class="text-link light-link">{{ $home['home_secondary_button'] }} <span>↓</span></a></div>
            </div>
            <form class="hero-trip-finder" id="trip-finder" method="get" action="{{ route('tours.index') }}" aria-labelledby="trip-finder-title">
                <h2 id="trip-finder-title">{{ $home['home_finder_title'] }}</h2>
                <fieldset><legend>{{ $home['home_finder_question'] }}</legend><div class="finder-types">
                    @foreach(['' => $home['home_finder_all'], 'safari' => $home['home_finder_safari'], 'mountain' => $home['home_finder_mountain']] as $type => $label)
                        <label><input type="radio" name="type" value="{{ $type }}" @checked($loop->first)><span>{{ $label }}</span></label>
                    @endforeach
                </div></fieldset>
                <label class="finder-field"><span>{{ $home['home_finder_destination'] }}</span><input type="search" name="q" maxlength="100" placeholder="{{ $home['home_finder_placeholder'] }}"></label>
                <label class="finder-field"><span>{{ $home['home_finder_duration'] }}</span><select name="max_days"><option value="">{{ $home['home_finder_any'] }}</option>@foreach([3,7,14] as $days)<option value="{{ $days }}">{{ $home['home_finder_'.$days] }}</option>@endforeach</select></label>
                <button type="submit" class="button button-dark">{{ $home['home_finder_button'] }} <span aria-hidden="true">↗</span></button>
                <small>{{ $home['home_finder_note'] }}</small>
            </form>
        </div>
    </section>
    @if($home['home_intro_enabled'] === '1') @include('public.home-intro') @endif
    @if($storyImages->isNotEmpty())
        <section class="our-story-gallery section-wrap" id="story-photos">
            <div class="section-heading"><div><span class="eyebrow">{{ $siteSettings['our_story_eyebrow'] ?? 'OUR STORY' }}</span><h2>{{ $siteSettings['our_story_title'] ?? 'The Serenovia story, in scenes.' }}</h2></div></div>
            <div class="our-story-gallery-grid">
                @foreach($storyImages as $image)
                    <figure><a href="{{ asset($image->path) }}" data-photo data-photo-group="home-story" data-caption="{{ $image->home_caption }}"><img src="{{ asset($image->path) }}" alt="{{ $image->alt_text ?: $image->title }}" loading="lazy"></a>@if($image->home_caption)<figcaption>{{ $image->home_caption }}</figcaption>@endif</figure>
                @endforeach
            </div>
        </section>
    @endif
    @if($home['home_journeys_enabled'] === '1')
    <section class="home-featured" id="journeys" aria-labelledby="featured-tours-title"><div class="section-wrap">
        <div class="home-featured-heading"><h2 id="featured-tours-title">{{ $home['home_journeys_title'] }}</h2><a href="{{ $homeLink('home_journeys_url') }}">{{ $home['home_journeys_more'] }} <span aria-hidden="true">»</span></a></div>
        @if($featuredTours->isNotEmpty())
            @php($whatsapp = preg_replace('/\D+/', '', $siteSettings['whatsapp'] ?? ''))
            <div class="home-featured-grid">@foreach($featuredTours as $tour) @include('public.home-featured-tour') @endforeach</div>
        @else
            <div class="catalogue-empty"><span class="empty-symbol">✳</span><div><b>Your next journey starts here.</b><p>Tell us what you have in mind and we will help you plan a custom trip.</p></div><a class="text-link" href="{{ route('contact') }}">Plan with our team <span>↗</span></a></div>
        @endif
    </div></section>
    @endif
    @if($home['home_safaris_enabled'] === '1') @include('public.home-safaris') @endif
    @if($home['home_destinations_enabled'] === '1')
    <div class="home-destinations-paper"><section class="destinations section-wrap" id="destinations">
        <div class="destination-title"><span class="eyebrow">{{ $home['home_destinations_eyebrow'] }}</span><h2>{{ $home['home_destinations_title'] }}</h2><p>{{ $home['home_destinations_copy'] }}</p><a class="text-link" href="{{ $homeLink('home_destinations_url') }}">{{ $home['home_destinations_button'] }} <span>↗</span></a></div>
        <div class="destination-list">@foreach($destinations as $index => $destination)
            <a href="{{ route('destinations.show', $destination->slug) }}"><span class="destination-no">{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="destination-name">{{ $destination->name }}</span><span class="destination-desc">{{ $destination->tagline ?: $destination->country }}</span><span class="destination-arrow">↗</span></a>
        @endforeach</div>
    </section></div>
    @endif
    @if($home['home_stays_enabled'] === '1') @include('public.home-stays') @endif
    @if($home['home_journal_enabled'] === '1') @include('public.article-discovery') @endif
    @include('public.home-social')
    @if($home['home_faq_enabled'] === '1')
    <div class="home-faq-band"><section class="faq home-faq section-wrap" id="faq" aria-labelledby="faq-title">
        <div class="faq-heading">
            <span class="eyebrow">{{ $home['home_faq_eyebrow'] }}</span><h2 id="faq-title">{{ $home['home_faq_title'] }}</h2><p>{{ $home['home_faq_copy'] }}</p>
            <a class="text-link" href="{{ $homeLink('home_faq_url') }}">{{ $home['home_faq_button'] }} <span>↗</span></a>
            @if($home['home_faq_image'])<img src="{{ asset($home['home_faq_image']) }}" alt="" loading="lazy" style="display:block;max-width:250px;width:100%;margin:35px auto;object-fit:contain">
            @else<div class="faq-question-sculpture" aria-hidden="true"><span>?</span></div>@endif
        </div>
        <div class="faq-list">@foreach($faqs as $index => $faq)<details class="faq-item"><summary><span class="faq-number" aria-hidden="true">{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="faq-question">{{ $faq->question }}</span><span class="faq-chevron" aria-hidden="true"></span></summary><div class="faq-answer"><p>{{ $faq->answer }}</p></div></details>@endforeach</div>
    </section></div>
    @endif
    @if($home['home_plan_enabled'] === '1')
    <section class="plan section-wrap" id="plan"><div class="plan-mark" aria-hidden="true">S<span>✳</span></div><span class="eyebrow">{{ $home['home_plan_eyebrow'] }}</span><h2>{{ $home['home_plan_title'] }}</h2><p>{{ $home['home_plan_copy'] }}</p><a class="button button-dark" href="{{ $homeLink('home_plan_url') }}">{{ $home['home_plan_button'] }} <span>↗</span></a><span class="plan-note">{{ $home['home_plan_note'] }}</span></section>
    @endif
@endsection
