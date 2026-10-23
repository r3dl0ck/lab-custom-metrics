<?php

declare(strict_types=1);

namespace App;

/** Symulowana bramka płatności: ok. 10% płatności kończy się błędem. */
final class PaymentGateway
{
    public const METHODS = ['card', 'blik', 'transfer'];

    private const FAILURE_REASONS = ['declined', 'timeout', 'insufficient_funds'];

    /** @throws PaymentFailed */
    public function charge(string $method, float $amount): void
    {
        Latency::simulate(50, 300);

        if (random_int(1, 100) <= 10) {
            throw new PaymentFailed(self::FAILURE_REASONS[array_rand(self::FAILURE_REASONS)]);
        }
    }
}
