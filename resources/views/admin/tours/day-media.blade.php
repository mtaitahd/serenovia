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
    <div class="field full"><label>THIS DAY’S PHOTOGRAPHS</label><div class="day-saved-photos" data-day-saved-photos></div><label>ADD FROM GALLERY</label><select multiple size="4" data-day-field="gallery_ids" data-day-array><option value="" disabled>Select photographs (Ctrl / Cmd to choose more than one)</option>@foreach($galleryImages as $dayGalleryImage)<option value="{{ $dayGalleryImage->id }}" data-photo-url="{{ asset($dayGalleryImage->path) }}">{{ $dayGalleryImage->title ?: $dayGalleryImage->alt_text ?: 'Photo '.$dayGalleryImage->id }}</option>@endforeach</select></div>
    <div class="field full"><label>OR UPLOAD PHOTOGRAPHS</label><input type="file" multiple accept="image/jpeg,image/png,image/webp" data-day-field="uploads" data-day-array><small>Up to five photos per day total (saved, selected and uploaded). JPG / PNG / WebP, 8 MB per upload.</small><div class="day-upload-previews" data-day-upload-previews></div></div>
    <div class="field full"><label>TRANSPORT PHOTOGRAPHS</label>
        <select multiple size="5" data-day-field="transport_gallery_ids" data-day-array>
            @foreach($galleryImages as $photo)
                <option value="{{ $photo->id }}">{{ $photo->title ?: 'Photo '.$photo->id }}</option>
            @endforeach
        </select>
        <small>Choose up to five transport photos from the gallery.</small>
        <a href="{{ route('admin.gallery.index') }}" target="_blank" rel="noopener">Manage transport photos ↗</a>
        @if($errors->has('itinerary_days.*.transport_gallery_ids*'))<small class="field-error">{{ $errors->first('itinerary_days.*.transport_gallery_ids*') }}</small>@endif
    </div>
</div>
</details>
