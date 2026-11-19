@extends('layouts.site')
@section('title', $article->title.' · '.$siteSettings['brand_name'])
@section('description', $article->excerpt)
@section('content')
    @php($cover = $article->image_path ?: \App\Support\SafariPhotography::article($article->category.' '.$article->title))
    <article class="story-page">
        <header class="story-header page-photo-hero"><img class="page-hero-image" src="{{ asset($cover) }}" alt="" fetchpriority="high" aria-hidden="true"><div class="page-hero-overlay" aria-hidden="true"></div><a class="story-back" href="{{ route('journal.index') }}">← BACK TO THE JOURNAL</a><span class="eyebrow">{{ strtoupper($article->category) }} · TANZANIA · {{ $article->published_at?->format('M j, Y') }}</span><h1>{{ $article->title }}</h1><p>{{ $article->excerpt }}</p></header>
        <div class="story-body">{!! \App\Support\RichText::display($article->body) !!}</div>
        <div class="story-footer"><span>MORE THAN A HOLIDAY. A STORY TO CARRY HOME.</span><a class="text-link" href="{{ route('contact') }}">Plan your own journey </a></div>
    </article>
@endsection
