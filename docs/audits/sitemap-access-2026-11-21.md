# Sitemap access audit — 21 November 2026

## A. Root cause and scope

A live GET to https://www.serenoviaafrica.com/sitemap.xml with the representative Googlebot User-Agent returned **403**, `Server: cloudflare`, `cf-mitigated: challenge`, `Content-Type: text/html; charset=UTF-8`, and a “Just a moment” challenge page. Ray ID: **a5e02ee3ada097c3-DAR**. HEAD was also challenged (Ray **a5e02ef18bc1c8aa-DAR**). This identifies the source of these reproduced responses as the Cloudflare challenge layer, not the XML generator. It does NOT prove why Google's real sitemap fetch was denied: the diagnostic client is not a verified Google IP. Bluehost must correlate actual Google requests with edge logs.

Browser and representative Inspection Tool requests returned 200 and valid XML with 21 URLs. All 21 live URLs separately returned 200, matching HTTPS www canonical tags and index/follow directives. No-user-agent requests to homepage/robots/sitemap returned 403. Browser HTTP requests to both hosts were challenged before any redirect; HTTPS non-www returned 301 to HTTPS www then 200. Homepage Googlebot request also received a challenge (Ray a5e02eec2ed716b1-DAR).

No application-level 403 was reproduced for public discovery endpoints. No public bot/IP filter was found. Application 403 checks are the admin-only middleware and prevention of re-running admin setup. Throttles apply to login/setup/inquiry/import submissions, not sitemap or robots. Missing/private content returns 404.

Confirmed separate robustness issue: discovery endpoints inherited sessions/cookies from the web middleware group. Live 200 responses set cookies; local responses no longer do after the scoped change. This removes unnecessary session-storage dependence; it is **not a fix for the upstream challenge**.

### Code inspected

Routes, bootstrap, HTTP controllers/middleware, SitemapController, SeoMetadata, model visibility scopes, AppServiceProvider/SiteSetting, session/auth configuration, public and root .htaccess, both entry points, deployment script and existing SEO tests. No routes/api.php is involved. Sitemap generation uses three cursor queries selecting slug/updated_at for published tours, destinations and journal entries, excluding scheduled tours/articles. Static pages are included. lastmod is the stored updated_at, not fetch time. Accommodations have a listing route, no individual public detail route to include. XML escapes locations and has a UTF-8 declaration. Memory/output still grows with catalogue size; no large-catalogue load test was performed.

### Middleware comparison

Before: homepage, safaris, tour detail, sitemap and robots all inherited EncryptCookies, AddQueuedCookiesToResponse, StartSession, ShareErrorsFromSession, ValidateCsrfToken, SubstituteBindings, SearchVisibility.

After: sitemap/robots retain SubstituteBindings and SearchVisibility, removing only the five stateful/form middleware classes. Other pages and admin are unchanged. Admin retains Authenticate and AdminOnly. Global middleware remains ValidatePathEncoding, InvokeDeferredCallbacks, TrustProxies, HandleCors, PreventRequestsDuringMaintenance, ValidatePostSize, TrimStrings and ConvertEmptyStringsToNull. Maintenance mode deliberately still applies. No broad trusted-proxy or User-Agent exception was added.

### Apache and origin limitations

public/.htaccess already canonicalizes bare production host and HTTP www, routes discovery documents to index.php even when static robots.txt exists, and uses the normal front controller for non-files. The explicit discovery rewrite is necessary to prevent the static robots file shadowing the dynamic response; internal rewriting terminates on index.php. No deny rule or discovery rewrite loop was found. No explicit DirectoryIndex override exists; working homepage requests show the deployed entry point is reached for allowed traffic. Production entry point points outside public_html into serenovia. The project-root RewriteBase /serenova rule is for local hosting and must not replace public_html/.htaccess.

No proxy TLS change is justified by current evidence: allowed HTTPS traffic succeeds without a loop. The origin's actual forwarded headers and provider configuration were not available. Remote Apache/WAF logs and actual deployed files were not directly inspected; the owner reports no matching sitemap 403 in Apache logs. No production security settings were changed.

## B. Changes

- routes/web.php: stateless middleware exclusion for sitemap.xml and robots.txt only.
- tests/Feature/SitemapAccessTest.php: browser/Googlebot/Inspection/no-UA and distinct test IPs; anonymous GET/HEAD; XML and cookies; deliberately invalid session driver; dynamic visibility, canonical/public pages, bounded queries, lastmod, deletion, 404 and protected-admin checks.
- This audit report records evidence, limits and deployment instructions.

No schema, credentials, sitemap generator, content, structured data, .htaccess or entry-point changes.

## C. Verification

