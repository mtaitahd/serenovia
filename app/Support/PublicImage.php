<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Keeps an untouched source file and serves a color-faithful, resized WebP copy.
 * PNG transparency is retained; no saturation, grayscale, or color filters are used.
 */
class PublicImage
{
    public const DEFAULT_QUALITY = 84;
    public const MIN_QUALITY = 25;
    public const MAX_QUALITY = 95;
    public const MAX_EDGE = 2400;

    public static function store(UploadedFile $image, string $folder, int $quality = self::DEFAULT_QUALITY): string
    {
        $folder = trim($folder, '/');
        $id = (string) Str::uuid();
        // Laravel validates uploaded files by their MIME type before calling this helper.
        // Prefer the already-known client extension here so local Windows/XAMPP file paths
        // don't need a second filesystem MIME probe while being copied into the library.
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');
        $sourceRelative = "uploads/{$folder}/originals/{$id}.{$extension}";
        $outputRelative = "uploads/{$folder}/{$id}.webp";
        $source = public_path($sourceRelative);

        File::ensureDirectoryExists(dirname($source));
        File::ensureDirectoryExists(dirname(public_path($outputRelative)));

        if (! File::copy($image->getRealPath(), $source)) {
            throw new RuntimeException('The original image could not be saved.');
        }

        try {
            self::encode($source, public_path($outputRelative), $quality);
        } catch (\Throwable $exception) {
            File::delete($source);
            throw $exception;
        }

        return $outputRelative;
    }

    /** Duplicate a gallery image and its untouched source into another upload folder. */
    public static function copy(string $path, string $folder): string
    {
        $sourceRelative = self::sourceFor($path);
        $id = (string) Str::uuid();
        $extension = strtolower(pathinfo($sourceRelative, PATHINFO_EXTENSION) ?: 'webp');
        $newSource = "uploads/".trim($folder, '/')."/originals/{$id}.{$extension}";
        $newOutput = "uploads/".trim($folder, '/')."/{$id}.webp";

        File::ensureDirectoryExists(dirname(public_path($newSource)));
        File::ensureDirectoryExists(dirname(public_path($newOutput)));

        if (! File::copy(public_path($sourceRelative), public_path($newSource))) {
            throw new RuntimeException('The selected gallery image is no longer available.');
        }

        try {
            self::encode(public_path($newSource), public_path($newOutput), self::DEFAULT_QUALITY);
        } catch (\Throwable $exception) {
            File::delete(public_path($newSource));
            throw $exception;
        }

        return $newOutput;
    }

    /** Re-compress an image from its original source, leaving that source untouched. */
    public static function compress(string $path, ?string $sourcePath, string $mode, int $value): array
    {
        $sourceRelative = self::sourceFor($path, $sourcePath);
        if ($sourceRelative === $path) {
            $sourceRelative = self::preserveOriginal($path);
        }

        $quality = max(self::MIN_QUALITY, min(self::MAX_QUALITY, $value));
        $outputRelative = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp'
            ? $path
            : preg_replace('/\.[^.]+$/', '.webp', $path);
        $outputPath = public_path($outputRelative);
        File::ensureDirectoryExists(dirname($outputPath));

        $before = is_file(public_path($path)) ? filesize(public_path($path)) : 0;
        $sourceSize = filesize(public_path($sourceRelative)) ?: $before;
        $workingPath = $outputPath.'.'.Str::random(8).'.working';
        $result = self::encode(
            public_path($sourceRelative),
            $workingPath,
            $quality,
            $mode === 'filesize' ? $value : null,
        );

        // Keep the existing display file if a quality pass cannot make it smaller.
        if ($outputRelative === $path && $result['size'] >= $before) {
            File::delete($workingPath);
            return [
                'path' => $path,
                'source_path' => $sourceRelative,
                'original_size' => $sourceSize,
                'optimized_size' => $before,
                'saved_bytes' => 0,
                'quality' => $quality,
            ];
        }

        if (! File::copy($workingPath, $outputPath)) {
            File::delete($workingPath);
            throw new RuntimeException('The optimized image could not replace the display copy.');
        }
        File::delete($workingPath);

        if ($outputRelative !== $path && is_file(public_path($path))) {
            File::delete(public_path($path));
        }

        return [
            'path' => $outputRelative,
            'source_path' => $sourceRelative,
            'original_size' => $sourceSize,
            'optimized_size' => $result['size'],
            'saved_bytes' => max(0, $sourceSize - $result['size']),
            'quality' => $result['quality'],
        ];
    }

