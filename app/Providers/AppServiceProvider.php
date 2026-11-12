<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $settings = collect(config('serenova.settings', []));

        if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            $settings = $settings->merge(SiteSetting::query()->pluck('value', 'key'));
        }

        View::share('siteSettings', $settings);
    }
}
