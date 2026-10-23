<?php

declare(strict_types=1);

namespace App;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

final class Metrics
{
    private static ?CollectorRegistry $registry = null;

    /**
     * Rejestr metryk. PHP nie trzyma stanu między żądaniami, więc wartości metryk
     * zapisujemy w Redisie - dzięki temu sumują się z wielu żądań i procesów.
     */
    public static function registry(): CollectorRegistry
    {
        if (self::$registry === null) {
            Redis::setDefaultOptions(['host' => getenv('REDIS_HOST') ?: 'redis', 'port' => 6379]);
            self::$registry = new CollectorRegistry(new Redis());
        }

        return self::$registry;
    }
}
