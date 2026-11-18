@php
    $tourUrl = route('tours.show', $tour->slug);
    $type = ucfirst($tour->tour_type);
    $days = (int) $tour->duration_days;
    $route = ['Start' => $tour->starting_point, 'You visit' => $tour->destination?->name, 'End' => $tour->ending_point];
@endphp
<article class="home-tour-card">
    <a class="home-tour-cover" href="{{ $tourUrl }}">
        <img src="{{ $homePhotoUrl($tourPhoto($tour)) }}" alt="" loading="lazy">
        <h3>@if($days && !preg_match('/^\s*\d+\s*[-– ]?\s*days?\b/i', $tour->title)){{ $days }} {{ Str::plural('day', $days) }} – @endif{{ $tour->title }}</h3>
    </a>
    <div class="home-tour-body">
        <p class="home-tour-country">{{ $tour->destination?->country ?? 'Serenovia' }} · {{ $type }}</p>
        <p class="home-tour-price">
            @if($tour->price_from !== null)
                @php($price = number_format((float) $tour->price_from, (float) $tour->price_from === floor((float) $tour->price_from) ? 0 : 2))
                <strong>{{ $tour->currency === 'USD' ? '$'.$price : $tour->currency.' '.$price }}</strong>
                <small>Starting price ({{ $tour->currency }})</small>
            @else
                <strong>Price on request</strong>
            @endif
        </p>
        @if(array_filter($route))
            <dl class="home-tour-route">
                @foreach(array_filter($route) as $label => $place)
                    <div @class(['home-tour-visit' => $label === 'You visit'])><dt>{{ $label }}</dt><dd>{{ $place }}</dd></div>
                @endforeach
            </dl>
        @endif
        <div class="home-tour-actions">
            <a class="home-tour-itinerary" href="{{ $tourUrl }}">View itinerary</a>
            <a class="home-tour-enquire" href="{{ route('contact', ['tour' => $tour->id]) }}">Enquire now</a>
            @if($whatsapp)
                <a class="home-tour-chat" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hello, I am interested in '.$tour->title.'.') }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp about {{ $tour->title }}" title="Chat on WhatsApp">
                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 18.5 4 22l3.7-1.1A9 9 0 1 0 5 18.5Z"/><path d="M9 8c-.7.3-1 .9-.7 1.7.8 2.4 2.8 4.4 5.2 5.2.8.3 1.5 0 1.8-.8l.2-.6-2-1-1 1a6 6 0 0 1-2.7-2.7l.9-.9-1-2Z"/></svg>
                </a>
            @endif
        </div>
    </div>
</article>
