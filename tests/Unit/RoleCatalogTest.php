<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Authorization\RoleCatalog;
use Tests\TestCase;

class RoleCatalogTest extends TestCase
{
    public function test_defines_the_eight_srs_105_roles(): void
    {
        $this->assertSame([
            'super-admin', 'sales-manager', 'package-manager', 'finance-manager',
            'developer', 'qa', 'deployment-manager', 'support',
        ], RoleCatalog::names());
    }

    public function test_every_role_permission_exists_in_the_permission_catalog(): void
    {
        $known = PermissionCatalog::all();

        foreach (RoleCatalog::names() as $role) {
            foreach (RoleCatalog::permissionsFor($role) as $permission) {
                $this->assertContains($permission, $known, "{$role} references unknown permission {$permission}");
            }
        }
    }

    public function test_super_admin_resolves_to_every_permission(): void
    {
        $this->assertSame(PermissionCatalog::all(), RoleCatalog::permissionsFor(Role::SUPER_ADMIN));
    }

    public function test_no_non_super_role_gets_roles_or_settings_management(): void
    {
        foreach (RoleCatalog::names() as $role) {
            if ($role === Role::SUPER_ADMIN) {
                continue;
            }

            $permissions = RoleCatalog::permissionsFor($role);
            $this->assertNotContains('roles.manage', $permissions);
            $this->assertNotContains('settings.manage', $permissions);
        }
    }

    public function test_each_role_permission_list_is_deduplicated(): void
    {
        foreach (RoleCatalog::names() as $role) {
            $permissions = RoleCatalog::permissionsFor($role);
            $this->assertSame($permissions, array_values(array_unique($permissions)), "{$role} has duplicate permissions");
        }
    }
}
