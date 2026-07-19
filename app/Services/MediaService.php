<?php

namespace App\Services;

use App\Actions\StoreUploadedMedia;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class MediaService
{
    /** @var list<array{0: string, 1: string}> */
    private const REFERENCES = [
        ['pages', 'og_image_id'], ['page_sections', 'image_id'],
        ['news', 'featured_image_id'], ['news', 'og_image_id'],
        ['programs', 'image_id'], ['programs', 'og_image_id'],
        ['team_members', 'photo_id'], ['partners', 'logo_id'],
        ['testimonials', 'photo_id'], ['galleries', 'cover_image_id'],
        ['gallery_images', 'media_id'],
    ];

    public function __construct(
        private readonly StoreUploadedMedia $storeUploadedMedia,
        private readonly ActivityLogger $activities,
    ) {}

    public function upload(UploadedFile $file, array $metadata = [], ?int $userId = null): Media
    {
        $media = $this->storeUploadedMedia->handle($file, $metadata, $userId);
        $this->activities->record('media.uploaded', $media, $userId, ['mime_type' => $media->mime_type, 'size' => $media->size]);

        return $media;
    }

    public function delete(Media $media, ?int $userId = null, bool $force = false): void
    {
        if ($force) {
            $this->detachEverywhere($media);
        }

        $this->ensureUnused($media);
        $disk = $media->disk;
        $path = $media->path;

        DB::transaction(function () use ($media, $userId, $force): void {
            $this->activities->record('media.deleted', $media, $userId, [
                'filename' => $media->filename,
                'forced' => $force,
            ]);
            $media->delete();
        });

        // The model event also covers deletions initiated by Filament; this explicit
        // cleanup makes the service contract deterministic after a successful commit.
        Storage::disk($disk)->delete($path);
    }

    /**
     * Retire ce média de tous les contenus qui l'utilisent (réservé à la
     * suppression forcée par un super administrateur).
     */
    public function detachEverywhere(Media $media): void
    {
        DB::transaction(function () use ($media): void {
            foreach (self::REFERENCES as [$table, $column]) {
                if ($table === 'gallery_images') {
                    DB::table($table)->where($column, $media->id)->delete();

                    continue;
                }

                DB::table($table)->where($column, $media->id)->update([$column => null]);
            }

            Setting::query()->whereIn('value', array_filter([
                $media->url,
                $media->image_url,
                Storage::disk($media->disk)->url($media->path),
            ]))->update(['value' => null]);
        });
    }

    public function ensureUnused(Media $media): void
    {
        foreach (self::REFERENCES as [$table, $column]) {
            if (DB::table($table)->where($column, $media->id)->exists()) {
                throw ValidationException::withMessages(['media' => 'Ce média est encore utilisé par un contenu et ne peut pas être supprimé.']);
            }
        }

        if (Setting::query()->whereIn('value', array_filter([
            $media->url,
            $media->image_url,
            Storage::disk($media->disk)->url($media->path),
        ]))->exists()) {
            throw ValidationException::withMessages([
                'media' => 'Ce média est utilisé comme logo, favicon ou image de partage et ne peut pas être supprimé.',
            ]);
        }
    }
}
