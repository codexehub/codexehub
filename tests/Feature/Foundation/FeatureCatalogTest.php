<?php

namespace Tests\Feature\Foundation;

use App\Models\Concerns\Auditable;
use App\Models\Feature;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * FEAT-001 — the reusable feature catalog (SRS §7, §52). Distinct table; not a
 * fold-in of the marketing `product_features` / `service_features` repeaters.
 */
class FeatureCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_features_table_has_the_srs_columns(): void
    {
        foreach ([
            'name', 'key', 'type', 'description', 'default_price',
            'version', 'status', 'dependencies', 'config_schema', 'documentation',
            'created_at', 'updated_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('features', $column), "features.{$column} missing");
        }
    }

    public function test_feature_is_auditable(): void
    {
        $this->assertContains(Auditable::class, class_uses_recursive(Feature::class));

        $feature = Feature::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'entity_type' => Feature::class,
            'entity_id' => $feature->id,
        ]);
    }

    public function test_feature_is_filterable_in_the_audit_viewer(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $auditor = User::factory()->asRole(Role::SUPER_ADMIN)->create();

        $feature = Feature::factory()->create(['name' => 'Catalog SEO Widget']);

        $this->actingAs($auditor)
            ->get(route('admin.audit.index', ['entity_type' => 'Feature']))
            ->assertOk()
            ->assertSee('Feature #'.$feature->id)
            ->assertSee('created');
    }
}