    public static function sourceFor(string $path, ?string $sourcePath = null): string
    {
        if ($sourcePath && is_file(public_path($sourcePath))) {
            return $sourcePath;
        }

        $relative = self::safeUploadPath($path);
        $folder = dirname($relative);
        $basename = pathinfo($relative, PATHINFO_FILENAME);
        $matches = glob(public_path($folder.'/originals/'.$basename.'.*')) ?: [];

        if ($matches !== []) {
            return str_replace('\\', '/', substr($matches[0], strlen(public_path()) + 1));
        }

        return $relative;
    }

    public static function delete(?string $path, ?string $sourcePath = null): void
    {
        foreach (array_unique(array_filter([$path, $sourcePath])) as $candidate) {
            $relative = self::safeUploadPath($candidate);
            $fullPath = public_path($relative);
            if (File::isFile($fullPath)) {
                File::delete($fullPath);
            }

            // New uploads keep the source in a sibling originals/ folder.
            $folder = dirname($relative);
            $basename = pathinfo($relative, PATHINFO_FILENAME);
            $originals = glob(public_path($folder.'/originals/'.$basename.'.*')) ?: [];
            if ($originals !== []) {
                File::delete($originals);
            }
        }
    }

    private static function preserveOriginal(string $path): string
    {
        $relative = self::safeUploadPath($path);
        $folder = dirname($relative);
        $basename = pathinfo($relative, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION) ?: 'webp');
        $sourceRelative = $folder.'/originals/'.$basename.'.'.$extension;

        File::ensureDirectoryExists(dirname(public_path($sourceRelative)));
        if (! is_file(public_path($sourceRelative)) && ! File::copy(public_path($relative), public_path($sourceRelative))) {
            throw new RuntimeException('The original image could not be preserved before compression.');
        }

        return $sourceRelative;
    }

    /** @return array{size:int, quality:int} */
    private static function encode(string $sourcePath, string $outputPath, int $quality, ?int $targetKb = null): array
    {
        $info = @getimagesize($sourcePath);
        $source = match ($info['mime'] ?? null) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => false,
        };

        if (! $source) {
            throw new RuntimeException('This image format cannot be optimized. Use JPG, PNG, or WebP.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::MAX_EDGE / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $image = imagecreatetruecolor($newWidth, $newHeight);

        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        $quality = max(self::MIN_QUALITY, min(self::MAX_QUALITY, $quality));
        $qualities = $targetKb
            ? range(self::MAX_QUALITY, self::MIN_QUALITY, -5)
            : [$quality];
        $bestBlob = null;
        $bestSize = PHP_INT_MAX;
        $bestQuality = $quality;
        $targetBytes = $targetKb ? max(1, $targetKb * 1024) : null;

        foreach ($qualities as $candidateQuality) {
            ob_start();
            $encoded = imagewebp($image, null, $candidateQuality);
            $blob = ob_get_clean();

            if (! $encoded || ! is_string($blob) || $blob === '') {
                continue;
            }

            $size = strlen($blob);
            if ($size < $bestSize) {
                $bestBlob = $blob;
                $bestSize = $size;
                $bestQuality = $candidateQuality;
            }

            if ($targetBytes !== null && $size <= $targetBytes) {
                $bestBlob = $blob;
                $bestSize = $size;
                $bestQuality = $candidateQuality;
                break;
            }
        }

        imagedestroy($image);
        if ($bestBlob === null) {
            throw new RuntimeException('Image compression did not produce a usable file.');
        }

        if (file_put_contents($outputPath, $bestBlob) === false) {
            throw new RuntimeException('The optimized image could not be written.');
        }

        return ['size' => $bestSize, 'quality' => $bestQuality];
    }

    private static function safeUploadPath(string $path): string
    {
        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (! str_starts_with($relative, 'uploads/') || str_contains($relative, '../')) {
            throw new RuntimeException('Invalid upload image path.');
        }

        return $relative;
    }
}
