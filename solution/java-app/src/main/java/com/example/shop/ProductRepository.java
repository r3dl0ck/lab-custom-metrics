package com.example.shop;

import java.util.List;
import java.util.Optional;
import org.springframework.stereotype.Repository;

@Repository
public class ProductRepository {

    /** Kategorie produktów - zbiór stały i znany z góry. */
    public static final List<String> CATEGORIES = List.of("pies", "kot", "akwarium", "gryzonie");

    private static final List<Product> PRODUCTS = List.of(
            new Product(1, "Karma sucha dla psa 12 kg", "pies", 89.90),
            new Product(2, "Karma mokra dla kota 12x400 g", "kot", 54.50),
            new Product(3, "Żwirek dla kota 10 l", "kot", 39.00),
            new Product(4, "Smycz regulowana", "pies", 29.90),
            new Product(5, "Akwarium 60 l", "akwarium", 249.00),
            new Product(6, "Pokarm dla ryb 250 ml", "akwarium", 12.50),
            new Product(7, "Klatka dla chomika", "gryzonie", 99.00),
            new Product(8, "Siano dla królika 1 kg", "gryzonie", 15.00));

    public List<Product> all() {
        return PRODUCTS;
    }

    public Optional<Product> find(int id) {
        return PRODUCTS.stream().filter(p -> p.id() == id).findFirst();
    }
}
