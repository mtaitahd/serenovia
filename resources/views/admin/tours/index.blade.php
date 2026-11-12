@extends('admin.layout')
@section('title', 'Safari packages')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">YOUR EXPERIENCE CATALOGUE</span><h1>Safari packages</h1><p>Create and update the journeys visitors can explore.</p></div><a class="button" data-admin-popup href="{{ route('admin.tours.create') }}">＋ Add package</a></div>
    <form class="filter-bar" method="GET"><input class="filter-select" type="search" name="q" value="{{ request('q') }}" placeholder="Search packages"><select class="filter-select" name="destination"><option value="">All destinations</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected((string)request('destination')===(string)$destination->id)>{{ $destination->name }}</option>@endforeach</select><select class="filter-select" name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select><select class="filter-select" name="status"><option value="">All statuses</option>@foreach(['draft','published'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="button secondary small" type="submit">Filter</button><a class="text-button" href="{{ route('admin.tours.index') }}">Clear</a></form>
    <section class="panel">
        @if ($tours->isEmpty())
            <div class="empty-state"><strong>No safari packages yet.</strong>Add a package when you are ready to publish your first journey.</div>
        @else
            <table class="data-table"><thead><tr><th>PACKAGE</th><th>DESTINATION</th><th>DURATION</th><th>FROM</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
                @foreach ($tours as $tour)
                    <tr>
                        <td><span class="table-title">{{ $tour->title }}</span><span class="table-sub">{{ ucfirst($tour->tour_type) }}{{ $tour->is_featured ? ' · Featured' : '' }}</span>@if($tour->categories->isNotEmpty())<div class="admin-category-tags">@foreach($tour->categories->take(3) as $category)<span>{{ $category->name }}</span>@endforeach</div>@endif</td>
                        <td>{{ $tour->destination?->name ?? '—' }}</td><td>{{ $tour->duration_days }} days / {{ $tour->duration_nights }} nights</td>
                        <td>{{ $tour->price_from ? $tour->currency.' '.number_format((float)$tour->price_from) : 'On request' }}</td>
                        <td><span class="status-pill {{ $tour->status }}">{{ $tour->status }}</span></td>
                        <td><div class="row-actions"><a class="text-button" data-admin-popup href="{{ route('admin.tours.edit', $tour) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.tours.destroy', $tour) }}" onsubmit="return confirm('Delete this package?')">@csrf @method('DELETE')<button class="text-button" type="submit">Delete</button></form>
                            @if ($tour->status === 'published')<a class="text-button" href="{{ route('tours.show', $tour->slug) }}" target="_blank" rel="noopener">View</a>@endif</div></td>
                    </tr>
                @endforeach
            </tbody></table>
            <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $tours])</div>
        @endif
    </section>
@endsection
