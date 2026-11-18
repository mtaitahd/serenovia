@extends('admin.layout')
@section('title', 'Itinerary preview')
@section('content')
    <div class="page-heading">
        <div><span class="eyebrow">Itinerary preview</span><h1>{{ $title }}</h1><p>Review the full itinerary as it will appear on the website.</p></div>
        <div class="generator-actions">
            <a class="button secondary" href="{{ $back }}">Back to review</a>
            @if($edit)<a class="button" href="{{ $edit }}">Edit itinerary</a>@endif
        </div>
    </div>
    <iframe class="generator-preview" title="Public tour preview" sandbox="allow-scripts allow-same-origin"
        src="{{ $url }}?{{ http_build_query(['frame' => 1, 'section' => request('section', 'itinerary')]) }}#tour-sections"></iframe>
@endsection
