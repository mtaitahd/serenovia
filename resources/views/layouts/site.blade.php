@php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
@php($phoneHref = preg_replace('/\D+/', '', $siteSettings['phone'] ?? ''))
@php($footerLinks = json_decode($siteSettings['footer_explore_links'] ?? '[]', true) ?: [['label' => 'Safari journeys', 'url' => '/safaris'], ['label' => 'Places to stay', 'url' => '/accommodations'], ['label' => 'Destinations', 'url' => '/destinations'], ['label' => 'Travel journal', 'url' => '/journal'], ['label' => 'Photo gallery', 'url' => '/gallery']])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173426">
    <meta name="description" content="@yield('description', $siteSettings['hero_subtitle'] ?? 'Thoughtfully made journeys through Tanzania.')">
    <title>@yield('title', $siteSettings['brand_name'] ?? 'Serenovia Africa Safaris')</title>
    <link rel="icon" type="image/png" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="apple-touch-icon" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/serenovia.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/faq.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/journal.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/site-pages.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/website-content.css?v=2">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/brand.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/safari-experience.css?v=8">
    @stack('styles')
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/ui-typography.css?v=2">
</head>
<body class="premium-type {{ request()->routeIs('home') ? 'page-home' : '' }} {{ request()->routeIs('tours.show') ? 'page-tour' : '' }}">
    <a class="skip-content" href="#main-content">Skip to content</a>
    <header class="site-header" id="top">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $siteSettings['brand_name'] }} home">
            <img class="brand-mark brand-logo-image" src="{{ $publicBasePath }}/images/serenovia-mark.png" alt="">
            <span class="brand-copy"><strong>SERENOVIA</strong><small>AFRICA SAFARIS</small></span>
        </a>
        <button class="menu-toggle" aria-label="Open navigation" aria-expanded="false"><span></span><span></span></button>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('tours.index') }}">Safaris</a>
            <a href="{{ route('accommodations.index') }}">Stays</a>
            <a href="{{ route('destinations.index') }}">Destinations</a>
            <a href="{{ route('journal.index') }}">Journal</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}" class="nav-cta">Plan your trip <span>↗</span></a>
        </nav>
    </header>
    <main id="main-content">@yield('content')</main>
    @include('public.footer')
    <dialog class="photo-viewer" aria-label="Photo gallery"><button type="button" class="photo-close" aria-label="Close photo">✕</button><div class="photo-stage"><button type="button" class="photo-prev" aria-label="Previous photo">←</button><figure><img alt=""><figcaption></figcaption></figure><button type="button" class="photo-next" aria-label="Next photo">→</button></div><span class="photo-count" aria-live="polite"></span></dialog>
    <script src="{{ $publicBasePath }}/js/serenovia.js"></script>
    <script src="{{ $publicBasePath }}/js/safari-experience.js?v=1"></script>
    @stack('scripts')
</body>
</html>
