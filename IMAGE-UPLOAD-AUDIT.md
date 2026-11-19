# Image upload and compression inspection — 19 November 2026

## Scope
Inspected the shared image pipeline used by gallery uploads, tour covers, itinerary days, accommodation uploads, destinations, articles, homepage and page heroes. Gallery-selected transport/stay photos reuse gallery WebP files. Video uploads are separate and are not converted to WebP.

## Measured results (local PHP 8.2 / GD with WebP and EXIF)
- Repository safari JPEG: 118,560 bytes -> valid WebP 67,708 bytes at default quality 84 (42.9% smaller).
- High-quality display WebP: 134,816 bytes -> 27,646 bytes at quality 35 (79.5% smaller). Quality 35 is a test of compression strength, not the default recommendation.
- Original SHA-256 checksums remain identical after upload and repeated compression.
- PNG transparency retained; 2600px image resized to 2400px; small images not enlarged.
- Phone JPEG orientation 6 is applied before encoding.
- JPEG/PNG/WebP files are decoded and written as actual WebP, not merely renamed.

## Corrections
- Compression reports bytes saved against the previous display copy, not repeatedly against the original.
- Shows before/after KB and explicitly reports when a requested KB target cannot be reached at the minimum quality.
- Refreshes PHP file-size metadata after replacing the output.
- Already optimized WebP uploads are kept if re-encoding would increase their size.
- Responsive variants (480/960/1440px) are discarded if larger than the main file; obsolete variants are removed.
- Uses detected image format for original extensions instead of trusting client filenames.
- Checks GD WebP capability and rejects inputs above 40 megapixels before decoding.
- Applies supported JPEG EXIF rotation/mirroring; servers without EXIF cannot automatically correct orientation.
- Fixes the gallery Done button to prevent a duplicate upload request.
- Deployment verifies GD WebP encoding and decoding support, rather than GD presence alone.

## Verification
10 isolated image tests cover encoding, real byte reduction, source preservation, no-growth recompression, target limits, transparency, orientation, filename safety, image copying/deletion and variant cleanup.
Browser simulation checks that a successful upload followed by Done sends exactly one upload request.
Admin HTTP upload/compression and itinerary regression tests: 7 passed, 69 assertions. Image unit tests: 10 passed, 34 assertions. Total: 17 tests and 103 assertions passed.

## Practical limits
WebP is lossy at the configured quality: zero pixel/color difference is not promised. Very small or already optimized JPEG/PNG inputs may not shrink. Compression is bounded by quality 25, so not every requested size is achievable. Original files plus responsive copies consume additional disk space; web transfer savings are not total disk savings.
Existing public image URLs can remain cached for up to the configured seven days after recompression. A fresh uncached request receives the current file; CDN caches may require purging for immediate verification.
No existing customer images were batch-recompressed. Tests use isolated temporary upload folders and the dedicated MySQL testing database. Production hosting and real customer uploads were not accessed during this inspection; deploy and verify the live PHP environment separately.
