#!/bin/bash
# inject_demo_influx.sh
# Insert demo points in InfluxDB for Grafana (when sensors are offline)
# Run on CT 104: bash scripts/inject_demo_influx.sh

if grep -q $'\r' "$0"; then
    sed -i 's/\r$//' "$0"
    exec bash "$0" "$@"
fi

URL="http://localhost:8086/write?db=sae23&u=admin&p=sae23admin"
TS=$(date +%s)


# last 6 hours, one point every 10 min
for i in 36 35 34 33 32 31 30 29 28 27 26 25 24 23 22 21 20 19 18 17 16 15 14 13 12 11 10 9 8 7 6 5 4 3 2 1 0; do
    T=$((TS - i * 600))
    TEMP_E=$((22 + i % 3))
    CO2_E=$((400 + i * 2))
    TEMP_C=$((21 + i % 2))
    HUM_C=$((30 + i % 5))

    curl -s -XPOST "$URL" --data-binary "temperature,building=E,room=E101,capteur=AM107-E101-temp value=$TEMP_E $T"
    curl -s -XPOST "$URL" --data-binary "co2,building=E,room=E101,capteur=AM107-E101-co2 value=$CO2_E $T"
    curl -s -XPOST "$URL" --data-binary "temperature,building=C,room=C101,capteur=AM107-C101-temp value=$TEMP_C $T"
    curl -s -XPOST "$URL" --data-binary "humidity,building=C,room=C101,capteur=AM107-C101-hum value=$HUM_C $T"
done

echo ""
echo "Demo data inserted."
echo "Open Grafana: http://localhost:3000/d/sae23-capteurs/sae23-surveillance-des-capteurs"
