@if($articles->isNotEmpty())
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-blog.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('js/home-blog.js') }}" defer></script>
@endpush
<section class="home-blog" id="journal" aria-labelledby="home-blog-title">
    <div class="home-blog-inner">
        <h2 id="home-blog-title">Explore Our Blogs</h2>
        <div class="home-blog-track" id="home-blog-track" tabindex="0" aria-label="Latest articles">
        @foreach($articles as $article)
            @php
                $topic = Str::lower($article->category.' '.$article->title);
                $photo = match (true) {
                    Str::contains($topic, ['kilimanjaro', 'trekking']) => 'kilimanjaro',
                    Str::contains($topic, ['zanzibar', 'island']) => 'zanzibar',
                    Str::contains($topic, 'migration') => 'migration',
                    default => ['serengeti-sunset', 'serengeti-leopard', 'elephant'][$loop->index % 3],
                };
            @endphp
            <article class="home-blog-card">
                <a class="home-blog-article" href="{{ route('journal.show', $article->slug) }}" aria-labelledby="home-blog-article-{{ $article->id }}">
                    <div class="home-blog-photo">
                        <img src="{{ $homePhotoUrl($photo) }}" alt="" loading="lazy">
                        @if($article->category)<span class="home-blog-category">{{ $article->category }}</span>@endif
                    </div>
                    <div class="home-blog-copy">
                        <small>{{ $siteSettings['brand_name'] }}</small>
                        <h3 id="home-blog-article-{{ $article->id }}">{{ $article->title }}</h3>
                        <span class="home-blog-read">Read full article</span>
                    </div>
                </a>
            </article>
        @endforeach
        </div>
        <div class="home-blog-pages" role="group" aria-label="Blog carousel pages" hidden></div>
        <a class="home-blog-more" href="{{ route('journal.index') }}">See more blogs</a>
    </div>
</section>
@endif
