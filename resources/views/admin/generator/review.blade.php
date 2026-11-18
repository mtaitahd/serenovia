@extends('admin.layout')
@section('content')
<div class="page-heading"><div><h1>Review uploaded package</h1><p>{{ $batch->filename }} · {{ ucfirst($batch->status) }}</p></div><a class="button secondary" href="{{ route('admin.generator.imports') }}">Back to Upload &amp; Preview</a></div>
@include('admin.generator.messages')
@if($batch->error)<p role="alert">{{ $batch->error }}</p>@endif
@foreach($records as $record)
    @php
        $row = json_decode($record->mapped_payload, true);
        $issues = json_decode($record->validation_messages ?? '[]', true);
        $lookup = ['destination_id' => $destinations, 'category_id' => $categories, 'accommodation_id' => $accommodations];
    @endphp
    <details class="panel generator-record" id="record-{{ $record->id }}" @if($records->count() === 1 || $loop->first) open @endif>
        <summary><strong>{{ $record->source_locator }} · {{ $row['day_title'] ?? 'Itinerary day' }}</strong> · {{ ucfirst($record->status) }}</summary>
        @if($issues)<ul>@foreach($issues as $issue)<li>{{ $issue }}</li>@endforeach</ul>@endif
        @if($batch->status === 'review')
            <p><button class="button secondary" type="submit" form="mapping-{{ $record->id }}" name="preview" value="1">Preview full itinerary</button></p>
        @elseif($record->status !== 'skipped')
            <p><a class="button secondary" href="{{ route('admin.generator.import-preview', [$batch->id, $record->id]) }}">Preview full itinerary</a></p>
        @endif
        <form id="mapping-{{ $record->id }}" class="form-grid" method="POST" action="{{ route('admin.generator.correct', [$batch->id, $record->id]) }}">
            @csrf
            @method('PATCH')
            <fieldset class="generator-fields" @disabled($batch->status !== 'review')>
                <input type="hidden" name="photo_picker" value="1">
                @foreach(['gallery_ids' => ['Day photographs', 'photo_ids'], 'transport_gallery_ids' => ['Transport photographs', 'transport_ids']] as $key => [$label, $name])
                    @php
                        $options = $photoOptions;
                        $selectedPhotos = array_map('trim', explode('|', $row[$key] ?? ''));
                    @endphp
                    <fieldset class="generator-photo-picker" data-photo-picker>
                        <legend>{{ $label }}</legend>
                        @if($options->isNotEmpty())
                            <button type="button" class="button secondary" data-open-gallery>Select from Gallery</button>
                            <small data-photo-count aria-live="polite"></small>
                            <dialog class="generator-gallery-dialog" aria-label="{{ $label }}">
                                <header><strong>{{ $label }}</strong><button type="button" class="button secondary" data-close-gallery>Done</button></header>
                                <div class="generator-gallery-filters">
                                    <input type="search" data-gallery-search aria-label="Search photographs" placeholder="Search photographs">
                                    <select data-gallery-category aria-label="Photo category">
                                        <option value="">All categories</option>
                                        @foreach($options->pluck('category')->filter()->unique()->sort() as $category)
                                            <option value="{{ $category }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small data-photo-count aria-live="polite"></small>
                            <div class="generator-photo-grid">
                                @foreach($options as $photo)
                                    <label class="generator-photo-option" data-category="{{ $photo->category }}" data-name="{{ $photo->title }} {{ $photo->alt_text }}">
                                        <img src="{{ asset($photo->path) }}" alt="" loading="lazy">
                                        <b class="generator-photo-tick" aria-hidden="true">✓</b>
                                        <span><input type="checkbox" name="{{ $name }}[]" value="{{ $photo->id }}" @checked(in_array((string) $photo->id, $selectedPhotos, true))>
                                        {{ $photo->title ?: 'Photo '.$photo->id }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p data-gallery-empty hidden>No photos match your filters.</p>
                            </dialog>
                        @else
                            <div class="generator-photo-empty">
                                <p>No gallery photos yet.</p>
                                <a href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Add photos in Gallery ↗</a>
                            </div>
                        @endif
                    </fieldset>
                @endforeach
                <p><a href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Upload photos to Gallery ↗</a><br><small>Save your mapping before refreshing to see new uploads.</small></p>
                @foreach(array_diff($fields, ['gallery_ids', 'transport_gallery_ids']) as $field)
                    <label>{{ str($field)->headline() }}
                        @if(isset($lookup[$field]))
                            <select name="fields[{{ $field }}]">
                                <option value="">Choose a match</option>
                                @if(filled($row[$field] ?? null) && !$lookup[$field]->contains('id', $row[$field]))
                                    <option selected value="{{ $row[$field] }}">Unmatched reference {{ $row[$field] }}</option>
                                @endif
                                @foreach($lookup[$field] as $option)
                                    <option value="{{ $option->id }}" @selected((string)($row[$field] ?? '') === (string)$option->id)>{{ $option->name }}</option>
                                @endforeach
                            </select>
                        @elseif(in_array($field, ['description', 'overview', 'highlights', 'inclusions', 'exclusions', 'notes']))
                            <textarea name="fields[{{ $field }}]" rows="2">{{ $row[$field] ?? '' }}</textarea>
                        @else
                            <input name="fields[{{ $field }}]" value="{{ $row[$field] ?? '' }}">
                        @endif
                    </label>
                @endforeach
                <label><input type="checkbox" name="skip" value="1" @checked($record->status === 'skipped')> Skip this row</label>
                @if($batch->status === 'review')<button class="button secondary" type="submit">Save mapping</button>@endif
            </fieldset>
        </form>
    </details>
@endforeach
{{ $records->links() }}
@if($batch->status === 'review')
    <form method="POST" action="{{ route('admin.generator.approve', $batch->id) }}">
        @csrf
        <button class="button" type="submit">Approve verified rows</button>
    </form>
@elseif($batch->status === 'approved')
    <a class="button" href="{{ route('admin.generator.index', ['import_batch' => $batch->id]).'#generate-packages' }}">Generate from this import</a>
@endif
<script>
    const revealRecord = () => {
        const record = document.getElementById(location.hash.slice(1));
        if (record instanceof HTMLDetailsElement) {
            record.open = true;
            record.scrollIntoView({block: 'start'});
        }
    };
    revealRecord();
    window.addEventListener('hashchange', revealRecord);
    document.querySelectorAll('[data-photo-picker]').forEach(picker => {
        const dialog = picker.querySelector('dialog');
        if (!dialog) return;
        const search = dialog.querySelector('[data-gallery-search]');
        const category = dialog.querySelector('[data-gallery-category]');
        const cards = [...dialog.querySelectorAll('[data-category]')];
        picker.querySelector('[data-open-gallery]').addEventListener('click', () => dialog.showModal());
        dialog.querySelector('[data-close-gallery]').addEventListener('click', () => dialog.close());
        const filter = () => {
            const query = search.value.trim().toLowerCase();
            cards.forEach(card => {
                card.hidden = (!!category.value && card.dataset.category !== category.value)
                    || !card.dataset.name.toLowerCase().includes(query);
            });
            dialog.querySelector('[data-gallery-empty]').hidden = cards.some(card => !card.hidden);
        };
        search.addEventListener('input', filter);
        search.addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
        category.addEventListener('change', filter);
        const options = [...picker.querySelectorAll('input[type="checkbox"]')];
        const update = () => {
            const selected = options.filter(option => option.checked).length;
            picker.querySelectorAll('[data-photo-count]').forEach(count => { count.textContent = selected + ' of 5 selected'; });
            options.forEach(option => { option.disabled = selected >= 5 && !option.checked; });
        };
        picker.addEventListener('change', update);
        update();
    });
</script>
@endsection
