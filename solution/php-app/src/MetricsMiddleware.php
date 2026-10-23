<?php

declare(strict_types=1);

namespace App;

use Prometheus\CollectorRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

/** Automatyczna instrumentacja HTTP (RED): liczba żądań, błędy (status) i czas obsługi. */
final class MetricsMiddleware implements MiddlewareInterface
{
    // bucket 0.3 odpowiada progowi SLO z prezentacji (300 ms)
    private const BUCKETS = [0.05, 0.1, 0.3, 0.5, 1, 2.5, 5];

    public function __construct(private CollectorRegistry $registry)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // szablon trasy (np. /products/{id}), a NIE pełny URL
        $route = RouteContext::fromRequest($request)->getRoute()?->getPattern() ?? 'unknown';

        // nie mierzymy samego endpointu /metrics
        if ($route === '/metrics') {
            return $handler->handle($request);
        }

        $start = microtime(true);
        $status = 500; // jeśli handler rzuci wyjątek, zliczamy to jako błąd 500

        try {
            $response = $handler->handle($request);
            $status = $response->getStatusCode();

            return $response;
        } finally {
            $method = $request->getMethod();

            $this->registry
                ->getOrRegisterCounter('app', 'http_requests_total', 'Liczba żądań HTTP', ['method', 'route', 'status'])
                ->inc([$method, $route, (string) $status]);

            $this->registry
                ->getOrRegisterHistogram(
                    'app',
                    'http_request_duration_seconds',
                    'Czas obsługi żądania HTTP',
                    ['method', 'route'],
                    self::BUCKETS,
                )
                ->observe(microtime(true) - $start, [$method, $route]);
        }
    }
}
