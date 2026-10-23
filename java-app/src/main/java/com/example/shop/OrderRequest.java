package com.example.shop;

public record OrderRequest(int productId, Integer quantity, String payment) {
}
