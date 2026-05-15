#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if ! command -v caddy >/dev/null 2>&1; then
	echo "Caddy is required for https://127.0.0.1:8000"
	echo "Install: brew install caddy"
	exit 1
fi

php artisan config:clear

php artisan serve --host=127.0.0.1 --port=8001 &
ARTISAN_PID=$!

cleanup() {
	kill "$ARTISAN_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

echo ""
echo "  App:  https://127.0.0.1:8000"
echo "  Add to Meta → Facebook Login → Valid OAuth Redirect URIs:"
echo "        https://127.0.0.1:8000/auth/facebook/callback"
echo ""

caddy run --config Caddyfile
