<?php

use App\Models\ContentRevision;
use App\Models\Page;

test('an authorized editor updates a controlled section and creates a revision', function (): void {
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
        'title' => 'Titre initial',
        'content' => 'Contenu initial',
        'settings' => ['background' => 'light', 'alignment' => 'left', 'container' => 'wide'],
        'position' => 1,
        'is_visible' => true,
    ]);
    $editor = userWithPermissions(['edit pages', 'restore revisions']);

    $this->actingAs($editor)->patch(route('sections.update', $section), [
        'title' => 'Titre mis à jour',
        'subtitle' => 'Sur-titre',
        'content' => '<p>Contenu <script>alert(1)</script>nettoyé</p>',
        'image_id' => null,
        'button_text' => 'Découvrir',
        'button_url' => '/formations',
        'is_visible' => true,
        'settings' => [
            'background' => 'blue',
            'alignment' => 'center',
            'container' => 'wide',
            'visual_title' => 'Un nouveau message dans le visuel',
            'kicker_text' => 'Choisissez votre orientation :',
            'rotating_item_1' => 'Droit des affaires',
            'rotating_item_2' => 'Administration publique',
            'location_text' => 'Campus Ambondrona',
            'degree_text' => 'L1 à M2',
            'visual_program_1' => 'Droit privé',
            'visual_program_2' => 'Science politique',
            'visual_footer' => 'Deux parcours, une même exigence',
            'secondary_button_text' => 'Candidater maintenant',
            'secondary_button_url' => '/inscription',
        ],
    ])->assertRedirect();

    $section->refresh();
    expect($section->title)->toBe('Titre mis à jour')
        ->and($section->content)->not->toContain('<script>')
        ->and($section->settings['background'])->toBe('blue')
        ->and($section->settings['visual_title'])->toBe('Un nouveau message dans le visuel')
        ->and($section->settings['kicker_text'])->toBe('Choisissez votre orientation :')
        ->and($section->settings['rotating_item_1'])->toBe('Droit des affaires')
        ->and($section->settings['location_text'])->toBe('Campus Ambondrona')
        ->and($section->settings['degree_text'])->toBe('L1 à M2')
        ->and($section->settings['visual_footer'])->toBe('Deux parcours, une même exigence')
        ->and($section->settings['secondary_button_url'])->toBe('/inscription');

    $revision = ContentRevision::query()
        ->where('revisionable_type', $section::class)
        ->where('revisionable_id', $section->id)
        ->where('action', 'updated')
        ->sole();
    expect($revision->old_values['title'])->toBe('Titre initial')
        ->and($revision->new_values['title'])->toBe('Titre mis à jour');

    $this->actingAs($editor)
        ->post(route('revisions.restore', $revision))
        ->assertRedirect();

    expect($section->fresh()->title)->toBe('Titre initial')
        ->and(ContentRevision::query()->where('action', 'restored')->exists())->toBeTrue();
});

test('visual editor rejects unknown or unsafe section settings', function (): void {
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
        'position' => 1,
        'is_visible' => true,
    ]);
    $editor = userWithPermissions(['edit pages']);

    $this->actingAs($editor)->patch(route('sections.update', $section), [
        'is_visible' => true,
        'settings' => [
            'background' => 'purple',
            'unknown_css' => 'position:fixed',
            'secondary_button_url' => 'javascript:alert(1)',
        ],
    ])->assertSessionHasErrors([
        'settings',
        'settings.background',
        'settings.secondary_button_url',
    ]);

    expect($section->fresh()->settings)->toBe([]);
});

test('section mutation is denied without backend permission', function (): void {
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
        'title' => 'Titre initial',
        'position' => 1,
        'is_visible' => true,
    ]);

    $this->actingAs(userWithPermissions([]))
        ->patch(route('sections.update', $section), [
            'title' => 'Interdit',
            'is_visible' => true,
        ])
        ->assertForbidden();

    expect($section->fresh()->title)->toBe('Titre initial');
});

test('guests are redirected to the administration login for protected mutations', function (): void {
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
        'position' => 1,
        'is_visible' => true,
    ]);

    $this->patch(route('sections.update', $section), ['is_visible' => true])
        ->assertRedirect(route('filament.admin.auth.login'));
});
