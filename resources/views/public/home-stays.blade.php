@if($featuredStays->isNotEmpty())
    <section class="home-stays" id="stays" aria-labelledby="home-stays-title">
        <div class="home-stays-intro">
            <h2 id="home-stays-title">Tanzania safari accommodation</h2>
            <p>Explore camps and lodges selected for their location, character and warm Tanzanian welcome. Find a place to rest that fits your route and travel style.</p>
            <a class="home-stays-button" href="{{ route('accommodations.index') }}">Discover more</a>
        </div>
        <div class="home-stays-media" data-home-carousel>
            <div id="home-stays-slides">
                @foreach($featuredStays as $stay)
                    <article class="home-stays-slide" data-stay-slide @if(!$loop->first) hidden @endif>
                        @php($stayPhoto = match ($stay->slug) {
                            'sample-lake-eyasi-guesthouse' => 'village',
                            'sample-tarangire-safari-lodge' => 'baobabs',
                            default => 'lodge',
                        })
                        <img src="{{ $homePhotoUrl($stayPhoto) }}" alt="" loading="lazy">
                        <div class="home-stays-caption">
                            <small>Illustrative photo</small>
                            <h3>{{ $stay->name }}</h3>
                            @if($stay->description || $stay->location || $stay->destination?->name)
                                <p>{{ Str::limit($stay->description ?: ($stay->location ?: $stay->destination?->name), 215) }}</p>
                            @endif
                            <a href="{{ route('accommodations.index', ['q' => $stay->name]) }}" aria-label="Explore {{ $stay->name }}">⟶</a>
                        </div>
                    </article>
                @endforeach
            </div>
            @if($featuredStays->count() > 1)
                <div class="home-stays-controls" role="group" aria-label="Choose an accommodation slide" hidden>
                    @foreach($featuredStays as $stay)
                        <button type="button" aria-controls="home-stays-slides" aria-label="Show {{ $stay->name }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
