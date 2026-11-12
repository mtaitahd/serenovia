@if($articles->isNotEmpty())
<section class="article-discovery" id="journal" aria-label="Travel articles">
    <div class="article-discovery-track" tabindex="0" aria-label="Article cards. Scroll to explore.">
        @foreach($articles as $article)
            @php($fallbackPhoto = str_contains(strtolower($article->category ?? ''), 'kilimanjaro') ? 'photo-1464822759023-fed622ff2c3b' : (str_contains(strtolower($article->category ?? ''), 'island') ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
            <article class="discovery-card"><a href="{{ route('journal.show', $article->slug) }}">
                <div class="discovery-card-photo"><img src="{{ $article->image_path ? asset($article->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=950&q=85' }}" alt="{{ $article->title }}" loading="lazy"></div>
                <div class="discovery-card-copy"><span class="discovery-category">{{ $article->category }}</span><h3>{{ $article->title }}</h3><div class="discovery-card-reveal">@if($article->excerpt)<p>{{ $article->excerpt }}</p>@endif<span class="discovery-read">Read the story <span>↗</span></span></div><span class="discovery-open" aria-hidden="true">↑</span></div>
            </a></article>
        @endforeach
    </div>
    <div class="article-slider-controls" hidden><button type="button" data-article-prev aria-label="Previous articles">←</button><button type="button" data-article-next aria-label="Next articles">→</button></div>
</section>
@endif
