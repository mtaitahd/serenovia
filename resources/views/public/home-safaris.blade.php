@php
    $safariSlides = $safariTours;
    $safariFallback = $home['home_safaris_image'] ?? '';
    if (!$safariFallback || !is_file(public_path($safariFallback))) {
        $safariFallback = 'images/home-safari/serengeti-sunset.jpg';
    }
@endphp
<section class="home-stays home-safaris" id="our-safaris" aria-labelledby="home-safaris-title">
    <div class="home-stays-media" data-home-carousel>
        <div id="home-safari-slides">
            @forelse($safariSlides as $tour)
                <article class="home-stays-slide" data-stay-slide @if(!$loop->first) hidden @endif>
                    <img src="{{ $homeRecordImage($tour) }}" alt="" loading="lazy">
                    <div class="home-stays-caption">
                        <h3>{{ $tour->title }}</h3>
                        <a href="{{ route('tours.show', $tour->slug) }}" aria-label="Explore {{ $tour->title }}">⟶</a>
                    </div>
                </article>
            @empty
                <article class="home-stays-slide">
                    <img src="{{ asset($safariFallback) }}" alt="" loading="lazy">
                    <div class="home-stays-caption">
                        <h3>Explore Tanzania</h3>
                        <a href="{{ route('tours.index') }}" aria-label="Explore Tanzania safaris">⟶</a>
                    </div>
                </article>
            @endforelse
        </div>
        @if($safariSlides->count() > 1)
            <div class="home-stays-controls" role="group" aria-label="Choose a safari slide" hidden>
                @foreach($safariSlides as $tour)
                    <button type="button" aria-controls="home-safari-slides" aria-label="Show {{ $tour->title }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="home-stays-intro">
        <h2 id="home-safaris-title">{{ $home['home_safaris_title'] }}</h2>
        <p>{{ $home['home_safaris_copy'] }}</p>
        <a class="home-stays-button" href="{{ $homeLink('home_safaris_url') }}">{{ $home['home_safaris_button'] }}</a>
    </div>
</section>
