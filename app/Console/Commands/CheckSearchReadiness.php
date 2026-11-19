<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SitemapController;

class CheckSearchReadiness extends Command
{
    protected $signature = 'serenovia:seo-check';
    protected $description = 'Read-only checks of canonical origin and public sitemap eligibility';

    public function handle(): int
    {
        $url = config('app.url');
        $host = parse_url($url, PHP_URL_HOST);
        if (!filter_var($url, FILTER_VALIDATE_URL) || (app()->environment('production') && (parse_url($url, PHP_URL_SCHEME) !== 'https' || in_array($host, ['localhost', '127.0.0.1'])))) {
            $this->error('Set APP_URL to the public HTTPS canonical address before deployment.');
            return self::FAILURE;
        }
        $entries = app(SitemapController::class)->entries();
        $xml = app(SitemapController::class)()->getContent();
        if (simplexml_load_string($xml) === false || count(array_unique(array_column($entries, 'loc'))) !== count($entries)) {
            $this->error('Sitemap XML is invalid or contains duplicate addresses.');
            return self::FAILURE;
        }
        $this->info('Canonical origin: '.$url);
        $this->info('Automatic sitemap: '.count($entries).' public URLs. No manual generation needed.');
        $this->line('External checks still required: Cloudflare verified-bot access, HTTPS redirects, Search Console and Bing live inspection.');
        return self::SUCCESS;
    }
}
