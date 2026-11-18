<div class="field full">
    <label for="itinerary-options">ALTERNATIVE ITINERARY PACKAGES</label>
    <select id="itinerary-options" name="package_details[itinerary_option_ids][]" multiple size="4">
        @foreach($itineraryOptions as $option)
            @if($option->id !== $tour->id)<option value="{{ $option->id }}" @selected(in_array($option->id, old('package_details.itinerary_option_ids', $tour->package_details['itinerary_option_ids'] ?? [])))>{{ $option->title }} · {{ $option->duration_days }} days · {{ ucfirst($option->status) }}</option>@endif
        @endforeach
    </select>
    <small>For a route with 6-day and 7-day options, save each as its own package, then select the other option here. Each keeps its own itinerary, photos, camps and price. Only published options appear to visitors. Ctrl / Cmd selects multiple options.</small>
    @if($errors->has('package_details.itinerary_option_ids*'))<small class="field-error">{{ $errors->first('package_details.itinerary_option_ids*') }}</small>@endif
</div>
<div class="field full">
    <div class="itinerary-builder-heading"><div><label>PACKAGE QUESTIONS &amp; ANSWERS</label><small>Add the questions specific to this route. If empty, the website uses the general FAQs.</small></div><button type="button" class="button secondary small" data-add-package-faq>＋ Add a question</button></div>
    <div class="itinerary-day-list" data-package-faq-list>
        @foreach(old('package_details.faqs', $tour->package_details['faqs'] ?? []) as $faq)
            <article class="itinerary-day-card" data-package-faq>
                <div class="field"><label>QUESTION</label><input data-faq-question maxlength="500" value="{{ $faq['question'] ?? '' }}"></div>
                <div class="field"><label>ANSWER</label><textarea data-faq-answer maxlength="5000" rows="3">{{ $faq['answer'] ?? '' }}</textarea></div>
                <button type="button" class="text-button" data-remove-package-faq>Remove question</button>
            </article>
        @endforeach
    </div>
    @if($errors->has('package_details.faqs*'))<small class="field-error">{{ $errors->first('package_details.faqs*') }}</small>@endif
    <template data-package-faq-template><article class="itinerary-day-card" data-package-faq><div class="field"><label>QUESTION</label><input data-faq-question maxlength="500"></div><div class="field"><label>ANSWER</label><textarea data-faq-answer maxlength="5000" rows="3"></textarea></div><button type="button" class="text-button" data-remove-package-faq>Remove question</button></article></template>
</div>
