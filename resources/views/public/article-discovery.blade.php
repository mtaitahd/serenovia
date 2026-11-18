@if($articles->isNotEmpty())
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-blog.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('js/home-blog.js') }}" defer></script>
@endpush
<section class="home-blog" id="journal" aria-labelledby="home-blog-title">
    <div class="home-blog-inner">
        <h2 id="home-blog-title">{{ $home['home_journal_title'] }}</h2>
        <div class="home-blog-track" id="home-blog-track" tabindex="0" aria-label="Latest articles">
        @foreach($articles as $article)
            <article class="home-blog-card">
                <a class="home-blog-article" href="{{ route('journal.show', $article->slug) }}" aria-labelledby="home-blog-article-{{ $article->id }}">
                    <div class="home-blog-photo">
                        <img src="{{ $homeRecordImage($article) }}" alt="" loading="lazy">
                        @if($article->category)<span class="home-blog-category">{{ $article->category }}</span>@endif
                    </div>
                    <div class="home-blog-copy">
                        <small>{{ $siteSettings['brand_name'] }}</small>
                        <h3 id="home-blog-article-{{ $article->id }}">{{ $article->title }}</h3>
                        <span class="home-blog-read">{{ $home['home_journal_read'] }}</span>
                    </div>
                </a>
            </article>
        @endforeach
        </div>
        <div class="home-blog-pages" role="group" aria-label="Blog carousel pages" hidden></div>
        <a class="home-blog-more" href="{{ $homeLink('home_journal_url') }}">{{ $home['home_journal_button'] }}</a>
    </div>
</section>
@endif
