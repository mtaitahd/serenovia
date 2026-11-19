@php($paths = collect($stay->gallery_photo_ids ?? [])->map(fn ($id) => $stayPhotos->get($id))->prepend($stay->image_path)->filter()->unique()->take(5))
@if($paths->isNotEmpty())
    <div class="stay-photo-strip">
        @foreach($paths as $path)
            <a href="{{ asset($path) }}" data-photo data-photo-group="accommodation-stay-{{ $stay->id }}-{{ $dayNumber }}" data-caption="{{ $stay->name }}"><img src="{{ asset($path) }}" alt="{{ $stay->name }} photo {{ $loop->iteration }}" loading="lazy"></a>
        @endforeach
    </div>
@endif
