<?php

namespace Tests\Feature\Foundation;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_logger_records_actor_action_and_entity(): void
    {
        $actor = User::factory()->create(['name' => 'Ada']);
        $role = Role::create(['name' => 'x', 'label' => 'X']);

        app(AuditLogger::class)->log('role.tested', $role, ['a' => 1], ['a' => 2], $actor);

        $log = AuditLog::where('action', 'role.tested')->firstOrFail();
        $this->assertSame($actor->id, $log->actor_id);
        $this->assertSame('Ada', $log->actor_label);
        $this->assertSame(Role::class, $log->entity_type);
        $this->assertSame($role->id, $log->entity_id);
        $this->assertSame(['a' => 1], $log->old_values);
        $this->assertSame(['a' => 2], $log->new_values);
    }

    public function test_without_auditing_suppresses_writes(): void
    {
        AuditLogger::withoutAuditing(fn () => Role::create(['name' => 'quiet', 'label' => 'Quiet']));

        $this->assertSame(0, AuditLog::count());
    }

    public function test_auditable_records_create_update_delete(): void
    {
        $role = Role::create(['name' => 'r', 'label' => 'R']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'entity_id' => $role->id]);

        $role->update(['label' => 'Renamed']);
        $updated = AuditLog::where('action', 'updated')->where('entity_id', $role->id)->firstOrFail();
        $this->assertSame(['label' => 'R'], $updated->old_values);
        $this->assertSame(['label' => 'Renamed'], $updated->new_values);

        $role->delete();
        $this->assertDatabaseHas('audit_logs', ['action' => 'deleted', 'entity_id' => $role->id]);
    }

    public function test_a_touch_only_change_is_not_audited(): void
    {
        $role = Role::create(['name' => 'r', 'label' => 'R']);
        AuditLog::query()->delete();

        $role->touch();

        $this->assertSame(0, AuditLog::where('action', 'updated')->count());
    }

    public function test_the_seeder_does_not_write_audit_rows(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertSame(0, AuditLog::count());
    }

    public function test_role_permission_change_is_audited_from_the_admin_screen(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->asRole(Role::SUPER_ADMIN)->create();
        $qa = Role::where('name', 'qa')->first();

        $this->actingAs($admin)
            ->put(route('admin.roles.update', $qa), ['permissions' => ['requirements.view']]);

        $log = AuditLog::where('action', 'role.permissions_updated')->firstOrFail();
        $this->assertSame($qa->id, $log->entity_id);
        $this->assertSame(['requirements.view'], $log->new_values['permissions']);
        $this->assertContains('developments.view', $log->old_values['permissions']);

        // no-op update writes nothing new
        AuditLog::query()->delete();
        $this->actingAs($admin)->put(route('admin.roles.update', $qa), ['permissions' => ['requirements.view']]);
        $this->assertSame(0, AuditLog::where('action', 'role.permissions_updated')->count());
    }

    public function test_user_role_assignment_is_audited(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->asRole(Role::SUPER_ADMIN)->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'editor',
            'roles' => ['support'],
        ]);

        $log = AuditLog::where('action', 'user.roles_updated')->firstOrFail();
        $this->assertSame($target->id, $log->entity_id);
        $this->assertSame(['support'], $log->new_values['roles']);
        $this->assertSame($admin->id, $log->actor_id);
    }
}
