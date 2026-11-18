@php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173426">
    <title>@yield('title', 'Admin') · Serenovia</title>
    <link rel="icon" type="image/png" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="apple-touch-icon" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-sidebar.css?v=1">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-components.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-brand.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-package-builder.css?v=3">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-gallery.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-popups.css?v=2">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-catalogue.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-website-content.css?v=3">
    @if(request()->routeIs('admin.dashboard'))<link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-dashboard.css?v=1">@endif
    @stack('styles')
    @if(request()->routeIs('admin.generator.*'))<link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-generator.css?v=5">@endif
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/ui-typography.css?v=1">
</head>
<body class="admin-body {{ request()->boolean('popup') ? 'admin-popup-body' : '' }} {{ request()->routeIs('admin.generator.*') ? 'generator-page' : '' }}">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <img class="admin-logo-image" src="{{ $publicBasePath }}/images/serenovia-mark.png" alt=""><span><b>SERENOVIA</b><small>ADMIN DESK</small></span>
        </a>
        <div class="sidebar-caption">WORKSPACE</div>
        <nav class="admin-navigation" aria-label="Admin navigation">
            <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])><span>⌂</span> Overview</a>
            <a href="{{ route('admin.settings.edit') }}" data-admin-popup @class(['active' => request()->routeIs('admin.settings.*')])><span>⚙</span> Settings</a>
            <a href="{{ route('admin.sitemap.index') }}" data-admin-popup @class(['active' => request()->routeIs('admin.sitemap.*')])><span>⌗</span> Sitemap</a>
            @php($websiteContentActive = request()->routeIs('admin.website-content.*'))
            <button class="sidebar-group-toggle" type="button" data-sidebar-toggle="website-content" data-active="{{ $websiteContentActive ? 'true' : 'false' }}" aria-expanded="{{ $websiteContentActive ? 'true' : 'false' }}" aria-controls="sidebar-group-website-content"><span>▤</span><span class="sidebar-group-label">Static Pages</span><i aria-hidden="true">⌄</i></button>
            <div class="sidebar-group-links {{ $websiteContentActive ? 'is-open' : '' }}" id="sidebar-group-website-content" data-sidebar-group="website-content" aria-hidden="{{ $websiteContentActive ? 'false' : 'true' }}">
                <a href="{{ route('admin.website-content.hero-images') }}" data-admin-popup><span>Hero images</span></a>
                <a href="{{ route('admin.website-content.homepage') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.homepage*')])><span>Homepage Buttons &amp; Captions</span></a>
                <a href="{{ route('admin.website-content.travellers') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.travellers*')])><span>Traveller Stories</span></a>
                <a href="{{ route('admin.website-content.subscribe') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.subscribe*')])><span>Subscribe Section</span></a>
                <a href="{{ route('admin.website-content.footer') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.footer*')])><span>Footer</span></a>
                <a href="{{ route('admin.website-content.route-points') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.route-points*')])><span>Start &amp; End Points</span></a>
                <a href="{{ route('admin.website-content.listing-titles') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.listing-titles*')])><span>Listing Titles</span></a>
                <a href="{{ route('admin.website-content.story-images') }}" data-admin-popup @class(['active' => request()->routeIs('admin.website-content.story-images*')])><span>Our Story Images &amp; Captions</span></a>
            </div>
            <div class="sidebar-caption nav-caption">EXPERIENCE</div>
            <a href="{{ route('admin.tours.index') }}" @class(['active' => request()->routeIs('admin.tours.*')])><span>✳</span> Safari packages</a>
            <a href="{{ route('admin.generator.imports') }}" @class(['active' => request()->routeIs('admin.generator.imports', 'admin.generator.review', 'admin.generator.import-preview')])><span>↑</span> Upload &amp; Preview</a>
            <a href="{{ route('admin.generator.index') }}" @class(['active' => request()->routeIs('admin.generator.index', 'admin.generator.run', 'admin.generator.preview')])><span>✦</span> Generate Packages</a>
            <a href="{{ route('admin.tour-categories.index') }}" @class(['active' => request()->routeIs('admin.tour-categories.*')])><span>⌗</span> Tour categories</a>
            <a href="{{ route('admin.accommodations.index') }}" @class(['active' => request()->routeIs('admin.accommodations.*')])><span>⌂</span> Accommodations</a>
            <a href="{{ route('admin.package-days.index') }}" data-admin-popup @class(['active' => request()->routeIs('admin.package-days.*')])><span>◷</span> Itinerary days</a>
            <a href="{{ route('admin.countries.index') }}" @class(['active' => request()->routeIs('admin.countries.*')])><span>◎</span> Countries</a>
            <a href="{{ route('admin.destinations.index') }}" @class(['active' => request()->routeIs('admin.destinations.*')])><span>⌖</span> Destinations</a>
            <div class="sidebar-caption nav-caption">PUBLISHING</div>
            <a href="{{ route('admin.articles.index') }}" @class(['active' => request()->routeIs('admin.articles.*')])><span>▤</span> Journal &amp; articles</a>
            <a href="{{ route('admin.gallery.index') }}" @class(['active' => request()->routeIs('admin.gallery.*')])><span>▧</span> Photo gallery</a>
            <a href="{{ route('admin.faqs.index') }}" @class(['active' => request()->routeIs('admin.faqs.*')])><span>?</span> FAQs</a>
            <div class="sidebar-caption nav-caption">COMMUNICATION</div>
            <a href="{{ route('admin.inquiries.index') }}" @class(['active' => request()->routeIs('admin.inquiries.*')])><span>✉</span> Enquiries <i>{{ \App\Models\Inquiry::where('status', 'new')->count() ?: '' }}</i></a>
            <a href="{{ route('home') }}" target="_blank" rel="noopener"><span>↗</span> View website</a>
        </nav>
        <div class="admin-user-card">
            <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="user-info"><b>{{ auth()->user()->name }}</b><small>Administrator</small></span>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button aria-label="Log out" title="Log out">↗</button></form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div><span class="topbar-overline">SERENOVIA AFRICA SAFARIS</span><span class="topbar-divider">/</span><span class="topbar-section">CONTENT MANAGEMENT</span></div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="site-preview">Open website <span>↗</span></a>
        </header>
        <div class="admin-page">
            @if (session('success')) <div class="notice success">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="notice error">{{ session('error') }}</div> @endif
            @if ($errors->any()) <div class="notice error">Please check the highlighted fields and try again.</div> @endif
            @yield('content')
        </div>
    </main>
    <dialog class="admin-form-popup" id="admin-form-popup" aria-label="Admin form">
        <button class="admin-form-popup-close" type="button" data-close-admin-popup aria-label="Close">×</button>
        <iframe id="admin-form-popup-frame" title="Admin form"></iframe>
    </dialog>
    <script src="{{ $publicBasePath }}/vendor/tinymce/tinymce.min.js?v=7.2.1"></script>
    <script src="{{ $publicBasePath }}/js/admin-rich-editor.js?v=1"></script>
    <script src="{{ $publicBasePath }}/js/admin-form-popups.js?v=4" defer></script>
    <script src="{{ $publicBasePath }}/js/admin-sidebar.js?v=1" defer></script>
    @if (request()->boolean('popup') && session()->has('success'))
        <script>window.parent.postMessage({ type: 'admin-form-saved' }, window.location.origin);</script>
    @endif
    @stack('scripts')
</body>
</html>
