#!/usr/bin/env bash
set -euo pipefail

# K2NET Portal — Stepped / Gradual Load & Stress Testing
# Menguji ketahanan server secara bertahap: 100 -> 250 -> 500 -> 1000 -> 1500 VUs

TARGET_ROUTE="${1:-/pricing/isp}"
BASE_URL="${2:-http://127.0.0.1:8083}"
DURATION="${3:-5}"

STEPS=(100 250 500 1000 1500)

echo "=========================================================="
echo "⚡ K2NET Portal — Pengujian Beban Bertahap (Stepped Load Test)"
echo "Target Route:  $TARGET_ROUTE"
echo "Target URL:    ${BASE_URL}${TARGET_ROUTE}"
echo "Durasi/Tahap:  ${DURATION}s"
echo "Tahapan VUs:   ${STEPS[*]}"
echo "=========================================================="

URL="${BASE_URL}${TARGET_ROUTE}"

for VUS in "${STEPS[@]}"; do
  echo ""
  echo "=========================================================="
  echo "🔥 TAHAP: $VUS Concurrent Connections (VUs) selama ${DURATION}s"
  echo "=========================================================="

  npx --yes autocannon \
    --connections "$VUS" \
    --duration "$DURATION" \
    --pipelining 1 \
    --renderStatusCodes \
    "$URL"

  # Jeda 2 detik antar tahap agar koneksi socket recycle
  sleep 2
done

echo ""
echo "=========================================================="
echo "🎉 Seluruh tahapan pengujian beban bertahap SELESAI."
echo "=========================================================="
