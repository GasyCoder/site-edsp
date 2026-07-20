<?php

use App\Filament\Resources\Programs\ProgramResource;
use App\Models\Program;

beforeEach(function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view programs',
        'create programs',
        'edit programs',
        'publish programs',
        'delete programs',
    ]));
});

test('programme creation uses a dedicated full width page', function (): void {
    $this->get(ProgramResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Créer une page de mention')
        ->assertSee('Retour aux formations')
        ->assertSee('Mention')
        ->assertSee('Organisation issue de Scolarité')
        ->assertSee('Aperçu de l’image')
        ->assertDontSee('Département')
        ->assertSee('Détails pédagogiques')
        ->assertSee('Version anglaise');
});

test('programme editing uses a dedicated page instead of a table modal', function (): void {
    $program = Program::query()->create([
        'title' => 'Droit privé',
        'slug' => 'droit-prive',
        'level' => 'Licence',
        'description' => 'Formation en droit privé.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(ProgramResource::getUrl('edit', ['record' => $program]))
        ->assertOk()
        ->assertSee('Modifier : Droit privé')
        ->assertSee('Voir sur le site')
        ->assertSee('Retour aux formations');
});
