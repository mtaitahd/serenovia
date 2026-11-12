@extends('admin.layout')
@section('title', 'Trip enquiries')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">TRAVELLER MESSAGES</span><h1>Trip enquiries</h1><p>Keep track of new requests and follow-up conversations.</p></div></div>
    <form class="filter-bar" method="GET"><select class="filter-select" name="status"><option value="">All statuses</option>@foreach(['new','contacted','quoted','confirmed','closed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="button secondary small">Filter</button><a class="text-button" href="{{ route('admin.inquiries.index') }}">Clear</a></form>
    <section class="panel">
        @if($inquiries->isEmpty())<div class="empty-state"><strong>No enquiries yet.</strong>Website enquiries will appear here when a traveller sends a request.</div>
        @else
        <table class="data-table"><thead><tr><th>TRAVELLER</th><th>JOURNEY</th><th>TRAVEL DATE</th><th>RECEIVED</th><th>STATUS</th><th>OPEN</th></tr></thead><tbody>
            @foreach($inquiries as $inquiry)<tr><td><span class="table-title">{{ $inquiry->name }}</span><span class="table-sub">{{ $inquiry->email }}</span></td><td>{{ $inquiry->tour?->title ?? 'Custom journey' }}</td><td>{{ $inquiry->travel_date?->format('M j, Y') ?? 'Flexible' }}</td><td>{{ $inquiry->created_at->format('M j, Y') }}</td><td><span class="status-pill {{ $inquiry->status }}">{{ $inquiry->status }}</span></td><td><a class="text-button" href="{{ route('admin.inquiries.show', $inquiry) }}">View →</a></td></tr>@endforeach
        </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $inquiries])</div>
        @endif
    </section>
@endsection
