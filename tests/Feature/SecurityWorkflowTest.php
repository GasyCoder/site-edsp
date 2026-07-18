<?php

use App\Actions\PublishContent;
use App\Enums\ContentStatus;
use App\Models\AdmissionCampaign;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\User;
use App\Services\PublicationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @param  list<string>  $permissions
 */
function securityRoleUser(string $roleName, array $permissions): User
{
    $role = Role::findOrCreate($roleName, 'web');

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

/** @return array{AdmissionCampaign, Program} */
function securityOpenCampaign(array $requiredDocuments = []): array
{
    $program = Program::query()->create([
        'title' => 'Droit privé — sécurité',
        'slug' => 'droit-prive-securite',
        'level' => 'Licence',
        'description' => 'Formation juridique',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $campaign = AdmissionCampaign::query()->create([
        'title' => 'Campagne sécurité',
        'academic_year' => '2026-2027',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addDay(),
        'required_documents' => $requiredDocuments,
        'status' => 'published',
        'is_visible' => true,
    ]);
    $campaign->programs()->attach($program);

    return [$campaign, $program];
}

test('publication permissions separate an editor from a publisher', function (): void {
    $page = Page::query()->create([
        'title' => 'Page à relire',
        'slug' => 'page-a-relire',
        'status' => 'draft',
        'template' => 'default',
    ]);
    $editor = securityRoleUser('security-editor', ['access admin', 'view pages', 'edit pages']);
    $publisher = securityRoleUser('security-publisher', ['access admin', 'view pages', 'edit pages', 'publish pages']);

    expect(Gate::forUser($editor)->allows('update', $page))->toBeTrue()
        ->and(Gate::forUser($editor)->allows('publish', $page))->toBeFalse()
        ->and(Gate::forUser($publisher)->allows('publish', $page))->toBeTrue();

    $this->actingAs($editor);
    expect(fn () => app(PublishContent::class)->handle($page, $editor->id))
        ->toThrow(AuthorizationException::class);

    $this->actingAs($publisher);
    app(PublishContent::class)->handle($page->fresh(), $publisher->id);

    expect($page->fresh()->status)->toBe(ContentStatus::Published)
        ->and($page->fresh()->published_at)->not->toBeNull();
});

test('an administrator cannot alter or delete the last super administrator', function (): void {
    $administrator = securityRoleUser('security-administrator', [
        'access admin',
        'view users',
        'edit users',
        'delete users',
    ]);
    $superAdministrator = securityRoleUser('superadmin', []);

    expect(User::role('superadmin')->count())->toBe(1)
        ->and(Gate::forUser($administrator)->allows('update', $superAdministrator))->toBeFalse()
        ->and(Gate::forUser($administrator)->allows('delete', $superAdministrator))->toBeFalse();

    $superAdministrator->removeRole('superadmin');

    expect($superAdministrator->fresh()->hasRole('superadmin'))->toBeTrue();
});

test('candidate documents reject a real mime type that does not match the extension', function (): void {
    Storage::fake('private');
    Queue::fake();
    [$campaign, $program] = securityOpenCampaign();

    $this->from(route('applications.create'))->post(route('applications.store'), [
        'admission_campaign_id' => $campaign->id,
        'program_id' => $program->id,
        'first_name' => 'Aina',
        'last_name' => 'Rakoto',
        'email' => 'aina.security@example.test',
        'phone' => '+261 34 00 000 00',
        'birth_date' => '2000-01-10',
        'address' => 'Mahajanga',
        'academic_background' => 'Baccalauréat',
        'privacy_accepted' => '1',
        'documents' => [
            'identity' => UploadedFile::fake()->create('identite.pdf', 120, 'image/jpeg'),
        ],
        'website' => '',
    ])->assertRedirect(route('applications.create'))
        ->assertSessionHasErrors('documents.identity');

    $this->assertDatabaseCount('applications', 0);
    $this->assertDatabaseCount('application_documents', 0);
});

test('redirect validation prevents indirect active cycles', function (): void {
    Redirect::query()->create([
        'source_path' => '/ancienne-a',
        'destination_url' => '/ancienne-b',
        'status_code' => 301,
        'is_active' => true,
    ]);
    Redirect::query()->create([
        'source_path' => '/ancienne-b',
        'destination_url' => '/ancienne-c',
        'status_code' => 301,
        'is_active' => true,
    ]);

    expect(fn () => Redirect::query()->create([
        'source_path' => '/ancienne-c',
        'destination_url' => '/ancienne-a',
        'status_code' => 301,
        'is_active' => true,
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('redirects', ['source_path' => '/ancienne-c']);
});

test('the scheduler publishes only content whose publication date is due', function (): void {
    $this->travelTo('2026-07-18 10:00:00', function (): void {
        $dueAt = now()->subMinute();
        $futureAt = now()->addMinute();

        $duePage = Page::query()->create([
            'title' => 'Page programmée',
            'slug' => 'page-programmee',
            'status' => 'scheduled',
            'template' => 'default',
            'published_at' => $dueAt,
        ]);
        $futurePage = Page::query()->create([
            'title' => 'Page future',
            'slug' => 'page-future',
            'status' => 'scheduled',
            'template' => 'default',
            'published_at' => $futureAt,
        ]);
        $dueNews = News::query()->create([
            'title' => 'Actualité programmée',
            'slug' => 'actualite-programmee-securite',
            'excerpt' => 'Actualité arrivée à échéance.',
            'content' => '<p>Contenu</p>',
            'status' => 'scheduled',
            'published_at' => $dueAt,
        ]);
        $dueProgram = Program::query()->create([
            'title' => 'Formation programmée',
            'slug' => 'formation-programmee-securite',
            'level' => 'Master',
            'description' => 'Formation arrivée à échéance.',
            'status' => 'scheduled',
            'published_at' => $dueAt,
        ]);
        $dueGallery = Gallery::query()->create([
            'title' => 'Galerie programmée',
            'slug' => 'galerie-programmee-securite',
            'status' => 'scheduled',
            'is_visible' => true,
            'published_at' => $dueAt,
        ]);
        $dueCampaign = AdmissionCampaign::query()->create([
            'title' => 'Campagne programmée',
            'academic_year' => '2026-2027',
            'opens_at' => $dueAt,
            'closes_at' => now()->addMonth(),
            'status' => 'scheduled',
            'is_visible' => true,
        ]);
        $futureCampaign = AdmissionCampaign::query()->create([
            'title' => 'Campagne future',
            'academic_year' => '2027-2028',
            'opens_at' => $futureAt,
            'closes_at' => now()->addMonths(2),
            'status' => 'scheduled',
            'is_visible' => true,
        ]);

        expect(app(PublicationService::class)->publishDue())->toBe(5)
            ->and($duePage->fresh()->status)->toBe(ContentStatus::Published)
            ->and($dueNews->fresh()->status)->toBe('published')
            ->and($dueProgram->fresh()->status)->toBe('published')
            ->and($dueGallery->fresh()->status)->toBe('published')
            ->and($dueCampaign->fresh()->status)->toBe('published')
            ->and($futurePage->fresh()->status)->toBe(ContentStatus::Scheduled)
            ->and($futureCampaign->fresh()->status)->toBe('scheduled');
    });
});
