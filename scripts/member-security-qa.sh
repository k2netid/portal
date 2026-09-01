#!/usr/bin/env bash
set -euo pipefail

API_BASE="${API_BASE:-http://127.0.0.1:8000/api/v1}"
CAPTCHA_HEADER="${E2E_CAPTCHA_BYPASS_TOKEN:-local-e2e}"
EMAIL="qa-member-$(date +%s)@example.com"
PASSWORD="${E2E_MEMBER_PASSWORD:-Password12!}"

echo "== Member security QA smoke =="
echo "API: $API_BASE"

register=$(curl -sS -X POST "$API_BASE/public/member/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-E2E-Captcha-Bypass: $CAPTCHA_HEADER" \
  -d "{\"name\":\"QA Reader\",\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\",\"password_confirmation\":\"$PASSWORD\"}")

token=$(echo "$register" | php -r '$j=json_decode(stream_get_contents(STDIN), true); echo $j["data"]["token"] ?? "";')
if [[ -z "$token" ]]; then
  echo "FAIL register: $register"
  exit 1
fi
echo "OK register $EMAIL"

login=$(curl -sS -X POST "$API_BASE/public/member/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-E2E-Captcha-Bypass: $CAPTCHA_HEADER" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")
echo "$login" | php -r '$j=json_decode(stream_get_contents(STDIN), true); exit(empty($j["data"]["token"]) ? 1 : 0);' || { echo "FAIL login: $login"; exit 1; }
echo "OK login"

logout=$(curl -sS -X POST "$API_BASE/member/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $token")
echo "$logout" | php -r '$j=json_decode(stream_get_contents(STDIN), true); exit(($j["success"] ?? false) ? 0 : 1);' || { echo "FAIL logout: $logout"; exit 1; }
echo "OK logout (audit member_logout expected in sec_logs)"

echo "Done."
