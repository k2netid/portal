#!/usr/bin/env bash
# Run Playwright E2E inside the official Microsoft image (same pin as CI).
# Host Playwright browsers are unsupported on this PVE kernel — do not install them locally.
#
# Copy this script to other repos. It pins the image to @playwright/test in that repo's lockfile.
#
# Usage (app + Vite already up on :8000 / :5273):
#   ./scripts/e2e-docker.sh
#   ./scripts/e2e-docker.sh npm run test:e2e:auth
#   PW_WORKDIR=/path/to/other-app/frontend ./scripts/e2e-docker.sh npm run test:e2e:smoke

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORKDIR="${PW_WORKDIR:-$ROOT/frontend}"

ENGINE=()
if command -v podman >/dev/null 2>&1; then
  if [[ "${EUID}" -eq 0 ]] || podman info >/dev/null 2>&1; then
    ENGINE=(podman)
  else
    ENGINE=(sudo podman)
  fi
elif command -v docker >/dev/null 2>&1; then
  ENGINE=(docker)
else
  echo "Neither podman nor docker is installed." >&2
  echo "On AlmaLinux/RHEL: sudo dnf install -y podman podman-docker" >&2
  exit 1
fi

if [[ ! -f "$WORKDIR/package.json" ]]; then
  echo "No package.json in $WORKDIR" >&2
  exit 1
fi

PW_VERSION=""
if [[ -f "$WORKDIR/package-lock.json" ]]; then
  PW_VERSION="$(
    node -e "const l=require(process.argv[1]); const p=l.packages?.['node_modules/@playwright/test']; if(p?.version) process.stdout.write(p.version)" \
      "$WORKDIR/package-lock.json" 2>/dev/null || true
  )"
fi

IMAGE="${PLAYWRIGHT_IMAGE:-}"
if [[ -z "$IMAGE" ]]; then
  if [[ -z "$PW_VERSION" ]]; then
    echo "Could not resolve @playwright/test version. Set PLAYWRIGHT_IMAGE." >&2
    exit 1
  fi
  IMAGE="mcr.microsoft.com/playwright:v${PW_VERSION}-noble"
fi

CMD=("$@")
if [[ ${#CMD[@]} -eq 0 ]]; then
  CMD=(npm run test:e2e:smoke)
fi

echo "Engine: ${ENGINE[*]}"
echo "Playwright image: $IMAGE"
echo "Workdir: $WORKDIR"
echo "Base URL: ${PLAYWRIGHT_BASE_URL:-http://127.0.0.1:5273}"

# If node_modules is a symlink (e.g. ja-core_engine → ja-cms), mount the parent
# so the symlink still resolves inside the container.
CONTAINER_WORKDIR="/work"
VOLUME_ARGS=(-v "$WORKDIR:/work")
if [[ -L "$WORKDIR/node_modules" ]]; then
  NM_REAL="$(readlink -f "$WORKDIR/node_modules")"
  DEV_ROOT="$(cd "$(dirname "$WORKDIR")/.." && pwd)"
  if [[ "$NM_REAL" == "$DEV_ROOT"* ]]; then
    CONTAINER_WORKDIR="$WORKDIR"
    VOLUME_ARGS=(-v "$DEV_ROOT:$DEV_ROOT")
    echo "node_modules symlink → $NM_REAL (mount $DEV_ROOT)"
  fi
fi

"${ENGINE[@]}" run --rm \
  --ipc=host \
  --network=host \
  --init \
  "${VOLUME_ARGS[@]}" \
  -w "$CONTAINER_WORKDIR" \
  -e HOME=/tmp \
  -e PLAYWRIGHT_BROWSERS_PATH=/ms-playwright \
  -e PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1 \
  -e PLAYWRIGHT_BASE_URL="${PLAYWRIGHT_BASE_URL:-http://127.0.0.1:5273}" \
  -e E2E_LOGIN_EMAIL="${E2E_LOGIN_EMAIL:-super@jejakawan.com}" \
  -e E2E_LOGIN_PASSWORD="${E2E_LOGIN_PASSWORD:-password}" \
  -e E2E_CAPTCHA_BYPASS_TOKEN="${E2E_CAPTCHA_BYPASS_TOKEN:-local-e2e}" \
  -e PLAYWRIGHT_WORKERS="${PLAYWRIGHT_WORKERS:-1}" \
  -e PLAYWRIGHT_PUBLIC_HOST="${PLAYWRIGHT_PUBLIC_HOST:-}" \
  -e PLAYWRIGHT_USE_FULL_STACK="${PLAYWRIGHT_USE_FULL_STACK:-}" \
  -e CI="${CI:-}" \
  "$IMAGE" \
  "${CMD[@]}"
