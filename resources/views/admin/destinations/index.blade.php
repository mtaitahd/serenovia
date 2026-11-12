@extends('admin.layout')
@section('title', 'Destinations')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">PLACES TO EXPLORE</span><h1>Destinations</h1><p>Organize the places featured in your safari catalogue.</p></div><a class="button" data-admin-popup href="{{ route('admin.destinations.create') }}">＋ Add destination</a></div>
    <section class="panel">
        @if($destinations->isEmpty())<div class="empty-state"><strong>No destinations yet.</strong>Add the destinations where you currently offer journeys.</div>
        @else
        <table class="data-table"><thead><tr><th>DESTINATION</th><th>COUNTRY</th><th>PACKAGES</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
            @foreach($destinations as $destination)<tr><td><span class="table-title">{{ $destination->name }}</span><span class="table-sub">{{ $destination->tagline }}</span></td><td>{{ $destination->country }}</td><td>{{ $destination->tours_count }}</td><td><span class="status-pill {{ $destination->status }}">{{ $destination->status }}</span></td><td><div class="row-actions"><a class="text-button" data-admin-popup href="{{ route('admin.destinations.edit', $destination) }}">Edit</a><form method="POST" action="{{ route('admin.destinations.destroy', $destination) }}" onsubmit="return confirm('Delete this destination?')">@csrf @method('DELETE')<button class="text-button">Delete</button></form></div></td></tr>@endforeach
        </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $destinations])</div>
        @endif
    </section>
@endsection
