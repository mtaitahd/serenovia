<input type="hidden" data-day-field="id">
<details class="day-media-settings"><summary>Photographs &amp; route stop <span aria-hidden="true">＋</span></summary><div class="form-grid itinerary-media-fields">
    <div class="field full"><label>MAP STOP / PLACE NAME</label><input type="text" data-day-field="location" maxlength="180" placeholder="For example: Serengeti National Park"><small>This label appears on the animated route.</small></div>
    @foreach(\App\Support\SafariDays::TRAIL_FIELDS as $key => $label)
        <div class="field">
            <label>{{ strtoupper($label) }}</label>
            <input type="text" data-day-field="{{ $key }}" maxlength="{{ in_array($key, ['altitude', 'habitat']) ? 240 : 120 }}" placeholder="{{ ['altitude' => '3,960 m → 4,035 m', 'habitat' => 'Rainforest, moorland or alpine desert', 'distance' => '6 km', 'walking_time' => '4–5 hours'][$key] }}">
            @if($errors->has('itinerary_days.*.'.$key))<small class="field-error">{{ $errors->first('itinerary_days.*.'.$key) }}</small>@endif
        </div>
    @endforeach
    <div class="field full"><label>OVERNIGHT CAMP / ACCOMMODATION NAME</label><input type="text" data-day-field="overnight_camp" maxlength="180" placeholder="For example: Machame Camp"><small>Use this for trekking camps. You can still select a property in Stays by day below.</small></div>
    <div class="field full"><label>MEALS &amp; DRINKS</label><textarea data-day-field="meals" rows="2" maxlength="500" placeholder="For example: Breakfast, lunch and drinking water"></textarea>@if($errors->has('itinerary_days.*.meals'))<small class="field-error">{{ $errors->first('itinerary_days.*.meals') }}</small>@endif</div>
    <div class="field"><label>LATITUDE (OPTIONAL)</label><input type="number" step="any" min="-90" max="90" data-day-field="latitude" placeholder="Pin a location on the map"></div>
    <div class="field"><label>LONGITUDE (OPTIONAL)</label><input type="number" step="any" min="-180" max="180" data-day-field="longitude"></div>
    <div class="field full"><button type="button" class="button secondary small" data-pin-day>⌖ Pin this stop on the map</button><small>Pin every stop for a geographic map. Without pins, an illustrative route is drawn automatically.</small></div>
    <div class="field full"><label>THIS DAY’S PHOTOGRAPHS</label>
        <div class="day-saved-photos" data-day-saved-photos></div>
        <button type="button" class="button secondary small" data-open-day-gallery="gallery_ids">Select from gallery</button>
        <small data-gallery-count="gallery_ids">0 of 5 photos selected</small>
        <select hidden multiple data-day-field="gallery_ids" data-day-array>@foreach($galleryImages as $photo)<option value="{{ $photo->id }}" data-photo-url="{{ asset($photo->path) }}" data-category="{{ $photo->category }}">{{ $photo->title ?: $photo->alt_text ?: 'Photo '.$photo->id }}</option>@endforeach</select>
        <input hidden type="file" multiple accept="image/jpeg,image/png,image/webp" data-day-field="uploads" data-day-array>
        <div class="day-upload-previews" data-day-upload-previews></div>
    </div>
    <div class="field full"><label>TRANSPORT / CAR PHOTOGRAPHS</label>
        <button type="button" class="button secondary small" data-open-day-gallery="transport_gallery_ids">Select from gallery</button>
        <small data-gallery-count="transport_gallery_ids">0 of 5 photos selected</small>
        <select hidden multiple data-day-field="transport_gallery_ids" data-day-array>@foreach($galleryImages as $photo)<option value="{{ $photo->id }}" data-photo-url="{{ asset($photo->path) }}" data-category="{{ $photo->category }}">{{ $photo->title ?: $photo->alt_text ?: 'Photo '.$photo->id }}</option>@endforeach</select>
        <div class="day-upload-previews" data-transport-previews></div>
        <a href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Add photos to gallery ↗</a>
        @if($errors->has('itinerary_days.*.transport_gallery_ids*'))<small class="field-error">{{ $errors->first('itinerary_days.*.transport_gallery_ids*') }}</small>@endif
    </div>
</div>
</details>
