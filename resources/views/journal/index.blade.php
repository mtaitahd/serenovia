@extends('layouts.site')
@section('title', 'Travel journal · '.$siteSettings['brand_name'])
@section('content')
    <section class="listing-hero page-photo-hero">@include('public.hero-image', ['heroKey' => 'journal'])<span class="eyebrow">{{ $siteSettings['journal_listing_eyebrow'] ?? 'NOTES FROM TANZANIA' }}</span><h1>{{ $siteSettings['journal_listing_title'] ?? 'The Serenovia journal.' }}</h1><p>{{ $siteSettings['journal_listing_intro'] ?? 'Small stories, local perspectives and inspiration for the places that stay with you.' }}</p></section>
    <section class="listing-wrap section-wrap">
        @if($articles->isEmpty())<div class="public-empty"><h2>Field notes are on their way.</h2><p>Our travel journal will share stories, inspiration and local perspectives.</p></div>
        @else
            <div class="article-grid journal-list">@foreach($articles as $article)
                @php($fallbackPhoto = str_contains(strtolower($article->category), 'kilimanjaro') ? 'photo-1464822759023-fed622ff2c3b' : (str_contains(strtolower($article->category), 'island') ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
                <article class="article-card"><a href="{{ route('journal.show', $article->slug) }}"><div class="article-image" style="background-image:url('{{ $article->image_path ? asset($article->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=950&q=85' }}')"><span>FIELD NOTES&nbsp; / &nbsp;TANZANIA</span></div><div class="article-meta"><span>◷ &nbsp;1 MIN READ</span><span>• &nbsp;{{ strtoupper($article->category) }}</span></div><h3>{{ $article->title }}</h3><p>{{ $article->excerpt }}</p><div class="article-read"><span>READ THE STORY</span><b>↗</b></div></a></article>
            @endforeach</div>
            @include('public.pagination', ['paginator' => $articles])
        @endif
    </section>
@endsection
