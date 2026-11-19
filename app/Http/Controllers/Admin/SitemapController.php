<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Support\SeoMetadata;
class SitemapController extends Controller
{
    public function index()
    {
        return view('admin.sitemap', ['sitemapUrl' => SeoMetadata::url('sitemap.xml'), 'urlCount' => count(app(\App\Http\Controllers\SitemapController::class)->entries())]);
    }
    public function generate()
    {
        return back()->with('success', 'The sitemap is automatic and reflects your current published content.');
    }
}
