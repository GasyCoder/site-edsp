<?php

use App\Filament\Forms\MediaImagePreview;
use App\Filament\Resources\Galleries\Pages\ManageGalleries;
use App\Filament\Resources\Partners\Pages\ManagePartners;
use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Media;
use Livewire\Livewire;

test('media selectors expose an immediate image preview in administration forms', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view team',
        'create team',
        'view partners',
        'create partners',
        'view testimonials',
        'create testimonials',
        'view galleries',
        'create galleries',
    ]));
    $photo = Media::query()->create([
        'disk' => 'public',
        'path' => 'media/portrait-preview.jpg',
        'filename' => 'portrait-preview.jpg',
        'original_name' => 'portrait-preview.jpg',
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size' => 10,
        'alt_text' => 'Portrait de vérification',
    ]);

    Livewire::test(ManageTeamMembers::class)
        ->mountAction('create')
        ->assertFormFieldExists('photo_id')
        ->assertSchemaComponentExists('photo_preview');

    expect(MediaImagePreview::render($photo->id)->toHtml())
        ->toContain('portrait-preview.jpg')
        ->toContain('Portrait de vérification');

    Livewire::test(ManagePartners::class)
        ->mountAction('create')
        ->assertFormFieldExists('logo_id')
        ->assertSchemaComponentExists('logo_preview');

    Livewire::test(ManageTestimonials::class)
        ->mountAction('create')
        ->assertFormFieldExists('photo_id')
        ->assertSchemaComponentExists('photo_preview');

    Livewire::test(ManageGalleries::class)
        ->mountAction('create')
        ->assertFormFieldExists('cover_image_id')
        ->assertSchemaComponentExists('cover_image_preview')
        ->assertFormFieldExists('images');
});
