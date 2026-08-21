#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKEND_DIR="${BACKEND_DIR:-${ROOT}/backend}"

if [[ -z "${BASE_URL:-}" && -f "${BACKEND_DIR}/.env" ]]; then
  _app_url="$(grep -E '^APP_URL=' "${BACKEND_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
  if [[ -n "${_app_url}" ]]; then
    BASE_URL="${_app_url}"
  fi
fi
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
API="${BASE_URL}/api/v1"
EMAIL="${SMOKE_EMAIL:-super@jejakawan.com}"

pass() { echo "OK   $*"; }
fail() { echo "FAIL $*"; exit 1; }

issue_console_token() {
  php "${BACKEND_DIR}/artisan" tinker --execute \
    '$u=Modules\Core\System\Models\User::where("email", "'"${EMAIL}"'")->first() ?: Modules\Core\System\Models\User::first();
     if (!$u) { echo ""; exit(0); }
     echo $u->createToken("api-smoke")->plainTextToken;' 2>/dev/null | tr -d '\n'
}

echo "=== Core Engine API Smoke Test @ ${API} ==="

ADMIN_TOKEN="${SMOKE_ADMIN_TOKEN:-$(issue_console_token)}"
[[ -n "${ADMIN_TOKEN}" ]] || fail "could not issue console token"
pass "Console sanctum token issued"

AUTH=(-H "Authorization: Bearer ${ADMIN_TOKEN}" -H "Accept: application/json")

curl -sS "${AUTH[@]}" "${API}/manage/infra/models/types" | grep -q '"success":true' || fail "Data Model Studio types list"
pass "GET /manage/infra/models/types"

curl -sS "${AUTH[@]}" "${API}/manage/system/users" | grep -q '"success":true' || fail "Users list API"
pass "GET /manage/system/users"

curl -sS "${AUTH[@]}" "${API}/manage/system/roles" | grep -q '"success":true' || fail "Roles list API"
pass "GET /manage/system/roles"

curl -sS "${AUTH[@]}" "${API}/manage/system/settings" | grep -q '"success":true' || fail "Settings API"
pass "GET /manage/system/settings"

curl -sS "${AUTH[@]}" "${API}/manage/system/scheduled-tasks" | grep -q '"success":true' || fail "Scheduled tasks list API"
pass "GET /manage/system/scheduled-tasks"

curl -sS "${AUTH[@]}" "${API}/manage/system/scheduled-tasks/allowed-commands" | grep -q '"success":true' || fail "Allowed commands API"
pass "GET /manage/system/scheduled-tasks/allowed-commands"

echo "=== All Core Engine API smoke checks passed successfully ==="
