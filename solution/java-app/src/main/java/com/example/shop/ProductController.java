package com.example.shop;

import io.micrometer.core.instrument.MeterRegistry;
import java.util.List;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.server.ResponseStatusException;

@RestController
public class ProductController {

    private final ProductRepository products;
    private final MeterRegistry registry;

    public ProductController(ProductRepository products, MeterRegistry registry) {
        this.products = products;
        this.registry = registry;

        // etykieta o znanym z góry zbiorze wartości: serie istnieją (z wartością 0) już od startu
        for (String category : ProductRepository.CATEGORIES) {
            registry.counter("app.product.views", "category", category);
        }
    }

    @GetMapping("/products")
    public List<Product> list() {
        Latency.simulate();
        return products.all();
    }

    @GetMapping("/products/{id}")
    public Product get(@PathVariable int id) {
        Latency.simulate();
        Product product = products.find(id)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "product not found"));

        registry.counter("app.product.views", "category", product.category()).increment();

        return product;
    }
}
