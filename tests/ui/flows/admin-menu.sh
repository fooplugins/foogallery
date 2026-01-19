#!/usr/bin/env bash
set -euo pipefail

SESSION_ARGS=()
if [ -n "${AGENT_BROWSER_SESSION:-}" ]; then
  SESSION_ARGS=(--session "$AGENT_BROWSER_SESSION")
fi

HEADED_ARGS=()
if [ "${AB_HEADED:-1}" = "1" ]; then
  HEADED_ARGS=(--headed)
fi

BASE_URL="${WP_BASE_URL%/}"
LOGIN_URL="$BASE_URL/wp-login.php"
ADMIN_URL="$BASE_URL/wp-admin/"

agent-browser "${SESSION_ARGS[@]}" open "$LOGIN_URL" "${HEADED_ARGS[@]}"
agent-browser "${SESSION_ARGS[@]}" wait "#user_login"
agent-browser "${SESSION_ARGS[@]}" fill "#user_login" "$WP_ADMIN_USER"
agent-browser "${SESSION_ARGS[@]}" fill "#user_pass" "$WP_ADMIN_PASS"
agent-browser "${SESSION_ARGS[@]}" click "#wp-submit"
agent-browser "${SESSION_ARGS[@]}" wait --url "**/wp-admin/**"
agent-browser "${SESSION_ARGS[@]}" open "$ADMIN_URL"
agent-browser "${SESSION_ARGS[@]}" wait "#adminmenu"
agent-browser "${SESSION_ARGS[@]}" is visible "#menu-posts-foogallery"

MENU_LABEL=$(agent-browser "${SESSION_ARGS[@]}" get text "#menu-posts-foogallery .wp-menu-name")
echo "Found FooGallery menu: $MENU_LABEL"

agent-browser "${SESSION_ARGS[@]}" click "#menu-posts-foogallery > a.menu-top"
agent-browser "${SESSION_ARGS[@]}" wait --url "**post_type=foogallery**"
agent-browser "${SESSION_ARGS[@]}" wait "h1.wp-heading-inline"

HEADER_TEXT=$(agent-browser "${SESSION_ARGS[@]}" get text "h1.wp-heading-inline")
if [[ "$HEADER_TEXT" != *"Galleries"* ]]; then
  echo "Expected Galleries header, got: $HEADER_TEXT"
  exit 1
fi

echo "Found Galleries header: $HEADER_TEXT"

if [ -n "${AB_ARTIFACTS_DIR:-}" ]; then
  mkdir -p "$AB_ARTIFACTS_DIR"
  agent-browser "${SESSION_ARGS[@]}" screenshot "$AB_ARTIFACTS_DIR/admin-menu.png"
fi

agent-browser "${SESSION_ARGS[@]}" close
