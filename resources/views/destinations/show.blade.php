@extends('layouts.site')
@section('title', $destination->name.' · '.$siteSettings['brand_name'])
@section('content')
    @php($fallbackPhoto = $destination->slug === 'kilimanjaro' ? 'photo-1464822759023-fed622ff2c3b' : ($destination->slug === 'zanzibar' ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
    <section class="detail-hero" style="background-image:linear-gradient(90deg,rgba(10,27,19,.67),rgba(10,27,19,.12)),url('{{ $destination->image_path ? asset($destination->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=2200&q=88' }}')"><div class="detail-hero-copy"><span class="eyebrow light">{{ strtoupper($destination->country) }} · DESTINATION NOTES</span><h1>{{ $destination->name }}</h1><p>{{ $destination->tagline }}</p></div></section>
    <section class="destination-story section-wrap"><div><span class="eyebrow">GET TO KNOW THE PLACE</span><h2>{{ $destination->tagline ?: 'A place to discover.' }}</h2></div><div class="prose">{!! nl2br(e($destination->description ?: $destination->summary)) !!}</div></section>
    <section class="related-tours section-wrap"><div class="section-heading"><div><span class="eyebrow">MADE FOR THIS PLACE</span><h2>Journeys in <em>{{ $destination->name }}.</em></h2></div></div>
        @if($tours->isEmpty())<div class="public-empty slim"><p>We are shaping journeys for this destination. Ask us about a custom route.</p><a class="text-link" href="{{ route('contact') }}">Plan with our team <span>↗</span></a></div>
        @else<div class="tour-list-grid">@foreach($tours as $tour)<a class="tour-tile compact" href="{{ route('tours.show', $tour->slug) }}"><div class="tour-tile-content"><div class="tile-meta">{{ $tour->duration_days }} DAYS</div><h2>{{ $tour->title }}</h2><p>{{ $tour->summary }}</p><div class="tile-bottom"><span>EXPLORE JOURNEY</span><b>↗</b></div></div></a>@endforeach</div>@include('public.pagination', ['paginator' => $tours])@endif
    </section>
@endsection
