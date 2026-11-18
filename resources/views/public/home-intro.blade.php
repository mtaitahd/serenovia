@php($introLines = preg_split('/\R/u', $home['home_intro_title'], 2))
<section class="home-intro" id="story" aria-labelledby="home-intro-title">
    <div class="home-intro-heading">
        <span class="eyebrow">{{ $home['home_intro_eyebrow'] }}</span>
        <h2 id="home-intro-title"><span class="home-intro-highlight">{{ $introLines[0] }}</span>@if(!empty($introLines[1]))<br>{{ $introLines[1] }}@endif</h2>
        <p>{{ $home['home_intro_lead'] }}</p>
    </div>
    <div class="home-intro-copy"><p>{{ $home['home_intro_copy'] }}</p>
        <div class="home-intro-actions"><a href="{{ $homeLink('home_intro_primary_url') }}">{{ $home['home_intro_primary'] }}</a><a href="{{ $homeLink('home_intro_secondary_url') }}">{{ $home['home_intro_secondary'] }}</a></div>
        <nav class="home-intro-links" aria-label="Travel information">@foreach(['about','guide','responsible'] as $link)<a href="{{ $homeLink('home_intro_'.$link.'_url') }}">{{ $home['home_intro_'.$link] }}</a>@endforeach</nav>
    </div>
</section>
