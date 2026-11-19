<?php
namespace App\Support;

final class ResponsiveImage
{
    public static function generate(string $path): void
    {
        if (!preg_match('#^uploads/[a-zA-Z0-9/_-]+\.webp$#', $path)) return;
        $source = @imagecreatefromwebp(public_path($path));
        if (!$source) return;
        try {
            foreach ([480, 960, 1440] as $width) {
                $variantPath = public_path(self::variant($path, $width));
                if (imagesx($source) <= $width) { \Illuminate\Support\Facades\File::delete($variantPath); continue; }
                $height = max(1, (int) round(imagesy($source) * $width / imagesx($source)));
                $image = imagecreatetruecolor($width, $height);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
                imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
                $written = imagewebp($image, $variantPath, 84);
                clearstatcache(true, $variantPath);
                // A responsive file should never cost more bandwidth than the main image.
                if (!$written || !is_file($variantPath) || filesize($variantPath) >= filesize(public_path($path))) {
                    \Illuminate\Support\Facades\File::delete($variantPath);
                }
                imagedestroy($image);
            }
        } finally {
            imagedestroy($source);
        }
    }

    private static function variant(string $path, int $width): string
    {
        return substr($path, 0, -5).'-'.$width.'w.webp';
    }

    public static function attributes(?string $path, string $sizes = '(max-width: 600px) 100vw, 50vw'): \Illuminate\Support\HtmlString
    {
        if (!$path || !preg_match('#^(uploads|images)/[a-zA-Z0-9/_.-]+$#', $path) || str_contains($path, '..')) return new \Illuminate\Support\HtmlString('');
        $info = @getimagesize(public_path($path));
        if (!$info) return new \Illuminate\Support\HtmlString('');
        $attrs = 'width="'.(int) $info[0].'" height="'.(int) $info[1].'"';
        $sources = [];
        if (str_ends_with($path, '.webp')) {
            foreach ([480, 960, 1440] as $width) {
                $variant = self::variant($path, $width);
                if ($width < $info[0] && is_file(public_path($variant))) $sources[] = asset($variant).' '.$width.'w';
            }
        }
        if ($sources) {
            $sources[] = asset($path).' '.$info[0].'w';
            $attrs .= ' srcset="'.e(implode(', ', $sources)).'" sizes="'.e($sizes).'"';
        }
        return new \Illuminate\Support\HtmlString($attrs);
    }

    public static function delete(string $path): void
    {
        if (!preg_match('#^uploads/[a-zA-Z0-9/_-]+\.webp$#', $path)) return;
        foreach ([480, 960, 1440] as $width) \Illuminate\Support\Facades\File::delete(public_path(self::variant($path, $width)));
    }
}
