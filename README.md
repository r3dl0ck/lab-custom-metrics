# Laboratorium: korzystanie z bibliotek klienckich Prometheusa

Kod źródłowy do laboratorium opisanego w `src/usage_custom_metrics_lab.md`.

- `php-app/`, `java-app/` - aplikacje bez metryk (punkt wyjścia do zadań),
- `prometheus/`, `loadgen/`, `docker-compose.yml` - środowisko (Prometheus, Redis, generator ruchu),
- `solution/` - kompletne rozwiązanie wszystkich zadań.

```
docker compose up -d --build
```

Porty: PHP `8081`, Java `8082`, Prometheus `9090`.
