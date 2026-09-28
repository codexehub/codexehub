<?php

namespace Tests\Unit\Support\VendorApi;

use App\Support\VendorApi\VendorApiErrorCode;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * APISEC-001 / SRS §72 — a technical error must never be classified as a
 * business (license) state, since the Vendor Client branches on this.
 */
class VendorApiErrorCodeTest extends TestCase
{
    public static function businessCodes(): array
    {
        return [
            [VendorApiErrorCode::LicenseSuspended],
            [VendorApiErrorCode::LicenseExpired],
            [VendorApiErrorCode::PaymentDue],
            [VendorApiErrorCode::LicenseRevoked],
        ];
    }

    public static function technicalCodes(): array
    {
        return [
            [VendorApiErrorCode::ApiTimeout],
            [VendorApiErrorCode::NetworkError],
            [VendorApiErrorCode::InvalidResponse],
            [VendorApiErrorCode::ServerError],
            [VendorApiErrorCode::SignatureError],
            [VendorApiErrorCode::ValidationError],
            [VendorApiErrorCode::NotFound],
        ];
    }

    #[DataProvider('businessCodes')]
    public function test_business_codes_classify_as_business(VendorApiErrorCode $code): void
    {
        $this->assertSame('business', $code->classification());
        $this->assertTrue($code->isBusiness());
        $this->assertFalse($code->isTechnical());
    }

    #[DataProvider('technicalCodes')]
    public function test_technical_codes_classify_as_technical(VendorApiErrorCode $code): void
    {
        $this->assertSame('technical', $code->classification());
        $this->assertTrue($code->isTechnical());
        $this->assertFalse($code->isBusiness());
    }
}
