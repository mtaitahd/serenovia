<?php

namespace App\Http\Controllers;

use App\Models\{Article, Destination, TourPackage};
use App\Support\SeoMetadata;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function entries(): array
    {
        $entries = array_map(fn ($path) => ['loc' => SeoMetadata::url($path)], [
            '', 'safaris', 'destinations', 'journal', 'accommodations', 'gallery', 'about-us', 'responsible-travel', 'travel-guide', 'contact',
        ]);
        foreach ([TourPackage::class => 'safaris', Destination::class => 'destinations', Article::class => 'journal'] as $model => $prefix) {
            foreach ($model::publiclyVisible()->select(['slug', 'updated_at'])->cursor() as $record) {
                $entry = ['loc' => SeoMetadata::url($prefix.'/'.rawurlencode($record->slug))];
                if ($record->updated_at) $entry['lastmod'] = $record->updated_at->toAtomString();
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    public function __invoke(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($this->entries() as $entry) {
            $xml .= '<url><loc>'.htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8').'</loc>';
            if (isset($entry['lastmod'])) $xml .= '<lastmod>'.$entry['lastmod'].'</lastmod>';
            $xml .= '</url>';
        }
        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'no-cache']);
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow:\n\nSitemap: ".SeoMetadata::url('sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
