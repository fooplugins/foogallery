#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="$ROOT_DIR/.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing .env file at $ENV_FILE"
  echo "Copy .env.example to .env and fill in credentials."
  exit 1
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

: "${WP_BASE_URL:?WP_BASE_URL is required}"
: "${WP_ADMIN_USER:?WP_ADMIN_USER is required}"
: "${WP_ADMIN_PASS:?WP_ADMIN_PASS is required}"

if ! command -v agent-browser >/dev/null 2>&1; then
  echo "agent-browser is not installed. Run: npm install -g agent-browser"
  exit 1
fi

export AGENT_BROWSER_SESSION="${AGENT_BROWSER_SESSION:-foogallery-ui}"
export AB_HEADED="${AB_HEADED:-1}"
export AB_ARTIFACTS_DIR="$ROOT_DIR/tests/ui/artifacts"

"$ROOT_DIR/tests/ui/flows/admin-menu.sh"
