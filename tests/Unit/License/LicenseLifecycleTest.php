<?php

namespace Tests\Unit\License;

use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use App\Services\License\LicenseLifecycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class LicenseLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function makeLicense(string $status): License
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom', 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);
        $customer = LicenseCustomer::factory()->create();

        return License::create([
            'license_customer_id' => $customer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => $status,
        ]);
    }

    /**
     * @dataProvider validTransitionProvider
     */
    public function test_valid_transitions_are_allowed_and_persisted(string $from, string $to): void
    {
        $license = $this->makeLicense($from);
        $lifecycle = new LicenseLifecycle;

        $this->assertTrue($lifecycle->canTransition($from, $to));

        $lifecycle->transition($license, $to);

        $this->assertSame($to, $license->fresh()->status);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function validTransitionProvider(): array
    {
        return [
            [License::STATUS_PENDING, License::STATUS_ACTIVE],
            [License::STATUS_PENDING, License::STATUS_REVOKED],
            [License::STATUS_ACTIVE, License::STATUS_PAYMENT_DUE],
            [License::STATUS_ACTIVE, License::STATUS_SUSPENDED],
            [License::STATUS_ACTIVE, License::STATUS_COMPLETED],
            [License::STATUS_ACTIVE, License::STATUS_EXPIRED],
            [License::STATUS_PAYMENT_DUE, License::STATUS_ACTIVE],
            [License::STATUS_PAYMENT_DUE, License::STATUS_GRACE_PERIOD],
            [License::STATUS_GRACE_PERIOD, License::STATUS_SUSPENDED],
            [License::STATUS_SUSPENDED, License::STATUS_REACTIVATED],
            [License::STATUS_SUSPENDED, License::STATUS_ACTIVE],
            [License::STATUS_REACTIVATED, License::STATUS_ACTIVE],
            [License::STATUS_EXPIRED, License::STATUS_ACTIVE],
            [License::STATUS_COMPLETED, License::STATUS_REVOKED],
        ];
    }

    /**
     * @dataProvider invalidTransitionProvider
     */
    public function test_invalid_transitions_are_rejected(string $from, string $to): void
    {
        $license = $this->makeLicense($from);
        $lifecycle = new LicenseLifecycle;

        $this->assertFalse($lifecycle->canTransition($from, $to));

        $this->expectException(InvalidArgumentException::class);
        $lifecycle->transition($license, $to);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function invalidTransitionProvider(): array
    {
        return [
            'reactivating a revoked license' => [License::STATUS_REVOKED, License::STATUS_ACTIVE],
            'leaving the completed state to active' => [License::STATUS_COMPLETED, License::STATUS_ACTIVE],
            'skipping straight from pending to grace period' => [License::STATUS_PENDING, License::STATUS_GRACE_PERIOD],
            'same-state no-op' => [License::STATUS_ACTIVE, License::STATUS_ACTIVE],
        ];
    }

    public function test_transition_rejects_an_unknown_status(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->expectException(InvalidArgumentException::class);
        (new LicenseLifecycle)->transition($license, 'not_a_real_status');
    }

    public function test_terminal_states_have_no_further_transitions_except_revoked_from_completed(): void
    {
        $lifecycle = new LicenseLifecycle;

        $this->assertSame([], $lifecycle->transitions(License::STATUS_REVOKED));
        $this->assertSame([License::STATUS_REVOKED], $lifecycle->transitions(License::STATUS_COMPLETED));
    }
}
