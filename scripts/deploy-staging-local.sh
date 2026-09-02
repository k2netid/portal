#!/usr/bin/env bash
# K2NET staging on ja-dev — build frontend + refresh Laravel (no rsync to ja-srv).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND="$ROOT/backend"
FRONTEND="$ROOT/frontend"

cd "$FRONTEND"
if [[ "${SKIP_BUILD:-0}" != "1" ]]; then
  echo "==> vite build (heap 4G)"
  NODE_OPTIONS=--max-old-space-size=4096 npx vite build
fi

echo "==> sync assets → backend/public"
SYNC_ONLY=1 bash "$ROOT/scripts/sync-frontend-assets-to-backend.sh"

echo "==> permissions"
bash /home/jejakawan/dev/docs/configs/www-php-permissions-jadev.sh "$BACKEND"

cd "$BACKEND"
php8.5 artisan storage:link --force 2>/dev/null || true
php8.5 artisan optimize:clear 2>&1 | tail -10

echo "==> done — https://staging.k2net.id (origin :8083 on ja-dev)"
echo "    smoke: curl -sI -H 'Host: staging.k2net.id' http://127.0.0.1:8083/ | head -3"
