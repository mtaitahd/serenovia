@extends('admin.layout')
@php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
@section('title', $tour->exists ? 'Edit safari package' : 'New safari package')
@section('content')
    @php($currentGalleryImage = $galleryImages->firstWhere('path', $tour->image_path))
    @php($selectedCategoryIds = old('category_ids', $tour->categories->pluck('id')->all()))
    <div class="page-heading"><div><span class="eyebrow">EXPERIENCE CATALOGUE</span><h1>{{ $tour->exists ? 'Edit package' : 'Create a safari package' }}</h1><p>Build the experience in clear steps, save it as a draft, then publish when it is ready.</p></div><a class="button secondary" href="{{ route('admin.tours.index') }}">← All packages</a></div>
    <form class="admin-form package-builder" data-recovery-user="{{ auth()->id() }}" data-recovery-version="{{ $tour->updated_at?->toISOString() ?? 'new' }}" data-max-upload-files="{{ ini_get('max_file_uploads') }}" data-max-post-size="{{ ini_get('post_max_size') }}" method="POST" enctype="multipart/form-data" action="{{ $tour->exists ? route('admin.tours.update', $tour) : route('admin.tours.store') }}">
        @csrf @if($tour->exists) @method('PUT') @endif
        <input type="hidden" id="gallery_image_id" name="gallery_image_id" value="{{ old('gallery_image_id', $currentGalleryImage?->id) }}">
        <div class="builder-progress"><p data-recovery-status role="status" style="font-size:12px">Preparing form recovery…</p>
            <div class="builder-progress-top"><div><span class="eyebrow">SAFARI BUILDER</span><strong id="builder-step-title">Step 1 of 4 · Package basics</strong></div><span id="builder-progress-label">25% complete</span></div>
            <div class="builder-progress-track"><span id="builder-progress-fill"></span></div>
            <nav class="builder-steps" aria-label="Package setup steps">
                <button type="button" class="is-current" data-step-jump="1"><i>01</i><span>Basics</span></button><button type="button" data-step-jump="2"><i>02</i><span>Pricing</span></button><button type="button" data-step-jump="3"><i>03</i><span>Itinerary</span></button><button type="button" data-step-jump="4"><i>04</i><span>Review</span></button>
            </nav>
        </div>
        <section class="builder-panel" data-builder-step="1">
            <div class="builder-panel-heading"><span class="eyebrow">01 / THE EXPERIENCE</span><h2>Start with the journey</h2><p>Give this safari a clear name, destination and a strong cover photo.</p></div>
            <div class="form-grid">
                <div class="field"><label for="title">PACKAGE NAME *</label><input id="title" name="title" value="{{ old('title', $tour->title) }}" required>@error('title')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="destination_id">DESTINATION</label><select id="destination_id" name="destination_id"><option value="">Choose a destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected(old('destination_id', $tour->destination_id) == $destination->id)>{{ $destination->name }} · {{ $destination->country }}</option>@endforeach</select>@error('destination_id')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="starting_point">STARTING POINT</label><select id="starting_point" name="starting_point"><option value="">Choose a starting point</option>@foreach($startingPoints as $point)<option value="{{ $point }}" @selected(old('starting_point', $tour->starting_point) === $point)>{{ $point }}</option>@endforeach</select>@error('starting_point')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="ending_point">ENDING POINT</label><select id="ending_point" name="ending_point"><option value="">Choose an ending point</option>@foreach($endingPoints as $point)<option value="{{ $point }}" @selected(old('ending_point', $tour->ending_point) === $point)>{{ $point }}</option>@endforeach</select>@error('ending_point')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="tour_type">JOURNEY TYPE</label><select id="tour_type" name="tour_type">@foreach(['safari','mountain','island','cultural','day trip'] as $type)<option value="{{ $type }}" @selected(old('tour_type', $tour->tour_type ?: 'safari') === $type)>{{ ucwords($type) }}</option>@endforeach</select></div>
                <div class="field"><label for="duration_days">DAYS *</label><input id="duration_days" name="duration_days" type="number" min="1" max="365" value="{{ old('duration_days', $tour->duration_days ?: 1) }}" required>@error('duration_days')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="duration_nights">NIGHTS *</label><input id="duration_nights" name="duration_nights" type="number" min="0" max="365" value="{{ old('duration_nights', $tour->duration_nights ?? 0) }}" required>@error('duration_nights')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field full"><label>HERO / COVER PHOTOGRAPH</label><div class="cover-picker-card">
                    <div class="cover-preview" id="cover-preview">@if($tour->image_path)<img src="{{ asset($tour->image_path) }}" alt="Current package cover">@else<span>Choose a photo that captures this journey</span>@endif</div>
                    <div class="cover-picker-actions"><button type="button" class="button" data-open-gallery-picker>Select from photo gallery</button><label class="button secondary file-choice" for="cover-upload">Upload a new photo</label><input class="visually-hidden" id="cover-upload" type="file" name="image" accept="image/jpeg,image/png,image/webp"><small>Choose a gallery photo or upload a hero image for this safari. It also fills itinerary cards when day photos are missing.</small></div>

            </div>@error('image')<small class="field-error">{{ $message }}</small>@enderror @error('gallery_image_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            </div>
<details class="field full package-optional"><summary>Subtitle &amp; categories <small>Optional</small></summary><div class="form-grid">                <div class="field full"><label for="package-subtitle">PACKAGE SUBTITLE</label><input id="package-subtitle" name="package_details[subtitle]" maxlength="255" value="{{ old('package_details.subtitle', $tour->package_details['subtitle'] ?? '') }}" placeholder="For example: The Whiskey Route — Kilimanjaro trekking">@error('package_details.subtitle')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field full"><label>PACKAGE CATEGORIES</label><div class="category-choice-grid">@forelse($categories as $category)<label class="category-choice"><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategoryIds))><span><b>{{ $category->name }}</b><small>{{ $category->description ?: 'Use this label to group similar journeys.' }}</small></span></label>@empty<a class="text-link" href="{{ route('admin.tour-categories.index') }}">Add your first category</a>@endforelse</div>@error('category_ids.*')<small class="field-error">{{ $message }}</small>@enderror</div></div></details>
            <div class="builder-actions"><span class="builder-save-note">You can return to earlier steps before saving.</span><button type="button" class="button builder-next" data-next-step="2">Continue to pricing <span>→</span></button></div>
        </section>
        <section class="builder-panel" data-builder-step="2" hidden>
            <div class="builder-panel-heading"><span class="eyebrow">02 / PRICING & DETAILS</span><h2>Set the trip details</h2><p>Share a starting price and the practical details guests need.</p></div>
            <div class="form-grid">
                <div class="field"><label for="tour_code">TOUR CODE</label><input id="tour_code" name="tour_code" maxlength="40" value="{{ old('tour_code', $tour->tour_code) }}" placeholder="e.g. SER-TZ-03">@error('tour_code')<small class="field-error">{{ $message }}</small>@enderror</div>
                @foreach(\App\Models\TourPackage::PRACTICAL_FIELDS as $key => $label)
                    @php($value = old('practical_details.'.$key, $tour->practical_details[$key] ?? null))
                    <div class="field">
                        <label for="practical-{{ $key }}">{{ strtoupper($label) }}</label>
                        @if($key === 'activity_level')
                            <select id="practical-{{ $key }}" name="practical_details[{{ $key }}]"><option value="">Not specified</option>@foreach(['Easy', 'Moderate', 'Challenging', 'Strenuous'] as $level)<option @selected($value === $level)>{{ $level }}</option>@endforeach</select>
                        @elseif(in_array($key, ['min_group_size', 'minimum_age'], true))
                            <input id="practical-{{ $key }}" name="practical_details[{{ $key }}]" type="number" min="{{ $key === 'minimum_age' ? 0 : 1 }}" max="{{ $key === 'minimum_age' ? 120 : 500 }}" value="{{ $value }}">
                        @else
                            <input id="practical-{{ $key }}" name="practical_details[{{ $key }}]" maxlength="180" value="{{ $value }}">
                        @endif
                        @error('practical_details.'.$key)<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                @endforeach
                <div class="field"><label for="price_from">STARTING PRICE</label><input id="price_from" name="price_from" type="number" min="0" step="0.01" value="{{ old('price_from', $tour->price_from) }}" placeholder="Leave blank for a custom quote">@error('price_from')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="currency">CURRENCY</label><select id="currency" name="currency">@foreach(['USD','TZS','EUR','GBP'] as $currency)<option value="{{ $currency }}" @selected(old('currency', $tour->currency ?: 'USD') === $currency)>{{ $currency }}</option>@endforeach</select></div>
                <div class="field"><label for="max_group_size">MAXIMUM GROUP SIZE</label><input id="max_group_size" name="max_group_size" type="number" min="1" max="500" value="{{ old('max_group_size', $tour->max_group_size) }}" placeholder="No limit set"></div>
                <div class="field full"><label for="inclusions">WHAT IS INCLUDED</label><textarea id="inclusions" name="inclusions" rows="5" placeholder="List one inclusion per line">{{ old('inclusions', $tour->inclusions) }}</textarea></div>
                <div class="field full"><label for="exclusions">WHAT IS NOT INCLUDED</label><textarea id="exclusions" name="exclusions" rows="5" placeholder="List one exclusion per line">{{ old('exclusions', $tour->exclusions) }}</textarea></div>
            </div>
            <div class="builder-actions"><button type="button" class="button secondary builder-back" data-previous-step="1">← Back</button><button type="button" class="button builder-next" data-next-step="3">Continue to itinerary <span>→</span></button></div>
        </section>
        <section class="builder-panel" data-builder-step="3" hidden>
            <div class="builder-panel-heading"><span class="eyebrow">03 / STORY & ROUTE</span><h2>Bring the route to life</h2><p>Explain what makes the journey memorable and what guests can expect each day.</p></div>
            <div class="form-grid">
                <div class="field full"><label for="description">TOUR OVERVIEW</label><textarea id="description" name="description" rows="8" placeholder="Tell the story of this experience">{{ old('description', $tour->description) }}</textarea><small>Describe the overall route, its highlights and who it suits. Add daily activities in the day cards below; do not repeat the full itinerary here.</small></div>
                <div class="field full itinerary-field">
                    <div class="itinerary-builder-heading"><div><label>ADD YOUR TOUR DAYS</label><small>Set the trip length in Basics. Each day gets its own title and activity notes here.</small></div><button type="button" class="button secondary small" id="add-itinerary-day">＋ Add a day</button></div>
                    <div class="itinerary-map-choice"><label class="checkbox-row"><input type="hidden" name="route_map_enabled" value="0"><input type="checkbox" name="route_map_enabled" value="1" @checked(old('route_map_enabled', $tour->route_map_enabled))><span><b>Need a route map?</b><small>Show an animated journey through pinned itinerary stops. Journey type Mountain uses a walking person; Safari uses a moving car.</small></span></label></div>
                    <div class="itinerary-day-list" id="itinerary-day-list" data-initial-days="{{ json_encode(old('itinerary_days', $tour->itinerary_days ?? [])) }}" data-saved-photos="{{ json_encode(collect($tour->itinerary_days ?? [])->mapWithKeys(fn ($day) => [$day['id'] => $day['images'] ?? []])) }}" data-photo-base="{{ asset('/') }}" data-initial-itinerary="{{ old('itinerary', $tour->itinerary) }}"></div>
                    <template id="itinerary-day-template"><article class="itinerary-day-card"><header><span data-day-number>DAY 1</span><button type="button" class="text-button" data-remove-itinerary-day>Remove day</button></header><div class="field"><label data-day-title-label>DAY TITLE / LOCATION</label><input data-itinerary-title data-day-field="title" type="text" maxlength="180" placeholder="For example: Serengeti game drive"></div><div class="field"><label data-day-details-label>DAY DESCRIPTION</label><textarea data-itinerary-details data-day-field="details" rows="4" placeholder="Describe this day’s route and activities. Add altitude, distance, hiking time, habitat, meals and camp in the day details."></textarea></div>@include('admin.tours.day-media')</article></template>
                    <textarea class="visually-hidden" id="itinerary" name="itinerary" tabindex="-1" aria-hidden="true">{{ old('itinerary', $tour->itinerary) }}</textarea>
                    @if($errors->has('itinerary_days.*'))<small class="field-error">{{ collect($errors->get('itinerary_days.*'))->flatten()->first() }}</small>@endif
                    @error('itinerary')<small class="field-error">{{ $message }}</small>@enderror
                </div>
                <div class="field full"><div class="accommodation-plan-heading"><div><label>STAYS BY DAY & COMFORT LEVEL</label><small>Choose the property for each itinerary day. Its photos come from the stay’s Gallery selection in Manage stays. Leave blank when no stay applies.</small></div><a class="text-link" href="{{ route('admin.accommodations.index') }}" target="_blank" rel="noopener">Manage stays</a></div>
                    <div class="accommodation-plan-list" id="accommodation-plan-list">
                        @foreach($selectedPlan as $index => $plan)
                            <div class="accommodation-plan-row">
                                <label><span>DAY</span><select name="accommodation_plan[{{ $index }}][day_number]" data-plan-day data-day-options='@json($dayOptions)'>@foreach($dayOptions as $day)@if($day <= max(1,(int)old('duration_days',$tour->duration_days ?: 1)))<option value="{{ $day }}" @selected((int)($plan['day_number'] ?? 1)===$day)>Day {{ $day }}</option>@endif @endforeach</select></label>
                                <label><span>COMFORT</span><select name="accommodation_plan[{{ $index }}][tier]"><option value="budget" @selected(($plan['tier'] ?? '')==='budget')>Budget</option><option value="mid_range" @selected(($plan['tier'] ?? 'mid_range')==='mid_range')>Mid-range</option><option value="luxury" @selected(($plan['tier'] ?? '')==='luxury')>Luxury</option></select></label>
                                <label class="plan-stay-select"><span>ACCOMMODATION</span><select name="accommodation_plan[{{ $index }}][accommodation_id]"><option value="">No stay selected</option>@foreach($accommodations as $stay)<option value="{{ $stay->id }}" data-tier="{{ $stay->tier }}" @selected((int)($plan['accommodation_id'] ?? 0)===$stay->id)>{{ $stay->name }}{{ $stay->destination ? ' · '.$stay->destination->name : '' }}</option>@endforeach</select></label>
                                <button type="button" class="text-button plan-remove" data-remove-plan-row aria-label="Remove stay row">Remove</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="button secondary small" id="add-accommodation-plan">＋ Add a stay for another day or level</button>
                    <template id="accommodation-plan-template"><div class="accommodation-plan-row"><label><span>DAY</span><select data-plan-day data-day-options='@json($dayOptions)'></select></label><label><span>COMFORT</span><select data-plan-tier><option value="budget">Budget</option><option value="mid_range" selected>Mid-range</option><option value="luxury">Luxury</option></select></label><label class="plan-stay-select"><span>ACCOMMODATION</span><select data-plan-stay><option value="">No stay selected</option>@foreach($accommodations as $stay)<option value="{{ $stay->id }}" data-tier="{{ $stay->tier }}">{{ $stay->name }}{{ $stay->destination ? ' · '.$stay->destination->name : '' }}</option>@endforeach</select></label><button type="button" class="text-button plan-remove" data-remove-plan-row aria-label="Remove stay row">Remove</button></div></template>
                    @error('accommodation_plan.*.accommodation_id')<small class="field-error">{{ $message }}</small>@enderror
                </div>
                @include('admin.tours.package-content')
            </div>
            <div class="builder-actions"><button type="button" class="button secondary builder-back" data-previous-step="2">← Back</button><button type="button" class="button builder-next" data-next-step="4">Review package <span>→</span></button></div>
        </section>
        <section class="builder-panel" data-builder-step="4" hidden>
            @include('admin.tours.seo-preview')
            <div class="builder-panel-heading"><span class="eyebrow">04 / READY TO SHARE</span><h2>Review and publish</h2><p>Choose whether this package stays private as a draft or appears on the website.</p></div>
            <div class="package-review-card"><div id="review-cover" class="review-cover">@if($tour->image_path)<img src="{{ asset($tour->image_path) }}" alt="">@endif</div><div><span class="eyebrow">PACKAGE PREVIEW</span><h3 id="review-title">{{ $tour->title ?: 'Your safari package' }}</h3><p id="review-summary">{{ $tour->summary ?: 'Your short introduction will appear here.' }}</p><span id="review-duration">{{ $tour->duration_days ?: 1 }} days · {{ $tour->duration_nights ?? 0 }} nights</span></div></div>
<details class="package-optional" id="package-search-settings"><summary>Search appearance &amp; short introduction <small>Optional — automatic when blank</small></summary><p>The overview describes the whole tour. Day descriptions explain individual days. You do not need to repeat either here.</p><div class="form-grid">                <div class="field full"><label for="summary">SHORT INTRODUCTION (OPTIONAL)</label><textarea id="summary" name="summary" maxlength="500" rows="3" placeholder="Add only if you want a separate short introduction">{{ old('summary', $tour->summary) }}</textarea><small>Leave blank to use the overview for the automatic search description. Up to 500 characters.</small></div>
                <div class="field full"><label for="package-meta-description">CUSTOM SEARCH DESCRIPTION (OPTIONAL)</label><textarea id="package-meta-description" name="package_details[meta_description]" maxlength="500" rows="3" placeholder="Leave blank for an automatic description">{{ old('package_details.meta_description', $tour->package_details['meta_description'] ?? '') }}</textarea><small>Uses the short introduction, then the overview when blank. Write a concise, tour-specific sentence if customising. Google may show a different snippet.</small>@error('package_details.meta_description')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="slug">PAGE ADDRESS</label><input id="slug" name="slug" value="{{ old('slug', $tour->slug) }}" placeholder="Leave blank to make from name">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div></div><small>The page address is created from the package name. Keep published addresses unchanged unless you also arrange a redirect.</small></details>
            <div class="form-grid review-options">
                <div class="field"><label for="status">VISIBILITY</label><select id="status" name="status"><option value="draft" @selected(old('status', $tour->status ?: 'draft') === 'draft')>Save as draft</option><option value="published" @selected(old('status', $tour->status) === 'published')>Publish on the website</option></select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label class="checkbox-row"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $tour->is_featured))> Feature this package on the home page</label><small>Featured packages appear in the homepage safari section.</small></div>
            </div>
            <div class="builder-actions"><button type="button" class="button secondary builder-back" data-previous-step="3">← Back</button><button class="button" type="submit">{{ $tour->exists ? 'Save package' : 'Create package' }} </button></div>
        </section>
    </form>
    <dialog class="admin-dialog gallery-picker-dialog" id="gallery-picker-dialog" aria-labelledby="gallery-picker-title">
        <div class="dialog-heading"><div><span class="eyebrow">YOUR PHOTO LIBRARY</span><h2 id="gallery-picker-title">Choose a cover image</h2><p>Search and select any photo saved in your gallery.</p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Close">×</button></div>
        <div class="gallery-picker-tools"><input id="gallery-picker-search" type="search" placeholder="Search image titles"><select id="gallery-picker-category"><option value="">All collections</option>@foreach($galleryImages->pluck('category')->unique()->sort() as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select><a class="button secondary" href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Manage gallery</a></div>
        @if($galleryImages->isEmpty())<div class="picker-empty"><strong>No gallery photos yet.</strong>Add photos in the Gallery manager, then return here to choose one.</div>@else
            <div class="gallery-picker-grid" id="gallery-picker-grid">@foreach($galleryImages as $galleryImage)<button type="button" class="gallery-picker-item" data-image-id="{{ $galleryImage->id }}" data-image-url="{{ asset($galleryImage->path) }}" data-image-name="{{ $galleryImage->title ?: 'Safari photograph' }}" data-image-category="{{ $galleryImage->category }}"><img src="{{ asset($galleryImage->path) }}" alt="{{ $galleryImage->alt_text }}" loading="lazy"><span>{{ $galleryImage->title ?: 'Untitled photograph' }}</span><small>{{ $galleryImage->category }}</small></button>@endforeach</div><div class="picker-empty" id="gallery-picker-empty" hidden>No photos match this search.</div>
        @endif
        <div class="dialog-footer"><span id="gallery-picker-selection">No cover selected</span><div><button type="button" class="button secondary" data-close-dialog>Cancel</button><button type="button" class="button" id="gallery-picker-confirm" disabled>Use this photo</button></div></div>
    </dialog>
@include('admin.tours.day-gallery-dialog')
@endsection
@push('styles')
<link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-package-simplified.css?v=1">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="{{ $publicBasePath }}/css/itinerary-map.css?v=1">
@endpush
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-package-notepad.css?v=1">
@endpush
@push('scripts')
<dialog class="route-location-dialog" id="route-location-dialog" aria-label="Choose itinerary location"><div class="route-location-heading"><div><h2>Pin this day’s location</h2><p>Move the map and click the destination. You can also enter coordinates in the day form.</p></div><button type="button" class="button secondary small" data-close-route-map>Close</button></div><div id="admin-day-location-map"></div><p data-route-map-status aria-live="polite">Click a place to select it.</p><button type="button" class="button" data-save-route-pin disabled>Use this location</button></dialog>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
<script src="{{ $publicBasePath }}/js/admin-itinerary-media.js?v=6" defer></script>
<script src="{{ $publicBasePath }}/js/admin-package-builder.js?v=6" defer></script>
<script src="{{ $publicBasePath }}/js/admin-package-content.js?v=1" defer></script>
<script src="{{ $publicBasePath }}/js/admin-day-gallery.js?v=3" defer></script>
<script src="{{ $publicBasePath }}/js/admin-package-recovery.js?v=2" defer></script>
<script src="{{ $publicBasePath }}/js/admin-package-notepad.js?v=1" defer></script>
@endpush

