<?php

use App\Filament\Resources\PageSections\Pages\ManagePageSections;
use App\Models\Page;
use Livewire\Livewire;

test('a page section edit modal can evaluate conditional fields', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view pages',
        'edit pages',
        'publish pages',
    ]));

    $page = Page::query()->create([
        'title' => 'Présentation',
        'slug' => 'presentation-test',
        'template' => 'standard',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $section = $page->sections()->create([
        'section_key' => 'director-message-test',
        'section_type' => 'director-message',
        'title' => 'Pr. Test Directeur',
        'position' => 16,
        'is_visible' => true,
    ]);

    Livewire::test(ManagePageSections::class)
        ->mountTableAction('edit', $section)
        ->assertFormFieldExists('settings.director_position')
        ->assertFormFieldExists('translations.en.settings.director_position')
        ->assertHasNoErrors();
});

test('student life imagery is editable from the administration', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view pages',
        'edit pages',
        'publish pages',
    ]));

    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil-student-life-test',
        'template' => 'home',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $section = $page->sections()->create([
        'section_key' => 'student-life-test',
        'section_type' => 'student_life',
        'title' => 'Vie étudiante',
        'position' => 10,
        'is_visible' => true,
    ]);

    Livewire::test(ManagePageSections::class)
        ->mountTableAction('edit', $section)
        ->assertFormFieldExists('image_id')
        ->assertFormFieldExists('settings.secondary_media_id')
        ->assertFormFieldExists('settings.tertiary_media_id')
        ->assertHasNoErrors();
});
