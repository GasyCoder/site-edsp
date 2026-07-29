<?php

use App\Filament\Resources\Galleries\GalleryResource;
use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Gallery;
use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('administrators see multiple image upload controls in media and gallery forms', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view media',
        'upload media',
        'view galleries',
        'create galleries',
    ]));

    Livewire::test(ManageMedia::class)
        ->assertActionExists('uploadMultiple')
        ->mountAction('uploadMultiple')
        ->assertFormFieldExists(
            'files',
            checkFieldUsing: fn (FileUpload $field): bool => $field->isMultiple(),
        );

    Livewire::test(CreateGallery::class)
        ->assertFormFieldExists(
            'new_images',
            checkFieldUsing: fn (FileUpload $field): bool => $field->isMultiple(),
        );
});

test('multiple stored images become individual media and gallery entries', function (): void {
    Storage::fake('public');
    $this->actingAs(userWithPermissions([
        'upload media',
        'create galleries',
    ]));

    $directory = MediaResource::currentMediaDirectory();
    $paths = collect(['campus-un.jpg', 'campus-deux.png'])
        ->mapWithKeys(function (string $originalName) use ($directory): array {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $path = $directory.'/'.Str::ulid().'.'.$extension;
            $file = UploadedFile::fake()->image($originalName, 900, 600);
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

            return [$path => $originalName];
        });

    $media = MediaResource::createManyFromStoredImages(
        $paths->keys()->all(),
        $paths->all(),
        'Campus EDSP',
        'Vie universitaire',
    );

    expect($media)->toHaveCount(2)
        ->and(Media::query()->count())->toBe(2)
        ->and($media->pluck('alt_text')->all())->toBe(['Campus EDSP 1', 'Campus EDSP 2']);

    $gallery = Gallery::query()->create([
        'title' => 'Vie du campus',
        'slug' => 'vie-du-campus',
        'status' => 'published',
        'is_visible' => true,
        'published_at' => now(),
        'position' => 1,
    ]);
    $galleryPaths = collect(['conference.jpg', 'atelier.png'])
        ->mapWithKeys(function (string $originalName) use ($directory): array {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $path = $directory.'/'.Str::ulid().'.'.$extension;
            $file = UploadedFile::fake()->image($originalName, 800, 500);
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

            return [$path => $originalName];
        });

    $count = GalleryResource::attachUploadedImages($gallery, [
        'paths' => $galleryPaths->keys()->all(),
        'names' => $galleryPaths->all(),
        'alt_prefix' => null,
        'caption' => null,
    ]);
    GalleryResource::syncDefaultCover($gallery);

    expect($count)->toBe(2)
        ->and($gallery->images()->count())->toBe(2)
        ->and($gallery->images()->orderBy('position')->pluck('alt_text')->all())
        ->toBe(['Vie du campus 1', 'Vie du campus 2'])
        ->and($gallery->fresh()->cover_image_id)->toBe($gallery->images()->orderBy('position')->value('media_id'))
        ->and(Media::query()->count())->toBe(4);

    foreach ($paths->keys()->merge($galleryPaths->keys()) as $path) {
        Storage::disk('public')->assertExists($path);
    }
});
