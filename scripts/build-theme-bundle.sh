#!/usr/bin/env bash
# Fase 6: package bundled theme → Vite theme.esm.js → bundle_checksum in theme.json
set -euo pipefail
export PATH="/usr/bin:${PATH}"

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SLUG="${1:-janari}"

echo "==> theme:package ${SLUG}"
cd "${ROOT}/backend"
php artisan theme:package "${SLUG}"

echo "==> vite theme bundle"
cd "${ROOT}/frontend"
THEME_SLUG="${SLUG}" npm run build:theme

echo "==> theme:build ${SLUG} (checksum)"
cd "${ROOT}/backend"
php artisan theme:build "${SLUG}"

BUNDLE="${ROOT}/backend/storage/app/public/themes/${SLUG}/theme.esm.js"
if [[ -f "${BUNDLE}" ]]; then
  echo "OK: ${BUNDLE} ($(du -h "${BUNDLE}" | cut -f1))"
else
  echo "ERROR: bundle missing at ${BUNDLE}" >&2
  exit 1
fi
