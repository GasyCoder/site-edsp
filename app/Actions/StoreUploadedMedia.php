<?php

namespace App\Actions;

use App\Models\Media;
use App\Services\MediaVariantDispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class StoreUploadedMedia
{
    /** @var array<string, list<string>> */
    private const ALLOWED_MIME_TYPES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    public function handle(UploadedFile $file, array $metadata = [], ?int $userId = null): Media
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();

        if (! isset(self::ALLOWED_MIME_TYPES[$extension]) || ! in_array($mime, self::ALLOWED_MIME_TYPES[$extension], true)) {
            throw ValidationException::withMessages(['file' => 'Le contenu réel du fichier ne correspond pas à un format autorisé.']);
        }

        if (str_starts_with($mime, 'image/') && blank($metadata['alt_text'] ?? null)) {
            throw ValidationException::withMessages(['alt_text' => 'Un texte alternatif est requis pour une image.']);
        }

        $filename = (string) Str::uuid().'.'.$extension;
        $path = 'media/'.now()->format('Y/m').'/'.$filename;
        $storedPath = Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));
        if ($storedPath === false) {
            throw ValidationException::withMessages(['file' => 'Le fichier n’a pas pu être enregistré.']);
        }

        try {
            [$width, $height] = $this->dimensions($file, $mime);

            $media = Media::query()->create([
                'disk' => 'public',
                'path' => $path,
                'filename' => $filename,
                'original_name' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
                'mime_type' => $mime,
                'extension' => $extension,
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
                'alt_text' => $metadata['alt_text'] ?? null,
                'caption' => $metadata['caption'] ?? null,
                'uploaded_by' => $userId,
            ]);

            if (str_starts_with($mime, 'image/')) {
                app(MediaVariantDispatcher::class)->dispatch($media);
            }

            return $media;
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    /** @return array{0: ?int, 1: ?int} */
    private function dimensions(UploadedFile $file, string $mime): array
    {
        if (! str_starts_with($mime, 'image/')) {
            return [null, null];
        }

        $size = @getimagesize($file->getRealPath());

        return $size === false ? [null, null] : [(int) $size[0], (int) $size[1]];
    }
}
