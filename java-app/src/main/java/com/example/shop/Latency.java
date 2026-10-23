package com.example.shop;

import java.util.concurrent.ThreadLocalRandom;

/** Symuluje czas pracy aplikacji, żeby metryki opóźnień miały ciekawy rozkład. */
public final class Latency {

    private Latency() {
    }

    public static void simulate() {
        simulate(10, 150);
    }

    public static void simulate(int minMs, int maxMs) {
        ThreadLocalRandom random = ThreadLocalRandom.current();

        // ok. 5% żądań jest wolnych (0,6-1,2 s)
        if (random.nextInt(100) < 5) {
            minMs = 600;
            maxMs = 1200;
        }

        try {
            Thread.sleep(random.nextInt(minMs, maxMs + 1));
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }
}
