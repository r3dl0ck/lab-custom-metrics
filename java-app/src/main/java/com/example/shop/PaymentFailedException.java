package com.example.shop;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(HttpStatus.PAYMENT_REQUIRED)
public class PaymentFailedException extends RuntimeException {

    private final String reason;

    public PaymentFailedException(String reason) {
        super("payment failed: " + reason);
        this.reason = reason;
    }

    public String getReason() {
        return reason;
    }
}
