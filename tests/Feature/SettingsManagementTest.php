<?php

use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Models\Media;
use App\Models\Setting;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(SettingsSeeder::class);

    $this->actingAs(userWithPermissions([
        'access admin',
        'view settings',
        'create settings',
        'edit settings',
        'view media',
        'upload media',
    ]));
});

test('the settings page uses a clear grouped form', function (): void {
    $this->get('/admin/settings')
        ->assertOk()
        ->assertSee('Paramètres du site')
        ->assertSee('Identité')
        ->assertSee('Référence officielle')
        ->assertSee('Modifier la référence officielle')
        ->assertSee('Habilitation de l’offre de formation')
        ->assertSee('Référence de l’arrêté d’habilitation')
        ->assertSee('Coordonnées')
        ->assertSee('Réseaux sociaux')
        ->assertSee('SEO et partage')
        ->assertSee('Enregistrer les paramètres');
});

test('an authorized editor updates both official references from the public editor', function (): void {
    $response = $this->post(route('settings.institutional-reference.update'), [
        'ministerial_reference_label' => 'Référence historique',
        'ministerial_reference' => 'Arrêté historique de l’EDSP',
        'accreditation_reference_label' => 'Habilitation des formations',
        'accreditation_reference' => 'Arrêté n°34682/2025-MESUPRES portant habilitation de l’offre de formation de l’EDSP',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Références officielles mises à jour.');

    expect(Setting::query()->where('key', 'ministerial_reference')->value('value'))
        ->toBe('Arrêté historique de l’EDSP')
        ->and(Setting::query()->where('key', 'accreditation_reference')->value('value'))
        ->toContain('34682/2025-MESUPRES');
});

test('an existing media image can be selected as the site logo', function (): void {
    $media = Media::query()->create([
        'disk' => 'public',
        'path' => 'media/logo.png',
        'filename' => 'logo.png',
        'original_name' => 'Logo institutionnel.png',
        'mime_type' => 'image/png',
        'extension' => 'png',
        'size' => 1024,
        'width' => 600,
        'height' => 200,
        'alt_text' => 'Logo de l’EDSP',
    ]);

    Livewire::test(ManageSettings::class)
        ->fillForm([
            'logo_source' => 'gallery',
            'logo_media_id' => $media->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Setting::query()->where('key', 'logo_url')->value('value'))->toBe($media->url);
});

test('a logo uploaded from the computer is stored in the media library', function (): void {
    Storage::fake('public');
    Queue::fake();

    $file = UploadedFile::fake()->image('logo-edsp.png', 600, 200);
    $path = Storage::disk('public')->putFileAs('branding', $file, 'logo-edsp.png');

    $page = app(ManageSettings::class);
    $resolveImage = function () use ($path): ?string {
        return $this->resolveImageValue('logo', [
            'logo_source' => 'computer',
            'logo_upload' => $path,
            'logo_original_name' => 'logo-edsp.png',
            'site_name' => 'EDSP',
        ]);
    };

    $logoUrl = $resolveImage->call($page);

    $saveLogo = function () use ($logoUrl): void {
        $this->saveSetting('logo_url', $logoUrl);
    };
    $saveLogo->call($page);

    $media = Media::query()->sole();

    expect($media->mime_type)->toBe('image/png')
        ->and($media->path)->toStartWith('branding/')
        ->and($logoUrl)->toBe($media->url)
        ->and(Setting::query()->where('key', 'logo_url')->value('value'))->toBe($media->url);

    Storage::disk('public')->assertExists($media->path);
});
