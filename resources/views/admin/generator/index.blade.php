@extends('admin.layout')
@section('content')
<div class="page-heading generator-heading">
    <div><span class="eyebrow">Safari packages</span><h1>{{ $importPage ? 'Upload & Preview' : 'Automatic Package Generator' }}</h1><p>{{ $importPage ? 'Upload your file, preview the package, add photos and approve it for generation.' : 'Select approved documents and generate tour drafts for preview and publishing.' }}</p></div>
</div>
@include('admin.generator.messages')
@unless($importPage)
<section class="panel" id="generate-packages">
    <h2>Generate packages</h2>
    <form method="POST" action="{{ route('admin.generator.generate') }}" class="form-grid">
        @csrf
        <label>Source <select name="source"><option value="hybrid">Database and approved documents</option><option value="database">Existing database</option><option value="import">Approved documents</option></select></label>
        <label>Approved documents <select name="import_batches[]" id="generator-document" multiple size="5">@foreach($approvedImports as $batch)<option value="{{ $batch->id }}" data-review="{{ route('admin.generator.review', $batch->id) }}" @selected(in_array((string) $batch->id, array_map('strval', (array) old('import_batches', request()->integer('import_batch') ?: [])), true))>{{ $batch->filename }}</option>@endforeach</select>
            <small>Choose one or more documents. Leave the list clear to use all approved documents.</small>
            <a id="generator-document-review" hidden>Review selected document and preview itinerary</a>
        </label>
        <label>Packages <input type="number" name="count" value="10" min="1" max="100" required></label>
        <label>Minimum days <input type="number" name="min_days" value="1" min="1" max="30" required></label>
        <label>Maximum days <input type="number" name="max_days" value="10" min="1" max="30" required></label>
        <label>Country code <input name="country" placeholder="TZ" maxlength="2" pattern="[A-Z]{2}"></label>
        <label>Destinations <select name="destinations[]" multiple size="4">@foreach($destinations as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
        @foreach(['category' => ['Tour category', $categories], 'start' => ['Starting location', $locations], 'end' => ['Ending location', $locations], 'activity' => ['Activity', $activities]] as $name => [$label, $options])
            <label>{{ $label }} <select name="{{ $name }}"><option value="">Any</option>@foreach($options as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
        @endforeach
        <label>Accommodation tier <select name="tier"><option value="">Any</option><option value="budget">Budget</option><option value="mid_range">Mid range</option><option value="luxury">Luxury</option></select></label>
        <label>Near duplicate threshold (%) <input type="number" name="similarity" value="90" min="70" max="100" required></label>
        <label><input type="checkbox" name="complete_packages" value="1" @checked(old('complete_packages', request()->filled('import_batch'))) > Use complete packages in their original day order</label>
        <div><button class="button" type="submit">Generate package drafts</button></div>
    </form>
</section>
@endunless
@if($importPage)
<section class="panel" id="generator-import">
    <h2>Import documents and itinerary blocks</h2>
    <p>Upload an Excel, Word, CSV or searchable PDF file, then check the extracted data and approve the verified rows.</p>
    <div class="generator-downloads">
        @foreach(['xlsx' => 'Excel', 'docx' => 'Word'] as $format => $label)
            <a class="button secondary" href="{{ route('admin.generator.template', $format) }}">
                <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v12m-5-5 5 5 5-5M5 16v4h14v-4"/>
                </svg>
                <strong>Download {{ $label }} template</strong>
            </a>
        @endforeach
    </div>
    <form method="POST" action="{{ route('admin.generator.upload') }}" enctype="multipart/form-data">
        @csrf
        <label>Document <input type="file" name="document" accept=".xlsx,.docx,.csv,.pdf" required></label>
        <button class="button" type="submit">Upload for review</button>
    </form>
    @if($tours->isNotEmpty())
        <form method="POST" action="{{ route('admin.generator.from-tour') }}">
            @csrf
            <label>Published tour <select name="source_tour" required>@foreach($tours as $tour)<option value="{{ $tour->id }}">{{ $tour->title }}</option>@endforeach</select></label>
            <button class="button secondary" type="submit">Review existing itinerary</button>
        </form>
    @endif
</section>
@endif
@unless($importPage)
<section class="panel">
    <h2>Generation history</h2>
    <table><thead><tr><th>Run</th><th>Admin</th><th>Requested</th><th>Created</th><th>Status</th></tr></thead><tbody>
        @forelse($runs as $run)
            <tr><td><a href="{{ route('admin.generator.run', $run->id) }}">{{ $run->created_at }}</a></td><td>{{ $run->requester }}</td><td>{{ $run->requested_count }}</td><td>{{ json_decode($run->metrics ?? '{}', true)['created'] ?? '—' }}</td><td>{{ ucfirst($run->status) }}</td></tr>
        @empty
            <tr><td colspan="5">No runs yet.</td></tr>
        @endforelse
    </tbody></table>
    {{ $runs->links() }}
</section>
@endunless
@if($importPage)
<section class="panel">
    <h2>Import history</h2>
    <table><thead><tr><th>Document</th><th>Uploaded</th><th>Admin</th><th>Status</th></tr></thead><tbody>
        @forelse($imports as $batch)
            <tr><td><a href="{{ route('admin.generator.review', $batch->id) }}">{{ $batch->filename }}</a></td><td>{{ $batch->created_at }}</td><td>{{ $batch->uploader }}</td><td>{{ ucfirst($batch->status) }}</td></tr>
        @empty
            <tr><td colspan="4">No imports yet.</td></tr>
        @endforelse
    </tbody></table>
    {{ $imports->links() }}
</section>
@endif
@unless($importPage)
<script>
    const generatorDocument = document.getElementById('generator-document');
    const generatorReview = document.getElementById('generator-document-review');
    const updateGeneratorReview = () => {
        const url = generatorDocument.selectedOptions[0]?.dataset.review;
        generatorReview.hidden = !url;
        if (url) generatorReview.href = url;
        else generatorReview.removeAttribute('href');
    };
    generatorDocument.addEventListener('change', updateGeneratorReview);
    updateGeneratorReview();
</script>
@endunless
@endsection
