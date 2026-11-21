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
                    <img src="{{ asset($photo['path']) }}" alt="{{ $photo['caption'] }}" loading="lazy">
                    <span class="detail-gallery-label">Open Day Photos ({{ $dayGallery->count() }})</span>
                @endif
            </a>
        @endforeach
    </section>
@endif
@if($tour->tour_type === 'safari')
    @php($transportGallery = collect($itineraryDays)->flatMap(fn ($day) => $day['transport_images'] ?? [])->filter()->unique()->take(5)->values())
    @if($transportGallery->isNotEmpty())
        <section class="detail-side-card detail-day-gallery">
            @foreach($transportGallery as $image)
                <a href="{{ asset($image) }}" data-photo data-photo-group="sidebar-transport-gallery" data-caption="Safari transport" @unless($loop->first) hidden @endunless>
                    @if($loop->first)<img src="{{ asset($image) }}" alt="Safari transport" loading="lazy"><span class="detail-gallery-label">Open Transport Photos ({{ $transportGallery->count() }})</span>@endif
                </a>
            @endforeach
        </section>
    @endif
@endif
@if($tour->route_map_enabled && count($itineraryDays))
    <section class="detail-side-card detail-related-links"><h2>{{ $tour->tour_type === 'mountain' ? 'Trekking route' : 'Safari route' }}</h2><a href="{{ strtok($tabUrl('overview'), '#') }}#journey-route-map">View route map</a></section>
@endif
