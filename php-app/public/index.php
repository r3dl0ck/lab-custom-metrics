<?php

declare(strict_types=1);

use App\Latency;
use App\OrderRejected;
use App\OrderService;
use App\PaymentFailed;
use App\PaymentGateway;
use App\ProductRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

function jsonResponse(Response $response, mixed $data, int $status = 200): Response
{
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
}

$products = new ProductRepository();
$orders = new OrderService($products, new PaymentGateway());

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// --- LAB 1.3: tutaj zarejestrujemy middleware z metrykami ---

$app->get('/products', function (Request $request, Response $response) use ($products) {
    Latency::simulate();

    return jsonResponse($response, $products->all());
});

$app->get('/products/{id}', function (Request $request, Response $response, array $args) use ($products) {
    Latency::simulate();

    $product = $products->find((int) $args['id']);
    if ($product === null) {
        return jsonResponse($response, ['error' => 'product not found'], 404);
    }

    return jsonResponse($response, $product);
});

$app->post('/orders', function (Request $request, Response $response) use ($orders) {
    $body = (array) $request->getParsedBody();

    try {
        $order = $orders->place(
            (int) ($body['product_id'] ?? 0),
            max(1, (int) ($body['quantity'] ?? 1)),
            (string) ($body['payment'] ?? ''),
        );

        return jsonResponse($response, $order, 201);
    } catch (OrderRejected $e) {
        return jsonResponse($response, ['error' => $e->getMessage()], 422);
    } catch (PaymentFailed $e) {
        return jsonResponse($response, ['error' => 'payment_failed', 'reason' => $e->reason], 402);
    }
});

// endpoint do wywoływania błędów 500
$app->get('/crash', function () {
    throw new RuntimeException('symulowany błąd aplikacji');
});

// --- LAB 1.4: tutaj dodamy endpoint /metrics ---

$app->addRoutingMiddleware();
$app->addErrorMiddleware(false, false, false);
$app->run();
