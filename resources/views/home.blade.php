@extends('layouts.site')
@section('title', $siteSettings['brand_name'].' — '.$siteSettings['tagline'])
@section('description', $siteSettings['hero_subtitle'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hero-trip-finder.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/home-featured-tours.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/home-intro.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/home-social.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home-faq.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/home-stays.css') }}?v=2">
@endpush
@push('scripts')
    <script src="{{ asset('js/home-hero-video.js') }}" defer></script>
    <script src="{{ asset('js/home-stays.js') }}?v=2" defer></script>
@endpush
@section('content')
    @php
        $homePhotos = [
            'serengeti-sunset' => 'Sunset in the Serengeti',
            'serengeti-leopard' => 'Leopard in a tree',
            'migration' => 'Wildlife running across the plains',
            'lodge' => 'Pool and terrace at a safari lodge',
            'baobabs' => 'Trees on the savanna',
            'elephants' => 'Elephants crossing a dirt road',
            'zanzibar' => 'A small island off the coast',
            'kilimanjaro' => 'Snow-covered Mount Kilimanjaro',
            'village' => 'A dirt road beside a Tanzanian village',
            'zebras' => 'Zebras with mountains in the distance',
            'elephant' => 'An elephant walking on dry ground',
            'beach' => 'A palm-lined beach',
        ];
        $homePhotoUrl = fn ($name) => asset('images/home-safari/'.$name.'.jpg');
        $tourPhoto = fn ($tour) => match ($tour->destination?->slug ?? $tour->slug) {
            'lake-eyasi', 'lake-eyasi-cultural-encounters' => 'village',
            'tarangire-national-park', 'tarangire-baobabs-elephant-country' => 'elephants',
            'serengeti', 'serengeti-plains-ngorongoro-crater' => 'zebras',
            default => match ($tour->tour_type) {
                'mountain' => 'kilimanjaro',
                'island' => 'zanzibar',
                'cultural' => 'village',
                default => 'serengeti-leopard',
            },
        };
        $gallery = collect(['elephant', 'zebras', 'kilimanjaro', 'migration', 'serengeti-leopard', 'beach']);
    @endphp
    <section class="hero hero-with-finder" aria-labelledby="hero-title">
        <div class="hero-image" style="background-image:url('{{ $homePhotoUrl('serengeti-sunset') }}')"></div>
        <div class="hero-shade"></div>
        <video class="home-hero-video" data-src="{{ asset('images/home-ngorongoro-elephants.webm') }}" muted loop playsinline preload="none" aria-hidden="true"></video>
        <div class="home-hero-video-controls" hidden>
            <button type="button">Pause video</button>
            <small><a href="https://commons.wikimedia.org/wiki/File:Elephants_Grazing_in_Ngorongoro.webm">Video by Eric Kilby</a> · <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC BY-SA 2.0</a></small>
        </div>
        <div class="hero-finder-layout section-wrap">
        <div class="hero-content">
            <div class="eyebrow light"><span class="eyebrow-line"></span> {{ $siteSettings['home_hero_eyebrow'] ?? 'AFRICA, AT YOUR OWN PACE' }}</div>
            <h1 id="hero-title">{{ $siteSettings['hero_title'] }}</h1>
            <p>{{ $siteSettings['hero_subtitle'] }}</p>
            <div class="hero-actions"><a href="{{ route('contact') }}" class="button button-coral">{{ $siteSettings['home_primary_button'] ?? 'Start your journey' }} <span>↗</span></a><a href="{{ route('tours.index') }}" class="text-link light-link">{{ $siteSettings['home_secondary_button'] ?? 'Explore our safaris' }} <span>↓</span></a></div>
        </div>
        <form class="hero-trip-finder" id="trip-finder" method="get" action="{{ route('tours.index') }}" aria-labelledby="trip-finder-title">
            <h2 id="trip-finder-title">Find your trip</h2>
            <fieldset>
                <legend>What would you like to explore?</legend>
                <div class="finder-types">
                    @foreach(['' => 'All trips', 'safari' => 'Safari', 'mountain' => 'Trekking'] as $type => $label)
                        <label><input type="radio" name="type" value="{{ $type }}" @checked($loop->first)><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </fieldset>
            <label class="finder-field"><span>Trip or destination</span><input type="search" name="q" maxlength="100" placeholder="e.g. Serengeti"></label>
            <label class="finder-field"><span>Duration</span><select name="max_days"><option value="">Any length</option><option value="3">Up to 3 days</option><option value="7">Up to 7 days</option><option value="14">Up to 14 days</option></select></label>
            <button type="submit" class="button button-dark">Find trips <span aria-hidden="true">↗</span></button>
            <small>Explore at your own pace. No commitment.</small>
        </form>
        </div>
    </section>
    @include('public.home-intro')

    @if($storyImages->isNotEmpty())
        <section class="our-story-gallery section-wrap">
            <div class="section-heading"><div><span class="eyebrow">OUR STORY</span><h2>{{ $siteSettings['our_story_title'] ?? 'The Serenovia story, in scenes.' }}</h2></div></div>
            <div class="our-story-gallery-grid">
                @foreach(array_slice(array_keys($homePhotos), 0, $storyImages->count()) as $photo)
                    <figure><img src="{{ $homePhotoUrl($photo) }}" alt="{{ $homePhotos[$photo] }}" loading="lazy"></figure>
                @endforeach
            </div>
        </section>
    @endif

    <section class="home-featured" id="journeys" aria-labelledby="featured-tours-title">
        <div class="section-wrap">
            <div class="home-featured-heading">
                <h2 id="featured-tours-title">{{ $siteSettings['home_journeys_title'] ?? 'Choose your adventure.' }}</h2>
                <a href="{{ route('tours.index') }}">View all tours <span aria-hidden="true">»</span></a>
            </div>
            @if($featuredTours->isNotEmpty())
                @php($whatsapp = preg_replace('/\D+/', '', $siteSettings['whatsapp'] ?? ''))
                <div class="home-featured-grid">
                    @foreach($featuredTours as $tour)
                        @include('public.home-featured-tour')
                    @endforeach
                </div>
            @else
                <div class="catalogue-empty"><span class="empty-symbol">✳</span><div><b>Your next journey starts here.</b><p>We are shaping a collection of Tanzania journeys. Tell us what you have in mind and we will help you plan a custom trip.</p></div><a class="text-link" href="{{ route('contact') }}">Plan with our team <span>↗</span></a></div>
            @endif
        </div>
    </section>

    @include('public.home-safaris')

    <div class="home-destinations-paper">
    <section class="destinations section-wrap" id="destinations">
        <div class="destination-title"><span class="eyebrow">{{ $siteSettings['home_destinations_eyebrow'] ?? 'A COUNTRY THAT STAYS WITH YOU' }}</span><h2>{{ $siteSettings['home_destinations_title'] ?? 'Find your place.' }}</h2><p>{{ $siteSettings['home_destinations_copy'] ?? 'Come for the wildlife. Stay for the way Tanzania makes you feel.' }}</p><a class="text-link" href="{{ route('destinations.index') }}">Explore destinations <span>↗</span></a></div>
        <div class="destination-list">
            @forelse($destinations as $index => $destination)
                <a href="{{ route('destinations.show', $destination->slug) }}"><span class="destination-no">{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="destination-name">{{ $destination->name }}</span><span class="destination-desc">{{ $destination->tagline ?: $destination->country }}</span><span class="destination-arrow">↗</span></a>
            @empty
                <p>Destination stories are being prepared. <a href="{{ route('contact') }}">Ask us about Tanzania.</a></p>
            @endforelse
        </div>
    </section>

    </div>

    @include('public.home-stays')
    @include('public.article-discovery')

    @include('public.home-social')

    <div class="home-faq-band">
    <section class="faq home-faq section-wrap" id="faq" aria-labelledby="faq-title">
        <div class="faq-heading">
            <span class="eyebrow">{{ $siteSettings['home_faq_eyebrow'] ?? 'GOOD TO KNOW' }}</span>
            <h2 id="faq-title">{{ $siteSettings['home_faq_title'] ?? 'Your questions, answered.' }}</h2>
            <p>{{ $siteSettings['home_faq_copy'] ?? 'Our local team is here to help you plan.' }}</p>
            <a class="text-link" href="{{ route('contact') }}">Speak with our local team <span>↗</span></a>
            <div class="faq-question-sculpture" aria-hidden="true"><span>?</span></div>
        </div>
        @if($faqs->isNotEmpty())
            <div class="faq-list">
                @foreach($faqs as $index => $faq)
                    <details class="faq-item">
                        <summary><span class="faq-number" aria-hidden="true">{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="faq-question">{{ $faq->question }}</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                        <div class="faq-answer"><p>{{ $faq->answer }}</p></div>
                    </details>
                @endforeach
            </div>
        @else
            <div class="catalogue-empty"><div><b>Need help planning?</b><p>Our local team can answer questions about your trip.</p></div><a class="text-link" href="{{ route('contact') }}">Ask Serenovia <span>↗</span></a></div>
        @endif
    </section>
    </div>



    <section class="plan section-wrap" id="plan"><div class="plan-mark" aria-hidden="true">S<span>✳</span></div><span class="eyebrow">{{ $siteSettings['home_plan_eyebrow'] ?? 'YOUR TANZANIA STORY STARTS HERE' }}</span><h2>{{ $siteSettings['home_plan_title'] ?? 'Let’s make it unforgettable.' }}</h2><p>{{ $siteSettings['home_plan_copy'] ?? 'Tell us what you have been dreaming about. We will take it from there.' }}</p><a class="button button-dark" href="{{ route('contact') }}">{{ $siteSettings['home_plan_button'] ?? 'Plan your safari' }} <span>↗</span></a><span class="plan-note">REAL PEOPLE. LOCAL KNOWLEDGE. A JOURNEY THAT'S YOURS.</span></section>
@endsection
