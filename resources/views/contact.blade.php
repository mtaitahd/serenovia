@extends('layouts.site')
@section('title', 'Plan your trip · '.$siteSettings['brand_name'])
@section('content')
    <section class="listing-hero page-photo-hero contact-hero">@include('public.hero-image', ['heroKey' => 'contact'])<span class="eyebrow">YOUR TANZANIA STORY STARTS HERE</span><h1>Let's make it <em>unforgettable.</em></h1><p>Tell us what you've been dreaming about. Our local team will help you shape the details.</p></section>
    <section class="contact-layout section-wrap">
        <aside class="contact-intro"><span class="eyebrow">SAY KARIBU</span><h2>Tell us a little<br>about your trip.</h2><p>Share the basics and we will follow up to learn more about the kind of journey you have in mind.</p><div class="contact-fact"><span>EMAIL</span><a href="mailto:{{ $siteSettings['email'] }}">{{ $siteSettings['email'] }}</a></div><div class="contact-fact"><span>PHONE</span><a href="tel:+{{ preg_replace('/\D+/', '', $siteSettings['phone']) }}">{{ $siteSettings['phone'] }}</a></div><div class="contact-fact"><span>BASED IN</span><b>{{ $siteSettings['address'] }}</b></div></aside>
        <form class="contact-form" method="POST" action="{{ route('inquiries.store') }}">
            @csrf
            @if(session('success'))<div class="form-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="form-error">Please review the highlighted fields and try again.</div>@endif
            <div class="form-grid">
                <div class="field"><label for="name">YOUR NAME *</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="email">EMAIL ADDRESS *</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="phone">PHONE / WHATSAPP</label><input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"></div>
                <div class="field"><label for="tour_package_id">JOURNEY YOU'RE INTERESTED IN</label><select id="tour_package_id" name="tour_package_id"><option value="">I'm open to ideas</option>@foreach($tours as $tour)<option value="{{ $tour->id }}" @selected(old('tour_package_id', $selectedTour) == $tour->id)>{{ $tour->title }}</option>@endforeach</select></div>
                <div class="field"><label for="travel_date">PREFERRED TRAVEL DATE</label><input id="travel_date" type="date" name="travel_date" min="{{ now()->toDateString() }}" value="{{ old('travel_date', $preferredDate ?? null) }}"></div>
                <div class="field"><label for="travellers">NUMBER OF TRAVELLERS *</label><input id="travellers" type="number" min="1" max="100" name="travellers" value="{{ old('travellers', $preferredParty ?? 2) }}" required></div>
                <div class="field full"><label for="message">WHAT WOULD MAKE THIS JOURNEY YOURS?</label><textarea id="message" name="message" rows="6" placeholder="Tell us about the places, experiences or pace you're dreaming of...">{{ old('message') }}</textarea></div>
                <div class="field website-field" aria-hidden="true"><label for="website">Leave this empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
            </div>
            <button class="button button-dark" type="submit">Send your enquiry </button>
            <p class="form-privacy">Your details are used only to respond to this trip enquiry.</p>
        </form>
    </section>
@endsection
