#!/bin/bash
# configure_l2l3.sh
# Configure Grafana datasource + dashboard after Docker stack is running
# Run from usine/: bash scripts/configure_l2l3.sh

if grep -q $'\r' "$0"; then
    sed -i 's/\r$//' "$0"
    exec bash "$0" "$@"
fi

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
DASHBOARD_FILE="$PROJECT_DIR/docker/grafana/dashboard.json"

GRAFANA_URL="http://localhost:3000"
GRAFANA_USER="admin"
GRAFANA_PASS="sae23admin"


# wait for Grafana
echo "Waiting for Grafana..."
for i in 1 2 3 4 5 6 7 8 9 10; do
    if curl -s -o /dev/null -w "%{http_code}" "$GRAFANA_URL/api/health" | grep -q "200"; then
        echo "Grafana is ready."
        break
    fi
    sleep 3
done

# add InfluxDB datasource (skip if exists)
EXISTING=$(curl -s -u "$GRAFANA_USER:$GRAFANA_PASS" "$GRAFANA_URL/api/datasources/name/InfluxDB" -w "%{http_code}" -o /tmp/ds_check.json)
if echo "$EXISTING" | grep -q "200"; then
    echo "Datasource InfluxDB already exists."
else
    curl -s -u "$GRAFANA_USER:$GRAFANA_PASS" \
        -H "Content-Type: application/json" \
        -X POST "$GRAFANA_URL/api/datasources" \
        -d '{
            "name": "InfluxDB",
            "type": "influxdb",
            "url": "http://influxdb:8086",
            "access": "proxy",
            "isDefault": true,
            "database": "sae23",
            "user": "admin",
            "password": "sae23admin"
        }'
    echo ""
    echo "Datasource InfluxDB created."
fi

# import dashboard
if [ ! -f "$DASHBOARD_FILE" ]; then
    echo "Error: dashboard.json not found at $DASHBOARD_FILE"
    exit 1
fi

curl -s -u "$GRAFANA_USER:$GRAFANA_PASS" \
    -H "Content-Type: application/json" \
    -X POST "$GRAFANA_URL/api/dashboards/db" \
    -d @"$DASHBOARD_FILE"
echo ""
echo "Dashboard imported."

echo ""
echo "Grafana dashboard : http://localhost:3000/d/sae23-capteurs/sae23-surveillance-des-capteurs"
echo "Grafana login     : http://localhost:3000 (admin / sae23admin)"
echo "Node-RED          : http://localhost:1880"
echo "InfluxDB API      : http://localhost:8086/ping (pas d'interface web a la racine)"
echo ""
echo "Si pas de donnees: bash scripts/inject_demo_influx.sh (hors heures capteurs)"
