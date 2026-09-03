#!/usr/bin/env bash
set -euo pipefail

# K2NET Portal — Automated Load & Stress Testing Runner
# Mendukung k6 (jika ada) dan fallback langsung ke high-speed autocannon (Node.js engine)

TARGET_URL="${1:-http://127.0.0.1:8083}"
CONCURRENCY="${2:-100}"
DURATION="${3:-10}"

echo "=========================================================="
echo "🚀 K2NET Portal Load & Stress Testing"
echo "Target URL:       $TARGET_URL"
echo "Concurrency (VUs): $CONCURRENCY"
echo "Duration:         ${DURATION}s"
echo "=========================================================="

if command -v k6 &> /dev/null; then
  echo "==> Menjalankan scenario k6 native..."
  TARGET_URL="$TARGET_URL" k6 run tests/load/k6-stress-test.js
  exit 0
fi

echo "==> k6 belum terinstal di sistem; menggunakan engine Autocannon (High-Concurrency Node.js Benchmark)..."

PAGES=(
  "/"
  "/pricing/isp"
  "/contact"
)

for PAGE in "${PAGES[@]}"; do
  URL="${TARGET_URL}${PAGE}"
  echo ""
  echo "----------------------------------------------------------"
  echo "📊 Stress testing route: $PAGE ($URL)"
  echo "   Simulasi $CONCURRENCY koneksi concurrent selama ${DURATION}s..."
  echo "----------------------------------------------------------"
  npx --yes autocannon \
    --connections "$CONCURRENCY" \
    --duration "$DURATION" \
    --pipelining 1 \
    --renderStatusCodes \
    "$URL"
done

echo ""
echo "=========================================================="
echo "✅ Load & Stress Testing selesai."
echo "=========================================================="
