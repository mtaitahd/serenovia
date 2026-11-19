<?php $publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')); ?>
<?php $sharedHome = \App\Support\HomepageContent::values($siteSettings); ?>
<?php $phoneHref = preg_replace('/\D+/', '', $siteSettings['phone'] ?? ''); ?>
<?php $footerLinks = json_decode($siteSettings['footer_explore_links'] ?? '[]', true) ?: [['label' => 'Safari journeys', 'url' => '/safaris'], ['label' => 'Places to stay', 'url' => '/accommodations'], ['label' => 'Destinations', 'url' => '/destinations'], ['label' => 'Travel journal', 'url' => '/journal'], ['label' => 'Photo gallery', 'url' => '/gallery']]; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173426">
    @php
        $seo = \App\Support\SeoMetadata::page(get_defined_vars(), $siteSettings);
        $seoTitle = trim($__env->yieldContent('title', $siteSettings['brand_name'] ?? 'Serenovia Africa Safaris'));
        $seoDescription = trim($__env->yieldContent('description', $seo['description']));
        $seo['description'] = \App\Support\SeoMetadata::text($seoDescription);
    @endphp
    <meta name="description" content="{{ $seo['description'] }}">
    <title>{{ $seoTitle }}</title>
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">
    @unless(request()->routeIs('admin.*'))
    <script type="application/ld+json">{!! json_encode(\App\Support\StructuredData::graph(get_defined_vars(), $siteSettings, $seo, $seoTitle), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}</script>
    @endunless
    <link rel="icon" type="image/png" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="apple-touch-icon" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/serenovia.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/faq.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/journal.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/site-pages.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/website-content.css?v=2">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/brand.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/safari-experience.css?v=9">
    @stack('styles')
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/public-navigation.css?v=2">
</head>
<body class="premium-type {{ request()->routeIs('home') ? 'page-home' : '' }} {{ request()->routeIs('tours.show') ? 'page-tour' : '' }}">
    <a class="skip-content" href="#main-content">Skip to content</a>
    <div id="top"></div>
    <header class="site-header serenovia-header">
        <div class="header-utility">
            <div class="header-utility-inner">
                <a href="{{ route('about') }}">{{ $siteSettings['brand_name'] }}</a>
                <a href="{{ route('contact') }}">{{ $sharedHome['nav_contact'] }}</a>
                <a href="{{ route('tours.index') }}" aria-label="Search safari packages">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg>
                </a>
            </div>
        </div>
        <div class="header-main">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $siteSettings['brand_name'] }} home">
            <img class="header-original-logo" src="{{ asset($sharedHome['site_logo']) }}" width="650" height="593" alt="">
        </a>
        <button class="menu-toggle" aria-label="Toggle navigation" aria-controls="public-menu" aria-expanded="false"><span></span><span></span></button>
        <nav id="public-menu" class="main-nav" aria-label="Main navigation">
            <a href="{{ route('home') }}">{{ $sharedHome['nav_home'] }}</a>
            <a href="{{ route('tours.index') }}">{{ $sharedHome['nav_safaris'] }}</a>
            <a href="{{ route('accommodations.index') }}">{{ $sharedHome['nav_stays'] }}</a>
            <a href="{{ route('destinations.index') }}">{{ $sharedHome['nav_destinations'] }}</a>
            <a href="{{ route('travel-guide') }}">{{ $sharedHome['nav_guide'] }}</a>
            <a href="{{ route('gallery.index') }}">{{ $sharedHome['nav_gallery'] }}</a>
            <a href="{{ route('contact') }}" class="nav-cta">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>{{ $sharedHome['nav_plan'] }}
            </a>
        </nav>
        </div>
    </header>
    <main id="main-content">@yield('content')</main>
    @include('public.footer')
    <dialog class="photo-viewer" aria-label="Photo gallery"><button type="button" class="photo-close" aria-label="Close photo">✕</button><div class="photo-stage"><button type="button" class="photo-prev" aria-label="Previous photo">←</button><figure><img alt=""><figcaption></figcaption></figure><button type="button" class="photo-next" aria-label="Next photo">→</button></div><span class="photo-count" aria-live="polite"></span></dialog>
    <script src="{{ $publicBasePath }}/js/serenovia.js"></script>
    <script src="{{ $publicBasePath }}/js/safari-experience.js?v=1"></script>
    @stack('scripts')
</body>
</html>
