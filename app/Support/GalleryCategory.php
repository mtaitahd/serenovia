<?php
namespace App\Support;

final class GalleryCategory
{
    public static function group(?string $category): string
    {
        $label = trim($category ?? '');
        $words = preg_split('/[^a-z]+/', strtolower($label), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            if (in_array($word, ['stay','stays','hotel','hotels','lodge','lodges','camp','camps','camping','malazi'], true)
                || (strlen($word) >= 9 && levenshtein($word, 'accommodation') <= 2)
                || $word === 'accommodations') return 'Accommodation';
        }
        foreach ($words as $word) {
            if (in_array($word, ['transport','transportation','vehicle','vehicles','car','cars','jeep','jeeps'], true)) return 'Transport';
        }
        return $label ?: 'Other';
    }
}
