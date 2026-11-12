@extends('admin.layout')
@section('title', 'Countries')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">TOUR CATALOGUE</span><h1>Countries</h1><p>Manage the countries shown in the safari listing and set their display order.</p></div><a class="button" data-admin-popup href="{{ route('admin.countries.create') }}">＋ Add country</a></div>
    <section class="panel">
        @if($countries->isEmpty())<div class="empty-state"><strong>No countries yet.</strong>Add a country to make it available in destinations and safari filters.</div>
        @else
            <table class="data-table"><thead><tr><th>ORDER</th><th>COUNTRY</th><th>DESTINATIONS</th><th>LIVE TOURS</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
                @foreach($countries as $country)
                    <tr><td>{{ $country->sort_order }}</td><td><span class="table-title">{{ $country->name }}</span><span class="table-sub">{{ $country->slug }}</span></td><td>{{ $country->destinations_count }}</td><td>{{ $country->published_tours_count }}</td><td><span class="status-pill {{ $country->is_active ? 'published' : 'draft' }}">{{ $country->is_active ? 'Active' : 'Hidden' }}</span></td><td><div class="row-actions"><a class="text-button" data-admin-popup href="{{ route('admin.countries.edit', $country) }}">Edit</a><form method="POST" action="{{ route('admin.countries.destroy', $country) }}" onsubmit="return confirm('Remove this country? Countries assigned to destinations cannot be removed.')">@csrf @method('DELETE')<button class="text-button" type="submit">Delete</button></form></div></td></tr>
                @endforeach
            </tbody></table><div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $countries])</div>
        @endif
    </section>
@endsection
