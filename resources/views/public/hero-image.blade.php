@php
    $pageHeroImage = \App\Support\SafariPhotography::path(match ($heroKey) {
        'safaris' => 'migration',
        'destinations' => 'zebras',
        'accommodations' => 'lodge',
        'journal' => 'serengeti-sunset',
        'gallery' => 'elephants',
        'contact' => 'baobabs',
        default => 'serengeti-sunset',
    });
@endphp
@if($pageHeroImage)<img class="page-hero-image" src="{{ asset($pageHeroImage) }}" alt="" fetchpriority="high" aria-hidden="true">@endif
<div class="page-hero-overlay" aria-hidden="true"></div>
