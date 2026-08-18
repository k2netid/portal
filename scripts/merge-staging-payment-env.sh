#!/usr/bin/env bash
# Idempotently merge SaaS payment staging vars into backend/.env (no secrets committed).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKEND="${BACKEND_DIR:-${ROOT}/backend}"
TEMPLATE="${ROOT}/docs/operational/env/platform-payment.staging.example"
ENV_FILE="${BACKEND}/.env"
MARKER="# --- staging payment (merge-staging-payment-env.sh) ---"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "Missing ${ENV_FILE} — copy from .env.example first"
  exit 1
fi
if [[ ! -f "${TEMPLATE}" ]]; then
  echo "Missing ${TEMPLATE}"
  exit 1
fi

if grep -qF "${MARKER}" "${ENV_FILE}" 2>/dev/null; then
  echo "Staging payment block already present in .env"
else
  {
    echo ""
    echo "${MARKER}"
    grep -v '^#' "${TEMPLATE}" | grep -E '^[A-Z_]+=' || true
  } >> "${ENV_FILE}"
  echo "Appended staging payment vars from ${TEMPLATE}"
fi

cd "${BACKEND}"
php artisan config:clear --ansi
echo "Config cleared. Run: php artisan platform:payment-env-check --profile=staging"
