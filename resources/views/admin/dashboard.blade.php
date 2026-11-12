@extends('admin.layout')
@section('title', 'Overview')
@section('content')
    @php
        $hour = now('Africa/Dar_es_Salaam')->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp
    <div class="page-heading">
        <div><span class="eyebrow">YOUR WORKSPACE</span><h1>{{ $greeting }}, {{ auth()->user()->name }}.</h1><p>Here is what is happening across your Serenovia website.</p></div>
        <a class="button" data-admin-popup href="{{ route('admin.tours.create') }}">＋ Add safari package</a>
    </div>
    <div class="stats-grid dashboard-stats">
        <div class="stat-card"><div class="stat-card-top"><span>SAFARI PACKAGES</span><i class="stat-icon icon-safari" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M10 4 7 11l5 3 4-7-6-3Zm-3 11-3 7 5 3 4-7-6-3Zm15-9-5 5 4 5 6-5-5-5Zm-4 12-5 5 4 5 6-5-5-5Z"/><path d="m12 14 5 3M8 24l4 2"/></svg></i></div><strong>{{ $stats['tours'] }}</strong><small>Packages in your catalogue</small></div>
        <div class="stat-card"><div class="stat-card-top"><span>DESTINATIONS</span><i class="stat-icon icon-place" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M25 13c0 7-9 16-9 16S7 20 7 13a9 9 0 1 1 18 0Z"/><circle cx="16" cy="13" r="3"/></svg></i></div><strong>{{ $stats['destinations'] }}</strong><small>Places in your collection</small></div>
        <div class="stat-card"><div class="stat-card-top"><span>JOURNAL STORIES</span><i class="stat-icon icon-journal" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M7 5h14l5 5v17H7z"/><path d="M21 5v6h5M11 16h11M11 20h11M11 24h7"/></svg></i></div><strong>{{ $stats['articles'] }}</strong><small>Stories and travel guides</small></div>
        <div class="stat-card"><div class="stat-card-top"><span>GALLERY IMAGES</span><i class="stat-icon icon-gallery" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="4" y="6" width="24" height="20"/><circle cx="12" cy="13" r="2.5"/><path d="m5 23 7-6 5 4 4-5 6 6"/></svg></i></div><strong>{{ $stats['gallery'] }}</strong><small>Photos in your gallery</small></div>
        <div class="stat-card"><div class="stat-card-top"><span>NEW ENQUIRIES</span><i class="stat-icon icon-inquiry" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="4" y="8" width="24" height="18"/><path d="m5 10 11 9 11-9M10 5h12"/></svg></i></div><strong>{{ $stats['new_inquiries'] }}</strong><small>Waiting for your reply</small></div>
    </div>
    <div class="dashboard-charts">
        <section class="panel chart-panel">
            <div class="panel-heading"><div><span class="eyebrow">ENQUIRIES</span><h2>Monthly enquiry trend</h2></div><span class="chart-period">LAST 12 MONTHS</span></div>
            <div class="chart-canvas-wrap"><canvas id="inquiry-trend-chart" aria-label="Monthly enquiries for the last 12 months" role="img"></canvas></div>
            <div class="chart-summary" id="inquiry-trend-summary">{{ $inquiryTrend->sum('total') }} enquiries received in the last 12 months</div>
        </section>
        <section class="panel chart-panel destination-chart-panel">
            <div class="panel-heading"><div><span class="eyebrow">TOUR CATALOGUE</span><h2>Packages by destination</h2></div></div>
            @if($toursByDestination->isNotEmpty())
                <div class="destination-chart-layout"><div class="donut-wrap"><canvas id="destination-chart" aria-label="Safari package distribution by destination" role="img"></canvas><div class="donut-center"><strong>{{ $stats['tours'] }}</strong><span>PACKAGES</span></div></div><ul class="chart-legend" id="destination-chart-legend"></ul></div>
            @else
                <div class="chart-empty"><span>✳</span><b>Your destination chart is ready.</b><small>Add a safari package to see the breakdown.</small></div>
            @endif
        </section>
    </div>
    <div class="admin-columns">
        <section class="panel">
            <div class="panel-heading"><h2>Recent trip enquiries</h2><a href="{{ route('admin.inquiries.index') }}">VIEW ALL ↗</a></div>
            @if ($inquiries->isEmpty())
                <div class="empty-state"><strong>Your inbox is ready.</strong>New trip enquiries from the website will appear here.</div>
            @else
                <table class="data-table"><thead><tr><th>TRAVELLER</th><th>INTERESTED IN</th><th>STATUS</th></tr></thead><tbody>
                    @foreach ($inquiries as $inquiry)
                        <tr><td><a class="table-title" href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }}</a><span class="table-sub">{{ $inquiry->created_at->format('M j, Y') }}</span></td><td>{{ $inquiry->tour?->title ?? 'Custom journey' }}</td><td><span class="status-pill {{ $inquiry->status }}">{{ $inquiry->status }}</span></td></tr>
                    @endforeach
                </tbody></table>
            @endif
        </section>
        <section class="panel">
            <div class="panel-heading"><h2>Recently updated tours</h2><a href="{{ route('admin.tours.index') }}">MANAGE ↗</a></div>
            @if ($latest_tours->isEmpty())
                <div class="empty-state"><strong>Start with a journey.</strong>Add your first safari package to build your catalogue.</div>
            @else
                <table class="data-table"><thead><tr><th>PACKAGE</th><th>STATUS</th></tr></thead><tbody>
                    @foreach ($latest_tours as $tour)
                        <tr><td><a class="table-title" href="{{ route('admin.tours.edit', $tour) }}">{{ $tour->title }}</a><span class="table-sub">{{ $tour->duration_days }} days · {{ $tour->destination?->name ?? 'Destination not set' }}</span></td><td><span class="status-pill {{ $tour->status }}">{{ $tour->status }}</span></td></tr>
                    @endforeach
                </tbody></table>
            @endif
        </section>
    </div>
    <section class="quick-links">
        <a href="{{ route('admin.articles.create') }}"><span>▤</span><b>Write a journal story</b><small>Share travel ideas and local perspective</small></a>
        <a href="{{ route('admin.gallery.index') }}"><span>▧</span><b>Build your gallery</b><small>Add and organize safari photography</small></a>
        <a href="{{ route('admin.settings.edit') }}"><span>⚙</span><b>Update website details</b><small>Keep your contact information current</small></a>
    </section>
    <script id="dashboard-chart-data" type="application/json">@json(['months' => $inquiryTrend, 'destinations' => $toursByDestination])</script>
    @push('scripts')<script src="{{ preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')) }}/js/admin-dashboard.js?v=1" defer></script>@endpush
@endsection
