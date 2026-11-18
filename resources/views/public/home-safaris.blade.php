@php($safariSlides = $featuredTours)
<section class="home-stays home-safaris" id="our-safaris" aria-labelledby="home-safaris-title">
    <div class="home-stays-media" data-home-carousel>
        <div id="home-safari-slides">
            @forelse($safariSlides as $tour)
                <article class="home-stays-slide" data-stay-slide @if(!$loop->first) hidden @endif>
                    <img src="{{ $homePhotoUrl($tourPhoto($tour)) }}" alt="" loading="lazy">
                    <div class="home-stays-caption">
                        <h3>{{ $tour->title }}</h3>
                        <a href="{{ route('tours.show', $tour->slug) }}" aria-label="Explore {{ $tour->title }}">⟶</a>
                    </div>
                </article>
            @empty
                <article class="home-stays-slide">
                    <img src="{{ $homePhotoUrl('serengeti-sunset') }}" alt="" loading="lazy">
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
        <h2 id="home-safaris-title">Our safaris in Tanzania</h2>
        <p>Watch wildlife on the Serengeti plains, explore Tarangire’s baobabs or spend time with local communities. Our team can help you choose a route and travel at your own pace.</p>
        <a class="home-stays-button" href="{{ route('tours.index') }}">Discover more</a>
    </div>
</section>
