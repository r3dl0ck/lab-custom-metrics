<?php

declare(strict_types=1);

namespace App;

/** Symuluje czas pracy aplikacji, żeby metryki opóźnień miały ciekawy rozkład. */
final class Latency
{
    public static function simulate(int $minMs = 10, int $maxMs = 150): void
    {
        // ok. 5% żądań jest wolnych (0,6-1,2 s)
        if (random_int(1, 100) <= 5) {
            $minMs = 600;
            $maxMs = 1200;
        }

        usleep(random_int($minMs, $maxMs) * 1000);
    }
}
