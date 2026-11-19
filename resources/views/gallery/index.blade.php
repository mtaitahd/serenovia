@extends('layouts.site')
@section('title', 'Photo gallery · '.$siteSettings['brand_name'])
@section('description', 'Explore Tanzania in photographs, from the Serengeti plains to the coast.')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/photo-gallery.css') }}">
@endpush
@section('content')
    <header class="gallery-hero">
        <img src="{{ asset(\App\Support\SafariPhotography::path('crater')) }}" alt="" fetchpriority="high">
        <div class="gallery-hero-copy">
            <span class="eyebrow">PHOTO GALLERY</span>
            <h1>Scenes from <em>Tanzania.</em></h1>
            <p>From wildlife on the Serengeti plains to the shores of the Indian Ocean.</p>
            <a href="#gallery-collection">Explore the collection <span aria-hidden="true">↓</span></a>
        </div>
    </header>
    <section class="gallery-collection section-wrap" id="gallery-collection" aria-labelledby="gallery-collection-title">
        <div class="gallery-collection-heading">
            <h2 id="gallery-collection-title">Tanzania in photographs</h2>
            <span>{{ $images->count() }} photographs</span>
        </div>
        <div class="gallery-editorial-grid">
            @foreach($images as $image)
                <a class="gallery-editorial-photo" href="{{ asset($image['path']) }}" data-photo data-photo-group="tanzania-gallery" data-caption="{{ $image['caption'] }}">
                    <img src="{{ asset($image['path']) }}" {{ \App\Support\ResponsiveImage::attributes($image['path']) }} alt="{{ $image['alt'] ?? $image['caption'] }}" loading="lazy" decoding="async">
                    <span class="gallery-editorial-caption">{{ $image['caption'] }} <span aria-hidden="true">↗</span></span>
                </a>
            @endforeach
        </div>
    </section>
@endsection
