#!/bin/bash
# File: scripts/pre-test.sh
# Pre-test setup: Clean artifacts, reset Docker, start fresh environment

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Load environment variables from .env file if it exists
if [ -f ".env" ]; then
    echo "[Pre-Test] Loading environment variables from .env..."
    export $(grep -v '^#' .env | xargs)
fi

echo ""
echo "========================================"
echo "[Pre-Test] Preparing clean environment"
echo "========================================"
echo ""

# 1. Clean previous test artifacts
echo "[Pre-Test] Removing previous test artifacts..."
rm -rf test-results playwright-report .auth
echo "[Pre-Test] Artifacts cleaned"

# 2. Stop existing containers and remove volumes
echo "[Pre-Test] Stopping existing containers..."
docker compose -f docker/docker-compose.yml down -v --remove-orphans 2>/dev/null || true
echo "[Pre-Test] Containers stopped"

# 3. Start fresh containers
echo "[Pre-Test] Starting fresh containers..."
docker compose -f docker/docker-compose.yml up -d

# 4. Wait for WordPress container to be healthy
echo "[Pre-Test] Waiting for WordPress to be healthy..."
COUNTER=0
MAX_TRIES=60
until docker compose -f docker/docker-compose.yml exec -T wordpress curl -sf http://localhost/ >/dev/null 2>&1; do
    COUNTER=$((COUNTER + 1))
    if [ $COUNTER -ge $MAX_TRIES ]; then
        echo "[Pre-Test] ERROR: WordPress not ready after ${MAX_TRIES} attempts"
        docker compose -f docker/docker-compose.yml logs wordpress
        exit 1
    fi
    if [ $((COUNTER % 10)) -eq 0 ]; then
        echo "[Pre-Test] Still waiting... (attempt $COUNTER/$MAX_TRIES)"
    fi
    sleep 2
done
echo "[Pre-Test] WordPress container is healthy!"

# 5. Run WordPress setup script
echo "[Pre-Test] Running WordPress setup..."
docker compose -f docker/docker-compose.yml exec -T wordpress /var/scripts/setup-wordpress.sh

echo ""
echo "========================================"
echo "[Pre-Test] Environment ready!"
echo "========================================"
echo ""
