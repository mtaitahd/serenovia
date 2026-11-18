# Automatic Package Generator

Open **Admin → Automatic Package Generator**. Upload a document or select a published tour under **Review existing itinerary**. Check each day, map catalogue IDs, and approve the verified rows. A transfer needs a verified transport mode. A night needs a published accommodation at the stated overnight stop.

For Excel, enter one row per day in **Day-by-Day**. Use the same Tour Ref on consecutive days for a complete tour and enter its package details in **Tours**. Leave Tour Ref blank for a reusable day block. **Accommodations** lists published properties and their IDs. Word uses one repeatable field table per day. CSV accepts the Day-by-Day column names. The sample rows with an EXAMPLE reference are excluded. For a searchable PDF, use headings such as Day 1: Arrival, then fill and verify the extracted data in the review screen.

Choose **Existing database**, **Approved documents**, or the combined source, choose the criteria, then generate. Review the results and use **Preview** and **Edit / Publish**. The generator leaves the rate open for an admin to verify in Edit Tour. Run history shows the number checked, rejected, created, and the elapsed time.

## Installation

1. Ensure the deployed PHP 8.2+ web runtime has ZIP, SimpleXML, XML and GD enabled. The module adds no Composer package.
2. Install local Poppler for PDF uploads. If pdftotext is not on the web runtime PATH, set TOUR_GENERATOR_PDFTOTEXT to its absolute path. PHP must permit process execution for PDF extraction. Searchable PDF extraction is limited to 100 pages.
3. Run `composer install --no-dev --optimize-autoloader` and `php artisan migrate --force` through the host's deployment mechanism. Refresh any Laravel config and route caches used by the deployment.
4. Verify that PHP allows a 5 MB upload, including enough POST overhead, and that uploaded media and temporary directories are writable. The document is parsed from PHP's private upload temporary file. The module retains its hash, source references and staged records.
5. Open the admin generator, download both templates, and test an approved import and draft preview on staging before publishing.

Generation runs in the web request, with an 18-second search budget and up to 100 requested drafts. It requires no persistent queue worker. The local test command is `C:/xampp/php/php.exe -d extension=zip vendor/bin/phpunit`. The runtime prerequisite for PDF has to be tested on the target host separately.
