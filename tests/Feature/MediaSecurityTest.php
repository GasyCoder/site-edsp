<?php

use App\Models\AdmissionCampaign;
use App\Models\Application;
use App\Models\Media;
use App\Models\Page;
use App\Models\Program;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authorized users upload only validated public media with required alt text', function (): void {
    Storage::fake('public');
    $user = userWithPermissions(['upload media']);

    $this->actingAs($user)->postJson(route('media.store'), [
        'file' => UploadedFile::fake()->image('campus.jpg', 800, 600),
        'alt_text' => 'Étudiants sur le campus',
        'caption' => 'Vie universitaire',
    ])->assertCreated()->assertJsonPath('media.alt_text', 'Étudiants sur le campus');

    $media = Media::query()->sole();
    Storage::disk('public')->assertExists($media->path);
    Storage::disk('public')->assertExists($media->optimized_path);
    Storage::disk('public')->assertExists($media->thumbnail_path);
    expect($media->image_url)->toContain('-optimized.webp')
        ->and($media->thumbnail_url)->toContain('-thumbnail.webp');

    $this->actingAs($user)->postJson(route('media.store'), [
        'file' => UploadedFile::fake()->image('sans-alt.png'),
    ])->assertUnprocessable()->assertJsonValidationErrors('alt_text');

    $this->actingAs($user)->postJson(route('media.store'), [
        'file' => UploadedFile::fake()->create('script.php', 2, 'application/x-php'),
        'alt_text' => 'Interdit',
    ])->assertUnprocessable()->assertJsonValidationErrors('file');
});

test('a referenced medium cannot be deleted and an unused file is removed', function (): void {
    Storage::fake('public');
    $user = userWithPermissions(['delete media']);
    $media = Media::query()->create([
        'disk' => 'public',
        'path' => 'media/test.jpg',
        'filename' => 'test.jpg',
        'original_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size' => 10,
        'alt_text' => 'Test',
    ]);
    Storage::disk('public')->put($media->path, 'image');
    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);
    $section = $page->sections()->create([
        'section_key' => 'hero',
        'section_type' => 'hero',
        'image_id' => $media->id,
        'position' => 1,
        'is_visible' => true,
    ]);

    $this->actingAs($user)->deleteJson(route('media.destroy', $media))
        ->assertUnprocessable();

    $section->update(['image_id' => null]);
    $this->actingAs($user)->deleteJson(route('media.destroy', $media))
        ->assertNoContent();

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
    Storage::disk('public')->assertMissing('media/test.jpg');
});

test('candidate documents remain private and require permission to download', function (): void {
    Storage::fake('private');
    $program = Program::query()->create([
        'title' => 'Droit privé', 'slug' => 'droit-prive', 'level' => 'Licence',
        'description' => 'Formation', 'status' => 'published', 'published_at' => now(),
    ]);
    $campaign = AdmissionCampaign::query()->create([
        'title' => 'Campagne', 'academic_year' => '2026-2027',
        'opens_at' => now()->subDay(), 'closes_at' => now()->addDay(),
        'status' => 'published', 'is_visible' => true,
    ]);
    $application = Application::query()->create([
        'public_id' => fake()->uuid(), 'application_number' => 'EDSP-2026-PRIVATE1',
        'admission_campaign_id' => $campaign->id, 'program_id' => $program->id,
        'first_name' => 'Soa', 'last_name' => 'Ranaivo', 'email' => 'soa@example.test',
        'phone' => '+261 32 00 000 00', 'birth_date' => '2000-01-01', 'address' => 'Mahajanga',
        'academic_background' => 'Baccalauréat', 'status' => 'submitted',
        'privacy_accepted' => true, 'submitted_at' => now(),
    ]);
    $document = $application->documents()->create([
        'type' => 'identity', 'disk' => 'private', 'path' => 'applications/private/identity.pdf',
        'original_name' => 'identite.pdf', 'mime_type' => 'application/pdf', 'size' => 100,
    ]);
    Storage::disk('private')->put($document->path, 'private document');

    $this->actingAs(userWithPermissions([]))
        ->get(route('application-documents.download', $document))
        ->assertNotFound();
    $this->get(route('application-documents.preview', $document))
        ->assertNotFound();

    $authorized = userWithPermissions(['download application documents']);
    $previewResponse = $this->actingAs($authorized)
        ->get(route('application-documents.preview', $document))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($previewResponse->headers->get('content-disposition'))
        ->toContain('inline')
        ->and($previewResponse->headers->get('cache-control'))
        ->toContain('private')
        ->toContain('no-store');

    $response = $this->actingAs($authorized)
        ->get(route('application-documents.download', $document))
        ->assertOk();

    expect($response->headers->get('cache-control'))
        ->toContain('private')
        ->toContain('no-store');

});
