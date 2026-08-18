#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${BASE_URL:-}" && -f "${BACKEND_DIR}/.env" ]]; then
  _app_url="$(grep -E '^APP_URL=' "${BACKEND_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" || true)"
  if [[ -n "${_app_url}" ]]; then
    BASE_URL="${_app_url}"
  fi
fi
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
API="${BASE_URL}/api/v1"
EMAIL="${SMOKE_EMAIL:-super@jejakawan.com}"
PASSWORD="${SMOKE_PASSWORD:-ChangeMeOnFirstLogin!}"
SUBSCRIPTION_DOMAIN="${SMOKE_SUBSCRIPTION_DOMAIN:-${SMOKE_WORKSPACE_DOMAIN:-${SMOKE_TENANT_DOMAIN:-demo.jejakawan.com}}}"
BACKEND_DIR="${BACKEND_DIR:-/opt/ja-control-plane/backend}"

pass() { echo "OK  $*"; }
fail() { echo "FAIL $*"; exit 1; }

json_field() {
  local key="$1"
  php -r '
    $d = json_decode(stream_get_contents(STDIN), true);
    if (!is_array($d)) { exit(1); }
    $path = explode(".", $argv[1]);
    $cur = $d;
    foreach ($path as $p) {
      if (!is_array($cur) || !array_key_exists($p, $cur)) { exit(1); }
      $cur = $cur[$p];
    }
    if (is_scalar($cur)) { echo $cur; exit(0); }
    exit(1);
  ' "$key"
}

issue_console_token() {
  php "${BACKEND_DIR}/artisan" tinker --execute \
    '$u=Modules\Core\System\Models\User::where("email", "'"${EMAIL}"'")->first();
     if (!$u) { echo ""; exit(0); }
     echo $u->createToken("api-smoke")->plainTextToken;' 2>/dev/null | tr -d '\n'
}

prepare_member_login() {
  php "${BACKEND_DIR}/artisan" tinker --execute \
    '     use Modules\Core\System\Models\Role;
     use Modules\Core\System\Models\User;
     use Modules\Operational\Member\Models\Member;
     use Modules\Operational\Platform\Models\PlatformSubscription;
     $subscription = PlatformSubscription::where("domain", "'"${SUBSCRIPTION_DOMAIN}"'")->where("status", "active")->first();
     if (!$subscription) { echo "NO_SUBSCRIPTION"; exit(0); }
     $email = "smoke-member@'"${SUBSCRIPTION_DOMAIN//./-}"'.test";
     $user = User::firstOrCreate(["email" => $email], ["name" => "Smoke Member", "password" => bcrypt("password123")]);
     $user->markEmailAsVerified();
     $memberRole = Role::firstOrCreate(["name" => "member", "guard_name" => "web"]);
     if (!$user->hasRole("member")) { $user->assignRole($memberRole); }
     Member::withoutGlobalScopes()->firstOrCreate(
       ["subscription_id" => $subscription->id, "user_id" => $user->id],
       ["points" => 0, "tier" => "bronze"]
     );
     echo $email;' 2>/dev/null | tr -d '\n'
}

echo "=== API smoke @ ${API} ==="

ADMIN_TOKEN="${SMOKE_ADMIN_TOKEN:-$(issue_console_token)}"
[[ -n "${ADMIN_TOKEN}" ]] || fail "could not issue console token"
pass "console sanctum token issued"

AUTH=(-H "Authorization: Bearer ${ADMIN_TOKEN}" -H "Accept: application/json")

curl -sS "${AUTH[@]}" "${API}/manage/platform" | grep -q '"success":true' || fail "platform overview"
pass "GET /manage/platform"

curl -sS "${AUTH[@]}" "${API}/manage/infra/cck/types" | grep -q '"success":true' || fail "cck types list"
pass "GET /manage/infra/cck/types"

MEMBER_EMAIL="$(prepare_member_login)"
[[ "${MEMBER_EMAIL}" != "NO_SUBSCRIPTION" && -n "${MEMBER_EMAIL}" ]] || fail "active subscription or member seed"
pass "member fixture ready (${MEMBER_EMAIL})"

curl -sS \
  -H "Accept: application/json" \
  -H "X-Subscription-Domain: ${SUBSCRIPTION_DOMAIN}" \
  "${API}/public/member/registration-policy" | grep -q '"member_registration_allowed":true' \
  || fail "registration-policy (enable subscription flag in seed)"
pass "GET /public/member/registration-policy"

REGISTER_EMAIL="e2e-register-$(date +%s)@${SUBSCRIPTION_DOMAIN//./-}.test"
REGISTER_BODY="$(curl -sS \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-Subscription-Domain: ${SUBSCRIPTION_DOMAIN}" \
  -X POST "${API}/public/member/register" \
  -d "{\"name\":\"E2E Register\",\"email\":\"${REGISTER_EMAIL}\",\"password\":\"Password123!\",\"password_confirmation\":\"Password123!\"}")"
echo "$REGISTER_BODY" | grep -q '"success":true' || fail "member register ($REGISTER_BODY)"
pass "POST /public/member/register (${REGISTER_EMAIL})"

curl -sS "${AUTH[@]}" "${API}/dynamic/announcements" | grep -q '"success":true' || fail "dynamic announcements list"
pass "GET /dynamic/announcements (CckDemoSeeder)"

LOGIN_MEMBER="$(curl -sS \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-Subscription-Domain: ${SUBSCRIPTION_DOMAIN}" \
  -X POST "${API}/public/member/login" \
  -d "{\"email\":\"${MEMBER_EMAIL}\",\"password\":\"password123\",\"subscription_domain\":\"${SUBSCRIPTION_DOMAIN}\"}")"
echo "$LOGIN_MEMBER" | grep -q '"success":true' || fail "member login ($LOGIN_MEMBER)"
MEMBER_TOKEN="$(echo "$LOGIN_MEMBER" | json_field data.token 2>/dev/null || true)"
[[ -n "${MEMBER_TOKEN:-}" ]] || fail "member token missing"
pass "POST /public/member/login"

curl -sS -H "Authorization: Bearer ${MEMBER_TOKEN}" \
  -H "X-Subscription-Domain: ${SUBSCRIPTION_DOMAIN}" \
  -H "Accept: application/json" \
  "${API}/public/member/profile" | grep -q '"success":true' || fail "member profile"
pass "GET /public/member/profile"

echo "=== All smoke checks passed ==="
