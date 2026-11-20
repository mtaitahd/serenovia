<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class GalleryController extends Controller
{
    public function index(): View
    {
        $images = GalleryImage::orderBy('category')->orderBy('sort_order')->latest()->paginate(30);

        $images->getCollection()->each(function (GalleryImage $image): void {
            $sourcePath = $image->source_path ?: PublicImage::sourceFor($image->path);
            $image->setAttribute('source_bytes', is_file(public_path($sourcePath)) ? filesize(public_path($sourcePath)) : null);
            $image->setAttribute('display_bytes', is_file(public_path($image->path)) ? filesize(public_path($image->path)) : null);
        });

        $categories = collect(['Safari', 'Wildlife', 'Culture', 'Kilimanjaro', 'Zanzibar', 'Transport', 'Accommodation', 'Other'])
            ->merge(GalleryImage::query()->distinct()->pluck('category'))->filter()->unique()->sort()->values();
        return view('admin.gallery.index', compact('images', 'categories'));
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->prepareCategory($request);
        $data = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['bail', 'required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192', new \App\Rules\GalleryImageDimensions()],
            'category' => ['required', 'string', 'max:80'],
            'quality' => ['nullable', 'integer', 'min:55', 'max:95'],
        ], [
            'images.*.image' => 'Photo :position must be a valid image.',
            'images.*.mimes' => 'Photo :position must be a JPG, PNG or WebP file.',
            'images.*.max' => 'Photo :position is too large. Each photo must be 8 MB or less.',
        ]);

        $created = [];
        foreach ($data['images'] as $image) {
            $label = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $path = PublicImage::store($image, 'gallery', (int) ($data['quality'] ?? PublicImage::DEFAULT_QUALITY));
            $sourcePath = PublicImage::sourceFor($path);
            $created[] = GalleryImage::create([
                'path' => $path,
                'source_path' => $sourcePath,
                'original_size' => filesize(public_path($sourcePath)),
                'optimized_size' => filesize(public_path($path)),
                'title' => $label,
                'alt_text' => $label,
                'category' => $data['category'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($created).' image(s) uploaded and optimized.',
                'uploaded' => count($created),
            ]);
        }

        return back()->with('success', count($created).' image(s) added and optimized.');
    }

    public function update(Request $request, GalleryImage $image): RedirectResponse
    {
        $this->prepareCategory($request);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);
        $image->update($data);

        return back()->with('success', 'Gallery image details updated.');
    }

    private function prepareCategory(Request $request): void
    {
        if ($request->input('category') === '__new__') {
            $request->validate(['new_category' => ['required', 'string', 'max:80']]);
            $request->merge(['category' => trim($request->input('new_category'))]);
        }
    }

    public function destroy(GalleryImage $image): RedirectResponse
    {
        PublicImage::delete($image->path, $image->source_path);
        $image->delete();

        return back()->with('success', 'Image removed from the gallery.');
    }

    public function compress(Request $request, GalleryImage $image): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:quality,filesize'],
            'quality_value' => ['required_if:mode,quality', 'nullable', 'integer', 'min:25', 'max:95'],
            'filesize_value' => ['required_if:mode,filesize', 'nullable', 'integer', 'min:10', 'max:10240'],
        ]);

        $value = (int) ($data['mode'] === 'quality' ? $data['quality_value'] : $data['filesize_value']);
        try {
            $result = PublicImage::compress($image->path, $image->source_path, $data['mode'], $value);
        } catch (Throwable $exception) {
            return back()->withErrors(['compression' => $exception->getMessage()])->withInput();
        }

        $image->update([
            'path' => $result['path'],
            'source_path' => $result['source_path'],
            'original_size' => $result['original_size'],
            'optimized_size' => $result['optimized_size'],
        ]);

        $saved = $result['saved_bytes'];
        $message = $saved > 0
            ? 'Image optimized: '.number_format($saved / 1024, 1).' KB saved in this compression. The original remains unchanged.'
            : 'Image checked: the current display copy is already smaller; the original remains safe.';

        $message .= ' Display size: '.number_format($result['before_size'] / 1024, 1).' KB → '.number_format($result['optimized_size'] / 1024, 1).' KB.';
        if (!$result['target_met']) $message .= ' The requested target could not be reached within the quality limit.';
        return back()->with('success', $message);
    }
}
