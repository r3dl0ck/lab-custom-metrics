package com.example.shop;

import java.util.List;
import java.util.concurrent.ThreadLocalRandom;
import org.springframework.stereotype.Component;

/** Symulowana bramka płatności: ok. 10% płatności kończy się błędem. */
@Component
public class PaymentGateway {

    public static final List<String> METHODS = List.of("card", "blik", "transfer");

    private static final List<String> FAILURE_REASONS = List.of("declined", "timeout", "insufficient_funds");

    public void charge(String method, double amount) {
        Latency.simulate(50, 300);

        ThreadLocalRandom random = ThreadLocalRandom.current();
        if (random.nextInt(100) < 10) {
            throw new PaymentFailedException(FAILURE_REASONS.get(random.nextInt(FAILURE_REASONS.size())));
        }
    }
}
