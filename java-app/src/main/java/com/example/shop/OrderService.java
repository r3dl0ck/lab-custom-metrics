package com.example.shop;

import java.util.UUID;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

@Service
public class OrderService {

    private final ProductRepository products;
    private final PaymentGateway payments;

    public OrderService(ProductRepository products, PaymentGateway payments) {
        this.products = products;
        this.payments = payments;
    }

    public Order place(OrderRequest request) {
        Product product = products.find(request.productId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.UNPROCESSABLE_ENTITY, "unknown_product"));
        if (!PaymentGateway.METHODS.contains(request.payment())) {
            throw new ResponseStatusException(HttpStatus.UNPROCESSABLE_ENTITY, "invalid_payment");
        }

        int quantity = Math.max(1, request.quantity() == null ? 1 : request.quantity());
        double total = Math.round(product.price() * quantity * 100) / 100.0;

        payments.charge(request.payment(), total);

        return new Order("ord_" + UUID.randomUUID().toString().substring(0, 13),
                product.id(), quantity, total, request.payment());
    }
}
