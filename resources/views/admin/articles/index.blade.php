@extends('admin.layout')
@section('title', 'Journal & articles')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">STORIES FROM TANZANIA</span><h1>Journal & articles</h1><p>Draft, publish and update the travel stories on your website.</p></div><a class="button" data-admin-popup href="{{ route('admin.articles.create') }}">＋ Write an article</a></div>
    <section class="panel">
        @if($articles->isEmpty())<div class="empty-state"><strong>No articles yet.</strong>Write your first story to start the journal.</div>
        @else
        <table class="data-table"><thead><tr><th>ARTICLE</th><th>CATEGORY</th><th>PUBLISHED</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
            @foreach($articles as $article)<tr><td><span class="table-title">{{ $article->title }}</span><span class="table-sub">{{ $article->excerpt }}</span></td><td>{{ $article->category }}</td><td>{{ $article->published_at?->format('M j, Y') ?? '—' }}</td><td><span class="status-pill {{ $article->status }}">{{ $article->status }}</span></td><td><div class="row-actions"><a class="text-button" data-admin-popup href="{{ route('admin.articles.edit', $article) }}">Edit</a><form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">@csrf @method('DELETE')<button class="text-button">Delete</button></form></div></td></tr>@endforeach
        </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $articles])</div>
        @endif
    </section>
@endsection
