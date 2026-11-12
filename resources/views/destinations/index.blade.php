@extends('layouts.site')
@section('title', 'Destinations · '.$siteSettings['brand_name'])
@section('content')
    <section class="listing-hero page-photo-hero">@include('public.hero-image', ['heroKey' => 'destinations'])<span class="eyebrow">{{ $siteSettings['destinations_listing_eyebrow'] ?? 'A COUNTRY THAT STAYS WITH YOU' }}</span><h1>{{ $siteSettings['destinations_listing_title'] ?? 'Find your place.' }}</h1><p>{{ $siteSettings['destinations_listing_intro'] ?? 'Explore the landscapes and communities that make Tanzania feel like nowhere else.' }}</p></section>
    <section class="listing-wrap section-wrap">
        @if($destinations->isEmpty())<div class="public-empty"><h2>Destination stories are being prepared.</h2><p>Our team can help you find the right places for your journey.</p><a class="button button-dark" href="{{ route('contact') }}">Ask our team <span>↗</span></a></div>
        @else
            <div class="destination-cards">@foreach($destinations as $index => $destination)
                @php($fallbackPhoto = $destination->slug === 'kilimanjaro' ? 'photo-1464822759023-fed622ff2c3b' : ($destination->slug === 'zanzibar' ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
                <a class="destination-card" href="{{ route('destinations.show', $destination->slug) }}"><div class="destination-card-photo"><img src="{{ $destination->image_path ? asset($destination->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=1000&q=85' }}" alt="{{ $destination->name }}"></div><div class="destination-card-copy"><span class="eyebrow">{{ strtoupper($destination->country) }} · {{ str_pad((string)($index+1),2,'0',STR_PAD_LEFT) }}</span><h2>{{ $destination->name }}</h2><p>{{ $destination->summary }}</p><span class="text-link">Discover {{ $destination->name }} <span>↗</span></span></div></a>
            @endforeach</div>
            @include('public.pagination', ['paginator' => $destinations])
        @endif
    </section>
@endsection
