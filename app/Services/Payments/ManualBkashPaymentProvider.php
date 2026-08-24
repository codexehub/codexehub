<?php

namespace App\Services\Payments;

use App\Models\License;
use App\Models\LicenseTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * v1: you receive the bKash payment directly and manually key in the
 * transaction ID/amount here for approval. Swap this implementation (not
 * the callers) if bKash's merchant API is integrated later.
 */
class ManualBkashPaymentProvider implements PaymentProvider
{
    public function record(License $license, string $txnId, float $amount, ?string $notes = null): LicenseTransaction
    {
        return $license->transactions()->create([
            'method' => 'bkash',
            'txn_id' => $txnId,
            'amount' => $amount,
            'status' => 'pending',
            'notes' => $notes,
        ]);
    }

    public function approve(LicenseTransaction $transaction, User $approver): LicenseTransaction
    {
        $transaction->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => Carbon::now(),
        ]);

        $license = $transaction->license;
        $license->update([
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays($license->package->duration_days),
        ]);

        return $transaction;
    }
}
