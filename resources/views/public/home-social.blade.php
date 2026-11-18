@php
    $instagram = $siteSettings['instagram'] ?? '';
    $youtube = $siteSettings['subscribe_youtube_url'] ?? '';
    $videos = collect(json_decode($siteSettings['subscribe_videos'] ?? '[]', true))
        ->filter(fn ($video) => is_array($video) && filled($video['url'] ?? null))->take(3);
    $showYoutube = ($siteSettings['subscribe_enabled'] ?? '0') === '1' && ($youtube || $videos->isNotEmpty());
@endphp
@if($gallery->isNotEmpty() || $instagram || $showYoutube)
    <div class="home-social">
        @if($gallery->isNotEmpty() || $instagram)
            <section class="home-social-section" aria-labelledby="home-social-photos">
                <h2 id="home-social-photos">{{ $instagram ? 'Stay in touch on Instagram' : 'Scenes from our Tanzania' }}</h2>
                @if($gallery->isNotEmpty())
                    <div class="home-social-photos">
                        @foreach($gallery as $image)
                            <a href="{{ $homePhotoUrl($image) }}" data-photo data-photo-group="home" data-caption="{{ $homePhotos[$image] }}" aria-label="View photo: {{ $homePhotos[$image] }}">
                                <img src="{{ $homePhotoUrl($image) }}" alt="{{ $homePhotos[$image] }}" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
                <a class="home-social-button {{ $instagram ? 'home-social-instagram' : '' }}" href="{{ $instagram ?: route('gallery.index') }}" @if($instagram) target="_blank" rel="noopener" @endif>
                    {{ $instagram ? 'Follow on Instagram' : 'Explore the photo gallery' }}
                </a>
            </section>
        @endif

        @if($showYoutube)
            <section class="home-social-section" aria-labelledby="home-social-videos">
                <h2 id="home-social-videos">{{ ($siteSettings['subscribe_heading'] ?? '') ?: 'Subscribe to Serenovia on YouTube' }}</h2>
                @if($videos->isNotEmpty())
                    <div class="home-social-videos">
                        @foreach($videos as $video)
                            <a class="home-social-video" href="{{ $video['url'] }}" target="_blank" rel="noopener">
                                <span class="home-social-thumbnail">
                                    @if(!empty($video['thumbnail']))
                                        <img src="{{ $video['thumbnail'] }}" alt="" loading="lazy">
                                    @endif
                                    <span class="home-social-play" aria-hidden="true">▶</span>
                                </span>
                                <strong>{{ ($video['title'] ?? '') ?: 'Watch on YouTube' }}</strong>
                                @if(!empty($video['views']) || !empty($video['when']))
                                    <small>{{ implode(' · ', array_filter([$video['views'] ?? '', $video['when'] ?? ''])) }}</small>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($youtube)
                    <a class="home-social-button home-social-youtube" href="{{ $youtube }}" target="_blank" rel="noopener">Subscribe on YouTube</a>
                @endif
            </section>
        @endif
    </div>
@endif
