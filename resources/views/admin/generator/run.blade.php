@extends('admin.layout')
@section('content')
<div class="page-heading"><div><h1>Generation results</h1><p>{{ ucfirst($run->status) }} · {{ $run->created_at }}</p></div><a class="button secondary" href="{{ route('admin.generator.index') }}">Back to generator</a></div>
<section class="panel">
    <p>{{ $packages->count() }} of {{ $run->requested_count }} requested packages available.</p>
    @if($run->status === 'failed')
        <p role="alert">The run stopped early. Drafts already saved are available below. The error was logged.</p>
    @elseif($packages->count() < $run->requested_count)
        <p>{{ empty($metrics['blocks']) ? 'There are no approved blocks for this source.' : 'The eligible itinerary blocks did not yield enough valid, distinct packages.' }}</p>
    @endif
    @if($metrics)
        <p>{{ $metrics['checked'] }} checked · {{ $metrics['incompatible'] }} incompatible · {{ $metrics['duplicates'] }} duplicates · {{ $metrics['seconds'] }} seconds</p>
        @if($metrics['limit_reached'])<p>The search reached its work limit.</p>@endif
    @endif
    <table><thead><tr><th>Package</th><th>Duration</th><th>Destination</th><th>Categories</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @foreach($packages as $tour)
            <tr><td>{{ $tour->title }}</td><td>{{ $tour->duration_days }} days</td><td>{{ $tour->destination?->name }}</td><td>{{ $tour->categories->pluck('name')->join(', ') }}</td><td>{{ ucfirst($tour->status) }}</td><td><a class="button secondary" href="{{ route('admin.generator.preview', $tour) }}">Preview full package</a></td></tr>
        @endforeach
    </tbody></table>
</section>
@endsection
