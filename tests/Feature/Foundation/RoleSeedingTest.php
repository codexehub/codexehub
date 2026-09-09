<?php

namespace Tests\Feature\Foundation;

use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\RoleCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleSeedingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_eight_roles_are_seeded_with_their_permission_sets(): void
    {
        $this->assertSame(8, Role::count());

        foreach (RoleCatalog::names() as $name) {
            $role = Role::where('name', $name)->firstOrFail();
            $this->assertSame(
                count(RoleCatalog::permissionsFor($name)),
                $role->permissions()->count(),
                "{$name} permission count",
            );
        }
    }

    public function test_re_running_the_seeder_is_idempotent(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertSame(8, Role::count());
        $this->assertSame(
            count(RoleCatalog::permissionsFor('sales-manager')),
            Role::where('name', 'sales-manager')->first()->permissions()->count(),
        );
    }

    public function test_least_privilege_boundaries_hold(): void
    {
        $cases = [
            'sales-manager' => ['can' => 'customers.manage', 'cannot' => 'payments.approve'],
            'package-manager' => ['can' => 'packages.manage', 'cannot' => 'offers.send'],
            'finance-manager' => ['can' => 'payments.approve', 'cannot' => 'packages.manage'],
            'developer' => ['can' => 'developments.manage', 'cannot' => 'releases.publish'],
            'qa' => ['can' => 'releases.manage', 'cannot' => 'releases.publish'],
            'deployment-manager' => ['can' => 'deployments.authorize', 'cannot' => 'releases.manage'],
            'support' => ['can' => 'requirements.manage', 'cannot' => 'customers.manage'],
        ];

        foreach ($cases as $role => $expect) {
            $user = User::factory()->nonAdmin()->asRole($role)->create();

            $this->assertTrue(Gate::forUser($user)->allows($expect['can']), "{$role} should allow {$expect['can']}");
            $this->assertFalse(Gate::forUser($user)->allows($expect['cannot']), "{$role} should deny {$expect['cannot']}");
        }
    }

    public function test_a_non_super_role_does_not_pass_gate_before(): void
    {
        $user = User::factory()->nonAdmin()->asRole('support')->create();

        $this->assertFalse(Gate::forUser($user)->allows('roles.manage'));
        $this->assertFalse(Gate::forUser($user)->allows('settings.manage'));
    }
}
