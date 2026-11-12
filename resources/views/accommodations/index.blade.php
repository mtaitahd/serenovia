@extends('layouts.site')
@section('title', 'Places to stay · '.$siteSettings['brand_name'])
@section('description', 'Explore the camps and lodges selected for Serenovia safari journeys.')
@section('content')
    <section class="listing-hero page-photo-hero">@include('public.hero-image', ['heroKey' => 'accommodations'])<span class="eyebrow">REST WELL, TRAVEL WELL</span><h1>Places to <em>stay.</em></h1><p>Explore camps and lodges selected for their location, character and warm Tanzanian welcome.</p></section>
    <section class="listing-wrap section-wrap">
        <form class="tour-filters" method="GET" action="{{ route('accommodations.index') }}">
            <label class="tour-filter-search"><span>FIND A STAY</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search camps and lodges"></label>
            <label><span>DESTINATION</span><select name="destination"><option value="">Every destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected((string)request('destination')===(string)$destination->id)>{{ $destination->name }}</option>@endforeach</select></label>
            <label><span>COMFORT LEVEL</span><select name="tier"><option value="">All stays</option><option value="budget" @selected(request('tier')==='budget')>Budget</option><option value="mid_range" @selected(request('tier')==='mid_range')>Mid-range</option><option value="luxury" @selected(request('tier')==='luxury')>Luxury</option></select></label>
            <span></span><button class="button button-dark" type="submit">Find a stay <span>↗</span></button>
        </form>
        @if($accommodations->isEmpty())
            <div class="public-empty"><span>⌂</span><h2>{{ request()->hasAny(['q','destination','tier']) ? 'No stays match those filters.' : 'Our stay collection is taking shape.' }}</h2><p>Ask our local team to recommend the right camp or lodge for your journey.</p><a class="button button-dark" href="{{ route('contact') }}">Ask our team <span>↗</span></a></div>
        @else
            <div class="stay-list-grid">
                @foreach($accommodations as $stay)
                    <article class="stay-card">@if($stay->image_path)<img src="{{ asset($stay->image_path) }}" alt="{{ $stay->name }}" loading="lazy">@else<div class="stay-card-placeholder">SERENOVIA STAYS</div>@endif<div class="stay-card-content"><span class="eyebrow">{{ ucwords(str_replace('_',' ',$stay->tier)) }} COMFORT · {{ $stay->destination?->name ?? 'TANZANIA' }}</span><h2>{{ $stay->name }}</h2><p>{{ $stay->description }}</p>@if($stay->amenities)<div class="stay-amenities">@foreach(array_slice($stay->amenities, 0, 4) as $amenity)<span>{{ $amenity }}</span>@endforeach</div>@endif<div class="tile-bottom"><span>{{ $stay->price_from ? 'FROM '.$stay->currency.' '.number_format((float)$stay->price_from) : ($stay->location ?: 'AVAILABLE IN CUSTOM ITINERARIES') }}</span><a href="{{ route('contact') }}">ASK ABOUT THIS STAY ↗</a></div></div></article>
                @endforeach
            </div>
            @include('public.pagination', ['paginator' => $accommodations])
        @endif
    </section>
    <section class="plan section-wrap"><span class="eyebrow">MATCH THE STAY TO THE JOURNEY</span><h2>Your comfort, <em>your way.</em></h2><p>Tell us your travel style and we will build the right mix of camps and lodges into your itinerary.</p><a class="button button-dark" href="{{ route('contact') }}">Plan your trip <span>↗</span></a></section>
@endsection
