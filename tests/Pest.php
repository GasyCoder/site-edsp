<?php

use App\Models\AcademicLevel;
use App\Models\Mention;
use App\Models\Parcours;
use App\Models\ParcoursLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * @param  list<string>  $permissions
 */
function userWithPermissions(array $permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $user->givePermissionTo($permissions);

    return $user;
}

/** @return array<string, mixed> */
function academicApplicationData(): array
{
    $mention = Mention::query()->firstOrCreate(
        ['code' => 'TEST-DROIT'],
        ['nom' => 'Droit test', 'is_active' => true],
    );
    $level = AcademicLevel::query()->firstOrCreate(
        ['code' => 'TEST-L1'],
        ['nom' => 'Licence 1 test', 'ordre' => 1],
    );
    $parcours = Parcours::query()->firstOrCreate(
        ['code' => 'TEST-DROI'],
        ['mention_id' => $mention->id, 'nom' => 'Droit test'],
    );
    ParcoursLevel::query()->firstOrCreate(
        ['parcours_id' => $parcours->id, 'level_id' => $level->id],
        ['is_active' => true],
    );

    return [
        'civility' => 'madame',
        'gender' => 'feminin',
        'birth_place' => 'Mahajanga',
        'nationality' => 'Malagasy',
        'father_name' => 'Rakoto Jean',
        'mother_name' => 'Rasoa Marie',
        'parent_phone' => '+261 34 00 000 02',
        'academic_level_id' => $level->id,
        'mention_id' => $mention->id,
        'parcours_id' => $parcours->id,
        'last_diploma' => 'Baccalauréat',
        'graduation_year' => 2025,
        'previous_institution' => 'Lycée de Mahajanga',
    ];
}
