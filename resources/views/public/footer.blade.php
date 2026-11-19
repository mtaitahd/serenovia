@php
    $footerImagePath = $sharedHome['footer_image'];
    if (!$footerImagePath) $footerImagePath = \App\Models\GalleryImage::orderBy('sort_order')->value('path');
    $footerWhatsapp = preg_replace('/\D+/', '', $siteSettings['whatsapp'] ?? $phoneHref);
@endphp
<footer class="site-footer scenic-footer" id="contact">
    @if($footerImagePath)<img class="footer-background" src="{{ asset($footerImagePath) }}" alt="" loading="lazy" aria-hidden="true">@endif
    <div class="footer-contact-strip section-wrap">
        <a class="footer-contact-item" href="tel:+{{ $phoneHref }}"><span class="footer-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m5 3 4 1 1 5-2 2a15 15 0 0 0 5 5l2-2 5 1 1 4c-1 3-4 3-7 2C8 19 3 14 2 8 1 5 2 3 5 3Z"/></svg></span><span><small>Call us</small><strong>{{ $siteSettings['phone'] }}</strong></span></a>
        <a class="footer-contact-item" href="mailto:{{ $siteSettings['email'] }}"><span class="footer-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 6 9 7 9-7"/></svg></span><span><small>Email us</small><strong>{{ $siteSettings['email'] }}</strong></span></a>
        <a class="footer-contact-item" href="{{ route('contact') }}"><span class="footer-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><span><small>Our office</small><strong>{{ $siteSettings['footer_location'] ?? $siteSettings['address'] }}</strong></span></a>
    </div>
    <div class="footer-main section-wrap">
        <div class="footer-brand-col"><a class="brand brand-footer" href="{{ route('home') }}" aria-label="{{ $siteSettings['brand_name'] }} home"><img class="footer-full-logo" src="{{ asset($sharedHome['site_logo']) }}" alt="{{ $siteSettings['brand_name'] }}"></a><p>{{ $siteSettings['footer_tagline'] ?? 'Thoughtfully made journeys through Tanzania.' }}</p></div>
        <div class="footer-links footer-explore"><h2 class="footer-heading">{{ $siteSettings['footer_explore_heading'] ?? 'Useful links' }}</h2>@foreach($footerLinks as $footerLink)<a href="{{ str_starts_with($footerLink['url'], '/') && !str_starts_with($footerLink['url'], '//') ? url($footerLink['url']) : $footerLink['url'] }}"><span aria-hidden="true">»</span>{{ $footerLink['label'] }}</a>@endforeach</div>
        <div class="footer-links"><h2 class="footer-heading">{{ $siteSettings['footer_contact_heading'] ?? 'Get in touch' }}</h2><p class="footer-address">{{ $siteSettings['address'] }}</p><a href="mailto:{{ $siteSettings['email'] }}">{{ $siteSettings['email'] }}</a><a href="tel:+{{ $phoneHref }}">{{ $siteSettings['phone'] }}</a>@if($footerWhatsapp)<a href="https://wa.me/{{ $footerWhatsapp }}" target="_blank" rel="noopener">WhatsApp our team</a>@endif</div>
        <div class="footer-links footer-connect"><h2 class="footer-heading">Stay connected</h2><p>{{ $siteSettings['home_plan_copy'] ?? 'Tell us what you have been dreaming about. We will take it from there.' }}</p><a class="footer-action" href="{{ \App\Support\HomepageContent::link($sharedHome['home_plan_url']) }}">{{ $siteSettings['home_plan_button'] ?? 'Plan your safari' }} </a><a class="footer-action" href="{{ route('journal.index') }}">Travel stories </a><div class="footer-socials">@if(!empty($siteSettings['instagram']))<a href="{{ $siteSettings['instagram'] }}" target="_blank" rel="noopener">Instagram</a>@endif @if(!empty($siteSettings['facebook']))<a href="{{ $siteSettings['facebook'] }}" target="_blank" rel="noopener">Facebook</a>@endif</div></div>
    </div>
    <div class="footer-bottom section-wrap">@if(!empty($siteSettings['footer_bottom_note']))<span>{{ $siteSettings['footer_bottom_note'] }}</span>@endif<span>© {{ date('Y') }} {{ $siteSettings['brand_name'] }}. All rights reserved.</span><nav aria-label="Footer navigation"><a href="{{ route('about') }}">About us</a><a href="{{ route('responsible') }}">Responsible travel</a><a href="{{ route('tours.index') }}">Safaris</a><a href="{{ route('contact') }}">Contact</a></nav><a class="footer-back-top" href="#top" aria-label="Back to top">↑</a></div>
    @if(request()->routeIs('home') && $sharedHome['home_hero_video_enabled'] === '1' && $sharedHome['home_hero_video'])
        <div class="section-wrap" style="padding-block:12px;font-size:11px">
            <button type="button" data-hero-video-toggle style="background:none;border:0;color:inherit;font:inherit;text-decoration:underline;cursor:pointer">Pause homepage video</button>
            @if($sharedHome['home_video_credit']) · @if($sharedHome['home_video_credit_url'])<a href="{{ $sharedHome['home_video_credit_url'] }}">{{ $sharedHome['home_video_credit'] }}</a>@else{{ $sharedHome['home_video_credit'] }}@endif @endif
            @if($sharedHome['home_video_license']) · @if($sharedHome['home_video_license_url'])<a href="{{ $sharedHome['home_video_license_url'] }}">{{ $sharedHome['home_video_license'] }}</a>@else{{ $sharedHome['home_video_license'] }}@endif @endif
        </div>
    @endif
</footer>
