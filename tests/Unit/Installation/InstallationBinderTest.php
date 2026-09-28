<?php

namespace Tests\Unit\Installation;

use App\Models\Installation;
use App\Models\Product;
use App\Services\Installation\InstallationBinder;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesTestLicenses;
use Tests\TestCase;

/**
 * INST-002 — `App\Services\Installation\InstallationBinder`.
 */
class InstallationBinderTest extends TestCase
{
    use MakesTestLicenses;
    use RefreshDatabase;

    private InstallationBinder $binder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->binder = new InstallationBinder;
    }

    public function test_registering_a_new_installation_id_creates_an_active_row_and_binds_the_license(): void
    {
        $license = $this->makeLicense('active');

        $result = $this->binder->register(
            license: $license,
            installationId: 'INS-NEW00001',
            domain: 'shop.example.com',
            environment: null,
            vendorClientVersion: '1.2.0',
            softwareVersion: '2.0.0',
        );

        $this->assertFalse($result['domain_changed']);
        $installation = $result['installation'];
        $this->assertSame(Installation::STATUS_ACTIVE, $installation->status);
        $this->assertSame('shop.example.com', $installation->domain);
        $this->assertSame(Installation::ENVIRONMENT_PRODUCTION, $installation->environment);
        $this->assertSame($license->customer->customer_id, $installation->customer_id);
        $this->assertSame($license->licensedApp->product_id, $installation->product_id);
        $this->assertSame($license->licensed_app_package_id, $installation->licensed_app_package_id);
        $this->assertSame('INS-NEW00001', $license->fresh()->installation_id);
    }

    public function test_re_registering_with_the_same_domain_does_not_change_status(): void
    {
        $license = $this->makeLicense('active');
        $first = $this->binder->register($license, 'INS-SAME0001', 'shop.example.com', null, '1.0.0', '1.0.0');
        $this->assertSame(Installation::STATUS_ACTIVE, $first['installation']->status);

        $second = $this->binder->register($license->fresh(), 'INS-SAME0001', 'shop.example.com', null, '1.1.0', null);

        $this->assertFalse($second['domain_changed']);
        $this->assertSame(Installation::STATUS_ACTIVE, $second['installation']->status);
        $this->assertSame('1.1.0', $second['installation']->vendor_client_version);
        // software_version left null on the second call — the prior value is kept.
        $this->assertSame('1.0.0', $second['installation']->software_version);
    }

    public function test_re_registering_with_a_different_domain_flips_status_to_pending(): void
    {
        $license = $this->makeLicense('active');
        $this->binder->register($license, 'INS-DOMCHG01', 'old-domain.example.com', null, '1.0.0', null);

        $result = $this->binder->register($license->fresh(), 'INS-DOMCHG01', 'new-domain.example.com', null, '1.0.0', null);

        $this->assertTrue($result['domain_changed']);
        $this->assertSame(Installation::STATUS_PENDING, $result['installation']->status);
        $this->assertSame('new-domain.example.com', $result['installation']->domain);
    }

    public function test_is_license_bound_elsewhere_detects_a_conflicting_installation(): void
    {
        $license = $this->makeLicense('active', ['installation_id' => 'INS-OTHER001']);

        $this->assertTrue($this->binder->isLicenseBoundElsewhere($license, 'INS-DIFFERENT'));
        $this->assertFalse($this->binder->isLicenseBoundElsewhere($license, 'INS-OTHER001'));
        $this->assertFalse($this->binder->isLicenseBoundElsewhere($this->makeLicense('active'), 'INS-ANY'));
    }

    public function test_is_installation_bound_to_another_customer_detects_a_mismatch(): void
    {
        $licenseA = $this->makeLicense('active');
        $this->binder->register($licenseA, 'INS-CUSTCHK1', 'shop-a.example.com', null, null, null);
        $installation = Installation::where('installation_id', 'INS-CUSTCHK1')->first();

        $licenseB = $this->makeLicense('active');

        $this->assertTrue($this->binder->isInstallationBoundToAnotherCustomer($installation, $licenseB));
        $this->assertFalse($this->binder->isInstallationBoundToAnotherCustomer($installation, $licenseA->fresh()));
    }

    public function test_business_error_for_is_only_set_when_suspended(): void
    {
        $active = Installation::factory()->create(['status' => Installation::STATUS_ACTIVE]);
        $pending = Installation::factory()->pending()->create();
        $suspended = Installation::factory()->suspended()->create();

        $this->assertNull($this->binder->businessErrorFor($active));
        $this->assertNull($this->binder->businessErrorFor($pending));
        $this->assertSame(VendorApiErrorCode::InstallationSuspended, $this->binder->businessErrorFor($suspended));
    }

    public function test_verify_activates_a_pending_installation_and_refreshes_last_verified_at(): void
    {
        $installation = Installation::factory()->pending()->create();

        $revalidated = $this->binder->verify($installation);

        $this->assertTrue($revalidated);
        $this->assertSame(Installation::STATUS_ACTIVE, $installation->fresh()->status);
        $this->assertNotNull($installation->fresh()->last_verified_at);
    }

    public function test_verify_on_an_already_active_installation_just_refreshes_the_timestamp(): void
    {
        $installation = Installation::factory()->create(['status' => Installation::STATUS_ACTIVE, 'last_verified_at' => null]);

        $revalidated = $this->binder->verify($installation);

        $this->assertFalse($revalidated);
        $this->assertSame(Installation::STATUS_ACTIVE, $installation->fresh()->status);
        $this->assertNotNull($installation->fresh()->last_verified_at);
    }

    public function test_heartbeat_updates_last_heartbeat_at_and_optional_software_version_without_touching_status(): void
    {
        $installation = Installation::factory()->pending()->create(['software_version' => '1.0.0']);

        $this->binder->heartbeat($installation, '1.1.0');

        $fresh = $installation->fresh();
        $this->assertSame(Installation::STATUS_PENDING, $fresh->status);
        $this->assertSame('1.1.0', $fresh->software_version);
        $this->assertNotNull($fresh->last_heartbeat_at);
    }

    public function test_heartbeat_without_a_software_version_keeps_the_existing_one(): void
    {
        $installation = Installation::factory()->create(['software_version' => '1.0.0']);

        $this->binder->heartbeat($installation, null);

        $this->assertSame('1.0.0', $installation->fresh()->software_version);
    }

    public function test_to_payload_includes_the_expected_keys(): void
    {
        $license = $this->makeLicense('active');
        $license->licensedApp->update(['product_id' => Product::factory()->create()->id]);
        $result = $this->binder->register($license->fresh(), 'INS-PAYLOAD1', 'shop.example.com', null, '1.0.0', '2.0.0');

        $payload = $this->binder->toPayload($result['installation']);

        foreach ([
            'installation_id', 'status', 'domain', 'environment', 'customer', 'product', 'package',
            'vendor_client_version', 'software_version', 'last_heartbeat_at', 'last_verified_at',
        ] as $key) {
            $this->assertArrayHasKey($key, $payload);
        }

        $this->assertSame('INS-PAYLOAD1', $payload['installation_id']);
        $this->assertIsArray($payload['customer']);
        $this->assertIsArray($payload['product']);
    }
}
