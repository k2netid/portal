#!/usr/bin/env bash
# Jejakawan hosted stack sanity check (no secrets printed).
# Product: Jejakawan · Infra operator: K2NET (typical)
# Usage: ./scripts/check-hosted-stack.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKEND="${ROOT}/backend"
cd "${BACKEND}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

pass() { echo -e "${GREEN}PASS${NC} $*"; }
warn() { echo -e "${YELLOW}WARN${NC} $*"; }
fail() { echo -e "${RED}FAIL${NC} $*"; }

echo "=== Jejakawan hosted stack check ==="
echo "Backend: ${BACKEND}"
echo ""

if [[ ! -f .env ]]; then
  fail "backend/.env missing"
  exit 1
fi

DB_CONN="$(grep -E '^DB_CONNECTION=' .env | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
DB_HOST="$(grep -E '^DB_HOST=' .env | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
DB_PORT="$(grep -E '^DB_PORT=' .env | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
DB_NAME="$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
PROV_URL="$(grep -E '^PLATFORM_PROVISIONING_API_URL=' .env | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
PROV_STUB="$(grep -E '^PLATFORM_PROVISIONING_STUB=' .env | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"

echo "--- Database ---"
if [[ "${DB_CONN}" == "pgsql" ]]; then
  pass "DB_CONNECTION=pgsql (hosted production standard)"
else
  warn "DB_CONNECTION=${DB_CONN:-<unset>} — hosted Jejakawan production expects pgsql"
fi

if php artisan db:show >/dev/null 2>&1; then
  pass "Laravel can connect (php artisan db:show)"
  php artisan db:show 2>/dev/null | head -8 | sed 's/^/  /'
else
  fail "Cannot connect to database — fix .env / PostgreSQL service"
fi

echo ""
echo "--- PostgreSQL tools ---"
if command -v pg_dump >/dev/null 2>&1; then
  pass "pg_dump: $(pg_dump --version | head -1)"
else
  fail "pg_dump not on PATH — required for platform:workspace-db-backup on PostgreSQL"
fi

echo ""
echo "--- SaaS provisioning mode ---"
if [[ -n "${PROV_URL}" ]]; then
  pass "PLATFORM_PROVISIONING_API_URL is set (live provisioner)"
elif [[ "${PROV_STUB}" == "true" ]]; then
  warn "PLATFORM_PROVISIONING_STUB=true — forced stub"
else
  warn "No PLATFORM_PROVISIONING_API_URL — stub mode (database_name metadata only)"
fi

echo ""
echo "--- Tenant DB backup dry-run ---"
php artisan platform:workspace-db-backup --dry-run 2>&1 | sed 's/\*\*\*/*** /g' | sed 's/^/  /' || fail "platform:workspace-db-backup failed"

echo ""
echo "--- Physical workspace databases (PostgreSQL) ---"
if [[ "${DB_CONN}" == "pgsql" ]]; then
  php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$names = \Modules\Operational\SaaS\Models\PlatformSubscription::query()
    ->whereNotNull("database_name")->where("database_name", "!=", "")
    ->pluck("database_name");
if ($names->isEmpty()) {
    echo "NONE\n";
    exit(0);
}
foreach ($names as $db) {
    $row = Illuminate\Support\Facades\DB::selectOne(
        "SELECT 1 AS ok FROM pg_database WHERE datname = ?",
        [$db]
    );
    echo ($row ? "EXISTS:" : "MISSING:").$db."\n";
}
' 2>/dev/null | while IFS= read -r line; do
    case "${line}" in
      NONE) warn "No platform_subscriptions with legacy database_name (hub is single-DB)" ;;
      EXISTS:*) pass "PostgreSQL database exists: ${line#EXISTS:}" ;;
      MISSING:*) warn "Metadata only (DB not created yet): ${line#MISSING:} — provisioner PG or CREATE DATABASE" ;;
      *) warn "${line}" ;;
    esac
  done
fi

echo ""
echo "--- Payment env (staging profile) ---"
php artisan platform:payment-env-check --profile=staging 2>&1 | tail -5 | sed 's/^/  /' || true

echo ""
echo "Done. See docs/operational/jejakawan-hosted-postgresql-checklist.md"