- PHP lint: routes/web.php and SitemapAccessTest.php passed.
- Real local Apache HTTP checks: **16/16 passed** (4 UA variants x 2 discovery endpoints x GET/HEAD). All 200; no cookies; XML GET parses correctly, 23 local URLs. This uses existing local data without modifying it.
- Live browser: sitemap 200, valid XML, 21 URLs; all 21 content pages 200 with matching canonical and index directives.
- Live robots browser/Googlebot/Inspection: 200. No-UA: 403.
- Live sitemap Googlebot GET/HEAD and no-UA: 403; browser/Inspection: 200.
- HTTPS bare-host redirect: 301 -> canonical 200. HTTP hosts: 403 challenge before redirect, so origin HTTP redirect cannot be validated externally.
- PHPUnit feature execution **not completed**: processes ended before results and local phpunit.xml and PublicSeoTest.php disappeared, with Windows refusing restoration. Exact HEAD copies are preserved as phpunit.xml.restore and tests/Feature/PublicSeoTest.php.restore. The owner subsequently supplied an Avast screenshot confirming phpunit-audit.xml was quarantined as IDP.Generic. The other missing files have not been individually confirmed in quarantine. This is a local test-execution blocker, unrelated to the live Cloudflare challenge; no antivirus protection was disabled and no exclusion was added. Do not count these tests as passed. Restore those two original files after resolving the local lock/quarantine, then run `php vendor/phpunit/phpunit/phpunit --filter 'SitemapAccessTest|PublicSeoTest'`. Only the dedicated MySQL database ending `_testing` is allowed by TestCase. No production data is used.

Full feature regression, invalid-session test, query-count assertion and authenticated non-admin assertion remain unverified until PHPUnit can complete. Production crawler access remains unresolved.

## D. Deployment and rollback

1. In cPanel Git: Update from Remote, then Deploy HEAD Commit. Production deployment was NOT performed during this audit.
2. The only runtime change is routes/web.php. Existing deploy script already runs `php artisan optimize:clear` (including route/config caches). If applying manually, run it from /home2/mpmrejmy/serenovia with the hosting PHP binary; use cPanel deployment if no Terminal.
3. Keep APP_URL=https://www.serenoviaafrica.com in the existing production environment. Do not upload the development .env or change database credentials.
4. public_html/.htaccess and index.php need NO new edits. Existing deployment copies their unchanged templates. No permission changes are necessary; do not use 777.
5. Open sitemap.xml and robots.txt anonymously. Expect 200, correct XML/text, no application session cookies and canonical sitemap URL. An edge security cookie may remain provider-controlled and is distinct from Laravel session/XSRF cookies.
6. Roll back by reverting this audit commit in Git and deploying again; clear caches. There is no database rollback. Preserve the previous HEAD 6bc9424 as the pre-change reference.

### Targeted Bluehost escalation

Please escalate serenoviaafrica.com to the team managing the Cloudflare/CDN/WAF layer. We manage the domain through Bluehost, not a personal Cloudflare zone. On 21 November 2026, GET /sitemap.xml using a representative Googlebot UA returned 403 with cf-mitigated: challenge, HTML challenge content and Ray a5e02ee3ada097c3-DAR; HEAD Ray a5e02ef18bc1c8aa-DAR. Browser GET returned valid 200 XML with 21 URLs, and the application does not filter crawlers. These probes are not verified Google requests. Please correlate the actual Search Console sitemap fetch by time and verified source IP with edge and origin logs, identify the exact rule/product and arrange a narrowly scoped exception for legitimate Google crawlers/Inspection and public GET/HEAD /sitemap.xml and /robots.txt. Preserve admin and other security protection. Do not allow requests solely because they claim a Google User-Agent. Please also investigate the HTTP-host challenge preceding canonical redirects. Confirm the rule change and an actual successful Google fetch with HTTP 200 XML.

## E. Google Search Console

After deployment and the provider correction, use the HTTPS www property. Submit `sitemap.xml` in Sitemaps; inspect its new last-read/status rather than an old cached failure. Expect Success and discovered URLs reflecting the published catalogue (21 at audit time). Use homepage and a published tour Live URL Inspection as additional checks; homepage success alone does not prove sitemap crawler access. Have Bluehost correlate verified Google requests returning 200 with XML, without challenges. Indexing is a separate decision and is not guaranteed by sitemap success.

Sources:
- Cloudflare challenge header identification: https://developers.cloudflare.com/cloudflare-challenges/challenge-types/challenge-pages/detect-response/
- Google source-IP / forward-and-reverse DNS verification: https://developers.google.com/crawling/docs/crawlers-fetchers/verify-google-requests
