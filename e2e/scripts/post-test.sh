#!/bin/bash
# File: scripts/post-test.sh
# Post-test cleanup: Stop containers, remove volumes, archive reports if configured

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Load environment variables
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs) 2>/dev/null || true
fi

echo ""
echo "========================================"
echo "[Post-Test] Cleaning up environment"
echo "========================================"
echo ""

# Archive report if configured
if [ "${ARCHIVE_REPORTS:-false}" = "true" ] && [ -d "playwright-report" ]; then
    TIMESTAMP=$(date +%Y%m%d-%H%M%S)
    ARCHIVE_DIR="playwright-reports-archive"
    mkdir -p "$ARCHIVE_DIR"
    cp -r playwright-report "$ARCHIVE_DIR/report-$TIMESTAMP"
    echo "[Post-Test] Report archived to $ARCHIVE_DIR/report-$TIMESTAMP"
fi

# Stop and clean containers based on configuration
if [ "${CLEANUP_AFTER_TESTS:-true}" = "true" ]; then
    echo "[Post-Test] Stopping containers and removing volumes..."
    docker compose -f docker/docker-compose.yml down -v --remove-orphans
    echo "[Post-Test] Containers stopped and volumes removed"
else
    echo "[Post-Test] Keeping containers running (CLEANUP_AFTER_TESTS=false)"
fi

echo ""
echo "========================================"
echo "[Post-Test] Cleanup complete!"
echo "========================================"
echo ""
echo "[Post-Test] View report: npm run test:report"
echo ""
