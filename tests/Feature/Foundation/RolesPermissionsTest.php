<?php

namespace Tests\Feature\Foundation;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_role_grants_only_its_own_permissions(): void
    {
        $perm = Permission::create(['name' => 'customers.view', 'label' => 'x', 'group' => 'customers']);
        Permission::create(['name' => 'customers.manage', 'label' => 'y', 'group' => 'customers']);

        $role = Role::create(['name' => 'sales', 'label' => 'Sales']);
        $role->permissions()->attach($perm);

        $user = User::factory()->nonAdmin()->create();
        $user->assignRole('sales');

        $this->assertTrue($user->hasRole('sales'));
        $this->assertTrue($user->hasPermissionTo('customers.view'));
        $this->assertFalse($user->hasPermissionTo('customers.manage'));
    }

    public function test_a_user_with_no_roles_has_no_permissions(): void
    {
        $user = User::factory()->nonAdmin()->create();

        $this->assertFalse($user->hasPermissionTo('customers.view'));
        $this->assertFalse(Gate::forUser($user)->allows('customers.view'));
    }

    public function test_has_role_accepts_a_list(): void
    {
        Role::create(['name' => 'qa', 'label' => 'QA']);
        $user = User::factory()->nonAdmin()->create();
        $user->assignRole('qa');

        $this->assertTrue($user->hasRole(['developer', 'qa']));
        $this->assertFalse($user->hasRole(['developer', 'support']));
    }

    public function test_seeder_creates_every_catalog_permission_and_a_super_admin_role(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertSame(count(PermissionCatalog::all()), Permission::count());

        $superAdmin = Role::where('name', Role::SUPER_ADMIN)->firstOrFail();
        $this->assertSame(count(PermissionCatalog::all()), $superAdmin->permissions()->count());
    }

    public function test_seeder_attaches_super_admin_to_existing_admin_users(): void
    {
        $admin = User::factory()->create();           // role => admin (factory default)
        $plain = User::factory()->nonAdmin()->create();

        $this->seed(RolePermissionSeeder::class);

        $this->assertTrue($admin->fresh()->hasRole(Role::SUPER_ADMIN));
        $this->assertFalse($plain->fresh()->hasRole(Role::SUPER_ADMIN));
    }

    public function test_super_admin_passes_every_gate_via_gate_before(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::SUPER_ADMIN);

        // strip the role's permissions — Gate::before must still let them through
        Role::where('name', Role::SUPER_ADMIN)->first()->permissions()->detach();

        $this->assertTrue(Gate::forUser($superAdmin->fresh())->allows('deployments.authorize'));
    }
}
