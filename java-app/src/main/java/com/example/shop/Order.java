package com.example.shop;

public record Order(String orderId, int productId, int quantity, double total, String payment) {
}
