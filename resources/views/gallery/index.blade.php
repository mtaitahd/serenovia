@extends('layouts.site')
@section('title', 'Photo gallery · '.$siteSettings['brand_name'])
@section('content')
    <section class="listing-hero page-photo-hero">@include('public.hero-image', ['heroKey' => 'gallery'])<span class="eyebrow">A GLIMPSE OF THE JOURNEY</span><h1>Scenes from <em>Tanzania.</em></h1><p>Landscapes, wildlife and the little moments that make a journey yours.</p></section>
    <section class="listing-wrap section-wrap">
        @if($images->isEmpty())<div class="public-empty"><span>▧</span><h2>The Serenovia gallery is growing.</h2><p>More scenes from Tanzania will appear here as our photo collection is curated.</p></div>
        @else<div class="public-gallery">@foreach($images as $image)<figure><img src="{{ asset($image->path) }}" alt="{{ $image->alt_text ?: $image->title }}"><figcaption>{{ $image->title ?: $image->category }}</figcaption></figure>@endforeach</div>@include('public.pagination', ['paginator' => $images])@endif
    </section>
@endsection
