<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'access admin',
            'view pages', 'create pages', 'edit pages', 'publish pages', 'delete pages',
            'view news', 'create news', 'edit news', 'publish news', 'delete news',
            'view programs', 'create programs', 'edit programs', 'publish programs', 'delete programs',
            'view team', 'create team', 'edit team', 'delete team',
            'view galleries', 'create galleries', 'edit galleries', 'delete galleries',
            'view documents', 'create documents', 'edit documents', 'delete documents',
            'view partners', 'create partners', 'edit partners', 'delete partners',
            'view testimonials', 'create testimonials', 'edit testimonials', 'delete testimonials',
            'view campaigns', 'create campaigns', 'edit campaigns', 'delete campaigns',
            'view applications', 'create applications', 'edit applications', 'delete applications',
            'change application status', 'download application documents', 'export applications',
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            'view newsletter subscribers', 'create newsletter subscribers', 'edit newsletter subscribers', 'delete newsletter subscribers',
            'import newsletter subscribers', 'export newsletter subscribers',
            'view newsletter campaigns', 'create newsletter campaigns', 'edit newsletter campaigns', 'delete newsletter campaigns',
            'send newsletter campaigns',
            'view media', 'upload media', 'edit media', 'delete media',
            'view settings', 'create settings', 'edit settings', 'delete settings',
            'view revisions', 'restore revisions', 'delete revisions',
            'view activity logs',
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',
            'view users', 'create users', 'edit users', 'delete users',
            'view academic', 'create academic', 'edit academic', 'delete academic',
            'manage roles', 'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $all = Permission::query()->where('guard_name', 'web')->pluck('name')->all();
        $this->consolidateLegacyRoles();

        $roles = [
            'superadmin' => $all,
            'manager' => array_values(array_diff($all, [
                'manage roles',
                'manage permissions',
                'view users',
                'create users',
                'edit users',
                'delete users',
                'view activity logs',
            ])),
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name, 'web')->syncPermissions($rolePermissions);
        }

        Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', array_keys($roles))
            ->get()
            ->each->delete();

        $this->seedSuperAdministrator();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function consolidateLegacyRoles(): void
    {
        $legacySuper = Role::query()->where('guard_name', 'web')->where('name', 'super-admin')->first();
        $superadmin = Role::findOrCreate('superadmin', 'web');
        $manager = Role::findOrCreate('manager', 'web');

        if ($legacySuper) {
            $legacySuper->users()->get()->each->assignRole($superadmin);
        }

        User::query()
            ->whereHas('roles', fn ($query) => $query
                ->where('guard_name', 'web')
                ->whereNotIn('name', ['super-admin', 'superadmin']))
            ->get()
            ->each->assignRole($manager);
    }

    private function seedSuperAdministrator(): void
    {
        $email = env('EDSP_ADMIN_EMAIL');
        $password = env('EDSP_ADMIN_PASSWORD');

        if (blank($email) && blank($password)) {
            return;
        }

        if (blank($email) || blank($password) || Str::length((string) $password) < 12) {
            throw new RuntimeException('EDSP_ADMIN_EMAIL et un EDSP_ADMIN_PASSWORD d’au moins 12 caractères sont requis ensemble.');
        }

        $user = User::query()->firstOrCreate(
            ['email' => Str::lower((string) $email)],
            [
                'name' => env('EDSP_ADMIN_NAME', 'Administration EDSP'),
                'password' => (string) $password,
                'email_verified_at' => now(),
            ],
        );
        $user->syncRoles(['superadmin']);
    }
}
