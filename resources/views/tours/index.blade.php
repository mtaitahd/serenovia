@extends('layouts.site')
@section('title', 'Safari journeys · '.$siteSettings['brand_name'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tour-catalogue.css') }}?v=6">
@endpush
@push('scripts')
    <script src="{{ asset('js/tour-price-range.js') }}?v=2" defer></script>
@endpush
@section('content')
    <section class="listing-hero page-photo-hero">@include('public.hero-image', ['heroKey' => 'safaris'])<span class="eyebrow">{{ $siteSettings['safaris_listing_eyebrow'] ?? 'MADE FOR YOUR KIND OF WONDER' }}</span><h1>{{ $siteSettings['safaris_listing_title'] ?? 'Safari journeys.' }}</h1><p>{{ $siteSettings['safaris_listing_intro'] ?? "Explore the journeys currently being shaped by Serenovia's local team." }}</p></section>
    <section class="listing-wrap section-wrap tour-catalogue">
        <aside class="catalogue-sidebar" aria-labelledby="catalogue-filter-title">
        <form class="tour-filters catalogue-filters" method="GET" action="{{ route('tours.index') }}">
            <h2 id="catalogue-filter-title">Find your trip</h2>
            @if(request('country'))<input type="hidden" name="country" value="{{ request('country') }}">@endif
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            <label class="tour-filter-search"><span>FIND A JOURNEY</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search safari journeys"></label>
            <label><span>DESTINATION</span><select name="destination"><option value="">Every destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected((string)request('destination')===(string)$destination->id)>{{ $destination->name }}</option>@endforeach</select></label>
            <label><span>JOURNEY TYPE</span><select name="type"><option value="">All experiences</option>@foreach(['safari','mountain','island','cultural','day trip'] as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ ucwords($type) }}</option>@endforeach</select></label>
            <label><span>COMFORT</span><select name="comfort"><option value="">Any stay</option><option value="budget" @selected(request('comfort')==='budget')>Budget</option><option value="mid_range" @selected(request('comfort')==='mid_range')>Mid-range</option><option value="luxury" @selected(request('comfort')==='luxury')>Luxury</option></select></label>
            <fieldset data-price-filter data-unit="days" data-bounds="{{ json_encode($lengthBounds) }}">
                <legend>Tour length</legend>
                <div class="catalogue-price-slider" hidden>
                    <div class="price-range-track"></div>
                    <input type="range" step="1" aria-label="Minimum tour days" data-price-thumb="min">
                    <input type="range" step="1" aria-label="Maximum tour days" data-price-thumb="max">
                </div>
                <div class="price-inputs">
                    <label><span>Minimum days</span><input data-range-field type="number" min="1" name="min_days" value="{{ request()->integer('min_days') ?: '' }}" placeholder="{{ $lengthBounds['days']['min'] }}"></label>
                    <label><span>Maximum days</span><input data-range-field type="number" min="1" name="max_days" value="{{ request()->integer('max_days') ?: '' }}" placeholder="{{ $lengthBounds['days']['max'] }}"></label>
                </div>
            </fieldset>
                <fieldset data-price-filter data-bounds="{{ $priceBounds->toJson() }}">
                    <legend>Filter by starting price</legend>
                    <div class="catalogue-price-slider" hidden>
                        <div class="price-range-track"></div>
                        <input type="range" step="any" aria-label="Minimum starting price" data-price-thumb="min">
                        <input type="range" step="any" aria-label="Maximum starting price" data-price-thumb="max">
                    </div>
                    @if($currencies->isNotEmpty())
                    <label><span>Currency</span><select name="price_currency">@foreach($currencies as $currency)<option @selected($currency === $priceCurrency)>{{ $currency }}</option>@endforeach</select></label>
                    <div class="price-inputs">
                        <label><span>Minimum</span><input data-range-field type="number" min="0" step="0.01" name="min_price" value="{{ $minPrice }}" placeholder="{{ $priceBounds[$priceCurrency]['min'] }}"></label>
                        <label><span>Maximum</span><input data-range-field type="number" min="0" step="0.01" name="max_price" value="{{ $maxPrice }}" placeholder="{{ $priceBounds[$priceCurrency]['max'] }}"></label>
                    </div>
                    <small>Trips priced on request are excluded when a price is set.</small>
                    @else
                        <div class="catalogue-price-slider is-unavailable" aria-hidden="true"><div class="price-range-track"></div></div>
                        <small>Price on request. Set a starting price on a published safari to enable this filter.</small>
                    @endif
                </fieldset>
            <button class="button button-dark" type="submit">Show journeys <span>↗</span></button>
            <a class="tour-filter-clear" href="{{ route('tours.index') }}">Clear filters</a>
        </form>
        </aside>
        <div class="catalogue-results">
        <div class="catalogue-filter-strip" aria-label="Country and category filters" role="region" tabindex="0">
        @if($countries->isNotEmpty())
            <div class="tour-country-filter"><span class="eyebrow">EXPLORE BY COUNTRY</span><nav class="tour-category-pills" aria-label="Filter journeys by country"><a class="{{ request('country') ? '' : 'is-active' }}" href="{{ route('tours.index', request()->except('country','page')) }}">All countries</a>@foreach($countries as $country)<a class="{{ request('country')===$country->slug ? 'is-active' : '' }}" href="{{ route('tours.index', array_merge(request()->except('page'), ['country'=>$country->slug])) }}">{{ $country->name }} <span>{{ $country->published_tours_count }}</span></a>@endforeach</nav></div>
        @endif
        @if($categories->where('packages_count', '>', 0)->isNotEmpty())
            <nav class="tour-category-pills" aria-label="Filter journeys by category"><a class="{{ request('category') ? '' : 'is-active' }}" href="{{ route('tours.index', request()->except('category','page')) }}">All journeys</a>@foreach($categories->where('packages_count', '>', 0) as $category)<a class="{{ request('category')===$category->slug ? 'is-active' : '' }}" href="{{ route('tours.index', array_merge(request()->except('page'), ['category'=>$category->slug])) }}">{{ $category->name }} <span>{{ $category->packages_count }}</span></a>@endforeach</nav>
        @endif
        </div>
        <p class="catalogue-count">{{ $tours->total() }} {{ \Illuminate\Support\Str::plural('journey', $tours->total()) }} found</p>
        @if($tours->isEmpty())
            <div class="public-empty"><span>✳</span><h2>{{ request()->hasAny(['q','destination','type','category','comfort']) ? 'No journeys match those filters.' : 'Good journeys take thought.' }}</h2><p>{{ request()->hasAny(['q','destination','type','category','comfort']) ? 'Try clearing a filter or ask our team to shape a custom Tanzania trip.' : 'Our safari collection is being prepared. Tell us what you would love to experience and we can start planning a custom Tanzania trip.' }}</p><a class="button button-dark" href="{{ route('contact') }}">Plan a custom journey <span>↗</span></a></div>
        @else
            <div class="tour-list-grid">
                @foreach($tours as $tour)
                    @include('public.tour-catalogue-card')
                @endforeach
            </div>
            {{ $tours->onEachSide(1)->links('public.numbered-pagination') }}
        @endif
        </div>
    </section>
    <section class="plan section-wrap"><span class="eyebrow">YOUR TRIP, YOUR WAY</span><h2>Have a different idea <em>in mind?</em></h2><p>We can help you plan a route around your dates, interests and pace.</p><a class="button button-dark" href="{{ route('contact') }}">Talk to our team <span>↗</span></a></section>
@endsection
