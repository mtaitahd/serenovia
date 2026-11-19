<?php
namespace App\Models\Concerns;

use Illuminate\Validation\ValidationException;

trait StablePublicSlug
{
    protected static function bootStablePublicSlug(): void
    {
        static::updating(function ($record) {
            if ($record->isDirty('slug') && $record->getOriginal('status') === 'published') {
                throw ValidationException::withMessages(['slug' => 'Keep the URL of a published page unchanged so existing search results and shared links continue to work.']);
            }
        });
    }
}
