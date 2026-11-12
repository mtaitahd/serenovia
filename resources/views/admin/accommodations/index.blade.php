@extends('admin.layout')
@section('title', 'Accommodations')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">PLACES TO STAY</span><h1>Accommodations</h1><p>Manage lodges, camps and stays, then assign them to the right day and comfort level in each package.</p></div><a class="button" href="{{ route('admin.accommodations.create') }}">＋ Add accommodation</a></div>
    <form class="filter-bar" method="GET"><input class="filter-select" type="search" name="q" value="{{ request('q') }}" placeholder="Search accommodations"><select class="filter-select" name="tier"><option value="">All comfort levels</option>@foreach(['budget'=>'Budget','mid_range'=>'Mid-range','luxury'=>'Luxury'] as $key=>$label)<option value="{{ $key }}" @selected(request('tier')===$key)>{{ $label }}</option>@endforeach</select><select class="filter-select" name="status"><option value="">All statuses</option><option value="published" @selected(request('status')==='published')>Published</option><option value="draft" @selected(request('status')==='draft')>Draft</option></select><button class="button secondary small" type="submit">Filter</button><a class="text-button" href="{{ route('admin.accommodations.index') }}">Clear</a></form>
    <section class="panel">
        @if($accommodations->isEmpty())<div class="empty-state"><strong>No accommodations yet.</strong>Add stays to make package planning faster and more consistent.</div>
        @else
            <table class="data-table"><thead><tr><th>ACCOMMODATION</th><th>DESTINATION</th><th>COMFORT</th><th>FROM</th><th>PACKAGES</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
                @foreach($accommodations as $accommodation)<tr><td><span class="table-title">{{ $accommodation->name }}</span><span class="table-sub">{{ $accommodation->location }}</span></td><td>{{ $accommodation->destination?->name ?? '—' }}</td><td>{{ ucwords(str_replace('_', ' ', $accommodation->tier)) }}</td><td>{{ $accommodation->price_from ? $accommodation->currency.' '.number_format((float)$accommodation->price_from) : 'On request' }}</td><td>{{ $accommodation->packages_count }}</td><td><span class="status-pill {{ $accommodation->status }}">{{ $accommodation->status }}</span></td><td><div class="row-actions"><a class="text-button" href="{{ route('admin.accommodations.edit', $accommodation) }}">Edit</a><form method="POST" action="{{ route('admin.accommodations.destroy', $accommodation) }}" onsubmit="return confirm('Delete this accommodation?')">@csrf @method('DELETE')<button class="text-button" type="submit">Delete</button></form></div></td></tr>@endforeach
            </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $accommodations])</div>
        @endif
    </section>
@endsection
