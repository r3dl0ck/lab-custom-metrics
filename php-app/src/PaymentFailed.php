<?php

declare(strict_types=1);

namespace App;

final class PaymentFailed extends \RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('payment failed: ' . $reason);
    }
}
