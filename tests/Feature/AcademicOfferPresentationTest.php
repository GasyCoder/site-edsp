<?php

use App\Models\AcademicLevel;
use App\Models\Mention;
use App\Models\Parcours;
use App\Models\ParcoursLevel;
use App\Models\Program;
use Inertia\Testing\AssertableInertia as Assert;

test('the public programmes page follows the mention pathway and level hierarchy', function (): void {
    $mention = Mention::query()->create([
        'code' => 'DROIT',
        'nom' => 'Droit',
        'description' => 'Mention Droit.',
        'is_active' => true,
    ]);
    $l1 = AcademicLevel::query()->create(['code' => 'L1', 'nom' => 'Licence 1', 'ordre' => 1]);
    $l3 = AcademicLevel::query()->create(['code' => 'L3', 'nom' => 'Licence 3', 'ordre' => 3]);
    $commonLaw = Parcours::query()->create([
        'mention_id' => $mention->id,
        'code' => 'DROI',
        'nom' => 'Droit',
        'description' => 'Parcours Droit pour les niveaux L1 et L2.',
    ]);
    $privateLaw = Parcours::query()->create([
        'mention_id' => $mention->id,
        'code' => 'DPRI',
        'nom' => 'Droit Privé',
        'description' => 'Parcours Droit Privé pour le niveau L3.',
    ]);
    ParcoursLevel::query()->create([
        'parcours_id' => $commonLaw->id,
        'level_id' => $l1->id,
        'is_active' => true,
        'is_common_core' => true,
    ]);
    ParcoursLevel::query()->create([
        'parcours_id' => $privateLaw->id,
        'level_id' => $l3->id,
        'is_active' => true,
        'is_common_core' => false,
    ]);
    Program::query()->create([
        'mention_id' => $mention->id,
        'title' => 'Droit',
        'slug' => 'droit',
        'level' => 'L1 à M2',
        'description' => 'Présentation de la mention Droit.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get('/formations')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Programs/Index')
            ->has('mentions', 1)
            ->where('mentions.0.nom', 'Droit')
            ->has('mentions.0.parcours', 2)
            ->where('mentions.0.parcours.0.nom', 'Droit')
            ->where('mentions.0.parcours.0.level_links.0.level.code', 'L1')
            ->where('mentions.0.parcours.0.level_links.0.is_common_core', true)
            ->where('mentions.0.parcours.1.nom', 'Droit Privé')
            ->where('mentions.0.parcours.1.level_links.0.level.code', 'L3'));
});
