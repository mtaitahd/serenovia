<?php
namespace App\Rules;

use App\Support\PublicImage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class GalleryImageDimensions implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) return;
        $size = @getimagesize($value->getRealPath());
        $number = ((int) substr(strrchr($attribute, '.'), 1)) + 1;
        if (!$size || $size[0] < 1 || $size[1] < 1) {
            $fail("Photo {$number} could not be read. Please upload a valid JPG, PNG or WebP image.");
        } elseif ($size[0] * $size[1] > PublicImage::MAX_PIXELS) {
            $fail("Photo {$number} is {$size[0]} × {$size[1]} pixels. Resize it to 40 megapixels or less before uploading.");
        }
    }
}
