<?php

namespace Tests\Unit\Support\VendorApi;

use App\Support\VendorApi\VendorApiSigner;
use Tests\TestCase;

class VendorApiSignerTest extends TestCase
{
    public function test_sign_and_verify_round_trip(): void
    {
        $payload = ['installation_id' => 'INS-1', 'request_id' => 'REQ-1'];
        $signature = VendorApiSigner::sign($payload, 'secret');

        $this->assertTrue(VendorApiSigner::verify($payload, $signature, 'secret'));
    }

    public function test_verify_fails_with_the_wrong_secret(): void
    {
        $payload = ['installation_id' => 'INS-1', 'request_id' => 'REQ-1'];
        $signature = VendorApiSigner::sign($payload, 'secret-a');

        $this->assertFalse(VendorApiSigner::verify($payload, $signature, 'secret-b'));
    }

    public function test_verify_fails_when_any_field_is_tampered_with(): void
    {
        $payload = ['installation_id' => 'INS-1', 'amount' => 100];
        $signature = VendorApiSigner::sign($payload, 'secret');

        $tampered = array_merge($payload, ['amount' => 999]);

        $this->assertFalse(VendorApiSigner::verify($tampered, $signature, 'secret'));
    }

    public function test_signature_is_independent_of_top_level_key_order(): void
    {
        $inOrderA = ['a' => 1, 'b' => 2, 'c' => 3];
        $inOrderB = ['c' => 3, 'a' => 1, 'b' => 2];

        $this->assertSame(
            VendorApiSigner::sign($inOrderA, 'secret'),
            VendorApiSigner::sign($inOrderB, 'secret'),
        );
    }

    public function test_signature_is_independent_of_nested_key_order(): void
    {
        $inOrderA = ['features' => ['key' => 'courier', 'enabled' => true]];
        $inOrderB = ['features' => ['enabled' => true, 'key' => 'courier']];

        $this->assertSame(
            VendorApiSigner::sign($inOrderA, 'secret'),
            VendorApiSigner::sign($inOrderB, 'secret'),
        );
    }

    public function test_the_signature_field_itself_is_excluded_from_the_signed_payload(): void
    {
        $withoutSignature = ['installation_id' => 'INS-1'];
        $withSignature = ['installation_id' => 'INS-1', 'signature' => 'whatever-was-here-before'];

        $this->assertSame(
            VendorApiSigner::sign($withoutSignature, 'secret'),
            VendorApiSigner::sign($withSignature, 'secret'),
        );
    }
}
