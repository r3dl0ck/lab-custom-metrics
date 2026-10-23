<?php

declare(strict_types=1);

namespace App;

use Prometheus\CollectorRegistry;

final class OrderService
{
    public function __construct(
        private ProductRepository $products,
        private PaymentGateway $payments,
        private CollectorRegistry $registry,
    ) {
    }

    /**
     * @throws OrderRejected
     * @throws PaymentFailed
     */
    public function place(int $productId, int $quantity, string $payment): array
    {
        $product = $this->products->find($productId);
        if ($product === null) {
            throw new OrderRejected('unknown_product');
        }
        // wartość z żądania trafia do etykiety dopiero po sprawdzeniu z listą dozwolonych wartości
        if (!in_array($payment, PaymentGateway::METHODS, true)) {
            throw new OrderRejected('invalid_payment');
        }

        $total = round($product['price'] * $quantity, 2);

        $start = microtime(true);
        $inProgress = $this->registry->getOrRegisterGauge(
            'app',
            'orders_in_progress',
            'Zamówienia w trakcie przetwarzania',
        );
        $inProgress->inc();

        try {
            $this->payments->charge($payment, $total);

            $this->registry
                ->getOrRegisterCounter('app', 'orders_total', 'Liczba złożonych zamówień', ['payment'])
                ->inc([$payment]);

            $this->registry
                ->getOrRegisterCounter('app', 'orders_revenue_pln_total', 'Przychód ze złożonych zamówień (PLN)')
                ->incBy($total);

            return [
                'order_id' => uniqid('ord_'),
                'product_id' => $productId,
                'quantity' => $quantity,
                'total' => $total,
                'payment' => $payment,
            ];
        } catch (PaymentFailed $e) {
            $this->registry
                ->getOrRegisterCounter('app', 'payments_failed_total', 'Liczba nieudanych płatności', ['reason'])
                ->inc([$e->reason]);

            throw $e;
        } finally {
            $inProgress->dec();

            $this->registry
                ->getOrRegisterHistogram(
                    'app',
                    'order_processing_seconds',
                    'Czas przetwarzania zamówienia',
                    [],
                    [0.1, 0.25, 0.5, 1, 2.5],
                )
                ->observe(microtime(true) - $start);
        }
    }
}
