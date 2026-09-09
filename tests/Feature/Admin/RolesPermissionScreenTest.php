<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesPermissionScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        return User::factory()->nonAdmin()->asRole(Role::SUPER_ADMIN)->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/roles')->assertRedirect('/admin/login');
    }

    public function test_a_role_assignment_grants_admin_area_access(): void
    {
        // isStaff() — a non-admin string role, but an assigned RBAC role.
        $this->actingAs(User::factory()->nonAdmin()->asRole('support')->create())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_a_user_without_roles_view_cannot_open_the_screen(): void
    {
        $this->actingAs(User::factory()->nonAdmin()->asRole('support')->create())
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_super_admin_sees_every_role(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('Sales Manager')
            ->assertSee('Deployment Manager');
    }

    public function test_the_super_admin_role_itself_cannot_be_edited(): void
    {
        $role = Role::where('name', Role::SUPER_ADMIN)->first();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.roles.edit', $role))
            ->assertForbidden();
    }

    public function test_a_manage_user_can_change_a_role_permission_set(): void
    {
        $qa = Role::where('name', 'qa')->first();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.roles.update', $qa), ['permissions' => ['requirements.view', 'developments.view']])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertEqualsCanonicalizing(['requirements.view', 'developments.view'], $qa->fresh()->permissions->pluck('name')->all());
    }

    public function test_update_rejects_an_unknown_permission(): void
    {
        $qa = Role::where('name', 'qa')->first();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.roles.update', $qa), ['permissions' => ['not.a.real.permission']])
            ->assertSessionHasErrors('permissions.0');
    }

    public function test_a_user_without_roles_manage_cannot_edit(): void
    {
        // developer has no roles.* permission
        $this->actingAs(User::factory()->nonAdmin()->asRole('developer')->create())
            ->get(route('admin.roles.edit', Role::where('name', 'qa')->first()))
            ->assertForbidden();
    }

    public function test_the_sidebar_hides_links_a_role_lacks(): void
    {
        $support = User::factory()->nonAdmin()->asRole('support')->create();

        $this->actingAs($support)->get('/admin/dashboard')
            ->assertDontSee('Role &amp; Permission Management', false)
            ->assertDontSee('Product Management');

        $this->actingAs($this->superAdmin())->get('/admin/dashboard')
            ->assertSee('Role &amp; Permission Management', false);
    }

    public function test_assigning_roles_from_the_user_edit_screen_syncs_the_pivot(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'editor',
                'roles' => ['finance-manager', 'support'],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertEqualsCanonicalizing(['finance-manager', 'support'], $target->fresh()->roles->pluck('name')->all());
    }

    public function test_the_last_super_admin_cannot_be_demoted(): void
    {
        $only = $this->superAdmin();

        $this->actingAs($only)
            ->put(route('admin.users.update', $only), [
                'name' => $only->name,
                'email' => $only->email,
                'role' => 'admin',
                'roles' => ['support'],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertTrue($only->fresh()->hasRole(Role::SUPER_ADMIN));
    }
}
