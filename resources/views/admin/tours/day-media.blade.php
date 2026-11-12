<input type="hidden" data-day-field="id">
<details class="day-media-settings"><summary>Photographs &amp; route stop <span aria-hidden="true">＋</span></summary><div class="form-grid itinerary-media-fields">
    <div class="field full"><label>MAP STOP / PLACE NAME</label><input type="text" data-day-field="location" maxlength="180" placeholder="For example: Serengeti National Park"><small>This label appears on the animated route.</small></div>
    <div class="field"><label>LATITUDE (OPTIONAL)</label><input type="number" step="any" min="-90" max="90" data-day-field="latitude" placeholder="Pin a location on the map"></div>
    <div class="field"><label>LONGITUDE (OPTIONAL)</label><input type="number" step="any" min="-180" max="180" data-day-field="longitude"></div>
    <div class="field full"><button type="button" class="button secondary small" data-pin-day>⌖ Pin this stop on the map</button><small>Pin every stop for a geographic map. Without pins, an illustrative route is drawn automatically.</small></div>
    <div class="field full"><label>THIS DAY’S PHOTOGRAPHS</label><div class="day-saved-photos" data-day-saved-photos></div><label>ADD FROM GALLERY</label><select multiple size="4" data-day-field="gallery_ids" data-day-array><option value="" disabled>Select photographs (Ctrl / Cmd to choose more than one)</option>@foreach($galleryImages as $dayGalleryImage)<option value="{{ $dayGalleryImage->id }}" data-photo-url="{{ asset($dayGalleryImage->path) }}">{{ $dayGalleryImage->title ?: $dayGalleryImage->alt_text ?: 'Photo '.$dayGalleryImage->id }}</option>@endforeach</select></div>
    <div class="field full"><label>OR UPLOAD PHOTOGRAPHS</label><input type="file" multiple accept="image/jpeg,image/png,image/webp" data-day-field="uploads" data-day-array><small>Up to 6 uploads per day, JPG / PNG / WebP, 8 MB each. New photos are added alongside saved photos.</small><div class="day-upload-previews" data-day-upload-previews></div></div>
</div>
</details>
