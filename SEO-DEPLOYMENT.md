# SEO / GEO deployment and verification

This implementation works with an empty catalogue; it does not seed, rewrite or publish tours. No SEO database migration or AI API is required.

## Implemented behavior

- Canonical tour HTML contains all day, accommodation, inclusion and practical content. Existing tabs select visible panels, support back/forward and retain real server links for visitors without JavaScript.
- Metadata is derived from existing content; optional tour description overrides remain available. Canonical origin comes from APP_URL. Tracking parameters are removed; noncurated filtered results are noindex; out-of-range tour pages return 404.
- Open Graph and Twitter use page-specific images. Tour/article/destination cover choices take precedence over stock fallbacks. Public gallery uses saved gallery images when available, retaining the existing layout.
- Organization, website, page, trip, destination, article and breadcrumb JSON-LD uses existing factual fields. No invented price, stock, review or author. No promise of a TouristTrip rich result or AI citation.
- Sitemap and robots are dynamic. Apache routes these endpoints through Laravel even if old physical copies remain from previous deployments. Important static routes and eligible published records are included; future articles and private records are excluded. Existing static XML is not the runtime source.
- Admin responses are private/no-store and noindex. Published slug changes are rejected to protect existing URLs; deliberate URL migrations require an explicit redirect plan.
- Admin tour Review shows saved search metadata, selected image and content-readiness warnings. The existing editor continues to control content and publishing.
- New uploaded WebP images get 480/960/1440px copies where smaller than the source. Tour hero, itinerary and gallery use srcset plus intrinsic dimensions; originals remain unchanged. Old images continue to work without a bulk data rewrite. Apache enables supported text compression and bounded asset expiry.
- Settings use a short file cache invalidated by normal editor saves/deletes. Public authenticated HTML is not put into a shared cache. Hero video remains autoplay; metadata preload is a hint, not a guaranteed bandwidth reduction.

## Deployment

1. Keep `APP_URL=https://www.serenoviaafrica.com` in the server environment, with production mode and debug disabled. Never commit .env or passwords.
2. Use the existing cPanel deployment workflow. It clears framework caches and installs the public rewrite configuration. `serenovia:seo-check` validates canonical origin and sitemap without changing content.
3. Open /robots.txt and /sitemap.xml externally. Both must be 200, show the production hostname and contain no login/challenge page. Old static files must not be served by a CDN cache: purge these two URLs in Cloudflare after deployment if necessary.
4. Confirm HTTP/non-www redirect to HTTPS www with no loop. Use Cloudflare Full (strict) with valid origin TLS; do not assume origin rewrite rules alone fix Flexible-mode loops.
5. Publish one genuine tour using the editor. Check it appears in /safaris and sitemap immediately. Confirm all days appear in View Source, tabs and browser Back work, share image matches the selected cover and quote flow still works. Withdraw the test draft through the editor if it was only a temporary fixture.
6. Verify Google Search Console domain ownership and Bing Webmaster Tools. Submit /sitemap.xml, inspect the home, listing and a genuine tour URL. Review engine-selected canonical and fetched HTML.
7. Review Cloudflare security events for verified search crawlers. Permit legitimate public crawling with scoped rules; retain admin/auth protection. Do not whitelist a bot merely because of its user-agent.
8. Measure production mobile Lighthouse/PageSpeed and field CWV after access works. Local measurements are not production scores. A large autoplay video may still dominate transfers; compress/transcode the actual live asset after visual approval.

## Issue disposition

| Audit issues | Implementation / remaining verification |
|---|---|
| S01 production crawler access | Local app permits public discovery; live Cloudflare credentials/logs and engine inspections are still required. No firewall changes were made. |
| S02 canonical itinerary | Complete SSR content with existing visual tabs. |
| S03–S04 sitemap | Automatic current public catalogue, static routes, record lastmod, robots advertisement. No cron dependency. |
| S05–S07 metadata/sharing/schema | Shared metadata and entity graph, record-specific images and no fabricated commercial data. |
| S08–S09 filters/private entries | Filter noindex, bounded tour pagination, noindex/private admin headers. |
| S10–S11 sample/thin content | No local sample data altered, as requested. Readiness guidance supports new live content; real copy and operating facts must come from the business. |
| S12 imagery | Saved covers/gallery and meaningful alt text; existing upload WebP optimization retained. |
| S13 URL stability | Published slug edits rejected; no redirect table needed for routine editing. |
| S14 entity identity | Schema uses configured name/contact/profile values. Owner must verify these in Settings; no credentials or awards invented. |
| S15–S16 performance | Settings-cache work and video preload hint; responsive copies for new uploads, intrinsic dimensions and text compression; production profiling and live video transcoding still need production measurements. |
| S17 search intent | Infrastructure supports genuine new route packages. No empty commercial landing pages or fabricated mountain products were created. |
| S18 publication | Shared eligibility used by sitemap and public article routes/home selection; future content withheld. |

## Tests and rollback

Run the SEO and tour regression tests only on the dedicated MySQL database ending in `_testing`; the test bootstrap refuses other databases. Validate JSON-LD syntax and Schema.org properties separately from Google's supported rich-result types.

Keep the previous application release available. Roll back code if public responses, tab behavior, auth or enquiry validation regress. Do not remove privacy guards or modify live package data to make tests pass. Monitor sitemap fetches, crawl errors, index coverage and page experience over subsequent weeks; submission is not an indexing guarantee.

## Local verification — 19 November 2026

The selected regression group covers 30 tests: search metadata, sitemap/publication, empty catalogue, safe JSON, responsive uploads, complete tour editing, homepage editor, itinerary media, generator workflow and travel discovery. The updated itinerary-media test asserts hidden panels rather than absence of canonical content. Its final rerun passed.

Eight public endpoint smoke checks returned 200, and JSON-LD parsed on each HTML page checked. Desktop/mobile tab changes, browser Back and photo lightbox were checked with no captured JavaScript errors. All 30 changed/new PHP files linted at the time of the check. No live deployment was performed. A live sitemap HEAD request still returned Cloudflare 403/challenge; verified-bot access remains an external verification item.
