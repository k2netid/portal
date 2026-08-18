#!/usr/bin/env bash
set -euo pipefail

PROFILE="${1:-staging}"
PROVIDER="${2:-all}"
BACKEND_DIR="${BACKEND_DIR:-$(cd "$(dirname "$0")/../backend" && pwd)}"

cd "${BACKEND_DIR}"

echo "=== SaaS payment env check (profile=${PROFILE}, provider=${PROVIDER}) ==="
php artisan platform:payment-env-check --profile="${PROFILE}" --provider="${PROVIDER}"
echo "=== OK ==="
