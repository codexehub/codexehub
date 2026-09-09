<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function auditor(): User
    {
        return User::factory()->asRole(Role::SUPER_ADMIN)->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.audit.index'))->assertRedirect(route('admin.login'));
    }

    public function test_a_user_without_audit_view_is_forbidden(): void
    {
        $user = User::factory()->nonAdmin()->asRole('qa')->create();

        $this->actingAs($user)->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_the_viewer_lists_entries(): void
    {
        $customer = Customer::factory()->create(['name' => 'Globex Audited']);

        $this->actingAs($this->auditor())->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('Customer #'.$customer->id)
            ->assertSee('created');
    }

    public function test_filter_by_action(): void
    {
        $customer = Customer::factory()->create();
        $customer->update(['company_name' => 'New Name Ltd']);

        $this->actingAs($this->auditor())->get(route('admin.audit.index', ['action' => 'updated']))
            ->assertOk()
            ->assertSee('company_name')
            ->assertDontSee('No audit entries');

        $this->actingAs($this->auditor())->get(route('admin.audit.index', ['action' => 'deleted']))
            ->assertSee('No audit entries');
    }

    public function test_filter_by_entity_type(): void
    {
        Customer::factory()->create(['name' => 'Only Customer']);

        $this->actingAs($this->auditor())->get(route('admin.audit.index', ['entity_type' => 'Customer']))
            ->assertOk()->assertSee('Only Customer', false);

        $this->actingAs($this->auditor())->get(route('admin.audit.index', ['entity_type' => 'Role']))
            ->assertSee('No audit entries');
    }

    public function test_filter_by_actor(): void
    {
        // A system row (no authenticated actor).
        Customer::factory()->create(['name' => 'System Corp']);

        $actor = $this->auditor();
        // A row with an actor (create a customer while acting as the auditor).
        $this->actingAs($actor)->post(route('admin.customers.store'), [
            'name' => 'Acted Corp', 'email' => 'acted@x.test', 'status' => 'prospect',
        ]);

        $this->actingAs($actor)->get(route('admin.audit.index', ['actor' => $actor->id]))
            ->assertOk()->assertSee('Acted Corp', false)->assertDontSee('System Corp', false);

        $this->actingAs($actor)->get(route('admin.audit.index', ['actor' => 'system']))
            ->assertOk()->assertSee('System Corp', false)->assertDontSee('Acted Corp', false);
    }

    public function test_filter_by_date_range(): void
    {
        $old = Customer::factory()->create(['name' => 'Ancient Corp']);
        AuditLog::where('entity_type', Customer::class)->where('entity_id', $old->id)
            ->update(['created_at' => now()->subDays(10)]);

        Customer::factory()->create(['name' => 'Fresh Corp']);

        // Only today's rows.
        $this->actingAs($this->auditor())
            ->get(route('admin.audit.index', ['date_from' => now()->subDay()->toDateString()]))
            ->assertOk()->assertSee('Fresh Corp', false)->assertDontSee('Ancient Corp', false);

        // Only rows up to a week ago.
        $this->actingAs($this->auditor())
            ->get(route('admin.audit.index', ['date_to' => now()->subWeek()->toDateString()]))
            ->assertOk()->assertSee('Ancient Corp', false)->assertDontSee('Fresh Corp', false);
    }
}
