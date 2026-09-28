<?php

namespace Tests\Unit;

use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;

    private function seedApp(): array
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom-'.Str::random(8), 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);

        return [$app, $package];
    }

    private function makeLicense(array $overrides = []): License
    {
        [$app, $package] = $this->seedApp();
        $customer = LicenseCustomer::factory()->create();

        return License::create(array_merge([
            'license_customer_id' => $customer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => License::STATUS_ACTIVE,
        ], $overrides));
    }

    public function test_licenses_table_has_the_srs_59_lifecycle_columns(): void
    {
        foreach (['installation_id', 'issued_at', 'starts_at', 'expires_at', 'grace_period_days', 'next_payment_due', 'ownership_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('licenses', $column), "licenses.{$column} missing");
        }
    }

    public function test_statuses_constant_covers_the_full_srs_39_lifecycle(): void
    {
        $this->assertSame([
            'pending', 'active', 'payment_due', 'grace_period',
            'suspended', 'reactivated', 'completed', 'revoked', 'expired',
        ], License::STATUSES);
    }

    public function test_issued_at_and_starts_at_default_to_now_when_blank(): void
    {
        $license = $this->makeLicense();

        $this->assertNotNull($license->issued_at);
        $this->assertNotNull($license->starts_at);
        $this->assertTrue($license->starts_at->equalTo($license->issued_at));
    }

    public function test_an_explicit_issued_at_is_kept(): void
    {
        $when = now()->subDays(10)->startOfSecond();
        $license = $this->makeLicense(['issued_at' => $when, 'starts_at' => $when]);

        $this->assertTrue($license->fresh()->issued_at->equalTo($when));
    }

    public function test_grace_period_days_defaults_from_config(): void
    {
        config(['licensing.default_grace_hours' => 72]);

        $license = $this->makeLicense();

        $this->assertSame(3, $license->grace_period_days);
    }

    public function test_an_explicit_grace_period_days_is_kept(): void
    {
        $license = $this->makeLicense(['grace_period_days' => 14]);

        $this->assertSame(14, $license->fresh()->grace_period_days);
    }

    public function test_next_payment_due_casts_to_a_date(): void
    {
        $license = $this->makeLicense(['next_payment_due' => '2026-10-10']);

        $this->assertSame('2026-10-10', $license->fresh()->next_payment_due->toDateString());
    }

    /**
     * @dataProvider operationalStatusProvider
     */
    public function test_is_operational_matches_the_srs_45_46_lock_rules(string $status, bool $expectedOperational): void
    {
        $license = $this->makeLicense(['status' => $status]);

        $this->assertSame($expectedOperational, $license->isOperational());
    }

    /**
     * @return list<array{0: string, 1: bool}>
     */
    public static function operationalStatusProvider(): array
    {
        return [
            [License::STATUS_PENDING, false],
            [License::STATUS_ACTIVE, true],
            [License::STATUS_PAYMENT_DUE, true],
            [License::STATUS_GRACE_PERIOD, true],
            [License::STATUS_SUSPENDED, false],
            [License::STATUS_REACTIVATED, true],
            [License::STATUS_COMPLETED, true],
            [License::STATUS_REVOKED, false],
            [License::STATUS_EXPIRED, false],
        ];
    }

    public function test_is_usable_also_requires_a_future_expiry_when_one_is_set(): void
    {
        $future = $this->makeLicense(['status' => License::STATUS_ACTIVE, 'expires_at' => now()->addDay()]);
        $past = $this->makeLicense(['status' => License::STATUS_ACTIVE, 'expires_at' => now()->subDay()]);
        $noExpiry = $this->makeLicense(['status' => License::STATUS_COMPLETED, 'expires_at' => null]);

        $this->assertTrue($future->isUsable());
        $this->assertFalse($past->isUsable());
        $this->assertTrue($noExpiry->isUsable());
    }
}
