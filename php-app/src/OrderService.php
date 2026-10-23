<?php

declare(strict_types=1);

namespace App;

final class OrderService
{
    public function __construct(
        private ProductRepository $products,
        private PaymentGateway $payments,
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
        if (!in_array($payment, PaymentGateway::METHODS, true)) {
            throw new OrderRejected('invalid_payment');
        }

        $total = round($product['price'] * $quantity, 2);

        $this->payments->charge($payment, $total);

        return [
            'order_id' => uniqid('ord_'),
            'product_id' => $productId,
            'quantity' => $quantity,
            'total' => $total,
            'payment' => $payment,
        ];
    }
}
