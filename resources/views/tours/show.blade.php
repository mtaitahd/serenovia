@extends('layouts.site')
@section('title', $tour->title.' · '.$siteSettings['brand_name'])
@section('description', $tour->searchDescription())
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tour-catalogue.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/tour-detail.css') }}?v=8">
@endpush
@section('content')
    @php
        $tourPhotos = collect([
            ['path' => $tour->image_path, 'caption' => $tour->title],
            ['path' => $tour->destination?->image_path, 'caption' => $tour->destination?->name],
        ])->merge($tour->accommodations->map(fn ($stay) => ['path' => $stay->image_path, 'caption' => $stay->name]))->filter(fn ($photo) => !empty($photo['path']))->unique('path')->values();
        $heroPhoto = ['path' => \App\Support\SafariPhotography::tour($tour), 'caption' => $tour->title];
        $tabs = ['overview' => 'Overview', 'itinerary' => 'Day by Day', 'rates' => 'Rates', 'stays' => 'Accommodation', 'included' => 'Inclusions', 'getting-there' => 'Getting There', 'operator' => 'Offered By'];
        if (!$itineraryDays) unset($tabs['itinerary']);
        if ($tour->accommodations->isEmpty()) unset($tabs['stays']);
        if (!$tour->inclusions && !$tour->exclusions) unset($tabs['included']);
        $requestedTab = request()->query('section');
        $activeTab = is_string($requestedTab) && isset($tabs[$requestedTab]) ? $requestedTab : 'overview';
        $tabUrl = fn ($tab) => (isset($previewBaseUrl)
            ? $previewBaseUrl.'?'.http_build_query(['frame' => 1, 'section' => $tab])
            : route('tours.show', ['tour' => $tour->slug] + ($tab === 'overview' ? [] : ['section' => $tab]))).'#tour-sections';
        $enquiryUrl = route('contact', ['tour' => $tour->id]);
        $whatsapp = preg_replace('/\D+/', '', $siteSettings['whatsapp'] ?? '');
    @endphp
    <div class="tour-detail-shell">
        <nav class="detail-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('tours.index') }}">Safaris</a><span>›</span><span>{{ $tour->destination?->name ?? ucfirst($tour->tour_type) }}</span></nav>
        <section class="detail-cover" aria-labelledby="tour-title">
            @if(!empty($heroPhoto['path']))<img src="{{ asset($heroPhoto['path']) }}" {{ \App\Support\ResponsiveImage::attributes($heroPhoto['path'], '100vw') }} alt="" fetchpriority="high">@endif
            <div class="detail-cover-copy">
                <span>{{ $tour->duration_days }} days · {{ ucfirst($tour->tour_type) }}</span>
                <h1 id="tour-title">{{ str_replace('&', 'and', $tour->title) }}</h1>
                @if(!empty($tour->package_details['subtitle']))<p>{{ $tour->package_details['subtitle'] }}</p>@endif
            </div>
        </section>
        <nav class="detail-tabs" id="tour-sections" aria-label="Explore this safari">
            @foreach($tabs as $tab => $label)
                <a data-tour-tab="{{ $tab }}" href="{{ $tabUrl($tab) }}" @if($tab === $activeTab) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    <section class="safari-layout detail-layout">
        <article class="safari-story">
            @if(isset($itineraryOptions) && $itineraryOptions->isNotEmpty())
                <section class="safari-section"><h2>Itinerary options</h2><div class="safari-tags">@foreach($itineraryOptions as $option)<a class="text-link" href="{{ route('tours.show', ['tour' => $option->slug, 'section' => 'itinerary']) }}">{{ $option->duration_days }} days · {{ $option->title }}</a>@endforeach</div></section>
            @endif
            <div data-tour-panel="overview" @if($activeTab !== 'overview') hidden @endif>
            <section class="safari-section" id="overview"><span class="eyebrow">THE EXPERIENCE</span><h2>Overview</h2>@if($tour->categories->isNotEmpty())<div class="safari-tags">@foreach($tour->categories as $category)<span>{{ $category->name }}</span>@endforeach</div>@endif<div class="prose">{!! \App\Support\RichText::display($tour->description ?: $tour->summary) !!}</div>
                @if($tour->starting_point || $tour->ending_point)<div class="safari-route">@if($tour->starting_point)<div><span>YOUR JOURNEY BEGINS</span><strong>{{ $tour->starting_point }}</strong></div>@endif<span aria-hidden="true">⟶</span>@if($tour->ending_point)<div><span>YOUR JOURNEY ENDS</span><strong>{{ $tour->ending_point }}</strong></div>@endif</div>@endif
            </section>
            @if(count($itineraryDays) && $tour->route_map_enabled)
                <section class="safari-section"><h2>Route</h2>@include('public.itinerary-route')</section>
            @endif
            @include('public.tour-practical-details')
            </div><div data-tour-panel="itinerary" @if($activeTab !== 'itinerary') hidden @endif>
                @include('public.tour-day-timeline')
            </div><div data-tour-panel="stays" @if($activeTab !== 'stays') hidden @endif>
                @include('public.tour-accommodation-table')
            </div><div data-tour-panel="included" @if($activeTab !== 'included') hidden @endif><section class="safari-section" id="included"><span class="eyebrow">THE FINER DETAILS</span><h2>Inclusions &amp; exclusions</h2><div class="safari-inclusions">@if($tour->inclusions)<div><h3><span>✓</span> What’s included</h3><div class="prose">{!! \App\Support\RichText::display($tour->inclusions) !!}</div></div>@endif @if($tour->exclusions)<div><h3><span>−</span> What’s excluded</h3><div class="prose">{!! \App\Support\RichText::display($tour->exclusions) !!}</div></div>@endif</div></section>
            </div><div data-tour-panel="rates" @if($activeTab !== 'rates') hidden @endif>
            <section class="safari-section" id="rates">
                <h2>Rates</h2>
                <p class="detail-rate">{{ $tour->price_from !== null ? 'From '.$tour->currency.' '.number_format((float) $tour->price_from, 2) : 'Price on request' }}</p>
                <p>Request a written quote for your dates and group size.</p>
            </section>
            </div><div data-tour-panel="getting-there" @if($activeTab !== 'getting-there') hidden @endif>
            <section class="safari-section" id="getting-there">
                <h2>Getting there</h2>
                @if(!empty($tour->practical_details['arrival']))<p>{{ $tour->practical_details['arrival'] }}</p>
                @else<p>Ask our team to confirm arrival and transfer arrangements for your trip.</p>@endif
                @if($tour->starting_point)<p><strong>Starts in:</strong> {{ $tour->starting_point }}</p>@endif
                @if($tour->ending_point)<p><strong>Ends in:</strong> {{ $tour->ending_point }}</p>@endif
            </section>
            </div><div data-tour-panel="operator" @if($activeTab !== 'operator') hidden @endif>
            <section class="safari-section" id="operator">
                <h2>Offered by</h2>
                <div class="detail-operator"><img src="{{ asset('images/serenovia-logo.png') }}" alt="" width="64" height="58"><div><strong>{{ $siteSettings['brand_name'] }}</strong><p>{{ $siteSettings['address'] }}</p><a class="text-link" href="{{ route('about') }}">About our team</a></div></div>
            </section>
            </div>
        </article>
        <aside class="detail-sidebar" aria-label="Quote and trip resources">
        <section class="detail-quote" aria-labelledby="quote-title">
            <h2 id="quote-title">Request a quote</h2>
            <p class="detail-rate">{{ $tour->price_from !== null ? 'From '.$tour->currency.' '.number_format((float) $tour->price_from, 2) : 'Price on request' }}</p>
            <form action="{{ route('contact') }}" method="get">
                <input type="hidden" name="tour" value="{{ $tour->id }}">
                <label>Preferred start date<input type="date" name="travel_date" min="{{ now()->toDateString() }}"></label>
                <label>Travellers<input type="number" name="travellers" min="1" max="100" value="2" required></label>
                <button class="button button-dark" type="submit">Get a quote </button>
            </form>
            <small>Send a request to Serenovia. Your dates and price will be confirmed by our team.</small>
            @if($whatsapp)<a class="text-link" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hello, I am interested in '.$tour->title.'.') }}" target="_blank" rel="noopener">Chat on WhatsApp</a>@endif
        </section>
        @include('public.tour-sidebar-cards')
        </aside>
    </section>
    </div>
    @if($faqs->isNotEmpty())<section class="faq section-wrap tour-faq" id="tour-faq"><div class="faq-heading"><span class="eyebrow">{{ $siteSettings['home_faq_eyebrow'] ?? 'GOOD TO KNOW' }}</span><h2>{{ $siteSettings['home_faq_title'] ?? 'Your questions, answered.' }}</h2><p>{{ $siteSettings['home_faq_copy'] ?? 'Our local team is here to help you plan.' }}</p><a href="{{ $enquiryUrl }}" class="text-link">Ask about this safari </a><div class="faq-question-sculpture" aria-hidden="true"><span>?</span></div></div><div class="faq-list">@foreach($faqs as $index => $faq)<details class="faq-item"><summary><span class="faq-number">{{ str_pad((string)($index+1),2,'0',STR_PAD_LEFT) }}</span><span class="faq-question">{{ $faq->question }}</span><span class="faq-chevron" aria-hidden="true"></span></summary><div class="faq-answer"><p>{{ $faq->answer }}</p></div></details>@endforeach</div></section>@endif
    @if($related->isNotEmpty())
        <section class="article-discovery related-tour-discovery" aria-labelledby="related-tour-title">
            <div class="section-heading"><h2 id="related-tour-title">Related Tours</h2><a class="text-link" href="{{ route('tours.index', (int)$tour->duration_days === 1 ? ['max_days' => 1] : ['min_days' => 2]) }}">Explore more tours</a></div>
            <div class="article-discovery-track" tabindex="0" aria-label="Related tour cards">
                @foreach($related as $other)
                    @php($relatedPhoto = $other->image_path ?: $other->destination?->image_path)
                    <article class="discovery-card"><a href="{{ route('tours.show', $other->slug) }}">
                        <div class="discovery-card-photo">
                            @if($relatedPhoto)<img src="{{ asset($relatedPhoto) }}" alt="" loading="lazy">@endif
                        </div>
                        <div class="discovery-card-copy">
                            <span class="discovery-category">{{ $other->duration_days }} {{ (int) $other->duration_days === 1 ? 'day' : 'days' }} · {{ $other->destination?->name ?? ucfirst($other->tour_type) }}</span>
                            <h3>{{ $other->title }}</h3>
                            <div class="discovery-card-reveal"><p>{{ $other->price_from !== null ? 'From '.$other->currency.' '.number_format((float) $other->price_from, 0) : 'Price on request' }}</p><span class="discovery-read">View tour</span></div>
                            <span class="discovery-open" aria-hidden="true">↑</span>
                        </div>
                    </a></article>
                @endforeach
            </div>
            <div class="article-slider-controls" hidden><button type="button" data-article-prev aria-label="Previous tours">←</button><button type="button" data-article-next aria-label="Next tours">→</button></div>
        </section>
    @endif
    <div class="mobile-safari-enquiry"><div><small>{{ $tour->duration_days }} DAYS · {{ $tour->duration_nights }} NIGHTS</small><strong>{{ $tour->price_from ? 'From '.$tour->currency.' '.number_format((float)$tour->price_from) : 'Your tailor-made safari' }}</strong></div><a class="button button-coral" href="{{ $enquiryUrl }}">Enquire </a></div>
@endsection

@push('styles')<link rel="stylesheet" href="{{ asset('css/itinerary-map.css') }}?v=3">@endpush

@push('scripts')<script src="{{ asset('js/tour-tabs.js') }}" defer></script>@endpush
