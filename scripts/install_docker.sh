#!/bin/bash
# install_docker.sh
# Install Docker and start the SAE23 stack on CT 104
# Run from the usine/ project root with: bash scripts/install_docker.sh
# Or from anywhere: the script detects its own location

set -e

# Detect the project root (parent of the scripts/ directory)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
DOCKER_DIR="$PROJECT_DIR/docker"

echo "Project dir : $PROJECT_DIR"
echo "Docker dir  : $DOCKER_DIR"
echo ""

# --- Step 1: Install Docker if not present ---
if ! command -v docker &> /dev/null; then
    sudo apt-get update -y
    sudo apt-get install -y docker.io
    echo "Docker installed."
else
    echo "Docker already installed: $(docker --version)"
fi

# --- Step 2: Install Docker Compose plugin ---
echo ""
if ! docker compose version &> /dev/null; then
    # Try both known package names for Ubuntu 24.04
    sudo apt-get install -y docker-compose-plugin || \
    sudo apt-get install -y docker-compose-v2 || {
        echo "ERROR: Could not install docker compose plugin."
        echo "Try manually: sudo apt-get install docker-compose-plugin"
        exit 1
    }
    echo "Docker Compose installed."
else
    echo "Docker Compose already available: $(docker compose version)"
fi

# --- Step 3: Ensure Docker daemon is running ---
echo ""
if ! sudo systemctl is-active --quiet docker; then
    sudo systemctl start docker
    sudo systemctl enable docker
    echo "Docker daemon started and enabled."
else
    echo "Docker daemon is already running."
fi

# Add current user to docker group (avoid sudo for docker commands)
if ! groups "$USER" | grep -q docker; then
    sudo usermod -aG docker "$USER"
    echo "User $USER added to docker group (you may need to re-login for this to take effect)."
fi

# --- Step 4: Prepare directories and config files ---
echo ""
mkdir -p "$DOCKER_DIR/mosquitto/config"
mkdir -p "$DOCKER_DIR/mosquitto/data"
mkdir -p "$DOCKER_DIR/mosquitto/log"
mkdir -p "$DOCKER_DIR/influxdb/data"
mkdir -p "$DOCKER_DIR/nodered/data"
mkdir -p "$DOCKER_DIR/grafana/data"
echo "Directories created."

# Verify required config files exist
REQUIRED_FILES=(
    "$DOCKER_DIR/docker-compose.yml"
    "$DOCKER_DIR/mosquitto/config/mosquitto.conf"
    "$DOCKER_DIR/nodered/flows.json"
)
MISSING=0
for f in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$f" ]; then
        echo "WARNING: Missing file: $f"
        MISSING=1
    fi
done
if [ $MISSING -eq 1 ]; then
    echo "Some config files are missing. Make sure the usine/ repository is fully cloned on CT 104."
    echo "Run: git pull   in the usine/ directory to get all files."
    exit 1
fi
echo "All required config files present."

# --- Step 5: Start Docker stack ---
echo ""
cd "$DOCKER_DIR"
sudo docker compose down --remove-orphans || true
sudo docker compose up -d

# Wait for containers to start
echo "Waiting for containers to initialize..."
sleep 10

# --- Show status ---
echo ""
sudo docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Quick health check (skip if netcat is not installed)
echo ""
if command -v nc &> /dev/null; then
    echo -n "Mosquitto (1883) : "
    nc -z -w2 localhost 1883 && echo "OK - listening" || echo "NOT REACHABLE (check: sudo docker logs sae23-mosquitto)"
    echo -n "Node-RED  (1880) : "
    nc -z -w2 localhost 1880 && echo "OK - listening" || echo "NOT REACHABLE (check: sudo docker logs sae23-nodered)"
    echo -n "InfluxDB  (8086) : "
    nc -z -w2 localhost 8086 && echo "OK - listening" || echo "NOT REACHABLE (check: sudo docker logs sae23-influxdb)"
    echo -n "Grafana   (3000) : "
    nc -z -w2 localhost 3000 && echo "OK - listening" || echo "NOT REACHABLE (check: sudo docker logs sae23-grafana)"
else
    echo "(netcat not installed — skipping port checks)"
    echo "Install with: sudo apt-get install netcat-openbsd"
fi

echo ""
echo "  DOCKER INSTALLATION COMPLETE"
echo ""
echo "Services accessible on CT 104:"
echo "  Mosquitto (MQTT) : port 1883"
echo "  Node-RED          : http://localhost:1880"
echo "  InfluxDB          : http://localhost:8086  (admin / sae23admin)"
echo "  Grafana           : http://localhost:3000   (admin / sae23admin)"
echo ""
echo "1. Import Node-RED flow:"
echo "   Open http://localhost:1880 → Menu → Import → paste flows.json content"
echo ""
echo "2. Configure Grafana InfluxDB datasource:"
echo "   Open http://localhost:3000 → Configuration → Data Sources → Add → InfluxDB"
echo "   URL: http://influxdb:8086"
echo "   Database: sae23"
echo "   User: admin / Password: sae23admin"
echo ""
echo "3. Import Grafana dashboard:"
echo "   Create → Import → Upload dashboard.json from docker/grafana/"
echo ""
echo "4. Check logs if something is wrong:"
echo "   sudo docker logs sae23-mosquitto"
echo "   sudo docker logs sae23-influxdb"
echo "   sudo docker logs sae23-nodered"
echo "   sudo docker logs sae23-grafana"
