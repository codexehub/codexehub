<?php

namespace Tests\Feature\Foundation;

use App\Models\Concerns\Auditable;
use App\Models\Installation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * INST-001 — the SRS §57 `installations` table (Phase 6 — Vendor Client &
 * Sync). Schema + Auditable rollout only; register/verify/heartbeat endpoints
 * are INST-002.
 */
class InstallationCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_installations_table_has_the_srs_columns(): void
    {
        foreach ([
            'customer_id', 'product_id', 'licensed_app_package_id', 'installation_id',
            'domain', 'environment', 'vendor_client_version', 'software_version', 'status',
            'last_heartbeat_at', 'last_verified_at', 'last_deployed_at',
            'created_at', 'updated_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('installations', $column), "installations.{$column} missing");
        }
    }

    public function test_installation_is_auditable(): void
    {
        $this->assertContains(Auditable::class, class_uses_recursive(Installation::class));

        $installation = Installation::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'entity_type' => Installation::class,
            'entity_id' => $installation->id,
        ]);
    }

    public function test_installation_is_filterable_in_the_audit_viewer(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $auditor = User::factory()->asRole(Role::SUPER_ADMIN)->create();

        $installation = Installation::factory()->create();

        $this->actingAs($auditor)
            ->get(route('admin.audit.index', ['entity_type' => 'Installation']))
            ->assertOk()
            ->assertSee('Installation #'.$installation->id)
            ->assertSee('created');
    }
}
