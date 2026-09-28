<?php

namespace App\Services\License;

use App\Models\License;
use InvalidArgumentException;

/**
 * SRS §39/§40 license state machine. A thin guard around `licenses.status`
 * transitions so a caller (an admin action today; LIC-001's automatic job
 * later) can't move a license through a nonsensical state change.
 *
 * Deliberately permissive of the manual admin overrides the pre-SRS UI
 * already relied on (e.g. suspending directly from ACTIVE without passing
 * through PAYMENT_DUE / GRACE_PERIOD first, or reactivating straight back to
 * ACTIVE) — it only rejects transitions that make no sense at all (e.g.
 * reactivating a REVOKED license, or leaving a terminal state other than to
 * REVOKED).
 */
class LicenseLifecycle
{
    /**
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        License::STATUS_PENDING => [License::STATUS_ACTIVE, License::STATUS_REVOKED],
        License::STATUS_ACTIVE => [
            License::STATUS_PAYMENT_DUE, License::STATUS_SUSPENDED,
            License::STATUS_COMPLETED, License::STATUS_EXPIRED, License::STATUS_REVOKED,
        ],
        License::STATUS_PAYMENT_DUE => [
            License::STATUS_ACTIVE, License::STATUS_GRACE_PERIOD,
            License::STATUS_SUSPENDED, License::STATUS_REVOKED,
        ],
        License::STATUS_GRACE_PERIOD => [
            License::STATUS_ACTIVE, License::STATUS_SUSPENDED, License::STATUS_REVOKED,
        ],
        License::STATUS_SUSPENDED => [
            License::STATUS_REACTIVATED, License::STATUS_ACTIVE, License::STATUS_REVOKED,
        ],
        License::STATUS_REACTIVATED => [License::STATUS_ACTIVE, License::STATUS_REVOKED],
        License::STATUS_EXPIRED => [License::STATUS_ACTIVE, License::STATUS_REVOKED],
        License::STATUS_COMPLETED => [License::STATUS_REVOKED],
        License::STATUS_REVOKED => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return false;
        }

        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function transitions(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }

    /**
     * @param  array<string, mixed>  $attributes  Extra columns to persist alongside the status change.
     *
     * @throws InvalidArgumentException when the transition is not allowed.
     */
    public function transition(License $license, string $to, array $attributes = []): License
    {
        if (! in_array($to, License::STATUSES, true)) {
            throw new InvalidArgumentException("Unknown license status [{$to}].");
        }

        if (! $this->canTransition($license->status, $to)) {
            throw new InvalidArgumentException(
                "License cannot transition from [{$license->status}] to [{$to}]."
            );
        }

        $license->update(array_merge($attributes, ['status' => $to]));

        return $license;
    }
}
