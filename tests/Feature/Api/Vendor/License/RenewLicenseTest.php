<?php

namespace Tests\Feature\Api\Vendor\License;

use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\MakesTestLicenses;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * LIC-CP-002 — `POST /api/vendor/v1/licenses/renew`. Does not process
 * payments itself; only closes the gap between "payment already recorded
 * elsewhere" (`next_payment_due` moved to the future) and the license's
 * stored status catching up, ahead of LIC-001's scheduled job (Phase 9).
 */
class RenewLicenseTest extends TestCase
{
    use MakesTestLicenses;
    use RefreshDatabase;
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    private function renew(License $license): TestResponse
    {
        return $this->postJson('/api/vendor/v1/licenses/renew', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]));
    }

    public function test_payment_due_with_a_future_next_payment_due_is_renewed_to_active(): void
    {
        $license = $this->makeLicense(License::STATUS_PAYMENT_DUE, ['next_payment_due' => now()->addWeek()]);

        $this->renew($license)->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'LICENSE_RENEWAL_CHECKED',
        ])->assertJsonPath('data.renewed', true)
            ->assertJsonPath('data.license_status', License::STATUS_ACTIVE);

        $this->assertSame(License::STATUS_ACTIVE, $license->fresh()->status);
    }

    public function test_grace_period_with_a_future_next_payment_due_is_renewed_to_active(): void
    {
        $license = $this->makeLicense(License::STATUS_GRACE_PERIOD, ['next_payment_due' => now()->addDays(3)]);

        $this->renew($license)->assertJsonPath('data.renewed', true);

        $this->assertSame(License::STATUS_ACTIVE, $license->fresh()->status);
    }

    public function test_payment_due_with_no_future_next_payment_due_is_not_renewed(): void
    {
        $license = $this->makeLicense(License::STATUS_PAYMENT_DUE, ['next_payment_due' => null]);

        $this->renew($license)->assertStatus(200)->assertJsonPath('data.renewed', false);

        $this->assertSame(License::STATUS_PAYMENT_DUE, $license->fresh()->status);
    }

    public function test_payment_due_with_a_past_next_payment_due_is_not_renewed(): void
    {
        $license = $this->makeLicense(License::STATUS_PAYMENT_DUE, ['next_payment_due' => now()->subDay()]);

        $this->renew($license)->assertJsonPath('data.renewed', false);

        $this->assertSame(License::STATUS_PAYMENT_DUE, $license->fresh()->status);
    }

    public function test_an_already_active_license_reports_no_renewal_needed(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->renew($license)->assertStatus(200)->assertJsonPath('data.renewed', false);
    }

    public function test_a_suspended_license_is_rejected_and_not_renewed_even_with_a_future_next_payment_due(): void
    {
        $license = $this->makeLicense(License::STATUS_SUSPENDED, ['next_payment_due' => now()->addWeek()]);

        $this->renew($license)->assertStatus(403)->assertJson(['success' => false, 'code' => 'LICENSE_SUSPENDED']);

        $this->assertSame(License::STATUS_SUSPENDED, $license->fresh()->status);
    }

    public function test_an_unknown_license_key_returns_not_found(): void
    {
        $this->postJson('/api/vendor/v1/licenses/renew', $this->signedVendorPayload([
            'license_key' => 'DOES-NOT-EXIST',
        ]))->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }

    public function test_renewal_updates_last_verified_at(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->renew($license);

        $this->assertNotNull($license->fresh()->last_verified_at);
    }
}
