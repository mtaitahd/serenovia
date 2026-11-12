<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = collect(config('serenova.settings', []))->merge(SiteSetting::query()->pluck('value', 'key'));

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'hero_title' => ['required', 'string', 'max:180'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:60'],
            'whatsapp' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:240'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
        ]);

        $heroImage = $data['hero_image'] ?? null;
        unset($data['hero_image']);
        if ($heroImage) {
            $current = SiteSetting::get('hero_image');
            PublicImage::delete($current);
            $data['hero_image'] = PublicImage::store($heroImage, 'site');
        }

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Website settings saved.');
    }
}
