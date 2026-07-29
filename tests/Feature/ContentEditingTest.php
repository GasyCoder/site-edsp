<?php

use App\Models\ContentRevision;
use App\Models\Media;
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
            'title_highlight_1' => 'droit',
            'title_highlight_1_color' => 'green',
            'title_highlight_2' => 'science politique',
            'title_highlight_2_color' => 'institutional',
            'title_font_size' => 56,
            'image_zoom' => 75,
            'image_position_x' => 42,
            'image_position_y' => 28,
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
        ->and($section->settings['title_highlight_1'])->toBe('droit')
        ->and($section->settings['title_highlight_2_color'])->toBe('institutional')
        ->and($section->settings['title_font_size'])->toBe(56)
        ->and($section->settings['image_zoom'])->toBe(75)
        ->and($section->settings['image_position_x'])->toBe(42)
        ->and($section->settings['image_position_y'])->toBe(28)
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

test('the production-safe post endpoint persists visual editor changes', function (): void {
    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil-post-edition',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);
    $section = $page->sections()->create([
        'section_key' => 'hero',
        'section_type' => 'hero',
        'title' => 'Avant',
        'position' => 1,
        'is_visible' => true,
    ]);

    $this->actingAs(userWithPermissions(['edit pages']))
        ->from('/')
        ->post(route('sections.update', $section), [
            'title' => 'Après publication',
            'is_visible' => true,
            'settings' => [
                'background' => 'light',
                'alignment' => 'left',
                'container' => 'wide',
            ],
        ])
        ->assertStatus(303)
        ->assertRedirect('/')
        ->assertSessionHas('success', 'Section mise à jour.');

    expect($section->fresh()->title)->toBe('Après publication');
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

test('an editor can assign the three student life images independently', function (): void {
    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil-vie-etudiante',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);
    $section = $page->sections()->create([
        'section_key' => 'student-life',
        'section_type' => 'student_life',
        'position' => 8,
        'is_visible' => true,
    ]);
    $images = collect(['principale', 'conference', 'evenement'])->map(fn (string $name): Media => Media::query()->create([
        'disk' => 'public',
        'path' => "media/{$name}.jpg",
        'filename' => "{$name}.jpg",
        'original_name' => "{$name}.jpg",
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size' => 10,
        'alt_text' => "Photo {$name}",
    ]));

    $this->actingAs(userWithPermissions(['edit pages']))
        ->patch(route('sections.update', $section), [
            'image_id' => $images[0]->id,
            'is_visible' => true,
            'settings' => [
                'secondary_media_id' => $images[1]->id,
                'tertiary_media_id' => $images[2]->id,
            ],
        ])
        ->assertRedirect();

    $section->refresh();

    expect($section->image_id)->toBe($images[0]->id)
        ->and($section->settings['secondary_media_id'])->toBe($images[1]->id)
        ->and($section->settings['tertiary_media_id'])->toBe($images[2]->id)
        ->and($section->secondary_image_url)->toContain('conference.jpg')
        ->and($section->tertiary_image_url)->toContain('evenement.jpg');
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
