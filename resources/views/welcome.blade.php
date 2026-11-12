<!DOCTYPE html>
<html lang="en">
<head>
    @php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f5ef">
    <meta name="description" content="Discover Tanzania with Serenovia Africa Safaris. Thoughtfully designed safaris, Kilimanjaro adventures and Zanzibar escapes, guided by local knowledge.">
    <title>Serenovia Africa Safaris — Find your wild</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/serenovia.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/faq.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/journal.css">
</head>
<body>
    <header class="site-header" id="top">
        <a class="brand" href="#top" aria-label="Serenovia Africa Safaris home">
            <span class="brand-mark" aria-hidden="true"><span>S</span><i></i></span>
            <span class="brand-copy"><strong>SERENOVIA</strong><small>AFRICA SAFARIS</small></span>
        </a>
        <button class="menu-toggle" aria-label="Open navigation" aria-expanded="false"><span></span><span></span></button>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="#journeys">Journeys</a>
            <a href="#destinations">Destinations</a>
            <a href="#journal">Journal</a>
            <a href="#faq">FAQ</a>
            <a href="#story">Our story</a>
            <a href="#contact">Contact</a>
            <a class="nav-cta" href="#plan">Plan your safari <span>↗</span></a>
        </nav>
    </header>

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-image"></div>
            <div class="hero-shade"></div>
            <div class="hero-content">
                <div class="eyebrow light"><span class="eyebrow-line"></span> TANZANIA, AT YOUR OWN PACE</div>
                <h1 id="hero-title">The wild<br>is calling<span>.</span></h1>
                <p>Some journeys change the way you see the world.<br class="desktop-only"> Let us take you to the heart of Tanzania.</p>
                <div class="hero-actions">
                    <a href="#plan" class="button button-coral">Start your journey <span>↗</span></a>
                    <a href="#journeys" class="text-link light-link">Explore our safaris <span>↓</span></a>
                </div>
            </div>
            <div class="hero-side-note"><span>03°20′S&nbsp; 37°20′E</span><i></i><span>KILIMANJARO, TANZANIA</span></div>
            <div class="hero-bottom">
                <span>LOCAL KNOWLEDGE. UNFORGETTABLE PLACES.</span>
                <a href="#journeys">SCROLL TO EXPLORE <span>↓</span></a>
            </div>
            <div class="hero-index"><strong>01</strong><span> / 04</span></div>
        </section>

        <section class="intro section-wrap" id="story">
            <div class="intro-label"><span class="eyebrow">KARIBU, WELCOME</span><span class="label-rule"></span><span class="label-place">MOSHI · TANZANIA</span></div>
            <div class="intro-copy">
                <h2>Made of wild places.<br><em>Made for your kind of wonder.</em></h2>
                <div class="intro-bottom">
                    <p>At Serenovia, we believe the best safari is the one that feels like yours. From the first morning on the savannah to the last light over the Indian Ocean, our local team brings Tanzania closer—with care, curiosity and a little bit of magic.</p>
                    <a class="text-link" href="#contact">Get to know us <span>↗</span></a>
                </div>
            </div>
            <div class="intro-stamp" aria-hidden="true"><span>LOCAL HEART</span><b>✳</b><span>WILD SOUL</span></div>
        </section>

        <section class="journeys" id="journeys">
            <div class="section-wrap">
                <div class="section-heading">
                    <div><span class="eyebrow">GO WHERE THE STORY TAKES YOU</span><h2>Choose your <em>adventure.</em></h2></div>
                    <p>Big skies, ancient trails and salt-kissed shores.<br>One beautiful country, a thousand ways to feel it.</p>
                </div>
                <div class="journey-grid">
                    <a class="journey-card card-safari" href="#plan">
                        <div class="journey-photo"></div><span class="card-number">01 / THE CLASSIC</span>
                        <div class="journey-info"><div><h3>Safari, untamed.</h3><p>Serengeti · Ngorongoro · Tarangire</p></div><span class="circle-arrow">↗</span></div>
                    </a>
                    <a class="journey-card card-mountain" href="#plan">
                        <div class="journey-photo"></div><span class="card-number">02 / THE CLIMB</span>
                        <div class="journey-info"><div><h3>Above the clouds.</h3><p>Kilimanjaro · Mount Meru</p></div><span class="circle-arrow">↗</span></div>
                    </a>
                    <a class="journey-card card-island" href="#plan">
                        <div class="journey-photo"></div><span class="card-number">03 / THE SLOWDOWN</span>
                        <div class="journey-info"><div><h3>Island state of mind.</h3><p>Zanzibar · Pemba · Mafia</p></div><span class="circle-arrow">↗</span></div>
                    </a>
                </div>
                <div class="journeys-foot"><span>NO TWO JOURNEYS ARE THE SAME.</span><a class="text-link" href="#plan">Tell us what moves you <span>↗</span></a></div>
            </div>
        </section>

        <section class="destinations section-wrap" id="destinations">
            <div class="destination-title"><span class="eyebrow">A COUNTRY THAT STAYS WITH YOU</span><h2>Four corners.<br><em>Endless feeling.</em></h2><p>Come for the wildlife. Stay for the way Tanzania makes you feel.</p><a class="text-link" href="#plan">Find your place <span>↗</span></a></div>
            <div class="destination-list">
                <a href="#plan"><span class="destination-no">01</span><span class="destination-name">Serengeti</span><span class="destination-desc">Where the plains go on forever</span><span class="destination-arrow">↗</span></a>
                <a href="#plan"><span class="destination-no">02</span><span class="destination-name">Ngorongoro</span><span class="destination-desc">A world within a world</span><span class="destination-arrow">↗</span></a>
                <a href="#plan"><span class="destination-no">03</span><span class="destination-name">Kilimanjaro</span><span class="destination-desc">The roof of Africa</span><span class="destination-arrow">↗</span></a>
                <a href="#plan"><span class="destination-no">04</span><span class="destination-name">Zanzibar</span><span class="destination-desc">Where the ocean slows time</span><span class="destination-arrow">↗</span></a>
            </div>
        </section>

        <section class="journal" id="journal" aria-labelledby="journal-title">
            <div class="section-wrap">
                <div class="journal-heading">
                    <div><span class="eyebrow">NOTES FROM TANZANIA</span><h2 id="journal-title">The Serenovia <em>journal.</em></h2></div>
                    <p>Small stories, local perspectives and inspiration<br>for the places that stay with you.</p>
                </div>
                <div class="article-grid">
                    <details class="article-card">
                        <summary>
                            <div class="article-image article-safari"><span>FIELD NOTES&nbsp; / &nbsp;TANZANIA</span></div>
                            <div class="article-meta"><span>◷ &nbsp;1 MIN READ</span><span>• &nbsp;SAFARI</span></div>
                            <h3>Your first safari: learning to slow down</h3>
                            <p>A safari is not a checklist. It is the hush before sunrise, the guide who knows where to pause, and the moment the plains begin to move.</p>
                            <div class="article-read"><span>READ THE STORY</span><b>↗</b></div>
                        </summary>
                        <div class="article-full"><p>Leave a little room in the day for the unexpected. The most memorable moments often arrive between the famous sightings: a herd crossing the horizon, the changing light on the grass, or a quiet conversation with your guide.</p><p>Every park has its own rhythm. Let your interests and the time you have shape the route, then give each place time to unfold. A thoughtful safari is measured in moments, not miles.</p></div>
                    </details>
                    <details class="article-card">
                        <summary>
                            <div class="article-image article-mountain"><span>FIELD NOTES&nbsp; / &nbsp;KILIMANJARO</span></div>
                            <div class="article-meta"><span>◷ &nbsp;1 MIN READ</span><span>• &nbsp;MOUNTAIN</span></div>
                            <h3>On the mountain, every step has a story</h3>
                            <p>The climb begins long before the summit. It starts with a trail, a steady pace and a team sharing the same horizon.</p>
                            <div class="article-read"><span>READ THE STORY</span><b>↗</b></div>
                        </summary>
                        <div class="article-full"><p>Kilimanjaro changes with every step: forest shade gives way to open moorland, then to a high, wide quiet. Each day brings a new landscape and a different view of the mountain.</p><p>There is no need to rush the experience. Walk at a considered pace, take in the small details around you, and let your local mountain crew lead the way. Reaching the summit is a moment to remember; so is the journey there.</p></div>
                    </details>
                    <details class="article-card">
                        <summary>
                            <div class="article-image article-island"><span>FIELD NOTES&nbsp; / &nbsp;ZANZIBAR</span></div>
                            <div class="article-meta"><span>◷ &nbsp;1 MIN READ</span><span>• &nbsp;ISLAND LIFE</span></div>
                            <h3>Zanzibar, best explored unhurried</h3>
                            <p>Beyond the shoreline, island life reveals itself in carved doorways, market colours and the scent of spices on the breeze.</p>
                            <div class="article-read"><span>READ THE STORY</span><b>↗</b></div>
                        </summary>
                        <div class="article-full"><p>Give yourself time to wander through Stone Town's narrow lanes, notice the details in its old doors, and pause when a small café or market catches your eye. The island's character is found in these everyday encounters.</p><p>Then follow the coast at your own pace. A few unplanned hours can turn a beach stay into a richer part of your Tanzania journey.</p></div>
                    </details>
                </div>
                <div class="journal-foot"><span>MORE STORIES ARE ON THEIR WAY</span><a href="#plan" class="text-link">Plan a journey of your own <span>↗</span></a></div>
            </div>
        </section>

        <section class="quote-band">
            <div class="quote-image"></div><div class="quote-overlay"></div>
            <div class="quote-content"><span class="eyebrow light">A LITTLE MORE THAN A HOLIDAY</span><blockquote>“The best souvenirs<br>are the stories <em>you carry home.”</em></blockquote><span class="quote-credit">— THE SERENOVIA WAY</span></div>
        </section>

        <section class="faq section-wrap" id="faq" aria-labelledby="faq-title">
            <div class="faq-heading">
                <span class="eyebrow">A FEW GOOD THINGS TO KNOW</span>
                <h2 id="faq-title">Frequently asked <em>questions.</em></h2>
                <p>Planning a trip should feel exciting. Here are a few answers to help you get started.</p>
            </div>
            <div class="faq-list">
                <details class="faq-item" open>
                    <summary><span class="faq-number">01</span><span class="faq-question">When is the best time to visit Tanzania?</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                    <div class="faq-answer"><p>There isn't one perfect month for every journey—wildlife, weather and experiences vary by region and season. Tell us what you'd love to see and when you can travel; we'll help you choose a route that fits.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-number">02</span><span class="faq-question">Do I need a visa to visit Tanzania?</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                    <div class="faq-answer"><p>Entry requirements depend on your nationality and travel details. Please check the <a href="https://visa.immigration.go.tz/guidelines" target="_blank" rel="noopener">official Tanzania Immigration visa guidelines</a> and use its <a href="https://visa.immigration.go.tz/" target="_blank" rel="noopener">visa application portal</a>. Confirm your requirements with Immigration before booking or travelling.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-number">03</span><span class="faq-question">What is included in my safari?</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                    <div class="faq-answer"><p>Every journey is different. Your written itinerary and quote will list what's included—such as accommodation, transport, guiding, meals or park fees—and anything arranged separately, so you can review the details before confirming.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-number">04</span><span class="faq-question">Can you customize the itinerary?</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                    <div class="faq-answer"><p>Absolutely. Share your travel dates, interests, pace and budget, and we'll help shape a Tanzania journey around what matters to you—from time on safari to a mountain climb or a few slower days by the sea.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-number">05</span><span class="faq-question">How far in advance should I book?</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                    <div class="faq-answer"><p>Availability depends on your dates, route and the places you want to stay. Get in touch as early as you can, especially if your dates are fixed; we'll check availability and talk you through the options.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-number">06</span><span class="faq-question">What should I pack for a safari?</span><span class="faq-chevron" aria-hidden="true"></span></summary>
                    <div class="faq-answer"><p>Comfortable neutral layers, sturdy walking shoes, sun protection and any personal medication are useful starting points. Conditions vary by itinerary, so we'll share a tailored packing list once your route is planned.</p></div>
                </details>
            </div>
            <div class="faq-contact"><span>Still have a question?</span><a class="text-link" href="https://wa.me/255621861958?text=Hello%20Serenovia%2C%20I%20have%20a%20question%20about%20planning%20a%20trip." target="_blank" rel="noopener">Ask our local team <span>↗</span></a></div>
        </section>

        <section class="plan section-wrap" id="plan">
            <div class="plan-mark" aria-hidden="true">S<span>✳</span></div>
            <span class="eyebrow">YOUR TANZANIA STORY STARTS HERE</span>
            <h2>Let's make it <em>unforgettable.</em></h2>
            <p>Tell us what you've been dreaming about.<br>We'll take it from there.</p>
            <a class="button button-dark" href="https://wa.me/255621861958?text=Hello%20Serenovia%2C%20I%27m%20planning%20a%20Tanzania%20safari." target="_blank" rel="noopener">Plan your safari on WhatsApp <span>↗</span></a>
            <span class="plan-note">REAL PEOPLE. LOCAL KNOWLEDGE. A JOURNEY THAT'S YOURS.</span>
        </section>
    </main>

    <footer class="site-footer" id="contact">
        <div class="footer-main section-wrap">
            <div class="footer-brand-col"><a class="brand brand-footer" href="#top"><span class="brand-mark" aria-hidden="true"><span>S</span><i></i></span><span class="brand-copy"><strong>SERENOVIA</strong><small>AFRICA SAFARIS</small></span></a><p>Thoughtfully made journeys<br>through Tanzania and beyond.</p><span class="footer-location">MOSHI, KILIMANJARO · TANZANIA</span></div>
            <div class="footer-links"><span class="footer-heading">EXPLORE</span><a href="#journeys">Our journeys</a><a href="#destinations">Destinations</a><a href="#story">Our story</a></div>
            <div class="footer-links"><span class="footer-heading">SAY HELLO</span><a href="mailto:kizzaadventures07@gmail.com">kizzaadventures07@gmail.com</a><a href="tel:+255621861958">+255 621 861 958</a><a href="https://wa.me/255621861958" target="_blank" rel="noopener">WhatsApp us ↗</a></div>
        </div>
        <div class="footer-bottom section-wrap"><span>© {{ date('Y') }} SERENOVIA AFRICA SAFARIS</span><span>MADE WITH CARE IN TANZANIA <b>✳</b></span><a href="#top">BACK TO TOP ↑</a></div>
    </footer>
    <script src="{{ $publicBasePath }}/js/serenovia.js"></script>
</body>
</html>
