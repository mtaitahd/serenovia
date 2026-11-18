<section class="safari-section itinerary-editorial" id="itinerary">
    <h2>Day by day</h2>
    @php
        $staysByDay = $tour->accommodations->groupBy('pivot.day_number');
    @endphp
    @if(!empty($tour->practical_details['arrival']))
        <div class="itinerary-arrival"><h3>Arrival</h3><p>{{ $tour->practical_details['arrival'] }} <a href="{{ $tabUrl('getting-there') }}">Getting there ↗</a></p></div>
    @endif
    @foreach($itineraryDays as $index => $day)
        @php
            $photos = collect($day['images'] ?? [])->filter()->unique()->take(5)->values();
            $dayStays = collect($staysByDay->get($index + 1, []));
            $facts = [];
            foreach (\App\Support\SafariDays::TRAIL_FIELDS as $key => $label) {
                if (!empty($day[$key])) $facts[$label] = $day[$key];
            }
        @endphp
        <article class="itinerary-day" id="tour-day-{{ $index + 1 }}" data-itinerary-day="{{ $index }}">
            <header class="itinerary-day-heading {{ $photos->isNotEmpty() ? 'has-photos' : '' }}">
                @if($photos->isNotEmpty())
                    <div class="itinerary-day-collage itinerary-day-photos collage-count-{{ $photos->count() }}">
                        @foreach($photos as $image)
                            <a href="{{ asset($image) }}" data-photo data-photo-group="itinerary-{{ $index }}" data-caption="{{ $day['label'] }} · {{ $day['title'] }}" aria-label="Open {{ $day['label'] }} photograph {{ $loop->iteration }}"><img src="{{ asset($image) }}" alt="" loading="lazy"></a>
                        @endforeach
                    </div>
                @endif
                <div class="itinerary-day-title"><span>{{ $day['label'] }}</span>@if($day['title'])<h3>{{ $day['title'] }}</h3>@endif</div>
            </header>
            @if($dayStays->isNotEmpty() || !empty($day['overnight_camp']))
                <div class="itinerary-accommodation-band"><span>Accommodation</span><strong>{{ $dayStays->isNotEmpty() ? $dayStays->pluck('name')->unique()->implode(', ') : $day['overnight_camp'] }}</strong></div>
            @endif
            <div class="itinerary-day-body">
                <div class="itinerary-day-description prose">{!! $day['html'] !!}</div>
                @if(!empty($day['location']) || !empty($day['meals']) || $facts)
                    <aside class="itinerary-day-plan" aria-label="{{ $day['label'] }} details">
                        <h4>Your {{ strtolower($day['label']) }}</h4>
                        @if(!empty($day['location']))<div><h5>Main destination</h5><p>{{ $day['location'] }}</p></div>@endif
                        @foreach($facts as $label => $value)<div><h5>{{ $label }}</h5><p>{{ $value }}</p></div>@endforeach
                        @if(!empty($day['meals']))<div><h5>Meal plan</h5><p>{{ $day['meals'] }}</p></div>@endif
                    </aside>
                @endif
            </div>
            @foreach($dayStays as $stay)
                @php($stayImages = collect($stay->gallery_photo_ids ?? [])->map(fn ($id) => $stayPhotos->get($id))->prepend($stay->image_path)->filter()->unique()->take(5)->values())
                <section class="itinerary-stay">
                    <div class="itinerary-stay-copy"><span class="eyebrow">{{ $day['label'] }} · Accommodation</span><h4>{{ $stay->name }}</h4>@if($stay->location)<p>{{ $stay->location }}</p>@endif @if($stay->tier)<span class="itinerary-stay-tier">{{ ucfirst($stay->tier) }}</span>@endif</div>
                    @if($stayImages->isNotEmpty())
                        <div class="itinerary-stay-gallery">
                            @foreach($stayImages as $path)<a href="{{ asset($path) }}" data-photo data-photo-group="stay-{{ $stay->id }}-{{ $index + 1 }}" data-caption="{{ $stay->name }}" aria-label="Open {{ $stay->name }} photograph {{ $loop->iteration }}"><img src="{{ asset($path) }}" alt="" loading="lazy"><span>{{ $stay->name }}</span></a>@endforeach
                        </div>
                    @endif
                </section>
            @endforeach
            @if(!empty($day['transport_images']))
                <section class="itinerary-transport"><h4>Transport</h4><div class="itinerary-transport-gallery">@foreach(array_slice($day['transport_images'], 0, 5) as $image)<a href="{{ asset($image) }}" data-photo data-photo-group="transport-{{ $index }}" data-caption="{{ $day['label'] }} · Transport" aria-label="Open transport photograph {{ $loop->iteration }}"><img src="{{ asset($image) }}" alt="" loading="lazy"></a>@endforeach</div></section>
            @endif
        </article>
    @endforeach
</section>


