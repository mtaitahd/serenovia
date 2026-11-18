@php
    $facts = ['Tour code' => $tour->tour_code];
    foreach (\App\Models\TourPackage::PRACTICAL_FIELDS as $key => $label) {
        if ($key === 'arrival') continue;
        $facts[$label] = $tour->practical_details[$key] ?? null;
    }
    $facts = array_filter($facts, fn ($value) => $value !== null && $value !== '');
@endphp
@if($facts)
    <section class="safari-section" aria-labelledby="practical-details-title">
        <span class="eyebrow">PLAN YOUR JOURNEY</span>
        <h2 id="practical-details-title">Tour features</h2>
        <dl class="prose">
            @foreach($facts as $label => $value)
                <dt><strong>{{ $label }}</strong></dt>
                <dd style="margin:0 0 12px">{{ $value }}</dd>
            @endforeach
        </dl>
    </section>
@endif
