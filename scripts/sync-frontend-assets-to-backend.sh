#!/usr/bin/env bash
# Build the Vue dashboard and sync dist/ → backend/public/ (logo, favicon, hashed JS/CSS).
#
# Usage from repo root (ja-core_engine):
#   npm run deploy:assets:full         # ONE command: clean rebuild + rsync (preferred)
#   npm run deploy:assets:sync         # rsync only — dist/ must already exist
#
# Do NOT chain "npm run build" before deploy:assets:full — that runs rebuild twice.
# Requires: Node 22.12+ (see .nvmrc), npm, rsync.
set -euo pipefail

# Prefer system Node 22 (NodeSource) over Cursor embedded Node 20 in agent shells.
export PATH="/usr/bin:${PATH}"

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC_DIST="$ROOT/frontend/dist/"
DST_PUBLIC="$ROOT/backend/public/"

NODE_BIN="$(command -v node)"
NODE_VER="$(node -v 2>/dev/null || echo unknown)"
echo "Using node: ${NODE_BIN} (${NODE_VER})"
case "${NODE_VER}" in
  v22.*|v23.*|v24.*|v25.*|v26.*) ;;
  *)
    echo "error: Node 22.12+ required (got ${NODE_VER}). Install NodeSource 22 or run: source .envrc" >&2
    exit 1
    ;;
esac

for bin in npm rsync; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "error: '$bin' not found in PATH" >&2
    exit 1
  fi
done

if [ "${SYNC_ONLY:-0}" != "1" ]; then
  echo "Full build and sync..."
  cd "$ROOT/frontend"
  export VITE_BUILD_ID="$(date -u +%Y%m%d%H%M%S)-$(git -C "$ROOT" rev-parse --short HEAD 2>/dev/null || echo local)"
  echo "VITE_BUILD_ID=${VITE_BUILD_ID}"
  npm run rebuild
else
  echo "SYNC_ONLY=1 — skipping npm run rebuild"
fi

if [ ! -d "$SRC_DIST" ]; then
  echo "error: missing $SRC_DIST (run without SYNC_ONLY first)" >&2
  exit 1
fi

mkdir -p "$DST_PUBLIC"
# Sync all contents of dist to backend/public, but PROTECT core Laravel entrance files
rsync -a --delete \
  --exclude='index.php' \
  --exclude='.htaccess' \
  --exclude='robots.txt' \
  --exclude='.well-known' \
  --exclude='storage' \
  "$SRC_DIST" "$DST_PUBLIC"

echo "OK: synced $SRC_DIST → $DST_PUBLIC"

# Runtime theme packages (theme.json + sample-data) so staging/prod www can scan-register
# after frontend/ is stripped from the publish tree.
THEMES_SRC="$ROOT/frontend/src/modules/Layout/views/themes"
THEMES_DST="$ROOT/backend/resources/themes"
if [ -d "$THEMES_SRC" ]; then
  mkdir -p "$THEMES_DST"
  for slug in janari layung sarangenge; do
    if [ -d "$THEMES_SRC/$slug" ]; then
      rsync -a --delete "$THEMES_SRC/$slug/" "$THEMES_DST/$slug/"
    fi
  done
  echo "OK: synced theme packages → $THEMES_DST"
fi
