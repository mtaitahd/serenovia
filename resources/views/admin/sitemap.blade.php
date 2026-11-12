@extends('admin.layout')
@section('title', 'Sitemap')
@section('content')
<div class="page-heading"><div><span class="eyebrow">SITE / SEARCH VISIBILITY</span><h1>Sitemap</h1><p>Build a fresh XML sitemap from published safari packages, destinations and journal articles.</p></div></div>
<div class="admin-columns">
    <section class="panel"><div class="panel-heading"><h2>Sitemap status</h2></div><p><strong>{{ $urlCount }}</strong> public addresses are ready to include.</p><p>Public sitemap: <a class="text-link" href="{{ $sitemapUrl }}" target="_blank" rel="noopener">{{ $sitemapUrl }} ↗</a></p><p>{{ $generatedAt ? 'Last generated '.$generatedAt : 'A sitemap has not been generated yet.' }}</p><form method="POST" action="{{ route('admin.sitemap.generate', request()->boolean('popup') ? ['popup' => 1] : []) }}">@csrf<button class="button" type="submit">Generate sitemap <span>↗</span></button></form></section>
    <section class="panel"><div class="panel-heading"><h2>Included pages</h2></div><p>Homepage, all published destinations, all published safari packages and all published journal articles.</p><small>The sitemap is saved as <code>public/sitemap.xml</code>.</small></section>
</div>
@endsection
