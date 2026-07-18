<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        foreach (['view academic', 'create academic', 'edit academic', 'delete academic'] as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $legacySuperId = DB::table('roles')->where('guard_name', 'web')->where('name', 'super-admin')->value('id');
        $superadminId = DB::table('roles')->where('guard_name', 'web')->where('name', 'superadmin')->value('id');

        if ($legacySuperId && ! $superadminId) {
            DB::table('roles')->where('id', $legacySuperId)->update(['name' => 'superadmin', 'updated_at' => now()]);
            $superadminId = $legacySuperId;
            $legacySuperId = null;
        }

        $superadminId ??= DB::table('roles')->insertGetId([
            'name' => 'superadmin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $managerId = DB::table('roles')->where('guard_name', 'web')->where('name', 'manager')->value('id');
        $managerId ??= DB::table('roles')->insertGetId([
            'name' => 'manager',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($legacySuperId) {
            $this->copyRoleAssignments((int) $legacySuperId, (int) $superadminId);
        }

        $legacyRoleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereNotIn('name', ['superadmin', 'manager'])
            ->pluck('id');

        foreach ($legacyRoleIds as $legacyRoleId) {
            $this->copyRoleAssignments((int) $legacyRoleId, (int) $managerId);
        }

        $permissionIds = DB::table('permissions')->where('guard_name', 'web')->pluck('id');
        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $superadminId,
            ]);
        }

        $managerExcluded = [
            'manage roles', 'manage permissions', 'view users', 'create users', 'edit users',
            'delete users', 'view activity logs',
        ];
        $managerPermissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereNotIn('name', $managerExcluded)
            ->pluck('id');
        foreach ($managerPermissionIds as $permissionId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $managerId,
            ]);
        }

        $rolesToDelete = $legacyRoleIds->when($legacySuperId, fn ($ids) => $ids->push($legacySuperId))->unique();
        DB::table('model_has_roles')->whereIn('role_id', $rolesToDelete)->delete();
        DB::table('role_has_permissions')->whereIn('role_id', $rolesToDelete)->delete();
        DB::table('roles')->whereIn('id', $rolesToDelete)->delete();
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('guard_name', 'web')
            ->where('name', 'superadmin')
            ->update(['name' => 'super-admin', 'updated_at' => now()]);
    }

    private function copyRoleAssignments(int $sourceRoleId, int $targetRoleId): void
    {
        DB::table('model_has_roles')
            ->where('role_id', $sourceRoleId)
            ->get(['model_type', 'model_id'])
            ->each(function (object $assignment) use ($targetRoleId): void {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $targetRoleId,
                    'model_type' => $assignment->model_type,
                    'model_id' => $assignment->model_id,
                ]);
            });
    }
};
