<?php

namespace App\Services\Payments;

use App\Models\License;
use App\Models\LicenseTransaction;
use App\Models\User;

interface PaymentProvider
{
    /**
     * Record an incoming payment against a license. Starts out `pending`
     * until approve() confirms it (manual today, could be automatic later
     * for a provider with a real verification API).
     */
    public function record(License $license, string $txnId, float $amount, ?string $notes = null): LicenseTransaction;

    /**
     * Confirm a payment: marks the transaction approved and activates the
     * license for the package's duration from now.
     */
    public function approve(LicenseTransaction $transaction, User $approver): LicenseTransaction;
}
