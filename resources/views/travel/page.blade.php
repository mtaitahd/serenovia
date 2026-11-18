@extends('layouts.site')
@section('title', $title.' · '.$siteSettings['brand_name'])
@section('description', $intro)
@section('content')
    <section class="listing-hero page-photo-hero">
        @include('public.hero-image', ['heroKey' => 'destinations'])
        <span class="eyebrow">{{ $siteSettings['brand_name'] }}</span>
        <h1>{{ $title }}</h1>
        <p>{{ $intro }}</p>
    </section>
    <article class="section-wrap" style="padding-block:55px;max-width:1000px">
        @foreach($sections as $heading => $copy)
            <section class="safari-section"><h2>{{ $heading }}</h2><p>{{ $copy }}</p></section>
        @endforeach
        @foreach($faqs ?? [] as $faq)
            <details class="itinerary-day">
                <summary><span>{{ $faq->question }}</span><span class="day-toggle" aria-hidden="true">+</span></summary>
                <div class="itinerary-day-body prose">{!! \App\Support\RichText::display($faq->answer) !!}</div>
            </details>
        @endforeach
        <div class="hero-actions">
            <a class="button button-dark" href="{{ route('tours.index') }}">Find your trip <span>↗</span></a>
            <a class="text-link" href="{{ route('contact') }}">Contact our team <span>↗</span></a>
            @isset($faqs)<a class="text-link" href="{{ route('journal.index') }}">Travel stories <span>↗</span></a>@endisset
            @if(!request()->routeIs('responsible'))<a class="text-link" href="{{ route('responsible') }}">Responsible travel <span>↗</span></a>@endif
        </div>
    </article>
@endsection
