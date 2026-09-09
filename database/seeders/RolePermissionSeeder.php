<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Authorization\RoleCatalog;
use Illuminate\Database\Seeder;

/**
 * Seeds every catalog permission and every SRS §105 role with its least-
 * privilege permission set (RBAC-001 + RBAC-002). Existing `role = 'admin'`
 * users are attached to `super-admin` so nothing loses access.
 *
 * Idempotent — safe to re-run after the catalogs grow.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        AuditLogger::withoutAuditing(fn () => $this->seed());
    }

    private function seed(): void
    {
        foreach (PermissionCatalog::groups() as $group => $permissions) {
            foreach ($permissions as $name => $label) {
                Permission::updateOrCreate(['name' => $name], ['label' => $label, 'group' => $group]);
            }
        }

        // Drop permissions that have been removed from the catalog (e.g. the
        // legacy `product.*` keys retired by PROD-002). Cascades clear the
        // `permission_role` pivot.
        Permission::whereNotIn('name', PermissionCatalog::all())->delete();

        foreach (RoleCatalog::roles() as $name => $definition) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $definition['label'], 'description' => $definition['description']],
            );
            $role->syncPermissions(RoleCatalog::permissionsFor($name));
        }

        User::where('role', 'admin')->get()->each(
            fn (User $user) => $user->assignRole(Role::SUPER_ADMIN)
        );
    }
}
