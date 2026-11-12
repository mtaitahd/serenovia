@extends('layouts.site')
@section('title', 'Safari journeys · '.$siteSettings['brand_name'])
@section('content')
    <section class="listing-hero page-photo-hero">@include('public.hero-image', ['heroKey' => 'safaris'])<span class="eyebrow">{{ $siteSettings['safaris_listing_eyebrow'] ?? 'MADE FOR YOUR KIND OF WONDER' }}</span><h1>{{ $siteSettings['safaris_listing_title'] ?? 'Safari journeys.' }}</h1><p>{{ $siteSettings['safaris_listing_intro'] ?? "Explore the journeys currently being shaped by Serenovia's local team." }}</p></section>
    <section class="listing-wrap section-wrap">
        <form class="tour-filters" method="GET" action="{{ route('tours.index') }}">
            @if(request('country'))<input type="hidden" name="country" value="{{ request('country') }}">@endif
            <label class="tour-filter-search"><span>FIND A JOURNEY</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search safari journeys"></label>
            <label><span>DESTINATION</span><select name="destination"><option value="">Every destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected((string)request('destination')===(string)$destination->id)>{{ $destination->name }}</option>@endforeach</select></label>
            <label><span>JOURNEY TYPE</span><select name="type"><option value="">All experiences</option>@foreach(['safari','mountain','island','cultural','day trip'] as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ ucwords($type) }}</option>@endforeach</select></label>
            <label><span>COMFORT</span><select name="comfort"><option value="">Any stay</option><option value="budget" @selected(request('comfort')==='budget')>Budget</option><option value="mid_range" @selected(request('comfort')==='mid_range')>Mid-range</option><option value="luxury" @selected(request('comfort')==='luxury')>Luxury</option></select></label>
            <button class="button button-dark" type="submit">Show journeys <span>↗</span></button>
            @if(request()->hasAny(['q','country','destination','type','category','comfort']))<a class="tour-filter-clear" href="{{ route('tours.index') }}">Clear filters</a>@endif
        </form>
        @if($countries->isNotEmpty())
            <div class="tour-country-filter"><span class="eyebrow">EXPLORE BY COUNTRY</span><nav class="tour-category-pills" aria-label="Filter journeys by country"><a class="{{ request('country') ? '' : 'is-active' }}" href="{{ route('tours.index', request()->except('country','page')) }}">All countries</a>@foreach($countries as $country)<a class="{{ request('country')===$country->slug ? 'is-active' : '' }}" href="{{ route('tours.index', array_merge(request()->except('page'), ['country'=>$country->slug])) }}">{{ $country->name }} <span>{{ $country->published_tours_count }}</span></a>@endforeach</nav></div>
        @endif
        @if($categories->where('packages_count', '>', 0)->isNotEmpty())
            <nav class="tour-category-pills" aria-label="Filter journeys by category"><a class="{{ request('category') ? '' : 'is-active' }}" href="{{ route('tours.index', request()->except('category','page')) }}">All journeys</a>@foreach($categories->where('packages_count', '>', 0) as $category)<a class="{{ request('category')===$category->slug ? 'is-active' : '' }}" href="{{ route('tours.index', array_merge(request()->except('page'), ['category'=>$category->slug])) }}">{{ $category->name }} <span>{{ $category->packages_count }}</span></a>@endforeach</nav>
        @endif
        @if($tours->isEmpty())
            <div class="public-empty"><span>✳</span><h2>{{ request()->hasAny(['q','destination','type','category','comfort']) ? 'No journeys match those filters.' : 'Good journeys take thought.' }}</h2><p>{{ request()->hasAny(['q','destination','type','category','comfort']) ? 'Try clearing a filter or ask our team to shape a custom Tanzania trip.' : 'Our safari collection is being prepared. Tell us what you would love to experience and we can start planning a custom Tanzania trip.' }}</p><a class="button button-dark" href="{{ route('contact') }}">Plan a custom journey <span>↗</span></a></div>
        @else
            <div class="tour-list-grid">
                @foreach($tours as $tour)
                    @php($fallbackPhoto = $tour->tour_type === 'mountain' ? 'photo-1464822759023-fed622ff2c3b' : ($tour->tour_type === 'island' ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
                    <a class="tour-tile" href="{{ route('tours.show', $tour->slug) }}">
                        <div class="tour-tile-photo"><img src="{{ $tour->image_path ? asset($tour->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=1000&q=85' }}" alt="{{ $tour->title }}"><span>{{ strtoupper($tour->tour_type) }}</span></div>
                        <div class="tour-tile-content"><div class="tile-meta">{{ $tour->duration_days }} DAYS <i>·</i> {{ $tour->destination?->country ?? 'TANZANIA' }} <i>·</i> {{ $tour->destination?->name ?? 'TANZANIA' }}</div><h2>{{ $tour->title }}</h2><p>{{ $tour->summary ?: $tour->description }}</p>@if($tour->categories->isNotEmpty())<div class="tour-card-categories">@foreach($tour->categories->take(3) as $category)<span>{{ $category->name }}</span>@endforeach</div>@endif<div class="tile-bottom"><span>{{ $tour->price_from ? 'FROM '.$tour->currency.' '.number_format((float)$tour->price_from) : 'PRICE ON REQUEST' }}</span><b>↗</b></div></div>
                    </a>
                @endforeach
            </div>
            @include('public.pagination', ['paginator' => $tours])
        @endif
    </section>
    <section class="plan section-wrap"><span class="eyebrow">YOUR TRIP, YOUR WAY</span><h2>Have a different idea <em>in mind?</em></h2><p>We can help you plan a route around your dates, interests and pace.</p><a class="button button-dark" href="{{ route('contact') }}">Talk to our team <span>↗</span></a></section>
@endsection
