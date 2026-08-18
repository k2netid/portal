#!/usr/bin/env bash
# Di server LMS: pcov sering dimatikan di php.ini agar opcache JIT tetap jalan (performa).
# Tes biasa: composer test / php artisan test — pakai phpunit.xml (tanpa driver coverage).
# Coverage satu kali: composer test:coverage (wrapper mengaktifkan pcov hanya untuk proses itu).

set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"

echo "[pcov] Verifikasi CLI dengan pcov.enabled=0 (hanya proses ini; tidak mengubah php.ini server)."
"$PHP_BIN" -d pcov.enabled=0 -r 'echo "pcov.enabled=0 (CLI override)\n";'
echo "[pcov] test-with-pcov.sh tidak menyentuh config global; setelah composer test:coverage selesai, proses berakhir dan tidak ada pcov yang tertinggal aktif."
