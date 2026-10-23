#!/bin/sh
# Prosty generator ruchu dla obu aplikacji (PHP i Java).
#   ID_RANGE   - zakres losowanych identyfikatorów w GET /products/{id} (domyślnie 8 = istniejące produkty)
#   LOOP_DELAY - przerwa między iteracjami w sekundach

PHP=http://php-app
JAVA=http://java-app:8080
ID_RANGE=${ID_RANGE:-8}
LOOP_DELAY=${LOOP_DELAY:-0.2}

# losowa liczba z przedziału 0..($1-1)
rnd() { echo $(( $(od -An -N2 -tu2 /dev/urandom | tr -d ' ') % $1 )); }

pick_payment() {
  case $(rnd 3) in
    0) echo card ;;
    1) echo blik ;;
    *) echo transfer ;;
  esac
}

echo "loadgen: ID_RANGE=$ID_RANGE LOOP_DELAY=$LOOP_DELAY"

while true; do
  for BASE in $PHP $JAVA; do
    curl -s -o /dev/null "$BASE/products"
    curl -s -o /dev/null "$BASE/products/$(( $(rnd "$ID_RANGE") + 1 ))"
    curl -s -o /dev/null -X POST "$BASE/orders" \
      -H 'Content-Type: application/json' \
      -d "{\"product_id\": $(( $(rnd 8) + 1 )), \"quantity\": $(( $(rnd 3) + 1 )), \"payment\": \"$(pick_payment)\"}"

    # co ok. 20 iteracji błąd 500, co ok. 30 niepoprawna metoda płatności
    [ "$(rnd 20)" -eq 0 ] && curl -s -o /dev/null "$BASE/crash"
    [ "$(rnd 30)" -eq 0 ] && curl -s -o /dev/null -X POST "$BASE/orders" \
      -H 'Content-Type: application/json' \
      -d '{"product_id": 1, "quantity": 1, "payment": "bitcoin"}'
  done
  sleep "$LOOP_DELAY"
done
