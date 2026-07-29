<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class OptimizeMediaImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public readonly int $mediaId) {}

    public function handle(ActivityLogger $activities): void
    {
        $media = Media::query()->find($this->mediaId);

        if ($media === null || $media->disk !== 'public' || ! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            return;
        }

        $sourcePath = $disk->path($media->path);
        $source = $this->openImage($sourcePath, (string) $media->mime_type);

        try {
            $directory = dirname($media->path).'/variants';
            $basename = pathinfo($media->filename, PATHINFO_FILENAME);
            $optimizedPath = $directory.'/'.$basename.'-optimized.webp';
            $thumbnailPath = $directory.'/'.$basename.'-thumbnail.webp';
            $disk->makeDirectory($directory);

            try {
                $this->writeVariant(
                    $source,
                    $disk->path($optimizedPath),
                    (int) config('media.optimized_width', 1920),
                    (int) config('media.optimized_quality', 82),
                );
                $this->writeVariant(
                    $source,
                    $disk->path($thumbnailPath),
                    (int) config('media.thumbnail_width', 640),
                    (int) config('media.thumbnail_quality', 78),
                );
            } catch (Throwable $exception) {
                $disk->delete([$optimizedPath, $thumbnailPath]);

                throw $exception;
            }

            $oldVariants = array_filter([$media->optimized_path, $media->thumbnail_path]);
            $media->update([
                'optimized_path' => $optimizedPath,
                'thumbnail_path' => $thumbnailPath,
            ]);
            $disk->delete(array_values(array_diff($oldVariants, [$optimizedPath, $thumbnailPath])));
            $activities->record('media.optimized', $media, metadata: [
                'optimized_path' => $optimizedPath,
                'thumbnail_path' => $thumbnailPath,
            ]);
        } finally {
            imagedestroy($source);
        }
    }

    private function openImage(string $path, string $mimeType): \GdImage
    {
        try {
            $image = match ($mimeType) {
                'image/jpeg' => imagecreatefromjpeg($path),
                'image/png' => imagecreatefrompng($path),
                'image/webp' => imagecreatefromwebp($path),
                default => false,
            };
        } catch (Throwable) {
            $image = false;
        }

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('Impossible de décoder le média image.');
        }

        return $image;
    }

    private function writeVariant(\GdImage $source, string $destination, int $maximumWidth, int $quality): void
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $ratio = min(1, $maximumWidth / max(1, $sourceWidth));
        $width = max(1, (int) round($sourceWidth * $ratio));
        $height = max(1, (int) round($sourceHeight * $ratio));
        $variant = imagecreatetruecolor($width, $height);

        if (! $variant instanceof \GdImage) {
            throw new RuntimeException('Impossible de créer une variante du média.');
        }

        try {
            imagealphablending($variant, false);
            imagesavealpha($variant, true);
            $transparent = imagecolorallocatealpha($variant, 0, 0, 0, 127);
            imagefill($variant, 0, 0, $transparent);
            imagecopyresampled($variant, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

            if (! imagewebp($variant, $destination, $quality)) {
                throw new RuntimeException('Impossible d’écrire une variante WebP.');
            }
        } finally {
            imagedestroy($variant);
        }
    }
}
