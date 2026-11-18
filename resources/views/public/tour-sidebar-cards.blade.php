<section class="detail-side-card detail-related-links">
    <h2>Related Links</h2>
    <ul>
        @if($tour->destination)<li><a href="{{ route('destinations.show', $tour->destination->slug) }}">Explore {{ $tour->destination->name }}</a></li>@endif
        @foreach($tour->categories->take(3) as $category)
            <li><a href="{{ route('tours.index', ['category' => $category->slug]) }}">{{ $category->name }} tours</a></li>
        @endforeach
        <li><a href="{{ route('tours.index', ['min_days' => $tour->duration_days, 'max_days' => $tour->duration_days]) }}">{{ $tour->duration_days }}-day tours</a></li>
    </ul>
</section>
@php
    $dayGallery = collect($itineraryDays)->flatMap(fn ($day) => collect($day['images'] ?? [])
        ->filter()->map(fn ($path) => ['path' => $path, 'caption' => $day['label'].' · '.$day['title']]))
        ->unique('path')->values();
@endphp
@if($dayGallery->isNotEmpty())
    <section class="detail-side-card detail-day-gallery">
        @foreach($dayGallery as $photo)
            <a href="{{ asset($photo['path']) }}" data-photo data-photo-group="sidebar-day-gallery" data-caption="{{ $photo['caption'] }}" @unless($loop->first) hidden @endunless>
                @if($loop->first)
                    <img src="{{ asset($photo['path']) }}" alt="" loading="lazy">
                    <span class="detail-gallery-label">Open Day Photos ({{ $dayGallery->count() }})</span>
                @endif
            </a>
        @endforeach
    </section>
@endif
@if($tour->route_map_enabled && count($itineraryDays))
    <section class="detail-side-card detail-related-links"><h2>Safari route</h2><a href="{{ strtok($tabUrl('overview'), '#') }}#journey-route-map">View route map ↗</a></section>
@endif
