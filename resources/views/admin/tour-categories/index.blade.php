@extends('admin.layout')
@section('title', 'Tour categories')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">ORGANIZE YOUR JOURNEYS</span><h1>Tour categories</h1><p>Group safari packages by wildlife, culture, mountain routes, islands or any theme you choose.</p></div><a class="button" href="{{ route('admin.tour-categories.create') }}">＋ Add category</a></div>
    <section class="panel">
        @if($categories->isEmpty())<div class="empty-state"><strong>No tour categories yet.</strong>Add a category, then assign it to packages in the package editor.</div>
        @else
            <table class="data-table"><thead><tr><th>CATEGORY</th><th>PACKAGES</th><th>ORDER</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
                @foreach($categories as $category)<tr><td><span class="table-title">{{ $category->name }}</span><span class="table-sub">{{ $category->description ?: $category->slug }}</span></td><td>{{ $category->packages_count }}</td><td>{{ $category->sort_order }}</td><td><span class="status-pill {{ $category->is_active ? 'published' : 'draft' }}">{{ $category->is_active ? 'Active' : 'Hidden' }}</span></td><td><div class="row-actions"><a class="text-button" href="{{ route('admin.tour-categories.edit', $category) }}">Edit</a><form method="POST" action="{{ route('admin.tour-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Packages will keep their other details.')">@csrf @method('DELETE')<button class="text-button" type="submit">Delete</button></form></div></td></tr>@endforeach
            </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $categories])</div>
        @endif
    </section>
@endsection
