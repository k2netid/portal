#!/usr/bin/env bash
# Create theme via theme:make, ZIP it, run upload install test.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SLUG="${THEME_SLUG:-e2e-demo-pack}"
NAME="${THEME_NAME:-E2E Demo Pack}"
THEMES_DIR="$ROOT/frontend/src/modules/Content/Layout/views/themes"
ZIP_PATH="${TMPDIR:-/tmp}/${SLUG}.zip"

cd "$ROOT/backend"

if [[ -d "$THEMES_DIR/$SLUG" ]]; then
  echo "Removing existing theme dir $SLUG"
  rm -rf "$THEMES_DIR/$SLUG"
fi

php artisan theme:make "$NAME" --slug="$SLUG" --no-interaction
test -f "$THEMES_DIR/$SLUG/theme.json"

rm -f "$ZIP_PATH"
(cd "$THEMES_DIR" && zip -qr "$ZIP_PATH" "$SLUG")
echo "ZIP: $ZIP_PATH ($(du -h "$ZIP_PATH" | cut -f1))"

php artisan test --filter=ThemePackageInstallServiceTest
echo "OK theme:make + ZIP + upload test"
