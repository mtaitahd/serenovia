<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use App\Support\HomepageContent;
use App\Support\PublicImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomepageEditorController extends Controller
{
    public function edit(Request $request)
    {
        $sections = HomepageContent::sections();
        $section = (string) $request->query('section', 'hero');
        abort_unless(isset($sections[$section]), 404);
        $settings = collect(config('serenova.settings', []))->merge(SiteSetting::pluck('value', 'key'));
        $definition = $sections[$section];
        $shown = isset($definition['collection']) ? HomepageContent::records($section, $settings) : collect();
        $available = isset($definition['collection']) ? HomepageContent::query($definition['collection'])->get() : collect();
        $available = $shown->concat($available->whereNotIn('id', $shown->pluck('id')))->values();
        return view('admin.website-content.homepage', [
            'sections' => $sections, 'section' => $section, 'definition' => $definition,
            'home' => HomepageContent::values($settings), 'shownRecords' => $shown, 'availableRecords' => $available,
            'manualSelection' => is_array(json_decode($settings->get('home_'.$section.'_ids', 'null'), true)),
            'galleryImages' => GalleryImage::orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function update(Request $request)
    {
        $sections = HomepageContent::sections();
        $request->validate(['section' => ['required', Rule::in(array_keys($sections))]]);
        $section = $request->string('section')->toString();
        $definition = $sections[$section];
        $rules = [];
        $safeLink = function ($attribute, $value, $fail) {
            if ($value === null || $value === '') return;
            if (preg_match('/[\x00-\x20\\\\]/', $value) ||
                ! (preg_match('#^https?://[^/]+#i', $value) || (str_starts_with($value, '/') && ! str_starts_with($value, '//')) || str_starts_with($value, '#'))) {
                $fail('Use a website path such as /safaris or a complete https:// URL.');
            }
        };
        foreach ($definition['fields'] as $key => $field) {
            if (in_array($field['type'], ['image', 'video'], true)) {
                $rules['uploads.'.$key] = $field['type'] === 'video'
                    ? ['nullable', 'file', 'mimetypes:video/mp4,video/webm,application/mp4', 'max:51200']
                    : ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'extensions:jpeg,jpg,png,webp', 'max:8192'];
                $rules['remove.'.$key] = ['nullable', 'boolean'];
                if ($field['type'] === 'image') $rules['gallery.'.$key] = ['nullable', 'integer', 'exists:gallery_images,id'];
            } else {
                $rules['content.'.$key] = match ($field['type']) {
                    'toggle' => ['required', 'boolean'],
                    'url' => ['nullable', 'url:http,https', 'max:1000'],
                    'link' => ['required', 'string', 'max:1000', $safeLink],
                    'title' => ['required', 'string', 'max:255'],
                    'textarea' => ['nullable', 'string', 'max:5000'],
                    default => ['nullable', 'string', 'max:500'],
                };
            }
        }
        if (isset($definition['collection'])) {
            $rules['selection_mode'] = ['required', Rule::in(['automatic', 'manual'])];
            $rules['record_ids'] = ['nullable', 'array', 'max:'.$definition['limit']];
            $rules['record_ids.*'] = ['integer', 'distinct', Rule::in(HomepageContent::query($definition['collection'])->pluck('id')->all())];
        }
        $data = $request->validate($rules);
        $values = $data['content'] ?? [];
        $createdImages = [];
        $createdVideos = [];
        try {
            foreach ($definition['fields'] as $key => $field) {
                if (! in_array($field['type'], ['image', 'video'], true)) continue;
                if ($request->hasFile('uploads.'.$key)) {
                    $file = $request->file('uploads.'.$key);
                    if ($field['type'] === 'image') {
                        $values[$key] = PublicImage::store($file, 'site/homepage');
                        $createdImages[] = $values[$key];
                    } else {
                        $extension = $file->getMimeType() === 'video/webm' ? 'webm' : 'mp4';
                        $name = Str::uuid().'.'.$extension;
                        $file->move(public_path('uploads/site/homepage/videos'), $name);
                        $values[$key] = 'uploads/site/homepage/videos/'.$name;
                        $createdVideos[] = $values[$key];
                    }
                } elseif (! empty($data['gallery'][$key])) {
                    $values[$key] = PublicImage::copy(GalleryImage::findOrFail($data['gallery'][$key])->path, 'site/homepage');
                    $createdImages[] = $values[$key];
                } elseif (! empty($data['remove'][$key])) {
                    $values[$key] = '';
                }
            }
            DB::transaction(function () use ($values, $definition, $data, $section) {
                foreach ($values as $key => $value) {
                    SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) ($value ?? '')]);
                }
                if (isset($definition['collection'])) {
                    SiteSetting::updateOrCreate(['key' => 'home_'.$section.'_ids'], ['value' => $data['selection_mode'] === 'manual' ? json_encode(array_map('intval', $data['record_ids'] ?? [])) : 'null']);
                }
            });
        } catch (\Throwable $error) {
            foreach ($createdImages as $path) PublicImage::delete($path);
            foreach ($createdVideos as $path) @unlink(public_path($path));
            throw $error;
        }
        return redirect()->route('admin.website-content.homepage', ['section' => $section] + ($request->boolean('popup') ? ['popup' => 1] : []))
            ->with('success', $definition['label'].' saved. The homepage now uses this content.');
    }
}
