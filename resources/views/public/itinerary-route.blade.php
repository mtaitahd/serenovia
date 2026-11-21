@if($tour->route_map_enabled && count($itineraryDays))
@php($mountainTrail = \App\Support\MountainTrail::forTour($tour))
@php($routeStops = collect(\App\Support\RouteStops::forTour($tour, $itineraryDays)))
<div class="itinerary-route-map" id="journey-route-map" data-itinerary-map data-route-type="{{ $tour->tour_type }}" data-route-start="{{ json_encode(\App\Support\RouteStops::start($tour)) }}">
    <script type="application/json" data-route-trail>@json($mountainTrail)</script>
    <script type="application/json" data-route-data>@json($routeStops)</script>
    <div class="route-map-top"><div><span class="eyebrow">FOLLOW THE JOURNEY</span><h3>{{ $tour->tour_type === 'mountain' ? 'Your trek, on the map.' : 'Your journey, on the map.' }}</h3></div><div class="route-play-controls"><button type="button" data-route-play hidden>▶ Play journey</button><button type="button" data-route-reset hidden aria-label="Restart route animation">↺</button></div></div>
    <div class="route-map-stage"><div class="route-map-canvas" data-route-canvas aria-label="Itinerary route map"></div><span class="route-map-badge" data-route-mode>{{ $tour->tour_type === 'mountain' ? 'Mountain trekking route' : 'Safari route map' }}</span></div>
    <div class="route-map-current"><img data-route-photo alt="" hidden><div><span class="eyebrow" data-route-day-label>DAY 1</span><strong data-route-current-title>{{ $routeStops->first()['title'] }}</strong><span data-route-current-location>{{ $routeStops->first()['label'] }}</span></div></div>
    <div class="route-day-tabs" aria-label="Select itinerary day">@foreach($routeStops as $stop)<button type="button" data-route-stop="{{ $loop->index }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">Day {{ $stop['day'] }}</button>@endforeach</div>
    <p class="route-map-note" data-route-note>Itinerary connections show the travel sequence, not driving directions.</p>@if($mountainTrail)<p class="route-map-note">Trail: © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a> · <a href="{{ asset('js/data/machame-trail.json') }}">Route data</a> (<a href="{{ asset('js/data/machame-trail-LICENSE.txt') }}">ODbL</a>). Walking trail only; hotel transfers are excluded.</p>@endif<span class="visually-hidden" aria-live="polite" data-route-status></span>
</div>
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">@endpush
@push('scripts')<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script><script src="{{ asset('js/itinerary-route.js') }}?v=4" defer></script>@endpush
@endif
