package com.example.shop;

import io.micrometer.core.instrument.Counter;
import io.micrometer.core.instrument.Gauge;
import io.micrometer.core.instrument.MeterRegistry;
import io.micrometer.core.instrument.Timer;
import java.time.Duration;
import java.util.UUID;
import java.util.concurrent.atomic.AtomicInteger;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

@Service
public class OrderService {

    private final ProductRepository products;
    private final PaymentGateway payments;
    private final MeterRegistry registry;

    private final Counter revenue;
    private final Timer processingTimer;
    private final AtomicInteger inProgress = new AtomicInteger();

    public OrderService(ProductRepository products, PaymentGateway payments, MeterRegistry registry) {
        this.products = products;
        this.payments = payments;
        this.registry = registry;

        // metryki rejestrujemy raz, w konstruktorze
        this.revenue = Counter.builder("app.orders.revenue")
                .baseUnit("pln")
                .description("Przychód ze złożonych zamówień (PLN)")
                .register(registry);

        this.processingTimer = Timer.builder("app.order.processing")
                .description("Czas przetwarzania zamówienia")
                .serviceLevelObjectives(
                        Duration.ofMillis(100), Duration.ofMillis(250), Duration.ofMillis(500),
                        Duration.ofSeconds(1), Duration.ofMillis(2500))
                .register(registry);

        // Gauge odczytuje wartość w momencie scrapowania
        Gauge.builder("app.orders.in.progress", inProgress, AtomicInteger::get)
                .description("Zamówienia w trakcie przetwarzania")
                .register(registry);

        // etykieta payment ma znany zbiór wartości - tworzymy serie z wartością 0 od startu
        for (String method : PaymentGateway.METHODS) {
            registry.counter("app.orders", "payment", method);
        }
    }

    public Order place(OrderRequest request) {
        Product product = products.find(request.productId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.UNPROCESSABLE_ENTITY, "unknown_product"));
        // wartość z żądania trafia do etykiety dopiero po sprawdzeniu z listą dozwolonych wartości
        if (!PaymentGateway.METHODS.contains(request.payment())) {
            throw new ResponseStatusException(HttpStatus.UNPROCESSABLE_ENTITY, "invalid_payment");
        }

        int quantity = Math.max(1, request.quantity() == null ? 1 : request.quantity());
        double total = Math.round(product.price() * quantity * 100) / 100.0;

        return processingTimer.record(() -> {
            inProgress.incrementAndGet();
            try {
                payments.charge(request.payment(), total);

                registry.counter("app.orders", "payment", request.payment()).increment();
                revenue.increment(total);

                return new Order("ord_" + UUID.randomUUID().toString().substring(0, 13),
                        product.id(), quantity, total, request.payment());
            } catch (PaymentFailedException e) {
                registry.counter("app.payments.failed", "reason", e.getReason()).increment();
                throw e;
            } finally {
                inProgress.decrementAndGet();
            }
        });
    }
}
