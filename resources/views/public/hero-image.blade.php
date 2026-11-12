@php
    $pageHeroImage = $siteSettings[$heroKey.'_hero_image'] ?? '';
    if (!$pageHeroImage) $pageHeroImage = \App\Models\GalleryImage::orderBy('sort_order')->value('path');
    if (!$pageHeroImage) $pageHeroImage = $siteSettings['hero_image'] ?? '';
@endphp
@if($pageHeroImage)<img class="page-hero-image" src="{{ asset($pageHeroImage) }}" alt="" fetchpriority="high" aria-hidden="true">@endif
<div class="page-hero-overlay" aria-hidden="true"></div>
