<?php

namespace App\Http\Controllers;

use App\Support\SafariPhotography;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $photos = [
            'lion' => 'Lion in the Serengeti',
            'crater' => 'View across the Ngorongoro Crater',
            'giraffe' => 'Giraffe in the Serengeti',
            'savanna-tree' => 'A tree in the Ngorongoro Crater',
            'elephants' => 'Elephants crossing a dirt road',
            'serengeti-leopard' => 'Leopard resting in a tree',
            'migration' => 'Wildlife running across the plains',
            'zebras' => 'Zebras with mountains in the distance',
            'kilimanjaro' => 'Snow-covered mountain',
            'serengeti-sunset' => 'Sunset in the Serengeti',
            'zanzibar' => 'An island off the coast',
            'beach' => 'A palm-lined beach',
        ];

        $images = collect($photos)->map(fn (string $caption, string $name) => [
            'path' => SafariPhotography::path($name),
            'caption' => $caption,
            'category' => match ($name) { 'kilimanjaro' => 'Kilimanjaro', 'zanzibar', 'beach' => 'Zanzibar', 'crater', 'savanna-tree', 'serengeti-sunset' => 'Landscapes', default => 'Wildlife' },
        ]);

        $uploaded = \App\Models\GalleryImage::orderBy('sort_order')->orderBy('id')->get();
        if ($uploaded->isNotEmpty()) $images = $uploaded->map(fn ($photo) => ['path' => $photo->path, 'caption' => $photo->title ?: $photo->alt_text, 'alt' => $photo->alt_text ?: $photo->title, 'category' => $photo->category ?: 'Other']);

        return view('gallery.index', compact('images'));
    }
}
