#!/usr/bin/env bash
# Bootstrap local database for WebDev2 (teammates / CI).
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

php artisan db:prepare --seed "$@"
