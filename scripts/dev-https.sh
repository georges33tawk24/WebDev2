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

php artisan queue:listen --tries=1 --timeout=0 &
QUEUE_PID=$!

php artisan schedule:work &
SCHEDULE_PID=$!

cleanup() {
	kill "$ARTISAN_PID" "$QUEUE_PID" "$SCHEDULE_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

echo ""
echo "  App:  https://127.0.0.1:8000"
echo "  Queue worker + appointment reminder scheduler are running."
echo "  Add to Meta → Facebook Login → Valid OAuth Redirect URIs:"
echo "        https://127.0.0.1:8000/auth/facebook/callback"
echo ""
echo "  Browser push notifications need a trusted local certificate."
echo "  If Enable notifications fails with an SSL error, run once:"
echo "        caddy trust"
echo "  Then quit Chrome completely and reopen the app."
echo ""

caddy run --config Caddyfile
