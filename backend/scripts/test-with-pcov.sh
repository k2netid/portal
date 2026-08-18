#!/usr/bin/env bash
# Aktifkan pcov + matikan JIT hanya untuk proses PHPUnit ini (override CLI, bukan php.ini global).
# Konfigurasi coverage ada di phpunit.coverage.xml agar phpunit.xml default aman untuk pcov off (JIT LMS).

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-php}"
PCOV_DIR="${PCOV_DIRECTORY:-$ROOT_DIR}"
PHPUNIT_COV="${PHPUNIT_COVERAGE_CONFIG:-$ROOT_DIR/phpunit.coverage.xml}"

PHP_D_ARGS=(
  "-d" "pcov.enabled=1"
  "-d" "pcov.directory=${PCOV_DIR}"
  "-d" "opcache.jit=0"
)

if "$PHP_BIN" -r 'exit(extension_loaded("pcov") ? 0 : 1);' >/dev/null 2>&1; then
  EXTRA_ARGS=()
elif "$PHP_BIN" -d extension=pcov -r 'exit(extension_loaded("pcov") ? 0 : 1);' >/dev/null 2>&1; then
  EXTRA_ARGS=("-d" "extension=pcov")
else
  echo "[pcov] Extension pcov tidak tersedia. Install/aktifkan PCOV dulu." >&2
  exit 1
fi

echo "[pcov] Enable untuk sesi test ini (auto-disable setelah proses selesai)."

if [ "$#" -eq 0 ]; then
  exec "$PHP_BIN" "${EXTRA_ARGS[@]}" "${PHP_D_ARGS[@]}" ./vendor/bin/pest -c "${PHPUNIT_COV}" --coverage-text
else
  # Argumen tambahan (filter path, dll.) diletakkan sebelum --coverage-text.
  exec "$PHP_BIN" "${EXTRA_ARGS[@]}" "${PHP_D_ARGS[@]}" ./vendor/bin/pest -c "${PHPUNIT_COV}" "$@" --coverage-text
fi
