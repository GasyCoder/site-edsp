<?php

use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\ManageApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Models\AdmissionCampaign;
use App\Models\Application;
use App\Models\Program;
use Livewire\Livewire;

test('the applications list is readable and offers practical processing tools', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view applications',
        'edit applications',
        'change application status',
        'export applications',
    ]));

    $this->get('/admin/applications')
        ->assertOk()
        ->assertSee('Dossiers d’inscription')
        ->assertSee('Nouveaux')
        ->assertSee('À traiter');

    Livewire::test(ManageApplications::class)
        ->assertSet('viewMode', 'list')
        ->assertTableColumnExists('application_number')
        ->assertTableColumnExists('last_name')
        ->assertTableColumnExists('academic_orientation')
        ->assertTableColumnExists('status')
        ->assertTableColumnExists('submitted_at')
        ->assertTableFilterExists('status')
        ->assertTableFilterExists('academic_level_id')
        ->assertTableFilterExists('mention_id')
        ->assertTableFilterExists('parcours_id')
        ->assertTableHeaderActionsExistInOrder(['grid_view', 'list_view', 'export'])
        ->assertTableActionExists('view')
        ->assertTableActionExists('edit')
        ->call('setViewMode', 'grid')
        ->assertSet('viewMode', 'grid');
});

test('viewing and processing an application use dedicated full width pages', function (): void {
    $user = userWithPermissions([
        'access admin',
        'view applications',
        'edit applications',
        'change application status',
        'download application documents',
    ]);
    $program = Program::query()->create([
        'title' => 'Droit privé',
        'slug' => 'droit-prive-admin-ui',
        'level' => 'Licence',
        'description' => 'Formation',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $campaign = AdmissionCampaign::query()->create([
        'title' => 'Inscriptions 2026-2027',
        'academic_year' => '2026-2027',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addMonth(),
        'status' => 'published',
        'is_visible' => true,
    ]);
    $application = Application::query()->create([
        'public_id' => fake()->uuid(),
        'application_number' => 'EDSP-2026-UI00001',
        'admission_campaign_id' => $campaign->id,
        'program_id' => $program->id,
        'first_name' => 'Fara',
        'last_name' => 'Rabe',
        'email' => 'fara@example.test',
        'phone' => '+261 34 00 000 01',
        'birth_date' => '2001-02-02',
        'address' => 'Mahajanga',
        'academic_background' => 'Baccalauréat',
        'status' => 'submitted',
        'internal_notes' => 'Dossier à vérifier.',
        'privacy_accepted' => true,
        'submitted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get("/admin/applications/{$application->getRouteKey()}")
        ->assertOk()
        ->assertSee('Identité et contact')
        ->assertSee('Pièces justificatives')
        ->assertSee('Suivi administratif');

    $this->get("/admin/applications/{$application->getRouteKey()}/edit")
        ->assertOk()
        ->assertSee('Repères du dossier')
        ->assertSee('Traitement administratif');

    Livewire::test(ViewApplication::class, ['record' => $application->getRouteKey()])
        ->assertActionExists('edit')
        ->assertSee('Fara');

    Livewire::test(EditApplication::class, ['record' => $application->getRouteKey()])
        ->assertFormFieldExists('status')
        ->assertFormFieldExists('internal_notes')
        ->assertFormSet(['internal_notes' => 'Dossier à vérifier.']);
});
