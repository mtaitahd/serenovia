<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class SitemapController extends Controller
{
    public function index(): View
    {
        return view('admin.sitemap', [
            'sitemapPath' => public_path('sitemap.xml'),
            'sitemapUrl' => URL::to('/sitemap.xml'),
            'generatedAt' => is_file(public_path('sitemap.xml')) ? date('M j, Y H:i', filemtime(public_path('sitemap.xml'))) : null,
            'urlCount' => 1 + TourPackage::where('status', 'published')->count() + Destination::where('status', 'published')->count() + Article::where('status', 'published')->count(),
        ]);
    }

    public function generate(): RedirectResponse
    {
        $urls = collect([URL::to('/')])
            ->merge(TourPackage::where('status', 'published')->pluck('slug')->map(fn ($slug) => URL::to('/safaris/'.$slug)))
            ->merge(Destination::where('status', 'published')->pluck('slug')->map(fn ($slug) => URL::to('/destinations/'.$slug)))
            ->merge(Article::where('status', 'published')->pluck('slug')->map(fn ($slug) => URL::to('/journal/'.$slug)))
            ->unique()->values();

        $entries = $urls->map(fn ($url) => '<url><loc>'.e($url).'</loc></url>')->implode('');
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$entries.'</urlset>';
        $written = file_put_contents(public_path('sitemap.xml'), $xml, LOCK_EX);
        abort_if($written === false, 500, 'Could not write the sitemap file.');

        return back()->with('success', 'Sitemap generated successfully.');
    }
}
