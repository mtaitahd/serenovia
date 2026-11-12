@extends('layouts.site')
@section('title', $siteSettings['brand_name'].' — '.$siteSettings['tagline'])
@section('description', $siteSettings['hero_subtitle'])
@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-image" style="background-image:url('{{ !empty($siteSettings['hero_image']) ? asset($siteSettings['hero_image']) : 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?auto=format&fit=crop&w=2400&q=88' }}')"></div>
        <div class="hero-shade"></div>
        <div class="hero-content">
            <div class="eyebrow light"><span class="eyebrow-line"></span> {{ $siteSettings['home_hero_eyebrow'] ?? 'AFRICA, AT YOUR OWN PACE' }}</div>
            <h1 id="hero-title">{{ $siteSettings['hero_title'] }}</h1>
            <p>{{ $siteSettings['hero_subtitle'] }}</p>
            <div class="hero-actions"><a href="{{ route('contact') }}" class="button button-coral">{{ $siteSettings['home_primary_button'] ?? 'Start your journey' }} <span>↗</span></a><a href="{{ route('tours.index') }}" class="text-link light-link">{{ $siteSettings['home_secondary_button'] ?? 'Explore our safaris' }} <span>↓</span></a></div>
        </div>
        <div class="hero-bottom"><span>{{ $siteSettings['brand_name'] }}</span><a href="#journeys">DISCOVER YOUR JOURNEY <span>↓</span></a><span>{{ $siteSettings['address'] }}</span></div>
    </section>
    @if($destinations->isNotEmpty())<nav class="home-destination-ribbon section-wrap" aria-label="Discover destinations"><span class="eyebrow">FIND YOUR WILD</span>@foreach($destinations as $destination)<a href="{{ route('destinations.show', $destination->slug) }}">{{ $destination->name }} <span>↗</span></a>@endforeach</nav>@endif

    <section class="intro section-wrap" id="story">
        @php($introTitleLines = preg_split('/\r?\n/', $siteSettings['home_intro_title'] ?? "Made of wild places.\nMade for your kind of wonder."))
        <div class="intro-label"><span class="eyebrow">{{ $siteSettings['home_intro_eyebrow'] ?? 'KARIBU, WELCOME' }}</span><span class="label-rule"></span><span class="label-place">MOSHI · TANZANIA</span></div>
        <div class="intro-copy"><h2>{{ $introTitleLines[0] ?? '' }}@if(!empty($introTitleLines[1]))<br><em>{{ $introTitleLines[1] }}</em>@endif</h2><div class="intro-bottom"><p>{{ $siteSettings['home_intro_copy'] ?? 'At Serenovia, we believe the best safari is the one that feels like yours. From the first morning on the savannah to the last light over the Indian Ocean, our local team brings Tanzania closer—with care, curiosity and a little bit of magic.' }}</p><a class="text-link" href="{{ route('contact') }}">Get to know us <span>↗</span></a></div></div>
        <div class="intro-stamp" aria-hidden="true"><span>LOCAL HEART</span><b>✳</b><span>WILD SOUL</span></div>
    </section>

    @if($storyImages->isNotEmpty())
        <section class="our-story-gallery section-wrap"><div class="section-heading"><div><span class="eyebrow">OUR STORY</span><h2>{{ $siteSettings['our_story_title'] ?? 'The Serenovia story, in scenes.' }}</h2></div></div><div class="our-story-gallery-grid">@foreach($storyImages as $storyImage)<figure><img src="{{ asset($storyImage->path) }}" alt="{{ $storyCaptions[$storyImage->id] ?? $storyImage->alt_text ?: $storyImage->title }}">@if($storyCaptions[$storyImage->id] ?? false)<figcaption>{{ $storyCaptions[$storyImage->id] }}</figcaption>@endif</figure>@endforeach</div></section>
    @endif

    <section class="journeys" id="journeys">
        <div class="section-wrap">
            <div class="section-heading"><div><span class="eyebrow">{{ $siteSettings['home_journeys_eyebrow'] ?? 'GO WHERE THE STORY TAKES YOU' }}</span><h2>{{ $siteSettings['home_journeys_title'] ?? 'Choose your adventure.' }}</h2></div><p>{{ $siteSettings['home_journeys_copy'] ?? 'Big skies, ancient trails and salt-kissed shores. One beautiful country, a thousand ways to feel it.' }}</p></div>
            @if($featuredTours->isNotEmpty())
                <div class="journey-grid">
                    @foreach($featuredTours as $index => $tour)
                        @php($fallbackPhoto = $tour->tour_type === 'mountain' ? 'photo-1464822759023-fed622ff2c3b' : ($tour->tour_type === 'island' ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
                        <a class="journey-card" href="{{ route('tours.show', $tour->slug) }}">
                            <div class="journey-photo" style="background-image:url('{{ $tour->image_path ? asset($tour->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=1000&q=85' }}')"></div>
                            <span class="card-number">{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }} / {{ strtoupper($tour->tour_type) }}</span>
                            <div class="journey-info"><div><p>{{ $tour->duration_days }} DAYS · {{ $tour->destination?->name ?? 'TANZANIA' }}</p><h3>{{ $tour->title }}</h3>@if($tour->price_from)<span class="journey-price">FROM {{ $tour->currency }} {{ number_format((float)$tour->price_from) }}</span>@endif</div><span class="circle-arrow">↗</span></div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="catalogue-empty"><span class="empty-symbol">✳</span><div><b>Your next journey starts here.</b><p>We are shaping a collection of Tanzania journeys. Tell us what you have in mind and we will help you plan a custom trip.</p></div><a class="text-link" href="{{ route('contact') }}">Plan with our team <span>↗</span></a></div>
            @endif
            <div class="journeys-foot"><span>NO TWO JOURNEYS ARE THE SAME.</span><a class="text-link" href="{{ route('tours.index') }}">Explore all journeys <span>↗</span></a></div>
        </div>
    </section>

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

    @include('public.article-discovery')

    @if($gallery->isNotEmpty())
        <section class="home-gallery section-wrap">
            <div class="section-heading"><div><span class="eyebrow">A GLIMPSE OF THE JOURNEY</span><h2>Scenes from <em>our Tanzania.</em></h2></div><a class="text-link" href="{{ route('gallery.index') }}">Open the gallery <span>↗</span></a></div>
            <div class="home-gallery-grid">@foreach($gallery as $index => $image)<a href="{{ asset($image->path) }}" data-photo data-photo-group="home" data-caption="{{ $image->title ?: $image->alt_text }}" aria-label="View photo: {{ $image->alt_text ?: $image->title }}"><img src="{{ asset($image->path) }}" alt="{{ $image->alt_text ?: $image->title }}" loading="lazy"><span class="gallery-image-caption"><small>{{ str_pad((string)($index+1),2,'0',STR_PAD_LEFT) }} / {{ $image->category }}</small><strong>{{ $image->title }}</strong><b aria-hidden="true">↗</b></span></a>@endforeach</div>
        </section>
    @endif

    <section class="faq section-wrap" id="faq" aria-labelledby="faq-title">
        <div class="faq-heading"><span class="eyebrow">{{ $siteSettings['home_faq_eyebrow'] ?? 'A FEW GOOD THINGS TO KNOW' }}</span><h2 id="faq-title">{{ $siteSettings['home_faq_title'] ?? 'Frequently asked questions.' }}</h2><p>{{ $siteSettings['home_faq_copy'] ?? 'Planning a trip should feel exciting. Here are a few answers to help you get started.' }}</p></div>
        @if($faqs->isNotEmpty())
            <div class="faq-list">@foreach($faqs as $index => $faq)<details class="faq-item" @if($index === 0) open @endif><summary><span class="faq-number">{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="faq-question">{{ $faq->question }}</span><span class="faq-chevron" aria-hidden="true"></span></summary><div class="faq-answer"><p>{{ $faq->answer }}</p></div></details>@endforeach</div>
        @else
            <div class="catalogue-empty"><div><b>Need help planning?</b><p>Our local team can answer questions about your trip.</p></div><a class="text-link" href="{{ route('contact') }}">Ask Serenovia <span>↗</span></a></div>
        @endif
        <div class="faq-contact"><span>Ready to talk about your trip?</span><a class="text-link" href="{{ route('contact') }}">Speak with our local team <span>↗</span></a></div>
    </section>


    @if(($siteSettings['subscribe_enabled'] ?? '0') === '1')
        @php($subscribeVideos = json_decode($siteSettings['subscribe_videos'] ?? '[]', true) ?: [])
        <section class="subscribe-section section-wrap"><div class="subscribe-heading"><span class="eyebrow light">FIELD NOTES / ON FILM</span><h2>{{ $siteSettings['subscribe_heading'] ?? 'Follow the Serenovia journey' }}</h2>@if(!empty($siteSettings['subscribe_youtube_url']))<a class="button button-coral" href="{{ $siteSettings['subscribe_youtube_url'] }}" target="_blank" rel="noopener">Subscribe on YouTube <span>↗</span></a>@endif</div>@if(count($subscribeVideos))<div class="subscribe-video-grid">@foreach(array_slice($subscribeVideos, 0, 3) as $video)<a class="subscribe-video-card" href="{{ $video['url'] ?? ($siteSettings['subscribe_youtube_url'] ?? '#') }}" target="_blank" rel="noopener">@if(!empty($video['thumbnail']))<img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] ?? 'Serenovia video' }}" loading="lazy">@else<div class="subscribe-video-fallback">SERENOVIA<br>ON FILM</div>@endif<span class="subscribe-video-play" aria-hidden="true">▶</span><span class="subscribe-video-meta">{{ $video['views'] ?? '' }} @if(!empty($video['when']))<i>·</i> {{ $video['when'] }} @endif</span><b>{{ $video['title'] ?? 'Watch our latest story' }}</b></a>@endforeach</div>@endif</section>
    @endif

    <section class="plan section-wrap" id="plan"><div class="plan-mark" aria-hidden="true">S<span>✳</span></div><span class="eyebrow">{{ $siteSettings['home_plan_eyebrow'] ?? 'YOUR TANZANIA STORY STARTS HERE' }}</span><h2>{{ $siteSettings['home_plan_title'] ?? 'Let’s make it unforgettable.' }}</h2><p>{{ $siteSettings['home_plan_copy'] ?? 'Tell us what you have been dreaming about. We will take it from there.' }}</p><a class="button button-dark" href="{{ route('contact') }}">{{ $siteSettings['home_plan_button'] ?? 'Plan your safari' }} <span>↗</span></a><span class="plan-note">REAL PEOPLE. LOCAL KNOWLEDGE. A JOURNEY THAT'S YOURS.</span></section>
@endsection
