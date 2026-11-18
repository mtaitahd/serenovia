@php
    $socialPhotos = collect(range(1,6))->filter(fn ($i) => filled($home['home_social_photo_'.$i]));
    $videos = collect(range(1,3))->filter(fn ($i) => filled($home['home_video_'.$i.'_url']));
    $showYoutube = $home['subscribe_enabled'] === '1' && ($home['subscribe_youtube_url'] || $videos->isNotEmpty());
@endphp
@if($home['home_social_enabled'] === '1' || $showYoutube)
<div class="home-social">
    @if($home['home_social_enabled'] === '1')
    <section class="home-social-section" aria-labelledby="home-social-photos">
        <h2 id="home-social-photos">{{ $home['home_social_title'] }}</h2>
        <div class="home-social-photos">@foreach($socialPhotos as $i)
            <a href="{{ asset($home['home_social_photo_'.$i]) }}" data-photo data-photo-group="home" data-caption="{{ $home['home_social_caption_'.$i] }}" aria-label="{{ $home['home_social_caption_'.$i] }}"><img src="{{ asset($home['home_social_photo_'.$i]) }}" alt="{{ $home['home_social_caption_'.$i] }}" loading="lazy"></a>
        @endforeach</div>
        <a class="home-social-button {{ $home['instagram'] ? 'home-social-instagram' : '' }}" href="{{ $homeLink('home_social_url') }}">{{ $home['home_social_button'] }}</a>
    </section>
    @endif
    @if($showYoutube)
    <section class="home-social-section" aria-labelledby="home-social-videos">
        <h2 id="home-social-videos">{{ $home['subscribe_heading'] }}</h2>
        <div class="home-social-videos">@foreach($videos as $i)
            <a class="home-social-video" href="{{ $home['home_video_'.$i.'_url'] }}" target="_blank" rel="noopener">
                <span class="home-social-thumbnail">@if($home['home_video_'.$i.'_thumbnail'])<img src="{{ asset($home['home_video_'.$i.'_thumbnail']) }}" alt="" loading="lazy">@endif<span class="home-social-play" aria-hidden="true">▶</span></span>
                <strong>{{ $home['home_video_'.$i.'_title'] ?: 'Watch on YouTube' }}</strong>
                <small>{{ implode(' · ', array_filter([$home['home_video_'.$i.'_views'], $home['home_video_'.$i.'_when']])) }}</small>
            </a>
        @endforeach</div>
        @if($home['subscribe_youtube_url'])<a class="home-social-button home-social-youtube" href="{{ $home['subscribe_youtube_url'] }}" target="_blank" rel="noopener">{{ $home['home_youtube_button'] }}</a>@endif
    </section>
    @endif
</div>
@endif
