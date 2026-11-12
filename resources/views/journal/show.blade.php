@extends('layouts.site')
@section('title', $article->title.' · '.$siteSettings['brand_name'])
@section('description', $article->excerpt)
@section('content')
    @php($fallbackPhoto = str_contains(strtolower($article->category), 'kilimanjaro') ? 'photo-1464822759023-fed622ff2c3b' : (str_contains(strtolower($article->category), 'island') ? 'photo-1518509562904-e7ef99cdcc86' : 'photo-1516426122078-c23e76319801'))
    <article class="story-page">
        <header class="story-header page-photo-hero"><img class="page-hero-image" src="{{ $article->image_path ? asset($article->image_path) : 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=1800&q=88' }}" alt="" fetchpriority="high" aria-hidden="true"><div class="page-hero-overlay" aria-hidden="true"></div><a class="story-back" href="{{ route('journal.index') }}">← BACK TO THE JOURNAL</a><span class="eyebrow">{{ strtoupper($article->category) }} · TANZANIA · {{ $article->published_at?->format('M j, Y') }}</span><h1>{{ $article->title }}</h1><p>{{ $article->excerpt }}</p></header>
        <div class="story-body">{!! \App\Support\RichText::display($article->body) !!}</div>
        <div class="story-footer"><span>MORE THAN A HOLIDAY. A STORY TO CARRY HOME.</span><a class="text-link" href="{{ route('contact') }}">Plan your own journey <span>↗</span></a></div>
    </article>
@endsection
