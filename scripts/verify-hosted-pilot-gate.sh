#!/usr/bin/env bash
set -euo pipefail
PROFILE="${1:-staging}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKEND="${BACKEND_DIR:-${ROOT}/backend}"
FRONTEND="${FRONTEND_DIR:-${ROOT}/frontend}"
echo "=== Hosted pilot gate (profile=${PROFILE}) ==="
echo "Tip: merge docs/operational/env/platform-payment.staging.example into backend/.env for staging profile"
cd "${BACKEND}"
if [[ "${SKIP_PAYMENT_ENV:-}" == "1" ]]; then
  echo "Payment env check skipped (SKIP_PAYMENT_ENV=1)"
else
  php artisan platform:payment-env-check --profile="${PROFILE}" --provider=all || {
    echo "Payment env check failed — merge env template or use SKIP_PAYMENT_ENV=1 for PHPUnit-only"
    exit 1
  }
fi
php artisan test --filter=PlatformPayment --ansi
if [[ "${SKIP_E2E:-}" == "1" ]]; then
  echo "E2E skipped (SKIP_E2E=1)"
elif curl -sf "http://127.0.0.1:8081/up" >/dev/null 2>&1; then
  cd "${FRONTEND}"
  node scripts/e2e-preflight.mjs
  PLAYWRIGHT_USE_FULL_STACK=1 npm run test:e2e:ci
else
  echo "E2E skipped — start: cd backend && php artisan serve --host=127.0.0.1 --port=8081"
fi
echo "=== Hosted pilot gate OK ==="
