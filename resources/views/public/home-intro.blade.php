@php
    $introTitle = $siteSettings['home_intro_title'] ?? "Made of wild places.\nMade for your kind of wonder.";
    $introLines = preg_split('/\R/u', $introTitle, 2);
@endphp
<section class="home-intro" id="story" aria-labelledby="home-intro-title">
    <div class="home-intro-heading">
        <span class="eyebrow">{{ $siteSettings['home_intro_eyebrow'] ?? 'KARIBU, WELCOME' }}</span>
        <h2 id="home-intro-title">
            <span class="home-intro-highlight">{{ $introLines[0] }}</span>
            @if(!empty($introLines[1]))<br>{{ $introLines[1] }}@endif
        </h2>
        <p>Explore Tanzania at your own pace, with a safari shaped around what you want to see and do.</p>
    </div>
    <div class="home-intro-copy">
        <p>{{ $siteSettings['home_intro_copy'] ?? 'At Serenovia, we believe the best safari is the one that feels like yours. From the first morning on the savannah to the last light over the Indian Ocean, our local team brings Tanzania closer—with care, curiosity and a little bit of magic.' }}</p>
        <div class="home-intro-actions">
            <a href="{{ route('contact') }}">Talk to our team</a>
            <a href="{{ route('tours.index') }}">Explore our safaris</a>
        </div>
        <nav class="home-intro-links" aria-label="Travel information">
            <a href="{{ route('about') }}">About us</a>
            <a href="{{ route('travel-guide') }}">Travel guide</a>
            <a href="{{ route('responsible') }}">Responsible travel</a>
        </nav>
    </div>
</section>
