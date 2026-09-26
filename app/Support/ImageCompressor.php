<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageCompressor
{
    /**
     * Phone photos are routinely 4000px+ and several MB — far more
     * than a web page needs. Everything is downscaled to fit within
     * this many pixels on its longest edge and re-encoded as WebP,
     * which typically lands a 5 MB camera JPEG at around 200–400 KB.
     */
    public const MAX_EDGE = 1920;

    public const QUALITY = 82;

    /**
     * Stores a compressed WebP copy of $file on the public disk and
     * returns its path. The original upload is never kept.
     */
    public static function store(UploadedFile $file, string $directory): string
    {
        // GD holds ~5 bytes per pixel uncompressed, so a 12 MP camera
        // photo needs ~60 MB just to open, and more while it's being
        // scaled — beyond PHP's usual 128 MB default. Only ever raised.
        self::raiseMemoryLimit('512M');

        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($source === false) {
            throw new RuntimeException('Unsupported or corrupt image.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::MAX_EDGE / max($width, $height));

        $image = $source;

        // Scale before rotating so the rotation copy is of the small image.
        if ($scale < 1) {
            $image = imagescale($source, (int) round($width * $scale), (int) round($height * $scale), IMG_BICUBIC);
            imagedestroy($source);
        }

        $image = self::applyExifOrientation($image, $file);

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private static function raiseMemoryLimit(string $limit): void
    {
        $current = ini_get('memory_limit');

        if ($current !== '-1' && ini_parse_quantity($current) < ini_parse_quantity($limit)) {
            ini_set('memory_limit', $limit);
        }
    }

    /**
     * GD ignores EXIF orientation, so portrait phone photos (which are
     * stored sideways with an orientation tag) would otherwise come
     * out rotated 90°.
     */
    private static function applyExifOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($file->getRealPath())['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }
}
