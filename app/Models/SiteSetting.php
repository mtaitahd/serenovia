<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    private static function settingsCache()
    {
        return \Illuminate\Support\Facades\Cache::store(app()->environment('testing') ? 'array' : 'file');
    }

    private static function settingsCacheKey(): string
    {
        return 'public-settings-'.sha1(config('app.url').'|'.config('database.default').'|'.config('database.connections.'.config('database.default').'.database'));
    }

    public static function publicValues(): array
    {
        return static::settingsCache()->remember(static::settingsCacheKey(), 300, function () {
            return \Illuminate\Support\Facades\Schema::hasTable('site_settings') ? static::query()->pluck('value', 'key')->all() : [];
        });
    }

    protected static function booted(): void
    {
        $forget = fn () => static::settingsCache()->forget(static::settingsCacheKey());
        static::saved($forget);
        static::deleted($forget);
    }

    public static function get(string $key, ?string $fallback = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $fallback;
    }
}
